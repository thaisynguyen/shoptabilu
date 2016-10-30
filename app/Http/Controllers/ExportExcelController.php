<?php namespace App\Http\Controllers;
date_default_timezone_set('Asia/Ho_Chi_Minh');
use Illuminate\Http\Request;
use Session;
use DB;
use Utils\commonUtils;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Cell;
use Convenient\excelUtils;

class ExportExcelController extends AppController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function outputFile($fileName, PHPExcel $objPHPExcel)
    {
        $filename = $fileName . '_' . date('Y-m-d') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //downloadable file is in Excel 2003 format (.xls)
        $objWriter->save('php://output');
    }

    /**
     * @param $name
     * @return string
     */
    private function convertNameSheet($name){
        $arrName = str_split($name);
        $nameSuccess = '';
        foreach($arrName as $n){
            if($n=='/' || $n=='[' || $n==']' || $n=='\\' || $n==':' || $n == '/' || $n=='?'){
                $nameSuccess.= ' ';
            } else {
                $nameSuccess.=$n;
            }
        }
        return $nameSuccess;
    }

    /**
     * Export danh muc cap 2
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function exportGoalLevelTwo($parentId)
    {

        if(!is_numeric($parentId)){
            Session::flash('message-errors', 'Mục tiêu cấp 1 không hợp lệ!');
            return redirect('goalLevelTwoCategories/0' );
        }

        $sql = "
            SELECT g.*, u.unit_name
            FROM goal g
            LEFT JOIN unit u ON u.id = g.unit_id
            WHERE g.inactive = 0
        ";

        if($parentId != 0){
            $sql .= " AND g.parent_id =  ".$parentId;
        }

        $objGoalDB = DB::select(DB::raw($sql));

        if(count($objGoalDB) == 0){
            Session::flash('message-errors', 'Vui lòng import dữ liệu trước khi xuất report!');
            return redirect('goalLevelTwoCategories/0' );
        }

        /* *************************************************************************************************************
         * Load sample
         * ************************************************************************************************************/
        $objPHPExcel = excelUtils::loadFile(excelUtils::PATH_BLANK);
        /* *************************************************************************************************************
         * write to excel
         * ************************************************************************************************************/
        $indexHeader = 1;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$indexHeader , 'DANH SÁCH MỤC TIÊU ')
        ;

        $indexTitle = 4;
        $beginData  = $indexTitle +1;

        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$indexTitle , 'STT')
            ->setCellValue('B'.$indexTitle , 'Mã')
            ->setCellValue('C'.$indexTitle , 'Mục tiêu')
            ->setCellValue('D'.$indexTitle , 'Loại mục tiêu')
            ->setCellValue('E'.$indexTitle , 'Công thức tính')
            ->setCellValue('F'.$indexTitle , 'Đơn vị tính')
        ;
        $no = 1;
        $arrPRow = array();
        $arrCRow = array();
        $lastRow = 1;
        foreach($objGoalDB as $goalParent){
            if($goalParent->parent_id == 0){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A'.$beginData , $no++)
                    ->setCellValue('B'.$beginData , $goalParent->goal_code)
                    ->setCellValue('C'.$beginData , $goalParent->goal_name)
                    ->setCellValue('D'.$beginData , '')
                    ->setCellValue('E'.$beginData , '')
                    ->setCellValue('F'.$beginData , '')
                ;
                $arrPRow[] = $beginData;
                $beginData++;
                $childRow = $beginData;

                foreach($objGoalDB as $goalChild){
                    if($goalChild->parent_id == $goalParent->id){
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue('A'.$childRow , $no++)
                            ->setCellValue('B'.$childRow , $goalChild->goal_code)
                            ->setCellValue('C'.$childRow , $goalChild->goal_name)
                            ->setCellValue('D'.$childRow , commonUtils::renderGoalTypeName($goalChild->goal_type))
                            ->setCellValue('E'.$childRow , commonUtils::renderFormulaOfGoalType($goalChild->formula))
                            ->setCellValue('F'.$childRow , $goalChild->unit_name)
                        ;

                        $arrCRow[] = $childRow;
                        $lastRow = $childRow;
                        $childRow++;
                    }
                }

                $beginData = $childRow;
            }
        }

        /* *************************************************************************************************************
         * Format template
         * ************************************************************************************************************/
        excelUtils::mergeCells($objPHPExcel, 'A'.$indexHeader.':F'.$indexHeader);
        excelUtils::setBold($objPHPExcel, 'A'.$indexHeader.':F'.$indexTitle);
        excelUtils::setHorizontal($objPHPExcel, 'A'.$indexHeader.':F'.$indexTitle, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        excelUtils::setHorizontal($objPHPExcel, 'A'.$indexTitle.':A'.$lastRow, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        excelUtils::setVertical($objPHPExcel, 'A'.$indexHeader.':F'.$lastRow, \PHPExcel_Style_Alignment::VERTICAL_CENTER);
        excelUtils::setRowHeight($objPHPExcel, $indexHeader, 25);
        excelUtils::setRowHeight($objPHPExcel, $indexTitle, 20);
        excelUtils::setFontSize($objPHPExcel, 'A'.$indexHeader, 20);
        $hashColumn = range('A', 'F');
        $count = 0;
        foreach ($hashColumn as $key => $value) {
            $dimension = 0;
            switch ($value) {
                case 'A':
                    $dimension = 8;
                    break;
                case 'B':
                case 'F':
                    $dimension = 12;
                    break;
                case 'C':
                    $dimension = 70;
                    break;
                case 'D':
                    $dimension = 19;
                    break;
                case 'E':
                    $dimension = 30;
                    break;
            }
            excelUtils::setColumnWidth($objPHPExcel, $value, $dimension);
            $count++;
        }

        foreach($arrPRow as $pRow){
            excelUtils::setBold($objPHPExcel, 'A'.$pRow.':F'.$pRow);
            excelUtils::fillBackGroundColor($objPHPExcel, 'A'.$pRow.':F'.$pRow, excelUtils::COLOR_GREEN);
            excelUtils::setBorderCell($objPHPExcel, 'A'.$pRow.':F'.$pRow, excelUtils::styleBorder());
        }

        foreach($arrCRow as $cRow){
            excelUtils::setBorderCell($objPHPExcel, 'A'.$cRow.':F'.$cRow, excelUtils::styleBorderChild());
        }
        excelUtils::setBorderCell($objPHPExcel, 'A'.$indexTitle.':F'.$indexTitle, excelUtils::styleBorder());
        excelUtils::fillBackGroundColor($objPHPExcel, 'A'.$indexTitle.':F'.$indexTitle, excelUtils::COLOR_DARK);
        excelUtils::setBorderCell($objPHPExcel, 'A'.$lastRow.':F'.$lastRow, excelUtils::styleBorderLasted());
        excelUtils::setZoomSheet($objPHPExcel, excelUtils::DEFAULT_ZOOM);
         /* ***********************************************************************************************************/
        $fileName = "DanhSachMucTieu";
        $this->outputFile($fileName, $objPHPExcel);
    }

    /**
     * Export danh sach nhan vien
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function exportEmployee($areaCode, $eCode, $sKey)
    {


        $sDataUser = Session::get('sDataUser');

        $accessLevel = $sDataUser->access_level;

        if($areaCode != "full"){
            $sqlArea = "
                SELECT *
                FROM area
                WHERE company_id = ".$sDataUser->company_id."
                AND inactive = 0
                AND area_code = '".$areaCode."'
            ";

            $objAreaDB = DB::select(DB::raw($sqlArea));

            if(count($objAreaDB) == 0){
                Session::flash('message-errors', 'Tổ/Quận/Huyện không tồn tại!');
                return redirect('employeeCategories');
            }
        }

        if($sDataUser->id == 0){
            $sqlUser = "
                SELECT us.*, c.company_code, c.company_name, a.area_code, a.area_name, g.group_code, g.group_name, al.access_level_code, al.access_level_name, p.position_code, p.position_name
                FROM users us
                LEFT JOIN `position` p ON p.id = us.position_id
                LEFT JOIN `group` g ON g.id = us.group_id
                LEFT JOIN `company` c ON c.id = us.company_id
                LEFT JOIN `area` a ON a.id = us.area_id
                LEFT JOIN `access_level` al ON al.id = us.access_level
                WHERE us.admin = 0
            ";


            if($sKey != "0"){
                $sqlUser .= " AND ( us.code LIKE '%".$sKey."%' OR us.name LIKE '%".$sKey."%') ";
            }else{
                if($areaCode != "full"){
                    $sqlUser .= " AND us.company_id = ".$sDataUser->company_id." ";
                    $sqlUser .= " AND us.area_id = ".$objAreaDB[0]->id." ";
                }

                if($eCode != "full"){
                    $sqlUser .= " AND us.code = '".$eCode."' ";
                }
            }
        }else{
            if(
                $accessLevel == 2
                || $accessLevel == 3
                || $accessLevel == 4
            ){
                $sqlUser = "
                    SELECT us.*, c.company_code, c.company_name, a.area_code, a.area_name, g.group_code, g.group_name, al.access_level_code, al.access_level_name,
                            p.position_code, p.position_name
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    LEFT JOIN `group` g ON g.id = us.group_id
                    LEFT JOIN `company` c ON c.id = us.company_id
                    LEFT JOIN `area` a ON a.id = us.area_id
                    LEFT JOIN `access_level` al ON al.id = us.access_level
                    WHERE us.admin = 0
                    AND us.company_id = ".$sDataUser->company_id."
                ";

                if($sKey != "0"){
                    $sqlUser .= " AND ( us.code LIKE '%".$sKey."%' OR us.name LIKE '%".$sKey."%') ";
                }else{
                    if($areaCode != "full"){
                        $sqlUser .= " AND us.area_id = ".$objAreaDB[0]->id." ";
                    }

                    if($eCode != "full"){
                        $sqlUser .= " AND us.code = '".$eCode."' ";
                    }
                }
            }elseif(
                $accessLevel == 5
                || $accessLevel == 6
            ){
                $sqlUser = "
                    SELECT us.*, c.company_code, c.company_name, a.area_code, a.area_name, g.group_code, g.group_name, al.access_level_code, al.access_level_name, p.position_code, p.position_name
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    LEFT JOIN `group` g ON g.id = us.group_id
                    LEFT JOIN `company` c ON c.id = us.company_id
                    LEFT JOIN `area` a ON a.id = us.area_id
                    LEFT JOIN `access_level` al ON al.id = us.access_level
                    WHERE us.admin = 0
                    AND us.company_id = ".$sDataUser->company_id."
                    AND us.area_id = ".$sDataUser->area_id."
                ";

                if($sKey != "0"){
                    $sqlUser .= " AND ( us.code LIKE '%".$sKey."%' OR us.name LIKE '%".$sKey."%') ";
                }else{
                    if($eCode != "full"){
                        $sqlUser .= " AND us.code = '".$eCode."' ";
                    }
                }
            }elseif(
                $accessLevel == 7
                || $accessLevel == 8
                || $accessLevel == 9
            ){
                $sqlUser = "
                    SELECT us.*, c.company_code, c.company_name, a.area_code, a.area_name, g.group_code, g.group_name, al.access_level_code, al.access_level_name, p.position_code, p.position_name
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    LEFT JOIN `group` g ON g.id = us.group_id
                    LEFT JOIN `company` c ON c.id = us.company_id
                    LEFT JOIN `area` a ON a.id = us.area_id
                    LEFT JOIN `access_level` al ON al.id = us.access_level
                    WHERE us.admin = 0
                    AND us.company_id = ".$sDataUser->company_id."
                    AND us.area_id = ".$sDataUser->area_id."
                    AND us.code = ".$eCode."
                ";

                if($sKey != "0"){
                    $sqlUser .= " AND ( us.code LIKE '%".$sKey."%' OR us.name LIKE '%".$sKey."%') ";
                }
            }
        }

        $sqlUser .= " ORDER BY us.position_id, p.position_name ";

        $data = DB::select(DB::raw($sqlUser));

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportEmployee.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $baseRow = 4;

        $defaultPosition = commonUtils::tempPosition();
        $arrPositionGroup = array();
        $iG = 0;

        foreach($data as $employee){

            if(count($arrPositionGroup) == 0){

                $idp = -1;
                foreach($defaultPosition as $dp){

                    if(commonUtils::compareTwoString($dp['name'], $employee->position_code) == 1){
                        $idp = $dp['id'];
                    }
                }

                if($idp != -1){
                    $arrPositionGroup[$iG]['positionCode'] = $employee->position_code;
                    $arrPositionGroup[$iG]['positionName'] = $employee->position_name;
                    $arrPositionGroup[$iG]['indexSort']    = $idp;
                    $iG++;
                }

            }else{
                $exist = 0;
                foreach($arrPositionGroup as $pg){
                    if(commonUtils::compareTwoString($pg['positionCode'], $employee->position_code) == 1){
                        $exist = 1;
                    }
                }

                if($exist == 0){
                    $idp = -1;
                    foreach($defaultPosition as $dp){

                        if(commonUtils::compareTwoString($dp['name'], $employee->position_code) == 1){
                            $idp = $dp['id'];
                        }
                    }

                    if($idp != -1){
                        $arrPositionGroup[$iG]['positionCode'] = $employee->position_code;
                        $arrPositionGroup[$iG]['positionName'] = $employee->position_name;
                        $arrPositionGroup[$iG]['indexSort']    = $idp;
                        $iG++;
                    }
                }
            }

        }


        if(count($arrPositionGroup) == 0){
            Session::flash('message-errors', 'Dữ liệu không tồn tại!');
            return redirect('employeeCategories');
        }

        $arrSortPosition = array();
        $iSP = 0;

        foreach($defaultPosition as $dPos){

            $posName = "";
            foreach($arrPositionGroup as $pg){
                if($pg['indexSort'] == $dPos['id']){
                    $posName = $pg['positionName'];
                    break;
                }
            }

            if($posName != ""){
                $arrSortPosition[$iSP]['positionCode'] = $dPos['name'];
                $arrSortPosition[$iSP]['positionName'] = $posName;
                $iSP++;
            }

        }

        $indexHeader = 4;

        $objPHPExcel->getActiveSheet()
            ->setCellValue('B' . ($indexHeader-2), 'DANH SÁCH NHÂN VIÊN')
            ->setCellValue('B' . $indexHeader, 'STT')
            ->setCellValue('C' . $indexHeader, 'Mã NV')
            ->setCellValue('D' . $indexHeader, 'Tên đăng nhập')
            ->setCellValue('E' . $indexHeader, 'Họ và tên')
            ->setCellValue('F' . $indexHeader, 'Mã Phòng/Đài/MBF HCM')
            ->setCellValue('G' . $indexHeader, 'Phòng/Đài/MBF HCM')
            ->setCellValue('H' . $indexHeader, 'Mã Tổ/Quận/Huyện')
            ->setCellValue('I' . $indexHeader, 'Tổ/Quận/Huyện')
            ->setCellValue('J' . $indexHeader, 'Mã Nhóm/Cửa hàng')
            ->setCellValue('K' . $indexHeader, 'Nhóm/Cửa hàng')
            ->setCellValue('L' . $indexHeader, 'Mã chức danh')
            ->setCellValue('M' . $indexHeader, 'Chức danh')
            ->setCellValue('N' . $indexHeader, 'Mã mức truy cập')
            ->setCellValue('O' . $indexHeader, 'Mức truy cập')
            ->setCellValue('P' . $indexHeader, 'Ngày nghỉ việc')
            ->setCellValue('Q' . $indexHeader, 'Chỉ xem')
            ->setCellValue('R' . $indexHeader, 'LDAP');


        $i = 1;

        $beginData = $indexHeader + 1;

        $arrParent = array();
        $arrChild  = array();

        foreach($arrSortPosition as $sPos){

            excelUtils::mergeCells($objPHPExcel, 'B'.$beginData.':E'.$beginData);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $beginData, '['.$sPos['positionCode'].'] '.$sPos['positionName']);
            $arrParent[] = $beginData;
            $childRow = $beginData + 1;
            foreach($data as $row){

                if(commonUtils::compareTwoString($row->position_code, $sPos['positionCode']) == 1){

                    $arrChild[] = $childRow;
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('B' . $childRow, $i++)
                        ->setCellValue('C' . $childRow, $row->code)
                        ->setCellValue('D' . $childRow, $row->username)
                        ->setCellValue('E' . $childRow, $row->name)
                        ->setCellValue('F' . $childRow, $row->company_code)
                        ->setCellValue('G' . $childRow, $row->company_name)
                        ->setCellValue('H' . $childRow, $row->area_code)
                        ->setCellValue('I' . $childRow, $row->area_name)
                        ->setCellValue('J' . $childRow, $row->group_code)
                        ->setCellValue('K' . $childRow, $row->group_name)
                        ->setCellValue('L' . $childRow, $row->position_code)
                        ->setCellValue('M' . $childRow, $row->position_name)
                        ->setCellValue('N' . $childRow, $row->access_level_code)
                        ->setCellValue('O' . $childRow, $row->access_level_name)
                        ->setCellValue('P' . $childRow, commonUtils::formatDate($row->terminate_date))
                        ->setCellValue('Q' . $childRow, $row->is_view)
                        ->setCellValue('R' . $childRow, $row->ldap);
                    $childRow++;

                }

            }
            $beginData = $childRow;

        }



        $lastRow = $beginData - 1;

        /**
         * Format file excel
         */

        $hashColumn = range('B', 'R');
        $count = 0;
        foreach ($hashColumn as $key => $value) {
            $dimension = 0;
            switch ($value) {
                case 'B':
                    $dimension = 6;
                    break;
                case 'C':
                case 'D':
                    $dimension = 18;
                    break;
                case 'E':
                    $dimension = 28;
                    break;
                case 'G':
                case 'K':
                case 'M':
                case 'O':
                    $dimension = 21;
                    break;
                case 'I':
                case 'F':
                case 'P':
                    $dimension = 14;
                    break;
                case 'H':
                case 'J':
                case 'L':
                case 'N':
                case 'Q':
                case 'R':
                    $dimension = 10;
                    break;
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
            $count++;
        }

        excelUtils::setWrapText($objPHPExcel, 'B'.$indexHeader.':R'.$indexHeader);
        excelUtils::setBold($objPHPExcel, 'B'.$indexHeader.':R'.$indexHeader);
        excelUtils::setBorderCell($objPHPExcel, 'B'.$indexHeader.':R'.$indexHeader, excelUtils::styleBorder());
        excelUtils::setRowHeight($objPHPExcel, $indexHeader, 30);
        excelUtils::fillBackGroundColor($objPHPExcel, 'B'.$indexHeader.':R'.$indexHeader, excelUtils::COLOR_DARK);

        foreach($arrParent as $pRow){
            excelUtils::setBorderCell($objPHPExcel, 'B'.$pRow.':R'.$pRow, excelUtils::styleBorder());
            excelUtils::fillBackGroundColor($objPHPExcel, 'B'.$pRow.':R'.$pRow, excelUtils::COLOR_YELLOWG);
        }

        excelUtils::setVertical($objPHPExcel, 'A1:R'.$lastRow, \PHPExcel_Style_Alignment::VERTICAL_CENTER);
        excelUtils::setHorizontal($objPHPExcel, 'B'.$indexHeader.':R'.$indexHeader, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        excelUtils::mergeCells($objPHPExcel, 'B2:R2');
        excelUtils::setBold($objPHPExcel, 'B2');
        excelUtils::setRowHeight($objPHPExcel, 2, 30);
        excelUtils::setHorizontal($objPHPExcel, 'B2', \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        excelUtils::setZoomSheet($objPHPExcel, 80);
        excelUtils::setFreezePane($objPHPExcel, 'F5');


        foreach($arrChild as $cRow){
            excelUtils::setBorderCell($objPHPExcel, 'B'.$cRow.':R'.$cRow, excelUtils::styleBorderChild());
            excelUtils::setHorizontal($objPHPExcel, 'B'.$cRow, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        excelUtils::setBorderCell($objPHPExcel, 'B'.$lastRow.':R'.$lastRow, excelUtils::styleBorderLasted());

        $fileName = 'DanhSachNhanVien';
        $this->outputFile($fileName, $objPHPExcel);

    }

    /**
     * Export danh muc phong ban
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function exportCompany()
    {
        $data = DB::table('company')->where('company.inactive', '=', 0)
            ->get();

        $data = commonUtils::objectToArray($data);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportCompany.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $baseRow = 4;

        $i = 1;
        $lastRow = count($data) + 1;
        foreach ($data as $row) {
            $j = $i + $baseRow;
            $lastRow = $j;

            $objPHPExcel->getActiveSheet()->setCellValue('B' . $j, $i++)
                ->setCellValue('C' . $j, $row['company_code'])
                ->setCellValue('D' . $j, $row['company_name'])
                ->setCellValue('E' . $j, $row['manager'])
                ->setCellValue('F' . $j, $row['phone']);
        }

        $objPHPExcel->getActiveSheet()->getStyle('B5:F' . $lastRow)->applyFromArray($styleArray);

        $fileName = 'DanhSachPhongBan';
        $this->outputFile($fileName, $objPHPExcel);

    }

    /**
     * Export danh muc khu vuc
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function exportArea()
    {
        $data = DB::table('area')
            ->select('area.id', 'area.area_code', 'area.area_name', 'company.company_name')
            ->leftJoin('company', 'area.company_id', '=', 'company.id')
            ->where('area.inactive', '=', 0)
            ->get();

        $data = commonUtils::objectToArray($data);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportArea.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $baseRow = 4;

        $i = 1;
        $lastRow = count($data) + 1;
        foreach ($data as $row) {
            $j = $i + $baseRow;
            $lastRow = $j;

            $objPHPExcel->getActiveSheet()
                ->setCellValue('B' . $j, $i++)
                ->setCellValue('C' . $j, $row['company_name'])
                ->setCellValue('D' . $j, $row['area_code'])
                ->setCellValue('E' . $j, $row['area_name']);
        }

        $objPHPExcel->getActiveSheet()->getStyle('B5:E' . $lastRow)->applyFromArray($styleArray);

        $fileName = 'DanhSachToQuanHuyen';
        $this->outputFile($fileName, $objPHPExcel);
    }

    /**
     * Export trong so cho cong ty
     * @param Request $request
     */
    public function exportTargetCorporation($goalId, $applyDate)
    {
        $sql = "
            SELECT ilc.*, g.goal_code, g.goal_name, g.parent_id, u.unit_code, u.unit_name
            FROM important_level_corporation ilc
            LEFT JOIN goal g on g.id = ilc.goal_id
            LEFT JOIN unit u on u.id = ilc.unit_id
            WHERE ilc.inactive = 0
            AND ilc.apply_date = '".$applyDate."'
        ";

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sql .= ' AND ilc.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sql .= ' AND ilc.goal_id = ' . $goalId;
            }
        }


        $importantLevelCorporation = DB::select(DB::raw($sql));

        if (count($importantLevelCorporation) == 0) {
            Session::flash('message-errors', 'Vui lòng import tỷ trọng công ty trước khi xuất báo cáo.');
            return redirect('managePriorityCorporation/' . $goalId . '/' . $applyDate );
        }

        #Define array


        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportPriorityCorporation.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);

        $formatApplyDate = substr($applyDate, 8,2).'/'.substr($applyDate, 5,2).'/'.substr($applyDate, 0,4);
        $year = substr($applyDate, 0,4);

        $objPHPExcel->getActiveSheet()->setCellValue('E4' , $year);
        $objPHPExcel->getActiveSheet()->setCellValue('E5' , $formatApplyDate);

        ##############################################################################################################
        #Set default goal to sheet

        $arrParent = array();
        $iP = 0;

        foreach($importantLevelCorporation as $importantLevel){
            if($importantLevel->parent_id == 0){
                $arrParent[$iP]['goalId']          = $importantLevel->goal_id;
                $arrParent[$iP]['goalName']        = $importantLevel->goal_name;
                $arrParent[$iP]['importantLevel']  = $importantLevel->important_level;
                $arrParent[$iP]['targetValue']     = $importantLevel->target_value;
                $arrParent[$iP]['benchmark']       = $importantLevel->benchmark;
                $arrParent[$iP]['implementPoint']  = $importantLevel->implement_point;
                $arrParent[$iP]['percentComplete'] = $importantLevel->percent_complete;
                $iP++;
            }
        }

        $beginData = 10;
        $arrPRowGoal = array();
        $arrRowGoal = array();
        $iRG = 0;
        $iPRG = 0;
        $i = 1;
        foreach ($arrParent as $prow) {
            #Parent
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $beginData . ':D' . $beginData);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $beginData.':'.'H' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $beginData, $prow['goalName'])
                ->setCellValue('E' . $beginData, $prow['importantLevel'])
                ->setCellValue('G' . $beginData, $this->formatNumber($prow['benchmark']))
                ->setCellValue('H' . $beginData, '')
                ->setCellValue('I' . $beginData, '')
            ;
            $arrPRowGoal[$iPRG]['numRow'] = $beginData;
            $arrPRowGoal[$iPRG]['goalId'] = $prow['goalId'];
            $iPRG++;
            $childRow = $beginData + 1;

            foreach ($importantLevelCorporation as $crow) {
                if ($crow->parent_id == $prow['goalId']) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    #Input array with row and goal id for loop of each user bellow
                    $arrRowGoal[$iRG]['numRow'] = $childRow;
                    $arrRowGoal[$iRG]['goalId'] = $crow->goal_id;

                    $iRG++;

                    $childPC = ($crow->percent_complete != 0) ? $this->formatNumber($crow->percent_complete*100).'%' : '-';
                    $childIP = ($crow->implement_point != 0) ? $this->formatNumber($crow->implement_point) : '-';
                    $childTV = ($crow->target_value != 0) ? $this->formatNumber($crow->target_value) : '-';

                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $childRow, $i++)
                        ->setCellValue('B' . $childRow, $crow->goal_code)
                        ->setCellValue('C' . $childRow, $crow->goal_name)
                        ->setCellValue('D' . $childRow, $crow->unit_name)
                        ->setCellValue('E' . $childRow, $crow->important_level)
                        ->setCellValue('F' . $childRow, $childTV)
                        ->setCellValue('G' . $childRow, $this->formatNumber($crow->benchmark))
                        ->setCellValue('H' . $childRow, $childIP)
                        ->setCellValue('I' . $childRow, $childPC)
                    ;
                    $childRow++;
                }
            }
            $beginData = $childRow;
        }
        $lastRow = $beginData - 1;


        $objPHPExcel->getActiveSheet()->getStyle('E10:I'.$lastRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        #Draw parent rows
        for ($iDP = 0; $iDP < count($arrPRowGoal); $iDP++) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrPRowGoal[$iDP]['numRow']. ':I' . $arrPRowGoal[$iDP]['numRow'])->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $arrPRowGoal[$iDP]['numRow']. ':I' . $arrPRowGoal[$iDP]['numRow'])
                ->getFill()
                ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('FFFF00');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrPRowGoal[$iDP]['numRow'] . ':I' . $arrPRowGoal[$iDP]['numRow'])->applyFromArray($styleArray);

        }
        $styleChildRows = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        #Draw child rows
        for ($iDC = 0; $iDC < count($arrRowGoal); $iDC++) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrRowGoal[$iDC]['numRow'] . ':I' . $arrRowGoal[$iDC]['numRow'])->applyFromArray($styleChildRows);
        }
        $styleLast = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        #Draw end row
        $objPHPExcel->getActiveSheet()->getStyle('A' . $lastRow . ':I' . $lastRow)->applyFromArray($styleLast);


        /*------------------------------------------------------------------------------------------------------------*/
        /* Các công thức tính toán:*/
        $rowCalculate = $lastRow + 3;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $rowCalculate, '* Các công thức tính toán:');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCalculate)->getFont()->setBold(true);
        $rowCalculate++;

        $arrFormulaGoalArea = commonUtils::arrFormulaGoalArea($rowCalculate, 0);

        foreach($arrFormulaGoalArea as $formula){
            $indexRow = $formula['id'];
            $nameFormula = $formula['name'];
            $description = $formula['description'];

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $indexRow, $nameFormula)
                ->setCellValue('C' . $indexRow, $description)
            ;
            $first = substr(trim($nameFormula),0,1);
            if($first == '-'){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $indexRow)->getFont()->setBold(true);
            }

        }
        $objPHPExcel->getActiveSheet()->getStyle('A4:I'.$indexRow)->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(10);
        /*------------------------------------------------------------------------------------------------------------*/

        #make a row for freeze panel
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);
        $objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(1);
        $objPHPExcel->getActiveSheet()->setTitle('QuanLyTyTrongCongtTy');
        $fileName = 'QuanLyTyTrongCongtTy';
        $this->outputFile($fileName, $objPHPExcel);
    }

    /**
     * Export trong so cho phong ban(cong ty)
     * @param Request $request
     */
    public function exportTargetCompany($companyId, $goalId, $applyDate)
    {

        $sqlGetApplyDateCor = "
            SELECT apply_date
            FROM important_level_corporation
            WHERE year(apply_date) = ".(int)substr($applyDate, 0, 4)."
            AND apply_date != ''
            ORDER BY apply_date DESC
            LIMIT 0,1
        ";
        $objApplyDateILCor = DB::select(DB::raw($sqlGetApplyDateCor));

        if(count($objApplyDateILCor) == 0){
            Session::flash('message-errors', 'Vui lòng import tỷ trọng công ty trước khi xuất báo cáo.');
            return redirect('managePriorityCompany/' . $companyId . '/' . $goalId . '/' . $applyDate );
        }

        $corApplyDate = commonUtils::objectToArray($objApplyDateILCor)[0]['apply_date'];

        // Corporation
        $sqlCorporation = "
            SELECT DISTINCT ilc.*, g.goal_code, g.goal_name, g.parent_id, u.unit_code, u.unit_name
            FROM important_level_corporation ilc
            LEFT JOIN goal g on g.id = ilc.goal_id
            LEFT JOIN unit u on u.id = ilc.unit_id
            WHERE ilc.inactive = 0
            AND ilc.apply_date = '".$corApplyDate."'
        ";
        $distinctILCs = DB::select(DB::raw($sqlCorporation));

        // Company
        $sql = "
            SELECT ilc.*, g.parent_id, c.company_code, c.company_name
            FROM important_level_company ilc
            LEFT JOIN company c on c.id = ilc.company_id
            LEFT JOIN goal g on g.id = ilc.goal_id
            WHERE ilc.inactive = 0
            AND ilc.apply_date = '".$applyDate."'
        ";

        if($companyId != 0){
            $sql .= " AND company_id = ".$companyId;
        }
        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sql .= ' AND ilc.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sql .= ' AND ilc.goal_id = ' . $goalId;
            }
        }

        $importantLevelCompanies = DB::select(DB::raw($sql));

        if (count($importantLevelCompanies) == 0) {
            Session::flash('message-errors', 'Vui lòng import tỷ trọng phòng ban trước khi xuất báo cáo.');
            return redirect('managePriorityCompany/' . $companyId . '/' . $goalId . '/' . $applyDate );
        }

        #Define array
        $arrParentId = array();
        $arrChildrenId = array();
        $arrParent = array();
        $arrChildren = array();
        $arrGoalId = array();
        $arrCompanyId= array();
        $arrImportantLevelCompany = array();

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportPriorityCompany.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);

        $formatApplyDate = substr($applyDate, 8,2).'/'.substr($applyDate, 5,2).'/'.substr($applyDate, 0,4);
        $year = substr($applyDate, 0,4);

        $objPHPExcel->getActiveSheet()->setCellValue('E4' , $year);
        $objPHPExcel->getActiveSheet()->setCellValue('E5' , $formatApplyDate);

        ##############################################################################################################
        #Set default goal to sheet
        $i = 1;
        $iILCA = 0;
        $iParent = 0;
        $iChild = 0;
        $totalImportantLevel = 0;
        $totalImportantLevelChild = 0;

        foreach ($distinctILCs as $dbILC) {
            if($dbILC->parent_id == 0){
                $totalImportantLevel += $dbILC->important_level;
            }else{
                $totalImportantLevelChild += $dbILC->important_level;
            }
        }

        foreach($importantLevelCompanies as $importantLevelCompany){
            if(!in_array($importantLevelCompany->company_id, $arrCompanyId)){
                $arrCompanyId[] = $importantLevelCompany->company_id;

                $arrImportantLevelCompany[$iILCA]['company_id']   = $importantLevelCompany->company_id;
                $arrImportantLevelCompany[$iILCA]['company_code'] = $importantLevelCompany->company_code;
                $arrImportantLevelCompany[$iILCA]['company_name'] = $importantLevelCompany->company_name;
                $iILCA++;
            }
        }

        $arrSumImportantLevel = array();
        $iSIL = 0;

        foreach ($distinctILCs as $ilcor) {

            if($ilcor->parent_id == 0){

                $totalImplementPoint = 0;
                $pilcorPC = 0;
                foreach($distinctILCs as $insideILCOr){
                    if($insideILCOr->parent_id == $ilcor->goal_id){
                        $totalImplementPoint += $insideILCOr->implement_point;
                        $pilcorPC += $insideILCOr->percent_complete;
                    }
                }

                $arrParent[$iParent]['goal_id']          =  $ilcor->goal_id;
                $arrParent[$iParent]['goal_name']        =  $ilcor->goal_name;
                $arrParent[$iParent]['important_level']  =  $ilcor->important_level;//Ty trong
                $arrParent[$iParent]['benchmark']        =  $ilcor->benchmark;//Diem chuan
                $arrParent[$iParent]['implement_point']  =  $totalImplementPoint;//Diem thuc hien
                $arrParent[$iParent]['percent_complete'] =  $pilcorPC * 100;//Ty le dat
                $iParent++;
            }else{
                $arrChildren[$iChild]['goal_id']            =  $ilcor->goal_id;
                $arrChildren[$iChild]['goal_code']          =  $ilcor->goal_code;
                $arrChildren[$iChild]['goal_name']          =  $ilcor->goal_name;
                $arrChildren[$iChild]['unit_name']          =  $ilcor->unit_name;
                $arrChildren[$iChild]['parent_id']          =  $ilcor->parent_id;
                $arrChildren[$iChild]['important_level']    =  $ilcor->important_level;
                $arrChildren[$iChild]['benchmark']          =  $ilcor->benchmark;
                $arrChildren[$iChild]['implement_point']    =  $ilcor->implement_point;
                $arrChildren[$iChild]['percent_complete']   =  $ilcor->percent_complete * 100;
                $iChild++;
            }
        }

        $beginData = 10;
        $arrPRowGoal = array();
        $arrRowGoal = array();
        $arrRowAll = array();
        $iRG = 0;
        $iPRG = 0;

        # Create section corporation from col A -> H
        foreach ($arrParent as $prow) {
            # Parent row
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $beginData . ':D' . $beginData);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $beginData.':'.'H' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $hpBenchmark        = ($prow['benchmark'] != 0) ? $this->formatNumber($prow['benchmark']) : '-';
            $hpImplementPoint   = ($prow['implement_point'] != 0) ?  $this->formatNumber($prow['implement_point']) : '-';
            $hpPercentComplete  = ($prow['percent_complete'] != 0) ?  $this->formatNumber($prow['percent_complete']).'%' : '-';

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $beginData, $prow['goal_name'])
                ->setCellValue('E' . $beginData, $prow['important_level'])
                ->setCellValue('F' . $beginData, $hpBenchmark)
                ->setCellValue('G' . $beginData, $hpImplementPoint)
                ->setCellValue('H' . $beginData, $hpPercentComplete)
            ;
            $arrPRowGoal[$iPRG]['numRow'] = $beginData;
            $arrPRowGoal[$iPRG]['goalId'] = $prow['goal_id'];
            $arrPRowGoal[$iPRG]['goalBenchmark'] = $prow['benchmark'];
            $iPRG++;
            $childRow = $beginData + 1;

            # Children row
            foreach ($arrChildren as $crow) {
                if ($crow['parent_id'] == $prow['goal_id']) {
                    #Set template
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $childRow.':'.'H' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    #Input array with row and goal id for loop of each user bellow
                    $arrRowGoal[$iRG]['numRow'] = $childRow;
                    $arrRowGoal[$iRG]['goalId'] = $crow['goal_id'];
                    $arrRowGoal[$iRG]['goalBenchmark'] = $crow['benchmark'];

                    $iRG++;

                    $hcBenchmark        = ($crow['benchmark'] != 0) ? $this->formatNumber($crow['benchmark']) : '-';
                    $hcImportantLevel   = ($crow['important_level'] != 0) ?  $crow['important_level'] : '-';
                    $hcImplementPoint   = ($crow['implement_point'] != 0) ?  $this->formatNumber($crow['implement_point']) : '-';
                    $hcPercentComplete  = ($crow['percent_complete'] != 0) ?  $this->formatNumber($crow['percent_complete']).'%' : '-';


                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $childRow, $i++)
                        ->setCellValue('B' . $childRow, $crow['goal_code'])
                        ->setCellValue('C' . $childRow, $crow['goal_name'])
                        ->setCellValue('D' . $childRow, $crow['unit_name'])
                        ->setCellValue('E' . $childRow, $hcImportantLevel)
                        ->setCellValue('F' . $childRow, $hcBenchmark)
                        ->setCellValue('G' . $childRow, $hcImplementPoint)
                        ->setCellValue('H' . $childRow, $hcPercentComplete)
                    ;
                    $childRow++;
                }
            }
            $beginData = $childRow;
        }

        $lastRow = $beginData - 1;
        #Merge 2 array rows

        $iMer = 0;
        foreach($arrPRowGoal as $a){
            $arrRowAll[$iMer]['numRow'] = $a['numRow'];
            $arrRowAll[$iMer]['goalId'] = $a['goalId'];
            $arrRowAll[$iMer]['goalBenchmark'] = $a['goalBenchmark'];
            $iMer++;
        }

        foreach($arrRowGoal as $b){
            $arrRowAll[$iMer]['numRow'] = $b['numRow'];
            $arrRowAll[$iMer]['goalId'] = $b['goalId'];
            $arrRowAll[$iMer]['goalBenchmark'] = $b['goalBenchmark'];
            $iMer++;
        }

        # Create dynamic company column

        $endLabelColumn = 'H';
        $startLoopColumn = 'I';

        $maxCol = 1;

        //commonUtils::pr($importantLevelCompanies); die;

        foreach ($arrImportantLevelCompany as $gilc) {
            if ($maxCol == 30) {
                break;
            }
            $maxCol++;
            $companyId = $gilc['company_id'];
            $arrOneILCs = array();
            $iInside = 0;

            foreach($importantLevelCompanies as $inside){
                if($inside->company_id == $companyId){
                    $arrOneILCs[$iInside]['id']                 = $inside->id;
                    $arrOneILCs[$iInside]['company_id']         = $inside->company_id;
                    $arrOneILCs[$iInside]['goal_id']            = $inside->goal_id;
                    $arrOneILCs[$iInside]['apply_date']         = $inside->apply_date;
                    $arrOneILCs[$iInside]['important_level']    = $inside->important_level;
                    $arrOneILCs[$iInside]['benchmark']          = $inside->benchmark;
                    $arrOneILCs[$iInside]['cal_benchmark']      = $inside->cal_benchmark;
                    $arrOneILCs[$iInside]['target_value']       = $inside->target_value;
                    $arrOneILCs[$iInside]['implement_point']    = $inside->implement_point;
                    $arrOneILCs[$iInside]['cal_implement_point']= $inside->cal_implement_point;
                    $arrOneILCs[$iInside]['unit_id']            = $inside->unit_id;
                    $arrOneILCs[$iInside]['goal_level']         = $inside->goal_level;
                    $arrOneILCs[$iInside]['inactive']           = $inside->inactive;
                    $arrOneILCs[$iInside]['lock']               = $inside->lock;
                    $arrOneILCs[$iInside]['real_percent']       = $inside->real_percent;
                    $arrOneILCs[$iInside]['parent_id']          = $inside->parent_id;
                    $iInside++;
                }
            }

            $endIndexColumn = \PHPExcel_Cell::columnIndexFromString($endLabelColumn);
            $uFirstLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn);
            $uLastLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn + 6);

            /**
             * $uLabelTA : Target Value
             * $uLabelIL : Important level
             * $uLabelBM : Benchmark
             * $uLabelPC : Percent complete
             * $uLabelIP : Implement point
             * $uLabelGV : Gather value
             * $uLabelCI : Calculate Implement Point
             */
            $column = $endIndexColumn;
            $uLabelTA = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIL = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelBM = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelGV = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelCI = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIP = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelPC = \PHPExcel_Cell::stringFromColumnIndex($column++);

            #Set width for column
            $hashColumn = array(
              1 => $uLabelTA
            , 2 => $uLabelIL
            , 3 => $uLabelBM
            , 4 => $uLabelGV
            , 5 => $uLabelCI
            , 6 => $uLabelIP
            , 7 => $uLabelPC
            );
            $count = 0;
            foreach ($hashColumn as $key => $value) {
                $dimension = 0;
                switch ($value) {
                    case $uLabelBM:
                        $dimension = 10;
                        break;
                    case $uLabelCI:
                    case $uLabelIP:
                    case $uLabelPC:
                        $dimension = 10;
                        break;
                    case $uLabelGV:
                    case $uLabelIL:
                        $dimension = 8;
                        break;
                    case $uLabelTA:
                        $dimension = 15;
                        break;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                $count++;
            }
            $objPHPExcel->getActiveSheet()->mergeCells($uFirstLabel  . '7:' . $uLastLabel . '7');

            #Set value title
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . '7', '['.$gilc['company_code'] . '] ' . $gilc['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . '8', 'Kế hoạch (Chỉ tiêu)');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelIL . '8', 'Tỷ trọng');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelBM . '8', 'Điểm chuẩn');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelGV . '8', 'Điểm chuẩn KPI');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelCI . '8', 'ĐTH KPI');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelPC . '8', 'Tỷ lệ đạt');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelIP . '8', 'Điểm thực hiện');

            foreach ($arrOneILCs as $dTC) {
                $tcTargetValue      = $dTC['target_value'];
                $tcIsParent         = $dTC['goal_level'];
                $tcBenchmark        = $dTC['benchmark'];
                $calBenchmark       = $dTC['cal_benchmark'];
                $tcImportantLevel   = $dTC['important_level'];
                $tcImplementPoint   = $dTC['implement_point'];
                $tcGoalId           = $dTC['goal_id'];
                $tcRealPercent      = $dTC['real_percent'];
                $tcCalImplementPoint= $dTC['cal_implement_point'];
                $tcParentId         = $dTC['parent_id'];

                $indexRow = 0;
                $parentCI = 0;/** parent calculate implement point */
                $parentPC = 0;/** parent percent complete */
                $parentPI = 0;/** parent implement point */

                if($tcParentId == 0){
                    foreach ($arrOneILCs as $idTC) {
                        if($idTC['parent_id'] == $tcGoalId){
                            $parentCI += $idTC['cal_implement_point'];
                            $parentPI += $idTC['implement_point'];
                        }
                    }
                    $parentPC = ($tcBenchmark != 0) ? $parentPI / $tcBenchmark : 0;
                }

                foreach($arrRowAll as $aRA){
                    if($tcGoalId == $aRA['goalId']){
                        $indexRow = $aRA['numRow'];
                    }
                }



                $tcTargetValue          = ($tcTargetValue != 0 && $tcIsParent != 0) ? $this->formatNumber($tcTargetValue) : "-";
                $tcRealPercent          = ($tcRealPercent != 0) ? $this->formatNumber($tcRealPercent * 100).'%' : "-";
                $parentPC               = ($parentPC != 0) ? $this->formatNumber($parentPC * 100).'%' : "-";
                $tcCalImplementPoint    = ($tcCalImplementPoint != 0) ? $this->formatNumber($tcCalImplementPoint) : "-";
                $tcImplementPoint       = ($tcImplementPoint != 0) ? $this->formatNumber($tcImplementPoint) : "-";
                $calBenchmark           = ($calBenchmark != 0) ? $this->formatNumber($calBenchmark) : "-";
                $tcBenchmark            = ($tcBenchmark != 0) ? $this->formatNumber($tcBenchmark) : "-";
                $parentCI               = ($parentCI != 0) ? $this->formatNumber($parentCI) : "-";
                $parentPI               = ($parentPI != 0) ? $this->formatNumber($parentPI) : "-";

                if($tcParentId == 0){
                    $tcCalImplementPoint = $parentCI;
                    $tcRealPercent       = $parentPC;
                    $tcImplementPoint    = $parentPI;
                }

                $objPHPExcel->getActiveSheet()
                    ->setCellValue($uLabelTA . $indexRow, $tcTargetValue)
                    ->setCellValue($uLabelIL . $indexRow, $tcImportantLevel)
                    ->setCellValue($uLabelBM . $indexRow, $tcBenchmark)
                    ->setCellValue($uLabelGV . $indexRow, $calBenchmark)
                    ->setCellValue($uLabelCI . $indexRow, $tcCalImplementPoint)
                    ->setCellValue($uLabelIP . $indexRow, $tcImplementPoint)
                    ->setCellValue($uLabelPC . $indexRow, $tcRealPercent);

                $objPHPExcel->getActiveSheet()->getStyle($uLabelTA.$indexRow.':'.$uLabelPC.$indexRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            }
            $endLabelColumn = $uLastLabel;
        }


        ##############################################################################################################

        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'8:'.$endLabelColumn.'8')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');


        #Draw parent rows
        for ($iDP = 0; $iDP < count($arrPRowGoal); $iDP++) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrPRowGoal[$iDP]['numRow']. ':' . $endLabelColumn . $arrPRowGoal[$iDP]['numRow'])->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $arrPRowGoal[$iDP]['numRow']. ':' . $endLabelColumn . $arrPRowGoal[$iDP]['numRow'])
                ->getFill()
                ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB(excelUtils::COLOR_GREEN);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrPRowGoal[$iDP]['numRow'] . ':' . $endLabelColumn . $arrPRowGoal[$iDP]['numRow'])->applyFromArray($styleArray);
        }

        #Draw child rows
        for ($iDC = 0; $iDC < count($arrRowGoal); $iDC++) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrRowGoal[$iDC]['numRow'] . ':' . $endLabelColumn . $arrRowGoal[$iDC]['numRow'])->applyFromArray(excelUtils::styleBorderChild());
        }

        $objPHPExcel->getActiveSheet()->getStyle('A' . $lastRow . ':' . $endLabelColumn . $lastRow)->applyFromArray(excelUtils::styleBorderLasted());

        /*------------------------------------------------------------------------------------------------------------*/
        /* Các công thức tính toán:*/
        $rowCalculate = $lastRow + 3;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $rowCalculate, '* Các công thức tính toán:');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCalculate)->getFont()->setBold(true);
        $rowCalculate++;

        $arrFormulaGoalArea = commonUtils::arrFormulaGoalArea($rowCalculate, 1);

        $i = $rowCalculate;
        foreach($arrFormulaGoalArea as $formula){
            $i++;
            $indexRow = $i;
            $nameFormula = $formula['name'];
            $description = $formula['description'];

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $indexRow, $nameFormula)
                ->setCellValue('C' . $indexRow, $description);
            $first = substr(trim($nameFormula),0,1);
            if($first == '-'){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $indexRow)->getFont()->setBold(true);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('A4:'.$endLabelColumn.$indexRow)->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(10);
        /*------------------------------------------------------------------------------------------------------------*/

        #make a row for freeze panel
        $objPHPExcel->getActiveSheet()->freezePane('I9');
        $objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(1);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);
        $objPHPExcel->getActiveSheet()->setTitle('QuanLyTyTrongPhongDaiMBFHCM');

        $fileName = 'QuanLyTyTrongPhongDaiMBFHCM';
        $this->outputFile($fileName, $objPHPExcel);
    }

    /**
     * Export trong so cho chức danh
     * @param Request $request
     */
    public  function exportPriorityPosition($companyId, $areaId, $positionId, $goalId, $year, $month)
    {
        /*------------------------------check data exists before export file excel----------------------------------------*/
        if ($companyId == 0) {
            Session::flash('message-errors', 'Vui lòng chọn Phòng/Đài/MBF HCM trước khi xuất báo cáo.');
            return redirect('managePriorityPosition/' . $companyId . '/' . $areaId . '/' . $positionId . '/' . $goalId . '/' . $year . '/' . $month);
        }

        if ($areaId == 0) {
            Session::flash('message-errors', 'Vui lòng chọn Tổ/Quận/Huyện trước khi xuất báo cáo.');
            return redirect('managePriorityPosition/' . $companyId . '/' . $areaId . '/' . $positionId . '/' . $goalId . '/' . $year . '/' . $month);
        }

        $company = DB::table('company')->where('id', $companyId)->where('inactive', 0)->first();
        $area = DB::table('area')->where('id', $areaId)->where('inactive', 0)->first();

        //get data of area-company ---> show total
        $sqlPriorityArea = "
            SELECT ila.*, g.goal_code, g.goal_name, g.parent_id
            FROM important_level_area ila
            LEFT JOIN goal g on ila.goal_id = g.id
            WHERE ila.inactive =0
            AND ila.company_id = '" . $companyId . "'
            AND ila.area_id = '" . $areaId . "'
            AND ila.month = '" . $month . "'
            AND ila.year = '" . $year . "'
            ";
        $objImportantLevelAreaDB = DB::select(DB::raw($sqlPriorityArea));

        if (count($objImportantLevelAreaDB) == 0) {
            Session::flash('message-errors', 'Vui lòng import tỷ trọng cho Tổ/Quận/Huyện: <b>' . $area->area_name . '</b>
                                                thuộc phòng ban: <b>' . $company->company_name . '</b>
                                                trước khi xuất báo cáo tỷ trọng chức danh.');
            return redirect('managePriorityPosition/' . $companyId . '/' . $areaId . '/' . $positionId . '/' . $goalId . '/' . $year . '/' . $month);
        }


        /* *************************************************************************************************************
         * Get target value
         * *************************************************************************************************************/
        $sqlTA = "
            SELECT ta.*, u.unit_name
                    FROM  target_area AS ta
                    LEFT JOIN goal ON goal.id = ta.goal_id
                    LEFT JOIN area ON area.id = ta.area_id
                    LEFT JOIN unit u on ta.unit_id = u.id
                    WHERE ta.inactive = 0
                    AND ta.company_id = ".$companyId."
                    AND ta.area_id = ".$areaId."
                    AND ta.year = ".$year."
                    AND ta.month = ".$month."
        ";

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlTA .= ' AND ta.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlTA .= ' AND ta.goal_id = ' . $goalId;
            }
        }

        $objTargetAreaDB = DB::select(DB::raw($sqlTA));

        /* *************************************************************************************************************
         * Get object important level position
         * *************************************************************************************************************/
        $sql = 'SELECT ilp.*, goal.id, goal.goal_code, goal.parent_id, goal.goal_name, area.area_code,
                area.area_name, position.position_name, position.position_code
                FROM  important_level_position AS ilp
                JOIN goal ON goal.id = ilp.goal_id
                JOIN position ON position.id = ilp.position_id
                JOIN area ON area.id = ilp.area_id
                WHERE ilp.inactive = 0
                AND ilp.company_id = ' . $companyId . '
                AND ilp.area_id = ' . $areaId . '
                AND ilp.month = ' . $month . '
                AND ilp.year = ' . $year . '
                ';
        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sql .= ' AND ilp.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sql .= ' AND ilp.goal_id = ' . $goalId;
            }
        }

        if ($positionId != 0) {
            $sql .= ' AND ilp.position_id  = ' . $positionId;
        }

        $objImportantLevelPositionDB = DB::select(DB::raw($sql));

        if (count($objImportantLevelPositionDB) == 0) {
            Session::flash('message-errors', 'Vui lòng import tỷ trọng cho chức danh trước khi xuất báo cáo.');
            return redirect('managePriorityPosition/' . $companyId . '/' . $areaId . '/' .
                $positionId . '/' . $goalId . '/' . $year . '/' . $month);
        }

        /*------------------------------------------export excel: goal, index, goal_code------------------------------*/
        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportPosition.xls");
        $objPHPExcel->setActiveSheetIndex(0);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $objPHPExcel->getActiveSheet()
            ->setCellValue('E' . 6, $company->company_name)
            ->setCellValue('E' . 7, $area->area_name)
            ->setCellValue('E' . 4, $year)
            ->setCellValue('E' . 5, $month);

        /* *************************************************************************************************************
         * Get array distinct position
         * ************************************************************************************************************/
        $arrDistinctPosition = array();
        $iDP = 0;

        foreach($objImportantLevelPositionDB as $ilp){
            if(count($arrDistinctPosition) == 0){

                $arrDistinctPosition[$iDP]['positionId']    = $ilp->position_id;
                $arrDistinctPosition[$iDP]['positionCode']  = $ilp->position_code;
                $arrDistinctPosition[$iDP]['positionName']  = $ilp->position_name;
                $iDP++;

            }else{
                $exist = 0;
                foreach($arrDistinctPosition as $dpPosition){
                    if($dpPosition['positionId'] == $ilp->position_id){
                        $exist = 1;
                        break;
                    }
                }

                if($exist == 0){
                    $arrDistinctPosition[$iDP]['positionId']    = $ilp->position_id;
                    $arrDistinctPosition[$iDP]['positionCode']  = $ilp->position_code;
                    $arrDistinctPosition[$iDP]['positionName']  = $ilp->position_name;
                    $iDP++;
                }
            }
        }

        /* *************************************************************************************************************
         * Get array data parent and child
         * ************************************************************************************************************/
        $arrParent = array();
        $arrChildren = array();

        $iCG = 0;
        $iPG = 0;

        foreach ($objImportantLevelAreaDB as $ila) {

            $ilaGoalName        = ($ila->goal_name != '') ? $ila->goal_name : '-';
            $ilaGoalCode        = ($ila->goal_code != '') ? $ila->goal_code : '-';
            $ilaGoalId          = ($ila->goal_id != 0) ? $ila->goal_id : '-';
            $ilaImportantLevel  = ($ila->important_level != 0) ? $ila->important_level : '-';
            $ilaBenchmark       = ($ila->benchmark != 0) ? $this->formatNumber($ila->benchmark) : '-';
            $ilaCalBenchmark    = ($ila->cal_benchmark != 0) ? $this->formatNumber($ila->cal_benchmark) : '-';

            $ilaTargetValue = 0;
            $ilaUnitName    = '-';
            foreach($objTargetAreaDB as $targetArea){
                if($targetArea->goal_id == $ilaGoalId){
                    $ilaTargetValue = $targetArea->target_value;
                    $ilaUnitName    = $targetArea->unit_name;
                    break;
                }
            }

            $ilaTargetValue       = ($ilaTargetValue != 0) ? $this->formatNumber($ilaTargetValue) : '-';

            if($ila->parent_id == 0){

                #Array for parent
                $arrParent[$iPG]['goalId']             = $ilaGoalId;
                $arrParent[$iPG]['goalCode']           = $ilaGoalCode;
                $arrParent[$iPG]['goalName']           = $ilaGoalName;
                $arrParent[$iPG]['importantLevel']     = $ilaImportantLevel;
                $arrParent[$iPG]['benchmark']          = $ilaBenchmark;
                $arrParent[$iPG]['calBenchmark']       = $ilaCalBenchmark;
                $arrParent[$iPG]['targetValue']        = $ilaTargetValue;
                $iPG++;

            }else{
                #Array for child
                $arrChildren[$iCG]['goalId']           = $ilaGoalId;
                $arrChildren[$iCG]['goalCode']         = $ilaGoalCode;
                $arrChildren[$iCG]['goalName']         = $ilaGoalName;
                $arrChildren[$iCG]['goalParent']       = $ila->parent_id;
                $arrChildren[$iCG]['unitName']         = $ilaUnitName;
                $arrChildren[$iCG]['targetValue']      = $ilaTargetValue;
                $arrChildren[$iCG]['importantLevel']   = $ilaImportantLevel;
                $arrChildren[$iCG]['benchmark']        = $ilaBenchmark;
                $arrChildren[$iCG]['calBenchmark']     = $ilaCalBenchmark;
                $arrChildren[$iPG]['targetValue']      = $ilaTargetValue;
                $iCG++;
            }
        }
         /* ***********************************************************************************************************/

        $beginData = 11;
        $index = 0;
        $arrGoalIndex = array();
        // export excel file root

        $arrPRow = array();
        $arrCRow = array();

        foreach ($arrParent as $prow) {
            #Parent
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $beginData . ':E' . $beginData);
            $objPHPExcel->getActiveSheet()->getRowDimension($beginData)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $beginData . ':H' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $beginData, $prow['goalName'])
                ->setCellValue('F' . $beginData, $prow['importantLevel'])
                ->setCellValue('G' . $beginData, $prow['benchmark'])
                ->setCellValue('H' . $beginData, $prow['calBenchmark'])
            ;

            $arrPRow[] = $beginData;

            $arrGoalIndex[$index]['goalId'] = $prow['goalId'];
            $arrGoalIndex[$index]['index'] = $beginData;
            $index++;
            $childRow = $beginData + 1;
            $i = 1;

            foreach ($arrChildren as $crow) {
                if ($crow['goalParent'] == $prow['goalId']) {
                    #Set template
                    //$objPHPExcel->getActiveSheet()->getStyle('A' . $childRow . ':H' . $childRow)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('E' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    #Input array with row and goal id for loop of each user bellow
                    $arrCRow[] = $childRow;

                    $arrGoalIndex[$index]['goalId'] = $crow['goalId'];
                    $arrGoalIndex[$index]['index'] = $childRow;
                    $index++;



                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $childRow, $i++)
                        ->setCellValue('B' . $childRow, $crow['goalCode'])
                        ->setCellValue('C' . $childRow, $crow['goalName'])
                        ->setCellValue('D' . $childRow, $crow['targetValue'])
                        ->setCellValue('E' . $childRow, $crow['unitName'])
                        ->setCellValue('F' . $childRow, $crow['importantLevel'])
                        ->setCellValue('G' . $childRow, $crow['benchmark'])
                        ->setCellValue('H' . $childRow, $crow['calBenchmark'])
                    ;
                    $childRow++;
                }
            }
            $beginData = $childRow;
        }

        $lastRow = $beginData - 1;

        $objPHPExcel->getActiveSheet()->setTitle('QuanLyTyTrongChoChucDanh');

        /*-------------------------export excel: data for multi position-----------------------*/
        // get data from target_position
        $sqlTargetPosition = '
                SELECT tp.*, unit.unit_code
                FROM target_position as tp
                LEFT JOIN unit ON unit.id = tp.unit_id
                WHERE tp.inactive = 0
                AND tp.company_id = '.$companyId.'
                AND tp.area_id = '.$areaId.'
                AND tp.year = '.$year.'
                AND tp.month = '.$month
        ;

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlTargetPosition .= ' AND tp.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlTargetPosition .= ' AND tp.goal_id = ' . $goalId;
            }
        }

        if ($positionId != 0) {
            $sqlTargetPosition .= ' AND tp.position_id  = ' . $positionId;
        }

        $objTargetPositionDB = DB::select(DB::raw($sqlTargetPosition));

        $endLabelColumn = 'H';
        $startLoopColumn = 'I';
        $headerForILA = 9;

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $sqlTargetPosition = "
            SELECT tp.*
            FROM target_position tp
            WHERE tp.inactive = 0
        ";
        $objTargetPosition = DB::select(DB::raw($sqlTargetPosition));

        foreach($arrDistinctPosition as $dpPosition){

            $dpPositionId   = $dpPosition['positionId'];
            $dpPositionCode = $dpPosition['positionCode'];
            $dpPositionName = $dpPosition['positionName'];

            $endIndexColumn = \PHPExcel_Cell::columnIndexFromString($endLabelColumn);
            $uFirstLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn);
            $uLastLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn + 3);

            /**
             * $uLabelTA  : Target Value
             * $uLabelIL  : Important level
             * $uLabelBM  : Benchmark
             * $uLabelCBM : Calculator benchmark
             */
            $column = $endIndexColumn;
            $uLabelTA = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIL = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelBM = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelCBM = \PHPExcel_Cell::stringFromColumnIndex($column++);

            #Set width for column
            $hashColumn = array(1 => $uLabelTA
            , 2 => $uLabelIL
            , 3 => $uLabelBM
            , 4 => $uLabelCBM
            );
            $count = 0;
            foreach ($hashColumn as $key => $value) {
                $dimension = 0;
                switch ($value) {
                    case $uLabelBM:
                    case $uLabelCBM:
                        $dimension = 10;
                        break;
                    case $uLabelIL:
                        $dimension = 7;
                        break;
                    case $uLabelTA:
                        $dimension = 15;
                        break;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                $count++;
            }
            $uFLLabel = $uFirstLabel . $headerForILA . ':' . $uLastLabel . ($headerForILA + 1);
            $objPHPExcel->getActiveSheet()->getStyle($uFLLabel)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->mergeCells($uFirstLabel . $headerForILA . ':' . $uLastLabel . $headerForILA);

            #Set value title
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . $headerForILA, '['.$dpPositionCode . '] ' . $dpPositionName);
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . ($headerForILA + 1), 'Kế hoạch (Chỉ tiêu)');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelIL . ($headerForILA + 1), 'Tỷ trọng');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelBM . ($headerForILA + 1), 'Điểm chuẩn');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelCBM . ($headerForILA + 1), 'Điểm chuẩn KPI');


            /* *********************************************************************************************************
             * Put to array One ILP
             * ********************************************************************************************************/

            $arrOneILP = array();
            $iILP = 0;

            foreach($objImportantLevelPositionDB as $ilp){
                if($ilp->position_id == $dpPositionId){

                    $ilpGoalId          = $ilp->goal_id;
                    $ilpImportantLevel  = $ilp->important_level;
                    $ilpBenchmark       = $ilp->benchmark;
                    $ilpCalBenchmark    = $ilp->cal_benchmark;

                    $ilpTargetValue = 0;
                    if(count($objTargetPositionDB) > 0){
                        foreach($objTargetPositionDB as $targetPosition){
                            if(
                                $targetPosition->position_id == $dpPositionId
                                && $targetPosition->goal_id == $ilpGoalId
                            ){
                                $ilpTargetValue = $targetPosition->target_value;
                                break;
                            }
                        }
                    }

                    $arrOneILP[$iILP]['targetValue']    = $ilpTargetValue;
                    $arrOneILP[$iILP]['importantLevel'] = $ilpImportantLevel;
                    $arrOneILP[$iILP]['benchmark']      = $ilpBenchmark;
                    $arrOneILP[$iILP]['calBenchmark']   = $ilpCalBenchmark;
                    $arrOneILP[$iILP]['goalId']         = $ilpGoalId;
                    $iILP++;

                }



                foreach($arrOneILP as $oneILP){

                    $oiGoalId           = $oneILP['goalId'];
                    $oiImportantLevel   = ($oneILP['importantLevel'] != 0) ? $oneILP['importantLevel'] : '-';
                    $oiBenchmark        = ($oneILP['benchmark'] != 0) ? $this->formatNumber($oneILP['benchmark']) : '-';
                    $oiCalBenchmark     = ($oneILP['calBenchmark'] != 0) ? $this->formatNumber($oneILP['calBenchmark']) : '-';
                    $oiTargetValue      = ($oneILP['targetValue'] != 0) ? $this->formatNumber($oneILP['targetValue']) : '-';

                    foreach($arrGoalIndex as $rowIndex){
                        if($oiGoalId == $rowIndex['goalId']){
                            $objPHPExcel->getActiveSheet()
                                ->setCellValue($uLabelTA . $rowIndex['index'], $oiTargetValue)
                                ->setCellValue($uLabelIL . $rowIndex['index'], $oiImportantLevel)
                                ->setCellValue($uLabelBM . $rowIndex['index'], $oiBenchmark)
                                ->setCellValue($uLabelCBM. $rowIndex['index'], $oiCalBenchmark)
                            ;
                            $objPHPExcel->getActiveSheet()->getStyle($uLabelTA . $rowIndex['index'])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        }
                    }

                }
            }


            $endLabelColumn = $uLastLabel;
        }
        /* *************************************************************************************************************
         * Draw template
         * ************************************************************************************************************/
        $labelHeader = 'A9:'.$endLabelColumn.'10';
        $objPHPExcel->getActiveSheet()->getStyle($labelHeader)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle($labelHeader)->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(28);
        $objPHPExcel->getActiveSheet()->getRowDimension(10)->setRowHeight(45);
        $objPHPExcel->getActiveSheet()->getStyle($labelHeader)->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle($labelHeader)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB(commonUtils::COLOR_DARK);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $endLabelColumn . $lastRow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        foreach($arrPRow as $pRow){
            $objPHPExcel->getActiveSheet()->getStyle('A' . $pRow . ':' . $endLabelColumn . $pRow)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $pRow . ':' . $endLabelColumn . $pRow)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB(commonUtils::COLOR_GOAL);
        }

        #Draw child rows
        $styleChildRows = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        foreach($arrCRow as $cRow){
            $objPHPExcel->getActiveSheet()->getStyle('A' . $cRow . ':' . $endLabelColumn . $cRow)->applyFromArray($styleChildRows);
        }

        $styleLast = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $lastRow . ':' . $endLabelColumn . $lastRow)->applyFromArray($styleLast);
        /*------------------------------------------------------------------------------------------------------------*/
        /* Các công thức tính toán:*/
        $rowCalculate = $lastRow + 3;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $rowCalculate, '* Các công thức tính toán:');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCalculate)->getFont()->setBold(true);
        $rowCalculate++;

        $arrFormulaGoalArea = commonUtils::arrFormulaGoalArea($rowCalculate, 3);
        $i = $rowCalculate;
        foreach($arrFormulaGoalArea as $formula){
            $i++;
            $indexRow = $i;
            $nameFormula = $formula['name'];
            $description = $formula['description'];

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $indexRow, $nameFormula)
                ->setCellValue('C' . $indexRow, $description);
            $first = substr(trim($nameFormula),0,1);
            if($first == '-'){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $indexRow)->getFont()->setBold(true);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('A4:'.$endLabelColumn.$indexRow)->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(10);
        /*--------------------------------------------xuất file excel-------------------------------------------------*/
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);
        $fileName = 'QuanLyTyTrongChoChucDanh';
        $this->outputFile($fileName, $objPHPExcel);
    }

    /**
     * Export trong so cho khu vuc(quan)
     * @param Request $request
     */
    public function exportTargetArea($companyId, $areaId, $goalId, $year, $month)
    {
        if ($companyId == 0
        ) {
            Session::flash('message-errors', 'Vui lòng chọn phòng ban trước khi xuất báo cáo.');
            return redirect('managePriorityArea/' . $companyId . '/' . $areaId . '/' . $goalId . '/' . $year . '/' . $month);
        }

        $company = DB::table('company')->where('id', $companyId)->where('inactive', 0)->first();

        /*------------------------------------------------------------------------------------------------------------*/
        $sqlGetApplyDate = "
            SELECT apply_date
            FROM important_level_company
            WHERE inactive = 0
            AND company_id = ".$companyId."
            AND year(apply_date) = ".$year."
            ORDER BY apply_date DESC
            LIMIT 0,1
        ";
        $objApplyDate = DB::select(DB::raw($sqlGetApplyDate));

        if (count($objApplyDate) == 0) {
            Session::flash('message-errors', 'Vui lòng import tỷ trọng cho Phòng/Đài/MBF HCM: <b>'.$company->company_name.'</b> trước khi xuất báo cáo tỷ trọng khu vực.');
            return redirect('managePriorityArea/' . $companyId . '/' . $areaId . '/' . $goalId . '/' . $year . '/' . $month);
        }

        $ilcApplyDate = commonUtils::objectToArray($objApplyDate)[0]['apply_date'];
        /*------------------------------------------------------------------------------------------------------------*/


        $sqlILCs = "
            SELECT ilc.goal_id, ilc.*, g.goal_code, g.goal_name, g.parent_id, u.unit_code, u.unit_name
            FROM important_level_company ilc
            LEFT JOIN goal g on ilc.goal_id = g.id
            LEFT JOIN unit u on ilc.unit_id = u.id
            WHERE ilc.inactive = 0
            AND ilc.company_id = '".$companyId."'
            AND ilc.apply_date = '".$ilcApplyDate."'
        ";
        $importantLevelCompanies = DB::select(DB::raw($sqlILCs));

        #define arrays
        $arrParentId = array();
        $arrChildrenId = array();
        $arrGoalId = array();


        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportPriorityArea.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->getRowDimension(12)->setRowHeight(15);

        $objPHPExcel->getActiveSheet()->setCellValue('E4', $year);
        $objPHPExcel->getActiveSheet()->setCellValue('E5', $month);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );

        $beginHeader = 9;
        $headerForILA = 7;
        #make a row for freeze panel
        $objPHPExcel->getActiveSheet()->getRowDimension($beginHeader)->setRowHeight(1);
        $objPHPExcel->getActiveSheet()->freezePane('F' . $beginHeader);
        ##############################################################################################################
        #Set default goal to sheet
        $i = 1;


        /* *************************************************************************************************************
         * Get arrParent and arrChild
         * ************************************************************************************************************/
        $arrParent   = array();
        $arrChildren = array();

        $iCG = 0;
        $iPG = 0;

        foreach ($importantLevelCompanies as $ilc) {

            $ilcTargetValue     = ($ilc->real_percent != 0) ? $this->formatNumber($ilc->target_value) : '-';
            $ilcUnitName        = ($ilc->unit_name != '' && $ilc->unit_id != 0) ? $ilc->unit_name : '-';
            $ilcGoalName        = ($ilc->goal_name != '') ? $ilc->goal_name : '-';
            $ilcGoalCode        = ($ilc->goal_code != '') ? $ilc->goal_code : '-';
            $ilcGoalId          = ($ilc->goal_id != 0) ? $ilc->goal_id : '-';
            $ilcImportantLevel  = ($ilc->important_level != 0) ? $ilc->important_level : '-';
            $ilcBenchmark       = ($ilc->benchmark != 0) ? $this->formatNumber($ilc->benchmark) : '-';
            $ilcCalBenchmark    = ($ilc->cal_benchmark != 0) ? $this->formatNumber($ilc->cal_benchmark) : '-';
            $ilcImplementPoint  = ($ilc->implement_point != 0) ? $this->formatNumber($ilc->implement_point) : '-';
            $ilcRealPercent     = ($ilc->real_percent != 0) ? $this->formatNumber($ilc->real_percent * 100).'%' : '-';

            if($ilc->parent_id == 0){

                /*$parentIP = 0;

                foreach ($importantLevelCompanies as $insideILC) {
                    if($insideILC->parent_id == $ilcGoalId){
                        $parentIP += $insideILC->implement_point;
                    }
                }

                $parentPC  = ($ilcBenchmark != 0) ? $this->formatNumber(($parentIP /  $ilcBenchmark )* 100).'%' : '-';
                $parentIP  = ($parentIP != 0) ? $this->formatNumber($parentIP) : '-';*/

                #Array for parent
                $arrParent[$iPG]['goalId']             = $ilcGoalId;
                $arrParent[$iPG]['goalCode']           = $ilcGoalCode;
                $arrParent[$iPG]['goalName']           = $ilcGoalName;
                $arrParent[$iPG]['targetValue']        = $ilcTargetValue;
                $arrParent[$iPG]['importantLevel']     = $ilcImportantLevel;
                $arrParent[$iPG]['benchmark']          = $ilcBenchmark;
                $arrParent[$iPG]['calBenchmark']       = $ilcCalBenchmark;
                //$arrParent[$iPG]['implementPoint']     = $parentIP;
                //$arrParent[$iPG]['percentComplete']    = $parentPC;
                $iPG++;

            }else{
                #Array for child
                $arrChildren[$iCG]['goalId']           = $ilcGoalId;
                $arrChildren[$iCG]['goalCode']         = $ilcGoalCode;
                $arrChildren[$iCG]['goalName']         = $ilcGoalName;
                $arrChildren[$iCG]['goalParent']       = $ilc->parent_id;
                $arrChildren[$iCG]['unitName']         = $ilcUnitName;
                $arrChildren[$iCG]['targetValue']      = $ilcTargetValue;
                $arrChildren[$iCG]['importantLevel']   = $ilcImportantLevel;
                $arrChildren[$iCG]['benchmark']        = $ilcBenchmark;
                $arrChildren[$iCG]['calBenchmark']     = $ilcCalBenchmark;
                $arrChildren[$iCG]['implementPoint']   = $ilcImplementPoint;
                $arrChildren[$iCG]['percentComplete']  = $ilcRealPercent;
                $iCG++;
            }
        }
        /***************************************************************************************************************/


        $beginData = 10;
        $arrPRowGoal = array();
        $arrRowGoal = array();
        $iRG = 0;
        $iPRG = 0;

        foreach ($arrParent as $prow) {
            #Parent row
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $beginData . ':E' . $beginData);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $beginData . ':H' . $beginData)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $beginData.':H' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $beginData . ':H' . $beginData)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $beginData, $prow['goalName'])
                ->setCellValue('F' . $beginData, $prow['importantLevel'])
                ->setCellValue('G' . $beginData, $prow['benchmark'])
                ->setCellValue('H' . $beginData, $prow['calBenchmark'])
                //->setCellValue('I' . $beginData, $prow['implementPoint'])
                //->setCellValue('J' . $beginData, $prow['percentComplete'])
                 ;
            $arrPRowGoal[$iPRG]['numRow'] = $beginData;
            $arrPRowGoal[$iPRG]['goalId'] = $prow['goalId'];
            $iPRG++;
            $childRow = $beginData + 1;
            foreach ($arrChildren as $crow) {
                if ($crow['goalParent'] == $prow['goalId']) {

                    $objPHPExcel->getActiveSheet()->getStyle('D' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    #Input array with row and goal id for loop of each user bellow
                    $arrRowGoal[$iRG]['numRow'] = $childRow;
                    $arrRowGoal[$iRG]['goalId'] = $crow['goalId'];
                    $iRG++;

                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $childRow, $i++)
                        ->setCellValue('B' . $childRow, $crow['goalCode'])
                        ->setCellValue('C' . $childRow, $crow['goalName'])
                        ->setCellValue('D' . $childRow, $crow['targetValue'])
                        ->setCellValue('E' . $childRow, $crow['unitName'])
                        ->setCellValue('F' . $childRow, $crow['importantLevel'])
                        ->setCellValue('G' . $childRow, $crow['benchmark'])
                        ->setCellValue('H' . $childRow, $crow['calBenchmark'])
                        //->setCellValue('I' . $childRow, $crow['implementPoint'])
                        //->setCellValue('J' . $childRow, $crow['percentComplete'])
                    ;
                    $childRow++;
                }
            }
            $beginData = $childRow;
        }
        $lastRow = $beginData - 1;
        $objPHPExcel->getActiveSheet()->freezePane('I9');

        ##############################################################################################################
        #Begin create dynamic area section
        $endLabelColumn = 'H';
        $startLoopColumn = 'I';

        $maxCol = 1;

        $sql = 'SELECT
                            ila.*
                            , goal.id
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , area.area_code
                            , area.area_name
                        FROM  important_level_area AS ila
                        LEFT JOIN goal ON goal.id = ila.goal_id
                        LEFT JOIN area ON area.id = ila.area_id
                        WHERE ila.inactive = 0
                        AND ila.company_id = '.$companyId.'
                    ';
        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sql .= ' AND ila.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $isParent = 0;
                $sql .= ' AND ila.goal_id = ' . $goalId;
            }
        }

        if ($areaId != 0) {
            $sql .= ' AND ila.area_id  = ' . $areaId;
        }

        if ($year != 0) {
            $sql .= ' AND ila.year = ' . $year;
        }

        if ($month != 0) {
            $sql .= ' AND ila.month = ' . $month;
        }

        $objImportantLevelAreaDB = DB::select(DB::raw($sql));

        /* *************************************************************************************************************
         * Get target value
         * *************************************************************************************************************/
        $sqlTA = "
            SELECT
                            ta.*
                            , goal.id
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , area.area_code
                            , area.area_name
                        FROM  target_area AS ta
                        LEFT JOIN goal ON goal.id = ta.goal_id
                        LEFT JOIN area ON area.id = ta.area_id
                        WHERE ta.inactive = 0
                        AND ta.company_id = ".$companyId."
        ";

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlTA .= ' AND ta.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlTA .= ' AND ta.goal_id = ' . $goalId;
            }
        }

        if ($areaId != 0) {
            $sqlTA .= ' AND ta.area_id  = ' . $areaId;
        }

        if ($year != 0) {
            $sqlTA .= ' AND ta.year = ' . $year;
        }

        if ($month != 0) {
            $sqlTA .= ' AND ta.month = ' . $month;
        }

        $objTargetAreaDB = DB::select(DB::raw($sqlTA));
        /* ************************************************************************************************************/
        if (count($objImportantLevelAreaDB) == 0) {
            Session::flash('message-errors', 'Vui lòng import tỷ trọng cho Tổ/Quận/Huyện trước khi xuất báo cáo.');
            return redirect('managePriorityArea/' . $companyId . '/' . $areaId . '/' . $goalId . '/' . $year . '/' . $month);
        }

        $arrDistinctArea = array();
        $iDA = 0;

        foreach($objImportantLevelAreaDB as $ila){
            if(count($arrDistinctArea) == 0){

                $arrDistinctArea[$iDA]['areaId']    = $ila->area_id;
                $arrDistinctArea[$iDA]['areaCode']  = $ila->area_code;
                $arrDistinctArea[$iDA]['areaName']  = $ila->area_name;
                $iDA++;

            }else{
                $exist = 0;
                foreach($arrDistinctArea as $daArea){
                    if($daArea['areaId'] == $ila->area_id){
                        $exist = 1;
                        break;
                    }
                }

                if($exist == 0){
                    $arrDistinctArea[$iDA]['areaId']    = $ila->area_id;
                    $arrDistinctArea[$iDA]['areaCode']  = $ila->area_code;
                    $arrDistinctArea[$iDA]['areaName']  = $ila->area_name;
                    $iDA++;
                }
            }
        }
        foreach ($arrDistinctArea as $daArea) {
            if ($maxCol == 30) {
                break;
            }
            $maxCol++;
            $areaId     = $daArea['areaId'];
            $arrOneILAs = array();
            $iInside    = 0;

            foreach($objImportantLevelAreaDB as $ila){
                if($ila->area_id == $areaId){

                    $targetValue = 0;
                    if(count($objTargetAreaDB) > 0){
                        foreach($objTargetAreaDB as $targetArea){
                            if($targetArea->area_id == $areaId && $targetArea->goal_id == $ila->goal_id){
                                $targetValue = $targetArea->target_value;
                                break;
                            }
                        }
                    }

                    $targetValue = ($targetValue != 0) ? $this->formatNumber($targetValue) : '-';

                    $arrOneILAs[$iInside]['id']                 = $ila->id;
                    $arrOneILAs[$iInside]['target_value']       = $targetValue;
                    $arrOneILAs[$iInside]['company_id']         = $ila->company_id;
                    $arrOneILAs[$iInside]['area_id']            = $ila->area_id;
                    $arrOneILAs[$iInside]['goal_id']            = $ila->goal_id;
                    $arrOneILAs[$iInside]['month']              = $ila->month;
                    $arrOneILAs[$iInside]['year']               = $ila->year;
                    $arrOneILAs[$iInside]['important_level']    = $ila->important_level;
                    $arrOneILAs[$iInside]['benchmark']          = $ila->benchmark;
                    $arrOneILAs[$iInside]['cal_benchmark']      = $ila->cal_benchmark;
                    $arrOneILAs[$iInside]['implement_point']    = $ila->implement_point;
                    $arrOneILAs[$iInside]['goal_level']         = $ila->goal_level;
                    $arrOneILAs[$iInside]['inactive']           = $ila->inactive;
                    $arrOneILAs[$iInside]['goal_code']          = $ila->goal_code;
                    $arrOneILAs[$iInside]['parent_id']          = $ila->parent_id;
                    $arrOneILAs[$iInside]['goal_name']          = $ila->goal_name;
                    $arrOneILAs[$iInside]['area_code']          = $ila->area_code;
                    $arrOneILAs[$iInside]['area_name']          = $ila->area_name;

                    $iInside++;
                }
            }

            $endIndexColumn = \PHPExcel_Cell::columnIndexFromString($endLabelColumn);
            $uFirstLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn);
            $uLastLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn + 3);

            /**
             * $uLabelTA  : Target Value
             * $uLabelIL  : Important level
             * $uLabelBM  : Benchmark
             * $uLabelCBM : Calculator Benchmark
             */
            $column    = $endIndexColumn;
            $uLabelTA  = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIL  = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelBM  = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelCBM = \PHPExcel_Cell::stringFromColumnIndex($column++);

            #Set width for column
            $hashColumn = array(
              1 => $uLabelTA
            , 2 => $uLabelIL
            , 3 => $uLabelBM
            , 4 => $uLabelCBM
            );
            $count = 0;
            foreach ($hashColumn as $key => $value) {
                $dimension = 0;
                switch ($value) {
                    case $uLabelBM:
                    case $uLabelCBM:
                        $dimension = 10;
                        break;
                    case $uLabelIL:
                        $dimension = 8;
                        break;
                    case $uLabelTA:
                        $dimension = 15;
                        break;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                $count++;
            }

            $objPHPExcel->getActiveSheet()->mergeCells($uFirstLabel . $headerForILA . ':' . $uLastLabel . $headerForILA);

            #Set value title
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . $headerForILA, '['.$daArea['areaCode'] . '] ' . $daArea['areaName']);
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . ($headerForILA + 1), 'Kế hoạch (Chỉ tiêu)');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelIL . ($headerForILA + 1), 'Tỷ trọng');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelBM . ($headerForILA + 1), 'Điểm chuẩn');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelCBM . ($headerForILA + 1), 'Điểm chuẩn KPI');

            foreach ($arrOneILAs as $dTA) {
                $indexRow = 0;

                $taGoalId         = $dTA['goal_id'];
                $taTargetValue    = $dTA['target_value'];
                $taImportantLevel = $dTA['important_level'];
                $taBenchmark      = $dTA['benchmark'];
                $taCalBenchmark   = $dTA['cal_benchmark'];

                $taImportantLevel = ($taImportantLevel != 0) ? $this->formatNumber($taImportantLevel) : '-';
                $taBenchmark      = ($taBenchmark != 0) ? $this->formatNumber($taBenchmark) : '-';
                $taCalBenchmark   = ($taCalBenchmark != 0) ? $this->formatNumber($taCalBenchmark) : '-';

                foreach($arrPRowGoal as $aPRow){
                    if($aPRow['goalId'] == $taGoalId){
                        $indexRow = $aPRow['numRow'];
                    }
                }
                if($indexRow == 0){
                    foreach($arrRowGoal as $aRow){
                        if($aRow['goalId'] == $taGoalId){
                            $indexRow = $aRow['numRow'];
                        }
                    }
                }

                $objPHPExcel->getActiveSheet()
                    ->setCellValue($uLabelTA . $indexRow, $taTargetValue)
                    ->setCellValue($uLabelIL . $indexRow, $taImportantLevel)
                    ->setCellValue($uLabelBM . $indexRow, $taBenchmark)
                    ->setCellValue($uLabelCBM . $indexRow, $taCalBenchmark)
                ;
                $objPHPExcel->getActiveSheet()->getStyle($uLabelTA . $indexRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle($uLabelCBM . $indexRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            }
            $endLabelColumn = $uLastLabel;
        }

        ##############################################################################################################

        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getRowDimension(7)->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(45);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($startLoopColumn.'7:'.$endLabelColumn.'8')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        #Draw parent rows
        for ($iDP = 0; $iDP < count($arrPRowGoal); $iDP++) {
            $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $arrPRowGoal[$iDP]['numRow']. ':' . $endLabelColumn . $arrPRowGoal[$iDP]['numRow'])
                ->getFill()
                ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB(excelUtils::COLOR_GREEN);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrPRowGoal[$iDP]['numRow'] . ':' . $endLabelColumn . $arrPRowGoal[$iDP]['numRow'])->applyFromArray($styleArray);
        }

        #Draw child rows

        $styleChildRows = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        for ($iDC = 0; $iDC < count($arrRowGoal); $iDC++) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $arrRowGoal[$iDC]['numRow'] . ':' . $endLabelColumn . $arrRowGoal[$iDC]['numRow'])->applyFromArray($styleChildRows);
        }

        $styleLast = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $lastRow . ':' . $endLabelColumn . $lastRow)->applyFromArray($styleLast);

        /*------------------------------------------------------------------------------------------------------------*/
        /* Các công thức tính toán:*/
        $rowCalculate = $lastRow + 3;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $rowCalculate, '* Các công thức tính toán:');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCalculate)->getFont()->setBold(true);
        $rowCalculate++;

        $arrFormulaGoalArea = commonUtils::arrFormulaGoalArea($rowCalculate, 2);

        $i = $rowCalculate;
        foreach($arrFormulaGoalArea as $formula){
            $i++;
            $indexRow = $i;
            $nameFormula = $formula['name'];
            $description = $formula['description'];

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $indexRow, $nameFormula)
                ->setCellValue('C' . $indexRow, $description);

            $first = substr(trim($nameFormula),0,1);
            if($first == '-'){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $indexRow)->getFont()->setBold(true);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(10);
        /*------------------------------------------------------------------------------------------------------------*/

        ##############################################################################################################
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);
        $fileName = 'QuanLyTyTrongChoToQuanHuyen';
        $this->outputFile($fileName, $objPHPExcel);
    }

    private function getGoalName($goalId)
    {
        return DB::table('goal')->select('goal_name')->where('inactive', 0)->where('id', $goalId)->first();
    }

    /**
     * Export Ke hoach cho chuc danh
     * @param $companyId
     * @param $areaId
     * @param $positionId
     * @param $goalId
     * @param $goalType
     * @param $year
     * @param $month
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \PHPExcel_Exception
     */
    public function exportGoalPosition($companyId, $areaId, $positionId, $goalId, $goalType, $year, $month){
        /* *************************************************************************************************************
         * define default excel
         * ************************************************************************************************************/
        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/blank.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        /* *************************************************************************************************************
         * check input data
         * get all data for excel
         * ************************************************************************************************************/
        if ($companyId == 0) {
            Session::flash('message-errors', 'Vui lòng chọn Phòng/Đài/MBF HCM trước khi xuất báo cáo.');
            return redirect('manageGoalPosition/' . $companyId . '/' . $areaId . '/' .$positionId.'/'. $goalId . '/'.$goalType.'/' . $year . '/' . $month);
        }

        /* *************************************************************************************************************
         * check data before export
         * redirect default page will user override URL not standard URL
         * redirect of user choose if data not valid
         * ************************************************************************************************************/
        if(
            !is_numeric($companyId)
            || !is_numeric($areaId)
            || !is_numeric($positionId)
            || !is_numeric($goalId)
            || !is_numeric($goalType)
            || !is_numeric($year)
            || !is_numeric($month)
        ){
            Session::flash('message-errors', 'Vui lòng chọn Danh mục trước khi xuất báo cáo!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }
        /* *************************************************************************************************************
         * get Data for left report
         * object target area
         * object target position
         * ************************************************************************************************************/
        $sqlTargetArea = "
            SELECT ta.*, g.goal_code, a.area_name, c.company_name, g.parent_id, g.goal_name, u.unit_name
            FROM target_area ta
            LEFT JOIN goal g ON g.id = ta.goal_id
            LEFT JOIN company c ON c.id = ta.company_id
            LEFT JOIN area a ON a.id = ta.area_id
            LEFT JOIN unit u ON u.id = ta.unit_id
            WHERE ta.inactive = 0
            AND ta.company_id = ".$companyId."
            AND ta.area_id = ".$areaId."
            AND ta.year = ".$year."
        ";

        $sqlTargetPosition = "
            SELECT tp.*, g.goal_code, a.area_name, c.company_name, g.parent_id, p.position_code, p.position_name
            FROM target_position tp
            LEFT JOIN goal g ON g.id = tp.goal_id
            LEFT JOIN company c ON c.id = tp.company_id
            LEFT JOIN area a ON a.id = tp.area_id
            LEFT JOIN position p ON p.id = tp.position_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND tp.area_id = ".$areaId."
            AND tp.year = ".$year."
        ";

        if ($month != 0) {
            $sqlTargetArea      .= " AND ta.month = '" . $month . "'";
            $sqlTargetPosition  .= " AND tp.month = '" . $month . "'";
        }

        if ($positionId != 0) {
            $sqlTargetPosition  .= " AND tp.position_id = " . $positionId . " ";
        }

        if ($goalType != -1) {
            $sqlTargetArea      .= " AND ta.goal_type = '" . $goalType . "'";
            $sqlTargetPosition  .= " AND tp.goal_type = '" . $goalType . "'";
        }

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlTargetArea      .= ' AND ta.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
                $sqlTargetPosition  .= ' AND tp.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlTargetArea      .= ' AND ta.goal_id = ' . $goalId;
                $sqlTargetPosition  .= ' AND tp.goal_id = ' . $goalId;
            }
        }

        $objTargetAreaDB     = DB::select(DB::raw($sqlTargetArea));
        $objTargetPositionDB = DB::select(DB::raw($sqlTargetPosition));
        $objGoalDB           = DB::table('goal')->where('inactive', 0)->get();

        if (count($objTargetAreaDB) == 0 || count($objTargetPositionDB) == 0) {
            Session::flash('message-errors', 'Vui lòng import kế hoạch trước khi xuất báo cáo.');
            return redirect('manageGoalPosition/' . $companyId . '/' . $areaId . '/' .$positionId.'/'. $goalId . '/'.$goalType.'/' . $year . '/' . $month);
        }
        /* *************************************************************************************************************
         * sort data
         * ************************************************************************************************************/
        $companyName = "";
        $areaName    = "";
        foreach($objTargetAreaDB as $targetArea){
            $companyName = $targetArea->company_name;
            $areaName    = $targetArea->area_name;
            break;
        }

        $arrDefaultParentGoal = array();
        $iDG = 0;

        $arrParentGoal = array();
        $iPG = 0;

        $arrChildGoal = array();
        $iCG = 0;

        $arrDistinctPosition = array();
        $iDP = 0;

        $arrMonth = array();

        $arrDefaultMonth = commonUtils::defaultMonth();

        foreach($objGoalDB as $goal){
            if($goal->parent_id == 0 ){
                if(count($arrDefaultParentGoal) == 0){
                    $arrDefaultParentGoal[$iDG]['goalId']   = $goal->id;
                    $arrDefaultParentGoal[$iDG]['goalCode'] = $goal->goal_code;
                    $arrDefaultParentGoal[$iDG]['goalName'] = $goal->goal_name;
                    $iDG++;
                }else{
                    $exist = 0;
                    foreach($arrDefaultParentGoal as $dpGoal){
                        if($dpGoal['goalId'] == $goal->id){
                            $exist = 1;
                            break;
                        }
                    }

                    if($exist == 0){
                        $arrDefaultParentGoal[$iDG]['goalId']   = $goal->id;
                        $arrDefaultParentGoal[$iDG]['goalCode'] = $goal->goal_code;
                        $arrDefaultParentGoal[$iDG]['goalName'] = $goal->goal_name;
                        $iDG++;
                    }
                }
            }

        }

        foreach($objTargetAreaDB as $targetArea){
            /* *********************************************************************************************************
             * get array parent goal of target area
             * ********************************************************************************************************/
            if(
                count($arrDefaultParentGoal) == count($arrParentGoal)
                && count($arrDefaultMonth) == count($arrMonth)
            ){
                break;
            }
            if($targetArea->parent_id != 0){

                $parentName = '';
                $parentCode = '';

                foreach($arrDefaultParentGoal as $dpGoal){
                    if($dpGoal['goalId'] == $targetArea->parent_id){
                        $parentName = $dpGoal['goalName'];
                        $parentCode = $dpGoal['goalCode'];
                        break;
                    }
                }

                if(count($arrParentGoal) == 0){
                    $arrParentGoal[$iPG]['goalId']   = $targetArea->parent_id;
                    $arrParentGoal[$iPG]['goalCode'] = $parentCode;
                    $arrParentGoal[$iPG]['goalName'] = $parentName;
                    $iPG++;
                }else{
                    $exist = 0;
                    foreach($arrParentGoal as $parentGoal){
                        if($parentGoal['goalId'] == $targetArea->parent_id){
                            $exist = 1;
                            break;
                        }
                    }

                    if($exist == 0){
                        $arrParentGoal[$iPG]['goalId']   = $targetArea->parent_id;
                        $arrParentGoal[$iPG]['goalCode'] = $parentCode;
                        $arrParentGoal[$iPG]['goalName'] = $parentName;
                        $iPG++;
                    }
                }
            }
            /* *********************************************************************************************************
             * get array child goal of target area
             * ********************************************************************************************************/
            if(count($arrChildGoal) == 0){
                $arrChildGoal[$iCG]['goalId']   = $targetArea->goal_id;
                $arrChildGoal[$iCG]['parentId'] = $targetArea->parent_id;
                $arrChildGoal[$iCG]['goalCode'] = $targetArea->goal_code;
                $arrChildGoal[$iCG]['goalName'] = $targetArea->goal_name;
                $iCG++;
            }else{
                $exist = 0;
                foreach($arrChildGoal as $childGoal){
                    if($childGoal['goalId'] == $targetArea->goal_id){
                        $exist = 1;
                        break;
                    }
                }

                if($exist == 0){
                    $arrChildGoal[$iCG]['goalId']   = $targetArea->goal_id;
                    $arrChildGoal[$iCG]['parentId'] = $targetArea->parent_id;
                    $arrChildGoal[$iCG]['goalCode'] = $targetArea->goal_code;
                    $arrChildGoal[$iCG]['goalName'] = $targetArea->goal_name;
                    $iCG++;
                }
            }
            if(!in_array($targetArea->month, $arrMonth)){

                $arrMonth[] = $targetArea->month;
            }

        }

        foreach($objTargetPositionDB as $targetPosition){
            if(count($arrDistinctPosition) == 0){

                $arrDistinctPosition[$iDP]['positionId']    = $targetPosition->position_id;
                $arrDistinctPosition[$iDP]['positionCode']  = $targetPosition->position_code;
                $arrDistinctPosition[$iDP]['positionName']  = $targetPosition->position_name;
                $iDP++;

            }else{
                $exist = 0;
                foreach($arrDistinctPosition as $dpPosition){
                    if($dpPosition['positionId'] == $targetPosition->position_id){
                        $exist = 1;
                        break;
                    }
                }

                if($exist == 0){
                    $arrDistinctPosition[$iDP]['positionId']    = $targetPosition->position_id;
                    $arrDistinctPosition[$iDP]['positionCode']  = $targetPosition->position_code;
                    $arrDistinctPosition[$iDP]['positionName']  = $targetPosition->position_name;
                    $iDP++;
                }
            }
        }

        /* *************************************************************************************************************
         * write to excel
         * A: STT
         * B: Mã mục tiêu
         * C: Tên mục tiêu
         * D: Loại mục tiêu
         * E: Đơn vị tính
         * F: Kế hoạch
         * G: Tỷ trọng
         * H: Điểm chuẩn
         * I: Điểm thực hiện
         * J: Tỷ lệ đạt
         * ************************************************************************************************************/
        $beginData = 11;
        $parentRow = $beginData;

        $arrGoalRow = array();
        $iGR = 0;

        $arrParentRow = array();
        $arrMiddleRow = array();

        foreach($arrParentGoal as $parentGoal){

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$parentRow.':E'.$parentRow);

            $pGoalId   = $parentGoal['goalId'];
            $pGoalCode = $parentGoal['goalCode'];
            $pGoalName = $parentGoal['goalName'];

            $arrParentRow[] = $parentRow;

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $parentRow, $pGoalName)
                ->setCellValue('F' . $parentRow, '-')
                ->setCellValue('G' . $parentRow, '-')
                ->setCellValue('H' . $parentRow, '-')
                ->setCellValue('I' . $parentRow, '-')
                ->setCellValue('J' . $parentRow, '-')
            ;

            $middleRow = $parentRow + 1;
            foreach($arrChildGoal as $childGoal){
                if($childGoal['parentId'] == $pGoalId){
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$middleRow.':E'.$middleRow);

                    $arrMiddleRow[] = $middleRow;

                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $middleRow, '')
                        ->setCellValue('B' . $middleRow, ' * '.$childGoal['goalName'])
                        ->setCellValue('F' . $middleRow, '-')
                        ->setCellValue('G' . $middleRow, '-')
                        ->setCellValue('H' . $middleRow, '-')
                        ->setCellValue('I' . $middleRow, '-')
                        ->setCellValue('J' . $middleRow, '-')
                    ;

                    $childRow = $middleRow + 1;

                    $no = 1;
                    foreach($objTargetAreaDB as $targetArea){

                        $taGoalId           = $targetArea->goal_id;
                        $taGoalCode         = $targetArea->goal_code;
                        $taGoalName         = $targetArea->goal_name.' tháng '.$targetArea->month;
                        $taGoalType         = commonUtils::renderGoalTypeName($targetArea->goal_type);
                        $taUnit             = $targetArea->unit_name;
                        $taImportantLevel   = ($targetArea->important_level != 0) ? $targetArea->important_level : '-';
                        $taTargetValue      = ($targetArea->target_value != 0) ? $this->formatNumber($targetArea->target_value) : '-';
                        $taBenchmark        = ($targetArea->benchmark != 0) ? $this->formatNumber($targetArea->benchmark) : '-';
                        $taImplementPoint   = ($targetArea->implement_point != 0) ? $this->formatNumber($targetArea->implement_point) : '-';
                        $taRealPercent      = ($targetArea->real_percent != 0) ? $this->formatNumber($targetArea->real_percent * 100).'%' : '-';
                        $taMonth            = $targetArea->month;
                        $taYear             = $targetArea->year;

                        if($taGoalId == $childGoal['goalId']){
                            $objPHPExcel->getActiveSheet()
                                ->setCellValue('A' . $childRow, $no++)
                                ->setCellValue('B' . $childRow, $taGoalCode)
                                ->setCellValue('C' . $childRow, $taGoalName)
                                ->setCellValue('D' . $childRow, $taGoalType)
                                ->setCellValue('E' . $childRow, $taUnit)
                                ->setCellValue('F' . $childRow, $taTargetValue)
                                ->setCellValue('G' . $childRow, $taImportantLevel)
                                ->setCellValue('H' . $childRow, $taBenchmark)
                                ->setCellValue('I' . $childRow, $taImplementPoint)
                                ->setCellValue('J' . $childRow, $taRealPercent)
                            ;

                            $objPHPExcel->getActiveSheet()->getStyle('A' . $childRow.':B' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            $arrGoalRow[$iGR]['month']      = $taMonth;
                            $arrGoalRow[$iGR]['goalId']     = $taGoalId;
                            $arrGoalRow[$iGR]['goalCode']   = $taGoalCode;
                            $arrGoalRow[$iGR]['numRow']     = $childRow;
                            $iGR++;

                            $lastRow = $childRow;

                            $childRow++;
                        }

                    }
                    $middleRow = $childRow;
                }
            }
            $parentRow = $middleRow;
        }
        /* *************************************************************************************************************
         * soft column
         * ************************************************************************************************************/
        $endLabelColumn = 'J';

        foreach($arrDistinctPosition as $dpPosition){

            $dpPositionId   = $dpPosition['positionId'];
            $dpPositionCode = $dpPosition['positionCode'];
            $dpPositionName = $dpPosition['positionName'];

            $endIndexColumn = \PHPExcel_Cell::columnIndexFromString($endLabelColumn);
            $uFirstLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn);
            $uLastLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn + 4);

            /**
             * $uLabelTA  : Target Value
             * $uLabelIL  : Important level
             * $uLabelCBM : CalBenchmark
             * $uLabelIM  : Implement
             * $uLabelIP  : Implement point
             */

            $column   = $endIndexColumn;
            $uLabelTA = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIL = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelBM = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIM = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIP = \PHPExcel_Cell::stringFromColumnIndex($column++);

            #Set width for column
            $loopColumn = array(
              1 => $uLabelTA
            , 2 => $uLabelIL
            , 3 => $uLabelBM
            , 4 => $uLabelIM
            , 5 => $uLabelIP
            );
            $count = 0;
            foreach ($loopColumn as $key => $value) {
                $dimension = 0;
                switch ($value) {
                    case $uLabelBM:
                    case $uLabelIL:
                    case $uLabelIP:
                        $dimension = 10;
                        break;
                    case $uLabelTA:
                    case $uLabelIM:
                        $dimension = 22;
                        break;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                $count++;
            }
            $beginHeader = $beginData - 3;
            $objPHPExcel->getActiveSheet()->mergeCells($uLabelTA.$beginHeader.':'.$uLabelIP.$beginHeader);
            $subLoopHeader = ($beginData - 2);
            $objPHPExcel->getActiveSheet()
                ->setCellValue($uLabelTA.$beginHeader, '['.$dpPositionCode.'] '.$dpPositionName)
                ->setCellValue($uLabelTA.$subLoopHeader, 'Kế hoạch')
                ->setCellValue($uLabelIL.$subLoopHeader, 'Tỷ trọng')
                ->setCellValue($uLabelBM.$subLoopHeader, 'Điểm chuẩn KPI')
                ->setCellValue($uLabelIM.$subLoopHeader, 'Thực hiện')
                ->setCellValue($uLabelIP.$subLoopHeader, 'Điểm thực hiện')
            ;

            foreach($objTargetPositionDB as $targetPosition){

                $tpPositionId       = $targetPosition->position_id;
                $tpGoalId           = $targetPosition->goal_id;
                $tpCalBenchmark     = ($targetPosition->cal_benchmark != 0) ? $this->formatNumber($targetPosition->cal_benchmark) : '-';
                $tpImportantLevel   = ($targetPosition->important_level != 0) ? $targetPosition->important_level : '-';
                $tpTargetValue      = ($targetPosition->target_value != 0) ? $this->formatNumber($targetPosition->target_value) : '-';
                $tpImplement        = ($targetPosition->implement != 0) ? $this->formatNumber($targetPosition->implement) : '-';
                $tpImplementPoint   = ($targetPosition->implement_point != 0) ? $this->formatNumber($targetPosition->implement_point) : '-';
                $tpMonth            = $targetPosition->month;

                if($tpPositionId == $dpPositionId){
                    $indexRow = 0;
                    foreach($arrGoalRow as $goalRow){
                        if(
                            $goalRow['goalId'] == $tpGoalId
                            && $goalRow['month'] == $tpMonth
                        ){
                            $indexRow = $goalRow['numRow'];
                            break;
                        }
                    }

                    if($indexRow != 0){
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($uLabelTA.$indexRow, $tpTargetValue)
                            ->setCellValue($uLabelIL.$indexRow, $tpImportantLevel)
                            ->setCellValue($uLabelBM.$indexRow, $tpCalBenchmark)
                            ->setCellValue($uLabelIM.$indexRow, $tpImplement)
                            ->setCellValue($uLabelIP.$indexRow, $tpImplementPoint)
                        ;
                    }

                }
            }

            $endLabelColumn = $uLabelIP;
        }
        /* *************************************************************************************************************
         * format template
         * ************************************************************************************************************/
        $indexTitle = 2;
        $objPHPExcel->getActiveSheet()->getRowDimension($indexTitle)->setRowHeight(27);
        $objPHPExcel->getActiveSheet()->getRowDimension(($subLoopHeader+1))->setRowHeight(1);
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$indexTitle.':J'.$indexTitle);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.($indexTitle + 0), 'KẾ HOẠCH PHÂN BỔ CHO CHỨC DANH');
        $objPHPExcel->getActiveSheet()->getStyle('A'.($indexTitle + 0))->getFont()->setSize(15);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.($indexTitle + 4), 'Năm:');
        $objPHPExcel->getActiveSheet()->setCellValue('D'.($indexTitle + 2), 'Phòng/Đài/MBF HCM:');
        $objPHPExcel->getActiveSheet()->setCellValue('D'.($indexTitle + 3), 'Quận:');
        $objPHPExcel->getActiveSheet()->freezePane('J'.($subLoopHeader + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('E'.($indexTitle + 4), $year);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.($indexTitle + 2), $companyName);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.($indexTitle + 3), $areaName);

        $objPHPExcel->getActiveSheet()->mergeCells('A'.$beginHeader.':A'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('B'.$beginHeader.':B'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('C'.$beginHeader.':C'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$beginHeader.':D'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('E'.$beginHeader.':E'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('F'.$beginHeader.':F'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('G'.$beginHeader.':G'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('H'.$beginHeader.':H'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('I'.$beginHeader.':I'.($beginHeader + 1));
        $objPHPExcel->getActiveSheet()->mergeCells('J'.$beginHeader.':J'.($beginHeader + 1));

        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$beginHeader, 'STT')
            ->setCellValue('B'.$beginHeader, 'Mã mục tiêu')
            ->setCellValue('C'.$beginHeader, 'Mục tiêu')
            ->setCellValue('D'.$beginHeader, 'Loại mục tiêu')
            ->setCellValue('E'.$beginHeader, 'Đơn vị tính')
            ->setCellValue('F'.$beginHeader, 'Kế hoạch')
            ->setCellValue('G'.$beginHeader, 'Tỷ trọng')
            ->setCellValue('H'.$beginHeader, 'Điểm chuẩn')
            ->setCellValue('I'.$beginHeader, 'Điểm thực hiện')
            ->setCellValue('J'.$beginHeader, 'Tỷ lệ đạt')
        ;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$beginHeader.':'.$endLabelColumn.$lastRow)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$endLabelColumn.$lastRow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$indexTitle)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$beginHeader.':'.$endLabelColumn.$subLoopHeader)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$beginData.':'.$endLabelColumn.$lastRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$beginHeader.':'.$endLabelColumn.$subLoopHeader)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB(commonUtils::COLOR_DARK);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$endLabelColumn.$subLoopHeader)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D'.($indexTitle + 2).':'.'D'.($indexTitle + 4))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('E'.($indexTitle + 2).':'.'E'.($indexTitle + 4))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


        #Set with for column
        $hashColumn = range('A', 'J');
        $count = 0;
        foreach ($hashColumn as $key => $value) {
            $dimension = 0;
            switch ($value) {
                case 'A':
                    $dimension = 8;
                    break;
                case 'B':
                    $dimension = 10;
                    break;
                case 'C':
                    $dimension = 72;
                    break;
                case 'D':
                    $dimension = 20;
                    break;
                case 'E':
                    $dimension = 12;
                    break;
                case 'F':
                case 'I':
                    $dimension = 22;
                    break;
                case 'G':
                case 'H':
                case 'J':
                    $dimension = 10;
                    break;
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
            $count++;
        }

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A'.$beginHeader.':'.$endLabelColumn.$subLoopHeader)->applyFromArray($styleArray);

        #Draw parent rows
        foreach ($arrParentRow as $pRow) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $pRow. ':' . $endLabelColumn . $pRow)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB(CommonUtils::COLOR_GOAL);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $pRow . ':' . $endLabelColumn . $pRow)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getRowDimension($pRow)->setRowHeight(16);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$pRow)->getFont()->setBold(true);
        }

        foreach ($arrMiddleRow as $mRow) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $mRow. ':' . $endLabelColumn . $mRow)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB(CommonUtils::COLOR_BROW);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $mRow . ':' . $endLabelColumn . $mRow)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$mRow)->getFont()->setBold(true);
        }

        #Draw child rows

        $styleChildRows = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        foreach ($arrGoalRow as $goalRow) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $goalRow['numRow'] . ':' . $endLabelColumn . $goalRow['numRow'])->applyFromArray($styleChildRows);
        }

        $styleLast = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $lastRow . ':' . $endLabelColumn . $lastRow)->applyFromArray($styleLast);

        /*------------------------------------------------------------------------------------------------------------*/
        /* Các công thức tính toán:*/
        $rowCalculate = $lastRow + 3;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $rowCalculate, '* Các công thức tính toán:');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCalculate)->getFont()->setBold(true);
        $rowCalculate++;

        $arrFormulaGoalArea = commonUtils::arrFormulaGoalArea($rowCalculate, 5);

        $i = $rowCalculate;
        foreach($arrFormulaGoalArea as $formula){
            $i++;
            $indexRow = $i;
            $nameFormula = $formula['name'];
            $description = $formula['description'];

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $indexRow, $nameFormula)
                ->setCellValue('C' . $indexRow, $description);

            $first = substr(trim($nameFormula),0,1);
            if($first == '-'){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $indexRow)->getFont()->setBold(true);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(10);

        /* *************************************************************************************************************
         * output file
         * ************************************************************************************************************/
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(80);
        $objPHPExcel->getActiveSheet()->setTitle('QuanLyKeHoachCuaChucDanh');
        $fileName = 'QuanLyKeHoachCuaChucDanh';
        $this->outputFile($fileName, $objPHPExcel);
    }

    private function formatNumber($floatValue)
    {
        return number_format($floatValue
            , 3 /*Số chữ số sau dấu thập phân*/
            , '.' /*Ký tự phân cách phần thập phân và phần nguyên*/
            , ',' /*Ký tự phân cách phần nghìn*/
        );
    }

    /**
     * Export ke hoach khu vuc
     * @param $companyId
     * @param $areaId
     * @param $goalId
     * @param $goalType
     * @param $year
     * @param $month
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \PHPExcel_Exception
     */
    public function exportGoalArea($companyId, $areaId, $goalId, $goalType, $year, $month)
    {
        /* *************************************************************************************************************
         * define default excel
         * ************************************************************************************************************/
        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/blank.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);

        /* *************************************************************************************************************
         * check and get data
         * ************************************************************************************************************/

        if ($companyId == 0) {
            Session::flash('message-errors', 'Vui lòng chọn Phòng/Đài/MBF HCM trước khi xuất báo cáo!!');
            return redirect('manageGoalArea/' . $companyId . '/' . $areaId . '/' . $goalId . '/' . $goalType . '/' . $year . '/' . $month);
        }

        #Get all value header
        $company = DB::table('company')->where('inactive', 0)->where('id', $companyId)->first();

        /*------------------------------------------------------------------------------------------------------------*/
        $sqlGetApplyDate = "
            SELECT apply_date
            FROM important_level_company
            WHERE inactive = 0
            AND company_id = ".$companyId."
            AND year(apply_date) = ".$year."
            ORDER BY apply_date DESC
            LIMIT 0,1
        ";
        $objApplyDate = DB::select(DB::raw($sqlGetApplyDate));

        if (count($objApplyDate) == 0) {
            Session::flash('message-errors', 'Vui lòng import tỷ trọng cho Phòng/Đài/MBF HCM: <b>'.$company->company_name.'</b> trước khi xuất báo cáo kế hoạch Tổ/Quận/Huyện.');
            return redirect('manageGoalArea/' . $companyId . '/' . $areaId . '/' . $goalId . '/' . $goalType . '/' . $year . '/' . $month);
        }

        $ilcApplyDate = commonUtils::objectToArray($objApplyDate)[0]['apply_date'];
        /*------------------------------------------------------------------------------------------------------------*/
        #Set width for column
        $hashColumn = range('A', 'H');
        $count = 0;
        foreach ($hashColumn as $key => $value) {
            $dimension = 0;
            switch ($value) {
                case 'A':
                case 'F':
                    $dimension = 7;
                    break;
                case 'D':
                    $dimension = 15;
                    break;
                case 'C':
                    $dimension = 63;
                    break;
                case 'G':
                    $dimension = 20;
                    break;
                case 'H':
                case 'E':
                case 'B':
                    $dimension = 11;
                    break;
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
            $count++;
        }

        #Cell A2
        $objPHPExcel->getActiveSheet()->mergeCells('A2' . ':L2');
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'KẾ HOẠCH/CHỈ TIÊU PHÂN BỔ CHO TỔ/QUẬN/HUYỆN');
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(27);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        #Set all value in header
        for ($beginHeader = 4; $beginHeader <= 6; $beginHeader++) {
            $objPHPExcel->getActiveSheet()->getStyle('D' . $beginHeader . ':E' . $beginHeader)->getFont()->setSize(11);
            $objPHPExcel->getActiveSheet()->getRowDimension($beginHeader)->setRowHeight(15);
            //$objPHPExcel->getActiveSheet()->getStyle('D' . $beginHeader . ':E' . $beginHeader)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $beginHeader)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $beginHeader)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            switch ($beginHeader) {
                case 4:
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $beginHeader, 'Phòng/Đài/MBF HCM:');
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $beginHeader, $company->company_name);
                    break;
                case 5:
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $beginHeader, 'Năm:');
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $beginHeader, $year);
                    break;
                case 6:
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $beginHeader, 'Tháng:');
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $beginHeader, $month);
                    break;
            }
        }

        $beginHeader++;
        $indexHeader = $beginHeader;

        #Format header
        $nextBeginHeader = $beginHeader + 1;
        $objPHPExcel->getActiveSheet()->freezePane('I10');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $beginHeader . ':H' . $beginHeader)->getFont()->setSize(11);
        $objPHPExcel->getActiveSheet()->getRowDimension($beginHeader)->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $beginHeader . ':H' . $beginHeader)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $objPHPExcel->getActiveSheet()->mergeCells('A' . $beginHeader . ':A' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $beginHeader . ':B' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('C' . $beginHeader . ':C' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('D' . $beginHeader . ':D' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('E' . $beginHeader . ':E' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('F' . $beginHeader . ':F' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('G' . $beginHeader . ':G' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('H' . $beginHeader . ':H' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('I' . $beginHeader . ':I' . $nextBeginHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('J' . $beginHeader . ':J' . $nextBeginHeader);

        #Set value title
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $beginHeader, 'STT');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $beginHeader, 'Mã');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $beginHeader, 'Mục tiêu');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $beginHeader, 'Loại mục tiêu');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $beginHeader, 'Đơn vị tính');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $beginHeader, 'Tỷ trọng');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $beginHeader, 'Kế hoạch (Chỉ tiêu)');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $beginHeader, 'Điểm chuẩn');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $beginHeader, 'Điểm thực hiện');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $beginHeader, 'Tỷ lệ đạt');
        /*------------------------------------------------------------------------------------------------------------*/


        $sqlILCs = "
            SELECT ilc.goal_id, ilc.*, g.goal_code, g.goal_type, g.goal_name, g.parent_id, u.unit_code, u.unit_name
            FROM important_level_company ilc
            LEFT JOIN goal g on ilc.goal_id = g.id
            LEFT JOIN unit u on ilc.unit_id = u.id
            WHERE ilc.inactive = 0
            AND ilc.company_id = '".$companyId."'
            AND ilc.apply_date = '".$ilcApplyDate."'
        ";
        $importantLevelCompanies = DB::select(DB::raw($sqlILCs));

        $sqlTargetArea = "
            SELECT ta.*, a.area_code, a.area_name, g.parent_id
            FROM target_area ta
            LEFT JOIN area a ON a.id = ta.area_id
            LEFT JOIN goal g on ta.goal_id = g.id
            WHERE ta.inactive = 0
            AND ta.company_id = ".$companyId."
            AND ta.year = ".$year."
            AND ta.month = ".$month."
        ";

        if($areaId != 0){
            $sqlTargetArea .= ' AND ta.area_id = ' . $areaId;
        }
        if($goalType != -1){
            $sqlTargetArea .= ' AND ta.goal_id in (SELECT id FROM  goal WHERE goal_type = ' . $goalType . '  ) ';
        }
        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlTargetArea .= ' AND ta.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlTargetArea .= ' AND ta.goal_id = ' . $goalId;
            }
        }

        $objTargetAreaDB = DB::select(DB::raw($sqlTargetArea));

        /* *************************************************************************************************************
         * Get arrParent and arrChild
         * ************************************************************************************************************/
        $arrParent   = array();
        $arrChildren = array();

        $iCG = 0;
        $iPG = 0;

        foreach ($importantLevelCompanies as $ilc) {

            $ilcTargetValue     = ($ilc->target_value != 0) ? $this->formatNumber($ilc->target_value) : '-';
            $ilcUnitName        = ($ilc->unit_name != '' && $ilc->unit_id != 0) ? $ilc->unit_name : '-';
            $ilcGoalName        = ($ilc->goal_name != '') ? $ilc->goal_name : '-';
            $ilcGoalCode        = ($ilc->goal_code != '') ? $ilc->goal_code : '-';
            $ilcGoalId          = ($ilc->goal_id != 0) ? $ilc->goal_id : '-';
            $ilcImportantLevel  = ($ilc->important_level != 0) ? $ilc->important_level : '-';
            $ilcBenchmark       = ($ilc->benchmark != 0) ? $this->formatNumber($ilc->benchmark) : '-';
            $ilcImplementPoint  = ($ilc->implement_point != 0) ? $this->formatNumber($ilc->implement_point) : '-';
            $ilcRealPercent     = ($ilc->real_percent != 0) ? $this->formatNumber($ilc->real_percent * 100).'%' : '-';

            if($ilc->parent_id == 0){

                $parentIP = 0;

                foreach ($importantLevelCompanies as $insideILC) {
                    if($insideILC->parent_id == $ilcGoalId){
                        $parentIP += $insideILC->implement_point;
                    }
                }

                $parentPC  = ($ilcBenchmark != 0) ? $this->formatNumber(($parentIP /  $ilcBenchmark )* 100).'%' : '-';
                $parentIP  = ($parentIP != 0) ? $this->formatNumber($parentIP) : '-';

                #Array for parent
                $arrParent[$iPG]['goalId']             = $ilcGoalId;
                $arrParent[$iPG]['goalCode']           = $ilcGoalCode;
                $arrParent[$iPG]['goalName']           = $ilcGoalName;
                $arrParent[$iPG]['targetValue']        = $ilcTargetValue;
                $arrParent[$iPG]['importantLevel']     = $ilcImportantLevel;
                $arrParent[$iPG]['benchmark']          = $ilcBenchmark;
                $arrParent[$iPG]['implementPoint']     = $parentIP;
                $arrParent[$iPG]['percentComplete']    = $parentPC;
                $iPG++;

            }else{
                #Array for child
                $arrChildren[$iCG]['goalId']           = $ilcGoalId;
                $arrChildren[$iCG]['goalCode']         = $ilcGoalCode;
                $arrChildren[$iCG]['goalName']         = $ilcGoalName;
                $arrChildren[$iCG]['goalParent']       = $ilc->parent_id;
                $arrChildren[$iCG]['goalType']         = commonUtils::renderGoalTypeName($ilc->goal_type);
                $arrChildren[$iCG]['unitName']         = $ilcUnitName;
                $arrChildren[$iCG]['targetValue']      = $ilcTargetValue;
                $arrChildren[$iCG]['importantLevel']   = $ilcImportantLevel;
                $arrChildren[$iCG]['benchmark']        = $ilcBenchmark;
                $arrChildren[$iCG]['implementPoint']   = $ilcImplementPoint;
                $arrChildren[$iCG]['percentComplete']  = $ilcRealPercent;
                $iCG++;
            }
        }
        /***************************************************************************************************************/
        $beginData = 10;
        $arrPRowGoal = array();
        $arrRowGoal = array();
        $iRG = 0;
        $iPRG = 0;
        $i = 1;
        foreach ($arrParent as $prow) {
            #Parent row
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $beginData . ':E' . $beginData);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $beginData.':H' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $beginData, $prow['goalName'])
                ->setCellValue('F' . $beginData, $prow['importantLevel'])
                ->setCellValue('H' . $beginData, $prow['benchmark'])
                ->setCellValue('I' . $beginData, $prow['implementPoint'])
                ->setCellValue('J' . $beginData, $prow['percentComplete'])
            ;
            $arrPRowGoal[$iPRG]['numRow'] = $beginData;
            $arrPRowGoal[$iPRG]['goalId'] = $prow['goalId'];
            $iPRG++;
            $childRow = $beginData + 1;
            foreach ($arrChildren as $crow) {
                if ($crow['goalParent'] == $prow['goalId']) {

                    $objPHPExcel->getActiveSheet()->getStyle('D' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    #Input array with row and goal id for loop of each user bellow
                    $arrRowGoal[$iRG]['numRow'] = $childRow;
                    $arrRowGoal[$iRG]['goalId'] = $crow['goalId'];
                    $iRG++;

                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $childRow, $i++)
                        ->setCellValue('B' . $childRow, $crow['goalCode'])
                        ->setCellValue('C' . $childRow, $crow['goalName'])
                        ->setCellValue('D' . $childRow, $crow['goalType'])
                        ->setCellValue('E' . $childRow, $crow['unitName'])
                        ->setCellValue('F' . $childRow, $crow['importantLevel'])
                        ->setCellValue('G' . $childRow, $crow['targetValue'])
                        ->setCellValue('H' . $childRow, $crow['benchmark'])
                        ->setCellValue('I' . $childRow, $crow['implementPoint'])
                        ->setCellValue('J' . $childRow, $crow['percentComplete'])
                    ;
                    $childRow++;
                }
            }
            $beginData = $childRow;
        }
        $lastRow = $beginData - 1;
        $objPHPExcel->getActiveSheet()->freezePane('J10');
        /* *************************************************************************************************************
         * Write data to soft
         * ************************************************************************************************************/
        #Begin create dynamic area section
        $endLabelColumn  = 'J';
        $startLoopColumn = 'K';

        /* ************************************************************************************************************/
        $sqlILA = 'SELECT
                            ila.*
                            , goal.id
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , area.area_code
                            , area.area_name
                        FROM  important_level_area AS ila
                        LEFT JOIN goal ON goal.id = ila.goal_id
                        LEFT JOIN area ON area.id = ila.area_id
                        WHERE ila.inactive = 0
                        AND ila.company_id = '.$companyId.'
                        AND ila.year = '.$year.'
                        AND ila.month = '.$month.'
                    ';
        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlILA .= ' AND ila.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlILA .= ' AND ila.goal_id = ' . $goalId;
            }
        }

        if ($areaId != 0) {
            $sqlILA .= ' AND ila.area_id  = ' . $areaId;
        }

        if($goalType != -1){
            $sqlILA .= ' AND ila.goal_id in (SELECT id FROM  goal WHERE goal_type = ' . $goalType . '  ) ';
        }
        $objImportantLevelAreaDB = DB::select(DB::raw($sqlILA));


        /* ************************************************************************************************************/

        $arrDistinctArea = array();
        $iDA = 0;

        foreach($objTargetAreaDB as $targetArea){
            if(count($arrDistinctArea) == 0){

                $arrDistinctArea[$iDA]['areaId']    = $targetArea->area_id;
                $arrDistinctArea[$iDA]['areaCode']  = $targetArea->area_code;
                $arrDistinctArea[$iDA]['areaName']  = $targetArea->area_name;
                $iDA++;

            }else{
                $exist = 0;
                foreach($arrDistinctArea as $daArea){
                    if($daArea['areaId'] == $targetArea->area_id){
                        $exist = 1;
                        break;
                    }
                }

                if($exist == 0){
                    $arrDistinctArea[$iDA]['areaId']    = $targetArea->area_id;
                    $arrDistinctArea[$iDA]['areaCode']  = $targetArea->area_code;
                    $arrDistinctArea[$iDA]['areaName']  = $targetArea->area_name;
                    $iDA++;
                }
            }
        }
        $maxCol = 1;

        $arrPercentColumn = array();

        foreach ($arrDistinctArea as $daArea) {
            if ($maxCol == 30) {
                break;
            }
            $maxCol++;
            $areaId     = $daArea['areaId'];
            $arrOneTAs  = array();
            $iInside    = 0;

            foreach($objImportantLevelAreaDB as $ila){
                if($ila->area_id == $areaId){

                    $targetValue = 0;

                    $parentIP  = 0;
                    $parentCIP = 0;
                    $childCIP  = 0;
                    $childIP   = 0;
                    $childPC   = 0;



                    if(count($objTargetAreaDB) > 0){
                        foreach($objTargetAreaDB as $targetArea){
                            if($targetArea->area_id == $areaId && $targetArea->goal_id == $ila->goal_id){
                                $targetValue = $targetArea->target_value;
                                $childCIP    = $targetArea->cal_implement_point;
                                $childIP     = $targetArea->implement_point;
                                $childPC     = $targetArea->real_percent;
                                break;
                            }
                        }
                        if($ila->parent_id == 0 ){
                            foreach($objTargetAreaDB as $targetArea1){
                                if($targetArea1->area_id == $areaId && $targetArea1->parent_id == $ila->goal_id){
                                    $parentIP  += $targetArea1->implement_point;
                                    $parentCIP += $targetArea1->cal_implement_point;
                                }
                            }
                        }

                    }

                    $parentPC = ($ila->benchmark != 0) ? $parentIP / $ila->benchmark : 0;

                    $arrOneTAs[$iInside]['target_value']       = $targetValue;
                    $arrOneTAs[$iInside]['company_id']         = $ila->company_id;
                    $arrOneTAs[$iInside]['area_id']            = $ila->area_id;
                    $arrOneTAs[$iInside]['goal_id']            = $ila->goal_id;
                    $arrOneTAs[$iInside]['month']              = $ila->month;
                    $arrOneTAs[$iInside]['year']               = $ila->year;
                    $arrOneTAs[$iInside]['important_level']    = $ila->important_level;
                    $arrOneTAs[$iInside]['benchmark']          = $ila->benchmark;
                    $arrOneTAs[$iInside]['cal_benchmark']      = $ila->cal_benchmark;
                    $arrOneTAs[$iInside]['goal_level']         = $ila->goal_level;
                    $arrOneTAs[$iInside]['goal_code']          = $ila->goal_code;
                    $arrOneTAs[$iInside]['parent_id']          = $ila->parent_id;
                    $arrOneTAs[$iInside]['goal_name']          = $ila->goal_name;
                    $arrOneTAs[$iInside]['percent_complete']   = ($ila->parent_id == 0) ? $parentPC : $childPC;
                    $arrOneTAs[$iInside]['cal_implement_point']= ($ila->parent_id == 0) ? $parentCIP : $childCIP;
                    $arrOneTAs[$iInside]['implement_point']    = ($ila->parent_id == 0) ? $parentIP : $childIP;

                    $iInside++;
                }
            }

            $endIndexColumn = \PHPExcel_Cell::columnIndexFromString($endLabelColumn);
            $uFirstLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn);
            $uLastLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn + 6);

            /**
             * $uLabelTA  : Target Value
             * $uLabelIL  : Important level
             * $uLabelBM  : Benchmark
             * $uLabelCBM : Calculator Benchmark
             * $uLabelCIP : Calculator Implement point
             * $uLabelIP  : Implement Point
             * $uLabelPC  : Percent complete
             */
            $column    = $endIndexColumn;
            $uLabelTA  = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIL  = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelCBM = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelCIP = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelBM  = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelIP  = \PHPExcel_Cell::stringFromColumnIndex($column++);
            $uLabelPC  = \PHPExcel_Cell::stringFromColumnIndex($column++);

            $arrPercentColumn[] = $uLabelPC;

            #Set width for column
            $hashColumn = array(
              1 => $uLabelTA
            , 2 => $uLabelIL
            , 3 => $uLabelCBM
            , 4 => $uLabelCIP
            , 5 => $uLabelBM
            , 6 => $uLabelIP
            , 7 => $uLabelPC
            );
            $count = 0;
            foreach ($hashColumn as $key => $value) {
                $dimension = 0;
                switch ($value) {
                    case $uLabelBM:
                    case $uLabelCBM:
                    case $uLabelCIP:
                    case $uLabelIP:
                    case $uLabelPC:
                        $dimension = 12;
                        break;
                    case $uLabelIL:
                        $dimension = 8;
                        break;
                    case $uLabelTA:
                        $dimension = 15;
                        break;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                $count++;
            }

            $objPHPExcel->getActiveSheet()->mergeCells($uFirstLabel . $indexHeader . ':' . $uLastLabel . $indexHeader);

            #Set value title
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . $indexHeader, '['.$daArea['areaCode'] . '] ' . $daArea['areaName']);
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelTA . ($indexHeader + 1), 'Kế hoạch (Chỉ tiêu)');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelIL . ($indexHeader + 1), 'Tỷ trọng');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelCBM . ($indexHeader + 1), 'Điểm chuẩn KPI');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelCIP . ($indexHeader + 1), 'Điểm thực hiện KPI');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelBM . ($indexHeader + 1), 'Điểm chuẩn');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelIP. ($indexHeader + 1), 'Điểm thực hiện');
            $objPHPExcel->getActiveSheet()->setCellValue($uLabelPC . ($indexHeader + 1), 'Tỷ lệ đạt');

            foreach ($arrOneTAs as $dTA) {
                $indexRow = 0;

                $taGoalId         = $dTA['goal_id'];
                $taTargetValue    = $dTA['target_value'];
                $taImportantLevel = $dTA['important_level'];
                $taBenchmark      = $dTA['benchmark'];
                $taCalBenchmark   = $dTA['cal_benchmark'];
                $taIP             = $dTA['implement_point'];
                $taCIP            = $dTA['cal_implement_point'];
                $taPC             = $dTA['percent_complete'];

                $taImportantLevel = ($taImportantLevel != 0) ? $taImportantLevel : '-';
                $taBenchmark      = ($taBenchmark != 0) ? $taBenchmark : '-';
                $taCalBenchmark   = ($taCalBenchmark != 0) ? $taCalBenchmark : '-';
                $taTargetValue    = ($taTargetValue != 0) ? $taTargetValue : '-';
                $taIP             = ($taIP != 0) ? $taIP : '-';
                $taCIP            = ($taCIP != 0) ? $taCIP : '-';
                $taPC             = ($taPC != 0) ? $taPC : '-';

                foreach($arrPRowGoal as $aPRow){
                    if($aPRow['goalId'] == $taGoalId){
                        $indexRow = $aPRow['numRow'];
                    }
                }
                if($indexRow == 0){
                    foreach($arrRowGoal as $aRow){
                        if($aRow['goalId'] == $taGoalId){
                            $indexRow = $aRow['numRow'];
                        }
                    }
                }

                $objPHPExcel->getActiveSheet()
                    ->setCellValue($uLabelTA . $indexRow, $taTargetValue)
                    ->setCellValue($uLabelIL . $indexRow, $taImportantLevel)
                    ->setCellValue($uLabelCBM . $indexRow, $taCalBenchmark)
                    ->setCellValue($uLabelCIP . $indexRow, $taCIP)
                    ->setCellValue($uLabelBM . $indexRow, $taBenchmark)
                    ->setCellValue($uLabelIP . $indexRow, $taIP)
                    ->setCellValue($uLabelPC . $indexRow, $taPC)
                ;
                //$objPHPExcel->getActiveSheet()->getStyle($uLabelTA . $indexRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                //$objPHPExcel->getActiveSheet()->getStyle($uLabelCBM . $indexRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            }
            $endLabelColumn = $uLastLabel;
        }

        /* ************************************************************************************************************
         * format template
         * ************************************************************************************************************/

        $preEndLabelColumn = excelUtils::getLabelColumn((excelUtils::getIndexColumn($endLabelColumn)-2));
        excelUtils::formatCell($objPHPExcel,'G10:'.$preEndLabelColumn.$lastRow, 1, excelUtils::STYLE_NUMBER);
        excelUtils::formatCell($objPHPExcel,'J10:J'.$lastRow, 1, \PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

        foreach($arrPercentColumn as $pCol){
            excelUtils::formatCell($objPHPExcel,$pCol.'10:'.$pCol.$lastRow, 1, \PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
        }

        $objPHPExcel->getActiveSheet()->getStyle('F10:'.$endLabelColumn.$lastRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$endLabelColumn.$lastRow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$endLabelColumn.$lastRow)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$endLabelColumn.'9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A8:'.$endLabelColumn.'9')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A8:'.$endLabelColumn.'9')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A8:'.$endLabelColumn.'9')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB(commonUtils::COLOR_DARK);

        foreach($arrPRowGoal as $aPRow){
            $objPHPExcel->getActiveSheet()->getRowDimension($aPRow['numRow'])->setRowHeight(17);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$aPRow['numRow'].':'.$endLabelColumn.$aPRow['numRow'])->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$aPRow['numRow'].':'.$endLabelColumn.$aPRow['numRow'])->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$aPRow['numRow'].':'.$endLabelColumn.$aPRow['numRow'])->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB(commonUtils::COLOR_GOAL);
        }

        #Draw child rows

        $styleChildRows = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        foreach($arrRowGoal as $aRow){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$aRow['numRow'].':'.$endLabelColumn.$aRow['numRow'])->applyFromArray($styleChildRows);
        }

        $styleLast = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $lastRow . ':' . $endLabelColumn . $lastRow)->applyFromArray($styleLast);
        /***************************************************************************************************************/
        /* Các công thức tính toán:*/
        $rowCalculate = $lastRow + 3;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $rowCalculate, '* Các công thức tính toán:');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCalculate)->getFont()->setBold(true);
        $rowCalculate++;
        $arrFormulaGoalArea = commonUtils::arrFormulaGoalArea($rowCalculate, 4);
        $i = $rowCalculate;
        foreach($arrFormulaGoalArea as $formula){
            $i++;
            $indexRow = $i;
            $nameFormula = $formula['name'];
            $description = $formula['description'];

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $indexRow, $nameFormula)
                ->setCellValue('C' . $indexRow, $description)
            ;
            $first = substr(trim($nameFormula),0,1);
            if($first == '-'){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $indexRow)->getFont()->setBold(true);
            }
        }
        /***************************************************************************************************************/


        #Output
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);
        $fileName = 'QuanLiKeHoachCuaToQuanHuyen';
        $this->outputFile($fileName, $objPHPExcel);
    }

    /**
     * @exportGoalPosition $companyId
     * @param $areaId
     * @param $positionId
     * @param $userId
     * @param $goalId
     * @param $goalType
     * @param $year
     * @param $month
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \PHPExcel_Exception
     * Export goal of employee or list of employee
     */
    public function exportGoalEmployee($companyId, $areaId, $positionId, $userId, $goalId, $goalType, $year, $month)
    {

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/blank.xlsx");
        /* *************************************************************************************************************
         * check data before export
         * redirect default page will user override URL not standard URL
         * redirect of user choose if data not valid
         * ************************************************************************************************************/
        if(
            !is_numeric($companyId)
            || !is_numeric($areaId)
            || !is_numeric($positionId)
            || !is_numeric($userId)
            || !is_numeric($goalId)
            || !is_numeric($goalType)
            || !is_numeric($year)
            || !is_numeric($month)
        ){
            Session::flash('message-errors', 'Vui lòng chọn Danh mục trước khi xuất báo cáo!');
            return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
        }

        if($companyId == 0){
            Session::flash('message-errors', 'Vui lòng chọn Phòng/Đài/MBF HCM trước khi xuất báo cáo!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$userId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($areaId == 0){
            Session::flash('message-errors', 'Vui lòng chọn Tổ/Quận/Huyện trước khi xuất báo cáo!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$userId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }
        /* *************************************************************************************************************
         * begin export
         * ************************************************************************************************************/

        $area = DB::table('area')->where('inactive', 0)->where('id', $areaId)->first();
        $company = DB::table('company')->where('inactive', 0)->where('id', $companyId)->first();

        $companyName = $company->company_name;
        $areaName    = $area->area_name;

        $sqlTargetEmployee = "
            SELECT te.*, u.code, u.name, g.parent_id
            FROM target_employee te
            LEFT JOIN users u ON u.id = te.user_id
            LEFT JOIN goal g ON g.id = te.goal_id
            WHERE te.inactive = 0
            AND te.company_id = ".$companyId."
            AND te.area_id = ".$areaId."
            AND te.year = ".$year."
            AND te.month = ".$month."
        ";

        if($positionId != 0){
            $sqlTargetEmployee .= " AND te.position_id =  ".$positionId;
        }
        if($userId != 0){
            $sqlTargetEmployee .= " AND te.user_id =  ".$userId;
        }
        if ($goalType != -1) {
            $sqlTargetEmployee .= " AND te.goal_type = '" . $goalType . "'";
        }

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlTargetEmployee .= ' AND te.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlTargetEmployee .= ' AND te.goal_id = ' . $goalId;
            }
        }

        $objTargetEmployeeDB = DB::select(DB::raw($sqlTargetEmployee));

        /** get object important level position */
        $sqlILP = "
            SELECT ilp.*, g.goal_code, g.goal_name, g.unit_id, g.goal_type, p.position_code, p.position_name, g.parent_id
            FROM important_level_position ilp
            LEFT JOIN goal g ON g.id = ilp.goal_id
            LEFT JOIN position p ON p.id = ilp.position_id
            WHERE ilp.inactive = 0
            AND ilp.company_id = ".$companyId."
            AND ilp.area_id = ".$areaId."
            AND ilp.year = ".$year."
            AND ilp.month = ".$month."
        ";

        if($positionId != 0){
            $sqlILP .= " AND ilp.position_id =  ".$positionId;
        }

        if ($goalType != -1) {
            $sqlILP .= " AND g.goal_type = '" . $goalType . "'";
        }

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlILP .= ' AND ilp.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlILP .= ' AND ilp.goal_id = ' . $goalId;
            }
        }

        $objImportantLevelPositionDB = DB::select(DB::raw($sqlILP));


        /** get object target position */
        $sqlTP = "
            SELECT tp.*, u.unit_name, g.parent_id
            FROM target_position tp
            LEFT JOIN unit u ON u.id = tp.unit_id
            LEFT JOIN goal g ON g.id = tp.goal_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND tp.area_id = ".$areaId."
            AND tp.year = ".$year."
            AND tp.month = ".$month."
        ";

        if($positionId != 0){
            $sqlTP .= " AND tp.position_id =  ".$positionId;
        }

        if ($goalType != -1) {
            $sqlTP .= " AND tp.goal_type = '" . $goalType . "'";
        }

        if ($goalId != 0) {
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id', 0)->count();
            if ($isParent == 1) {
                $sqlTP .= ' AND tp.goal_id in (SELECT id FROM  goal WHERE id = ' . $goalId . ' or parent_id = ' . $goalId . ' ) ';
            } else {
                $sqlTP .= ' AND tp.goal_id = ' . $goalId;
            }
        }

        $objTargetPositionDB = DB::select(DB::raw($sqlTP));

        if(count($objTargetEmployeeDB) == 0){
            Session::flash('message-errors', 'Vui lòng import kế hoạch nhân viên trước khi xuất báo cáo!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$userId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        /* *************************************************************************************************************
         * begin export
         * put data to array for loop
         * ************************************************************************************************************/
        $arrComparePos = array();
        foreach($objTargetEmployeeDB as $cTE){
            if(!in_array($cTE->position_id, $arrComparePos)){
                $arrComparePos[] = $cTE->position_id;
            }
        }

        $arrDistinctPosition = array();
        $iDP = 0;

        $arrDataFullTI = array(); /** array data full important level position merge target position */
        $iDF = 0;

        $sqlGoal = "
            SELECT  g.*, u.unit_name
            FROM goal g LEFT JOIN unit u ON g.unit_id = u.id
            WHERE g.inactive = 0
        ";

        $objGoalDB = DB::select(DB::raw($sqlGoal));

        foreach($objImportantLevelPositionDB as $ilp){

            if(in_array($ilp->position_id, $arrComparePos)){
                if(count($arrDistinctPosition) == 0){

                    $arrDistinctPosition[$iDP]['sheetIndex']    = $iDP;
                    $arrDistinctPosition[$iDP]['positionId']    = $ilp->position_id;
                    $arrDistinctPosition[$iDP]['positionCode']  = $ilp->position_code;
                    $arrDistinctPosition[$iDP]['positionName']  = $ilp->position_name;
                    $iDP++;

                }else{
                    $exist = 0;
                    foreach($arrDistinctPosition as $dpPosition){
                        if($dpPosition['positionId'] == $ilp->position_id){
                            $exist = 1;
                            break;
                        }
                    }

                    if($exist == 0){
                        $arrDistinctPosition[$iDP]['sheetIndex']    = $iDP;
                        $arrDistinctPosition[$iDP]['positionId']    = $ilp->position_id;
                        $arrDistinctPosition[$iDP]['positionCode']  = $ilp->position_code;
                        $arrDistinctPosition[$iDP]['positionName']  = $ilp->position_name;
                        $iDP++;
                    }
                }
            }

            $tvPosition  = 0;/** target value of position */
            $ipPosition  = 0;/** implement point of position */
            $pipPosition = 0;/** parent implement point of position */
            $imPosition  = 0;/** implement of position */
            $unitName    = "";/** unit name */

            foreach($objGoalDB as $goal){
                if($goal->id == $ilp->goal_id){
                    $unitName = $goal->unit_name;
                    break;
                }
            }

            foreach($objTargetPositionDB as $targetPosition){
                if(
                    $targetPosition->position_id == $ilp->position_id
                    && $targetPosition->goal_id == $ilp->goal_id
                ){
                    $tvPosition = $targetPosition->target_value;
                    $ipPosition = $targetPosition->implement_point;
                    $imPosition = $targetPosition->implement;
                    //$unitName   = $targetPosition->unit_name;
                    break;
                }
            }
            if($ilp->parent_id == 0){
                foreach($objTargetPositionDB as $targetPosition){
                    if(
                        $targetPosition->position_id == $ilp->position_id
                        && $targetPosition->parent_id == $ilp->goal_id
                    ){
                        $pipPosition += $targetPosition->implement_point;
                    }
                }
            }

            $arrDataFullTI[$iDF]['positionId']      = $ilp->position_id;
            $arrDataFullTI[$iDF]['goalId']          = $ilp->goal_id;
            $arrDataFullTI[$iDF]['goalCode']        = $ilp->goal_code;
            $arrDataFullTI[$iDF]['goalName']        = $ilp->goal_name;
            $arrDataFullTI[$iDF]['parentId']        = $ilp->parent_id;
            $arrDataFullTI[$iDF]['goalType']        = commonUtils::renderGoalTypeName($ilp->goal_type);
            $arrDataFullTI[$iDF]['unitName']        = $unitName;
            $arrDataFullTI[$iDF]['targetValue']     = $tvPosition;
            $arrDataFullTI[$iDF]['importantLevel']  = $ilp->important_level;
            $arrDataFullTI[$iDF]['benchmark']       = $ilp->benchmark;
            $arrDataFullTI[$iDF]['calBenchmark']    = $ilp->cal_benchmark;
            $arrDataFullTI[$iDF]['implement']       = $imPosition;
            $arrDataFullTI[$iDF]['implementPoint']  = ($ilp->parent_id == 0) ? $pipPosition : $ipPosition;
            $iDF++;


        }

        $arrDataExcel = array();
        $iDE = 0;

        foreach($arrDistinctPosition as $dpPosition){

            $dpSheetIndex   = $dpPosition['sheetIndex'];
            $dpPositionId   = $dpPosition['positionId'];
            $dpPositionCode = $dpPosition['positionCode'];
            $dpPositionName = $dpPosition['positionName'];

            $arrETI = array(); /** Array for each data of important level position with position code*/
            $iET = 0;

            $arrETE = array(); /** Array for each target employee */
            $iE = 0;

            $arrParentILP = array(); /** Array for important level parent goal */
            $iPI = 0;

            $arrDistinctEmployee = array();
            $iDI = 0;

            foreach($arrDataFullTI as $dfTI){
                if($dfTI['positionId'] == $dpPositionId){
                    $arrETI[$iET]['goalId']          = $dfTI['goalId'];
                    $arrETI[$iET]['goalCode']        = $dfTI['goalCode'];
                    $arrETI[$iET]['goalName']        = $dfTI['goalName'];
                    $arrETI[$iET]['parentId']        = $dfTI['parentId'];
                    $arrETI[$iET]['goalType']        = $dfTI['goalType'];
                    $arrETI[$iET]['unitName']        = $dfTI['unitName'];
                    $arrETI[$iET]['targetValue']     = $dfTI['targetValue'];
                    $arrETI[$iET]['importantLevel']  = $dfTI['importantLevel'];
                    $arrETI[$iET]['calBenchmark']    = $dfTI['calBenchmark'];
                    $arrETI[$iET]['implement']       = $dfTI['implement'];
                    $arrETI[$iET]['implementPoint']  = $dfTI['implementPoint'];
                    $iET++;

                    if($dfTI['parentId'] == 0){
                        $arrParentILP[$iPI]['goalId']         = $dfTI['goalId'];
                        $arrParentILP[$iPI]['importantLevel'] = $dfTI['importantLevel'];
                        $arrParentILP[$iPI]['benchmark']      = $dfTI['benchmark'];
                        $iPI++;
                    }
                }
            }

            foreach($objTargetEmployeeDB as $targetEmployee){
                if($targetEmployee->position_id == $dpPositionId){
                    $arrETE[$iE]['goalId']          = $targetEmployee->goal_id;
                    $arrETE[$iE]['employeeId']      = $targetEmployee->user_id;
                    $arrETE[$iE]['employeeCode']    = $targetEmployee->code;
                    $arrETE[$iE]['employeeName']    = $targetEmployee->name;
                    $arrETE[$iE]['parentId']        = $targetEmployee->parent_id;
                    $arrETE[$iE]['targetValue']     = $targetEmployee->target_value;
                    $arrETE[$iE]['importantLevel']  = $targetEmployee->important_level;
                    $arrETE[$iE]['benchmark']       = $targetEmployee->benchmark;
                    $arrETE[$iE]['implement']       = $targetEmployee->implement;
                    $arrETE[$iE]['implementPoint']  = $targetEmployee->implement_point;
                    $iE++;

                    if(count($arrDistinctEmployee) == 0){

                        $arrDistinctEmployee[$iDI]['employeeId']    = $targetEmployee->user_id;
                        $arrDistinctEmployee[$iDI]['employeeCode']  = $targetEmployee->code;
                        $arrDistinctEmployee[$iDI]['employeeName']  = $targetEmployee->name;
                        $iDI++;

                    }else{
                        $exist = 0;
                        foreach($arrDistinctEmployee as $deEmployee){
                            if($deEmployee['employeeId'] == $targetEmployee->user_id){
                                $exist = 1;
                                break;
                            }
                        }

                        if($exist == 0){
                            $arrDistinctEmployee[$iDI]['employeeId']    = $targetEmployee->user_id;
                            $arrDistinctEmployee[$iDI]['employeeCode']  = $targetEmployee->code;
                            $arrDistinctEmployee[$iDI]['employeeName']  = $targetEmployee->name;
                            $iDI++;
                        }
                    }

                }
            }

            $arrDataExcel[$iDE]['sheetIndex']           = $dpSheetIndex;
            $arrDataExcel[$iDE]['positionId']           = $dpPositionId;
            $arrDataExcel[$iDE]['positionCode']         = $dpPositionCode;
            $arrDataExcel[$iDE]['positionName']         = $dpPositionName;
            $arrDataExcel[$iDE]['arrETE']               = $arrETE;
            $arrDataExcel[$iDE]['arrETI']               = $arrETI;
            $arrDataExcel[$iDE]['arrParentILP']         = $arrParentILP;
            $arrDataExcel[$iDE]['arrDistinctEmployee']  = $arrDistinctEmployee;
            $iDE++;
        }

        /* *************************************************************************************************************
         * begin export
         * ************************************************************************************************************/

        $indexHeader = 10;
        $subLoopHeader = $indexHeader + 1;

        foreach($arrDataExcel as $dataExcel){

            $deSheetIndex           = $dataExcel['sheetIndex'];
            $dePositionId           = $dataExcel['positionId'];
            $dePositionCode         = $dataExcel['positionCode'];
            $dePositionName         = $dataExcel['positionName'];
            $deArrETE               = $dataExcel['arrETE'];
            $deArrETI               = $dataExcel['arrETI'];
            $deArrParentILP         = $dataExcel['arrParentILP'];
            $deArrDistinctEmployee  = $dataExcel['arrDistinctEmployee'];

            if($deSheetIndex != 0){
                $objPHPExcel->createSheet($deSheetIndex);
            }

            $objPHPExcel->setActiveSheetIndex($deSheetIndex);
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Calibri');
            $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
            $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(82);
            $objPHPExcel->getActiveSheet()->setTitle($this->convertNameSheet($dePositionCode));

            $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
            $objPHPExcel->getActiveSheet()->setCellValue('A2', 'KẾ HOẠCH/CHỈ TIÊU PHÂN BỔ CHO CÁ NHÂN');

            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'Phòng/Đài/MBF HCM:');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'Tổ/Quận/Huyện:');
            $objPHPExcel->getActiveSheet()->setCellValue('D6', 'Chức danh:');
            $objPHPExcel->getActiveSheet()->setCellValue('D7', 'Năm:');
            $objPHPExcel->getActiveSheet()->setCellValue('D8', 'Tháng:');
            $objPHPExcel->getActiveSheet()->getStyle('D4:D8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->setCellValue('E4', $companyName);
            $objPHPExcel->getActiveSheet()->setCellValue('E5', $areaName);
            $objPHPExcel->getActiveSheet()->setCellValue('E6', $dePositionName);
            $objPHPExcel->getActiveSheet()->setCellValue('E7', $year);
            $objPHPExcel->getActiveSheet()->setCellValue('E8', $month);
            $objPHPExcel->getActiveSheet()->getStyle('E4:E8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);



            $objPHPExcel->getActiveSheet()->mergeCells('A'.$indexHeader.':A'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('B'.$indexHeader.':B'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('C'.$indexHeader.':C'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$indexHeader.':D'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('E'.$indexHeader.':E'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('F'.$indexHeader.':F'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('G'.$indexHeader.':G'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('H'.$indexHeader.':H'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('I'.$indexHeader.':I'.($indexHeader+1));
            $objPHPExcel->getActiveSheet()->mergeCells('J'.$indexHeader.':J'.($indexHeader+1));

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$indexHeader, 'STT')
                ->setCellValue('B'.$indexHeader, 'Mã')
                ->setCellValue('C'.$indexHeader, 'Mục tiêu')
                ->setCellValue('D'.$indexHeader, 'Loại mục tiêu')
                ->setCellValue('E'.$indexHeader, 'Đơn vị tính')
                ->setCellValue('F'.$indexHeader, 'Kế hoạch')
                ->setCellValue('G'.$indexHeader, 'Tỷ trọng')
                ->setCellValue('H'.$indexHeader, 'Điểm chuẩn KPI')
                ->setCellValue('I'.$indexHeader, 'Thực hiện')
                ->setCellValue('J'.$indexHeader, 'Điểm thực hiện')
            ;

            $arrParent   = array();
            $arrChildren = array();

            $iCG = 0;
            $iPG = 0;

            foreach($deArrETI as $eti){

                $etiGoalId          = $eti['goalId'];
                $etiGoalCode        = $eti['goalCode'];
                $etiGoalName        = $eti['goalName'];
                $etiParentId        = $eti['parentId'];
                $etiGoalType        = $eti['goalType'];
                $etiUnitName        = $eti['unitName'];
                $etiTargetValue     = $eti['targetValue'];
                $etiImportantLevel  = $eti['importantLevel'];
                $etiCalBenchmark    = $eti['calBenchmark'];
                $etiImplement       = $eti['implement'];
                $etiImplementPoint  = $eti['implementPoint'];

                if($etiParentId == 0){
                    $etiParentIP = 0;
                    foreach($deArrETI as $insideETI){
                        if($insideETI['parentId'] == $etiGoalId){
                            $etiParentIP += $insideETI['implementPoint'];
                        }
                    }

                    $arrParent[$iPG]['goalId']          = $etiGoalId;
                    $arrParent[$iPG]['goalCode']        = $etiGoalCode;
                    $arrParent[$iPG]['goalName']        = $etiGoalName;
                    $arrParent[$iPG]['parentId']        = $etiParentId;
                    $arrParent[$iPG]['importantLevel']  = $etiImportantLevel;
                    $arrParent[$iPG]['calBenchmark']    = $etiCalBenchmark;
                    $arrParent[$iPG]['implement']       = 0;
                    $arrParent[$iPG]['implementPoint']  = $etiParentIP;
                    $iPG++;
                }else{
                    $arrChildren[$iCG]['goalId']          = $etiGoalId;
                    $arrChildren[$iCG]['goalCode']        = $etiGoalCode;
                    $arrChildren[$iCG]['goalName']        = $etiGoalName;
                    $arrChildren[$iCG]['parentId']        = $etiParentId;
                    $arrChildren[$iCG]['goalType']        = $etiGoalType;
                    $arrChildren[$iCG]['unitName']        = $etiUnitName;
                    $arrChildren[$iCG]['targetValue']     = $etiTargetValue;
                    $arrChildren[$iCG]['importantLevel']  = $etiImportantLevel;
                    $arrChildren[$iCG]['calBenchmark']    = $etiCalBenchmark;
                    $arrChildren[$iCG]['implement']       = $etiImplement;
                    $arrChildren[$iCG]['implementPoint']  = $etiImplementPoint;
                    $iCG++;
                }
            }

            $beginData = 12;
            $arrPRowGoal = array();
            $arrRowGoal = array();
            $iRG = 0;
            $iPRG = 0;
            $i = 1;

            foreach ($arrParent as $prow) {
                #Parent row
                //$objPHPExcel->getActiveSheet()->mergeCells('A' . $beginData . ':E' . $beginData);
                //$objPHPExcel->getActiveSheet()->getStyle('A' . $beginData . ':H' . $beginData)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                //$objPHPExcel->getActiveSheet()->getStyle('G' . $beginData.':H' . $beginData)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$beginData.':E'.$beginData);

                $hpImportantLevel     = ($prow['importantLevel'] != 0) ? $prow['importantLevel'] : '-';
                $hpCalBenchmark       = ($prow['calBenchmark'] != 0) ? $this->formatNumber($prow['calBenchmark']) : '-';
                $hpImplementPoint     = ($prow['implementPoint'] != 0) ? $this->formatNumber($prow['implementPoint']) : '-';

                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $beginData, $prow['goalName'])
                    ->setCellValue('F' . $beginData, '-')
                    ->setCellValue('G' . $beginData, $hpImportantLevel)
                    ->setCellValue('H' . $beginData, $hpCalBenchmark)
                    ->setCellValue('I' . $beginData, '-')
                    ->setCellValue('J' . $beginData, $hpImplementPoint)
                ;
                $arrPRowGoal[$iPRG]['numRow'] = $beginData;
                $arrPRowGoal[$iPRG]['goalId'] = $prow['goalId'];
                $iPRG++;
                $childRow = $beginData + 1;
                foreach ($arrChildren as $crow) {
                    if ($crow['parentId'] == $prow['goalId']) {

                        //$objPHPExcel->getActiveSheet()->getStyle('D' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        //$objPHPExcel->getActiveSheet()->getStyle('A' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        //$objPHPExcel->getActiveSheet()->getStyle('H' . $childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        #Input array with row and goal id for loop of each user bellow
                        $arrRowGoal[$iRG]['numRow'] = $childRow;
                        $arrRowGoal[$iRG]['goalId'] = $crow['goalId'];
                        $iRG++;

                        $hcTargetValue        = ($crow['targetValue'] != 0) ? $this->formatNumber($crow['targetValue']) : '-';
                        $hcImportantLevel     = ($crow['importantLevel'] != 0) ? $crow['importantLevel'] : '-';
                        $hcBenchmark          = ($crow['calBenchmark'] != 0) ? $this->formatNumber($crow['calBenchmark']) : '-';
                        $hcImplement          = ($crow['implement'] != 0) ? $this->formatNumber($crow['implement']) : '-';
                        $hcImplementPoint     = ($crow['implementPoint'] != 0) ? $this->formatNumber($crow['implementPoint']) : '-';

                        $objPHPExcel->getActiveSheet()
                            ->setCellValue('A' . $childRow, $i++)
                            ->setCellValue('B' . $childRow, $crow['goalCode'])
                            ->setCellValue('C' . $childRow, $crow['goalName'])
                            ->setCellValue('D' . $childRow, $crow['goalType'])
                            ->setCellValue('E' . $childRow, $crow['unitName'])
                            ->setCellValue('F' . $childRow, $hcTargetValue)
                            ->setCellValue('G' . $childRow, $hcImportantLevel)
                            ->setCellValue('H' . $childRow, $hcBenchmark)
                            ->setCellValue('I' . $childRow, $hcImplement)
                            ->setCellValue('J' . $childRow, $hcImplementPoint)
                        ;

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $childRow.':B'.$childRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $childRow++;
                    }
                }
                $beginData = $childRow;
            }
            $lastRow = $beginData - 1;
            ##############################################################################################################
            #Begin create dynamic employee section
            $endLabelColumn = 'J';
            $startLoopColumn = 'K';

            foreach($deArrDistinctEmployee as $deEmp){

                $deEmpId    = $deEmp['employeeId'];
                $deEmpCode  = $deEmp['employeeCode'];
                $deEmpName  = $deEmp['employeeName'];

                $endIndexColumn = \PHPExcel_Cell::columnIndexFromString($endLabelColumn);
                $uFirstLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn);
                $uLastLabel = \PHPExcel_Cell::stringFromColumnIndex($endIndexColumn + 5);

                /**
                 * $uLabelTA : Target Value
                 * $uLabelIL : Important level
                 * $uLabelBM : Benchmark
                 * $uLabelIM : Implement
                 * $uLabelIP : Implement point
                 * $uLabelPC : Percent complete
                 */
                $column   = $endIndexColumn;
                $uLabelTA = \PHPExcel_Cell::stringFromColumnIndex($column++);
                $uLabelIL = \PHPExcel_Cell::stringFromColumnIndex($column++);
                $uLabelBM = \PHPExcel_Cell::stringFromColumnIndex($column++);
                $uLabelIM = \PHPExcel_Cell::stringFromColumnIndex($column++);
                $uLabelIP = \PHPExcel_Cell::stringFromColumnIndex($column++);
                $uLabelPC = \PHPExcel_Cell::stringFromColumnIndex($column++);

                #Set width for column
                $loopColumn = array(
                  1 => $uLabelTA
                , 2 => $uLabelIL
                , 3 => $uLabelBM
                , 4 => $uLabelIM
                , 5 => $uLabelIP
                , 6 => $uLabelPC
                );
                $count = 0;
                foreach ($loopColumn as $key => $value) {
                    $dimension = 0;
                    switch ($value) {
                        case $uLabelBM:
                        case $uLabelIL:
                        case $uLabelIP:
                        case $uLabelPC:
                            $dimension = 10;
                            break;
                        case $uLabelTA:
                        case $uLabelIM:
                            $dimension = 22;
                            break;
                    }
                    $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                    $count++;
                }

                $objPHPExcel->getActiveSheet()->mergeCells($uLabelTA.$indexHeader.':'.$uLabelPC.$indexHeader);


                $objPHPExcel->getActiveSheet()
                    ->setCellValue($uLabelTA.$indexHeader, '['.$deEmpCode.'] '.$deEmpName)
                    ->setCellValue($uLabelTA.$subLoopHeader, 'Kế hoạch')
                    ->setCellValue($uLabelIL.$subLoopHeader, 'Tỷ trọng')
                    ->setCellValue($uLabelBM.$subLoopHeader, 'Điểm chuẩn')
                    ->setCellValue($uLabelIM.$subLoopHeader, 'Thực hiện')
                    ->setCellValue($uLabelIP.$subLoopHeader, 'Điểm thực hiện')
                    ->setCellValue($uLabelPC.$subLoopHeader, 'Tỷ lệ đạt')

                ;

                foreach($deArrParentILP as $parentILP){
                    $pIndex = 0;
                    foreach($arrPRowGoal as $prGoal){
                        if($prGoal['goalId'] == $parentILP['goalId']){
                            $pIndex = $prGoal['numRow'];
                            break;
                        }
                    }
                    if($pIndex != 0){
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($uLabelTA.$pIndex, '-')
                            ->setCellValue($uLabelIL.$pIndex, $parentILP['importantLevel'])
                            ->setCellValue($uLabelBM.$pIndex, $parentILP['benchmark'])
                            ->setCellValue($uLabelIM.$pIndex, '-')
                            ->setCellValue($uLabelIP.$pIndex, '-')
                            ->setCellValue($uLabelPC.$pIndex, '-')

                        ;
                    }

                }

                foreach($deArrETE as $ete){
                   if($ete['employeeId'] == $deEmpId){
                       $cIndex = 0;
                       foreach($arrRowGoal as $crGoal){
                           if($crGoal['goalId'] == $ete['goalId']){
                               $cIndex = $crGoal['numRow'];
                               break;
                           }
                       }

                       $cTargetValue        = ($ete['targetValue'] != 0) ? $this->formatNumber($ete['targetValue']) : '-';
                       $cImportantLevel     = ($ete['importantLevel'] != 0) ? $ete['importantLevel'] : '-';
                       $cBenchmark          = ($ete['benchmark'] != 0) ? $this->formatNumber($ete['benchmark']) : '-';
                       $cImplement          = ($ete['implement'] != 0) ? $this->formatNumber($ete['implement']) : '-';
                       $cImplementPoint     = ($ete['implementPoint'] != 0) ? $this->formatNumber($ete['implementPoint']) : '-';
                       $cPercentComplete    = ($ete['benchmark'] != 0) ? $this->formatNumber(($ete['implementPoint'] / $ete['benchmark']) * 100).'%' : 0;
                       $cPercentComplete    = ($cPercentComplete != 0) ? $cPercentComplete : '-';

                       if($cIndex != 0){
                           $objPHPExcel->getActiveSheet()
                               ->setCellValue($uLabelTA.$cIndex, $cTargetValue)
                               ->setCellValue($uLabelIL.$cIndex, $cImportantLevel)
                               ->setCellValue($uLabelBM.$cIndex, $cBenchmark)
                               ->setCellValue($uLabelIM.$cIndex, $cImplement)
                               ->setCellValue($uLabelIP.$cIndex, $cImplementPoint)
                               ->setCellValue($uLabelPC.$cIndex, $cPercentComplete)

                           ;
                       }
                   }
                }

                $endLabelColumn = $uLabelPC;
            }

            /* *********************************************************************************************************
             * Format template here
             * ********************************************************************************************************/
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$endLabelColumn.$lastRow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$indexHeader.':'.$endLabelColumn.$subLoopHeader)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$endLabelColumn.$subLoopHeader)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->freezePane('K'.($subLoopHeader + 1));
            $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$indexHeader.':'.$endLabelColumn.$subLoopHeader)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('F'.($subLoopHeader + 1).':'.$endLabelColumn.$lastRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(27);
            $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(15);

            excelUtils::fillBackGroundColor($objPHPExcel, 'A10:'.$endLabelColumn.$subLoopHeader, excelUtils::COLOR_DARK);

            #Set with for column
            $hashColumn = range('A', 'J');
            $count = 0;
            foreach ($hashColumn as $key => $value) {
                $dimension = 0;
                switch ($value) {
                    case 'A':
                        $dimension = 8;
                        break;
                    case 'B':
                        $dimension = 10;
                        break;
                    case 'C':
                        $dimension = 51;
                        break;
                    case 'D':
                        $dimension = 15;
                        break;
                    case 'E':
                        $dimension = 12;
                        break;
                    case 'F':
                    case 'I':
                        $dimension = 22;
                        break;
                    case 'G':
                    case 'H':
                    case 'J':
                        $dimension = 10;
                        break;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                $count++;
            }

            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )

                )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A'.$indexHeader.':'.$endLabelColumn.$subLoopHeader)->applyFromArray($styleArray);
            #Draw parent rows
            for ($iDP = 0; $iDP < count($arrPRowGoal); $iDP++) {
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . $arrPRowGoal[$iDP]['numRow']. ':' . $endLabelColumn . $arrPRowGoal[$iDP]['numRow'])
                    ->getFill()
                    ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFFF00');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $arrPRowGoal[$iDP]['numRow'] . ':' . $endLabelColumn . $arrPRowGoal[$iDP]['numRow'])->applyFromArray($styleArray);
            }

            #Draw child rows

            $styleChildRows = array(
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_DOTTED
                    ),
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_DOTTED
                    ),
                    'left' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    ),
                    'right' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    ),
                    'vertical' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            for ($iDC = 0; $iDC < count($arrRowGoal); $iDC++) {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $arrRowGoal[$iDC]['numRow'] . ':' . $endLabelColumn . $arrRowGoal[$iDC]['numRow'])->applyFromArray($styleChildRows);
            }

            $styleLast = array(
                'borders' => array(
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )

                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . $lastRow . ':' . $endLabelColumn . $lastRow)->applyFromArray($styleLast);

            /*------------------------------------------------------------------------------------------------------------*/
            /* Các công thức tính toán:*/
            $rowCalculate = $lastRow + 3;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $rowCalculate, '* Các công thức tính toán:');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCalculate)->getFont()->setBold(true);
            $rowCalculate++;

            $arrFormulaGoalArea = commonUtils::arrFormulaGoalArea($rowCalculate, 6);

            $i = $rowCalculate;
            foreach($arrFormulaGoalArea as $formula){
                $i++;
                $indexRow = $i;
                $nameFormula = $formula['name'];
                $description = $formula['description'];

                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $indexRow, $nameFormula)
                    ->setCellValue('C' . $indexRow, $description);

                $first = substr(trim($nameFormula),0,1);
                if($first == '-'){
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $indexRow)->getFont()->setBold(true);
                }
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(10);
            /*------------------------------------------------------------------------------------------------------------*/
        }

        $objPHPExcel->setActiveSheetIndex(0);/** target the first sheet */
        $fileName = 'QuanLyKeHoachCuaNhanVien';
        $this->outputFile($fileName, $objPHPExcel);
    }

    /**
     * @param $fromMonth
     * @param $toMonth
     * @param $fromYear
     * @param $toYear
     * @param $company
     * @throws \PHPExcel_Exception
     */
    public function reportCompanyByTimes($company, $applyDate){
        //make query export into excel file to get data
        $company = explode(",",$company);
        $select = "select c.company_code, c.company_name,
                          sum(ilc.benchmark) as bm, sum(ilc.implement_point) as ip
                    from important_level_company as ilc
                    join company as c on c.id = ilc.company_id
                    join goal as g on g.id = ilc.goal_id
                    where ilc.inactive = 0 and g.parent_id > 0
                    AND ilc.apply_date = '".$applyDate."' ";
        if($company != null){
            $select .= ' and ( ';
            for($c=0; $c<count($company); $c++){
                if($c == (count($company)-1)){
                    $select .= " c.company_code = '". $company[$c] ."' )";
                } else {
                    $select .= " c.company_code = '". $company[$c] ."' or ";
                }
            }
        } else {
            $select .= ' and 0 ';
        }
        $select .= ' GROUP BY c.company_code, c.company_name
                     ORDER BY ip desc';
        // for chart Goal
        $data = DB::select(DB::raw($select));

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportImplementByCompany.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        if(count($data) > 0){
            $objPHPExcel->getActiveSheet()->setCellValue('C5', commonUtils::formatDate($applyDate));
            $index = 1;
            $firstRow = 9;
            $startRow = 9;
            foreach($data as $da){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $index++)
                                                ->setCellValue('B'.$startRow, $da->company_name)
                                                ->setCellValue('C'.$startRow, $da->ip)
                                                ->setCellValue('D'.$startRow, '=rank(D'.$startRow.',D'.$firstRow.':D'.$startRow.')');
                $startRow++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('A'.$firstRow.':D'.($startRow-1))->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$firstRow.':C'.($startRow-1))->getNumberFormat()->setFormatCode('#,##0.000');
        }
        excelUtils::setZoomSheet($objPHPExcel, 80);
        $fileName = 'BaoCaoThucHienCuaPhongDaiMBFHCM';
        $this->outputFile($fileName, $objPHPExcel);
    }

    public function reportEmpByTimes($check, $month, $year, $fromMonth, $toMonth, $fromYear, $toYear, $company, $area, $position, $group, $emp, $goal, $empChoose){
        $emp = explode(",", $emp);
        $goal = explode(",", $goal);
        $area = explode(",", $area);
        $group = explode(",", $group);
        $positionF = explode(",",$position);
        $position = array();
        for($p=0; $p<count($positionF); $p++){
            $positionFinal = '';
            $arrName = str_split($positionF[$p]);
            foreach($arrName as $n){
                if($n == '*'){
                    $positionFinal.= '/';
                } else {
                    $positionFinal.=$n;
                }
            }
            $position[count($position)] = $positionFinal;
        }

        $selectData = "select sum(te.implement) as implement, sum(te.target_value) as targetValue,
								sum(te.implement_point) as implementPoint,
							    te.month, te.year,  area.area_name, area.area_code, company.company_name,
							    users.name, users.code, position.position_name, position.position_code,
						  		group.group_code, group.group_name
						from target_employee as te
						join goal on te.goal_id = goal.id
						join users on users.id = te.user_id
						join `group` on group.id = users.group_id
						join `position` on position.id = te.position_id
						join area on area.id = te.area_id
						join company on company.id = te.company_id
						where te.inactive = 0 and te.goal_level = 1 and company.company_code = '" . $company . "' ";

        //area
        $allArea = 0;
        if ($area != null) {
            $select = ' and ( ';
            for ($e = 0; $e < count($area); $e++) {
                if ($area[$e] == '1') {
                    $allArea = 1;
                }
                if ($e == (count($area) - 1)) {
                    $select .= " area.area_code = '" . $area[$e] . "' ) ";
                } else {
                    $select .= " area.area_code = '" . $area[$e] . "' or ";
                }
            }
            if ($allArea == 0) {
                $selectData .= $select;
            }
        } else {
            $selectData .= ' and 0 ';
        }
        //position
        $selectData .= " and ( ";
        if($position != null){
            for($p=0; $p<count($position); $p++){
                if($p == (count($position)-1)){
                    $selectData .= " position.position_code = '". $position[$p] ."' ) ";
                } else {
                    $selectData .= " position.position_code = '". $position[$p] ."' or ";
                }
            }
        } else {
            $selectData .= '0 )';
        }

        //group
        $selectData .= " and (";
        for ($p = 0; $p < count($group); $p++) {
            if ($p == (count($group) - 1)) {
                $selectData .= " group.group_code = '" . $group[$p] . "' ) ";
            } else {
                $selectData .= " group.group_code = '" . $group[$p] . "' or ";
            }
        }

        //emp
        $all = 0;
        if ($emp != null) {
            $select = ' and ( ';
            for ($e = 0; $e < count($emp); $e++) {
                if ($emp[$e] == '1') {
                    $all = 1;
                }
                if ($e == (count($emp) - 1)) {
                    $select .= " users.code = '" . $emp[$e] . "' )";
                } else {
                    $select .= " users.code = '" . $emp[$e] . "' or ";
                }
            }
            if ($all == 0) {
                $selectData .= $select;
            }
        } else {
            $selectData .= ' and 0 ';
        }

        // goal
        $selectData .= ' and ( ';
        for ($g = 0; $g < count($goal); $g++) {
            if ($g == (count($goal) - 1)) {
                $selectData .= " goal.goal_code = '" . $goal[$g] . "' )";
            } else {
                $selectData .= " goal.goal_code = '" . $goal[$g] . "' or ";
            }
        }

        //time
        if ($check == 2) {
            // time to time
            if ($fromYear == $toYear) {
                $selectData .= ' and te.year=' . $fromYear . ' and te.month <=' . $toMonth .
                    ' and te.month >=' . $fromMonth;
            } else {
                $selectData .= ' and ((te.month >= ' . $fromMonth . ' and te.year = ' . $fromYear . ') or
						(te.month <= ' . $toMonth . '  and te.year = ' . $toYear . '))
						and te.year >= ' . $fromYear . ' and te.year <= ' . $toYear;
            }

        } else {
            $selectData .= ' and te.month = ' . $month . ' and te.year = ' . $year;
        }

        $selectData .= ' group by te.month, te.year, te.company_id, te.area_id, te.position_id, te.user_id
                        order by area.id, users.name, position.id, goal.id';

        $data = DB::select(DB::raw($selectData));

        $arrayEmpPosUnique = array();
        foreach ($data as $rowDN) {
            // position-emp
            $countEPU = 0;
            foreach ($arrayEmpPosUnique as $rowEPU) {
                if (($rowEPU['position_code'] == $rowDN->position_code) && ($rowEPU['name'] == $rowDN->name)) {
                    $countEPU = 1;
                }
            }

            if ($countEPU == 0) {
                $index = count($arrayEmpPosUnique);
                $arrayEmpPosUnique[$index]['position_code'] = $rowDN->position_code;
                $arrayEmpPosUnique[$index]['position_name'] = $rowDN->position_name;
                $arrayEmpPosUnique[$index]['name'] = $rowDN->name;
                $arrayEmpPosUnique[$index]['code'] = $rowDN->code;
            }
        }

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportImplementByEmp.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        if (count($data) > 0) {
            $aN = '';
            if ($allArea == 0) {
                $are = DB::table('area')->where('inactive', 0)->get();
                for ($ra = 0; $ra < count($area); $ra++) {
                    foreach ($are as $a) {
                        if ($a->area_code == $area[$ra]) {
                            if ($ra == (count($area) - 1)) {
                                $aN .= $a->area_name;
                            } else {
                                $aN .= $a->area_name . ' - ';
                            }
                        }
                    }
                }
            } else {
                $aN = 'Tất cả';
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C4', $data[0]->company_name)->setCellValue('C5', $aN);
        } else {
            $comp = DB::table('company')->where('company_code', $company)->first();
            if ($allArea == 0) {
                $are = DB::table('area')->where('inactive', 0)->get();
                $aN = '';
                for ($ra = 0; $ra < count($area); $ra++) {
                    foreach ($are as $a) {
                        if ($a->area_code == $area[$ra]) {
                            if ($ra == (count($area) - 1)) {
                                $aN .= $a->area_name;
                            } else {
                                $aN .= $a->area_name . ' - ';
                            }
                        }
                    }
                }
            } else {
                $aN = 'Tất cả';
            }

            $objPHPExcel->getActiveSheet()->setCellValue('C4', $comp->company_name)->setCellValue('C5', $aN);
        }

        if ($check == 1 || (($fromYear == $toYear) && ($fromMonth == $toMonth))) {
            if ($check == 1) {
                $objPHPExcel->getActiveSheet()->setCellValue('C6', $month . '/' . $year);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('C6', $fromMonth . '/' . $fromYear);
            }

        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('C6', $fromMonth . '/' . $fromYear . ' - ' . $toMonth . '/' . $toYear);
        }
        $index = 0;
        $startRow = 9;
        foreach ($arrayEmpPosUnique as $rowEP) {
            $index++;
            $startRow++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $startRow, $index)
                ->setCellValue('B' . $startRow, $rowEP['name'])
                ->setCellValue('C' . $startRow, $rowEP['position_name']);
        }
        $objPHPExcel->getActiveSheet()->getStyle('A10:C' . $startRow)->applyFromArray($styleArray);

        //paint month
        if ($check == 1) {
            $objPHPExcel->getActiveSheet()->mergeCells('D8:D9');
            $objPHPExcel->getActiveSheet()->getStyle('D8:D9')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            $objPHPExcel->getActiveSheet()->getStyle('D8:D9')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('D8:D9')->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->setCellValue('D8', 'Tháng ' . $month);
        } else if (($fromYear == $toYear) && ($fromMonth == $toMonth)) {
            $objPHPExcel->getActiveSheet()->mergeCells('D8:D9');
            $objPHPExcel->getActiveSheet()->getStyle('D8:D9')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            $objPHPExcel->getActiveSheet()->getStyle('D8:D9')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('D8:D9')->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->setCellValue('D8', 'Tháng ' . $fromMonth);
        } else {
            $objPHPExcel->getActiveSheet()->freezePane('D10');
            $startColumn = 3;
            if ($fromYear == $toYear) {
                $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                for ($m = $fromMonth; $m <= $toMonth; $m++) {
                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                    $startColumn++;
                    $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . '9', $m);
                }
                $to = PHPExcel_Cell::stringFromColumnIndex($startColumn - 1);
                $objPHPExcel->getActiveSheet()->mergeCells($from . '8:' . $to . '8');
                $objPHPExcel->getActiveSheet()->setCellValue($from . '8', $fromYear);
                $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            } else if ($fromYear < $toYear) {

                $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                for ($m = $fromMonth; $m <= 12; $m++) {
                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                    $startColumn++;
                    $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . '9', $m);
                }
                $to = PHPExcel_Cell::stringFromColumnIndex($startColumn - 1);
                $objPHPExcel->getActiveSheet()->mergeCells($from . '8:' . $to . '8');
                $objPHPExcel->getActiveSheet()->setCellValue($from . '8', $fromYear);
                $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

                if ($toYear - $fromYear > 1) {
                    for ($y = $fromYear + 1; $y < $toYear; $y++) {
                        $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        for ($m = 1; $m <= 12; $m++) {
                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . '9', $m);
                        }
                        $to = PHPExcel_Cell::stringFromColumnIndex($startColumn - 1);
                        $objPHPExcel->getActiveSheet()->mergeCells($from . '8:' . $to . '8');
                        $objPHPExcel->getActiveSheet()->setCellValue($from . '8', $y);
                        $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                    }
                }

                $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                for ($m = 1; $m <= $toMonth; $m++) {
                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                    $startColumn++;
                    $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . '9', $m);
                }
                $to = PHPExcel_Cell::stringFromColumnIndex($startColumn - 1);
                $objPHPExcel->getActiveSheet()->mergeCells($from . '8:' . $to . '8');
                $objPHPExcel->getActiveSheet()->setCellValue($from . '8', $toYear);
                $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($from . '8:' . $to . '9')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            }
        }

        //write data
        $startRow = 10;
        if ($check == 1) {
            foreach ($arrayEmpPosUnique as $rowEP) {
                foreach ($data as $rowData) {
                    if (($rowEP['code'] == $rowData->code) &&
                        ($month == $rowData->month) && ($year == $rowData->year)
                    ) {
                        $objPHPExcel->getActiveSheet()->getStyle('D' . $startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                        $objPHPExcel->getActiveSheet()->setCellValue('D' . $startRow, round($rowData->implementPoint, 3));
                        $startRow++;
                    }
                }
            }
            $objPHPExcel->getActiveSheet()->getStyle('D10:D' . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('D10:D' . ($startRow - 1))->applyFromArray($styleArray);
        } else if (($fromYear == $toYear) && ($fromMonth == $toMonth)) {
            foreach ($arrayEmpPosUnique as $rowEP) {
                foreach ($data as $rowData) {
                    if (($rowEP['position_name'] == $rowData->position_name) && ($rowEP['code'] == $rowData->code) &&
                        ($fromMonth == $rowData->month) && ($fromYear == $rowData->year)
                    ) {
                        $objPHPExcel->getActiveSheet()->getStyle('D' . $startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                        $objPHPExcel->getActiveSheet()->setCellValue('D' . $startRow, round($rowData->implementPoint, 3));
                        $startRow++;
                    }
                }
            }
            $objPHPExcel->getActiveSheet()->getStyle('D10:D' . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('D10:D' . ($startRow - 1))->applyFromArray($styleArray);
        } else {
            $startColumn = 3;
            if ($fromYear == $toYear) {
                for ($m = $fromMonth; $m <= $toMonth; $m++) {
                    $startRow = 10;
                    foreach ($arrayEmpPosUnique as $rowEP) {
                        $ip = 0;
                        foreach ($data as $rowData) {
                            if (($rowEP['position_name'] == $rowData->position_name) && ($rowEP['code'] == $rowData->code) &&
                                ($m == $rowData->month) && ($fromYear == $rowData->year)
                            ) {
                                $ip = round($rowData->implementPoint, 3);
                            }
                        }
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $objPHPExcel->getActiveSheet()->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                        $startRow++;
                    }
                    $startColumn++;
                    $objPHPExcel->getActiveSheet()->getStyle('D10:' . $currentColumn . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('D10:' . $currentColumn . ($startRow - 1))->applyFromArray($styleArray);
                }

            } else if ($fromYear < $toYear) {
                $startColumn = 3;
                for ($m = $fromMonth; $m <= 12; $m++) {
                    $startRow = 10;
                    foreach ($arrayEmpPosUnique as $rowEP) {
                        $ip = 0;
                        foreach ($data as $rowData) {
                            if (($rowEP['position_name'] == $rowData->position_name) && ($rowEP['code'] == $rowData->code) &&
                                ($m == $rowData->month) && ($fromYear == $rowData->year)
                            ) {
                                $ip = round($rowData->implementPoint, 3);
                            }
                        }
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $objPHPExcel->getActiveSheet()->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                        $startRow++;
                    }
                    $startColumn++;
                    $objPHPExcel->getActiveSheet()->getStyle('D10:' . $currentColumn . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('D10:' . $currentColumn . ($startRow - 1))->applyFromArray($styleArray);
                }
                $start = $currentColumn;
                if ($toYear - $fromYear > 1) {
                    for ($y = $fromYear + 1; $y < $toYear; $y++) {
                        for ($m = 1; $m <= 12; $m++) {
                            $startRow = 10;
                            foreach ($arrayEmpPosUnique as $rowEP) {
                                $ip = 0;
                                foreach ($data as $rowData) {
                                    if (($rowEP['position_name'] == $rowData->position_name) && ($rowEP['code'] == $rowData->code) &&
                                        ($m == $rowData->month) && ($y == $rowData->year)
                                    ) {
                                        $ip = round($rowData->implementPoint, 3);
                                    }
                                }
                                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $objPHPExcel->getActiveSheet()->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                                $startRow++;
                            }
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->getStyle($start . '10:' . $currentColumn . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $objPHPExcel->getActiveSheet()->getStyle($start . '10:' . $currentColumn . ($startRow - 1))->applyFromArray($styleArray);
                        }
                    }
                }
                $start = $currentColumn;
                for ($m = 1; $m <= $toMonth; $m++) {
                    $startRow = 10;
                    foreach ($arrayEmpPosUnique as $rowEP) {
                        $ip = 0;
                        foreach ($data as $rowData) {
                            if (($rowEP['position_name'] == $rowData->position_name) && ($rowEP['code'] == $rowData->code) &&
                                ($m == $rowData->month) && ($toYear == $rowData->year)
                            ) {
                                $ip = round($rowData->implementPoint, 3);
                            }
                        }
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $objPHPExcel->getActiveSheet()->getStyle($currentColumn . $startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                        $startRow++;
                    }
                    $startColumn++;
                    $objPHPExcel->getActiveSheet()->getStyle($start . '10:' . $currentColumn . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle($start . '10:' . $currentColumn . ($startRow - 1))->applyFromArray($styleArray);
                }
            }
        }

        //get data to show detail of emp
        if ($empChoose == '1') {
        } else {

            // comp, area,pos,group --> base on $empChoose(users.code), goal=
            //get info
            $empChoose = explode(",", $empChoose);
            $sheet = 0;
            foreach ($empChoose as $empChooseThis) {
                $sheet++;
                $areaEmp = '';
                $groupEmp = '';
                $positionEmp = '';
                $aN = '';
                $cN = '';
                $pN = '';
                $gN = '';
                $eN = '';
                foreach ($data as $rowData) {
                    if ($rowData->code == $empChooseThis) {
                        $areaEmp = $rowData->area_code;
                        $groupEmp = $rowData->group_code;
                        $positionEmp = $rowData->position_code;
                        $aN = $rowData->area_name;
                        $cN = $rowData->company_name;
                        $pN = $rowData->position_name;
                        $gN = $rowData->group_name;
                        $eN = $rowData->name;
                        break;
                    }
                }
                // get data
                $select = "select goal.goal_code, goal.goal_name, goal.parent_id,
						sum(te.implement_point) as implementPoint,
						sum(te.implement) as implement,
						sum(te.target_value) as targetValue,
						te.month, te.year, te.position_id, te.user_id, te.goal_id,
						te.company_id, te.area_id, users.group_id, te.month, te.year
                    from target_employee as te
                    join goal on te.goal_id = goal.id
                    join users on users.id = te.user_id
                    join `group` on group.id = users.group_id
                    join `position` on position.id = te.position_id
                    join area on area.id = te.area_id
                    join company on company.id = te.company_id
                    where te.inactive = 0 and te.goal_level = 1 and company.company_code = '" . $company .
                    "' and area.area_code = '" . $areaEmp . "' and position.position_code = '" . $positionEmp . "' and users.code = '" . $empChooseThis . "'
							and group.group_code = '" . $groupEmp . "' ";
                if ($check == 2) {
                    // time to time
                    if ($fromYear == $toYear) {
                        $select .= ' and te.year=' . $fromYear . ' and te.month <=' . $toMonth .
                            ' and te.month >=' . $fromMonth;
                    } else {
                        $select .= ' and ((te.month >= ' . $fromMonth . ' and te.year = ' . $fromYear . ') or
						  (te.month <= ' . $toMonth . '  and te.year = ' . $toYear . '))
						  and te.year >= ' . $fromYear . ' and te.year <= ' . $toYear;
                    }
                } else {
                    $select .= ' and te.month = ' . $month . ' and te.year = ' . $year;
                }

                if ($goal != null) {
                    $select .= ' and ( ';
                    for ($g = 0; $g < count($goal); $g++) {
                        if ($g == (count($goal) - 1)) {
                            $select .= " goal.goal_code = '" . $goal[$g] . "' )";
                        } else {
                            $select .= " goal.goal_code = '" . $goal[$g] . "' or ";
                        }
                    }
                }
                $select .= ' group by te.month, te.year, te.company_id, te.area_id, te.position_id, te.user_id, te.goal_id
                     order by goal.parent_id, goal.id';

                $dataForThisEmp = DB::select(DB::raw($select));
                //list unique goal_name and goal_code
                $arrGoal = array();
                $arrMonthYear = array();
                $arrParent = array();
                foreach ($dataForThisEmp as $rowData) {
                    $countGoal = 0;
                    for ($i = 0; $i < count($arrGoal); $i++) {
                        if ($rowData->goal_code == $arrGoal[$i]['goal_code']) {
                            $countGoal = 1;
                        }
                    }
                    if ($countGoal == 0) {
                        $in = count($arrGoal);
                        $arrGoal[$in]['goal_code'] = $rowData->goal_code;
                        $arrGoal[$in]['goal_name'] = $rowData->goal_name;
                        $arrGoal[$in]['parent_id'] = $rowData->parent_id;
                    }

                    $countTime = 0;
                    for ($i = 0; $i < count($arrMonthYear); $i++) {
                        if (($rowData->month == $arrMonthYear[$i]['month']) && ($rowData->year == $arrMonthYear[$i]['year'])) {
                            $countTime = 1;
                        }
                    }
                    if ($countTime == 0) {
                        $in = count($arrMonthYear);
                        $arrMonthYear[$in]['month'] = $rowData->month;
                        $arrMonthYear[$in]['year'] = $rowData->year;
                    }

                    $countParent = 0;
                    for ($i = 0; $i < count($arrParent); $i++) {
                        if ($rowData->parent_id == $arrParent[$i]['id']) {
                            $countParent = 1;
                        }
                    }
                    if ($countParent == 0) {
                        $in = count($arrParent);
                        $arrParent[$in]['id'] = $rowData->parent_id;
                        $nameP = DB::table('goal')->where('id', $rowData->parent_id)->first();
                        $arrParent[$in]['goal_name'] = $nameP->goal_name;
                        $arrParent[$in]['goal_code'] = $nameP->goal_code;
                    }
                }

                for ($j = 0; $j < count($arrMonthYear); $j++) {
                    $ipValue = 0;
                    foreach ($dataForThisEmp as $row) {
                        if ($row->month == $arrMonthYear[$j]['month'] && $row->year == $arrMonthYear[$j]['year']) {
                            $ipValue += round($row->implementPoint, 3);
                        }
                    }
                    $arrMonthYear[$j]['ip'] = round($ipValue, 3);
                }

                $objPHPExcel->createSheet($sheet);
                $objPHPExcel->setActiveSheetIndex($sheet);
                //set header
                $objPHPExcel->getActiveSheet()->mergeCells('C2:E2');
                $objPHPExcel->getActiveSheet()->setCellValue('C2', 'BÁO CÁO THỰC HIỆN CỦA CÁ NHÂN');
                $objPHPExcel->getActiveSheet()->getStyle('C2:E2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('C2')->getFont()->setBold(true)->setSize(18);
                $objPHPExcel->getActiveSheet()->setCellValue('C4', 'Phòng/Đài/MBF HCM: ')->setCellValue('C5', 'Tổ/Quận/Huyện: ')
                    ->setCellValue('C6', 'Chức danh: ')->setCellValue('C7', 'Nhóm: ')
                    ->setCellValue('C8', 'Họ và tên: ')->setCellValue('C9', 'Tháng: ')
                    ->setCellValue('D4', $cN)->setCellValue('D5', $aN)
                    ->setCellValue('D6', $pN)->setCellValue('D7', $gN)
                    ->setCellValue('D8', $eN);
                $objPHPExcel->getActiveSheet()->setTitle($eN);
                if ($check == 1) {
                    $objPHPExcel->getActiveSheet()->setCellValue('D9', $month . '/' . $year);
                } else if (($fromMonth == $toMonth) && ($fromYear == $toYear)) {
                    $objPHPExcel->getActiveSheet()->setCellValue('D9', $fromMonth . '/' . $fromYear);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('D9', $fromMonth . '/' . $fromYear . ' - ' . $toMonth . '/' . $toYear);
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
//                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getStyle('C4:C9')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('D4:D9')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle('C4:C9')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $startRow = 11;
                if ($check == 1 || (($fromMonth == $toMonth) && ($fromYear == $toYear))) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    //paint month
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $startRow, 'STT')->setCellValue('C' . $startRow, 'Tiêu chí')
                        ->setCellValue('D' . $startRow, 'Kế hoạch')->setCellValue('E' . $startRow, 'Thực hiện')->setCellValue('F' . $startRow, 'Điểm thực hiện');
                    $startRow++;

                    $objPHPExcel->getActiveSheet()->getRowDimension($startRow - 1)->setRowHeight(25);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':F' . ($startRow - 1))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                    $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':F' . ($startRow - 1))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':F' . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':F' . ($startRow - 1))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':F' . ($startRow - 1))->applyFromArray($styleArray);

                    // write data
                    for ($gP = 0; $gP < count($arrParent); $gP++) {
                        $isExist = 0;
                        foreach ($dataForThisEmp as $rowDFTE) {
                            if ($rowDFTE->parent_id == $arrParent[$gP]['id']) {
                                if ($rowDFTE->targetValue != 0 || $rowDFTE->implement != 0 || $rowDFTE->implementPoint != 0) {
                                    $isExist = 1;
                                }
                            }
                        }
                        if($isExist == 1){
                            $rowParent = $startRow;
                            $ipParent = 0;
                            $startRow++;
                            $index = 0;
                            foreach ($dataForThisEmp as $rowDFTE) {
                                if ($rowDFTE->parent_id == $arrParent[$gP]['id']) {
                                    $tv = round($rowDFTE->targetValue, 3);
                                    $i = round($rowDFTE->implement, 3);
                                    $ip = round($rowDFTE->implementPoint, 3);
                                    $ipParent += $ip;
                                    if ($tv != 0 || $i != 0 || $ip != 0) {
                                        $isExist = 1;
                                        $index++;
                                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $startRow, $index)
                                            ->setCellValue('C' . $startRow, $rowDFTE->goal_name)
                                            ->setCellValue('D' . $startRow, $tv)
                                            ->setCellValue('E' . $startRow, $i)
                                            ->setCellValue('F' . $startRow, $ip);
                                        $startRow++;
                                    }
                                }
                                if ($isExist) {
                                    $objPHPExcel->getActiveSheet()->getStyle('D' . ($rowParent + 1) . ':F' . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                    $objPHPExcel->getActiveSheet()->getStyle('B' . ($rowParent + 1) . ':B' . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    $objPHPExcel->getActiveSheet()->getStyle('D' . ($rowParent + 1) . ':F' . ($startRow - 1))->getNumberFormat()->setFormatCode('#,##0.000');
                                }
                            }

                            $objPHPExcel->getActiveSheet()->mergeCells('B' . $rowParent . ':E' . $rowParent);
                            $objPHPExcel->getActiveSheet()->setCellValue('B' . $rowParent, $arrParent[$gP]['goal_name'])->setCellValue('F' . $rowParent, $ipParent);
                            $objPHPExcel->getActiveSheet()->getStyle('B' . $rowParent . ':F' . $rowParent)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                            $objPHPExcel->getActiveSheet()->getStyle('F' . $rowParent)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        }

                    }
                    $objPHPExcel->getActiveSheet()->mergeCells('B' . $startRow . ':E' . $startRow);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':E' . $startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':F' . $startRow)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $startRow, 'ĐIỂM THÁNG')
                        ->setCellValue('F' . $startRow, $arrMonthYear[0]['ip']);
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                    $objPHPExcel->getActiveSheet()->getStyle('B12:F' . ($startRow))->applyFromArray($styleArray);
                } else {
                    //paint month - header
                    $objPHPExcel->getActiveSheet()->mergeCells('B' . $startRow . ':B' . ($startRow + 1));
                    $objPHPExcel->getActiveSheet()->mergeCells('C' . $startRow . ':C' . ($startRow + 1));
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $startRow, 'STT')->setCellValue('C' . $startRow, 'Tên chỉ tiêu');

                    $startColumn = 3;
                    if ($fromYear == $toYear) {
                        $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $to = PHPExcel_Cell::stringFromColumnIndex($startColumn + $toMonth - $fromMonth);
                        $objPHPExcel->getActiveSheet()->mergeCells($from . $startRow . ':' . $to . $startRow);
                        $objPHPExcel->getActiveSheet()->setCellValue($from . $startRow, $fromYear);
                        $startRow++;
                        for ($m = $fromMonth; $m <= $toMonth; $m++) {
                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $m);
                        }

                        $objPHPExcel->getActiveSheet()->getRowDimension($startRow - 1)->setRowHeight(20);
                        $objPHPExcel->getActiveSheet()->getRowDimension($startRow)->setRowHeight(25);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->applyFromArray($styleArray);

                    } else {
                        $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $to = PHPExcel_Cell::stringFromColumnIndex($startColumn + 12 - $fromMonth);
                        $objPHPExcel->getActiveSheet()->mergeCells($from . $startRow . ':' . $to . $startRow);
                        $objPHPExcel->getActiveSheet()->setCellValue($from . $startRow, $fromYear);
                        for ($m = $fromMonth; $m <= 12; $m++) {
                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . ($startRow + 1), $m);
                        }

                        if (($toYear - $fromYear) > 1) {
                            for ($y = $fromYear + 1; $y < $toYear; $y++) {
                                $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $to = PHPExcel_Cell::stringFromColumnIndex($startColumn + 11);
                                $objPHPExcel->getActiveSheet()->mergeCells($from . $startRow . ':' . $to . $startRow);
                                $objPHPExcel->getActiveSheet()->setCellValue($from . $startRow, $y);
                                for ($m = 1; $m <= 12; $m++) {
                                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                    $startColumn++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . ($startRow + 1), $m);
                                }
                            }
                        }

                        $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $to = PHPExcel_Cell::stringFromColumnIndex($startColumn + $toMonth - 1);
                        $objPHPExcel->getActiveSheet()->mergeCells($from . $startRow . ':' . $to . $startRow);
                        $objPHPExcel->getActiveSheet()->setCellValue($from . $startRow, $toYear);
                        for ($m = 1; $m <= $toMonth; $m++) {
                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . ($startRow + 1), $m);
                        }

                        $startRow++;
                        $objPHPExcel->getActiveSheet()->getRowDimension($startRow - 1)->setRowHeight(20);
                        $objPHPExcel->getActiveSheet()->getRowDimension($startRow)->setRowHeight(25);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($startRow - 1) . ':' . $to . ($startRow))->applyFromArray($styleArray);
                    }

                    // write data
                    //get array goal_name, goal_code, ip, month, year of parent
                    $arrayParentTimeIP = array();
                    foreach ($arrMonthYear as $rowMY) {
                        if ($rowMY['ip'] > 0) {
                            foreach ($arrParent as $rowAP) {
                                $ip = 0;
                                foreach ($dataForThisEmp as $rowFT) {
                                    if ($rowFT->month == $rowMY['month'] &&
                                        $rowFT->year == $rowMY['year'] &&
                                        $rowFT->parent_id == $rowAP['id']
                                    ) {
                                        $ip += round($rowFT->implementPoint, 3);
                                    }
                                }

                                if ($ip > 0) {
                                    $index = count($arrayParentTimeIP);
                                    $arrayParentTimeIP[$index]['month'] = $rowMY['month'];
                                    $arrayParentTimeIP[$index]['year'] = $rowMY['year'];
                                    $arrayParentTimeIP[$index]['ip'] = $ip;
                                    $arrayParentTimeIP[$index]['id'] = $rowAP['id'];
                                    $arrayParentTimeIP[$index]['goal_name'] = $rowAP['goal_name'];
                                    $arrayParentTimeIP[$index]['goal_code'] = $rowAP['goal_code'];
                                }

                            }
                        }
                    }

                    $arrayParentExists = array();
                    foreach ($arrayParentTimeIP as $row) {
                        $count = 0;
                        foreach ($arrayParentExists as $rowE) {
                            if ($rowE['goal_code'] == $row['goal_code']) {
                                $count = 1;
                            }
                        }

                        if ($count == 0) {
                            $index = count($arrayParentExists);
                            $arrayParentExists[$index]['id'] = $row['id'];
                            $arrayParentExists[$index]['goal_name'] = $row['goal_name'];
                            $arrayParentExists[$index]['goal_code'] = $row['goal_code'];
                        }
                    }
                    // write data
                    $startRow++;

                    foreach ($arrayParentExists as $rowPTIP) {
                        $objPHPExcel->getActiveSheet()->mergeCells('B' . $startRow . ':C' . $startRow);
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $startRow, $rowPTIP['goal_name']);

                        $startColumn = 3;
                        if ($fromYear == $toYear) {
                            for ($m = $fromMonth; $m <= $toMonth; $m++) {
                                $ip = 0;
                                foreach ($arrayParentTimeIP as $rowAPT) {
                                    if ($rowAPT['goal_code'] == $rowPTIP['goal_code'] &&
                                        $rowAPT['month'] == $m &&
                                        $rowAPT['year'] == $fromYear
                                    ) {
                                        $ip = $rowAPT['ip'];
                                    }
                                }

                                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $startColumn++;
                                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                            }
                        } else {
                            for ($m = $fromMonth; $m <= 12; $m++) {
                                $ip = 0;
                                foreach ($arrayParentTimeIP as $rowAPT) {
                                    if ($rowAPT['goal_code'] == $rowPTIP['goal_code'] &&
                                        $rowAPT['month'] == $m &&
                                        $rowAPT['year'] == $fromYear
                                    ) {
                                        $ip = $rowAPT['ip'];
                                    }
                                }

                                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $startColumn++;
                                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                            }

                            if ($toYear - $fromYear > 1) {
                                for ($y = $fromYear + 1; $y <= $toYear; $y++) {
                                    for ($m = 1; $m <= 12; $m++) {
                                        $ip = 0;
                                        foreach ($arrayParentTimeIP as $rowAPT) {
                                            if ($rowAPT['goal_code'] == $rowPTIP['goal_code'] &&
                                                $rowAPT['month'] == $m &&
                                                $rowAPT['year'] == $y
                                            ) {
                                                $ip = $rowAPT['ip'];
                                            }
                                        }

                                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                        $startColumn++;
                                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                                    }
                                }
                            }

                            for ($m = 1; $m <= $toMonth; $m++) {
                                $ip = 0;
                                foreach ($arrayParentTimeIP as $rowAPT) {
                                    if ($rowAPT['goal_code'] == $rowPTIP['goal_code'] &&
                                        $rowAPT['month'] == $m &&
                                        $rowAPT['year'] == $toYear
                                    ) {
                                        $ip = $rowAPT['ip'];
                                    }
                                }

                                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $startColumn++;
                                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ip);
                            }
                        }
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn - 1);
                        $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':' . $currentColumn . $startRow)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');

                        $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':' . $currentColumn . ($startRow))->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->getStyle('D' . $startRow . ':' . $currentColumn . ($startRow))->getNumberFormat()->setFormatCode('#,##0.000');
                        $startRow++;

                        // child
                        $index = 0;
                        $start = $startRow;
                        foreach ($arrGoal as $rowGC) {
                            if ($rowGC['parent_id'] == $rowPTIP['id']) {

                                // all zero
                                $isNull = 0;
                                if ($fromYear == $toYear) {
                                    for ($m = $fromMonth; $m <= $toMonth; $m++) {
                                        $ipE = 0;
                                        foreach ($dataForThisEmp as $rowDTE) {
                                            if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                $rowDTE->month == $m &&
                                                $rowDTE->year == $fromYear
                                            ) {
                                                $ipE = $rowDTE->implementPoint;
                                            }
                                        }
                                        if ($ipE > 0) {
                                            $isNull = 1;
                                        }
                                    }
                                } else {
                                    for ($m = $fromMonth; $m <= 12; $m++) {
                                        $ipE = 0;
                                        foreach ($dataForThisEmp as $rowDTE) {
                                            if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                $rowDTE->month == $m &&
                                                $rowDTE->year == $fromYear
                                            ) {
                                                $ipE = $rowDTE->implementPoint;
                                            }
                                        }

                                        if ($ipE > 0) {
                                            $isNull = 1;
                                        }
                                    }

                                    if ($toYear - $fromYear > 1) {
                                        for ($y = $fromYear + 1; $y <= $toYear; $y++) {
                                            for ($m = 1; $m <= 12; $m++) {
                                                $ipE = 0;
                                                foreach ($dataForThisEmp as $rowDTE) {
                                                    if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                        $rowDTE->month == $m &&
                                                        $rowDTE->year == $y
                                                    ) {
                                                        $ipE = $rowDTE->implementPoint;
                                                    }
                                                }
                                                if ($ipE > 0) {
                                                    $isNull = 1;
                                                }
                                            }
                                        }
                                    }

                                    for ($m = 1; $m <= $toMonth; $m++) {
                                        $ipE = 0;
                                        foreach ($dataForThisEmp as $rowDTE) {
                                            if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                $rowDTE->month == $m &&
                                                $rowDTE->year == $toYear
                                            ) {
                                                $ipE = $rowDTE->implementPoint;
                                            }
                                        }
                                        if ($ipE > 0) {
                                            $isNull = 1;
                                        }
                                    }
                                }
                                //paint each month
                                if ($isNull == 1) {
                                    $startColumn = 3;
                                    $index++;
                                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $startRow, $index)->setCellValue('C' . $startRow, $rowGC['goal_name']);
                                    if ($fromYear == $toYear) {
                                        for ($m = $fromMonth; $m <= $toMonth; $m++) {
                                            $ipE = 0;
                                            foreach ($dataForThisEmp as $rowDTE) {
                                                if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                    $rowDTE->month == $m &&
                                                    $rowDTE->year == $fromYear
                                                ) {
                                                    $ipE = $rowDTE->implementPoint;
                                                }
                                            }

                                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                            $startColumn++;
                                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                                        }
                                    } else {
                                        for ($m = $fromMonth; $m <= 12; $m++) {
                                            $ipE = 0;
                                            foreach ($dataForThisEmp as $rowDTE) {
                                                if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                    $rowDTE->month == $m &&
                                                    $rowDTE->year == $fromYear
                                                ) {
                                                    $ipE = $rowDTE->implementPoint;
                                                }
                                            }

                                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                            $startColumn++;
                                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                                        }

                                        if ($toYear - $fromYear > 1) {
                                            for ($y = $fromYear + 1; $y <= $toYear; $y++) {
                                                for ($m = 1; $m <= 12; $m++) {
                                                    $ipE = 0;
                                                    foreach ($dataForThisEmp as $rowDTE) {
                                                        if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                            $rowDTE->month == $m &&
                                                            $rowDTE->year == $y
                                                        ) {
                                                            $ipE = $rowDTE->implementPoint;
                                                        }
                                                    }

                                                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                                    $startColumn++;
                                                    $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                                                }
                                            }
                                        }

                                        for ($m = 1; $m <= $toMonth; $m++) {
                                            $ipE = 0;
                                            foreach ($dataForThisEmp as $rowDTE) {
                                                if ($rowDTE->goal_code == $rowGC['goal_code'] &&
                                                    $rowDTE->month == $m &&
                                                    $rowDTE->year == $toYear
                                                ) {
                                                    $ipE = $rowDTE->implementPoint;
                                                }
                                            }

                                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                            $startColumn++;
                                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                                        }
                                    }
                                    $startRow++;
                                }

                            }
                        }

                        $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':' . $currentColumn . ($startRow - 1))->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->getStyle('D' . $start . ':' . $currentColumn . ($startRow - 1))->getNumberFormat()->setFormatCode('#,##0.000');
                        $objPHPExcel->getActiveSheet()->getStyle('B' . $start . ':B' . ($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    }

                    //paint point of month
                    $objPHPExcel->getActiveSheet()->mergeCells('B' . $startRow . ':C' . $startRow);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $startRow, 'ĐIỂM THÁNG');
                    $startColumn = 3;
                    if ($fromYear == $toYear) {
                        for ($m = $fromMonth; $m <= $toMonth; $m++) {
                            $ipE = 0;
                            foreach ($arrMonthYear as $rowMYT) {
                                if ($rowMYT['month'] == $m &&
                                    $rowMYT['year'] == $fromYear
                                ) {
                                    $ipE = $rowMYT['ip'];
                                }
                            }

                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                        }
                    } else {
                        for ($m = $fromMonth; $m <= 12; $m++) {
                            $ipE = 0;
                            foreach ($arrMonthYear as $rowMYT) {
                                if ($rowMYT['month'] == $m &&
                                    $rowMYT['year'] == $fromYear
                                ) {
                                    $ipE = $rowMYT['ip'];
                                }
                            }

                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                        }

                        if ($toYear - $fromYear > 1) {
                            for ($y = $fromYear + 1; $y <= $toYear; $y++) {
                                for ($m = 1; $m <= 12; $m++) {
                                    foreach ($arrMonthYear as $rowMYT) {
                                        if ($rowMYT['month'] == $m &&
                                            $rowMYT['year'] == $y
                                        ) {
                                            $ipE = $rowMYT['ip'];
                                        }
                                    }

                                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                    $startColumn++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                                }
                            }
                        }

                        for ($m = 1; $m <= $toMonth; $m++) {
                            $ipE = 0;
                            foreach ($arrMonthYear as $rowMYT) {
                                if ($rowMYT['month'] == $m &&
                                    $rowMYT['year'] == $toYear
                                ) {
                                    $ipE = $rowMYT['ip'];
                                }
                            }

                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $startColumn++;
                            $objPHPExcel->getActiveSheet()->setCellValue($currentColumn . $startRow, $ipE);
                        }
                    }
                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn - 1);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':' . $currentColumn . ($startRow))->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle('D' . $startRow . ':' . $currentColumn . ($startRow))->getNumberFormat()->setFormatCode('#,##0.000');
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':C' . ($startRow))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':' . $currentColumn . ($startRow))->getFont()->setBold(true);
                }
            }
        }
        excelUtils::setZoomSheet($objPHPExcel, 80);
        $objPHPExcel->setActiveSheetIndex(0);
        $fileName = 'BaoCaoThucHienCuaCaNhan';
        $this->outputFile($fileName, $objPHPExcel);
    }

    function reportPositionByTime($check, $month, $year, $fromMonth, $toMonth, $fromYear, $toYear, $company, $area, $position){
        $company = explode(",",$company);
        $area = explode(",",$area);
        $positionF = explode(",",$position);
        $position = array();
        for($p=0; $p<count($positionF); $p++){
            $positionFinal = '';
            $arrName = str_split($positionF[$p]);
            foreach($arrName as $n){
                if($n == '*'){
                    $positionFinal.= '/';
                } else {
                    $positionFinal.=$n;
                }
            }
            $position[count($position)] = $positionFinal;
        }

        $select = 'select company.company_code, company.company_name,
							position.position_name, position.position_code,
							area.area_name, area.area_code, tp.month, tp.year,
							sum(tp.implement_point) as implementPoint
                    from target_position as tp
                    join company on company.id = tp.company_id
                    join area on area.id = tp.area_id
                    join position on position.id = tp.position_id
                    where tp.inactive = 0 and tp.goal_level = 1' ;

        if($check == 2){
            if($fromYear == $toYear){
                $select .=' and tp.year='.$fromYear.' and tp.month <='.$toMonth.
                    ' and tp.month >='.$fromMonth;
            } else {
                $select .= ' and ((tp.month >= '.$fromMonth.' and tp.year = '.$fromYear.') or
						(tp.month <= '.$toMonth.'  and tp.year = '.$toYear.') or ( tp.year > '.$fromYear.' and tp.year < '.$toYear.'))';
            }

        } else {
            $select .= ' and tp.month = '.$month.' and tp.year = '.$year;
        }

        //comp
        if($company != null){
            $select .= " and ( ";
            for($p=0; $p<count($company); $p++){
                if($p == (count($company)-1)){
                    $select .= "company.company_code = '".$company[$p]."' )";
                } else {
                    $select .= "company.company_code = '".$company[$p]."' or ";
                }
            }
        } else {
            $select .= " and 0 ";
        }

        //area
        $all = 0;
        if($area != null){
            $selectChild = ' and ( ';
            for($e=0; $e<count($area); $e++){
                if($area[$e] == '1'){
                    $all = 1;
                }
                if($e == (count($area)-1)){
                    $selectChild .= " area.area_code = '". $area[$e] ."' ) ";
                } else {
                    $selectChild .= " area.area_code = '". $area[$e] ."' or ";
                }
            }
            if($all == 0){
                $select .= $selectChild;
            }
        } else {
            $select .= ' and 0 ';
        }

        //pos
        if($position != null){
            $select .= " and ( ";
            for($p=0; $p<count($position); $p++){
                if($p == (count($position)-1)){
                    $select .= "position.position_code = '".$position[$p]."' )";
                } else {
                    $select .= "position.position_code = '".$position[$p]."' or ";
                }
            }
        } else {
            $select .= " and 0 ";
        }

        $select .= ' group by company.company_code, company.company_name,
							position.position_name, position.position_code,
							area.area_name, area.area_code, tp.month, tp.year
                     order by company.company_name, area.area_name, position.position_code, implementPoint desc';

        $data = DB::select(DB::raw($select));

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        if(($check == 1) || (($fromMonth == $toMonth) && ($fromYear == $toYear))) {
            $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportImplementByPositionOneMonth.xlsx");
            $objPHPExcel->setActiveSheetIndex(0);
            if($check == 1){
                $objPHPExcel->getActiveSheet()->setCellValue('E5' , $month.'/'.$year)
                    ->setCellValue('E7' , "Tháng ".$month);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('E5' , $fromMonth.'/'.$fromYear)
                    ->setCellValue('E7' , "Tháng ".$fromMonth);
            }
            $starRow = 8;
            $index = 0;
            foreach($data as $rowData){
                $starRow++;
                $index++;
                $ip = round($rowData->implementPoint, 3);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$starRow , $index)
                    ->setCellValue('B'.$starRow , $rowData->company_name)
                    ->setCellValue('C'.$starRow , $rowData->area_name)
                    ->setCellValue('D'.$starRow , $rowData->position_name)
                    ->setCellValue('E'.$starRow , $ip)
                    ->setCellValue('F'.$starRow , $ip);
            }
            for($i = 9; $i<= $starRow; $i++){
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$i , '=rank(F'.$i.',F9:F'.$starRow.')');
            }
            $objPHPExcel->getActiveSheet()->getStyle('A9:G'.$starRow)->applyFromArray($styleArray);
        } else {
            //list unique time
            $arrayCAPGE = array();
            foreach($data as $rowData){
                $count = 0;
                for($j=0; $j<count($arrayCAPGE); $j++){
                    if(($rowData->company_code == $arrayCAPGE[$j]['company_code']) &&
                        ($rowData->area_code == $arrayCAPGE[$j]['area_code']) &&
                        ($rowData->position_code == $arrayCAPGE[$j]['position_code'])){
                        $count = 1;
                    }
                }
                if($count == 0){
                    $in = count($arrayCAPGE);
                    $arrayCAPGE[$in]['company_code'] = $rowData->company_code;
                    $arrayCAPGE[$in]['company_name'] = $rowData->company_name;
                    $arrayCAPGE[$in]['area_code'] = $rowData->area_code;
                    $arrayCAPGE[$in]['area_name'] = $rowData->area_name;
                    $arrayCAPGE[$in]['position_code'] = $rowData->position_code;
                    $arrayCAPGE[$in]['position_name'] = $rowData->position_name;
                }
            }

            $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportImplementByPosition.xlsx");
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('D5' , $fromMonth.'/'.$toYear)
                ->setCellValue('F5' , $toMonth.'/'.$toYear);

            //paint month
            $startColumn = 6;
            if($fromYear == $toYear){
                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $startColumn = $startColumn+($toMonth-$fromMonth);
                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $objPHPExcel->getActiveSheet()->mergeCells($currentColumn.'7:'.$toColumn.'7');
                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.'7' , $fromYear);
                $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            } else if($fromYear < $toYear) {
                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $startColumn = $startColumn+(12-$fromMonth);
                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $startColumn ++;
                $objPHPExcel->getActiveSheet()->mergeCells($currentColumn.'7:'.$toColumn.'7');
                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.'7' , $fromYear);
                $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

                if($toYear-$fromYear > 1){
                    for($y=$fromYear+1; $y<$toYear; $y++){
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $startColumn = $startColumn + 11;
                        $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $startColumn ++;
                        $objPHPExcel->getActiveSheet()->mergeCells($currentColumn.'7:'.$toColumn.'7');
                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.'7' , $y);
                        $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                    }
                }

                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $startColumn = $startColumn + $toMonth-1;
                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $objPHPExcel->getActiveSheet()->mergeCells($currentColumn.'7:'.$toColumn.'7');
                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.'7' , $toYear);
                $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($currentColumn.'7:'.$toColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            }

            $starRow = 8;
            $index = 0;
            foreach($arrayCAPGE as $rowData){
                $starRow++;
                $index++;
                $totalIp = 0;
                $monthExists = 0;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$starRow , $index)
                    ->setCellValue('B'.$starRow , $rowData['company_name'])
                    ->setCellValue('C'.$starRow , $rowData['area_name'])
                    ->setCellValue('D'.$starRow , $rowData['position_name']);

                $startColumn = 5;
                if($fromYear == $toYear){
                    for($ft=$fromMonth; $ft<=$toMonth; $ft++){
                        $implementPoint = 0;
                        foreach($data as $row){
                            if($row->month == $ft && $row->year == $fromYear &&
                                $row->company_code == $rowData['company_code'] &&
                                $row->area_code == $rowData['area_code'] &&
                                $row->position_code == $rowData['position_code']){
                                $implementPoint = round($row->implementPoint, 3);
                                $monthExists++;
                                $totalIp += $implementPoint;
                            }
                        }
                        $startColumn++;
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.$starRow , $implementPoint)
                            ->setCellValue($currentColumn.'8' , $ft);

                    }
                } else if($fromYear < $toYear){

                    for($f=$fromMonth; $f<=12; $f++){
                        $implementPoint = 0;
                        foreach($data as $row){
                            if($row->month == $f && $row->year == $fromYear &&
                                $row->company_code == $rowData['company_code'] &&
                                $row->area_code == $rowData['area_code'] &&
                                $row->position_code == $rowData['position_code']){
                                $implementPoint = round($row->implementPoint, 3);
                                $totalIp += $implementPoint;
                                $monthExists++;
                            }
                        }
                        $startColumn++;
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.$starRow , $implementPoint)
                            ->setCellValue($currentColumn.'8' , $f);

                    }

                    if($toYear-$fromYear > 1){
                        for($y=$fromYear+1; $y<$toYear; $y++){
                            for($m=1; $m<=12; $m++){
                                $implementPoint = 0;
                                foreach($data as $row){
                                    if($row->month == $m && $row->year == $y &&
                                        $row->company_code == $rowData['company_code'] &&
                                        $row->area_code == $rowData['area_code'] &&
                                        $row->position_code == $rowData['position_code']){
                                        $implementPoint = round($row->implementPoint, 3);
                                        $totalIp += $implementPoint;
                                        $monthExists++;
                                    }
                                }
                                $startColumn++;
                                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.$starRow , $implementPoint)
                                    ->setCellValue($currentColumn.'8' , $m);
                            }
                        }
                    }

                    for($t=1; $t<=$toMonth; $t++){
                        $implementPoint = 0;
                        foreach($data as $row){
                            if($row->month == $t && $row->year == $toYear &&
                                $row->company_code == $rowData['company_code'] &&
                                $row->area_code == $rowData['area_code'] &&
                                $row->position_code == $rowData['position_code']){
                                $implementPoint = round($row->implementPoint, 3);
                                $totalIp += $implementPoint;
                                $monthExists++;
                            }
                        }
                        $startColumn++;
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $objPHPExcel->getActiveSheet()->setCellValue($currentColumn.$starRow , $implementPoint)
                            ->setCellValue($currentColumn.'8' , $t);
                    }
                }

                //set total ip
                if($monthExists == 0){
                    $AVG = 0;
                } else {
                    $AVG = round(($totalIp/$monthExists), 3);
                }

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$starRow , $AVG);
            }

            //rank
            for($i = 9; $i < ($starRow+1); $i++){
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$i , '=rank(E'.$i.',E9:E'.($starRow+1).')');
            }
            //set boder for whole table
            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
            $objPHPExcel->getActiveSheet()->getStyle('A9:'.$currentColumn.$starRow)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->freezePane('G9');
        }
        excelUtils::setZoomSheet($objPHPExcel, 80);
        $fileName = 'BaoCaoThucHienCuaChucDanh';
        $this->outputFile($fileName, $objPHPExcel);
    }

    /*
     * report KPI by Month
     */
    public function reportKPIByMonth($company, $goal, $applyDate){
        $company = explode(",",$company);
        $goal = explode(",",$goal);
        $select = "select c.company_code, c.company_name,
                          sum(ilc.benchmark) as bm, sum(ilc.implement_point) as ip
                    from important_level_company as ilc
                    join company as c on c.id = ilc.company_id
                    join goal as g on g.id = ilc.goal_id
                    where ilc.inactive = 0 and g.parent_id > 0
                    AND ilc.apply_date = '".$applyDate."' ";

        //comp
        if($company != null){
            $select .= ' and ( ';
            for($c=0; $c<count($company); $c++){
                if($c == (count($company)-1)){
                    $select .= " c.company_code = '". $company[$c] ."' )";
                } else {
                    $select .= " c.company_code = '". $company[$c] ."' or ";
                }
            }
        } else {
            $select .= ' and 0 ';
        }

        // goal
        if($goal != null){
            $select .= ' and ( ';
            for($c=0; $c<count($goal); $c++){
                if($c == (count($goal)-1)){
                    $select .= " g.goal_code = '". $goal[$c] ."' )";
                } else {
                    $select .= " g.goal_code = '". $goal[$c] ."' or ";
                }
            }
        } else {
            $select .= ' and 0 ';
        }

        $select .= ' GROUP BY c.company_code, c.company_name
                     ORDER BY ip desc';

        $data = DB::select(DB::raw($select));

        //get company not exists
        $companyData = DB::table('company')->where('inactive', 0)->get();
        $arrCompNotExists = array();
        foreach($company as $rowComp){
            $countComp = 0;
            foreach($data as $rowData){
                if($rowData->company_code == $rowComp){
                    $countComp = 1;
                }
            }

            if($countComp == 0){
                foreach($companyData as $dC){
                    if($dC->company_code == $rowComp){
                        $arrCompNotExists[count($arrCompNotExists)] = $dC->company_name;
                        break;
                    }
                }
            }
        }

        //export into file excel
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportKPIByMonthOneMonth.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);

        if(count($data) > 0){
            $objPHPExcel->getActiveSheet()->setCellValue('C5', commonUtils::formatDate($applyDate));
            $index = 1;
            $firstRow = 8;
            $startRow = 8;
            foreach($data as $row){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $index++)
                                                ->setCellValue('B'.$startRow, $row->company_name)
                                                ->setCellValue('C'.$startRow, $row->ip)
                                                ->setCellValue('D'.$startRow, '=rank(D'.$startRow.',D'.$firstRow.':D'.$startRow.')');
                $startRow++;
            }
            $objPHPExcel->getActiveSheet()->getStyle('C'.$firstRow.':C'.($startRow-1))->getNumberFormat()->setFormatCode('#,##0.000');
            foreach($arrCompNotExists as $arr){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $index++)
                                            ->setCellValue('B'.$startRow, $arr)
                                            ->setCellValue('C'.$startRow, 'KHÔNG')
                                            ->setCellValue('D'.$startRow, 'KHÔNG');
                $startRow++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('A'.$firstRow.':D'.($startRow-1))->applyFromArray($styleArray);
        }
        excelUtils::setZoomSheet($objPHPExcel, 80);
        $fileName = 'BaoCaoKetQuaKPITheoThang';
        $this->outputFile($fileName, $objPHPExcel);
    }

    public function reportDataKPIResult($check, $month, $year, $fromMonth, $toMonth, $fromYear, $toYear, $company, $area, $position, $group, $emp, $goal){
        $emp = explode(",",$emp);
        $goal = explode(",",$goal);
        $group = explode(",",$group);
        $positionF = explode(",",$position);
        $position = array();
        for($p=0; $p<count($positionF); $p++){
            $positionFinal = '';
            $arrName = str_split($positionF[$p]);
            foreach($arrName as $n){
                if($n == '*'){
                    $positionFinal.= '/';
                } else {
                    $positionFinal.=$n;
                }
            }
            $position[count($position)] = $positionFinal;
        }

        if($area == 'No'){
            Session::flash('message-errors', "Vui lòng chọn một Tổ/Quận/Huyện trước khi xuất báo cáo.");
            return redirect('reportKPIResult');
        } else {
            // export data: each position each sheet

            $allGoal = DB::table('goal')->where('inactive', 0)->get();
            $arrayGP = array();
            if($goal != null){
                foreach($goal as $g){
                    $parent = -1;
                    foreach($allGoal as $gl){
                        if($g == $gl->goal_code){
                            $parent = $gl->parent_id;
                            break;
                        }
                    }

                    if(!in_array($parent, $arrayGP)){
                        $arrayGP[count($arrayGP)] = $parent;
                    }
                }
            }

            $selectBM = "SELECT ilp.benchmark, position.position_code, goal.goal_name, position.position_name,
                            goal.goal_code, ilp.goal_id, goal.parent_id, ilp.important_level, goal.goal_type, ilp.month, ilp.year
                    FROM important_level_position as ilp
                    LEFT JOIN company ON company.id = ilp.company_id
                    LEFT JOIN area ON area.id = ilp.area_id
                    LEFT JOIN goal ON goal.id = ilp.goal_id
                    LEFT JOIN `position` ON position.id = ilp.position_id
                    WHERE ilp.inactive = 0 and company.company_code = '".$company."' and area.area_code = '".$area."' ";

            $selectEmp = "select goal.goal_name, goal.goal_code, goal.goal_type, goal.parent_id, te.month, te.year,
								 users.name, users.code, position.position_code, te.goal_id,
								te.implement, te.implement_point, te.target_value
							from target_employee as te
							left join goal on goal.id=te.goal_id
							left join `position` on position.id=te.position_id
							left join area on area.id = te.area_id
							left join company on company.id = te.company_id
							left join users on users.id = te.user_id
							left join `group` on group.id = users.group_id
							where company.company_code = '".$company."'
								and area.area_code = '".$area."' ";

            if($check == 2){
                if($fromYear == $toYear){
                    $selectEmp .= ' and te.year='.$fromYear.' and te.month <='.$toMonth.
                        ' and te.month >='.$fromMonth;
                    $selectBM .= ' and ilp.year='.$fromYear.' and ilp.month <='.$toMonth.
                        ' and ilp.month >='.$fromMonth;

                } else {
                    $selectEmp .= ' and ((te.month >= '.$fromMonth.' and te.year = '.$fromYear.') or
						(te.month <= '.$toMonth.'  and te.year = '.$toYear.') or (te.year > '.$fromYear.' and te.year < '.$toYear.'))';
                    $selectBM .= ' and ((ilp.month >= '.$fromMonth.' and ilp.year = '.$fromYear.') or
						(ilp.month <= '.$toMonth.'  and ilp.year = '.$toYear.')or (ilp.year > '.$fromYear.' and ilp.year < '.$toYear.'))';
                }

            } else {
                $selectEmp .= ' and te.month = '.$month.' and te.year = '.$year;
                $selectBM .= ' and ilp.month = '.$month.' and ilp.year = '.$year;
            }

            //pos
            $selectEmp .= " and ( ";
            $selectBM .= " and ( ";
            if($position != null){
                for($p=0; $p<count($position); $p++){
                    if($p == (count($position)-1)){
                        $selectEmp .= "position.position_code = '".$position[$p]."' )";
                        $selectBM .= "position.position_code = '".$position[$p]."' )";
                    } else {
                        $selectEmp .= "position.position_code = '".$position[$p]."' or ";
                        $selectBM .= "position.position_code = '".$position[$p]."' or ";
                    }
                }
            } else {
                $selectEmp .= '0 )';
                $selectBM .= '0 )';
            }

            //group
            if($group != null){
                $selectEmp .= " and ( ";
                for($g=0; $g<count($group); $g++){
                    if($g == (count($group)-1)){
                        $selectEmp .= "group.group_code = '".$group[$g]."' )";
                    } else {
                        $selectEmp .= "group.group_code = '".$group[$g]."' or ";
                    }
                }
            } else {
                $selectEmp .= " and 0 ";
            }

            // emp
            $all = 0;
            if($emp != null){
                $select = ' and ( ';
                for($e=0; $e<count($emp); $e++){
                    if($emp[$e] == '1'){
                        $all = 1;
                    }
                    if($e == (count($emp)-1)){
                        $select .= " users.code = '". $emp[$e] ."' )";
                    } else {
                        $select .= " users.code = '". $emp[$e] ."' or ";
                    }
                }
                if($all == 0){
                    $selectEmp .= $select;
                }
            } else {
                $selectEmp .= ' and 0 ';
            }
            //goal
            if($goal != null){
                $selectEmp .= " and ( ";
                $selectBM .= " and ( ";
                for($g=0; $g<count($goal); $g++){
                    if($g == (count($goal)-1)){
                        $selectEmp .= "goal.goal_code = '".$goal[$g]."' )";
                        $selectBM .= "goal.goal_code = '".$goal[$g]."' ";

                        if(count($arrayGP) > 0){
                            for($ga=0; $ga<count($arrayGP); $ga++){
                                $selectBM .= " or goal.id = ".$arrayGP[$ga]." ";
                            }
                        }
                        $selectBM .= ")";

                    } else {
                        $selectEmp .= "goal.goal_code = '".$goal[$g]."' or ";
                        $selectBM .= "goal.goal_code = '".$goal[$g]."' or ";
                    }
                }
            } else {
                $selectEmp .= " and 0 ";
                $selectBM .= " and 0 ";
            }

            $selectEmp .= ' order by goal.id, position.id';
            $selectBM .= ' order by ilp.goal_id';

            $dataEmp = DB::select(DB::raw($selectEmp));
            $dataBM = DB::select(DB::raw($selectBM));

            $arrayUniquePosition = array();
            $arrayP = array();
            foreach($dataBM as $bm){
                if(!in_array($bm->position_code, $arrayP)){
                    $arrayP[count($arrayP)] = $bm->position_code;
                    $index = count($arrayUniquePosition);
                    $arrayUniquePosition[$index]['position_code'] = $bm->position_code;
                    $arrayUniquePosition[$index]['position_name'] = $bm->position_name;
                }
            }

            $arrDataFinal = array();
            foreach($arrayUniquePosition as $po){
                $arrayParentGoal = array();
                $arrayFindGoal = array();
                foreach($dataBM as $bmark){
                    if($bmark->position_code == $po['position_code'] && $bmark->parent_id == 0 && (!in_array($bmark->goal_code, $arrayFindGoal))){
                        $arrayFindGoal[count($arrayFindGoal)] = $bmark->goal_code;
                        $index = count($arrayParentGoal);
                        $arrayParentGoal[$index]['goal_name'] = $bmark->goal_name;
                        $arrayParentGoal[$index]['goal_code'] = $bmark->goal_code;
                        $arrayParentGoal[$index]['goal_id'] = $bmark->goal_id;
                    }
                }

                $arrayGoal = array();
                foreach($arrayParentGoal as $gP){
                    $arrayChild = array();
                    $arrayFindGoalChild = array();
                    foreach($dataBM as $bemark){
                        if(!in_array($bemark->goal_code, $arrayFindGoalChild) && $bemark->parent_id == $gP['goal_id'] && $po['position_code'] == $bemark->position_code){
                            $arrayFindGoalChild[count($arrayFindGoalChild)] = $bemark->goal_code;
                            $index = count($arrayChild);
                            $arrayChild[$index]['goal_name'] = $bemark->goal_name;
                            $arrayChild[$index]['goal_code'] = $bemark->goal_code;
                            $arrayChild[$index]['goal_id'] = $bemark->goal_code;
                            $arrayChild[$index]['gt'] = $bemark->goal_type;
                        }
                    }
                    $index = count($arrayGoal);
                    $arrayGoal[$index]['parent_name'] = $gP['goal_name'];
                    $arrayGoal[$index]['parent_code'] = $gP['goal_code'];
                    $arrayGoal[$index]['parent_id'] = $gP['goal_id'];
                    $arrayGoal[$index]['child'] = $arrayChild;
                }

                $index = count($arrDataFinal);
                $arrDataFinal[$index]['position_name'] = $po['position_name'];
                $arrDataFinal[$index]['position_code'] = $po['position_code'] ;
                $arrDataFinal[$index]['goal'] = $arrayGoal;
            }

            $arrayData = array();
            foreach($arrDataFinal as $aF){
                $arrayEmp = array();
                foreach($dataEmp as $dE){
                    if(!in_array($dE->code, $arrayFindGoal) && $aF['position_code'] == $dE->position_code){
                        $arrayFindGoal[count($arrayFindGoal)] = $dE->code;
                        $index = count($arrayEmp);
                        $arrayEmp[$index]['name'] = $dE->name;
                        $arrayEmp[$index]['code'] = $dE->code;
                    }
                }

                $index = count($arrayData);
                $arrayData[$index]['position_name'] = $aF['position_name'];
                $arrayData[$index]['position_code'] = $aF['position_code'];
                $arrayData[$index]['goal'] = $aF['goal'];
                $arrayData[$index]['emp'] = $arrayEmp;
            }

            $arPoDefault = commonUtils::arrPositionCode();
            $arrDataFinalSort = array();
            foreach($arPoDefault as $def){
                foreach($arrayData as $d){
                    if($def == $d['position_code']){
                        $arrDataFinalSort[count($arrDataFinalSort)] = $d;
                    }
                }
            }

            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );

            $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/blank.xlsx");
            if($check == 2){
                $countMonth = (($toYear - $fromYear) * 12 + ($toMonth - $fromMonth + 1));
            } else {
                $countMonth = 1;
            }

            for($p=0; $p<count($arrDataFinalSort); $p++) {
                if ($p > 0) {
                    $objWorkSheet = $objPHPExcel->createSheet($p);
                }
                $objPHPExcel->setActiveSheetIndex($p);
                //set name for current sheet
                $positionName = $arrDataFinalSort[$p]['position_name'];
                $positionCode = $arrDataFinalSort[$p]['position_code'];

                $arrName = str_split($positionCode);
                $nameSuccess = '';
                foreach ($arrName as $n) {
                    if ($n == '/' || $n == '[' || $n == ']' || $n == '\\' || $n == ':' || $n == '/' || $n == '?') {
                        $nameSuccess .= ' ';
                    } else {
                        $nameSuccess .= $n;
                    }
                }

                $startRow = 1;
                $objPHPExcel->getActiveSheet()->setTitle($nameSuccess);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, 'BÁO CÁO KẾT QUẢ KPI');
                $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$startRow++)->getFont()->setSize(20);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow++, 'Chức danh: '. $positionName)
                                              ->setCellValue('A'.$startRow, 'Tháng: '.(($check==2) ? $fromMonth.'/'.$fromYear.' - '.$toMonth.'/'.$toYear : $month.'/'.$year));
                $objPHPExcel->getActiveSheet()->getStyle('A1:A'.$startRow)->getFont()->setBold(true);

                $startRow += 3;

                $arrayEmp = $arrDataFinalSort[$p]['emp'];

                $arrayUniqueGoal = $arrDataFinalSort[$p]['goal'];
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
                $startG = $startRow;

                $lastColumn = PHPExcel_Cell::stringFromColumnIndex(2+$countMonth*5);

                foreach($arrayEmp as $emp){
                    $first = $startG;
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$startG, 'Nhân viên: '. $emp['name']);

                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$startG.':C'.($startG+1));
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$startG.':'.$lastColumn.($startG+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$startG.':'.$lastColumn.($startG+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$startG.':'.$lastColumn.($startG+2))->getFont()->setBold(true);
                    $startG += 2;
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$startG, 'STT')
                                                ->setCellValue('B'.$startG, 'Tên mục tiêu')
                                                ->setCellValue('C'.$startG++, 'Loại mục tiêu');

                    $startColumn = 3;
                    if($check == 1 || ($fromMonth == $toMonth && $fromYear == $toYear)){
                        $cTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $cIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $cBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $cI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $cIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                        $objPHPExcel->getActiveSheet()->getColumnDimension($cTV)->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cIL)->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cBM)->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cI)->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cIP)->setWidth(15);

                        $objPHPExcel->getActiveSheet()->mergeCells($cTV.$first.':'.$cIP.$first);
                        $objPHPExcel->getActiveSheet()->mergeCells($cTV.($first+1).':'.$cIP.($first+1));

                        $objPHPExcel->getActiveSheet()->setCellValue("D".$first, 'Năm '.(($check == 1) ? $year : $fromYear))
                            ->setCellValue("D".($first+1), 'Tháng '.(($check == 1) ? $month : $fromYear))
                            ->setCellValue($cTV.($first+2), 'Kế hoạch')
                            ->setCellValue($cIL.($first+2), 'Tỷ trọng')
                            ->setCellValue($cBM.($first+2), 'Điểm chuẩn')
                            ->setCellValue($cI.($first+2), 'Thực hiện')
                            ->setCellValue($cIP.($first+2), 'Điểm thực hiện');
                    } else {

                        if($fromYear == $toYear){
                            $objPHPExcel->getActiveSheet()->mergeCells('D'.$first.':'.$lastColumn.$first);
                            $objPHPExcel->getActiveSheet()->setCellValue('D'.$first, 'Năm '.$fromYear);
                            for($f = $fromMonth; $f <= $toMonth; $f++){
                                $cTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                $objPHPExcel->getActiveSheet()->getColumnDimension($cTV)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cIL)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cBM)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cI)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cIP)->setWidth(15);

                                $objPHPExcel->getActiveSheet()->mergeCells($cTV.($first+1).':'.$cIP.($first+1));

                                $objPHPExcel->getActiveSheet()->setCellValue($cTV.($first+1), 'Tháng '. $f)
                                    ->setCellValue($cTV.($first+2), 'Kế hoạch')
                                    ->setCellValue($cIL.($first+2), 'Tỷ trọng')
                                    ->setCellValue($cBM.($first+2), 'Điểm chuẩn')
                                    ->setCellValue($cI.($first+2), 'Thực hiện')
                                    ->setCellValue($cIP.($first+2), 'Điểm thực hiện');
                            }
                        } else {
                            $cL = PHPExcel_Cell::stringFromColumnIndex($startColumn+(13-$fromMonth)*5 - 1);
                            $objPHPExcel->getActiveSheet()->mergeCells('D'.$first.':'.$cL.$first);
                            $objPHPExcel->getActiveSheet()->setCellValue('D'.$first, 'Năm '.$fromYear);
                            for ($m = $fromMonth; $m <= 12; $m++) {
                                $cTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                $objPHPExcel->getActiveSheet()->getColumnDimension($cTV)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cIL)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cBM)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cI)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cIP)->setWidth(15);

                                $objPHPExcel->getActiveSheet()->mergeCells($cTV.($first+1).':'.$cIP.($first+1));

                                $objPHPExcel->getActiveSheet()->setCellValue($cTV.($first+1), 'Tháng '. $m)
                                    ->setCellValue($cTV.($first+2), 'Kế hoạch')
                                    ->setCellValue($cIL.($first+2), 'Tỷ trọng')
                                    ->setCellValue($cBM.($first+2), 'Điểm chuẩn')
                                    ->setCellValue($cI.($first+2), 'Thực hiện')
                                    ->setCellValue($cIP.($first+2), 'Điểm thực hiện');
                            }

                            if ($toYear - $fromYear > 1) {
                                for ($y = $fromYear + 1; $y < $toYear; $y++) {
                                    $fL = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                    $cL = PHPExcel_Cell::stringFromColumnIndex($startColumn+ 12*5 - 1);
                                    $objPHPExcel->getActiveSheet()->mergeCells($fL.$first.':'.$cL.$first);
                                    $objPHPExcel->getActiveSheet()->setCellValue($fL.$first, 'Năm '.$y);

                                    for($m = 1; $m <= 12 ; $m++){
                                        $cTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $cIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $cBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $cI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $cIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                        $objPHPExcel->getActiveSheet()->getColumnDimension($cTV)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($cIL)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($cBM)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($cI)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($cIP)->setWidth(15);

                                        $objPHPExcel->getActiveSheet()->mergeCells($cTV.($first+1).':'.$cIP.($first+1));

                                        $objPHPExcel->getActiveSheet()->setCellValue($cTV.($first+1), 'Tháng '. $m)
                                            ->setCellValue($cTV.($first+2), 'Kế hoạch')
                                            ->setCellValue($cIL.($first+2), 'Tỷ trọng')
                                            ->setCellValue($cBM.($first+2), 'Điểm chuẩn')
                                            ->setCellValue($cI.($first+2), 'Thực hiện')
                                            ->setCellValue($cIP.($first+2), 'Điểm thực hiện');
                                    }
                                }
                            }

                            $fL = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $cL = PHPExcel_Cell::stringFromColumnIndex($startColumn+$toMonth*5 - 1);
                            $objPHPExcel->getActiveSheet()->mergeCells($fL.$first.':'.$cL.$first);
                            $objPHPExcel->getActiveSheet()->setCellValue($fL.$first, 'Năm '.$toYear);
                            for ($m = 1; $m <= $toMonth; $m++) {
                                $cTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $cIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                $objPHPExcel->getActiveSheet()->getColumnDimension($cTV)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cIL)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cBM)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cI)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($cIP)->setWidth(15);

                                $objPHPExcel->getActiveSheet()->mergeCells($cTV.($first+1).':'.$cIP.($first+1));

                                $objPHPExcel->getActiveSheet()->setCellValue($cTV.($first+1), 'Tháng '. $m)
                                    ->setCellValue($cTV.($first+2), 'Kế hoạch')
                                    ->setCellValue($cIL.($first+2), 'Tỷ trọng')
                                    ->setCellValue($cBM.($first+2), 'Điểm chuẩn')
                                    ->setCellValue($cI.($first+2), 'Thực hiện')
                                    ->setCellValue($cIP.($first+2), 'Điểm thực hiện');
                            }

                        }
                    }

                    foreach($arrayUniqueGoal as $goPE){
                        $child = $goPE['child'];
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$startG, $goPE['parent_name']);
                        // fill data
                        $start = 3;
                        if($check == 1 || ($fromMonth == $toMonth && $fromYear == $toYear)){
                            $start += 2;
                            $cBM = PHPExcel_Cell::stringFromColumnIndex($start);
                            $start += 2;
                            $cIP = PHPExcel_Cell::stringFromColumnIndex($start);

                            $month = (($check) ? $month : $fromMonth);
                            $year = (($check) ? $year : $fromYear);
                            $BM = 0;
                            $IP = 0;

                            foreach($dataBM as $dBM){
                                if($dBM->goal_code == $goPE['parent_code']&&
                                $dBM->position_code == $positionCode &&
                                $dBM->month == $month &&
                                $dBM->year == $year
                                ){
                                    $BM = $dBM->benchmark;
                                    break;
                                }
                            }

                            foreach($dataEmp as $dEm){
                                if($dEm->parent_id == $goPE['parent_id']&&
                                    $dEm->position_code == $positionCode &&
                                    $dEm->code == $emp['code'] &&
                                    $dEm->month == $month &&
                                    $dEm->year == $year
                                ){
                                    $IP += $dEm->implement_point;
                                }
                            }

                            $objPHPExcel->getActiveSheet()
                                ->setCellValue($cBM.$startG, $BM)
                                ->setCellValue($cIP.$startG, $IP);
                        } else {
                            if ($fromYear == $toYear) {
                                for ($f = $fromMonth; $f <= $toMonth; $f++) {
                                    $start += 2;
                                    $cBM = PHPExcel_Cell::stringFromColumnIndex($start);
                                    $start += 2;
                                    $cIP = PHPExcel_Cell::stringFromColumnIndex($start);
                                    $BM = 0;
                                    $IP = 0;
                                    foreach($dataBM as $dBM){
                                        if($dBM->goal_code == $goPE['parent_code']&&
                                            $dBM->position_code == $positionCode &&
                                            $dBM->month == $f &&
                                            $dBM->year == $fromYear
                                        ){
                                            $BM = $dBM->benchmark;
                                            break;
                                        }
                                    }

                                    foreach($dataEmp as $dEm){
                                        if($dEm->parent_id == $goPE['parent_id']&&
                                            $dEm->position_code == $positionCode &&
                                            $dEm->code == $emp['code'] &&
                                            $dEm->month == $f &&
                                            $dEm->year == $fromYear
                                        ){
                                            $IP += $dEm->implement_point;
                                        }
                                    }

                                    $objPHPExcel->getActiveSheet()
                                        ->setCellValue($cBM . $startG, $BM)
                                        ->setCellValue($cIP . $startG, $IP);
                                    $start++;
                                }
                            } else {
                                for ($m = $fromMonth; $m <= 12; $m++) {
                                    $start += 2;
                                    $cBM = PHPExcel_Cell::stringFromColumnIndex($start);
                                    $start += 2;
                                    $cIP = PHPExcel_Cell::stringFromColumnIndex($start);
                                    $BM = 0;
                                    $IP = 0;
                                    foreach($dataBM as $dBM){
                                        if($dBM->goal_code == $goPE['parent_code']&&
                                            $dBM->position_code == $positionCode &&
                                            $dBM->month == $m &&
                                            $dBM->year == $fromYear
                                        ){
                                            $BM = $dBM->benchmark;
                                            break;
                                        }
                                    }

                                    foreach($dataEmp as $dEm){
                                        if($dEm->parent_id == $goPE['parent_id']&&
                                            $dEm->position_code == $positionCode &&
                                            $dEm->code == $emp['code'] &&
                                            $dEm->month == $m &&
                                            $dEm->year == $fromYear
                                        ){
                                            $IP += $dEm->implement_point;
                                        }
                                    }
                                    $objPHPExcel->getActiveSheet()
                                        ->setCellValue($cBM . $startG, $BM)
                                        ->setCellValue($cIP . $startG, $IP);
                                    $start++;
                                }

                                if ($toYear - $fromYear > 1) {
                                    for ($y = ($fromYear + 1); $y < $toYear; $y++) {
                                        for ($mo = 1; $mo <= 12; $mo++) {
                                            $start += 2;
                                            $cBM = PHPExcel_Cell::stringFromColumnIndex($start);
                                            $start += 2;
                                            $cIP = PHPExcel_Cell::stringFromColumnIndex($start);
                                            $BM = 0;
                                            $IP = 0;

                                            foreach($dataBM as $dBM){
                                                if($dBM->goal_code == $goPE['parent_code']&&
                                                    $dBM->position_code == $positionCode &&
                                                    $dBM->month == $mo &&
                                                    $dBM->year == $y
                                                ){
                                                    $BM = $dBM->benchmark;
                                                    break;
                                                }
                                            }


                                            foreach($dataEmp as $dEm){
                                                if($dEm->parent_id == $goPE['parent_id']&&
                                                    $dEm->position_code == $positionCode &&
                                                    $dEm->code == $emp['code'] &&
                                                    $dEm->month == $mo &&
                                                    $dEm->year == $y
                                                ){
                                                    $IP += $dEm->implement_point;
                                                }
                                            }

                                            $objPHPExcel->getActiveSheet()
                                                ->setCellValue($cBM . $startG, $BM)
                                                ->setCellValue($cIP . $startG, $IP);
                                            $start++;
                                        }
                                    }
                                }

                                for ($m = 1; $m <= $toMonth; $m++) {
                                    $start += 2;
                                    $cBM = PHPExcel_Cell::stringFromColumnIndex($start);
                                    $start += 2;
                                    $cIP = PHPExcel_Cell::stringFromColumnIndex($start);
                                    $BM = 0;
                                    $IP = 0;
                                    foreach($dataBM as $dBM){
                                        if($dBM->goal_code == $goPE['parent_code']&&
                                            $dBM->position_code == $positionCode &&
                                            $dBM->month == $m &&
                                            $dBM->year == $toYear
                                        ){
                                            $BM = $dBM->benchmark;
                                            break;
                                        }
                                    }

                                    foreach($dataEmp as $dEm){
                                        if($dEm->parent_id == $goPE['parent_id']&&
                                            $dEm->position_code == $positionCode &&
                                            $dEm->code == $emp['code'] &&
                                            $dEm->month == $m &&
                                            $dEm->year == $toYear
                                        ){
                                            $IP += $dEm->implement_point;
                                        }
                                    }
                                    $objPHPExcel->getActiveSheet()
                                        ->setCellValue($cBM . $startG, $BM)
                                        ->setCellValue($cIP . $startG, $IP);
                                    $start++;
                                }

                            }
                        }

                        $objPHPExcel->getActiveSheet()->mergeCells('A'.$startG.':C'.$startG);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$startG.':'.$lastColumn.$startG)->getFill()
                            ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$startG.':'.$lastColumn.$startG)->getFont()->setBold(true);
                        $startG++;
                        $index = 1;
                        foreach($child as $goC){
                            $start = 3;
                            if($check == 1 || ($fromMonth == $toMonth && $fromYear == $toYear)){

                                $cTV = PHPExcel_Cell::stringFromColumnIndex($start++);
                                $cIL= PHPExcel_Cell::stringFromColumnIndex($start++);
                                $cBM = PHPExcel_Cell::stringFromColumnIndex($start++);
                                $cI = PHPExcel_Cell::stringFromColumnIndex($start++);
                                $cIP = PHPExcel_Cell::stringFromColumnIndex($start++);

                                $month = (($check) ? $month : $fromMonth);
                                $year = (($check) ? $year : $fromYear);
                                $BM = 0;
                                $IP = 0;
                                $TV = 0;
                                $IL = 0;
                                $I = 0;

                                foreach($dataBM as $dBM){
                                    if($dBM->goal_code == $goC['goal_code']&&
                                        $dBM->position_code == $positionCode &&
                                        $dBM->month == $month &&
                                        $dBM->year == $year
                                    ){
                                        $BM = $dBM->benchmark;
                                        $IL = $dBM->important_level;
                                        break;
                                    }
                                }

                                foreach($dataEmp as $dEm){
                                    if($dEm->goal_code == $goC['goal_code']&&
                                        $dEm->position_code == $positionCode &&
                                        $dEm->code == $emp['code'] &&
                                        $dEm->month == $month &&
                                        $dEm->year == $year
                                    ){
                                        $IP = $dEm->implement_point;
                                        $TV = $dEm->target_value;
                                        $I = $dEm->implement;
                                        break;
                                    }
                                }

                                $objPHPExcel->getActiveSheet()
                                    ->setCellValue($cBM.$startG, $BM)
                                    ->setCellValue($cIP.$startG, $IP)
                                    ->setCellValue($cTV.$startG, $TV)
                                    ->setCellValue($cIL.$startG, $IL)
                                    ->setCellValue($cI.$startG, $I);
                            } else {

                                if ($fromYear == $toYear) {
                                    for ($f = $fromMonth; $f <= $toMonth; $f++) {
                                        $cTV = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cIL= PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cBM = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cI = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cIP = PHPExcel_Cell::stringFromColumnIndex($start++);

                                        $BM = 0;
                                        $IP = 0;
                                        $TV = 0;
                                        $IL = 0;
                                        $I = 0;

                                        foreach($dataBM as $dBM){
                                            if($dBM->goal_code == $goC['goal_code']&&
                                                $dBM->position_code == $positionCode &&
                                                $dBM->month == $f &&
                                                $dBM->year == $fromYear
                                            ){
                                                $BM = $dBM->benchmark;
                                                $IL = $dBM->important_level;
                                                break;
                                            }
                                        }

                                        foreach($dataEmp as $dEm){
                                            if($dEm->goal_code == $goC['goal_code']&&
                                                $dEm->position_code == $positionCode &&
                                                $dEm->code == $emp['code'] &&
                                                $dEm->month == $f &&
                                                $dEm->year == $fromYear
                                            ){
                                                $IP = $dEm->implement_point;
                                                $TV = $dEm->target_value;
                                                $I = $dEm->implement;
                                                break;
                                            }
                                        }

                                        $objPHPExcel->getActiveSheet()
                                            ->setCellValue($cBM.$startG, $BM)
                                            ->setCellValue($cIP.$startG, $IP)
                                            ->setCellValue($cTV.$startG, $TV)
                                            ->setCellValue($cIL.$startG, $IL)
                                            ->setCellValue($cI.$startG, $I);
                                    }
                                } else {
                                    for ($m = $fromMonth; $m <= 12; $m++) {
                                        $cTV = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cIL= PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cBM = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cI = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cIP = PHPExcel_Cell::stringFromColumnIndex($start++);

                                        $BM = 0;
                                        $IP = 0;
                                        $TV = 0;
                                        $IL = 0;
                                        $I = 0;

                                        foreach($dataBM as $dBM){
                                            if($dBM->goal_code == $goC['goal_code']&&
                                                $dBM->position_code == $positionCode &&
                                                $dBM->month == $m &&
                                                $dBM->year == $fromYear
                                            ){
                                                $BM = $dBM->benchmark;
                                                $IL = $dBM->important_level;
                                                break;
                                            }
                                        }

                                        foreach($dataEmp as $dEm){
                                            if($dEm->goal_code == $goC['goal_code']&&
                                                $dEm->position_code == $positionCode &&
                                                $dEm->code == $emp['code'] &&
                                                $dEm->month == $m &&
                                                $dEm->year == $fromYear
                                            ){
                                                $IP = $dEm->implement_point;
                                                $TV = $dEm->target_value;
                                                $I = $dEm->implement;
                                                break;
                                            }
                                        }

                                        $objPHPExcel->getActiveSheet()
                                            ->setCellValue($cBM.$startG, $BM)
                                            ->setCellValue($cIP.$startG, $IP)
                                            ->setCellValue($cTV.$startG, $TV)
                                            ->setCellValue($cIL.$startG, $IL)
                                            ->setCellValue($cI.$startG, $I);
                                    }

                                    if ($toYear - $fromYear > 1) {
                                        for ($y = ($fromYear + 1); $y < $toYear; $y++) {
                                            for ($mo = 1; $mo <= 12; $mo++) {
                                                $cTV = PHPExcel_Cell::stringFromColumnIndex($start++);
                                                $cIL= PHPExcel_Cell::stringFromColumnIndex($start++);
                                                $cBM = PHPExcel_Cell::stringFromColumnIndex($start++);
                                                $cI = PHPExcel_Cell::stringFromColumnIndex($start++);
                                                $cIP = PHPExcel_Cell::stringFromColumnIndex($start++);

                                                $BM = 0;
                                                $IP = 0;
                                                $TV = 0;
                                                $IL = 0;
                                                $I = 0;

                                                foreach($dataBM as $dBM){
                                                    if($dBM->goal_code == $goC['goal_code']&&
                                                        $dBM->position_code == $positionCode &&
                                                        $dBM->month == $mo &&
                                                        $dBM->year == $y
                                                    ){
                                                        $BM = $dBM->benchmark;
                                                        $IL = $dBM->important_level;
                                                        break;
                                                    }
                                                }

                                                foreach($dataEmp as $dEm){
                                                    if($dEm->goal_code == $goC['goal_code']&&
                                                        $dEm->position_code == $positionCode &&
                                                        $dEm->code == $emp['code'] &&
                                                        $dEm->month == $mo &&
                                                        $dEm->year == $y
                                                    ){
                                                        $IP = $dEm->implement_point;
                                                        $TV = $dEm->target_value;
                                                        $I = $dEm->implement;
                                                        break;
                                                    }
                                                }

                                                $objPHPExcel->getActiveSheet()
                                                    ->setCellValue($cBM.$startG, $BM)
                                                    ->setCellValue($cIP.$startG, $IP)
                                                    ->setCellValue($cTV.$startG, $TV)
                                                    ->setCellValue($cIL.$startG, $IL)
                                                    ->setCellValue($cI.$startG, $I);
                                            }
                                        }
                                    }

                                    for ($m = 1; $m <= $toMonth; $m++) {
                                        $cTV = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cIL= PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cBM = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cI = PHPExcel_Cell::stringFromColumnIndex($start++);
                                        $cIP = PHPExcel_Cell::stringFromColumnIndex($start++);

                                        $BM = 0;
                                        $IP = 0;
                                        $TV = 0;
                                        $IL = 0;
                                        $I = 0;

                                        foreach($dataBM as $dBM){
                                            if($dBM->goal_code == $goC['goal_code']&&
                                                $dBM->position_code == $positionCode &&
                                                $dBM->month == $m &&
                                                $dBM->year == $toYear
                                            ){
                                                $BM = $dBM->benchmark;
                                                $IL = $dBM->important_level;
                                                break;
                                            }
                                        }

                                        foreach($dataEmp as $dEm){
                                            if($dEm->goal_code == $goC['goal_code']&&
                                                $dEm->position_code == $positionCode &&
                                                $dEm->code == $emp['code'] &&
                                                $dEm->month == $m &&
                                                $dEm->year == $toYear
                                            ){
                                                $IP = $dEm->implement_point;
                                                $TV = $dEm->target_value;
                                                $I = $dEm->implement;
                                                break;
                                            }
                                        }

                                        $objPHPExcel->getActiveSheet()
                                            ->setCellValue($cBM.$startG, $BM)
                                            ->setCellValue($cIP.$startG, $IP)
                                            ->setCellValue($cTV.$startG, $TV)
                                            ->setCellValue($cIL.$startG, $IL)
                                            ->setCellValue($cI.$startG, $I);
                                    }

                                }
                            }
                            $objPHPExcel->getActiveSheet()->setCellValue('A'.$startG, $index++)
                                                            ->setCellValue('B'.$startG, $goC['goal_name'])
                                                            ->setCellValue('C'.$startG++, commonUtils::renderGoalTypeName( $goC['gt']));
                        }

                    }

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$first.':'.$lastColumn.($startG-1))->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$first.':'.$lastColumn.($startG-1))->getNumberFormat()->setFormatCode('#,##0.000');
                    $startG += 3;

                }

                excelUtils::setZoomSheet($objPHPExcel, 80);
            }

            $objPHPExcel->setActiveSheetIndex(0);
            $fileName = 'BaoCaoKetQuaKPI';
            $this->outputFile($fileName, $objPHPExcel);
        }
    }

    /**
     * export data: detail position
     */
    public function reportDetailByPosition($check, $month, $year, $fromMonth, $toMonth, $fromYear, $toYear, $company, $area, $position, $goal, $posChoose){
        if($area == 'No'){
            Session::flash('message-errors', "Vui lòng chọn một khu vực trước khi xuất excel.");
            return redirect('reportKPIResult');
        } else {
            $goal = explode(",", $goal);
            $positionF = explode(",", $position);
            $position = array();
            for($p=0; $p<count($positionF); $p++){
                $positionFinal = '';
                $arrName = str_split($positionF[$p]);
                foreach($arrName as $n){
                    if($n == '*'){
                        $positionFinal.= '/';
                    } else {
                        $positionFinal.=$n;
                    }
                }
                $position[count($position)] = $positionFinal;
            }

            $select = 'SELECT sum(tp.implement_point) as implementPoint,
 						  position.position_name,
						  position.position_code, tp.month, tp.year
                   FROM  target_position AS tp
                   JOIN goal ON goal.id = tp.goal_id
                   JOIN company ON company.id = tp.company_id
                   JOIN `position` ON position.id = tp.position_id
                   JOIN area ON area.id = tp.area_id
                   WHERE tp.inactive = 0 and goal.parent_id > 0
                    ';

            if($check == 2){
                // time to time
                if($fromYear == $toYear){
                    $select .= ' and tp.year='.$fromYear.' and tp.month <='.$toMonth.
                        ' and tp.month >='.$fromMonth;
                } else {
                    $select .= ' and ((tp.month >= '.$fromMonth.' and tp.year = '.$fromYear.') or
						(tp.month <= '.$toMonth.'  and tp.year = '.$toYear.') or (tp.year > '.$fromYear.' and tp.year < '.$toYear.')'
						;
                }
            } else {
                $select .= ' and tp.month = '.$month.' and tp.year = '.$year;
            }

            $select .= " and company.company_code = '".$company."' ";
            if($area == 'No'){
                $select .= ' and 0 ';
            } else {
                $select .= " and area.area_code = '".$area."' ";
            }

            //goal
            if($goal != null){
                $select .= " and ( ";

                for($g=0; $g<count($goal); $g++){
                    if($g == (count($goal)-1)){
                        $select .= "goal.goal_code = '".$goal[$g]."' )";
                    } else {
                        $select .= "goal.goal_code = '".$goal[$g]."' or ";
                    }
                }
            } else {
                $select .= " and 0 ";
            }

            //position
            if($position != null){
                $select .= " and ( ";
                for($g=0; $g<count($position); $g++){
                    if($g == (count($position)-1)){
                        $select .= "position.position_code = '".$position[$g]."' )";
                    } else {
                        $select .= "position.position_code = '".$position[$g]."' or ";
                    }
                }
            } else {
                $select .= " and 0 ";
            }

            $select .= ' group by position.position_code, tp.month, tp.year
					order by position.id';

            $data = DB::select(DB::raw($select));

            $arrayPosition = array();
            if(count($data) > 0){
                foreach($data as $row){
                    $countPosition = 0;
                    foreach($arrayPosition as $rowP){
                        if($rowP['position_code'] == $row->position_code){
                            $countPosition = 1;
                        }
                    }

                    if($countPosition == 0){
                        $index = count($arrayPosition);
                        $arrayPosition[$index]['position_code'] = $row->position_code;
                        $arrayPosition[$index]['position_name'] = $row->position_name;
                    }
                }
            }

            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );

            $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/blank.xlsx");
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('BaoCaoChiTietTheoChucDanh');
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BÁO CÁO CHI TIẾT THEO CHỨC DANH');
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20)->setBold(true);

            $companyName = DB::table('company')->where('inactive', 0)->where('company_code', $company)->first();
            $areaName = DB::table('area')->where('inactive', 0)->where('area_code', $area)->first();
            $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Phòng/Đài/MBF HCM: ')->setCellValue('D3', $companyName->company_name)
                ->setCellValue('C4', 'Tổ/Quận/Huyện: ')->setCellValue('D4', $areaName->area_name);


            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'Tháng: ');
            if($check == 1){
                $objPHPExcel->getActiveSheet()->setCellValue('D5', $month.'/'.$year);
            } else if(($fromMonth == $toMonth) && ($fromYear == $toYear)){
                $objPHPExcel->getActiveSheet()->setCellValue('D5', $fromMonth.'/'.$fromYear);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('D5', $fromMonth.'/'.$fromYear.' - '.$toMonth.'/'.$toYear);
            }
            $objPHPExcel->getActiveSheet()->getStyle('C3:C5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('C3:D5')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);

            $objPHPExcel->getActiveSheet()->mergeCells('B7:B8');
            $objPHPExcel->getActiveSheet()->mergeCells('C7:C8');

            $objPHPExcel->getActiveSheet()->setCellValue('B7', 'STT')->setCellValue('C7', 'Chức danh');
            $startRow = 8;
            if(($check == 1) || ($fromMonth == $toMonth && $fromYear == $toYear)){
                $objPHPExcel->getActiveSheet()->mergeCells('D7:D8');
                if($check == 1){
                    $objPHPExcel->getActiveSheet()->setCellValue('D7', 'Tháng '.$month);
                } else if($fromMonth == $toMonth && $fromYear == $toYear){
                    $objPHPExcel->getActiveSheet()->setCellValue('D7', 'Tháng '.$fromMonth);
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                $index = 0;
                foreach($data as $rowData){
                    $startRow++;
                    $index++;
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$startRow, $index)
                        ->setCellValue('C'.$startRow, $rowData->position_name)
                        ->setCellValue('D'.$startRow, $rowData->implementPoint);
                }
                $objPHPExcel->getActiveSheet()->getStyle('B7:D'.$startRow)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('B7:D8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B7:B'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B7:D8')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B7:D8')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('B7:D8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                $objPHPExcel->getActiveSheet()->getStyle('D9:D'.$startRow)->getNumberFormat()->setFormatCode('#,##0.000');
            } else {
                $startColumn = 3;
                if($fromYear == $toYear){
                    $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+$toMonth-$fromMonth);
                    $objPHPExcel->getActiveSheet()->mergeCells('D7:'.$toColumn.'7');
                    $objPHPExcel->getActiveSheet()->setCellValue('D7', 'Năm '.$fromYear);
                    for($m = $fromMonth; $m <= $toMonth; $m++){
                        $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                        $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'8', 'Tháng '.$m);
                    }
                    $index = 0;
                    foreach($arrayPosition as $rowPo){
                        $startRow++;
                        $index++;
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$startRow, $index)->setCellValue('C'.$startRow, $rowPo['position_name']);
                        $startColumn = 3;
                        for($m = $fromMonth; $m <= $toMonth; $m++){
                            $ip = 0;
                            foreach($data as $rowDT){
                                if($rowPo['position_code'] == $rowDT->position_code &&
                                    $rowDT->month == $m && $rowDT->year == $fromYear){
                                    $ip = $rowDT->implementPoint;
                                }
                            }
                            $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                            $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                        }
                    }

                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.$startRow)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle('B9:B'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                    $objPHPExcel->getActiveSheet()->getStyle('D9:'.$toColumn.$startRow)->getNumberFormat()->setFormatCode('#,##0.000');
                } else {
                    $objPHPExcel->getActiveSheet()->freezePane('D9');
                    $fromFirstColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                    $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+12-$fromMonth);
                    $objPHPExcel->getActiveSheet()->mergeCells($fromFirstColumn.'7:'.$toColumn.'7');
                    $objPHPExcel->getActiveSheet()->setCellValue($fromFirstColumn.'7', 'Năm '.$fromYear);
                    for($m = $fromMonth; $m <= 12; $m++){
                        $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                        $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'8', 'Tháng '.$m);
                    }


                    if($toYear-$fromYear > 1){
                        for($y = $fromYear+1; $y < $toYear; $y++){
                            $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+11);
                            $objPHPExcel->getActiveSheet()->mergeCells($from.'7:'.$toColumn.'7');
                            $objPHPExcel->getActiveSheet()->setCellValue($from.'7', 'Năm '.$y);
                            for($m = 1; $m <= 12; $m++){
                                $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                                $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'8', 'Tháng '.$m);
                            }
                        }
                    }

                    $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                    $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+$toMonth-1);
                    $objPHPExcel->getActiveSheet()->mergeCells($from.'7:'.$toColumn.'7');
                    $objPHPExcel->getActiveSheet()->setCellValue($from.'7', 'Năm '.$toYear);
                    for($m = 1; $m <= $toMonth; $m++){
                        $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                        $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'8', 'Tháng '.$m);
                    }


                    $index = 0;
                    foreach($arrayPosition as $rowPo){
                        $startRow++;
                        $index++;
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$startRow, $index)->setCellValue('C'.$startRow, $rowPo['position_name']);
                        $startColumn = 3;
                        for($m = $fromMonth; $m <= 12; $m++){
                            $ip = 0;
                            foreach($data as $rowDT){
                                if($rowPo['position_code'] == $rowDT->position_code &&
                                    $rowDT->month == $m && $rowDT->year == $fromYear){
                                    $ip = $rowDT->implementPoint;
                                }
                            }
                            $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                            $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                        }

                        if($toYear-$fromYear > 1){
                            for($y = $fromYear+1; $y < $toYear; $y++){
                                for($m = 1; $m <= 12; $m++){
                                    $ip = 0;
                                    foreach($data as $rowDT){
                                        if($rowPo['position_code'] == $rowDT->position_code &&
                                            $rowDT->month == $m && $rowDT->year == $y){
                                            $ip = $rowDT->implementPoint;
                                        }
                                    }
                                    $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                                    $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                                }
                            }
                        }
                        for($m = 1; $m <= $toMonth; $m++){
                            $ip = 0;
                            foreach($data as $rowDT){
                                if($rowPo['position_code'] == $rowDT->position_code &&
                                    $rowDT->month == $m && $rowDT->year == $toYear){
                                    $ip = $rowDT->implementPoint;
                                }
                            }
                            $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                            $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                        }
                    }

                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.$startRow)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle('B9:B'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('B7:'.$toColumn.'8')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                    $objPHPExcel->getActiveSheet()->getStyle('D9:'.$toColumn.$startRow)->getNumberFormat()->setFormatCode('#,##0.000');

                }
            }

            //paint position choose
            if($posChoose != '1'){
                $pos = explode(",", $posChoose);
                $posChoose = array();
                for($p=0; $p<count($pos); $p++){
                    $positionFinal = '';
                    $arrName = str_split($pos[$p]);
                    foreach($arrName as $n){
                        if($n == '*'){
                            $positionFinal.= '/';
                        } else {
                            $positionFinal.=$n;
                        }
                    }
                    $posChoose[count($posChoose)] = $positionFinal;
                }
                $select = 'SELECT   tp.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , unit.unit_name
                            , unit.id as unit_id,
                            unit.unit_name, position.position_name, position.position_code
                        FROM  target_position AS tp
                        JOIN goal ON goal.id = tp.goal_id
                        JOIN unit ON unit.id = tp.unit_id
                        JOIN company ON company.id = tp.company_id
                        JOIN `position` ON position.id = tp.position_id
                        JOIN area ON area.id = tp.area_id
                        WHERE tp.inactive = 0
                    ';;

                if($check == 2){
                    // time to time
                    if($fromYear == $toYear){
                        $select .= ' and tp.year='.$fromYear.' and tp.month <='.$toMonth.
                            ' and tp.month >='.$fromMonth;
                    } else {
                        $select .= ' and ((tp.month >= '.$fromMonth.' and tp.year = '.$fromYear.') or
						(tp.month <= '.$toMonth.'  and tp.year = '.$toYear.') or (tp.year > '.$fromYear.' and tp.year < '.$toYear.')'
						;
                    }
                } else {
                    $select .= ' and tp.month = '.$month.' and tp.year = '.$year;
                }

                $select .= " and company.company_code = '".$company."' ";

                if($area == 'No'){
                    $select .= ' and 0 ';
                } else {
                    $select .= " and area.area_code = '".$area."' ";
                }

                $select .= " and ( ";
                if($posChoose != null){
                    for($p=0; $p<count($posChoose); $p++){
                        if($p == (count($posChoose)-1)){
                            $select .= "position.position_code = '".$posChoose[$p]."' )";
                        } else {
                            $select .= "position.position_code = '".$posChoose[$p]."' or ";
                        }
                    }
                } else {
                    $select .= '0 )';
                }

                //goal
                if($goal != null){
                    $select .= " and ( ";
                    for($g=0; $g<count($goal); $g++){
                        if($g == (count($goal)-1)){
                            $select .= "goal.goal_code = '".$goal[$g]."' )";
                        } else {
                            $select .= "goal.goal_code = '".$goal[$g]."' or ";
                        }
                    }
                } else {
                    $select .= " and 0 ";
                }
                $data = DB::select(DB::raw($select));

                $arrayPositionParent = array();
                foreach($data as $da){
                    $countPosition = 0;
                    foreach($arrayPositionParent as $rowCP){
                        if($da->position_code == $rowCP['position_code']){
                            $countPosition = 1;
                        }
                    }
                    if($countPosition == 0){
                        $arrayParent= array();
                        foreach($data as $rowDT){
                            if($rowDT->position_code == $da->position_code){
                                $countGoal = 0;
                                foreach($arrayParent as $rowParent){
                                    if($rowParent['id'] == $rowDT->parent_id){
                                        $countGoal = 1;
                                    }
                                }
                                if($countGoal == 0){
                                    $parent = DB::table('goal')->where('inactive', 0)->where('id', $rowDT->parent_id)->first();
                                    $in = count($arrayParent);
                                    $arrayParent[$in]['id'] = $parent->id;
                                    $arrayParent[$in]['goal_name'] = $parent->goal_name;
                                    $arrayParent[$in]['goal_code'] = $parent->goal_code;
                                }
                            }
                        }
                        $index = count($arrayPositionParent);
                        $arrayPositionParent[$index]['position_name'] = $da->position_name;
                        $arrayPositionParent[$index]['position_code'] = $da->position_code;
                        $arrayPositionParent[$index]['goal_parent'] = $arrayParent;
                    }
                }
                for($p=0; $p<count($arrayPositionParent); $p++){
                    $objWorkSheet = $objPHPExcel->createSheet($p+1);

                    $arrName = str_split($arrayPositionParent[$p]['position_code']);
                    $nameSuccess = '';
                    foreach($arrName as $n){
                        if($n=='/' || $n=='[' || $n==']' || $n=='\\' || $n==':' || $n == '/' || $n=='?'){
                            $nameSuccess.= ' ';
                        } else {
                            $nameSuccess.=$n;
                        }
                    }
                    $objPHPExcel->setActiveSheetIndex($p+1);
                    $objPHPExcel->getActiveSheet()->setTitle($nameSuccess);

                    $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
                    $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
                    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BÁO CÁO CHI TIẾT THEO CHỨC DANH');
                    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20)->setBold(true);

                    $companyName = DB::table('company')->where('inactive', 0)->where('company_code', $company)->first();
                    $areaName = DB::table('area')->where('inactive', 0)->where('area_code', $area)->first();
                    $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Phòng/Đài/MBF HCM: ')->setCellValue('D3', $companyName->company_name)
                        ->setCellValue('C4', 'Tổ/Quận/Huyện: ')->setCellValue('D4', $areaName->area_name)
                        ->setCellValue('C5', 'Chức danh: ')->setCellValue('D5', $arrayPositionParent[$p]['position_name']);


                    $objPHPExcel->getActiveSheet()->setCellValue('C6', 'Tháng: ');
                    if($check == 1){
                        $objPHPExcel->getActiveSheet()->setCellValue('D6', $month.'/'.$year);
                    } else if(($fromMonth == $toMonth) && ($fromYear == $toYear)){
                        $objPHPExcel->getActiveSheet()->setCellValue('D6', $fromMonth.'/'.$fromYear);
                    } else {
                        $objPHPExcel->getActiveSheet()->setCellValue('D6', $fromMonth.'/'.$fromYear.' - '.$toMonth.'/'.$toYear);
                    }
                    $objPHPExcel->getActiveSheet()->getStyle('C3:C6')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('C3:D6')->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);

                    $startRow = 8;
                    $arrParent = $arrayPositionParent[$p]['goal_parent'];

                    // paint header
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$startRow.':A'.($startRow+2));
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$startRow.':B'.($startRow+2));
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.$startRow.':C'.($startRow+2));
                    $objPHPExcel->getActiveSheet()->mergeCells('D'.$startRow.':D'.($startRow+2));

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, 'STT')
                        ->setCellValue('B'.$startRow, 'Mã')
                        ->setCellValue('C'.$startRow, 'Tên mục tiêu')
                        ->setCellValue('D'.$startRow, 'Loại mục tiêu');
                    $rowStart = $startRow;
                    $startRow += 2;
                    $arrayGoalIndex = array();

                    foreach($arrParent as $rowP){
                        $isExists = 0;
                        foreach($data as $rDT){
                            if($rDT->position_code == $arrayPositionParent[$p]['position_code'] && $rDT->parent_id == $rowP['id'] &&
                                ($rDT->important_level > 0 || $rDT->benchmark > 0 ||
                                    $rDT->target_value > 0 || $rDT->implement > 0 ||
                                    $rDT->implement_point > 0)){
                                $isExists = 1;
                                break;
                            }
                        }

                        if($isExists){
                            $startRow++;
                            $objPHPExcel->getActiveSheet()->mergeCells('A'.$startRow.':D'.$startRow);
                            $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $rowP['goal_name']);
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$startRow.':D'.$startRow)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                            $i = count($arrayGoalIndex);
                            $arrayGoalIndex[$i]['goal_code'] = $rowP['goal_code'];
                            $arrayGoalIndex[$i]['index'] = $startRow;
                            $arrayGoalIndex[$i]['parent_id'] = 0;
                            $arrayGoalIndex[$i]['id'] = $rowP['id'];
                            $index = 0;
                            foreach($data as $rDaTa){
                                if($rDaTa->position_code == $arrayPositionParent[$p]['position_code'] && $rDaTa->parent_id == $rowP['id'] &&
                                    ($rDaTa->important_level > 0 || $rDaTa->benchmark > 0 ||
                                        $rDaTa->target_value > 0 || $rDaTa->implement > 0 ||
                                        $rDaTa->implement_point > 0)){
                                    $count = 0;
                                    foreach($arrayGoalIndex as $row){
                                        if($row['goal_code'] == $rDaTa->goal_code){
                                            $count = 1;
                                            break;
                                        }
                                    }
                                    if($count == 0){
                                        $index ++;
                                        $startRow++;
                                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $index)
                                            ->setCellValue('B'.$startRow, $rDaTa->goal_code)
                                            ->setCellValue('C'.$startRow, $rDaTa->goal_name)
                                            ->setCellValue('D'.$startRow, commonUtils::renderGoalTypeName($rDaTa->goal_type));
                                        $i = count($arrayGoalIndex);
                                        $arrayGoalIndex[$i]['goal_code'] = $rDaTa->goal_code;
                                        $arrayGoalIndex[$i]['index'] = $startRow;
                                        $arrayGoalIndex[$i]['parent_id'] = $rDaTa->parent_id;
                                        $arrayGoalIndex[$i]['id'] = -1;
                                        $objPHPExcel->getActiveSheet()->getStyle('A'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    }
                                }
                            }
                        }
                    }

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.$startRow)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

                    $startRow = 8;
                    if(($check == 1) || ($fromMonth == $toMonth && $fromYear == $toYear)){
                        $objPHPExcel->getActiveSheet()->mergeCells('E'.$startRow.':J'.$startRow);
                        $objPHPExcel->getActiveSheet()->mergeCells('E'.($startRow+1).':J'.($startRow+1));
                        if($check == 1){
                            $objPHPExcel->getActiveSheet()->setCellValue('E'.$startRow, 'Năm '.$year)->setCellValue('E'.($startRow+1), 'Tháng '.$month);
                        } else {
                            $objPHPExcel->getActiveSheet()->setCellValue('E'.$startRow, 'Năm '.$fromYear)->setCellValue('E'.($startRow+1), 'Tháng '.$fromMonth);
                        }
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.($startRow+2), 'Tỷ trọng')->setCellValue('F'.($startRow+2), 'Điểm chuẩn')
                            ->setCellValue('G'.($startRow+2), 'Kế hoạch')->setCellValue('H'.($startRow+2), 'Đơn vị tính')
                            ->setCellValue('I'.($startRow+2), 'Thực hiện')->setCellValue('J'.($startRow+2), 'Điểm thực hiện');
                        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

                        $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->applyFromArray($styleArray);

                        //paint data
                        $startRow += 3;
                        $end = 0;
                        foreach($arrayGoalIndex as $rowGI){
                            if($rowGI['parent_id'] == 0){
                                $sumIP = 0;
                                $sumBM = 0;
                                foreach($data as $DT){
                                    if($rowGI['id'] == $DT->parent_id && $arrayPositionParent[$p]['position_code'] == $DT->position_code){
                                        $sumIP += round($DT->implement_point, 3);
                                        $sumBM += round($DT->benchmark, 3);
                                    }
                                }

                                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowGI['index'], $sumIP)->setCellValue('F'.$rowGI['index'], $sumBM);
                                $objPHPExcel->getActiveSheet()->getStyle('D'.$rowGI['index'].':J'.$rowGI['index'])->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                            } else {
                                $ip = 0;
                                $i = 0;
                                $tv = 0;
                                $il = 0;
                                $bm = 0;
                                $unitName = '';
                                foreach($data as $DaTa){
                                    if($rowGI['goal_code'] == $DaTa->goal_code && $arrayPositionParent[$p]['position_code'] == $DaTa->position_code){
                                        $ip = $DaTa->implement_point;
                                        $i = $DaTa->implement;
                                        $tv = $DaTa->target_value;
                                        $il = $DaTa->important_level;
                                        $bm = $DaTa->benchmark;
                                        $unitName = $DaTa->unit_name;
                                        break;
                                    }
                                }

                                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowGI['index'], $il)->setCellValue('F'.$rowGI['index'], $bm)
                                    ->setCellValue('G'.$rowGI['index'], $tv)->setCellValue('H'.$rowGI['index'], $unitName)
                                    ->setCellValue('I'.$rowGI['index'], $i)->setCellValue('J'.$rowGI['index'], $ip);
                                $end = $rowGI['index'];
                            }

                        }
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$startRow.':J'.$end)->applyFromArray($styleArray);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':G'.$end)->getNumberFormat()->setFormatCode('#,##0.000');
                        $objPHPExcel->getActiveSheet()->getStyle('I'.$startRow.':J'.$end)->getNumberFormat()->setFormatCode('#,##0.000');
                    } else {
                        $startColumn = 4;
                        $startColumnPosition = 0;
                        $objPHPExcel->getActiveSheet()->freezePane('E11');
                        if($fromYear == $toYear){
                            $fromFirst = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+($toMonth-$fromMonth+1)*6 -1);
                            $objPHPExcel->getActiveSheet()->mergeCells($fromFirst.$startRow.':'.$to.$startRow);
                            $objPHPExcel->getActiveSheet()->setCellValue($fromFirst.$startRow, 'Năm '.$fromYear);
                            $startRow++;
                            for($m = $fromMonth; $m <= $toMonth; $m++){
                                $startColumnPosition = $startRow;
                                $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                                $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnPosition.':'.$toColumn.$startColumnPosition);
                                $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnPosition, 'Tháng '.$m);

                                $startColumnPosition++;
                                $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnPosition, 'Kế hoạch');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnPosition, 'Tỷ trọng');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnPosition, 'Điểm chuẩn ');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnI.$startColumnPosition, 'Thực hiện');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnPosition, 'Điểm thực hiện');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnPosition, 'Đơn vị tính');

                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnI)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                                //data for each month
                                foreach($arrayGoalIndex as $rowGI){
                                    if($rowGI['parent_id'] == 0){
                                        $sumIP = 0;
                                        $sumBM = 0;
                                        foreach($data as $DT){
                                            if($rowGI['id'] == $DT->parent_id && $arrayPositionParent[$p]['position_code'] == $DT->position_code &&
                                                $DT->month == $m && $DT->year == $fromYear){
                                                $sumIP += round($DT->implement_point, 3);
                                                $sumBM += round($DT->benchmark, 3);
                                            }
                                        }
                                        $startColumnPosition++;
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                        $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnIP.$rowGI['index'])->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                    } else {
                                        $ip = 0;
                                        $i = 0;
                                        $tv = 0;
                                        $il = 0;
                                        $bm = 0;
                                        $unitName = '_';
                                        foreach($data as $rowData){
                                            if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                                ($arrayPositionParent[$p]['position_code'] == $rowData->position_code) &&
                                                ($rowData->month == $m) && ($rowData->year == $fromYear)){
                                                $ip = $rowData->implement_point;
                                                $i = $rowData->implement;
                                                $tv = $rowData->target_value;
                                                $il = $rowData->important_level;
                                                $bm = $rowData->benchmark;
                                                $unitName = $rowData->unit_name;
                                                break;
                                            }
                                        }
                                        $startColumnPosition++;
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                            ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                            ->setCellValue($columnI.$rowGI['index'], $i)->setCellValue($columnIP.$rowGI['index'], $ip);
                                    }
                                }
                                $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                                $objPHPExcel->getActiveSheet()->getStyle($columnI.($startRow+2).':'.$columnIP.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                            }

                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.($startRow+1))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.($startRow+1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.($startRow+1))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.$startColumnPosition)->applyFromArray($styleArray);
                        } else {
                            $rowYear = $startRow;
                            $fromFirst = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+(13-$fromMonth)*6 -1);
                            $objPHPExcel->getActiveSheet()->mergeCells($fromFirst.$rowYear.':'.$to.$rowYear);
                            $objPHPExcel->getActiveSheet()->setCellValue($fromFirst.$rowYear, 'Năm '.$fromYear);
                            $rowYear++;
                            for($m=$fromMonth; $m <= 12; $m++){
                                $startColumnPosition = $rowYear;
                                $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                                $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnPosition.':'.$toColumn.$startColumnPosition);
                                $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnPosition, 'Tháng '.$m);

                                $startColumnPosition++;
                                $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnPosition, 'Kế hoạch');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnPosition, 'Tỷ trọng');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnPosition, 'Điểm chuẩn ');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnI.$startColumnPosition, 'Thực hiện');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnPosition, 'Điểm thực hiện');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnPosition, 'Đơn vị tính');

                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnI)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                                // data for each month
                                foreach($arrayGoalIndex as $rowGI){
                                    if($rowGI['parent_id'] == 0){
                                        $sumIP = 0;
                                        $sumBM = 0;
                                        foreach($data as $DT){
                                            if($rowGI['id'] == $DT->parent_id && $arrayPositionParent[$p]['position_code'] == $DT->position_code &&
                                                $DT->month == $m && $DT->year == $fromYear){
                                                $sumIP += round($DT->implement_point, 3);
                                                $sumBM += round($DT->benchmark, 3);
                                            }
                                        }
                                        $startColumnPosition++;
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                        $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnIP.$rowGI['index'])->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                    } else {
                                        $ip = 0;
                                        $i = 0;
                                        $tv = 0;
                                        $il = 0;
                                        $bm = 0;
                                        $unitName = '_';
                                        foreach($data as $rowData){
                                            if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                                ($arrayPositionParent[$p]['position_code'] == $rowData->position_code) &&
                                                ($rowData->month == $m) && ($rowData->year == $fromYear)){
                                                $ip = $rowData->implement_point;
                                                $i = $rowData->implement;
                                                $tv = $rowData->target_value;
                                                $il = $rowData->important_level;
                                                $bm = $rowData->benchmark;
                                                $unitName = $rowData->unit_name;
                                                break;
                                            }
                                        }
                                        $startColumnPosition++;
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                            ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                            ->setCellValue($columnI.$rowGI['index'], $i)->setCellValue($columnIP.$rowGI['index'], $ip);
                                    }
                                }
                                $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                                $objPHPExcel->getActiveSheet()->getStyle($columnI.($startRow+2).':'.$columnIP.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');

                            }

                            if($toYear-$fromYear > 1){
                                for($y = $fromYear+1; $y < $toYear; $y++){
                                    $rowYear = $startRow;
                                    $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                    $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+12*6 -1);
                                    $objPHPExcel->getActiveSheet()->mergeCells($from.$rowYear.':'.$to.$rowYear);
                                    $objPHPExcel->getActiveSheet()->setCellValue($from.$rowYear, 'Năm '.$y);
                                    $rowYear++;
                                    for($m = 1; $m <= 12 ; $m++){
                                        $startColumnPosition = $rowYear;
                                        $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                        $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                                        $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnPosition.':'.$toColumn.$startColumnPosition);
                                        $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnPosition, 'Tháng '.$m);

                                        $startColumnPosition++;
                                        $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $columnI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                        $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                        $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnPosition, 'Kế hoạch');
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnPosition, 'Tỷ trọng');
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnPosition, 'Điểm chuẩn ');
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnI.$startColumnPosition, 'Thực hiện');
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnPosition, 'Điểm thực hiện');
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnPosition, 'Đơn vị tính');

                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnI)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                                        foreach($arrayGoalIndex as $rowGI){
                                            if($rowGI['parent_id'] == 0){
                                                $sumIP = 0;
                                                $sumBM = 0;
                                                foreach($data as $DT){
                                                    if($rowGI['id'] == $DT->parent_id && $arrayPositionParent[$p]['position_code'] == $DT->position_code &&
                                                        $DT->month == $m && $DT->year == $y){
                                                        $sumIP += round($DT->implement_point, 3);
                                                        $sumBM += round($DT->benchmark, 3);
                                                    }
                                                }
                                                $startColumnPosition++;
                                                $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                                $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnIP.$rowGI['index'])->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                            } else {
                                                $ip = 0;
                                                $i = 0;
                                                $tv = 0;
                                                $il = 0;
                                                $bm = 0;
                                                $unitName = '_';
                                                foreach($data as $rowData){
                                                    if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                                        ($arrayPositionParent[$p]['position_code'] == $rowData->position_code) &&
                                                        ($rowData->month == $m) && ($rowData->year == $y)){
                                                        $ip = $rowData->implement_point;
                                                        $i = $rowData->implement;
                                                        $tv = $rowData->target_value;
                                                        $il = $rowData->important_level;
                                                        $bm = $rowData->benchmark;
                                                        $unitName = $rowData->unit_name;
                                                        break;
                                                    }
                                                }
                                                $startColumnPosition++;
                                                $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                                    ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                                    ->setCellValue($columnI.$rowGI['index'], $i)->setCellValue($columnIP.$rowGI['index'], $ip);
                                            }
                                        }
                                        $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                                        $objPHPExcel->getActiveSheet()->getStyle($columnI.($startRow+2).':'.$columnIP.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                                    }
                                }
                            }

                            $rowYear = $startRow;
                            $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+($toMonth)*6 -1);
                            $objPHPExcel->getActiveSheet()->mergeCells($from.$rowYear.':'.$to.$rowYear);
                            $objPHPExcel->getActiveSheet()->setCellValue($from.$rowYear, 'Năm '.$toYear);
                            $rowYear++;
                            for($m = 1; $m <= $toMonth; $m++){
                                $startColumnPosition = $rowYear;
                                $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                                $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnPosition.':'.$toColumn.$startColumnPosition);
                                $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnPosition, 'Tháng '.$m);

                                $startColumnPosition++;
                                $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnI = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnPosition, 'Kế hoạch');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnPosition, 'Tỷ trọng');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnPosition, 'Điểm chuẩn ');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnI.$startColumnPosition, 'Thực hiện');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnPosition, 'Điểm thực hiện');
                                $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnPosition, 'Đơn vị tính');

                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnI)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                                foreach($arrayGoalIndex as $rowGI){
                                    if($rowGI['parent_id'] == 0){
                                        $sumIP = 0;
                                        $sumBM = 0;
                                        foreach($data as $DT){
                                            if($rowGI['id'] == $DT->parent_id && $arrayPositionParent[$p]['position_code'] == $DT->position_code &&
                                                $DT->month == $m && $DT->year == $toYear){
                                                $sumIP += round($DT->implement_point, 3);
                                                $sumBM += round($DT->benchmark, 3);
                                            }
                                        }
                                        $startColumnPosition++;
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                        $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnIP.$rowGI['index'])->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                    } else {
                                        $ip = 0;
                                        $i = 0;
                                        $tv = 0;
                                        $il = 0;
                                        $bm = 0;
                                        $unitName = '_';
                                        foreach($data as $rowData){
                                            if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                                ($arrayPositionParent[$p]['position_code'] == $rowData->position_code) &&
                                                ($rowData->month == $m) && ($rowData->year == $toYear)){
                                                $ip = $rowData->implement_point;
                                                $i = $rowData->implement;
                                                $tv = $rowData->target_value;
                                                $il = $rowData->important_level;
                                                $bm = $rowData->benchmark;
                                                $unitName = $rowData->unit_name;
                                                break;
                                            }
                                        }
                                        $startColumnPosition++;
                                        $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                            ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                            ->setCellValue($columnI.$rowGI['index'], $i)->setCellValue($columnIP.$rowGI['index'], $ip);
                                    }
                                }
                                $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                                $objPHPExcel->getActiveSheet()->getStyle($columnI.($startRow+2).':'.$columnIP.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                            }

                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.($startRow+2))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.($startRow+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.($startRow+2))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.$startColumnPosition)->applyFromArray($styleArray);
                        }
                    }
                }
            }
            excelUtils::setZoomSheet($objPHPExcel, 80);
            $objPHPExcel->setActiveSheetIndex(0);
            $fileName = 'BaoCaoChiTietTheoChucDanh';
            $this->outputFile($fileName, $objPHPExcel);
        }
    }

    /**
     * export data: detail area
     */
    public function reportDetailArea($check, $month, $year, $fromMonth, $toMonth, $fromYear, $toYear, $company, $area, $goal, $areaChoose)
    {
        //get data for summary area
        $area = explode(",", $area);
        $goal = explode(",", $goal);
        $select = "select sum(ta.implement_point) as implementPoint, sum(ta.implement) as implement,
 						  sum(ta.target_value) as targetValue,
						  area.area_name, area.area_code, ta.month, ta.year
					from target_area as ta
					join area on area.id = ta.area_id
					join goal on goal.id = ta.goal_id
					join company on company.id = ta.company_id
					WHERE ta.inactive = 0 and company.company_code = '" . $company . "' and goal.parent_id > 0";

        if ($check == 2) {
            // time to time
            if ($fromYear == $toYear) {
                $select .= ' and ta.year=' . $fromYear . ' and ta.month <=' . $toMonth .
                            ' and ta.month >=' . $fromMonth;
            } else {
                $select .= ' and ((ta.month >= ' . $fromMonth . ' and ta.year = ' . $fromYear . ') or
						    (ta.month <= ' . $toMonth . '  and ta.year = ' . $toYear . '))
						    and ta.year >= ' . $fromYear . ' and ta.year <= ' . $toYear;
            }
        } else {
            $select .= ' and ta.month = ' . $month . ' and ta.year = ' . $year;
        }

        //goal
        if ($goal != null) {
            $select .= " and ( ";
            for ($g = 0; $g < count($goal); $g++) {
                if ($g == (count($goal) - 1)) {
                    $select .= "goal.goal_code = '" . $goal[$g] . "' )";
                } else {
                    $select .= "goal.goal_code = '" . $goal[$g] . "' or ";
                }
            }
        } else {
            $select .= " and 0 ";
        }

        //area
        $all = 0;
        if ($area != null) {
            $selectChild = ' and ( ';
            for ($e = 0; $e < count($area); $e++) {
                if ($area[$e] == '1') {
                    $all = 1;
                }
                if ($e == (count($area) - 1)) {
                    $selectChild .= " area.area_code = '" . $area[$e] . "' ) ";
                } else {
                    $selectChild .= " area.area_code = '" . $area[$e] . "' or ";
                }
            }
            if ($all == 0) {
                $select .= $selectChild;
            }
        } else {
            $select .= ' and 0 ';
        }
        $select .= ' group by area.area_name, area.area_code, ta.month, ta.year
					 order by area_name';
        $data = DB::select(DB::raw($select));

        //get unique area
        $arrayArea = array();
        if (count($data) > 0) {
            foreach ($data as $rowData) {
                $countArea = 0;
                foreach ($arrayArea as $rowArea) {
                    if ($rowData->area_code == $rowArea['area_code']) {
                        $countArea = 1;
                    }
                }

                if ($countArea == 0) {
                    $index = count($arrayArea);
                    $arrayArea[$index]['area_code'] = $rowData->area_code;
                    $arrayArea[$index]['area_name'] = $rowData->area_name;
                }
            }
        }

        // sty array to apply for table data
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        // start export summary
        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/blank.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('BaoCaoChiTietTheoToQuanHuyen');
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BÁO CÁO CHI TIẾT THEO TỔ/QUẬN/HUYỆN');
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20)->setBold(true);

        $companyName = DB::table('company')->where('inactive', 0)->where('company_code', $company)->first();
        $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Phòng/Đài/MBF HCM: ')->setCellValue('D3', $companyName->company_name);

        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'Tháng: ');
        if($check == 1){
            $objPHPExcel->getActiveSheet()->setCellValue('D4', $month.'/'.$year);
        } else if(($fromMonth == $toMonth) && ($fromYear == $toYear)){
            $objPHPExcel->getActiveSheet()->setCellValue('D4', $fromMonth.'/'.$fromYear);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D4', $fromMonth.'/'.$fromYear.' - '.$toMonth.'/'.$toYear);
        }
        $objPHPExcel->getActiveSheet()->getStyle('C3:C4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('C3:D4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);

        $objPHPExcel->getActiveSheet()->mergeCells('B6:B7');
        $objPHPExcel->getActiveSheet()->mergeCells('C6:C7');

        $objPHPExcel->getActiveSheet()->setCellValue('B6', 'STT')->setCellValue('C6', 'Tổ/Quận/Huyện');
        $startRow = 7;
        if(($check == 1) || ($fromMonth == $toMonth && $fromYear == $toYear)){
            $objPHPExcel->getActiveSheet()->mergeCells('D6:D7');
            if($check == 1){
                $objPHPExcel->getActiveSheet()->setCellValue('D6', 'Tháng '.$month);
            } else if($fromMonth == $toMonth && $fromYear == $toYear){
                $objPHPExcel->getActiveSheet()->setCellValue('D6', 'Tháng '.$fromMonth);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

            $index = 0;
            foreach($data as $rowData){
                $startRow++;
                $index++;
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$startRow, $index)
                    ->setCellValue('C'.$startRow, $rowData->area_name)
                    ->setCellValue('D'.$startRow, $rowData->implementPoint);
            }
            $objPHPExcel->getActiveSheet()->getStyle('B6:D'.$startRow)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('B6:D7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B6:B'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B6:D7')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B6:D7')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('B6:D7')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            $objPHPExcel->getActiveSheet()->getStyle('D8:D'.$startRow)->getNumberFormat()->setFormatCode('#,##0.000');
        } else {
            $startColumn = 3;
            if($fromYear == $toYear){
                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+$toMonth-$fromMonth);
                $objPHPExcel->getActiveSheet()->mergeCells('D6:'.$toColumn.'6');
                $objPHPExcel->getActiveSheet()->setCellValue('D6', 'Năm '.$fromYear);
                for($m = $fromMonth; $m <= $toMonth; $m++){
                    $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                    $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'7', 'Tháng '.$m);
                }
                $index = 0;
                foreach($arrayArea as $rowPo){
                    $startRow++;
                    $index++;
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$startRow, $index)->setCellValue('C'.$startRow, $rowPo['area_name']);
                    $startColumn = 3;
                    for($m = $fromMonth; $m <= $toMonth; $m++){
                        $ip = 0;
                        foreach($data as $rowDT){
                            if($rowPo['area_code'] == $rowDT->area_code &&
                                $rowDT->month == $m && $rowDT->year == $fromYear){
                                $ip = $rowDT->implementPoint;
                            }
                        }
                        $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                        $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                    }
                }

                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.$startRow)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('B8:B'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                $objPHPExcel->getActiveSheet()->getStyle('D8:'.$toColumn.$startRow)->getNumberFormat()->setFormatCode('#,##0.000');
            } else {
                $objPHPExcel->getActiveSheet()->freezePane('D8');
                $fromFirstColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+12-$fromMonth);
                $objPHPExcel->getActiveSheet()->mergeCells($fromFirstColumn.'6:'.$toColumn.'6');
                $objPHPExcel->getActiveSheet()->setCellValue($fromFirstColumn.'6', 'Năm '.$fromYear);
                for($m = $fromMonth; $m <= 12; $m++){
                    $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                    $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'7', 'Tháng '.$m);
                }

                if($toYear-$fromYear > 1){
                    for($y = $fromYear+1; $y < $toYear; $y++){
                        $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+11);
                        $objPHPExcel->getActiveSheet()->mergeCells($from.'6:'.$toColumn.'6');
                        $objPHPExcel->getActiveSheet()->setCellValue($from.'6', 'Năm '.$y);
                        for($m = 1; $m <= 12; $m++){
                            $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                            $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'7', 'Tháng '.$m);
                        }
                    }
                }

                $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+$toMonth-1);
                $objPHPExcel->getActiveSheet()->mergeCells($from.'6:'.$toColumn.'6');
                $objPHPExcel->getActiveSheet()->setCellValue($from.'6', 'Năm '.$toYear);
                for($m = 1; $m <= $toMonth; $m++){
                    $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                    $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.'7', 'Tháng '.$m);
                }

                $index = 0;
                foreach($arrayArea as $rowPo){
                    $startRow++;
                    $index++;
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$startRow, $index)->setCellValue('C'.$startRow, $rowPo['area_name']);
                    $startColumn = 3;
                    for($m = $fromMonth; $m <= 12; $m++){
                        $ip = 0;
                        foreach($data as $rowDT){
                            if($rowPo['area_code'] == $rowDT->area_code &&
                                $rowDT->month == $m && $rowDT->year == $fromYear){
                                $ip = $rowDT->implementPoint;
                            }
                        }
                        $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                        $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                    }

                    if($toYear-$fromYear > 1){
                        for($y = $fromYear+1; $y < $toYear; $y++){
                            for($m = 1; $m <= 12; $m++){
                                $ip = 0;
                                foreach($data as $rowDT){
                                    if($rowPo['area_code'] == $rowDT->area_code &&
                                        $rowDT->month == $m && $rowDT->year == $y){
                                        $ip = $rowDT->implementPoint;
                                    }
                                }
                                $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                                $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                            }
                        }
                    }
                    for($m = 1; $m <= $toMonth; $m++){
                        $ip = 0;
                        foreach($data as $rowDT){
                            if($rowPo['area_code'] == $rowDT->area_code &&
                                $rowDT->month == $m && $rowDT->year == $toYear){
                                $ip = $rowDT->implementPoint;
                            }
                        }
                        $monthColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($monthColumn)->setWidth(13);
                        $objPHPExcel->getActiveSheet()->setCellValue($monthColumn.$startRow, $ip);
                    }
                }

                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.$startRow)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('B8:B'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('B6:'.$toColumn.'7')->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                $objPHPExcel->getActiveSheet()->getStyle('D8:'.$toColumn.$startRow)->getNumberFormat()->setFormatCode('#,##0.000');
            }
        }

        if($areaChoose != '1'){
            $areaChoose = explode(",", $areaChoose);
            $select = "SELECT   ta.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , unit.unit_name
                            , unit.id
                            , unit.unit_name
                            , area.area_code
                            , area.area_name
                        FROM  target_area AS ta
                        JOIN goal ON goal.id = ta.goal_id
                        JOIN unit ON unit.id = ta.unit_id
                        JOIN company ON company.id = ta.company_id
                        JOIN area ON area.id = ta.area_id
                        WHERE ta.inactive = 0 and company.company_code = '".$company."' and goal.parent_id > 0
                    ";

            if($check == 2){
                if($fromYear == $toYear){
                    $select .= ' and ta.year='.$fromYear.' and ta.month <='.$toMonth.
                        ' and ta.month >='.$fromMonth;
                } else {
                    $select .= ' and ((ta.month >= '.$fromMonth.' and ta.year = '.$fromYear.') or
						(ta.month <= '.$toMonth.'  and ta.year = '.$toYear.') or ( ta.year > '.$fromYear.' and ta.year < '.$toYear.')'
						;
                }
            } else {
                $select .= ' and ta.month = '.$month.' and ta.year = '.$year;
            }

            //goal
            if($goal != null){
                $select .= " and ( ";
                for($g=0; $g<count($goal); $g++){
                    if($g == (count($goal)-1)){
                        $select .= "goal.goal_code = '".$goal[$g]."' )";
                    } else {
                        $select .= "goal.goal_code = '".$goal[$g]."' or ";
                    }
                }
            } else {
                $select .= " and 0 ";
            }

            //area
            $all = 0;
            if($areaChoose != null){
                $selectChild = ' and ( ';
                for($e=0; $e<count($areaChoose); $e++){
                    if($areaChoose[$e] == '1'){
                        $all = 1;
                    }
                    if($e == (count($areaChoose)-1)){
                        $selectChild .= " area.area_code = '". $areaChoose[$e] ."' ) ";
                    } else {
                        $selectChild .= " area.area_code = '". $areaChoose[$e] ."' or ";
                    }
                }
                if($all == 0){
                    $select .= $selectChild;
                }
            } else {
                $select .= ' and 0 ';
            }

            $data = DB::select(DB::raw($select));

            $arrayAreaGoal = array();
            foreach($data as $rowData){
                $countArea = 0;
                foreach($arrayAreaGoal as $rowAG){
                    if($rowData->area_code == $rowAG['area_code']){
                        $countArea = 1;
                    }
                }

                if($countArea == 0){
                    $arrayParent= array();
                    foreach($data as $rowDT){
                        if($rowDT->area_code == $rowData->area_code){
                            $countGoal = 0;
                            foreach($arrayParent as $rowParent){
                                if($rowParent['id'] == $rowDT->parent_id){
                                    $countGoal = 1;
                                }
                            }
                            if($countGoal == 0){
                                $parent = DB::table('goal')->where('inactive', 0)->where('id', $rowDT->parent_id)->first();
                                $in = count($arrayParent);
                                $arrayParent[$in]['id'] = $parent->id;
                                $arrayParent[$in]['goal_name'] = $parent->goal_name;
                                $arrayParent[$in]['goal_code'] = $parent->goal_code;
                            }
                        }
                    }

                    $index = count($arrayAreaGoal);
                    $arrayAreaGoal[$index]['area_name'] = $rowData->area_name;
                    $arrayAreaGoal[$index]['area_code'] = $rowData->area_code;
                    $arrayAreaGoal[$index]['goal_parent'] = $arrayParent;
                }
            }

            for($p=0; $p<count($arrayAreaGoal); $p++) {
                $objWorkSheet = $objPHPExcel->createSheet($p+1);

                $objPHPExcel->setActiveSheetIndex($p+1);
                $objPHPExcel->getActiveSheet()->setTitle($arrayAreaGoal[$p]['area_name']);

                $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
                $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
                $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BÁO CÁO CHI TIẾT THEO TỔ/QUẬN/HUYỆN');
                $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20)->setBold(true);

                $companyName = DB::table('company')->where('inactive', 0)->where('company_code', $company)->first();
                $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Phòng/Đài/MBF HCM: ')->setCellValue('D3', $companyName->company_name)
                    ->setCellValue('C4', 'Tổ/Quận/Huyện: ')->setCellValue('D4', $arrayAreaGoal[$p]['area_name']);

                $objPHPExcel->getActiveSheet()->setCellValue('C5', 'Tháng: ');
                if($check == 1){
                    $objPHPExcel->getActiveSheet()->setCellValue('D5', $month.'/'.$year);
                } else if(($fromMonth == $toMonth) && ($fromYear == $toYear)){
                    $objPHPExcel->getActiveSheet()->setCellValue('D5', $fromMonth.'/'.$fromYear);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('D5', $fromMonth.'/'.$fromYear.' - '.$toMonth.'/'.$toYear);
                }

                $objPHPExcel->getActiveSheet()->getStyle('C3:C5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('C3:D5')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);

                $startRow = 7;
                $arrParent = $arrayAreaGoal[$p]['goal_parent'];

                // paint header
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$startRow.':A'.($startRow+2));
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$startRow.':B'.($startRow+2));
                $objPHPExcel->getActiveSheet()->mergeCells('C'.$startRow.':C'.($startRow+2));
                $objPHPExcel->getActiveSheet()->mergeCells('D'.$startRow.':D'.($startRow+2));

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, 'STT')
                    ->setCellValue('B'.$startRow, 'Mã')
                    ->setCellValue('C'.$startRow, 'Tên mục tiêu')
                    ->setCellValue('D'.$startRow, 'Loại mục tiêu');

                $rowStart = $startRow;
                $startRow += 2;
                $arrayGoalIndex = array();

                //paint goal - make array : goal-index
                foreach($arrParent as $rowP){
                    $isExists = 0;
                    foreach($data as $rDT){
                        if($rDT->area_code == $arrayAreaGoal[$p]['area_code'] && $rDT->parent_id == $rowP['id'] &&
                            ($rDT->important_level > 0 || $rDT->benchmark > 0 ||
                                $rDT->target_value > 0 || $rDT->implement > 0 ||
                                $rDT->implement_point > 0)){
                            $isExists = 1;
                            break;
                        }
                    }

                    if($isExists){
                        $startRow++;
                        $objPHPExcel->getActiveSheet()->mergeCells('A'.$startRow.':D'.$startRow);
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $rowP['goal_name']);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$startRow.':D'.$startRow)->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $i = count($arrayGoalIndex);
                        $arrayGoalIndex[$i]['goal_code'] = $rowP['goal_code'];
                        $arrayGoalIndex[$i]['index'] = $startRow;
                        $arrayGoalIndex[$i]['parent_id'] = 0;
                        $arrayGoalIndex[$i]['id'] = $rowP['id'];
                        $index = 0;
                        foreach($data as $rDaTa){
                            if($rDaTa->area_code == $arrayAreaGoal[$p]['area_code'] && $rDaTa->parent_id == $rowP['id'] &&
                                ($rDaTa->important_level > 0 || $rDaTa->benchmark > 0 ||
                                    $rDaTa->target_value > 0 || $rDaTa->implement > 0 ||
                                    $rDaTa->implement_point > 0)){
                                $count = 0;
                                foreach($arrayGoalIndex as $row){
                                    if($row['goal_code'] == $rDaTa->goal_code){
                                        $count = 1;
                                        break;
                                    }
                                }
                                if($count == 0){
                                    $index ++;
                                    $startRow++;
                                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $index)
                                        ->setCellValue('B'.$startRow, $rDaTa->goal_code)
                                        ->setCellValue('C'.$startRow, $rDaTa->goal_name)
                                        ->setCellValue('D'.$startRow, commonUtils::renderGoalTypeName($rDaTa->goal_type));
                                    $i = count($arrayGoalIndex);
                                    $arrayGoalIndex[$i]['goal_code'] = $rDaTa->goal_code;
                                    $arrayGoalIndex[$i]['index'] = $startRow;
                                    $arrayGoalIndex[$i]['parent_id'] = $rDaTa->parent_id;
                                    $arrayGoalIndex[$i]['id'] = -1;
                                    $objPHPExcel->getActiveSheet()->getStyle('A'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                }
                            }
                        }
                    }
                }
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.$startRow)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowStart.':D'.($rowStart+2))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

                $startRow = 7;
                if(($check == 1) || ($fromMonth == $toMonth && $fromYear == $toYear)){
                    $objPHPExcel->getActiveSheet()->mergeCells('E'.$startRow.':J'.$startRow);
                    $objPHPExcel->getActiveSheet()->mergeCells('E'.($startRow+1).':J'.($startRow+1));
                    if($check == 1){
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$startRow, 'Năm '.$year)->setCellValue('E'.($startRow+1), 'Tháng '.$month);
                    } else {
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$startRow, 'Năm '.$fromYear)->setCellValue('E'.($startRow+1), 'Tháng '.$fromMonth);
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.($startRow+2), 'Tỷ trọng')->setCellValue('F'.($startRow+2), 'Điểm chuẩn')
                        ->setCellValue('G'.($startRow+2), 'Kế hoạch')->setCellValue('H'.($startRow+2), 'Đơn vị tính')
                        ->setCellValue('I'.($startRow+2), 'Điểm thực hiện')->setCellValue('J'.($startRow+2), 'Tỉ lệ đạt');
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

                    $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':J'.($startRow+2))->applyFromArray($styleArray);

                    //paint data
                    $startRow += 3;
                    $end = 0;
                    foreach($arrayGoalIndex as $rowGI){
                        if($rowGI['parent_id'] == 0){
                            $sumIP = 0;
                            $sumBM = 0;
                            foreach($data as $DT){
                                if($rowGI['id'] == $DT->parent_id && $arrayAreaGoal[$p]['area_code'] == $DT->area_code){
                                    $sumIP += round($DT->implement_point, 3);
                                    $sumBM += round($DT->benchmark, 3);
                                }
                            }

                            $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowGI['index'], $sumIP);
                            $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowGI['index'], $sumBM);
                            $objPHPExcel->getActiveSheet()->getStyle('D'.$rowGI['index'].':J'.$rowGI['index'])->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        } else {
                            $ip = 0;
                            $rp = 0;
                            $tv = 0;
                            $il = 0;
                            $bm = 0;
                            $unitName = '_';
                            foreach($data as $DaTa){
                                if($rowGI['goal_code'] == $DaTa->goal_code && $arrayAreaGoal[$p]['area_code'] == $DaTa->area_code){
                                    $ip = $DaTa->implement_point;
                                    $rp = round(($DaTa->real_percent*100), 2);
                                    $tv = $DaTa->target_value;
                                    $il = $DaTa->important_level;
                                    $bm = $DaTa->benchmark;
                                    $unitName = $DaTa->unit_name;
                                    break;
                                }
                            }

                            $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowGI['index'], $il)->setCellValue('F'.$rowGI['index'], $bm)
                                ->setCellValue('G'.$rowGI['index'], $tv)->setCellValue('H'.$rowGI['index'], $unitName)
                                ->setCellValue('I'.$rowGI['index'], $ip)->setCellValue('J'.$rowGI['index'], number_format($rp, 2, '.', ',').'%');
                            $end = $rowGI['index'];
                            if($unitName == '_'){
                                $objPHPExcel->getActiveSheet()->getStyle('H'.$rowGI['index'])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            }
                        }

                    }
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$startRow.':J'.$end)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$startRow.':G'.$end)->getNumberFormat()->setFormatCode('#,##0.000');
                    $objPHPExcel->getActiveSheet()->getStyle('I'.$startRow.':I'.$end)->getNumberFormat()->setFormatCode('#,##0.000');
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$startRow.':J'.$end)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                } else {
                    $startColumn = 4;
                    $startColumnArea = 0;
                    $objPHPExcel->getActiveSheet()->freezePane('E10');
                    if($fromYear == $toYear){
                        $fromFirst = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+($toMonth-$fromMonth+1)*6 -1);
                        $objPHPExcel->getActiveSheet()->mergeCells($fromFirst.$startRow.':'.$to.$startRow);
                        $objPHPExcel->getActiveSheet()->setCellValue($fromFirst.$startRow, 'Năm '.$fromYear);
                        $startRow++;
                        for($m = $fromMonth; $m <= $toMonth; $m++){
                            $startColumnArea = $startRow;
                            $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                            $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnArea.':'.$toColumn.$startColumnArea);
                            $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnArea, 'Tháng '.$m);

                            $startColumnArea++;
                            $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnRP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                            $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnArea, 'Kế hoạch');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnArea, 'Tỷ trọng');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnArea, 'Điểm chuẩn ');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnRP.$startColumnArea, 'Tỉ lệ đạt');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnArea, 'Điểm thực hiện');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnArea, 'Đơn vị tính');

                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnRP)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                            //data for each month
                            foreach($arrayGoalIndex as $rowGI){
                                if($rowGI['parent_id'] == 0){
                                    $sumIP = 0;
                                    $sumBM = 0;
                                    foreach($data as $DT){
                                        if($rowGI['id'] == $DT->parent_id && $arrayAreaGoal[$p]['area_code'] == $DT->area_code &&
                                            $DT->month == $m && $DT->year == $fromYear){
                                            $sumIP += round($DT->implement_point, 3);
                                            $sumBM += round($DT->benchmark, 3);
                                        }
                                    }
                                    $startColumnArea++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                    $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnRP.$rowGI['index'])->getFill()
                                                ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                } else {
                                    $ip = 0;
                                    $rp = 0;
                                    $tv = 0;
                                    $il = 0;
                                    $bm = 0;
                                    $unitName = '_';
                                    foreach($data as $rowData){
                                        if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                            ($arrayAreaGoal[$p]['area_code'] == $rowData->area_code) &&
                                            ($rowData->month == $m) && ($rowData->year == $fromYear)){
                                            $ip = $rowData->implement_point;
                                            $rp = round(($rowData->real_percent*100), 2);
                                            $tv = $rowData->target_value;
                                            $il = $rowData->important_level;
                                            $bm = $rowData->benchmark;
                                            $unitName = $rowData->unit_name;
                                            break;
                                        }
                                    }
                                    $startColumnArea++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                        ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                        ->setCellValue($columnRP.$rowGI['index'], number_format($rp, 2, '.', ',').'%')->setCellValue($columnIP.$rowGI['index'], $ip);
                                }
                            }
                            $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnArea)->getNumberFormat()->setFormatCode('#,##0.000');
                            $objPHPExcel->getActiveSheet()->getStyle($columnIP.($startRow+2).':'.$columnIP.$startColumnArea)->getNumberFormat()->setFormatCode('#,##0.000');
                            $objPHPExcel->getActiveSheet()->getStyle($columnRP.($startRow+2).':'.$columnRP.$startColumnArea)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        }

                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.($startRow+1))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.($startRow+1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.($startRow+1))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow-1).':'.$to.$startColumnArea)->applyFromArray($styleArray);
                    } else {
                        $rowYear = $startRow;
                        $fromFirst = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+(13-$fromMonth)*6 -1);
                        $objPHPExcel->getActiveSheet()->mergeCells($fromFirst.$rowYear.':'.$to.$rowYear);
                        $objPHPExcel->getActiveSheet()->setCellValue($fromFirst.$rowYear, 'Năm '.$fromYear);
                        $rowYear++;
                        $startColumnPosition = 0;
                        for($m=$fromMonth; $m <= 12; $m++){
                            $startColumnPosition = $rowYear;
                            $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                            $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnPosition.':'.$toColumn.$startColumnPosition);
                            $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnPosition, 'Tháng '.$m);

                            $startColumnPosition++;
                            $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnRP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                            $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnPosition, 'Kế hoạch');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnPosition, 'Tỷ trọng');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnPosition, 'Điểm chuẩn ');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnRP.$startColumnPosition, 'Tỷ lệ đạt');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnPosition, 'Điểm thực hiện');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnPosition, 'Đơn vị tính');

                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnRP)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                            // data for each month
                            foreach($arrayGoalIndex as $rowGI){
                                if($rowGI['parent_id'] == 0){
                                    $sumIP = 0;
                                    $sumBM = 0;
                                    foreach($data as $DT){
                                        if($rowGI['id'] == $DT->parent_id && $arrayAreaGoal[$p]['area_code'] == $DT->area_code &&
                                            $DT->month == $m && $DT->year == $fromYear){
                                            $sumIP += round($DT->implement_point, 3);
                                            $sumBM += round($DT->benchmark, 3);
                                        }
                                    }
                                    $startColumnPosition++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                    $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnRP.$rowGI['index'])->getFill()
                                                ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                } else {
                                    $ip = 0;
                                    $rp = 0;
                                    $tv = 0;
                                    $il = 0;
                                    $bm = 0;
                                    $unitName = '_';
                                    foreach($data as $rowData){
                                        if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                            ($arrayAreaGoal[$p]['area_code'] == $rowData->area_code) &&
                                            ($rowData->month == $m) && ($rowData->year == $fromYear)){
                                            $ip = $rowData->implement_point;
                                            $rp = round(($rowData->real_percent*100), 2);
                                            $tv = $rowData->target_value;
                                            $il = $rowData->important_level;
                                            $bm = $rowData->benchmark;
                                            $unitName = $rowData->unit_name;
                                            break;
                                        }
                                    }
                                    $startColumnPosition++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                        ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                        ->setCellValue($columnRP.$rowGI['index'], number_format($rp, 2, '.', ',').'%')->setCellValue($columnIP.$rowGI['index'], $ip);
                                }
                            }
                            $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                            $objPHPExcel->getActiveSheet()->getStyle($columnIP.($startRow+2).':'.$columnIP.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                            $objPHPExcel->getActiveSheet()->getStyle($columnRP.($startRow+2).':'.$columnRP.$startColumnPosition)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        }

                        if($toYear-$fromYear > 1){
                            for($y = $fromYear+1; $y < $toYear; $y++){
                                $rowYear = $startRow;
                                $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+12*6 -1);
                                $objPHPExcel->getActiveSheet()->mergeCells($from.$rowYear.':'.$to.$rowYear);
                                $objPHPExcel->getActiveSheet()->setCellValue($from.$rowYear, 'Năm '.$y);
                                $rowYear++;
                                for($m = 1; $m <= 12 ; $m++){
                                    $startColumnPosition = $rowYear;
                                    $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                                    $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                                    $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnPosition.':'.$toColumn.$startColumnPosition);
                                    $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnPosition, 'Tháng '.$m);

                                    $startColumnPosition++;
                                    $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                    $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                    $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                    $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                    $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                                    $columnRP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                                    $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnPosition, 'Kế hoạch');
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnPosition, 'Tỷ trọng');
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnPosition, 'Điểm chuẩn ');
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnRP.$startColumnPosition, 'Tỷ lệ đạt');
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnPosition, 'Điểm thực hiện');
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnPosition, 'Đơn vị tính');

                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnRP)->setWidth(15);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                                    foreach($arrayGoalIndex as $rowGI){
                                        if($rowGI['parent_id'] == 0){
                                            $sumIP = 0;
                                            $sumBM = 0;
                                            foreach($data as $DT){
                                                if($rowGI['id'] == $DT->parent_id && $arrayAreaGoal[$p]['area_code'] == $DT->area_code &&
                                                    $DT->month == $m && $DT->year == $fromYear){
                                                    $sumIP += round($DT->implement_point, 3);
                                                    $sumBM += round($DT->benchmark, 3);
                                                }
                                            }
                                            $startColumnPosition++;
                                            $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                            $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnRP.$rowGI['index'])->getFill()
                                                        ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                        } else {
                                            $ip = 0;
                                            $rp = 0;
                                            $tv = 0;
                                            $il = 0;
                                            $bm = 0;
                                            $unitName = '_';
                                            foreach($data as $rowData){
                                                if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                                    ($arrayAreaGoal[$p]['area_code'] == $rowData->area_code) &&
                                                    ($rowData->month == $m) && ($rowData->year == $fromYear)){
                                                    $ip = $rowData->implement_point;
                                                    $rp = round(($rowData->real_percent*100), 2);
                                                    $tv = $rowData->target_value;
                                                    $il = $rowData->important_level;
                                                    $bm = $rowData->benchmark;
                                                    $unitName = $rowData->unit_name;
                                                    break;
                                                }
                                            }
                                            $startColumnPosition++;
                                            $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                                ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                                ->setCellValue($columnRP.$rowGI['index'], number_format($rp, 2, '.', ',').'%')->setCellValue($columnIP.$rowGI['index'], $ip);
                                        }
                                    }
                                    $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                                    $objPHPExcel->getActiveSheet()->getStyle($columnIP.($startRow+2).':'.$columnIP.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                                    $objPHPExcel->getActiveSheet()->getStyle($columnRP.($startRow+2).':'.$columnRP.$startColumnPosition)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                }
                            }
                        }

                        $rowYear = $startRow;
                        $from = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                        $to = PHPExcel_Cell::stringFromColumnIndex($startColumn+($toMonth)*6 -1);
                        $objPHPExcel->getActiveSheet()->mergeCells($from.$rowYear.':'.$to.$rowYear);
                        $objPHPExcel->getActiveSheet()->setCellValue($from.$rowYear, 'Năm '.$toYear);
                        $rowYear++;
                        for($m = 1; $m <= $toMonth; $m++){
                            $startColumnPosition = $rowYear;
                            $fromColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn);
                            $toColumn = PHPExcel_Cell::stringFromColumnIndex($startColumn+5);

                            $objPHPExcel->getActiveSheet()->mergeCells($fromColumn.$startColumnPosition.':'.$toColumn.$startColumnPosition);
                            $objPHPExcel->getActiveSheet()->setCellValue($fromColumn.$startColumnPosition, 'Tháng '.$m);

                            $startColumnPosition++;
                            $columnIL = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnBM = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnTV = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnUN = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnIP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);
                            $columnRP = PHPExcel_Cell::stringFromColumnIndex($startColumn++);

                            $objPHPExcel->getActiveSheet()->setCellValue($columnTV.$startColumnPosition, 'Kế hoạch');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$startColumnPosition, 'Tỷ trọng');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnBM.$startColumnPosition, 'Điểm chuẩn ');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnRP.$startColumnPosition, 'Tỷ lệ đạt');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$startColumnPosition, 'Điểm thực hiện');
                            $objPHPExcel->getActiveSheet()->setCellValue($columnUN.$startColumnPosition, 'Đơn vị tính');

                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnTV)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnIL)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnBM)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnRP)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnIP)->setWidth(15);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnUN)->setWidth(15);

                            foreach($arrayGoalIndex as $rowGI){
                                if($rowGI['parent_id'] == 0){
                                    $sumIP = 0;
                                    $sumBM = 0;
                                    foreach($data as $DT){
                                        if($rowGI['id'] == $DT->parent_id && $arrayAreaGoal[$p]['area_code'] == $DT->area_code &&
                                            $DT->month == $m && $DT->year == $fromYear){
                                            $sumIP += round($DT->implement_point, 3);
                                            $sumBM += round($DT->benchmark, 3);
                                        }
                                    }
                                    $startColumnPosition++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIP.$rowGI['index'], $sumIP)->setCellValue($columnBM.$rowGI['index'], $sumBM);
                                    $objPHPExcel->getActiveSheet()->getStyle($columnIL.$rowGI['index'].':'.$columnRP.$rowGI['index'])->getFill()
                                                ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                                } else {
                                    $ip = 0;
                                    $rp = 0;
                                    $tv = 0;
                                    $il = 0;
                                    $bm = 0;
                                    $unitName = '_';
                                    foreach($data as $rowData){
                                        if(($rowGI['goal_code'] == $rowData->goal_code) &&
                                            ($arrayAreaGoal[$p]['area_code'] == $rowData->area_code) &&
                                            ($rowData->month == $m) && ($rowData->year == $fromYear)){
                                            $ip = $rowData->implement_point;
                                            $rp = round(($rowData->real_percent*100), 2);
                                            $tv = $rowData->target_value;
                                            $il = $rowData->important_level;
                                            $bm = $rowData->benchmark;
                                            $unitName = $rowData->unit_name;
                                            break;
                                        }
                                    }
                                    $startColumnPosition++;
                                    $objPHPExcel->getActiveSheet()->setCellValue($columnIL.$rowGI['index'], $il)->setCellValue($columnBM.$rowGI['index'], $bm)
                                        ->setCellValue($columnTV.$rowGI['index'], $tv)->setCellValue($columnUN.$rowGI['index'], $unitName)
                                        ->setCellValue($columnRP.$rowGI['index'], number_format($rp, 2, '.', ',').'%')->setCellValue($columnIP.$rowGI['index'], $ip);
                                }
                            }
                            $objPHPExcel->getActiveSheet()->getStyle($columnIL.($startRow+2).':'.$columnTV.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                            $objPHPExcel->getActiveSheet()->getStyle($columnIP.($startRow+2).':'.$columnIP.$startColumnPosition)->getNumberFormat()->setFormatCode('#,##0.000');
                            $objPHPExcel->getActiveSheet()->getStyle($columnRP.($startRow+2).':'.$columnRP.$startColumnPosition)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        }

                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.($startRow+2))->getFill()->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.($startRow+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.($startRow+2))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle($fromFirst.($startRow).':'.$to.$startColumnPosition)->applyFromArray($styleArray);
                    }
                }
            }
        }
        excelUtils::setZoomSheet($objPHPExcel, 80);
        $objPHPExcel->setActiveSheetIndex(0);
        $fileName = 'BaoCaoChiTietTheoToQuanHuyen';
        $this->outputFile($fileName, $objPHPExcel);
    }

    public function exportAreaAffectToComp($company, $area, $goal, $applyDate){
        $area = explode(",", $area);
        $goal = explode(",", $goal);
        $year = (int)substr($applyDate, 0, 4);

        $monthSelectSQL = "select ta.month
                            FROM target_area as ta
                            JOIN company as c on c.id = ta.company_id
                            WHERE ta.year = ".$year." AND c.company_code = '".$company."'";
        $monthSelect = DB::select(DB::raw($monthSelectSQL));
        $month = (count($monthSelect) > 0 ) ? $monthSelect[0]->month : 0 ;

        $select = " select sum(ta.cal_benchmark) as bm, sum(ta.cal_implement_point) as ip, a.area_name, a.area_code, ta.area_id, c.company_name
                    from target_area as ta
                    join company as c on c.id = ta.company_id
                    join area as a on a.id = ta.area_id
                    join goal as g on g.id = ta.goal_id
                    where ta.month = ".$month." and ta.year = ".$year." and c.company_code = '".$company."'";

        $selectChild = ' and ( ';
        $all = 0;
        for($e=0; $e<count($area); $e++){
            if($area[$e] == '1'){
                $all = 1;
            }
            if($e == (count($area)-1)){
                $selectChild .= " a.area_code = '". $area[$e] ."' ) ";
            } else {
                $selectChild .= " a.area_code = '". $area[$e] ."' or ";
            }
        }
        if($all == 0){
            $select .= $selectChild;
        }

        $select .= ' and ( ';
        for($g=0; $g<count($goal); $g++){
            if($g == (count($goal)-1)){
                $select .= " g.goal_code = '". $goal[$g] ."' )";
            } else {
                $select .= " g.goal_code = '". $goal[$g] ."' or ";
            }
        }

        $select .= ' GROUP BY a.area_name, a.area_code, ta.area_id
                     ORDER BY a.area_name';
        $data = DB::select(DB::raw($select));

        // sty array to apply for table data
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        // start export summary

        $objPHPExcel = PHPExcel_IOFactory::load("public/exportTemplate/sampleExportAreaAffectToCompany.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);

        if(count($data) > 0){
            $objPHPExcel->getActiveSheet()->setCellValue('D4', $data[0]->company_name)->setCellValue('D5', commonUtils::formatDate($applyDate));

            $startRow = 8;
            $index = 1;
            foreach($data as $da){
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$startRow, $index++)
                    ->setCellValue('C'.$startRow, $da->area_name)
                    ->setCellValue('D'.$startRow, $da->bm)
                    ->setCellValue('E'.$startRow++, $da->ip);
            }

            $objPHPExcel->getActiveSheet()->getStyle('B8:E'.($startRow-1))->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('B8:B'.$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('D8:E'.($startRow-1))->getNumberFormat()->setFormatCode('#,##0.000');
        }
        excelUtils::setZoomSheet($objPHPExcel, 80);
        $fileName = 'AnhHuongCuaToQuanHuyen...';
        $this->outputFile($fileName, $objPHPExcel);
    }

    public function exportPlanByArea($companyId, $areaId, $year, $month, $goalId){

        /* *************************************************************************************************************
         * Check data input
         * ************************************************************************************************************/

        if($companyId == 0){
            Session::flash('message-errors', "Vui lòng chọn Phòng/Đài/MBF HCM trước khi xuất excel.");
            return redirect('reportPlanByArea/0/0/0/0/0');
        }

        if(
            $areaId == 0
        ){
            Session::flash('message-errors', "Vui lòng chọn Tổ/Quận/Huyện trước khi xuất excel.");
            return redirect('reportPlanByArea/0/0/0/0/0');
        }

        $company = DB::table('company')->where('inactive', 0)->where('id', $companyId)->get();
        $area    = DB::table('area')->where('inactive', 0)->where('company_id', $companyId)->where('id', $areaId)->get();

        if(count($company) == 0){
            Session::flash('message-errors', "Phòng/Đài/MBF HCM không tồn tại.");
            return redirect('reportPlanByArea/0/0/0/0/0');
        }

        if(count($area) == 0){
            Session::flash('message-errors', "Tổ/Quận/Huyện không tồn tại.");
            return redirect('reportPlanByArea/0/0/0/0/0');
        }

        $company = $company[0];
        $area    = $area[0];

        $companyName = $company->company_name;

            /* *************************************************************************************************************
             * get Data from database
             * ************************************************************************************************************/
        $sqlTE = "
            SELECT te.*, g.goal_code, g.goal_name, us.code, us.name, p.position_code, p.position_name, g.parent_id
            FROM target_employee te
            LEFT JOIN goal g ON g.id = te.goal_id
            LEFT JOIN company c ON c.id = te.company_id
            LEFT JOIN users us ON us.id = te.user_id
            LEFT JOIN area a ON a.id = te.area_id
            LEFT JOIN `position` p ON p.id = te.position_id
            WHERE te.inactive = 0
            AND te.company_id  = '".$companyId."'
            AND te.area_id  = '".$areaId."'
            AND te.year  = ".$year."
            AND te.month  = ".$month."
        ";

        if($goalId != "0"){
            $sqlTE .= " AND te.goal_id =  ".$goalId." ";
        }

        $objTEDB = DB::select(DB::raw($sqlTE));

        if(count($objTEDB) == 0){

            $str = "Vui lòng import dữ liệu cho "
                .excelUtils::TITLE_AREA." <b>".$area->area_name."</b> thuộc "
                .excelUtils::TITLE_COMPANY." <b>".$companyName."</b>"
                ." trước khi xuất báo cáo!"

            ;

            Session::flash('message-errors', $str);
            return redirect('reportPlanByArea/0/0/0/0/0');
        }

        $sqlGoal = "
            SELECT *
            FROM goal
            WHERE inactive = 0
        ";
        $objGoalDB = DB::select(DB::raw($sqlGoal));

        $sqlTP = "
            SELECT tp.*, p.position_code
            FROM target_position tp
            LEFT JOIN `position` p ON p.id = tp.position_id
            WHERE tp.inactive = 0
            AND tp.company_id  = '".$companyId."'
            AND tp.area_id  = '".$areaId."'
            AND tp.year = ".$year."
            AND tp.month = ".$month."
        ";


        if($goalId != 0){
            $sqlTP .= " AND tp.goal_id = ".$goalId." ";
        }

        $objTPDB = DB::select(DB::raw($sqlTP));

        $arrPosition = array(
            commonUtils::POSITION_CODE_GDV
            , commonUtils::POSITION_CODE_KAM_AM
            , commonUtils::POSITION_CODE_NVBH
        );

        /* *************************************************************************************************************
         * set default excel template
         * ************************************************************************************************************/
        $objPHPExcel = excelUtils::loadFile(excelUtils::PATH_BLANK);


        /* *************************************************************************************************************
         * put data to sheet
         * ************************************************************************************************************/
        $indexSheet     = 0;
        $arrSheetNull   = array();

        $arrSheetRemove = array();

        foreach($arrPosition as $position){

            if($indexSheet != 0){
                excelUtils::createSheet($objPHPExcel, $indexSheet);
            }
            excelUtils::activeSheet($objPHPExcel, $indexSheet);
            excelUtils::setDefaultFont($objPHPExcel, excelUtils::FONT_CALIBRI);
            excelUtils::setDefaultFontSize($objPHPExcel, 10);
            excelUtils::setZoomSheet($objPHPExcel, excelUtils::DEFAULT_ZOOM);
            excelUtils::setTitle($objPHPExcel, $this->convertNameSheet($position));

            $indexTitle = 1;

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$indexTitle , excelUtils::TITLE_BACKUP_GOAL_AREA)
                ->setCellValue('C'.($indexTitle + 2) , excelUtils::TITLE_COMPANY)
                ->setCellValue('C'.($indexTitle + 3) , excelUtils::TITLE_AREA)
                ->setCellValue('D'.($indexTitle + 2) , $company->company_code)
                ->setCellValue('D'.($indexTitle + 3) , $area->area_code)
                ->setCellValue('E'.($indexTitle + 2) , 'Năm')
                ->setCellValue('E'.($indexTitle + 3) , 'Tháng')
                ->setCellValue('F'.($indexTitle + 2) , $year)
                ->setCellValue('F'.($indexTitle + 3) , $month)
            ;

            /*--------------------------------------------------------------------------------------------------------*/
            $arrDataExcelLeft = array();
            $iDE = 0;

            $arrEmployee = array();
            $iE = 0;

            $arrGoalParent = array();
            $iP = 0;

            $arrGoalChild = array();
            $iC = 0;

            $arrDataLoop = array();
            $iDL = 0;


            $arrDataPosition = array();
            $iDP = 0;

            switch($indexSheet) {
                case 0:

                    foreach($objTPDB as $tp){
                        if(commonUtils::compareTwoString($tp->position_code, commonUtils::POSITION_CODE_GDV) == 1){
                            $arrDataPosition[$iDP]['position_id']   = $tp->position_id;
                            $arrDataPosition[$iDP]['position_code'] = $tp->position_code;
                            $arrDataPosition[$iDP]['goalId']        = $tp->goal_id;
                            $arrDataPosition[$iDP]['targetValue']   = $tp->target_value;
                            $iDP++;
                        }
                    }

                    foreach($objTEDB as $te){
                        if(
                            commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_CHT) == 1
                            || commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_GDV) == 1
                        ){

                            $arrDataLoop[$iDL]['employeeId']    = $te->user_id;
                            $arrDataLoop[$iDL]['goalId']        = $te->goal_id;
                            $arrDataLoop[$iDL]['targetValue']   = $te->target_value;
                            $iDL++;

                            if(count($arrEmployee) == 0){
                                $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                $arrEmployee[$iE]['employeeCode'] = $te->code;
                                $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                $arrEmployee[$iE]['employeeName'] = $te->name;
                                $iE++;
                            }else{
                                $exist = 0;
                                foreach($arrEmployee as $employee){
                                    if($employee['employeeId'] == $te->user_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                    $arrEmployee[$iE]['employeeCode'] = $te->code;
                                    $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                    $arrEmployee[$iE]['employeeName'] = $te->name;
                                    $iE++;
                                }
                            }

                            $pName = "";
                            foreach($objGoalDB as $goal){
                                if($goal->id == $te->parent_id){
                                    $pName = $goal->goal_name;
                                    break;
                                }
                            }

                            if(count($arrGoalParent) == 0){
                                $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                $arrGoalParent[$iP]['goalName'] = $pName;
                                $iP++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalParent as $goalParent){
                                    if($goalParent['goalId'] == $te->parent_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                    $arrGoalParent[$iP]['goalName'] = $pName;
                                    $iP++;
                                }
                            }

                            if(count($arrGoalChild) == 0){

                                $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                $iC++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalChild as $goalChild){
                                    if($goalChild['goalId'] == $te->goal_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                    $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                    $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                    $iC++;
                                }
                            }


                        }
                    }
                    break;
                case 1:

                    foreach($objTPDB as $tp){
                        if(commonUtils::compareTwoString($tp->position_code, commonUtils::POSITION_CODE_KAM_AM) == 1){
                            $arrDataPosition[$iDP]['position_id']   = $tp->position_id;
                            $arrDataPosition[$iDP]['position_code'] = $tp->position_code;
                            $arrDataPosition[$iDP]['goalId']        = $tp->goal_id;
                            $arrDataPosition[$iDP]['targetValue']   = $tp->target_value;
                            $iDP++;
                        }
                    }

                    foreach($objTEDB as $te){
                        if(
                            commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_CV_KHDN) == 1
                            || commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_KAM_AM) == 1
                        ){

                            $arrDataLoop[$iDL]['employeeId']    = $te->user_id;
                            $arrDataLoop[$iDL]['goalId']        = $te->goal_id;
                            $arrDataLoop[$iDL]['targetValue']   = $te->target_value;
                            $iDL++;

                            if(count($arrEmployee) == 0){
                                $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                $arrEmployee[$iE]['employeeCode'] = $te->code;
                                $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                $arrEmployee[$iE]['employeeName'] = $te->name;
                                $iE++;
                            }else{
                                $exist = 0;
                                foreach($arrEmployee as $employee){
                                    if($employee['employeeId'] == $te->user_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                    $arrEmployee[$iE]['employeeCode'] = $te->code;
                                    $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                    $arrEmployee[$iE]['employeeName'] = $te->name;
                                    $iE++;
                                }
                            }

                            $pName = "";
                            foreach($objGoalDB as $goal){
                                if($goal->id == $te->parent_id){
                                    $pName = $goal->goal_name;
                                    break;
                                }
                            }

                            if(count($arrGoalParent) == 0){
                                $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                $arrGoalParent[$iP]['goalName'] = $pName;
                                $iP++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalParent as $goalParent){
                                    if($goalParent['goalId'] == $te->parent_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                    $arrGoalParent[$iP]['goalName'] = $pName;
                                    $iP++;
                                }
                            }

                            if(count($arrGoalChild) == 0){
                                $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                $iC++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalChild as $goalChild){
                                    if($goalChild['goalId'] == $te->goal_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                    $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                    $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                    $iC++;
                                }
                            }



                        }
                    }
                    break;
                case 2:

                    foreach($objTPDB as $tp){
                        if(commonUtils::compareTwoString($tp->position_code, commonUtils::POSITION_CODE_NVBH) == 1){
                            $arrDataPosition[$iDP]['position_id']   = $tp->position_id;
                            $arrDataPosition[$iDP]['position_code'] = $tp->position_code;
                            $arrDataPosition[$iDP]['goalId']        = $tp->goal_id;
                            $arrDataPosition[$iDP]['targetValue']   = $tp->target_value;
                            $iDP++;
                        }
                    }

                    foreach($objTEDB as $te){
                        if(
                            commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_CV_KHCN) == 1
                            || commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_NVBH) == 1
                        ){

                            $arrDataLoop[$iDL]['employeeId']    = $te->user_id;
                            $arrDataLoop[$iDL]['goalId']        = $te->goal_id;
                            $arrDataLoop[$iDL]['targetValue']   = $te->target_value;
                            $iDL++;

                            if(count($arrEmployee) == 0){
                                $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                $arrEmployee[$iE]['employeeCode'] = $te->code;
                                $arrEmployee[$iE]['employeeName'] = $te->name;
                                $iE++;
                            }else{
                                $exist = 0;
                                foreach($arrEmployee as $employee){
                                    if($employee['employeeId'] == $te->user_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                    $arrEmployee[$iE]['employeeCode'] = $te->code;
                                    $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                    $arrEmployee[$iE]['employeeName'] = $te->name;
                                    $iE++;
                                }
                            }

                            $pName = "";
                            foreach($objGoalDB as $goal){
                                if($goal->id == $te->parent_id){
                                    $pName = $goal->goal_name;
                                    break;
                                }
                            }

                            if(count($arrGoalParent) == 0){
                                $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                $arrGoalParent[$iP]['goalName'] = $pName;
                                $iP++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalParent as $goalParent){
                                    if($goalParent['goalId'] == $te->parent_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                    $arrGoalParent[$iP]['goalName'] = $pName;
                                    $iP++;
                                }
                            }

                            if(count($arrGoalChild) == 0){
                                $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                $iC++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalChild as $goalChild){
                                    if($goalChild['goalId'] == $te->goal_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                    $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                    $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                    $iC++;
                                }
                            }
                        }
                    }
                    break;
            }
            /*--------------------------------------------------------------------------------------------------------*/


            $indexHeader = $indexTitle + 5;

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$indexHeader , 'STT')
                ->setCellValue('B'.$indexHeader , 'Mục tiêu')
            ;

            $beginData = $indexHeader + 1;

            $arrChildRow = array();
            $iCR = 0;
            $no = 1;

            $arrPRows = array();

            $lastRow = 1;

            foreach($arrGoalParent as $goalParent){

                $arrPRows[] = $beginData;

                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A'.$beginData ,$goalParent['goalName'] )
                ;

                $beginData++;
                $childRow = $beginData;

                foreach($arrGoalChild as $goalChild){
                    if($goalChild['parentId'] == $goalParent['goalId']){

                        $arrChildRow[$iCR]['indexRow']  = $childRow;
                        $arrChildRow[$iCR]['goalId']    = $goalChild['goalId'];
                        $iCR++;

                        $objPHPExcel->getActiveSheet()
                            ->setCellValue('A'.$childRow , $no++)
                            ->setCellValue('B'.$childRow , $goalChild['goalName'])
                        ;
                        $lastRow = $childRow;
                        $childRow++;
                    }
                }
                $beginData = $childRow;

            }

            if(count($arrChildRow) == 0){
                $arrSheetNull[] = $indexSheet;
            }
            $labelEmployee = "";
            $startLoopColumn = 'C';
            $indexLoop = excelUtils::getIndexColumn($startLoopColumn) - 1;

            $positionCompare = "";
            $hardPosition = "";
            switch($indexSheet) {
                case 0:
                    $positionCompare    = commonUtils::POSITION_CODE_CHT;
                    $hardPosition       = commonUtils::POSITION_CODE_GDV;
                    break;
                case 1:
                    $positionCompare    = commonUtils::POSITION_CODE_CV_KHDN;
                    $hardPosition       = commonUtils::POSITION_CODE_KAM_AM;
                    break;
                case 2:
                    $positionCompare    = commonUtils::POSITION_CODE_CV_KHCN;
                    $hardPosition       = commonUtils::POSITION_CODE_NVBH;
                    break;
            }

            foreach($arrEmployee as $employee){
                if(commonUtils::compareTwoString($employee['positionCode'], $positionCompare) == 1){
                    $labelEmployee = excelUtils::getLabelColumn($indexLoop);
                    excelUtils::setColumnWidth($objPHPExcel, $labelEmployee, 24);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($labelEmployee.$indexHeader , '['.$employee['employeeCode'].'] '.$employee['employeeName'])
                    ;

                    foreach($arrDataLoop as $dataLoop){

                        $dlGoalId       = $dataLoop['goalId'];
                        $dlTargetValue  = $dataLoop['targetValue'];

                        if($dataLoop['employeeId'] == $employee['employeeId']){
                            $indexLRow = -1;
                            foreach($arrChildRow as $childRow){
                                if($childRow['goalId'] == $dlGoalId){
                                    $indexLRow = $childRow['indexRow'];
                                    break;
                                }
                            }

                            if($indexLRow != -1){
                                $objPHPExcel->getActiveSheet()
                                    ->setCellValue($labelEmployee.$indexLRow , $dlTargetValue)
                                ;
                            }
                        }
                    }

                    $indexLoop++;
                }
            }

            if($labelEmployee == ""){
                $arrSheetRemove[] = $indexSheet;
                $labelEmployee = 'A';
                //Session::flash('message-errors', 'Dữ liệu không tồn tại!');
                //return redirect('reportPlanByArea/0/0/0/0/0');
            }

            $hardColumnPosition = excelUtils::getLabelColumn(excelUtils::getIndexColumn($labelEmployee));

            $objPHPExcel->getActiveSheet()
                ->setCellValue($hardColumnPosition.$indexHeader , $hardPosition)
            ;

            foreach($arrDataPosition as $dPosition){
                $dpGoalId       = $dPosition['goalId'];
                $dpTargetValue  = $dPosition['targetValue'];

                $indexLRow = -1;
                foreach($arrChildRow as $childRow){
                    if($childRow['goalId'] == $dpGoalId){
                        $indexLRow = $childRow['indexRow'];
                        break;
                    }
                }

                if($indexLRow != -1){
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($hardColumnPosition.$indexLRow , $dpTargetValue)
                    ;
                }

            }

            $endLabelColumn = 'A';
//            $lastRow = 1;

            $ndIndexLoop = excelUtils::getIndexColumn($hardColumnPosition);
            foreach($arrEmployee as $employee){
                if(commonUtils::compareTwoString($employee['positionCode'], $hardPosition) == 1){
                    $ndLoopColumn = excelUtils::getLabelColumn($ndIndexLoop);
                    excelUtils::setColumnWidth($objPHPExcel, $ndLoopColumn, 24);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($ndLoopColumn.$indexHeader , '['.$employee['employeeCode'].'] '.$employee['employeeName'])
                    ;

                    foreach($arrDataLoop as $dataLoop){

                        $dlGoalId       = $dataLoop['goalId'];
                        $dlTargetValue  = $dataLoop['targetValue'];

                        if($dataLoop['employeeId'] == $employee['employeeId']){
                            $indexLRow = -1;
                            foreach($arrChildRow as $childRow){
                                if($childRow['goalId'] == $dlGoalId){
                                    $indexLRow = $childRow['indexRow'];
                                    break;
                                }
                            }

                            if($indexLRow != -1){
                                $objPHPExcel->getActiveSheet()
                                    ->setCellValue($ndLoopColumn.$indexLRow , $dlTargetValue)
                                ;
                            }

//                            $lastRow = $indexLRow;
                        }
                    }

                    $ndIndexLoop++;

                    $endLabelColumn = $ndLoopColumn;
                }
            }

            $endLabelColumn = ($endLabelColumn != 'A') ? $endLabelColumn : 'F';
            /*echo $labelEmployee;
            echo $endLabelColumn;
            die;*/
            /*--------------------------------------------------------------------------------------------------------*/
            excelUtils::setVertical($objPHPExcel, 'A1:'.$endLabelColumn.$lastRow, \PHPExcel_Style_Alignment::VERTICAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel,'A'.$indexHeader.':'.$endLabelColumn.$indexHeader, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel,'A1:A'.$lastRow, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::mergeCells($objPHPExcel,'A1:D1');
            excelUtils::setBold($objPHPExcel, 'A1:'.$endLabelColumn.$indexHeader);
            excelUtils::setColumnWidth($objPHPExcel, 'A', 9);
            excelUtils::setColumnWidth($objPHPExcel, 'B', 30);
            excelUtils::setColumnWidth($objPHPExcel, 'D', 16);
            excelUtils::setRowHeight($objPHPExcel, $indexHeader, 26);
            excelUtils::setWrapText($objPHPExcel, 'A'.$indexHeader.':'.$endLabelColumn.$indexHeader);
            excelUtils::setRowHeight($objPHPExcel, 1, 30);
            excelUtils::setlandScape($objPHPExcel);
            excelUtils::setPageA4($objPHPExcel);
            excelUtils::setFontSize($objPHPExcel,'A1', 22);
            excelUtils::fillBackGroundColor($objPHPExcel, 'A'.$indexHeader.':'.$endLabelColumn.$indexHeader, excelUtils::COLOR_DARK);
            excelUtils::setBorderCell($objPHPExcel, 'A'.$indexHeader.':'.$endLabelColumn.$indexHeader, excelUtils::styleBorder());
            foreach($arrPRows as $pRow){
                excelUtils::mergeCells($objPHPExcel,'A'.$pRow.':B'.$pRow);
                excelUtils::setHorizontal($objPHPExcel,'A'.$pRow, \PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                excelUtils::setBold($objPHPExcel, 'A'.$pRow);

                excelUtils::fillBackGroundColor($objPHPExcel, 'A'.$pRow.':'.$endLabelColumn.$pRow, excelUtils::COLOR_GREEN);
                excelUtils::setBorderCell($objPHPExcel, 'A'.$pRow.':'.$endLabelColumn.$pRow, excelUtils::styleBorder());

            }

            foreach($arrChildRow as $childRow){
                excelUtils::setBorderCell($objPHPExcel, 'A'.$childRow['indexRow'].':'.$endLabelColumn.$childRow['indexRow'], excelUtils::styleBorderChild());
            }

            excelUtils::setBorderCell($objPHPExcel, 'A'.$lastRow.':'.$endLabelColumn.$lastRow, excelUtils::styleBorderLasted());
            excelUtils::setFreezePane($objPHPExcel, 'E7');
            excelUtils::formatCell($objPHPExcel,'C7:'.$endLabelColumn.$lastRow, 1, excelUtils::STYLE_NUMBER);
            excelUtils::setBold($objPHPExcel, 'A2');
            /*--------------------------------------------------------------------------------------------------------*/
            $indexSheet++;
        }

        if(count($arrSheetNull) != 0 && count($arrSheetNull) != 3){
            foreach($arrSheetNull as $sheetNull){
                excelUtils::removeSheet($objPHPExcel, $sheetNull);
            }
        }elseif(count($arrSheetNull) == 3){
            $objPHPExcel = excelUtils::loadFile(excelUtils::PATH_BLANK);
            excelUtils::setFontSize($objPHPExcel,'A1', 22);
            excelUtils::setFontColor($objPHPExcel,'A1', excelUtils::COLOR_RED);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1' , 'Vui lòng import dữ liệu '.$month.'/'.$year.' trước khi xuất báo cáo!')
            ;
        }

        /* *************************************************************************************************************
         * output file
         * ************************************************************************************************************/

        $objPHPExcel->setActiveSheetIndex(0);
        $fileName = 'MauInLuuKeHoach_'.$this->convertNameSheet($company->company_code).'_'.$this->convertNameSheet($area->area_code);
        $this->outputFile($fileName, $objPHPExcel);
    }

    public function exportImplementByArea($companyId, $areaId, $year, $month, $goalId){

        /* *************************************************************************************************************
         * Check data input
         * ************************************************************************************************************/

        if($companyId == 0){
            Session::flash('message-errors', "Vui lòng chọn Phòng/Đài/MBF HCM trước khi xuất excel.");
            return redirect('reportImplementByArea/0/0/0/0/0');
        }

        if(
            $areaId == 0
        ){
            Session::flash('message-errors', "Vui lòng chọn Tổ/Quận/Huyện trước khi xuất excel.");
            return redirect('reportImplementByArea/0/0/0/0/0');
        }

        $company = DB::table('company')->where('inactive', 0)->where('id', $companyId)->get();
        $area    = DB::table('area')->where('inactive', 0)->where('company_id', $companyId)->where('id', $areaId)->get();

        if(count($company) == 0){
            Session::flash('message-errors', "Phòng/Đài/MBF HCM không tồn tại.");
            return redirect('reportImplementByArea/0/0/0/0/0');
        }

        if(count($area) == 0){
            Session::flash('message-errors', "Tổ/Quận/Huyện không tồn tại.");
            return redirect('reportImplementByArea/0/0/0/0/0');
        }

        $company = $company[0];
        $area    = $area[0];

        $companyName = $company->company_name;

        /* *************************************************************************************************************
         * get Data from database
         * ************************************************************************************************************/
        $sqlTE = "
            SELECT te.*, g.goal_code, g.goal_name, us.code, us.name, p.position_code, p.position_name, g.parent_id
            FROM target_employee te
            LEFT JOIN goal g ON g.id = te.goal_id
            LEFT JOIN company c ON c.id = te.company_id
            LEFT JOIN users us ON us.id = te.user_id
            LEFT JOIN area a ON a.id = te.area_id
            LEFT JOIN `position` p ON p.id = te.position_id
            WHERE te.inactive = 0
            AND te.company_id  = '".$companyId."'
            AND te.area_id  = '".$areaId."'
            AND te.year  = ".$year."
            AND te.month  = ".$month."
        ";

        if($goalId != "0"){
            $sqlTE .= " AND te.goal_id =  ".$goalId." ";
        }


        $objTEDB = DB::select(DB::raw($sqlTE));

        if(count($objTEDB) == 0){

            $str = "Vui lòng import dữ liệu cho "
                .excelUtils::TITLE_AREA." <b>".$area->area_name."</b> thuộc "
                .excelUtils::TITLE_COMPANY." <b>".$companyName."</b>"
                ." trước khi xuất báo cáo!"

            ;

            Session::flash('message-errors', $str);
            return redirect('reportImplementByArea/0/0/0/0/0');
        }

        $sqlGoal = "
            SELECT *
            FROM goal
            WHERE inactive = 0
        ";
        $objGoalDB = DB::select(DB::raw($sqlGoal));

        $sqlTP = "
            SELECT tp.*, p.position_code
            FROM target_position tp
            LEFT JOIN `position` p ON p.id = tp.position_id
            WHERE tp.inactive = 0
            AND tp.company_id  = '".$objTEDB[0]->company_id."'
            AND tp.area_id  = '".$objTEDB[0]->area_id."'
            AND tp.year = ".$year."
            AND tp.month = ".$month."
        ";
        $objTPDB = DB::select(DB::raw($sqlTP));

        if($goalId != "0"){
            $sqlTP .= " AND tp.goal_id =  ".$goalId." ";
        }

        $arrPosition = array(
            commonUtils::POSITION_CODE_GDV
        , commonUtils::POSITION_CODE_KAM_AM
        , commonUtils::POSITION_CODE_NVBH
        );

        /* *************************************************************************************************************
         * set default excel template
         * ************************************************************************************************************/
        $objPHPExcel = excelUtils::loadFile(excelUtils::PATH_BLANK);


        /* *************************************************************************************************************
         * put data to sheet
         * ************************************************************************************************************/
        $indexSheet = 0;
        $arrSheetNull = array();
        foreach($arrPosition as $position){

            if($indexSheet != 0){
                excelUtils::createSheet($objPHPExcel, $indexSheet);
            }
            excelUtils::activeSheet($objPHPExcel, $indexSheet);
            excelUtils::setDefaultFont($objPHPExcel, excelUtils::FONT_CALIBRI);
            excelUtils::setDefaultFontSize($objPHPExcel, 10);
            excelUtils::setZoomSheet($objPHPExcel, excelUtils::DEFAULT_ZOOM);
            excelUtils::setTitle($objPHPExcel, $this->convertNameSheet($position));

            $indexTitle = 1;

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$indexTitle , excelUtils::TITLE_BACKUP_ILPO_AREA)
                ->setCellValue('C'.($indexTitle + 2) , excelUtils::TITLE_COMPANY)
                ->setCellValue('C'.($indexTitle + 3) , excelUtils::TITLE_AREA)
                ->setCellValue('D'.($indexTitle + 2) , $company->company_code)
                ->setCellValue('D'.($indexTitle + 3) , $area->area_name)
                ->setCellValue('E'.($indexTitle + 2) , 'Năm')
                ->setCellValue('E'.($indexTitle + 3) , 'Tháng')
                ->setCellValue('F'.($indexTitle + 2) , $year)
                ->setCellValue('F'.($indexTitle + 3) , $month)
            ;

            /*--------------------------------------------------------------------------------------------------------*/
            $arrDataExcelLeft = array();
            $iDE = 0;

            $arrEmployee = array();
            $iE = 0;

            $arrGoalParent = array();
            $iP = 0;

            $arrGoalChild = array();
            $iC = 0;

            $arrDataLoop = array();
            $iDL = 0;


            $arrDataPosition = array();
            $iDP = 0;

            switch($indexSheet) {
                case 0:

                    foreach($objTPDB as $tp){
                        if(commonUtils::compareTwoString($tp->position_code, commonUtils::POSITION_CODE_GDV) == 1){
                            $arrDataPosition[$iDP]['position_id']   = $tp->position_id;
                            $arrDataPosition[$iDP]['position_code'] = $tp->position_code;
                            $arrDataPosition[$iDP]['goalId']        = $tp->goal_id;
                            $arrDataPosition[$iDP]['implement']     = $tp->implement;
                            $iDP++;
                        }
                    }

                    foreach($objTEDB as $te){
                        if(
                            commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_CHT) == 1
                            || commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_GDV) == 1
                        ){

                            $arrDataLoop[$iDL]['employeeId']        = $te->user_id;
                            $arrDataLoop[$iDL]['goalId']            = $te->goal_id;
                            $arrDataLoop[$iDL]['targetValue']       = $te->target_value;
                            $arrDataLoop[$iDL]['implement']         = $te->implement;
                            $arrDataLoop[$iDL]['implementPoint']    = $te->implement_point;
                            $arrDataLoop[$iDL]['percentComplete']   = ($te->benchmark != 0) ? $te->implement_point / $te->benchmark : 0;
                            $iDL++;

                            if(count($arrEmployee) == 0){
                                $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                $arrEmployee[$iE]['employeeCode'] = $te->code;
                                $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                $arrEmployee[$iE]['employeeName'] = $te->name;
                                $iE++;
                            }else{
                                $exist = 0;
                                foreach($arrEmployee as $employee){
                                    if($employee['employeeId'] == $te->user_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                    $arrEmployee[$iE]['employeeCode'] = $te->code;
                                    $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                    $arrEmployee[$iE]['employeeName'] = $te->name;
                                    $iE++;
                                }
                            }

                            $pName = "";
                            foreach($objGoalDB as $goal){
                                if($goal->id == $te->parent_id){
                                    $pName = $goal->goal_name;
                                    break;
                                }
                            }

                            if(count($arrGoalParent) == 0){
                                $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                $arrGoalParent[$iP]['goalName'] = $pName;
                                $iP++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalParent as $goalParent){
                                    if($goalParent['goalId'] == $te->parent_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                    $arrGoalParent[$iP]['goalName'] = $pName;
                                    $iP++;
                                }
                            }

                            if(count($arrGoalChild) == 0){

                                $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                $iC++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalChild as $goalChild){
                                    if($goalChild['goalId'] == $te->goal_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                    $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                    $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                    $iC++;
                                }
                            }


                        }
                    }
                    break;
                case 1:

                    foreach($objTPDB as $tp){
                        if(commonUtils::compareTwoString($tp->position_code, commonUtils::POSITION_CODE_KAM_AM) == 1){
                            $arrDataPosition[$iDP]['position_id']   = $tp->position_id;
                            $arrDataPosition[$iDP]['position_code'] = $tp->position_code;
                            $arrDataPosition[$iDP]['goalId']        = $tp->goal_id;
                            $arrDataPosition[$iDP]['implement']     = $tp->implement;
                            $iDP++;
                        }
                    }

                    foreach($objTEDB as $te){
                        if(
                            commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_CV_KHDN) == 1
                            || commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_KAM_AM) == 1
                        ){

                            $arrDataLoop[$iDL]['employeeId']        = $te->user_id;
                            $arrDataLoop[$iDL]['goalId']            = $te->goal_id;
                            $arrDataLoop[$iDL]['targetValue']       = $te->target_value;
                            $arrDataLoop[$iDL]['implement']         = $te->implement;
                            $arrDataLoop[$iDL]['implementPoint']    = $te->implement_point;
                            $arrDataLoop[$iDL]['percentComplete']   = ($te->benchmark != 0) ? $te->implement_point / $te->benchmark : 0;
                            $iDL++;

                            if(count($arrEmployee) == 0){
                                $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                $arrEmployee[$iE]['employeeCode'] = $te->code;
                                $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                $arrEmployee[$iE]['employeeName'] = $te->name;
                                $iE++;
                            }else{
                                $exist = 0;
                                foreach($arrEmployee as $employee){
                                    if($employee['employeeId'] == $te->user_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                    $arrEmployee[$iE]['employeeCode'] = $te->code;
                                    $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                    $arrEmployee[$iE]['employeeName'] = $te->name;
                                    $iE++;
                                }
                            }

                            $pName = "";
                            foreach($objGoalDB as $goal){
                                if($goal->id == $te->parent_id){
                                    $pName = $goal->goal_name;
                                    break;
                                }
                            }

                            if(count($arrGoalParent) == 0){
                                $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                $arrGoalParent[$iP]['goalName'] = $pName;
                                $iP++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalParent as $goalParent){
                                    if($goalParent['goalId'] == $te->parent_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                    $arrGoalParent[$iP]['goalName'] = $pName;
                                    $iP++;
                                }
                            }

                            if(count($arrGoalChild) == 0){
                                $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                $iC++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalChild as $goalChild){
                                    if($goalChild['goalId'] == $te->goal_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                    $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                    $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                    $iC++;
                                }
                            }



                        }
                    }
                    break;
                case 2:

                    foreach($objTPDB as $tp){
                        if(commonUtils::compareTwoString($tp->position_code, commonUtils::POSITION_CODE_NVBH) == 1){
                            $arrDataPosition[$iDP]['position_id']   = $tp->position_id;
                            $arrDataPosition[$iDP]['position_code'] = $tp->position_code;
                            $arrDataPosition[$iDP]['goalId']        = $tp->goal_id;
                            $arrDataPosition[$iDP]['implement']     = $tp->implement;
                            $iDP++;
                        }
                    }

                    foreach($objTEDB as $te){
                        if(
                            commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_CV_KHCN) == 1
                            || commonUtils::compareTwoString($te->position_code, commonUtils::POSITION_CODE_NVBH) == 1
                        ){

                            $arrDataLoop[$iDL]['employeeId']        = $te->user_id;
                            $arrDataLoop[$iDL]['goalId']            = $te->goal_id;
                            $arrDataLoop[$iDL]['targetValue']       = $te->target_value;
                            $arrDataLoop[$iDL]['implement']         = $te->implement;
                            $arrDataLoop[$iDL]['implementPoint']    = $te->implement_point;
                            $arrDataLoop[$iDL]['percentComplete']   = ($te->benchmark != 0) ? $te->implement_point / $te->benchmark : 0;
                            $iDL++;

                            if(count($arrEmployee) == 0){
                                $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                $arrEmployee[$iE]['employeeCode'] = $te->code;
                                $arrEmployee[$iE]['employeeName'] = $te->name;
                                $iE++;
                            }else{
                                $exist = 0;
                                foreach($arrEmployee as $employee){
                                    if($employee['employeeId'] == $te->user_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrEmployee[$iE]['employeeId']   = $te->user_id;
                                    $arrEmployee[$iE]['employeeCode'] = $te->code;
                                    $arrEmployee[$iE]['positionCode'] = $te->position_code;
                                    $arrEmployee[$iE]['employeeName'] = $te->name;
                                    $iE++;
                                }
                            }

                            $pName = "";
                            foreach($objGoalDB as $goal){
                                if($goal->id == $te->parent_id){
                                    $pName = $goal->goal_name;
                                    break;
                                }
                            }

                            if(count($arrGoalParent) == 0){
                                $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                $arrGoalParent[$iP]['goalName'] = $pName;
                                $iP++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalParent as $goalParent){
                                    if($goalParent['goalId'] == $te->parent_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalParent[$iP]['goalId']   = $te->parent_id;
                                    $arrGoalParent[$iP]['goalName'] = $pName;
                                    $iP++;
                                }
                            }

                            if(count($arrGoalChild) == 0){
                                $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                $iC++;
                            }else{
                                $exist = 0;
                                foreach($arrGoalChild as $goalChild){
                                    if($goalChild['goalId'] == $te->goal_id){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrGoalChild[$iC]['goalId']   = $te->goal_id;
                                    $arrGoalChild[$iC]['parentId'] = $te->parent_id;
                                    $arrGoalChild[$iC]['goalName'] = $te->goal_name;
                                    $iC++;
                                }
                            }
                        }
                    }
                    break;
            }
            /*--------------------------------------------------------------------------------------------------------*/


            $indexHeader = $indexTitle + 5;

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$indexHeader , 'STT')
                ->setCellValue('B'.$indexHeader , 'Mục tiêu')
            ;

            $beginData = $indexHeader + 2;

            $arrChildRow = array();
            $iCR = 0;
            $no = 1;

            $arrPRows = array();

            foreach($arrGoalParent as $goalParent){

                $arrPRows[] = $beginData;

                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A'.$beginData ,$goalParent['goalName'] )
                ;

                $beginData++;
                $childRow = $beginData;

                foreach($arrGoalChild as $goalChild){
                    if($goalChild['parentId'] == $goalParent['goalId']){

                        $arrChildRow[$iCR]['indexRow']  = $childRow;
                        $arrChildRow[$iCR]['goalId']    = $goalChild['goalId'];
                        $iCR++;

                        $objPHPExcel->getActiveSheet()
                            ->setCellValue('A'.$childRow , $no++)
                            ->setCellValue('B'.$childRow , $goalChild['goalName'])
                        ;
                        $childRow++;
                    }
                }
                $beginData = $childRow;

            }

            if(count($arrChildRow) == 0){
                $arrSheetNull[] = $indexSheet;
            }

            $startLoopColumn = 'C';
            $indexLoop = excelUtils::getIndexColumn($startLoopColumn) - 1;

            $positionCompare = "";
            $hardPosition = "";
            switch($indexSheet) {
                case 0:
                    $positionCompare = commonUtils::POSITION_CODE_CHT;
                    $hardPosition = commonUtils::POSITION_CODE_GDV;
                    break;
                case 1:
                    $positionCompare = commonUtils::POSITION_CODE_CV_KHDN;
                    $hardPosition = commonUtils::POSITION_CODE_KAM_AM;
                    break;
                case 2:
                    $positionCompare = commonUtils::POSITION_CODE_CV_KHCN;
                    $hardPosition = commonUtils::POSITION_CODE_NVBH;
                    break;
            }

            /*$lblTargetValue     = 'A';
            $lblImplement       = 'A';
            $lblPercentComplete = 'A';
            $lblImplementPoint  = 'A';*/

            $lblTargetValue     = excelUtils::getLabelColumn($indexLoop);
            $lblImplement       = excelUtils::getLabelColumn($indexLoop + 1);
            $lblPercentComplete = excelUtils::getLabelColumn($indexLoop + 2);
            $lblImplementPoint  = excelUtils::getLabelColumn($indexLoop + 3);

            $arrColumnPercent = array();

            foreach($arrEmployee as $employee){
                if(commonUtils::compareTwoString($employee['positionCode'], $positionCompare) == 1){
                    $lblTargetValue     = excelUtils::getLabelColumn($indexLoop);
                    $lblImplement       = excelUtils::getLabelColumn($indexLoop + 1);
                    $lblPercentComplete = excelUtils::getLabelColumn($indexLoop + 2);
                    $lblImplementPoint  = excelUtils::getLabelColumn($indexLoop + 3);

                    $arrColumnPercent[] = $lblPercentComplete;

                    excelUtils::mergeCells($objPHPExcel,$lblTargetValue.$indexHeader.':'.$lblImplementPoint.$indexHeader);

                    $hashColumn = range($lblTargetValue, $lblImplementPoint);
                    $count = 0;
                    foreach ($hashColumn as $key => $value) {
                        $dimension = 0;
                        switch ($value) {
                            case $lblTargetValue:
                            case $lblImplement:
                                $dimension = 24;
                                break;
                            case $lblPercentComplete:
                            case $lblImplementPoint:
                                $dimension = 15;
                                break;
                        }
                        $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth($dimension);
                        $count++;
                    }


                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($lblTargetValue.$indexHeader , '['.$employee['employeeCode'].'] '.$employee['employeeName'])
                        ->setCellValue($lblTargetValue.($indexHeader + 1) , 'Kế hoạch')
                        ->setCellValue($lblImplement.($indexHeader + 1) , 'Thực hiện')
                        ->setCellValue($lblPercentComplete.($indexHeader + 1) , 'Tỷ lệ đạt')
                        ->setCellValue($lblImplementPoint.($indexHeader + 1) , 'Điểm Thực hiện')
                    ;

                    foreach($arrDataLoop as $dataLoop){

                        $dlGoalId           = $dataLoop['goalId'];
                        $dlTargetValue      = $dataLoop['targetValue'];
                        $dlImplement        = $dataLoop['implement'];
                        $dlImplementPoint   = $dataLoop['implementPoint'];
                        $dlPercentComplete  = $dataLoop['percentComplete'];

                        if($dataLoop['employeeId'] == $employee['employeeId']){
                            $indexLRow = -1;
                            foreach($arrChildRow as $childRow){
                                if($childRow['goalId'] == $dlGoalId){
                                    $indexLRow = $childRow['indexRow'];
                                    break;
                                }
                            }

                            if($indexLRow != -1){
                                $objPHPExcel->getActiveSheet()
                                    ->setCellValue($lblTargetValue.$indexLRow , $dlTargetValue)
                                    ->setCellValue($lblImplement.$indexLRow , $dlImplement)
                                    ->setCellValue($lblPercentComplete.$indexLRow , $dlPercentComplete)
                                    ->setCellValue($lblImplementPoint.$indexLRow , $dlImplementPoint)
                                ;
                            }
                        }
                    }

                    $indexLoop = $indexLoop + 4;
                }
            }

            $hardColumnPosition = excelUtils::getLabelColumn(excelUtils::getIndexColumn($lblImplementPoint));
            excelUtils::setColumnWidth($objPHPExcel, $hardColumnPosition, 24);
            excelUtils::mergeCells($objPHPExcel,$hardColumnPosition.$indexHeader.':'.$hardColumnPosition.($indexHeader + 1));
            $objPHPExcel->getActiveSheet()
                ->setCellValue($hardColumnPosition.$indexHeader , $hardPosition)
            ;

            foreach($arrDataPosition as $dPosition){
                $dpGoalId       = $dPosition['goalId'];
                $dpImplement    = $dPosition['implement'];

                $indexLRow = -1;
                foreach($arrChildRow as $childRow){
                    if($childRow['goalId'] == $dpGoalId){
                        $indexLRow = $childRow['indexRow'];
                        break;
                    }
                }

                if($indexLRow != -1){
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($hardColumnPosition.$indexLRow , $dpImplement)
                    ;
                }

            }

            $endLabelColumn = 'A';
            $lastRow = 1;

            $ndIndexLoop = excelUtils::getIndexColumn($hardColumnPosition);

            foreach($arrEmployee as $employee){
                if(commonUtils::compareTwoString($employee['positionCode'], $hardPosition) == 1){
                    $ndLoopColumn = excelUtils::getLabelColumn($ndIndexLoop);
                    excelUtils::setColumnWidth($objPHPExcel, $ndLoopColumn, 24);
                    excelUtils::mergeCells($objPHPExcel,$ndLoopColumn.$indexHeader.':'.$ndLoopColumn.($indexHeader + 1));
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($ndLoopColumn.$indexHeader , '['.$employee['employeeCode'].'] '.$employee['employeeName'])
                    ;

                    foreach($arrDataLoop as $dataLoop){

                        $dlGoalId       = $dataLoop['goalId'];
                        $dlImplement    = $dataLoop['implement'];

                        if($dataLoop['employeeId'] == $employee['employeeId']){
                            $indexLRow = -1;
                            foreach($arrChildRow as $childRow){
                                if($childRow['goalId'] == $dlGoalId){
                                    $indexLRow = $childRow['indexRow'];
                                    break;
                                }
                            }

                            if($indexLRow != -1){
                                $objPHPExcel->getActiveSheet()
                                    ->setCellValue($ndLoopColumn.$indexLRow , $dlImplement)
                                ;
                            }

                            $lastRow = $indexLRow;
                        }
                    }

                    $ndIndexLoop++;

                    $endLabelColumn = $ndLoopColumn;
                }
            }
            /*--------------------------------------------------------------------------------------------------------*/
            excelUtils::setVertical($objPHPExcel, 'A1:'.$endLabelColumn.$lastRow, \PHPExcel_Style_Alignment::VERTICAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel,'A'.$indexHeader.':'.$endLabelColumn.($indexHeader + 1), \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel,'A1:A'.$lastRow, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::mergeCells($objPHPExcel,'A1:D1');
            excelUtils::setBold($objPHPExcel, 'A1:'.$endLabelColumn.($indexHeader+ 1));
            excelUtils::setColumnWidth($objPHPExcel, 'A', 9);
            excelUtils::setColumnWidth($objPHPExcel, 'B', 30);
            excelUtils::setColumnWidth($objPHPExcel, 'D', 16);
            excelUtils::setRowHeight($objPHPExcel, $indexHeader, 18);
            excelUtils::setRowHeight($objPHPExcel, 1, 30);
            excelUtils::setlandScape($objPHPExcel);
            excelUtils::setPageA4($objPHPExcel);
            excelUtils::setFontSize($objPHPExcel,'A1', 22);
            excelUtils::fillBackGroundColor($objPHPExcel, 'A'.$indexHeader.':'.$endLabelColumn.($indexHeader + 1), excelUtils::COLOR_DARK);
            excelUtils::setBorderCell($objPHPExcel, 'A'.$indexHeader.':'.$endLabelColumn.($indexHeader + 1), excelUtils::styleBorder());
            foreach($arrPRows as $pRow){
                excelUtils::mergeCells($objPHPExcel,'A'.$pRow.':B'.$pRow);
                excelUtils::setHorizontal($objPHPExcel,'A'.$pRow, \PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                excelUtils::setBold($objPHPExcel, 'A'.$pRow);

                excelUtils::fillBackGroundColor($objPHPExcel, 'A'.$pRow.':'.$endLabelColumn.$pRow, excelUtils::COLOR_GREEN);
                excelUtils::setBorderCell($objPHPExcel, 'A'.$pRow.':'.$endLabelColumn.$pRow, excelUtils::styleBorder());

            }

            foreach($arrChildRow as $childRow){
                excelUtils::setBorderCell($objPHPExcel, 'A'.$childRow['indexRow'].':'.$endLabelColumn.$childRow['indexRow'], excelUtils::styleBorderChild());
            }

            excelUtils::setBorderCell($objPHPExcel, 'A'.$lastRow.':'.$endLabelColumn.$lastRow, excelUtils::styleBorderLasted());
            excelUtils::setFreezePane($objPHPExcel, 'H8');
            excelUtils::formatCell($objPHPExcel,'C8:'.$endLabelColumn.$lastRow, 1, excelUtils::STYLE_NUMBER);

            excelUtils::mergeCells($objPHPExcel,'A'.$indexHeader.':A'.($indexHeader + 1));
            excelUtils::mergeCells($objPHPExcel,'B'.$indexHeader.':B'.($indexHeader + 1));
           // commonUtils::pr($arrColumnPercent); die;
            if(count($arrColumnPercent) != 0){
                foreach($arrColumnPercent as $columnPercent){

                    foreach($arrChildRow as $childRow){
                        excelUtils::formatCell($objPHPExcel,$columnPercent.$childRow['indexRow'], 1, \PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
                    }

                }
            }
            excelUtils::setBold($objPHPExcel, 'A2');
            /*--------------------------------------------------------------------------------------------------------*/
            $indexSheet++;
        }
        $numSNull = count($arrSheetNull);
        if(count($arrSheetNull) == 3){
            excelUtils::createSheet($objPHPExcel, 3);
            excelUtils::activeSheet($objPHPExcel, 3);

        }
        if(count($arrSheetNull) != 0 && count($arrSheetNull) != 3){

            $arrCase201 = array(0,1);
            $arrCase202 = array(0,2);

            switch($numSNull) {
                case 1:
                    excelUtils::removeSheet($objPHPExcel, $arrSheetNull[0]);
                    break;
                case 2:

                    $result = array_diff($arrSheetNull,$arrCase201);

                    if(count($result) != 0){
                        $result = array_diff($arrSheetNull,$arrCase202);

                        if(count($result) != 0){
                            excelUtils::removeSheet($objPHPExcel, 2);
                            excelUtils::removeSheet($objPHPExcel, 1);
                        }else{
                            excelUtils::removeSheet($objPHPExcel, 2);
                            excelUtils::removeSheet($objPHPExcel, 0);
                        }
                    }else{
                        excelUtils::removeSheet($objPHPExcel, 1);
                        excelUtils::removeSheet($objPHPExcel, 0);
                    }

                    break;
                case 3:
                    excelUtils::removeSheet($objPHPExcel, 2);
                    excelUtils::removeSheet($objPHPExcel, 1);
                    excelUtils::removeSheet($objPHPExcel, 0);
                    break;

            }



        }elseif(count($arrSheetNull) == 3){
            $objPHPExcel = excelUtils::loadFile(excelUtils::PATH_BLANK);
            excelUtils::setFontSize($objPHPExcel,'A1', 22);
            excelUtils::setFontColor($objPHPExcel,'A1', excelUtils::COLOR_RED);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1' , 'Vui lòng import dữ liệu '.$month.'/'.$year.' trước khi xuất báo cáo!')
            ;
        }

        /* *************************************************************************************************************
         * output file
         * ************************************************************************************************************/

        $objPHPExcel->setActiveSheetIndex(0);
        $fileName = 'MauInLuuThucHien_'.$this->convertNameSheet($company->company_code).'_'.$this->convertNameSheet($area->area_code);
        $this->outputFile($fileName, $objPHPExcel);
    }
}

?>