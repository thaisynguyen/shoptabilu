<?php namespace App\Http\Controllers;

date_default_timezone_set('Asia/Ho_Chi_Minh');

use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;
use Session;
use DB;
use Utils\commonUtils;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Illuminate\Support\Facades\Config;

class ImportExcelController extends AppController
{
    public function __construct()
    {
        $this->middleware('auth');

        //get config
        $this->config = Config::getFacadeRoot();
    }

    public function getImport()
    {
        return view('major.manageImport');
    }

    public function postImport(Request $request)
    {
        #Get variable from form Import
        $post         = $request->all();
        $typeImport   = (int)$post['selTypeImport'];
        $startRow     = (isset($post['startRow']) && $post['startRow'] != '' && $post['startRow'] != null) ? $post['startRow'] : 0;
        $chooseImport = (int)$post['typeImport'];

        $listSheetIndex = isset($post['arrSheetIndex']) ? $post['arrSheetIndex'] : '';

        $date           = date('Y-m-d');
        $year           = substr($date, 0, 4);
        $month          = substr($date, 5, 2);
        $monthYear      = $year . '/' . $month;

        $time           = date('H:m:s');
        $time           = (string)$time;
        $time           = str_replace(':', '-', $time);
        $curIdUser      = Session::get('sid');
        $sDataUser      = Session::get('sDataUser');
        $path           = public_path() . '/upload';
        $localFileName  = $request->file('uploadFile')->getClientOriginalName();

        $rename         =
            $sDataUser->area_code
            . '_' . $curIdUser
            . '_' . $date
            . '_' . $time
            . '_' . $localFileName;

        $path           = $path . '/ ' . $year;

        $path = str_replace(' ', '', $path);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $path = $path . '/ ' . $month;
        $path = str_replace(' ', '', $path);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $path = $path . '/ ';
        $path = str_replace(' ', '', $path);
        #move file from client to sever
        $request->file('uploadFile')->move($path, $rename);

        #get list code from users
        #Load by sheet index

        Session::put('pathFileImport', $path . $rename);

        #Kiểm tra startRow với template Excel tương ứng
        Session::put('curType', -1);

        $sAccessLevel   = Session::get('saccess_level');
        $sCompanyId     = Session::get('scompany_id');
        $sAreaId        = Session::get('sarea_id');
        $sPositionId    = Session::get('sposition_id');
        $sId            = Session::get('sid');

        $arrDataSession = array(
            'sAccessLevel'  => $sAccessLevel
            , 'sCompanyId'  => $sCompanyId
            , 'sAreaId'     => $sAreaId
            , 'sPositionId' => $sPositionId
            , 'sId'         => $sId
        );

        switch ($typeImport) {
            case 1:/*Import Mục tiêu*/
                if ($startRow < 5) {
                    $startRow = 5;
                }
                $this->importGoal($path . $rename, $startRow, $arrDataSession);
                break;
            case 2:/*Import Tỷ trọng Phòng/Đài/MBF HCm*/
                if ($startRow < 8) {
                    $startRow = 8;
                }
                $this->beforeImportPriorityCompany($path . $rename, $startRow, $typeImport, $rename, $monthYear, $arrDataSession);
                break;
            case 3:/*Import tỷ trọng chức danh*/
                if ($startRow < 9) {
                    $startRow = 9;
                }
                if($chooseImport != 1){
                    $listSheetIndex = "1";
                }
                $this->beforeImportMultiPriorityPosition($path . $rename, $startRow, $typeImport, $rename, $listSheetIndex, $arrDataSession);
                break;
            case 4:/*Import Nhân viên*/
                if ($startRow < 4) {
                    $startRow = 4;
                }
                $this->importEmployee($path . $rename, $startRow, $arrDataSession);
                break;
            case 5:/*Import Kế hoạch Tổ/Quận/Huyện*/
                if ($startRow < 9) {
                    $startRow = 9;
                }
                $this->beforeImportMultiGoalArea($path . $rename, $startRow, $typeImport, $rename, $arrDataSession);
                break;
            case 6:/*Import Kế hoạch nhân viên*/
                if ($startRow < 12) {
                    $startRow = 12;
                }
                if($chooseImport != 1){
                    $listSheetIndex = "1";
                }
                $this->beforeImportMultiGoalEmployee($path . $rename, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession);
                break;
            case 7:/*Import Tỷ trọng công ty*/
                if ($startRow < 8) {
                    $startRow = 8;
                }
                $this->beforeImportPriorityCorporation($path . $rename, $startRow, $rename, $arrDataSession);
                break;
            case 8:/*Import Thuc hiện nhân viên*/
                if ($startRow < 12) {
                    $startRow = 12;
                }
                if($chooseImport != 1){
                    $listSheetIndex = "1";
                }
                $this->beforeImportMultiPerformForEmployee($path . $rename, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession);
                break;
            case 9:/*Import Kế hoạch chức danh*/
                if ($startRow < 9) {
                    $startRow = 9;
                }
                if($chooseImport != 1){
                    $listSheetIndex = "1";
                }
                $this->beforeImportMultiGoalPosition($path . $rename, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession);
                break;
            case 10:/*Import Tỷ trọng Tổ/Quận/Huyện*/
                if ($startRow < 8) {
                    $startRow = 8;
                }
                if($chooseImport != 1){
                    $listSheetIndex = "1";
                }
                $this->beforeImportMultiPriorityArea($path . $rename, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession);
                break;
            case 11:/*Import Thực hiện chức danh CTV*/
                if ($startRow < 10) {
                    $startRow = 10;
                }

                $this->importPerformPositionCTV($path . $rename, $startRow, $typeImport, $rename, $arrDataSession);
                break;
        }
        return redirect('importGoal');
    }
    /*----------------------------------------------------------------------------------------------------------------*/
    public function importEmployee($path, $startRow, $arrDataSession)
    {
        DB::beginTransaction();
        try{

            $sId = Session::get('sid');
            if($sId !== 0){
                Session::flash('message-errors', "Bạn không có quyền sử dụng chức năng này. Vui lòng liên hệ Mrs.Ngân Phòng Công nghệ Thông Tin để biết thêm chi tiết!");
                $this->clearSession();
                Session::flash('type', 4);
                return redirect('importGoal');
            }


            $sAccessLevel   = $arrDataSession['sAccessLevel'];
            $sCompanyId     = $arrDataSession['sCompanyId'];
            $sAreaId        = $arrDataSession['sAreaId'];
            $sPositionId    = $arrDataSession['sPositionId'];

            if($sAccessLevel >1){
                Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
                $this->clearSession();
                Session::flash('type', 4);
                return redirect('importGoal');
            }

            #Load by Sheet Name
            $inputFileName = $path;
            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            //  Get worksheet dimensions
            $sheet          = $objPHPExcel->getSheet(0);
            $highestRow     = $sheet->getHighestRow();
            $title          = trim($sheet->rangeToArray('A' . 2)[0][0]);

            $like = commonUtils::compareTwoString($title, commonUtils::TITLE_IMPORT_EMPLOYEE);

            if($like == 0) {
                Session::flash('message-errors', $this->config->get('constant.ERR_IMPORT_FILE_INVALID'));
                $this->clearSession();
                Session::flash('type', 4);
                return redirect('importGoal');
            }

            $createdUser = Session::get('sid');

            /************************************************************************************************************/
            /* Get All data from database
            /************************************************************************************************************/

            #object company
            $objCompanyDB = DB::table('company')->where('inactive', 0)->get();

            #object area
            $objAreaDB = DB::table('area')->where('inactive', 0)->get();

            #object position
            $objPositionDB = DB::table('position')->where('inactive', 0)->get();

            #object position
            $objGroupDB = DB::table('group')->where('inactive', 0)->get();

            #object group
            $objAccessLevelDB = DB::table('access_level')->where('inactive', 0)->get();

            #Get object access level
            $sqlEmployee = "
                SELECT us.*, co.company_code, co.company_name, po.position_code, po.position_name, ar.area_code, ar.area_name
                FROM users us
                LEFT JOIN company co ON co.id = us.company_id
                LEFT JOIN position po ON po.id = us.position_id
                LEFT JOIN area ar ON ar.id = us.area_id
                WHERE us.inactive = 0

            ";

            $objEmployeeDB = DB::select(DB::raw($sqlEmployee));

            /************************************************************************************************************/

            $arrDataInsert  = array();

            $numUpdate      = 0;
            $listCodeUpdate = "";

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':L' . $row, NULL, TRUE, FALSE);

                $no              = trim($rowData[0][0]);
                $code            = (trim($rowData[0][1]) != "") ? trim($rowData[0][1]) : commonUtils::generalCode();
                $name            = (trim($rowData[0][2]) != "") ? trim($rowData[0][2]) : $code;
                $companyCode     = trim($rowData[0][3]);
                $areaCode        = trim($rowData[0][4]);
                $groupCode       = trim($rowData[0][5]);
                $positionCode    = trim($rowData[0][6]);
                $accessLevelCode = trim($rowData[0][7]);
                $username        = (trim($rowData[0][8]) != "") ? trim($rowData[0][8]) : $code;
                $password        = (trim($rowData[0][9]) != "") ? md5(trim($rowData[0][9])) : md5(commonUtils::DEFAULT_PASSWORD);
                $isView          = commonUtils::checkValueNumeric(trim($rowData[0][10]));
                $LDAP            = commonUtils::checkValueNumeric(trim($rowData[0][11]));

                if($username != ""){
                    $companyId      = 0;
                    $areaId         = 0;
                    $positionId     = 0;
                    $groupId        = 0;
                    $accessLevelId  = 0;

                    if($companyCode != ""){
                        foreach($objCompanyDB as $company){
                            if(commonUtils::compareTwoString($company->company_code, $companyCode) == 1){
                                $companyId = $company->id;
                                break;
                            }
                        }
                    }

                    if($companyId != 0){
                        if($areaCode != ""){
                            foreach($objAreaDB as $area){
                                if(commonUtils::compareTwoString($area->area_code, $areaCode) == 1){
                                    $areaId = $area->id;
                                    break;
                                }
                            }
                        }

                    }

                    if($positionCode != ""){
                        foreach($objPositionDB as $position){
                            if(commonUtils::compareTwoString($position->position_code, $positionCode) == 1){
                                $positionId = $position->id;
                                break;
                            }
                        }
                    }

                    if($groupCode != ""){
                        foreach($objGroupDB as $group){
                            if(commonUtils::compareTwoString($group->group_code, $groupCode) == 1){
                                $groupId = $group->id;
                                break;
                            }
                        }
                    }

                    if($accessLevelCode != ""){
                        foreach($objAccessLevelDB as $accessLevel){
                            if(commonUtils::compareTwoString($accessLevel->access_level_code, $accessLevelCode) == 1){
                                $accessLevelId = $accessLevel->id;
                                break;
                            }
                        }
                    }

                    $existEmployee = -1;
                    if($code != ""){
                        foreach($objEmployeeDB as $employee){
                            if(commonUtils::compareTwoString($employee->code, $code) == 1
                                || commonUtils::compareTwoString($employee->username, $username) == 1){
                                $existEmployee = $employee->id;
                                break;
                            }
                        }
                    }

                    switch($sAccessLevel) {
                        case 1:/** Mức truy cập Công ty được quyền import tất cả nhân viên */
                            if($existEmployee == -1){

                                $iEmployee = array(
                                    'code'           => $code
                                    , 'name'         => $name
                                    , 'company_id'   => $companyId
                                    , 'position_id'  => $positionId
                                    , 'access_level' => $accessLevelId
                                    , 'area_id'      => $areaId
                                    , 'group_id'     => $groupId
                                    , 'username'     => $username
                                    , 'password'     => $password
                                    , 'is_view'      => $isView
                                    , 'ldap'         => $LDAP
                                    , 'created_user' => $createdUser
                                );
                                $arrDataInsert[] = $iEmployee;

                            } else {
                                $uEmployee = array(
                                    'name'           => $name
                                    , 'company_id'   => $companyId
                                    , 'position_id'  => $positionId
                                    , 'access_level' => $accessLevelId
                                    , 'area_id'      => $areaId
                                    , 'group_id'     => $groupId
                                    , 'password'     => $password
                                    , 'is_view'      => $isView
                                    , 'ldap'         => $LDAP
                                    , 'created_user' => $createdUser
                                );

                                DB::table('users')->where('id', $existEmployee)->update($uEmployee);
                                $numUpdate++;

                                if($listCodeUpdate == ""){
                                    $listCodeUpdate = $code;
                                }else{
                                    $listCodeUpdate .= ', '.$code;
                                }
                            }

                            break;
                        case 2:/** Mức truy cập Phòng/Đài/MBF HCM được quyền import tất cả nhân viên trực thuộc Phòng/Đài/MBF HCM */

                            if($companyId == $sCompanyId){
                                if($existEmployee == -1){
                                    $iEmployee = array(
                                        'code'           => $code
                                        , 'name'         => $name
                                        , 'company_id'   => $companyId
                                        , 'position_id'  => $positionId
                                        , 'access_level' => $accessLevelId
                                        , 'area_id'      => $areaId
                                        , 'group_id'     => $groupId
                                        , 'username'     => $username
                                        , 'password'     => $password
                                        , 'is_view'      => $isView
                                        , 'ldap'         => $LDAP
                                        , 'created_user' => $createdUser
                                    );
                                    $arrDataInsert[] = $iEmployee;
                                } else {
                                    $uEmployee = array(
                                        'name'           => $name
                                        , 'company_id'   => $companyId
                                        , 'position_id'  => $positionId
                                        , 'access_level' => $accessLevelId
                                        , 'area_id'      => $areaId
                                        , 'group_id'     => $groupId
                                        , 'password'     => $password
                                        , 'is_view'      => $isView
                                        , 'ldap'         => $LDAP
                                    );

                                    DB::table('users')->where('id', $existEmployee)->update($uEmployee);
                                    $numUpdate++;
                                    if($listCodeUpdate == ""){
                                        $listCodeUpdate = $code;
                                    }else{
                                        $listCodeUpdate .= ', '.$code;
                                    }
                                }
                            }

                            break;
                        case 3:/** Mức truy cập Tổ/Quận/Huyện được quyền import tất cả nhân viên trực thuộc Tổ/Quận/Huyện */

                            if($areaId == $sAreaId){
                                if($existEmployee == -1){
                                    $iEmployee = array(
                                        'code'           => $code
                                        , 'name'         => $name
                                        , 'company_id'   => $companyId
                                        , 'position_id'  => $positionId
                                        , 'access_level' => $accessLevelId
                                        , 'area_id'      => $areaId
                                        , 'group_id'     => $groupId
                                        , 'username'     => $username
                                        , 'password'     => $password
                                        , 'is_view'      => $isView
                                        , 'ldap'         => $LDAP
                                        , 'created_user' => $createdUser
                                    );
                                    $arrDataInsert[] = $iEmployee;
                                } else {
                                    $uEmployee = array(
                                        'name'           => $name
                                        , 'company_id'   => $companyId
                                        , 'position_id'  => $positionId
                                        , 'access_level' => $accessLevelId
                                        , 'area_id'      => $areaId
                                        , 'group_id'     => $groupId
                                        , 'password'     => $password
                                        , 'is_view'      => $isView
                                        , 'ldap'         => $LDAP
                                    );

                                    DB::table('users')->where('id', $existEmployee)->update($uEmployee);
                                    $numUpdate++;

                                    if($listCodeUpdate == ""){
                                        $listCodeUpdate = $code;
                                    }else{
                                        $listCodeUpdate .= ', '.$code;
                                    }
                                }
                            }

                            break;
                    }
                }
            }

            $strSuccess = "";
            if(count($arrDataInsert) > 0){
                DB::table('users')->insert($arrDataInsert);
                $strSuccess = "Đã import thành công ".count($arrDataInsert)." dòng.";
            }

            if($numUpdate > 0){
                if($strSuccess == ""){
                    $strSuccess = "Đã cập nhật thành công ".$numUpdate." dòng."
                                ."<br/> &nbsp;&nbsp;&nbsp; - Danh sách nhân viên đã cập nhật: ".$listCodeUpdate.".";
                }
            }
            if($strSuccess != ""){
                Session::flash('message-success', '<b>Import người dùng</b><hr/>'.$strSuccess);
            }

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        $this->clearSession();
        Session::flash('type', 4);
        return redirect('importGoal');
    }

    public function importGoal($path, $startRow, $arrDataSession)
    {
        DB::beginTransaction();
        try{
            $sAccessLevel   = $arrDataSession['sAccessLevel'];
            $sCompanyId     = $arrDataSession['sCompanyId'];
            $sAreaId        = $arrDataSession['sAreaId'];
            $sPositionId    = $arrDataSession['sPositionId'];

            if($sAccessLevel > 2){
                Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
                $this->clearSession();
                Session::flash('type', 1);
                return redirect('importGoal');
            }

            $inputFileName = $path;
            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            //  Get worksheet dimensions
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            //echo $highestRow;die;
            #Get title sheet for compare with type import
            $title = $sheet->rangeToArray('D' . 2);
            $like = $title[0][0];
            if (strtolower(trim($like)) != strtolower(commonUtils::TITLE_IMPORT_GOAL)) {
                Session::flash('message-errors', $this->config->get('constant.ERR_IMPORT_FILE_INVALID'));
                $this->clearSession();
                Session::flash('type', 1);
                return redirect('importGoal');
            }

            $successRow = 0;
            $updateRow = "";
            $parentId = 0;
            $strNullData = "";
            $strUpdateGoal = "";
            $strErrorUnit = "";
            $strErrorGoal = "";
            $strError = "";

            $objGoalDB = DB::table('goal')->where('inactive', 0)->get();
            $objUnitDB = DB::table('unit')->where('inactive', 0)->get();
            $arrGoalCode = array();
            $arrGoal = array();
            $iGoal = 0;

            for ($row = $startRow; $row <= $highestRow; $row++) {
                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('B' . $row . ':' . ($highestColumn) . $row, NULL, TRUE, FALSE);
                //commonUtils::pr($rowData);die;
                $no = trim($rowData[0][0]);
                $code = trim($rowData[0][1]);
                $name = trim($rowData[0][2]);
                $isParent = trim($rowData[0][3]);
                $goalType = trim($rowData[0][4]);
                $formula = trim($rowData[0][5]);
                $unitCode = trim($rowData[0][6]);
                $isParent = (isset($isParent) && ($isParent != '' || $isParent != null)) ? $isParent : 0;
                //echo $isParent; die;
                if($code != '' || $code != null){
                    $existGoalCode = 0;
                    foreach($objGoalDB as $goal){
                        if($goal->goal_code == $code){
                            $existGoalCode = 1;
                            break;
                        }
                    }

                    if(!in_array($code, $arrGoalCode) && $existGoalCode == 0) {
                        if($isParent == 0){
                            $data = array('goal_code' => $code,
                                'goal_name' => $name,
                                'formula' => 0,
                                'unit_id' => 0,
                                'parent_id' => 0,
                                'goal_type' => 0);
                            $id = DB::table('goal')->insertGetId($data);
                            $parentId = $id;
                            $arrGoalCode[] = $code;
                            $successRow++;

                            $arrGoal[$iGoal]['goalId'] = $id;
                            $arrGoal[$iGoal]['goalCode'] = $code;
                            $iGoal++;

                        } else {
                            if (($name != '' || $name != null) && ($goalType != '' || $goalType != null)
                                && ($formula != '' || $formula != null) && ($unitCode != '' || $unitCode != null)){
                                $validUnitCode = 0;
                                $unitId = '';
                                foreach($objUnitDB as $unit){
                                    if($unit->unit_code == $unitCode){
                                        $validUnitCode = 1;
                                        $unitId = $unit->id;
                                        break;
                                    }
                                }

                                $existGoalCode = 0;
                                foreach($objGoalDB as $goal){
                                    if($goal->goal_code == $code){
                                        $existGoalCode = 1;
                                        break;
                                    }
                                }
                                if(
                                    $existGoalCode == 0 #Goal Code Không tồn tại trong database
                                    && $validUnitCode == 1 #Unit code hợp lệ
                                ){

                                }
                            }
                            $data = array('goal_code' => $code,
                                'goal_name' => $name,
                                'formula' => $formula,
                                'unit_id' => $unitId,
                                'parent_id' => $parentId,
                                'goal_type' => $goalType);
                            $id = DB::table('goal')->insertGetId($data);
                            $successRow++;
                            $arrGoalCode[] = $code;

                            $arrGoal[$iGoal]['goalId'] = $id;
                            $arrGoal[$iGoal]['goalCode'] = $code;
                            $iGoal++;
                        }

                    } else {
                        $uGoalId = "";
                        $uGoalCode = "";
                        $uUnitId = "";

                        if($existGoalCode == 1){
                            foreach($objGoalDB as $uGoal){
                                if($uGoal->goal_code == $code){
                                    $uGoalId = $uGoal->id;
                                    $uGoalCode = $code;
                                    $uGoalName = $name;
                                    break;
                                }
                            }
                        }

                        if($uGoalId == ""){
                            if(in_array($code, $arrGoalCode)){
                                foreach($arrGoal as $auGoal){
                                    if($auGoal['goalCode'] == $code){
                                        $uGoalId = $auGoal['goalId'];
                                        $uGoalCode = $code;
                                        $uGoalName = $name;
                                        break;
                                    }
                                }
                            }
                        }

                        if($isParent == 0){
                            $sqlUpdateGoal = "
                                    UPDATE goal
                                    SET goal_name = '".$uGoalName."', unit_id = 0, goal_type = 0, formula = 0
                                    WHERE id = '".$uGoalId."'
                                ";
                            DB::update(DB::raw($sqlUpdateGoal));

                            if($strUpdateGoal == ""){
                                $strUpdateGoal = $uGoalCode;
                            } else {
                                $strUpdateGoal .= ", ".$uGoalCode;
                            }
                        } else {
                            if(($unitCode != '' || $unitCode != null)
                                && ($goalType != '' || $goalType != null)
                                && ($formula != '' || $formula != null)){
                                $uGoalType = $goalType;
                                $uFormula = $formula;
                                foreach($objUnitDB as $unit){
                                    if($unit->unit_code == $unitCode){
                                        $uUnitId = $unit->id;
                                        break;
                                    }
                                }
                                if($uUnitId != ""){
                                    $sqlUpdateGoal = "
                                    UPDATE goal
                                    SET goal_name = '".$uGoalName."', unit_id = '".$uUnitId."', goal_type = '".$uGoalType."', formula = '".$uFormula."'
                                    WHERE id = '".$uGoalId."'
                                ";
                                    DB::update(DB::raw($sqlUpdateGoal));
                                    if($strUpdateGoal == ""){
                                        $strUpdateGoal = $uGoalCode;
                                    } else {
                                        $strUpdateGoal .= ", ".$uGoalCode;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if($strNullData != ""){
                if($strError == ""){
                    $strError = $strNullData;
                } else {
                    $strError .= '<br/>'.$strNullData;
                }
            }

            if($strErrorGoal != ""){
                if($strError == ""){
                    $strError = $strErrorGoal;
                } else {
                    $strError .= '<br/>'.$strErrorGoal;
                }
            }

            if($strErrorUnit != ""){
                if($strError == ""){
                    $strError = $strErrorUnit;
                } else {
                    $strError .= '<br/>'.$strErrorUnit;
                }
            }
            $strSuccess = "";
            if($successRow > 0){
                $strSuccess = '&nbsp;&nbsp;&nbsp; + Import thành công: ' . $successRow . ' dòng.';
            }
            if($strUpdateGoal != ''){
                if($strSuccess == ""){
                    $strSuccess = '&nbsp;&nbsp;&nbsp; + Danh sách mã cập nhật: ' . $strUpdateGoal . '.';
                } else {
                    $strSuccess .= '<br/>&nbsp;&nbsp;&nbsp; + Danh sách mã cập nhật: ' . $strUpdateGoal . '.';
                }
            }
            if($strSuccess != ""){
                Session::flash('message-success', '<b>Import mục tiêu</b><hr/>'.$strSuccess);
            }
            if($strError != ""){
                Session::flash('message-errors', '<b>Import mục tiêu</b><hr/>'.$strError);
            }
            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        Session::flash('type', 1);
        return redirect('importGoal');
    }

    public function beforeImportPriorityCorporation($path , $startRow, $rename, $arrDataSession){
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        if($sAccessLevel != 1){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 7);
            return redirect('importGoal');
        }

        #Load by Sheet Name
        #Excel::selectSheets('Sheet1')->load($path.$rename, function($reader){
        $inputFileName = $path;
        $objPHPExcel = new PHPExcel();
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);

        #Check exist data in this month of year
        $title = $sheet->rangeToArray('B' . 2);
        $like = $title[0][0];
        if (strtolower(trim($like)) != strtolower(commonUtils::TITLE_IMPORT_CORPORATION)) {
            Session::flash('message-errors', $this->config->get('constant.ERR_IMPORT_FILE_INVALID'));
            $this->clearSession();
            Session::flash('type', 7);
            return redirect('importGoal');
        }

        $yearApply = $sheet->rangeToArray('E' . 3)[0][0];
        $applyDate = $sheet->rangeToArray('E' . 4)[0][0];
        if($yearApply == '' || $applyDate == ''){
            Session::flash('message-errors', 'Ngày áp dụng rỗng hoặc năm rỗng.'.$this->config->get('constant.WARN_CHECK_DATA'));
            $this->clearSession();
            Session::flash('type', 7);
            return redirect('importGoal');
        }
        $date = substr($applyDate, 0, 2);
        $month = substr($applyDate, 3, 2);
        $year = (isset($yearApply) && $yearApply != null) ? $yearApply : '';
        $compareCorporationCode = trim($sheet->rangeToArray(trim('B5'))[0][0]);
        #get Index of Highest Column in current sheet
        $dayCheck = $year . '-' . $month . '-' . $date;
        $fdayCheck = $date . '/' . $month . '/' . $year;

        $corporation = DB::table('corporation')->where('corporation_code', commonUtils::CORPORATION_CODE)->first();
        $corporationId = $corporation->id;

        $sqlILCO = "
            SELECT ilco.*, co.corporation_code
            FROM important_level_corporation ilco
            LEFT JOIN corporation co ON co.id = ilco.corporation_id
            WHERE ilco.inactive = 0
            AND ilco.apply_date = '" . $dayCheck . "'
            AND ilco.corporation_id = '" . $corporationId . "'
        ";
        $objILCODB = DB::select(DB::raw($sqlILCO));

        $sqlApplyDateBCor = "
                    SELECT apply_date
                    FROM important_level_corporation
                    WHERE year(apply_date) = ".$year."
                    GROUP BY apply_date
                    ORDER BY apply_date DESC
                ";
        $objApplyDateBF = DB::select(DB::raw($sqlApplyDateBCor));

        if(count($objApplyDateBF) > 0){
            if($objApplyDateBF[0]->apply_date >  $dayCheck){
                $str = "Đã tồn tại tỷ trọng áp dụng từ ngày <b>"
                    .commonUtils::formatDate($objApplyDateBF[0]->apply_date)."</b> lớn hơn ngày áp dụng trên file import <b>"
                    .$fdayCheck."</b>"
                ;
                Session::flash('message-errors', $str);
                $this->clearSession();
                Session::flash('type', 7);
                return redirect('importGoal');
            }
        }

        $isLocked = $this->checkLockData('', '', $dayCheck, 0, '');
        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu áp dụng từ ngày <b>'.$fdayCheck.'</b> đang khóa. Vui lòng kiểm tra lại!');
            $this->clearSession();
            Session::flash('type', 7);
            return redirect('importGoal');
        }


        $strShow = "";
        $strIssetData = "";
        if(count($objILCODB) != 0){
            $strIssetData = 'exist';
            $strShow = "&nbsp;&nbsp;&nbsp;+ Dữ liệu tỷ trọng áp dụng từ ngày <b>".$fdayCheck."</b> cho <b>Công ty Mobifone</b> đã tồn tại.";
            $strShow .= "<br/> ** Cảnh báo: Khi ghi đè tỷ trọng công ty thì toàn bộ Tỷ trọng, Kế hoạch, Thực hiện Phòng/Đài/MBF HCM, Tổ/Quận/Huyện, Chức danh, Nhân viên áp dụng từ ngày <b>".$fdayCheck."</b> ";
            $strShow .= " trong năm <b>".$year."</b> sẽ bị xóa!";
        }
        $data = array();
        $data[0] = $corporation;
        $data[1] = $objILCODB;
        $data[2] = $objApplyDateBF;
        /**
         * Config exist important_level_company database
         */
        Session::put('strIssetData', $strIssetData);
        Session::put('strIssetDataShow', $strShow);

        Session::put('pathFile', $path);
        Session::put('startRow', $startRow);
        Session::put('curType', 7);
        Session::put('curExcelFile', $rename);
        Session::put('data', $data);
        Session::save();

        Session::flash('type', 7);
        return redirect('importGoal');

    }

    public function importPriorityCorporation(){
        DB::beginTransaction();
        try{
            $path           = Session::get('pathFile');
            $startRow       = Session::get('startRow');
            $actionUser     = Session::get('sid');
            $strIssetData   = Session::get('strIssetData');
            $curExcelFile   = Session::get('curExcelFile');
            $data           = Session::get('data');

            #Load by Sheet Name
            #Excel::selectSheets('Sheet1')->load($path.$rename, function($reader){
            $inputFileName = $path;
            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            #Get worksheet dimensions
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();

            $yearApply          = $sheet->rangeToArray('E' . 3);
            $applyDate          = $sheet->rangeToArray('E' . 4);
            $date               = substr($applyDate[0][0], 0, 2);
            $month              = substr($applyDate[0][0], 3, 2);
            $year               = $yearApply[0][0];
            $formatApplyDate    = $yearApply[0][0] . '-' . substr($applyDate[0][0], 3, 2) . '-' . substr($applyDate[0][0], 0, 2);
            $showApplyDate      = substr($applyDate[0][0], 0, 2).'/' . substr($applyDate[0][0], 3, 2) . '/' . $yearApply[0][0];

            if(count($data[1]) > 0){
                $isLock = 0;
                foreach($data[1] as $ilco){
                    if($ilco->lock == 1){
                        $isLock = 1;
                        break;
                    }
                }
                if ($isLock == 1) {
                    Session::flash('isset-codes-errors', '<b>Import Tỷ trọng Công ty</b><hr/>'.
                                                            "Import thất bại. Vui lòng kiểm tra lại <b>Ngày áp dụng: " .
                                                            $date . '/' . $month . "/" . $year . "</b> đang bị khóa.");
                    $this->clearSession();
                    Session::flash('type', 7);
                    return redirect('importGoal');
                }

                /**
                 * data isn't lock, begin override and write log here
                 * Delete important level company
                 * Delete important level area
                 * Delete important level position
                 * Delete target area
                 * Delete target position
                 * Delete target employee
                 * Lock tỷ trọng phòng đài có apply_date trước
                 */
                $sqlDelete = "DELETE FROM important_level_corporation
                              WHERE apply_date = '".$formatApplyDate."'";
                DB::delete(DB::raw($sqlDelete));

                $sqlDeleteILC = "DELETE FROM important_level_company
                                    WHERE apply_date >= '".$formatApplyDate."'
                                    AND year(apply_date) = ".$year."";
                DB::delete(DB::raw($sqlDeleteILC));

                $sqlDeleteILA = "DELETE FROM important_level_area
                                WHERE year = ".$year."
                                AND month >= ".$month."
                                AND month <= 12";
                DB::delete(DB::raw($sqlDeleteILA));

                $sqlDeleteILP = " DELETE FROM important_level_position
                                WHERE year = ".$year."
                                AND month >= ".$month."
                                AND month <= 12";
                DB::delete(DB::raw($sqlDeleteILP));

                $sqlDeleteTA = "DELETE FROM target_area
                                WHERE year = ".$year."
                                AND month >= ".$month."
                                AND month <= 12";
                DB::delete(DB::raw($sqlDeleteTA));

                $sqlDeleteTP = "DELETE FROM target_position
                                WHERE year = ".$year."
                                AND month >= ".$month."
                                AND month <= 12";
                DB::delete(DB::raw($sqlDeleteTP));

                $sqlDeleteTE = "DELETE FROM target_employee
                                WHERE year = ".$year."
                                AND month >= ".$month."
                                AND month <= 12";
                DB::delete(DB::raw($sqlDeleteTE));

                $dataLog = array('function_name' => 'Tỷ trọng cho Công ty (importPriorityCorporation)',
                    'action' => commonUtils::ACTION_OVERRIDE,
                    'url' => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'id_row' => 0,
                    'old_value' => '',
                    'new_value' => $curExcelFile,
                    'created_user' => $actionUser);

                #Write log override here
                DB::table('kpi_log')->insert($dataLog);
            }

            /** @var  $sqlApplyDateBCor: get apply date before current apply date in excel file */
            if($data[2] > 0){
                $sqlLockILCor = "
                        UPDATE `important_level_corporation` SET `lock`=1
                        WHERE year(apply_date) = ".$year."
                        AND apply_date <= '".$formatApplyDate."'
                    ";
                DB::update(DB::raw($sqlLockILCor));
            }

            $objGoalDB = DB::table('goal')->where('inactive', 0)->get();

            $arrGoalCode = array();
            foreach($objGoalDB as $goal){
                $arrGoalCode[] = $goal->goal_code;
            }
            $strErrGoalCode = "";
            $strNullGoalCode = "";

            $sumImportantLevel = 0;
            $index = 0;
            $indexChild = 0;
            $arrChildExcel = array();
            $arrPriorityCorporation[] = array();

            for ($row = $startRow; $row <= $highestRow; $row++) {
                #Read a row of data into an array
                $dataSheet = $sheet->rangeToArray('A' . $row . ':' . 'G' . $row, NULL, TRUE, FALSE);

                $no             = trim($dataSheet[0][0]);
                $code           = trim($dataSheet[0][1]);
                $isParent       = trim($dataSheet[0][3]);
                $targetValue    = trim($dataSheet[0][5]);
                $importantLevel = trim($dataSheet[0][6]);

                $importantLevel = (isset($importantLevel) && ($importantLevel != "" || $importantLevel != null)) ? $importantLevel : 0;
                $isParent       = (isset($isParent) && ($isParent != "" || $isParent != null) && (int)$isParent != 0) ? 1 : 0;
                $targetValue    = (isset($targetValue) && is_numeric($targetValue) && $importantLevel != 0) ? $targetValue : 0;
                //$targetValue = 0;
                #Check valid data get from excel file
                if (
                    $code == ""
                    || $code == null
                ) {
                    #Define errors null data in here
                    if ($strNullGoalCode == "") {
                        $strNullGoalCode .= "<b>Mã mục tiêu rỗng tại dòng: </b>" . $no;
                    } else {
                        $strNullGoalCode .= ' ,' . $no;
                    }
                } else {
                    $unitId     = '';
                    $goalId     = '';
                    $parentId   = '';
                    $exist      = 0;
                    foreach($objGoalDB as $insideGoal){
                        if($insideGoal->goal_code == $code){
                            $exist      = 1;
                            $unitId     = $insideGoal->unit_id;
                            $parentId   = $insideGoal->parent_id;
                            $goalId     = $insideGoal->id;
                            break;
                        }
                    }

                    if($exist == 1){
                        if((int)$parentId == 0){
                            $sumImportantLevel += $importantLevel;
                        }else{

                            if($targetValue == 0){
                                Session::flash('message-errors', 'Mục tiêu <b>'.$code.'</b>  tồn tại Tỷ trọng nhưng chưa phân bổ Kế hoạch. Vui lòng kiểm tra lại và kiểm tra cho các mục tiêu khác!');
                                $this->clearSession();
                                Session::flash('type', 7);
                                return redirect('importGoal');
                            }

                            $arrChildExcel[$indexChild]['goalId']           = $goalId;
                            $arrChildExcel[$indexChild]['isParent']         = ($parentId == 0) ? 0 : 1;
                            $arrChildExcel[$indexChild]['parentId']         = $parentId;
                            $arrChildExcel[$indexChild]['unitId']           = $unitId;
                            $arrChildExcel[$indexChild]['importantLevel']   = $importantLevel;
                            $arrChildExcel[$indexChild]['targetValue']      = $targetValue;
                            $indexChild++;
                        }
                        #Write to array
                        $arrPriorityCorporation[$index]['goalId']           = $goalId;
                        $arrPriorityCorporation[$index]['isParent']         = ($parentId == 0) ? 0 : 1;
                        $arrPriorityCorporation[$index]['parentId']         = $parentId;
                        $arrPriorityCorporation[$index]['unitId']           = $unitId;
                        $arrPriorityCorporation[$index]['importantLevel']   = $importantLevel;
                        $arrPriorityCorporation[$index]['targetValue']      = $targetValue;
                        $index++;

                    } else {
                        if ($strErrGoalCode == "") {
                            $strErrGoalCode .= "<b>Mã mục tiêu lỗi tại dòng: </b>" . $no;
                        } else {
                            $strErrGoalCode .= ' ,' . $no;
                        }
                    }
                }
            }

            $strError = "";
            if($strErrGoalCode != ""){
                if($strError == ""){
                    $strError = '* '.$strErrGoalCode.'.';
                } else {
                    $strError .= '* '.$strErrGoalCode.'.';
                }
            }

            if($strNullGoalCode != ""){
                if($strError == ""){
                    $strError = '* '.$strNullGoalCode.'.';
                } else {
                    $strError .= '<br/>* '.$strNullGoalCode.'.';
                }
            }

            if($strError != ""){
                Session::flash('message-errors', '<b>Import Tỷ trọng Công ty</b><hr/>'.$strError);
                $this->clearSession();
                Session::flash('type', 7);
                return redirect('importGoal');
            }

            if(count($arrPriorityCorporation) == 0){
                Session::flash('message-errors', '<b>Import Tỷ trọng Công ty</b><hr/>* File Excel import không có dữ liệu. Vui lòng kiểm tra lại.');
                $this->clearSession();
                Session::flash('type', 7);
                return redirect('importGoal');
            }
            /*********************************************************************************************************/
            #Begin import to database here
            $benchMark = 0;
            $parentBenchMark = 0;
            $sumSuccess = 0;
            $corporation = $data[0];
            $sumImportantLevelChildren = 0;

            foreach($arrPriorityCorporation as $priorityCorporation ){
                $goalId         = $priorityCorporation['goalId'];
                $isParent       = $priorityCorporation['isParent'];
                $unitId         = $priorityCorporation['unitId'];
                $importantLevel = $priorityCorporation['importantLevel'];
                $targetValue    = $priorityCorporation['targetValue'];

                if( $isParent == 0 ){
                    #Calculate benchmark of parent
                    if ($sumImportantLevel != 0) {
                        //$benchMark = round((($importantLevel / $sumImportantLevel) * 100), 2);
                        $benchMark = (100 / $sumImportantLevel) * $importantLevel;
                    }

                    #Set value benchMark for calculate of child
                    $parentBenchMark = $benchMark;

                    #Ceate array data for insert
                    $importantLevelCorporation = array(
                        'corporation_id'    => $corporation->id,
                        'goal_id'           => $goalId,
                        'apply_date'        => $formatApplyDate,
                        'important_level'   => $importantLevel,
                        'benchmark'         => $benchMark,
                        'target_value'      => 0,
                        'unit_id'           => 0,
                        'goal_level'        => 0,
                        'created_user'      => $actionUser,
                        'updated_user'      => 1
                    );

                    #Calculate total Important level for child
                    $sumImportantLevelChildren = 0;
                    foreach($arrChildExcel as $childGoal ){
                        if($childGoal['parentId'] == $goalId){
                            $sumImportantLevelChildren += $childGoal['importantLevel'];
                        }
                    }

                } else {
                    #Calculate benchMark child
                    if ($sumImportantLevelChildren == 0) {
                        $benchMark = 0;
                    } else {
                        $benchMark = ( $parentBenchMark / $sumImportantLevelChildren ) * $importantLevel;
                        $importantLevelCorporation = array(
                            'corporation_id'    => $corporation->id,
                            'goal_id'           => $goalId,
                            'apply_date'        => $formatApplyDate,
                            'important_level'   => $importantLevel,
                            'benchmark'         => $benchMark,
                            'target_value'      => $targetValue,
                            'unit_id'           => $unitId,
                            'goal_level'        => 1,
                            'created_user'      => $actionUser,
                            'updated_user'      => 1
                        );
                    }
                }
                DB::table('important_level_corporation')->insert($importantLevelCorporation);
                $sumSuccess++;
            }
            if($sumSuccess > 0){
                Session::flash('message-success', '<b>Import Tỷ trọng Công ty</b><hr/>* Import thành công tỷ trọng áp dụng từ ngày <b>'.$showApplyDate.'</b>: '.$sumSuccess.' dòng.');

                $dataLog = array('function_name' => 'Tỷ trọng cho Công ty (importPriorityCorporation)',
                    'action' => commonUtils::ACTION_IMPORT,
                    'url' => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'id_row' => 0,
                    'old_value' => '',
                    'new_value' => '<b>Import Tỷ trọng Công ty</b><br/>* Import thành công '.$sumSuccess.' dòng.<br/>'.$curExcelFile,
                    'created_user' => $actionUser);

                #Write log override here
                DB::table('kpi_log')->insert($dataLog);
            }
            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        $this->clearSession();
        Session::flash('type', 7);
        return redirect('importGoal');
    }

    public function beforeImportPriorityCompany($path, $startRow, $typeImport, $rename, $monthYear, $arrDataSession)
    {
        $sAccessLevel   = $arrDataSession['sAccessLevel'];

        if($sAccessLevel != 1){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 2);
            return redirect('importGoal');
        }

        #Load by Sheet Name
        $inputFileName = $path;
        $objPHPExcel = new PHPExcel();
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }
        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);

        #Check exist data in this month of year
        $title = $sheet->rangeToArray('B' . 2);
        $like = $title[0][0];
        if (strtolower(trim($like)) != strtolower(commonUtils::TITLE_IMPORT_COMPANY_GOAL)) {
            Session::flash('message-errors', '<b>Import Tỷ trọng Phòng/Đài/MBF HCM</b><hr>'.$this->config->get('constant.ERR_IMPORT_FILE_INVALID'));
            $this->clearSession();
            Session::flash('type', 2);
            return redirect('importGoal');
        }

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $yearApply  = $sheet->rangeToArray('G' . 3);
        $applyDate  = $sheet->rangeToArray('G' . 4);
        $date       = substr($applyDate[0][0], 0, 2);
        $month      = substr($applyDate[0][0], 3, 2);
        $year       = $yearApply[0][0];

        #get Index of Highest Column in current sheet
        $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

        $dayCheck   = $year . '-' . $month . '-' . $date;
        $fdayCheck  = $date . '/' . $month . '/' . $year;

        $corApplyDate = $this->getApplyDate4Corporation($year);

        if($corApplyDate == ""){
            Session::flash('message-errors', '<b>Import Tỷ trọng Phòng/Đài/MBF HCM</b><hr>'.'Vui lòng import tỷ trọng cho Công ty Mobifone năm <b> '.
                                            $year.'</b> trước khi import tỷ trọng cho Phòng/Đài/MBF HCM.');
            $this->clearSession();
            Session::flash('type', 2);
            return redirect('importGoal');
        }
        $diff  = commonUtils::dateDifference($corApplyDate, $dayCheck);
        if($diff < 0){
            Session::flash('message-errors', '<b>Import Tỷ trọng Phòng/Đài/MBF HCM</b><hr>'.'Ngày áp dụng Phòng/Đài/MBF HCM không được nhỏ hơn <b> '.$corApplyDate.'</b>.');
            $this->clearSession();
            Session::flash('type', 2);
            return redirect('importGoal');
        }

        $sqlILCorporation = "SELECT cor.*, g.goal_code, g.parent_id
                            FROM important_level_corporation cor LEFT JOIN goal g ON g.id = cor.goal_id
                            WHERE cor.apply_date = '".$corApplyDate."'
                            AND cor.inactive =  0";
        $dataILCODB = DB::select(DB::raw($sqlILCorporation));

        $sqlApplyDateBCom = "SELECT apply_date
                            FROM important_level_company
                            WHERE year(apply_date) = ".$year."
                            GROUP BY apply_date
                            ORDER BY apply_date DESC";
        $objApplyDateBF = DB::select(DB::raw($sqlApplyDateBCom));

        if(count($objApplyDateBF) > 0){
            if($objApplyDateBF[0]->apply_date >  $dayCheck){
                $str = "Đã tồn tại tỷ trọng áp dụng từ ngày <b>"
                    .commonUtils::formatDate($objApplyDateBF[0]->apply_date)."</b> lớn hơn ngày áp dụng trên file import <b>"
                    .$fdayCheck."</b>";
                Session::flash('message-errors', $str);
                $this->clearSession();
                Session::flash('type', 2);
                return redirect('importGoal');
            }
        }

        if(count($dataILCODB) == 0){
            Session::flash('message-errors', '<b>Import Tỷ trọng Phòng/Đài/MBF HCM</b><hr> Vui lòng import tỷ trọng Công ty Mobifone trước khi import tỷ trọng Phòng/Đài/MBF HCM');
            $this->clearSession();
            Session::flash('type', 2);
            return redirect('importGoal');
        }

        $isLocked = $this->checkLockData('', '', $dayCheck, 1, '');
        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu áp dụng từ ngày <b>'.$fdayCheck.'</b> đang khóa. Vui lòng kiểm tra lại!');
            $this->clearSession();
            Session::flash('type', 2);
            return redirect('importGoal');
        }

        $sqlILC = "SELECT ilc.*, c.company_code
                    FROM important_level_company ilc
                    LEFT JOIN company c ON c.id = ilc.company_id
                    WHERE ilc.inactive = 0
                    AND ilc.apply_date = '" . $dayCheck . "'";
        $dataILCDB = DB::select(DB::raw($sqlILC));

        $listCompanyIdExist = "";
        $listCompanyCodeExist = "";
        if(count($dataILCDB) > 0){
            $arrExistCompanyCode = array();
            for ($i = 5; $i < $indexHighestColumn - 4; $i += 2) {
                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($i);
                $compareCompanyCode = trim($sheet->rangeToArray(trim($currentColumn . '6'))[0][0]);

                foreach($dataILCDB as $importantLevelCompany){
                    if($importantLevelCompany->company_code == trim($compareCompanyCode)
                        && trim($compareCompanyCode) != ""){
                        if(!in_array($importantLevelCompany->company_code, $arrExistCompanyCode)){
                            $arrExistCompanyCode[] = $importantLevelCompany->company_code;
                            if($listCompanyCodeExist == ""){
                                $listCompanyIdExist = $importantLevelCompany->company_id;
                                $listCompanyCodeExist = $importantLevelCompany->company_code;
                            } else {
                                $listCompanyIdExist .= ','.$importantLevelCompany->company_id;
                                $listCompanyCodeExist .= ', '.$importantLevelCompany->company_code;
                            }
                        }
                        break;
                    }
                }
            }
        }
        $strShow = "";
        if($listCompanyCodeExist != ""){
            $strShow = "&nbsp;&nbsp;&nbsp;+ Dữ liệu tỷ trọng áp dụng từ ngày <b>".$fdayCheck."</b> cho Phòng/Đài/MBF HCM: <b>".$listCompanyCodeExist.'</b> đã tồn tại.';
            $strShow .= "<br><b>*** Chú ý:</b> Khi ghi đè tỷ trọng Phòng/Đài/MBF HCM thì các dữ liệu liên quan: Tỷ trọng/Kế hoạch/Thực hiện Tổ/Quận/Huyện, Chức danh, Nhân viên "
                ." thuộc Phòng/Đài/MBF HCM <b>".$listCompanyCodeExist."</b> áp dụng sau ngày <b>".$fdayCheck."</b> trong năm <b> ".$year."</b> sẽ bị xóa!";
        }

        $data = array();
        $data['objILCODB']      = $dataILCODB;
        $data['objApplyDateBF'] = $objApplyDateBF;
        $data['corApplyDate']   = $corApplyDate;

        /**
         * Config exist important_level_company database
         */
        Session::put('strIssetData', $listCompanyIdExist);
        Session::put('strIssetDataShow', $strShow);

        Session::put('dataImportMultiTE', $data);
        Session::put('dayCheck', $dayCheck);
        Session::put('fDayCheck', $fdayCheck);
        Session::put('pathFile', $path);
        Session::put('startRow', $startRow);
        Session::put('curType', $typeImport);
        Session::put('curExcelFile', $rename);
        Session::put('monthYear', $monthYear);
        Session::save();
        Session::flash('type', 2);
        return redirect('importGoal');
    }

    /** Import ty trong cho phong/dai/mbf
     * @param $path
     * @param $startRow
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception...
     */
    public function importPriorityCompany(){
        DB::beginTransaction();
        try{
            /* *********************************************************************************************************
             * Get all data from session
             * ********************************************************************************************************/
            $path           = Session::get('pathFile');
            $startRow       = Session::get('startRow');
            $actionUser     = Session::get('sid');
            $strIssetData   = Session::get('strIssetData');
            $curExcelFile   = Session::get('curExcelFile');

            $data           = Session::get('dataImportMultiTE');
            $objILCODB      = $data['objILCODB'];
            $corApplyDate   = $data['corApplyDate'];

            /**********************************************************************************************************/
            #Load by Sheet Name
            $inputFileName = $path;
            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            #Get worksheet dimensions
            $sheet          = $objPHPExcel->getSheet(0);
            $highestRow     = $sheet->getHighestRow();
            $highestColumn  = $sheet->getHighestColumn();

            $yearApply      = $sheet->rangeToArray('G' . 3);
            $applyDate      = $sheet->rangeToArray('G' . 4);
            $date           = substr($applyDate[0][0], 0, 2);
            $month          = substr($applyDate[0][0], 3, 2);
            $year           = $yearApply[0][0];

            $formatApplyDate = $yearApply[0][0] . '-' . substr($applyDate[0][0], 3, 2) . '-' . substr($applyDate[0][0], 0, 2);

            /* *********************************************************************************************************
             * Override data here
             * Delete important level area
             * Delete important level position
             * Delete target area
             * Delete target position
             * Delete target employee
             * Lock tỷ trọng phòng đài có apply_date trước
             * Tính lại diểm thực hiện cho Phòng/Đài/MbF có ngày áp dụng trước đó
             * ********************************************************************************************************/
            if($strIssetData != ""){
                $arrIssetData = explode(',', $strIssetData);
                foreach($arrIssetData as $issetData){
                    $this->deleteILC($issetData, $year, $formatApplyDate);
                    $this->deleteILA($issetData, 0, $year, $month, 12);
                    $this->deleteTA($issetData, 0, $year, $month, 12);
                    $this->deleteILP($issetData, 0, 0, $year, $month, 12);
                    $this->deleteTP($issetData, 0, 0, $year, $month, 12);
                    $this->deleteTE($issetData, 0, 0, $year, $month, 12, 0);

                    /* *************************************************************************************************
                    * Tính lại kpi cho Phòng/Đài/MBF HCM
                    * *************************************************************************************************/
                    $bfApplyDate = $this->getApplyDate4Company($issetData, $year, $formatApplyDate);
                    if($bfApplyDate != ''){
                        $this->formatIPOCompany($issetData, $applyDate);
                        $this->calKpi4Company($issetData, $year, 1, $month, $formatApplyDate, $actionUser);
                    } else {
                        $this->deleteILA($issetData, 0, $year, 1, 12);
                        $this->deleteTA($issetData, 0, $year, 1, 12);
                        $this->deleteILP($issetData, 0, 0, $year, 1, 12);
                        $this->deleteTP($issetData, 0, 0, $year, 1, 12);
                        $this->deleteTE($issetData, 0, 0, $year, 1, 12, 0);
                    }
                    /***************************************************************************************************/
                }

                $this->formatIPOCorporation($corApplyDate);
                $this->calKpi4Corporation($corApplyDate, $formatApplyDate, $actionUser);

                $dataLog = array('function_name' => 'Tỷ trọng cho Phòng/Đài/MBF HCM (importPriorityCompany)',
                    'action' => commonUtils::ACTION_OVERRIDE,
                    'url' => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'id_row' => 0,
                    'old_value' => '',
                    'new_value' => $curExcelFile,
                    'created_user' => $actionUser);

                #Write log override here
                DB::table('kpi_log')->insert($dataLog);

            }

            $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $objGoalDB = DB::table('goal')->where('inactive', 0)->get();
            $objCompanyDB = DB::table('company')->where('inactive', 0)->get();

            $arrDataValidChild = array();
            $iDV = 0;

            $arrDataValidParent = array();
            $iDVP = 0;

            $arrDataError = array();
            $iDE = 0;

            $arrParentIL = array();
            $iP = 0;

            $arrGoalCodeValid = array();
            $listGoalNValid = "";
            $listGoalNull = "";

            $arrCodeNotPriCor = array();

            $arrGoalRelationship = array();

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $dataSheet = $sheet->rangeToArray('A' . $row . ':B' . $row, NULL, TRUE, FALSE);

                $no    = trim($dataSheet[0][0]);
                $gCode = trim($dataSheet[0][1]);

                $gCode = (isset($gCode) && $gCode != null) ? $gCode : '';
                $no    = (isset($no) && $no != null) ? $no : '?';

                if($gCode != ''){
                    $isValidGoalCode = 0;
                    foreach($objGoalDB as $goal){
                        if($goal->goal_code == $gCode){
                            $isValidGoalCode = 1;
                            break;
                        }
                    }
                    if($isValidGoalCode == 1){
                        $isValidILCOR = 0;
                        foreach($objILCODB as $ilcor){
                            if($ilcor->goal_code == $gCode){
                                $isValidILCOR = 1;
                                break;
                            }

                        }
                        if($isValidILCOR == 0){
                            if(!in_array($gCode, $arrCodeNotPriCor)){
                                $arrCodeNotPriCor[] = $gCode;
                            }
                        }

                        if(!in_array($gCode, $arrGoalCodeValid)){
                            $arrGoalCodeValid[] = $gCode;
                        }
                    } else {
                        if($listGoalNValid == ""){
                            $listGoalNValid = $gCode;
                        } else {
                            $listGoalNValid .= ', '.$gCode;
                        }
                    }
                } else {
                    if($listGoalNull == ""){
                        $listGoalNull = $no;
                    } else {
                        $listGoalNull .= ', '.$no;
                    }
                }
            }

            $listCodeNotPriCor = "";
            if(count($arrCodeNotPriCor) > 0){
                foreach($arrCodeNotPriCor as $CodeNPC){
                    if($listCodeNotPriCor == ""){
                        $listCodeNotPriCor = $CodeNPC;
                    } else {
                        $listCodeNotPriCor .= ', '.$CodeNPC;
                    }
                }
            }
            if($listCodeNotPriCor != ""){
                $arrDataError[$iDE]['content'] = 'Danh sách mã mục không tồn tại Tỷ trọng Công ty: '.$listCodeNotPriCor.'.';
                $iDE++;
            }
            if($listGoalNValid != ""){
                $arrDataError[$iDE]['content'] = 'Danh sách mã mục tiêu lỗi: '.$listGoalNValid.'.';
                $iDE++;
            }
            if($listGoalNull != ""){
                $arrDataError[$iDE]['content'] = $this->config->get('constant.NULL_GOAL_CODE').$listGoalNull.'.';
                $iDE++;
            }
            $listCompanyCodeErr = "";

            if(count($arrGoalCodeValid) > 0){
                for ($i = 5; $i < $indexHighestColumn - 4; $i = $i + 2) {
                    $currentColumn = PHPExcel_Cell::stringFromColumnIndex($i);
                    $companyCode = trim($sheet->rangeToArray($currentColumn . 6)[0][0]);
                    $companyCode = (isset($companyCode) && $companyCode != null) ? $companyCode : '';

                    /*Check valid company code*/
                    $companyId = -1;
                    foreach($objCompanyDB as $company){
                        if($company->company_code == $companyCode && $companyCode != ''){
                            $companyId = $company->id;
                            break;
                        }
                    }

                    $loopColumn = PHPExcel_Cell::stringFromColumnIndex($i + 1);

                    if($companyId != -1){
                        $totalParentIL = 0;
                        for ($row = $startRow; $row <= $highestRow; $row++) {
                            #Read a row of data into an array
                            $dataSheet = $sheet->rangeToArray('A' . $row . ':' . ($loopColumn) . $row, NULL, TRUE, FALSE);

                            $code           = trim($dataSheet[0][1]);
                            $targetValue    = trim($dataSheet[0][$i]);
                            $importantLevel = trim($dataSheet[0][$i + 1]);

                            $importantLevel = (isset($importantLevel) && is_numeric((int)$importantLevel)) ? (int)$importantLevel : 0;
                            $targetValue    = (isset($targetValue) && is_numeric($targetValue)) ? $targetValue : 0;

                            $parentId = 0;
                            $goalId = 0;
                            $validGoal = 0;
                            $coBenchmark = 0;
                            $unitId = -1;

                            if(in_array($code, $arrGoalCodeValid)){
                                foreach($objILCODB as $ilco){
                                    if(
                                        $ilco->goal_code == $code &&
                                        $ilco->important_level != 0
                                    ){
                                        $validGoal = 1;
                                        $parentId    = $ilco->parent_id;
                                        $goalId      = $ilco->goal_id;
                                        $coBenchmark = $ilco->benchmark;
                                        $unitId      = $ilco->unit_id;
                                        break;
                                    }
                                }

                                if($validGoal == 1 && $importantLevel != 0){

                                    if(!in_array($goalId, $arrGoalRelationship)){
                                        $arrGoalRelationship[] = $goalId;
                                    }

                                    if($parentId == 0){
                                        $totalParentIL += $importantLevel;
                                        $arrDataValidParent[$iDVP]['companyId']      = $companyId;
                                        $arrDataValidParent[$iDVP]['goalId']         = $goalId;
                                        $arrDataValidParent[$iDVP]['goalCode']       = $code;
                                        $arrDataValidParent[$iDVP]['unitId']         = $unitId;
                                        $arrDataValidParent[$iDVP]['importantLevel'] = $importantLevel;
                                        $arrDataValidParent[$iDVP]['coBenchmark']    = $coBenchmark;
                                        $arrDataValidParent[$iDVP]['applyDate']      = $formatApplyDate;
                                        $iDVP++;
                                    } else {

                                        /*if($targetValue == 0){
                                            Session::flash('message-errors', 'Mục tiêu <b>'.$code.'</b> thuộc Phòng/Đài/MBF HCM <b>'.$companyCode.'</b> tồn tại Tỷ trọng nhưng chưa phân bổ Kế hoạch. Vui lòng kiểm tra lại và kiểm tra cho các mục tiêu khác!');
                                            $this->clearSession();
                                            Session::flash('type', 2);
                                            return redirect('importGoal');
                                        }*/

                                        $arrDataValidChild[$iDV]['companyId']      = $companyId;
                                        $arrDataValidChild[$iDV]['goalId']         = $goalId;
                                        $arrDataValidChild[$iDV]['goalCode']       = $code;
                                        $arrDataValidChild[$iDV]['unitId']         = $unitId;
                                        $arrDataValidChild[$iDV]['parentId']       = $parentId;
                                        $arrDataValidChild[$iDV]['importantLevel'] = $importantLevel;
                                        $arrDataValidChild[$iDV]['coBenchmark']    = $coBenchmark;
                                        $arrDataValidChild[$iDV]['targetValue']    = $targetValue;
                                        $arrDataValidChild[$iDV]['applyDate']      = $formatApplyDate;
                                        $iDV++;
                                    }
                                }
                            }
                        }
                        if($totalParentIL > 0){
                            $arrParentIL[$iP]['companyId']     = $companyId;
                            $arrParentIL[$iP]['companyCode']   = $companyCode;
                            $arrParentIL[$iP]['totalParentIL'] = $totalParentIL;
                            $arrParentIL[$iP]['applyDate']     = $date.'/'.$month.'/'.$year;
                            $iP++;
                        }
                    } else {
                        if($companyCode != ""){
                            if($listCompanyCodeErr == ""){
                                $listCompanyCodeErr = $companyCode;
                            } else {
                                $listCompanyCodeErr = ', '.$companyCode;
                            }
                        }
                    }
                }
            }

            if($listCompanyCodeErr != ""){
                $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_COMP_CODE_APPLY').$listCompanyCodeErr.'.';
                $iDE++;
            }

            /*if(count($arrGoalRelationship) != 0 ){

                $sqlILCCheck = "
                    SELECT *
                    FROM important_level_company
                    WHERE inactive = 0
                    AND apply_date = '".trim($applyDate[0][0])."'
                ";

                $objILCCheck = DB::select(DB::raw($sqlILCCheck));

                foreach($objILCODB as $cILCO){

                    $existGL = 0;
                    foreach($arrGoalRelationship as $gl){

                        if($gl == $cILCO->goal_id){
                            $existGL = 1;
                            break;
                        }

                    }
                    if($existGL == 0){

                        $lastCheck = 0;
                        foreach($objILCCheck as $cILC){
                            if($cILC->goal_id == $cILCO->goal_id){
                                $lastCheck = 1;
                            }
                        }
                        if($lastCheck == 0){
                            Session::flash('message-errors', '<b>Import Tỷ trọng Phòng/Đài/MBF HCM</b><hr> Tồn tại tỷ trọng phân bổ Công ty Mobifone mà Phòng/Đài/MBF HCM chưa được phân bổ. Vui lòng kiểm tra lại!');
                            $this->clearSession();
                            Session::flash('type', 2);
                            return redirect('importGoal');
                        }

                    }

                }

            }*/

            $strError = "";
            if(count($arrDataError) > 0){
                foreach($arrDataError as $error){
                    if($strError == ""){
                        $strError = '<b>'.commonUtils::TITLE_IMPORT_COMPANY_GOAL.'<hr></b>- '.$error['content'];
                    }else{
                        $strError .= '</br>- '.$error['content'];
                    }
                }
            }
            /**********************************************************************************************************/
            $arrDataInsert = array();

            if(count($arrDataValidParent) > 0 && $arrParentIL > 0){
                $arrSuccess = array();
                $iS = 0;

                foreach($arrParentIL as $parentIL){
                    $iCalIL = $parentIL['totalParentIL'];

                    $numInsert = 0;
                    $listGoalInserted = "";
                    foreach($arrDataValidParent as $parent){
                        /* *************************************************************************************************
                         * Tính điểm chuẩn cho mục tiêu cha bao gồm:
                         *  Điểm chuẩn dùng cho phân bổ Tố/Quận/Huyện: $iBenchmark
                         *  Điểm chuẩn dùng khi tính KPI: $iCalBenchmark
                         * ************************************************************************************************/
                        $iCompanyId      = $parent['companyId'];
                        $iGoalId         = $parent['goalId'];
                        $iGoalCode       = $parent['goalCode'];
                        $iImportantLevel = $parent['importantLevel'];
                        $iCoBenchmark    = $parent['coBenchmark'];
                        $iApplyDate      = $parent['applyDate'];
                        $iUnitId         = $parent['unitId'];

                        if($parentIL['companyId'] == $iCompanyId){
                            $insPIL = 0;
                            foreach($arrDataValidParent as $insideParent){
                                if($insideParent['goalId'] == $iGoalId){
                                    $insPIL += $iImportantLevel;
                                }
                            }
                            /* ****************************************************************************************
                             * Calculate for parent
                             * ****************************************************************************************/
                            if($iCalIL > 0 && $insPIL > 0){
                                $iBenchmark = (100 / $iCalIL) * $iImportantLevel;
                                $iCalBenchmark = ($iCoBenchmark / $insPIL) * $iImportantLevel;

                                $importantLevelCompany = array(
                                    'company_id'        => $iCompanyId,
                                    'goal_id'           => $iGoalId,
                                    'apply_date'        => $iApplyDate,
                                    'important_level'   => $iImportantLevel,
                                    'benchmark'         => $iBenchmark,
                                    'cal_benchmark'     => 0,
                                    'target_value'      => 0,
                                    'unit_id'           => $iUnitId,
                                    'goal_level'        => 0
                                );
                                $arrDataInsert[] = $importantLevelCompany;
                                $numInsert++;
                                if($listGoalInserted == ""){
                                    $listGoalInserted = $iGoalCode;
                                } else {
                                    $listGoalInserted .= ', '.$iGoalCode;
                                }
                                /* *************************************************************************************
                                 * Calculate for child
                                 * ************************************************************************************/
                                foreach($arrDataValidChild as $child){
                                    $cCompanyId      = $child['companyId'];
                                    $cGoalId         = $child['goalId'];
                                    $cGoalCode       = $child['goalCode'];
                                    $cImportantLevel = $child['importantLevel'];
                                    $cCoBenchmark    = $child['coBenchmark'];
                                    $cApplyDate      = $child['applyDate'];
                                    $cUnitId         = $child['unitId'];
                                    $cTargetValue    = $child['targetValue'];
                                    $cParentId       = $child['parentId'];

                                    $aTotalChildIL = 0;/* Tổng tỷ trọng của các mục tiêu có cùng parent*/
                                    $eTotalChildIL = 0;/* Tổng tỷ trọng của các Phòng/Đài/MBF có cùng mục tiêu*/

                                    if($cParentId == $iGoalId && $cCompanyId == $iCompanyId){
                                        foreach($arrDataValidChild as $insideChild){
                                            if($insideChild['parentId'] == $iGoalId && $insideChild['companyId'] == $cCompanyId){
                                                $aTotalChildIL += $insideChild['importantLevel'];
                                            }
                                            if($insideChild['goalId'] == $cGoalId){
                                                $eTotalChildIL += $insideChild['importantLevel'];
                                            }
                                        }
                                        if($aTotalChildIL > 0 && $eTotalChildIL > 0){
                                            $cBenchmark = ($iBenchmark / $aTotalChildIL) * $cImportantLevel;
                                            $cCalBenchmark = ($cCoBenchmark / $eTotalChildIL) * $cImportantLevel;

                                            $importantLevelCompany = array(
                                                'company_id'        => $cCompanyId,
                                                'goal_id'           => $cGoalId,
                                                'apply_date'        => $cApplyDate,
                                                'important_level'   => $cImportantLevel,
                                                'benchmark'         => $cBenchmark,
                                                'cal_benchmark'     => 0,
                                                'target_value'      => $cTargetValue,
                                                'unit_id'           => $cUnitId,
                                                'goal_level'        => 1
                                            );
                                            $arrDataInsert[] = $importantLevelCompany;
                                            $numInsert++;
                                            $listGoalInserted .= ', '.$cGoalCode;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($numInsert > 0){
                        $arrSuccess[$iS]['companyCode'] = $parentIL['companyCode'];
                        $arrSuccess[$iS]['applyDate']   = $parentIL['applyDate'];
                        $arrSuccess[$iS]['numRows']     = $numInsert;
                        $arrSuccess[$iS]['listGoal']    = $listGoalInserted;
                        $iS++;
                    }
                }
                /* *****************************************************************************************************
                 * Insert to database
                 * ****************************************************************************************************/
                if(count($arrDataInsert) > 0){
                    DB::table('important_level_company')->insert($arrDataInsert);
                }
                /* *****************************************************************************************************
                 * Update Calbenchmark
                 * ****************************************************************************************************/
                $sqlILCAfter = "
                    SELECT *
                    FROM important_level_company
                    WHERE apply_date = '".$formatApplyDate."'
                ";

                $objILCAfter = DB::select(DB::raw($sqlILCAfter));

                $sqlILFMAfter = "
                    UPDATE important_level_company SET cal_benchmark = 0
                    WHERE apply_date = '".$formatApplyDate."'
                ";

                DB::update(DB::raw($sqlILFMAfter));


                foreach($objILCODB as $ilcor){

                    $totalILGoal = 0;
                    foreach($objILCAfter as $ilcA){
                        if($ilcA->goal_id == $ilcor->goal_id){
                            $totalILGoal += $ilcor->important_level;
                        }
                    }

                    if($totalILGoal > 0){
                        foreach($objILCAfter as $ilcAU){
                            if($ilcAU->goal_id == $ilcor->goal_id){

                                $calBenchmarkA = ($ilcor->benchmark / $totalILGoal) * $ilcAU->important_level;

                                $uILACalbechmark = array(
                                    'cal_benchmark'  => $calBenchmarkA
                                );
                                DB::table('important_level_company')
                                    ->where('company_id', $ilcAU->company_id)
                                    ->where('goal_id', $ilcAU->goal_id)
                                    ->where('apply_date', $ilcAU->apply_date)
                                    ->update($uILACalbechmark);
                            }

                        }
                    }
                }


                 /*****************************************************************************************************/

                $strSuccess = "";
                if(count($arrSuccess) > 0){
                    foreach($arrSuccess as $success){
                        if($strSuccess == ""){
                            $strSuccess = '<b>'.commonUtils::TITLE_IMPORT_COMPANY_GOAL
                                .'<hr>* '.$success['companyCode']
                                .'</b> đã import thành công <b>'.$success['numRows'].'</b> dòng dữ liệu Kế hoạch '
                                .' và Tỷ trọng áp dụng từ ngày <b>'.$success['applyDate'].'</b>'
                                .'<br/> &nbsp;&nbsp; - Danh sách mục tiêu đã import: '.$success['listGoal'].'.'
                            ;
                        }else{
                            $strSuccess .= '<b><br/>* '.$success['companyCode']
                                .'</b> đã import thành công <b>'.$success['numRows'].'</b> dòng dữ liệu Kế hoạch '
                                .' và Tỷ trọng áp dụng từ ngày <b>'.$success['applyDate'].'</b>'
                                .'<br/> &nbsp;&nbsp; - Danh sách mục tiêu đã import: '.$success['listGoal'].'.'
                            ;
                        }
                    }
                }
                if($strSuccess != ""){
                    Session::flash('message-success', $strSuccess);
                }
                if($strError != ""){
                    Session::flash('message-errors', $strError);
                }
                /*****************************************************************************************************/

                $dataLog = array();
                $dataLog['functionName'] = 'Tỷ trọng cho Phòng/Đài/MBF HCM (importPriorityCompany)';
                $dataLog['action']       = commonUtils::ACTION_IMPORT;
                $dataLog['url']          = 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile;
                $dataLog['newValue']     = $curExcelFile;
                $dataLog['createdUser']  = $actionUser;

                $this->writeLog($dataLog);

            }
            /**********************************************************************************************************/

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        $this->clearSession();
        Session::flash('type', 2);
        return redirect('importGoal');
    }

    public function beforeImportMultiPriorityArea($path, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession)
    {
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        if($sAccessLevel != 1){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 10);
            return redirect('importGoal');
        }

        $inputFileName = $path;
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheetNames = $objPHPExcel->getSheetNames();

        if($listSheetIndex == ""){
            #Call back error when array index sheet is null
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_NULL'));
            $this->clearSession();
            Session::flash('type', 10);
            return redirect('importGoal');
        }

        $arrSheets = commonUtils::getArraySheets($listSheetIndex);

        if(count($arrSheets) == 0){
            #Call back error when array index sheet is not valid
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_INVALID'));
            $this->clearSession();
            Session::flash('type', 10);
            return redirect('importGoal');
        }else{
            $numOfSheet =  $objPHPExcel->getSheetCount();
            foreach($arrSheets as $checkExist){
                if($checkExist >= $numOfSheet){
                    Session::flash('message-errors', $this->config->get('constant.ERR_OVER_NUMBER_SHEET'));
                    $this->clearSession();
                    Session::flash('type', 10);
                    return redirect('importGoal');
                }
            }
        }
        /* *************************************************************************************************************
         * Get all data need for import priority here
         * ************************************************************************************************************/
        $objLockDB = DB::table('lock')->where('inactive', 0)->get();

        #object company
        $objCompanyDB = DB::table('company')->where('inactive', 0)->get();

        #object area
        $objAreaDB = DB::table('area')->where('inactive', 0)->get();

        #object goal
        $objGoalDB = DB::table('goal')->where('inactive', 0)->get();

        $suff = 'trước khi import Tỷ trọng Tổ/Quận/Huyện!';

        if(count($objCompanyDB) == 0){
            #Call back error when $objAreaDB is null
            Session::flash('message-errors', $this->config->get('constant.NULL_COMPANY_DB').$suff);
            $this->clearSession();
            Session::flash('type', 10);
            return redirect('importGoal');
        }

        if(count($objAreaDB) == 0){
            #Call back error when $objAreaDB is null
            Session::flash('message-errors', $this->config->get('constant.NULL_AREA_DB').$suff);
            $this->clearSession();
            Session::flash('type', 10);
            return redirect('importGoal');
        }

        if(count($objGoalDB) == 0){
            #Call back error when $objAreaDB is null
            Session::flash('message-errors', $this->config->get('constant.NULL_GOAL_DB').$suff);
            $this->clearSession();
            Session::flash('type', 10);
            return redirect('importGoal');
        }
        /**************************************************************************************************************/
        $arrDataError = array();
        $iDE = 0;

        $arrDataOverride = array();
        $iDO = 0;

        $arrDistinctArea = array();/*Array with area not same in year and month*/
        $iDA = 0;

        $arrDataValid = array();
        $iDV = 0;

        $preCompanyCode = "";
        $preMonth = "";
        $preYear = "";
        $ilcApplyDate = "";

        $arrCPermission = array();
        $iGP = 0;
        /**************************************************************************************************************/
        foreach ($arrSheets as $arrSheet) {
            $sheet            = $objPHPExcel->getSheet($arrSheet);
            $yearApply        = trim($sheet->rangeToArray('E' . 3)[0][0]);
            $monthApply       = trim($sheet->rangeToArray('E' . 4)[0][0]);
            $titleApply       = trim($sheet->rangeToArray('C' . 2)[0][0]);
            $companyCodeApply = trim($sheet->rangeToArray('B' . 5)[0][0]);
            $highestColumn    = $sheet->getHighestColumn();
            $highestRow       = $sheet->getHighestRow();

            $year        = (isset($yearApply) && is_numeric((int)$yearApply) && $yearApply >= 2015) ? (int)$yearApply : '';
            $month       = (isset($monthApply) && is_numeric((int)$monthApply) && (int)$monthApply >= 1 && (int)$monthApply <= 12) ? (int)$monthApply : '';
            $companyCode = (isset($companyCodeApply) && $companyCodeApply != null) ? $companyCodeApply : '';
            $title       = (isset($titleApply) && $titleApply != null) ? $titleApply : '';
            $dir         = $month.'/'.$year;

            #get Index of Highest Column in current sheet
            $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

            if($year != '' && $month != '' && $companyCode != ''&& $title != ''){
                if($preCompanyCode == "" || strtolower(trim($companyCode)) == strtolower(trim($preCompanyCode))){
                    $preCompanyCode = $companyCode;
                } else {
                    Session::flash('message-errors', "Mỗi file excel import chỉ duy nhất mỗi Phòng/Đài/MBF HCM.");
                    $this->clearSession();
                    Session::flash('type', 10);
                    return redirect('importGoal');
                }

                if($month != $preMonth){
                    $preMonth = $month;
                } else {
                    Session::flash('message-errors', "Các sheet trên file import không được trùng tháng áp dụng.");
                    $this->clearSession();
                    Session::flash('type', 10);
                    return redirect('importGoal');
                }

                if($preYear == "" || $preYear == $year){
                    $preYear = $year;
                } else {
                    Session::flash('message-errors', "Năm áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 10);
                    return redirect('importGoal');
                }
                /* *****************************************************************************************************
                 * Kiểm tra mã phòng đài hợp lệ
                 * ****************************************************************************************************/
                $companyId = -1;
                foreach($objCompanyDB as $company){
                    if($company->company_code == $companyCode){
                        $companyId   = $company->id;
                        break;
                    }
                }
                /* *****************************************************************************************************
                 * Kiểm tra loại import hợp lệ
                 * ****************************************************************************************************/
                $validTitle = 0;
                if(strtolower(trim($title)) == strtolower(commonUtils::TITLE_IMPORT_AREA_GOAL)){
                    $validTitle = 1;
                }
                /* *****************************************************************************************************
                 * Kiểm tra dữ liệu đã khóa
                 * ****************************************************************************************************/
                $isLocked = $this->checkLockData($year, $month, '', 2, $companyId);
                /*foreach($objLockDB as $lock){
                    if($lock->ofyear == $year && $lock->ofmonth == $month && $lock->lock == 1){
                        $isLocked = 1;
                        break;
                    }
                }*/

                if($companyId != -1 && $isLocked == 0 && $validTitle == 1){
                    /* *************************************************************************************************
                     * Get object important level company for each sheet with month and year
                     * ************************************************************************************************/

                    $ilcApplyDate = $this->getApplyDate4Company($companyId, $year, "");
                    if($ilcApplyDate != ""){
                        $objILCDB = $this->getImportantLevelCompany($companyId, $ilcApplyDate);
                        $objILADB = $this->getImportantLevelArea($companyId, 0, $year, $month);

                        if(count($objILCDB) > 0){
                            /* *****************************************************************************************
                             * Get arrDataOverride
                             * Get arrayAreaCode valid
                             * Get arrGoalCode valid database
                             * Get arrGoal have priority company
                             * ****************************************************************************************/

                            $arrCodeNotPriCor = array();
                            $arrGoalValid = array();
                            $iGV = 0;

                            $listGoalNValid = "";
                            $listGoalNull = "";

                            for ($row = $startRow; $row <= $highestRow; $row++) {
                                $dataSheet = $sheet->rangeToArray('A' . $row . ':B' . $row, NULL, TRUE, FALSE);

                                $no    = trim($dataSheet[0][0]);
                                $gCode = trim($dataSheet[0][1]);

                                $gCode = (isset($gCode) && $gCode != null) ? $gCode : '';
                                $no    = (isset($no) && $no != null) ? $no : '?';

                                if($gCode != ''){
                                    $goalId   = -1;
                                    $parentId = -1;
                                    foreach($objGoalDB as $goal){
                                        if($goal->goal_code == $gCode){
                                            $goalId   = $goal->id;
                                            $parentId = $goal->parent_id;
                                            break;
                                        }
                                    }
                                    if($goalId != -1){
                                        $isValidILCOR = 0;
                                        foreach($objILCDB as $ilc){
                                            if($ilc->goal_code == $gCode){
                                                $isValidILCOR = 1;
                                                break;
                                            }
                                        }
                                        if($isValidILCOR == 0){
                                            if(!in_array($gCode, $arrCodeNotPriCor)){
                                                $arrCodeNotPriCor[] = $gCode;
                                            }
                                        }

                                        if(!in_array($gCode, $arrCodeNotPriCor)){
                                            if(count($arrGoalValid) == 0){
                                                $arrGoalValid[$iGV]['goalId']   = $goalId;
                                                $arrGoalValid[$iGV]['goalCode'] = $gCode;
                                                $arrGoalValid[$iGV]['parentId'] = $parentId;
                                                $iGV++;
                                            } else {
                                                $existGV = 0;
                                                foreach($arrGoalValid as $goalValid){
                                                    if($goalValid['goalId'] == $goalId){
                                                        $existGV = 1;
                                                        break;
                                                    }
                                                }
                                                if($existGV == 0){
                                                    $arrGoalValid[$iGV]['goalId']   = $goalId;
                                                    $arrGoalValid[$iGV]['goalCode'] = $gCode;
                                                    $arrGoalValid[$iGV]['parentId'] = $parentId;
                                                    $iGV++;
                                                }
                                            }
                                        }
                                    } else {
                                        if($listGoalNValid == ""){
                                            $listGoalNValid = $gCode;
                                        } else {
                                            $listGoalNValid .= ', '.$gCode;
                                        }
                                    }
                                } else {
                                    if($listGoalNull == ""){
                                        $listGoalNull = $no;
                                    } else {
                                        $listGoalNull .= ', '.$no;
                                    }
                                }
                            }
                            $listCodeNotPriCor = "";
                            if(count($arrCodeNotPriCor) > 0){
                                foreach($arrCodeNotPriCor as $CodeNPC){
                                    if($listCodeNotPriCor == ""){
                                        $listCodeNotPriCor = $CodeNPC;
                                    } else {
                                        $listCodeNotPriCor .= ', '.$CodeNPC;
                                    }
                                }
                            }

                            if($listCodeNotPriCor != ""){
                                $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                $arrDataError[$iDE]['content']    = 'Danh sách mã mục không tồn tại Tỷ trọng Công ty: '.$listCodeNotPriCor.'.';
                                $iDE++;
                            }
                            if($listGoalNValid != ""){
                                $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                $arrDataError[$iDE]['content']    = 'Danh sách mã mục tiêu lỗi: '.$listGoalNValid.'.';
                                $iDE++;
                            }
                            if($listGoalNull != ""){
                                $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_GOAL_CODE').$listGoalNull.'.';
                                $iDE++;
                            }
                            /* ***************************************************************************************/

                            $listAreaCodeErr = "";
                            $listAreaCodeNull= "";

                            $arrEachArea = array();
                            $iEA = 0;

                            for ($i = 4; $i < $indexHighestColumn - 4; $i++) {
                                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($i);
                                $compareAreaCode = trim($sheet->rangeToArray(trim($currentColumn . '7'))[0][0]);

                                $compareAreaCode = (isset($compareAreaCode) && $compareAreaCode != null ) ? $compareAreaCode : '';
                                //echo $compareAreaCode; die;
                                if($compareAreaCode != ""){
                                    $areaId   = -1;
                                    foreach($objAreaDB as $area){
                                        if($area->area_code == $compareAreaCode && $area->company_id == $companyId){
                                            $areaId   = $area->id;
                                            break;
                                        }
                                    }

                                    if($areaId != -1){
                                        $exist = 0;
                                        if(count($arrDistinctArea) == 0){
                                            $arrDistinctArea[$iDA]['companyId'] = $companyId;
                                            $arrDistinctArea[$iDA]['areaId']    = $areaId;
                                            $arrDistinctArea[$iDA]['year']      = $year;
                                            $arrDistinctArea[$iDA]['month']     = $month;
                                            $iDA++;
                                        } else {
                                            foreach($arrDistinctArea as $dArea){
                                                if($dArea['companyId'] == $companyId
                                                    && $dArea['areaId'] == $areaId
                                                    && $dArea['year']   == $year
                                                    && $dArea['month']  == $month){
                                                    $exist = 1;
                                                }
                                            }
                                            if($exist == 0){
                                                $arrDistinctArea[$iDA]['companyId'] = $companyId;
                                                $arrDistinctArea[$iDA]['areaId']    = $areaId;
                                                $arrDistinctArea[$iDA]['year']      = $year;
                                                $arrDistinctArea[$iDA]['month']     = $month;
                                                $iDA++;
                                            }
                                        }
                                        /*echo $exist;die;*/
                                        if($exist == 0){
                                            if(count($objILADB) > 0){
                                                /* *********************************************************************
                                                 * Get arr Dara Override
                                                 * ********************************************************************/
                                                $isILA = 0;
                                                foreach($objILADB as $ila){
                                                    if($ila->area_id == $areaId && $ila->company_id == $companyId
                                                        && $ila->year == $year && $ila->month == $month){
                                                        $isILA = 1;
                                                        break;
                                                    }
                                                }
                                                if($isILA == 1){
                                                    $arrDataOverride[$iDO]['sheetIndex']    = $arrSheet;
                                                    $arrDataOverride[$iDO]['companyId']     = $companyId;
                                                    $arrDataOverride[$iDO]['companyCode']   = $companyCode;
                                                    $arrDataOverride[$iDO]['areaId']        = $areaId;
                                                    $arrDataOverride[$iDO]['areaCode']      = $compareAreaCode;
                                                    $arrDataOverride[$iDO]['year']          = $year;
                                                    $arrDataOverride[$iDO]['month']         = $month;
                                                    $iDO++;
                                                }
                                            }

                                            /* *************************************************************************
                                             * Get arr Dara valid import
                                             * ************************************************************************/
                                            $arrGoalDataValid = array();
                                            $iGDV = 0;

                                            for ($row = $startRow; $row <= $highestRow; $row++) {
                                                #Read a row of data into an array
                                                $dataSheet = $sheet->rangeToArray('A' . $row . ':' . ($currentColumn) . $row, NULL, TRUE, FALSE);

                                                $code           = trim($dataSheet[0][1]);
                                                $importantLevel = trim($dataSheet[0][$i]);

                                                $importantLevel = (isset($importantLevel) && is_numeric((int)$importantLevel)) ? (int)$importantLevel : 0;

                                                $insideExist = 0;
                                                $vParentId = -1;

                                                foreach($arrGoalValid as $goalValid){
                                                    if($goalValid['goalCode'] == $code){
                                                        $insideExist = 1;
                                                        $vParentId = $goalValid['parentId'];
                                                        break;
                                                    }
                                                }

                                                if($insideExist == 1 && $importantLevel != 0){
                                                    if(count($arrGoalDataValid) == 0){
                                                        $arrGoalDataValid[$iGDV]['goalId']         = $goalValid['goalId'];
                                                        $arrGoalDataValid[$iGDV]['goalCode']       = $code;
                                                        $arrGoalDataValid[$iGDV]['importantLevel'] = $importantLevel;
                                                        $arrGoalDataValid[$iGDV]['parentId']       = $vParentId;

                                                        $iGDV++;
                                                    } else {
                                                        $eDGV = 0;
                                                        foreach($arrGoalDataValid as $dgValid){
                                                            if($dgValid['goalId'] == $goalValid['goalId']){
                                                                $eDGV = 1;
                                                                break;
                                                            }
                                                        }
                                                        if($eDGV == 0){
                                                            $arrGoalDataValid[$iGDV]['goalId']         = $goalValid['goalId'];
                                                            $arrGoalDataValid[$iGDV]['goalCode']       = $code;
                                                            $arrGoalDataValid[$iGDV]['importantLevel'] = $importantLevel;
                                                            $arrGoalDataValid[$iGDV]['parentId']       = $vParentId;

                                                            $iGDV++;
                                                        }
                                                    }
                                                }
                                            }

                                            if(count($arrGoalDataValid) > 0){
                                                $arrEachArea[$iEA]['areaId']           = $areaId;
                                                $arrEachArea[$iEA]['areaCode']         = $compareAreaCode;
                                                $arrEachArea[$iEA]['arrGoalDataValid'] = $arrGoalDataValid;

                                                $iEA++;
                                            }
                                        }

                                    } else {
                                        if($listAreaCodeErr == ""){
                                            $listAreaCodeErr = $compareAreaCode;
                                        } else {
                                            $listAreaCodeErr .= ', '.$compareAreaCode;
                                        }
                                    }
                                } else {
                                    if($listAreaCodeNull == ""){
                                        $listAreaCodeNull = $currentColumn . '7';
                                    } else {
                                        $listAreaCodeNull .= ', '.$currentColumn . '7';
                                    }
                                }
                            }

                            if($listAreaCodeErr != ''){
                                $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                $arrDataError[$iDE]['content']    = 'Danh sách mã Tổ/Quận/Huyện lỗi: '.$listAreaCodeErr.'.';
                                $iDE++;
                            }

                            if($listAreaCodeNull != ''){
                                $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                $arrDataError[$iDE]['content']    = 'Mã Tổ/Quận/Huyện rỗng tại: '.$listAreaCodeNull.'.';
                                $iDE++;
                            }

                            if(count($arrEachArea) > 0){
                                $arrDataValid[$iDV]['sheetIndex']  = $arrSheet;
                                $arrDataValid[$iDV]['companyId']   = $companyId;
                                $arrDataValid[$iDV]['companyCode'] = $companyCode;
                                $arrDataValid[$iDV]['year']        = $year;
                                $arrDataValid[$iDV]['month']       = $month;
                                $arrDataValid[$iDV]['arrEachArea'] = $arrEachArea;
                                $arrDataValid[$iDV]['objILCDB']    = $objILCDB;

                                $iDV++;
                            }
                        }
                    } else {
                        Session::flash('message-errors', "Vui lòng import Tỷ trọng Phòng/Đài/MBF HCM <b>".$companyCode."</b> áp dụng năm <b>".$year."</b> trước khi import Tỷ trọng cho Tổ/Quận/Huyện.");
                        $this->clearSession();
                        Session::flash('type', 10);
                        return redirect('importGoal');
                    }

                    /**************************************************************************************************/
                } else {
                    if($companyId == -1){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_COMP_CODE_APPLY').$companyCode;
                        $iDE++;
                    }
                    if($isLocked == 1){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = 'Dữ liệu áp dụng tháng: '.$dir.' đang khóa';
                        $iDE++;
                    }
                    if($validTitle == 0){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_IMPORT_FILE_INVALID');
                        $iDE++;
                    }
                }

            } else {
                if($year == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_YEAR_APPLY');
                    $iDE++;
                }
                if($month == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_MONTH_APPLY');
                    $iDE++;
                }
                if($title == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_TITLE_FILE');
                    $iDE++;
                }
                if($companyCode == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_COMP_APPLY');
                    $iDE++;
                }
            }
        }

        $strOverride = "";
        if(count($arrDataOverride) > 0){
            foreach($arrSheets as $indexSheet){
                $strEachOR = "";
                foreach($arrDataOverride as $dataOverride){
                    if($dataOverride['sheetIndex'] == $indexSheet){
                        $orCompanyCode = $dataOverride['companyCode'];
                        if($strEachOR == ""){
                            $strEachOR = '<br/>&nbsp;&nbsp;&nbsp; <b>- '
                                .$dataOverride['areaCode']
                                .' </b>'.$this->config->get('constant.WARN_OVERRIDE_PRI').' <b>'.$dataOverride['month'].'/'.$dataOverride['year'].'</b>.';
                        } else {
                            $strEachOR .= '<br/>&nbsp;&nbsp;&nbsp; <b>- '
                                .$dataOverride['areaCode']
                                .' </b>'.$this->config->get('constant.WARN_OVERRIDE_PRI').' <b>'.$dataOverride['month'].'/'.$dataOverride['year'].'</b>.';
                        }
                    }
                }
                if($strEachOR != ""){
                    if($strOverride == ""){
                        $strOverride = '<b>'.commonUtils::TITLE_IMPORT_AREA_GOAL.'<hr/> * '.$sheetNames[$indexSheet].' ➤ '.$orCompanyCode.':</b>'.$strEachOR;
                    } else {
                        $strOverride .= '<br/><b> * '.$sheetNames[$indexSheet].' ➤ '.$orCompanyCode.':</b>'.$strEachOR;
                    }
                }
            }
        }

        if($strOverride != ""){
            $strOverride .= "<br/><br/><b>*** Chú ý:</b> Khi ghi đè tỷ trọng Tổ/Quận/Huyện thì các dữ liệu liên quan: Kế hoạch/ Thực hiện Tổ/Quận/Huyện, Tỷ trọng/Kế hoạch/Thực hiện Chức danh, Nhân viên"
                ." thuộc Tổ/Quận/Huyện vào các tháng/năm ghi đè sẽ bị xóa!"
            ;
        }

        $data['arrSheet']        = $arrSheets;
        $data['arrDataValid']    = $arrDataValid;
        $data['arrDataOverride'] = $arrDataOverride;
        $data['arrDataError']    = $arrDataError;
        $data['curExcelFile']    = $rename;
        $data['pathFile']        = $path;
        $data['ilcApplyDate']    = $ilcApplyDate;

        /**************************************************************************************************************/
        #Write session for action next
        Session::flash('type', 10);
        Session::put('data', $data);
        Session::put('curType', 10);
        Session::put('chooseImport', 1);
        Session::put('strIssetDataShow', $strOverride);
        return redirect('importGoal');

    }

    public function importMultiPriorityArea(){
        DB::beginTransaction();
        try{
            /**********************************************************************************************************/
            $data       = Session::get('data');
            $actionUser = Session::get('sid');

            $arrSheets       = $data['arrSheet'];
            $arrDataValid    = $data['arrDataValid'];
            $arrDataOverride = $data['arrDataOverride'];
            $arrDataError    = $data['arrDataError'];
            $curExcelFile    = $data['curExcelFile'];
            $path            = $data['pathFile'];
            $ilcApplyDate    = $data['ilcApplyDate'];

            /**********************************************************************************************************/
            $dir = date('Y').'/'.date('m');
            $inputFileName = $path;

            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            $sheetNames = $objPHPExcel->getSheetNames();

            /**********************************************************************************************************/
            if(count($arrDataOverride) > 0){
                foreach($arrDataOverride as $dataOverride){
                    $this->deleteILA($dataOverride['companyId'], $dataOverride['areaId'], $dataOverride['year'], $dataOverride['month'], $dataOverride['month'] );
                    $this->deleteTA($dataOverride['companyId'], $dataOverride['areaId'], $dataOverride['year'], $dataOverride['month'], $dataOverride['month']);
                    $this->deleteILP($dataOverride['companyId'], $dataOverride['areaId'], 0, $dataOverride['year'], $dataOverride['month'], $dataOverride['month']);
                    $this->deleteTP($dataOverride['companyId'], $dataOverride['areaId'], 0, $dataOverride['year'], $dataOverride['month'], $dataOverride['month']);
                    $this->deleteTE($dataOverride['companyId'], $dataOverride['areaId'], 0, $dataOverride['year'], $dataOverride['month'], $dataOverride['month'], 0);

                }

                $this->formatIPOCompany($dataOverride['companyId'], $ilcApplyDate);
                $this->calKpi4Company($dataOverride['companyId'], $dataOverride['year'], 1, 12, $ilcApplyDate, $actionUser);
                $corApplyDate = $this->getApplyDate4Corporation($dataOverride['year']);
                if($corApplyDate == ""){
                    Session::flash('message-errors', '<b>Import Tỷ trọng Tổ/Quận/Huyện</b><hr>'.'Vui lòng import tỷ trọng cho Công ty Mobifone năm <b> '.$dataOverride['year'].'</b> trước khi import tỷ trọng cho Phòng/Đài/MBF HCM.');
                    $this->clearSession();
                    Session::flash('type', 10);
                    return redirect('importGoal');
                }

                $this->formatIPOCorporation($corApplyDate);
                $this->calKpi4Corporation($corApplyDate, $ilcApplyDate, $actionUser);

                #Write log override here

                $dataLog = array();
                $dataLog['functionName'] = 'Tỷ trọng cho Tổ/Quận/Huyện (importMultiPriorityArea)';
                $dataLog['action']       = commonUtils::ACTION_OVERRIDE;
                $dataLog['url']          = 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile;
                $dataLog['newValue']     = $curExcelFile;
                $dataLog['createdUser']  = $actionUser;

                $this->writeLog($dataLog);

            }
            /**********************************************************************************************************/

            $arrSuccess = array();
            $iS = 0;

            $numSheetSuccess = 0;

            foreach($arrDataValid as $dataValid){
                $sheetIndex  = $dataValid['sheetIndex'];
                $companyId   = $dataValid['companyId'];
                $companyCode = $dataValid['companyCode'];
                $year        = $dataValid['year'];
                $month       = $dataValid['month'];
                $arrEachArea = $dataValid['arrEachArea'];

                $arrDataInsert = array();
                $arrInsideSuccess = array();
                $iIS = 0;

                $numSheetSuccess = $numSheetSuccess + 1;

                foreach($arrEachArea as $ILAData){
                    $areaId           = $ILAData['areaId'];
                    $areaCode         = $ILAData['areaCode'];
                    $arrGoalDataValid = $ILAData['arrGoalDataValid'];

                    $totalILParent = 0;

                    $arrToTalChild = array();
                    $iTTC = 0;

                    foreach($arrGoalDataValid as $gValid){
                        if($gValid['parentId'] == 0){
                            $totalILParent += $gValid['importantLevel'];

                            $totalILChild  = 0;
                            foreach($arrGoalDataValid as $gChild){
                                if($gChild['parentId'] == $gValid['goalId']){
                                    $totalILChild += $gChild['importantLevel'];
                                }
                            }
                            $arrToTalChild[$iTTC]['parentId']     = $gValid['goalId'];
                            $arrToTalChild[$iTTC]['totalILChild'] = $totalILChild;
                            $iTTC++;
                        }
                    }
                    $numSuccess = 0;
                    $listGoal = "";

                    foreach($arrGoalDataValid as $igValid){
                        $igGoalId         = $igValid['goalId'];
                        $igGoalCode       = $igValid['goalCode'];
                        $igImportantLevel = $igValid['importantLevel'];
                        $igParentId       = $igValid['parentId'];

                        if($igValid['parentId'] == 0){
                            $benchmark = ($totalILParent != 0) ? (100 / $totalILParent / 12) * $igImportantLevel : 0;

                            #Ceate array data for insert
                            $importantLevelArea = array(
                                'company_id'      => $companyId,
                                'area_id'         => $areaId,
                                'goal_id'         => $igGoalId,
                                'month'           => $month,
                                'year'            => $year,
                                'benchmark'       => $benchmark,
                                'goal_level'      => 0,
                                'important_level' => $igImportantLevel
                            );

                            $numSuccess++;
                            $arrDataInsert [] = $importantLevelArea;

                            if($listGoal == ""){
                                $listGoal = $igGoalCode;
                            } else {
                                $listGoal .= ', '.$igGoalCode;
                            }

                            /* *****************************************************************************************
                             * Import for child
                             * ****************************************************************************************/
                            $icTotalChild = 0;
                            foreach($arrToTalChild as $totalChild){
                                if($totalChild['parentId'] == $igGoalId){
                                    $icTotalChild = $totalChild['totalILChild'];
                                    break;
                                }
                            }
                            if($icTotalChild != 0){
                                foreach($arrGoalDataValid as $icChild){
                                    $icGoalId         = $icChild['goalId'];
                                    $icGoalCode       = $icChild['goalCode'];
                                    $icImportantLevel = $icChild['importantLevel'];
                                    $icParentId       = $icChild['parentId'];

                                    if($icParentId  == $igGoalId){
                                        $childBenchmark = ($benchmark / $icTotalChild) * $icImportantLevel;

                                        $cImportantLevelArea = array(
                                            'company_id'      => $companyId,
                                            'area_id'         => $areaId,
                                            'goal_id'         => $icGoalId,
                                            'month'           => $month,
                                            'year'            => $year,
                                            'benchmark'       => $childBenchmark,
                                            'goal_level'      => 0,
                                            'important_level' => $icImportantLevel
                                        );
                                        $numSuccess++;
                                        $arrDataInsert [] = $cImportantLevelArea;
                                        $listGoal .= ', '.$icGoalCode;

                                    }
                                }
                            }
                        }
                    }

                    if($numSuccess > 0){
                        $arrInsideSuccess[$iIS]['areaCode'] = $areaCode;
                        $arrInsideSuccess[$iIS]['numRow']   = $numSuccess;
                        $arrInsideSuccess[$iIS]['listGoal'] = $listGoal;
                        $iIS++;
                    }
                }

                /* *****************************************************************************************************
                * Write to database.
                * *****************************************************************************************************/
                DB::table('important_level_area')->insert($arrDataInsert);

                if(count($arrInsideSuccess) > 0){
                    /******************************************************************************************************/
                    $arrSuccess[$iS]['sheetIndex']       = $sheetIndex;
                    $arrSuccess[$iS]['companyCode']      = $companyCode;
                    $arrSuccess[$iS]['year']             = $year;
                    $arrSuccess[$iS]['month']            = $month;
                    $arrSuccess[$iS]['arrInsideSuccess'] = $arrInsideSuccess;
                    $iS++;
                    /******************************************************************************************************/
                }
            }
            /* ********************************************************************************************************/
            $strError = "";
            if(count($arrDataError) > 0){
                foreach($arrSheets as $indexSheet){
                    $strInsideErr = "";
                    foreach($arrDataError as $error){
                        if($error['sheetIndex'] == $indexSheet){
                            if($strInsideErr == ""){
                                $strInsideErr = '<br/>&nbsp;&nbsp;&nbsp;- '.$error['content'];
                            } else {
                                $strInsideErr .= '<br/>&nbsp;&nbsp;&nbsp;- '.$error['content'];
                            }
                        }
                    }
                    if($strInsideErr != ""){
                        if($strError == ""){
                            $strError = '<b>'.commonUtils::TITLE_IMPORT_AREA_GOAL.'<hr>* '.$sheetNames[$indexSheet].':</b>'.$strInsideErr;
                        } else {
                            $strError .= '<br/><b>* '.$sheetNames[$indexSheet].':</b>'.$strInsideErr;
                        }
                    }
                }
            }
            if($strError != ""){
                Session::flash('message-errors', $strError);
            }
            /* ********************************************************************************************************/
            $strSuccess = '';
            if(count($arrSuccess) > 0){
                foreach($arrSuccess as $success){
                    $dirS = $success['month'].'/'.$success['year'];
                    $strES = "";
                    foreach($success['arrInsideSuccess'] as $insideSuccess){
                        if($strES == ""){
                            $strES = "&nbsp;&nbsp;&nbsp; <b>- "
                                .$insideSuccess['areaCode']
                                .'</b> đã import thành công <b>'
                                .$insideSuccess['numRow']
                                .'</b> dòng. <br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; + Danh sách mục tiêu đã import: '.$insideSuccess['listGoal'].'.';
                        } else {
                            $strES .= "<br/>&nbsp;&nbsp;&nbsp; <b>- "
                                .$insideSuccess['areaCode']
                                .'</b> đã import thành công <b>'
                                .$insideSuccess['numRow']
                                .'</b> dòng. <br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; + Danh sách mục tiêu đã import: '.$insideSuccess['listGoal'].'.';
                        }
                    }
                    if($strES != ""){
                        if($strSuccess == ""){
                            $strSuccess = '<b>'.commonUtils::TITLE_IMPORT_AREA_GOAL.'<br/>*</b> Số sheet import: '.count($arrSheets).'<br/>* Số sheet đã import thành công: '.$numSheetSuccess
                                .'<hr/><b>* '.$sheetNames[$success['sheetIndex']].' > '.$success['companyCode'].' > '.$dirS.'</b><br/>'.$strES;
                        } else {
                            $strSuccess .= '<b><br/><br/>* '.$sheetNames[$success['sheetIndex']].' > '.$success['companyCode'].' > '.$dirS.'</b><br/>'.$strES;
                        }
                    }
                }
            }
            if($strSuccess != ""){
                Session::flash('message-success', $strSuccess);
            }

            /* *********************************************************************************************************
             * Calculate cal_benchmark(Điểm chuẩn KPI)
             * ********************************************************************************************************/
            foreach($arrDataValid as $dataValid) {
                $sheetIndex  = $dataValid['sheetIndex'];
                $companyId   = $dataValid['companyId'];
                $companyCode = $dataValid['companyCode'];
                $year        = $dataValid['year'];
                $month       = $dataValid['month'];
                $arrEachArea = $dataValid['arrEachArea'];
                $objILCDB    = $dataValid['objILCDB'];

                $sqlILA = "
                        SELECT *
                        FROM important_level_area
                        WHERE inactive = 0
                        AND company_id = ".$companyId."
                        AND year = ".$year."
                        AND month = ".$month."
                    ";
                $objILADB = DB::select(DB::raw($sqlILA));

                $arrDistinctGoalId = array();

                if(count($objILADB) > 0){
                    foreach($objILADB as $ila){
                        if(!in_array( $ila->goal_id, $arrDistinctGoalId)){
                            $arrDistinctGoalId[] = $ila->goal_id;
                        }
                    }
                }

                $arrPreIL = array();
                $iP = 0;

                if(count($arrDistinctGoalId) > 0 && count($objILCDB) > 0){
                    foreach($arrDistinctGoalId as $dGoalId){
                        $preTotalIL = 0;
                        foreach($objILADB as $pILA){
                            if($pILA->goal_id == $dGoalId){
                                $preTotalIL += $pILA->important_level;
                            }
                        }

                        $gCompanyBenchmark = 0;
                        foreach($objILCDB as $ilc){
                            if($ilc->goal_id == $dGoalId){
                                $gCompanyBenchmark = $ilc->benchmark;
                                break;
                            }
                        }
                        $arrPreIL[$iP]['goalId']     = $dGoalId;
                        $arrPreIL[$iP]['companyBM']  = $gCompanyBenchmark;
                        $arrPreIL[$iP]['preTotalIL'] = $preTotalIL;
                        $iP++;
                    }
                }

                foreach($arrEachArea as $eAreaIL){
                    $eAreaId           = $eAreaIL['areaId'];
                    $eAreaCode         = $eAreaIL['areaCode'];
                    $eArrGoalDataValid = $eAreaIL['arrGoalDataValid'];

                    foreach($objILADB as $uILA){
                        if($uILA->area_id == $eAreaId){
                            $uImportantLevel = $uILA->important_level;

                            $uCompanyBenchmark = 0;
                            $uPreTotalIL       = 0;

                            foreach($arrPreIL as $preIL){
                                if($preIL['goalId'] == $uILA->goal_id){
                                    $uCompanyBenchmark  = $preIL['companyBM'];
                                    $uPreTotalIL        = $preIL['preTotalIL'];
                                    break;
                                }
                            }

                            if($uCompanyBenchmark != 0 && $uPreTotalIL!= 0){
                                $uCalBenchmark = ($uCompanyBenchmark / $uPreTotalIL) * $uImportantLevel;
                                $uILACalbechmark = array(
                                    'cal_benchmark'  => $uCalBenchmark,
                                    'created_user' 	 => $actionUser,
                                    'created_date'   => date("Y-m-d h:i:sa")
                                );
                                DB::table('important_level_area')
                                    ->where('company_id', $companyId)
                                    ->where('area_id', $eAreaId)
                                    ->where('goal_id', $uILA->goal_id)
                                    ->update($uILACalbechmark);
                            }
                        }
                    }
                }
            }
            /**********************************************************************************************************/
            $dataLog = array();
            $dataLog['functionName'] = 'Tỷ trọng cho Tổ/Quận/Huyện (importMultiPriorityArea)';
            $dataLog['action']       = commonUtils::ACTION_IMPORT;
            $dataLog['url']          = 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile;
            $dataLog['newValue']     = $curExcelFile;
            $dataLog['createdUser']  = $actionUser;

            $this->writeLog($dataLog);
            /**********************************************************************************************************/
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }

        $this->clearSession();
        Session::flash('type', 10);
        return redirect('importGoal');
    }

    public function beforeImportMultiPriorityPosition($path, $startRow, $typeImport, $rename, $listSheetIndex, $arrDataSession){
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        if($sAccessLevel != 1){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 3);
            return redirect('importGoal');
        }

        $inputFileName = $path;
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheetNames = $objPHPExcel->getSheetNames();
        if($listSheetIndex == ""){
            #Call back error when array index sheet is null
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_NULL'));
            $this->clearSession();
            Session::flash('type', 3);
            return redirect('importGoal');
        }

        $arrSheets = commonUtils::getArraySheets($listSheetIndex);

        if(count($arrSheets) == 0){
            #Call back error when array index sheet is not valid
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_INVALID'));
            $this->clearSession();
            Session::flash('type', 3);
            return redirect('importGoal');
        } else {
            $numOfSheet =  $objPHPExcel->getSheetCount();
            foreach($arrSheets as $checkExist){
                if($checkExist >= $numOfSheet){
                    Session::flash('message-errors', $this->config->get('constant.ERR_OVER_NUMBER_SHEET'));
                    $this->clearSession();
                    Session::flash('type', 3);
                    return redirect('importGoal');
                }
            }
        }
        /* *************************************************************************************************************
         * get all data before loop
         * ************************************************************************************************************/

        $objLockDB = DB::table('lock')->where('inactive', 0)->get();

        #object company
        $objCompanyDB = DB::table('company')->where('inactive', 0)->get();

        #object area
        $objAreaDB = DB::table('area')->where('inactive', 0)->get();

        #object position
        $objPositionDB = DB::table('position')->where('inactive', 0)->get();

        #object goal
        $objGoalDB = DB::table('goal')->where('inactive', 0)->get();

        if(count($objCompanyDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_COMPANY_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 3);
            return redirect('importGoal');
        }

        if(count($objAreaDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_AREA_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 3);
            return redirect('importGoal');
        }

        if(count($objPositionDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_POSITION_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 3);
            return redirect('importGoal');
        }

        if(count($objGoalDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_GOAL_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 3);
            return redirect('importGoal');
        }

        /* *************************************************************************************************************
         * Begin loop array sheets
         * Get array data valid for import priority
         * ************************************************************************************************************/

        $arrDataError = array();
        $iDE = 0;

        $arrDataValid = array();
        $iDV = 0;

        $arrDataOverride = array();
        $iDO = 0;

        $preCompanyCode = "";
        $preAreaCode = "";
        $preYear = "";
        foreach ($arrSheets as $arrSheet) {
            $sheet              = $objPHPExcel->getSheet($arrSheet);
            $highestRow         = $sheet->getHighestRow();
            $highestColumn      = $sheet->getHighestColumn();

            $yearApply          = trim($sheet->rangeToArray('E' . 4)[0][0]);
            $fromMonthApply     = trim($sheet->rangeToArray('E' . 5)[0][0]);
            $toMonthApply       = trim($sheet->rangeToArray('E' . 6)[0][0]);
            $companyCodeApply   = trim($sheet->rangeToArray('B' . 6)[0][0]);
            $areaCodeApply      = trim($sheet->rangeToArray('B' . 7)[0][0]);
            $titleExcel         = trim($sheet->rangeToArray('C' . 2)[0][0]);

            $year               = (isset($yearApply) && is_numeric((int)$yearApply) && (int)$yearApply >= 2015) ? (int)$yearApply : '';
            $fromMonth          = (isset($fromMonthApply) && is_numeric((int)$fromMonthApply) && (int)$fromMonthApply >= 1 && (int)$fromMonthApply <= 12) ? (int)$fromMonthApply : '';
            $toMonth            = (isset($toMonthApply) && is_numeric((int)$toMonthApply) && (int)$toMonthApply >= 1 && (int)$toMonthApply <= 12 && (int)$toMonthApply > $fromMonth) ? (int)$toMonthApply : $fromMonth;
            $companyCode        = (isset($companyCodeApply) && $companyCodeApply != null) ? $companyCodeApply : '';
            $areaCode           = (isset($areaCodeApply) && $areaCodeApply != null) ? $areaCodeApply : '';
            $title              = (isset($titleExcel) && $titleExcel != null) ? $titleExcel : '';

            if($title != "" && $companyCode != "" && $areaCode != "" && $year != "" && $fromMonth != ""){
                if($preCompanyCode == "" || strtolower(trim($companyCode)) == strtolower(trim($preCompanyCode))){
                    $preCompanyCode = $companyCode;
                } else {
                    Session::flash('message-errors', "Mỗi file excel import chỉ duy nhất mỗi Phòng/Đài/MBF HCM.");
                    $this->clearSession();
                    Session::flash('type', 3);
                    return redirect('importGoal');
                }

                if($preAreaCode == "" || strtolower(trim($areaCode)) != strtolower(trim($preAreaCode))){
                    $preAreaCode = $areaCode;
                } else {
                    Session::flash('message-errors', "Các sheet trên file import không được trùng Mã Tổ/Quận/Huyện.");
                    $this->clearSession();
                    Session::flash('type', 3);
                    return redirect('importGoal');
                }

                if($preYear == "" || $preYear == $year){
                    $preYear = $year;
                } else {
                    Session::flash('message-errors', "Năm áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 3);
                    return redirect('importGoal');
                }

                /* *****************************************************************************************************
                 * check type import is valid
                 * ****************************************************************************************************/
                $isValidTitle = 0;
                if(strtolower(trim($title)) == strtolower(commonUtils::TITLE_IMPORT_POSITION_GOAL)){
                    $isValidTitle = 1;
                }
                /* *****************************************************************************************************
                 * check companyCode is valid
                 * If exist companyCode return companyId else return -1
                 * ****************************************************************************************************/
                $companyId = -1;
                foreach($objCompanyDB as $company){
                    if($company->company_code == $companyCode){
                        $companyId = $company->id;
                        break;
                    }
                }

                /* *****************************************************************************************************
                 * check areaCode is valid
                 * If exist areaCode return areaId else return -1
                 * ****************************************************************************************************/
                $areaId = -1;
                foreach($objAreaDB as $area){
                    if($area->area_code == $areaCode && $area->company_id == $companyId && $companyId != -1){
                        $areaId = $area->id;
                        break;
                    }
                }

                if($companyId != -1 && $areaId  != -1 && $isValidTitle != 0
                ){
                    /* *************************************************************************************************
                     * get object important level area before import important level position os this area
                     * ************************************************************************************************/
                    /*$sqlILA = "
                        SELECT ila.*, g.goal_code, g.parent_id
                        FROM important_level_area ila
                        LEFT JOIN goal g ON g.id = ila.goal_id
                        WHERE ila.inactive = 0
                        AND ila.year = ".$year."
                        AND ila.company_id = ".$companyId."
                        AND ila.area_id = ".$areaId."
                    ";
                    $objILADB = DB::select(DB::raw($sqlILA));*/
                    $objILADB = $this->getImportantLevelArea($companyId, $areaId, $year, 0);
                    $arrUnlock = array();
                    $iUL = 0;

                    $arrHILA = array();/** array have important level area */
                    $iHI = 0;

                    $listMonthLock = "";
                    $listMonthNILA = "";/** list month not important level area */

                    $arrM1 = array();
                    $arrM2 = array();
                    $arrM3 = array();
                    $arrM4 = array();
                    $arrM5 = array();
                    $arrM6 = array();
                    $arrM7 = array();
                    $arrM8 = array();
                    $arrM9 = array();
                    $arrM10 = array();
                    $arrM11 = array();
                    $arrM12 = array();

                    $iA1 = 0;
                    $iA2 = 0;
                    $iA3 = 0;
                    $iA4 = 0;
                    $iA5 = 0;
                    $iA6 = 0;
                    $iA7 = 0;
                    $iA8 = 0;
                    $iA9 = 0;
                    $iA10 = 0;
                    $iA11 = 0;
                    $iA12 = 0;

                    for($m = $fromMonth; $m <= $toMonth; $m++){
                        /***********************************************************************************************
                         * Check locked
                         **********************************************************************************************/
                        $isLock = $this->checkLockData($year, $m, '', 2, $companyId);
                        /*foreach($objLockDB as $lock){
                            if(
                                $lock->ofmonth == $m
                                && $lock->ofyear == $year
                                && $lock->lock == 1
                            ){
                                $isLock = 1;
                                break;
                            }
                        }*/
                        if($isLock == 1){
                            if($listMonthLock == ""){
                                $listMonthLock = $m;
                            } else {
                                $listMonthLock .= ', '.$m;
                            }

                        } else {

                            $arrUnlock[$iUL]['arrSheet']= $arrSheet;
                            $arrUnlock[$iUL]['month']= $m;
                            $arrUnlock[$iUL]['year']= $year;
                            $iUL++;
                        }

                        /***********************************************************************************************
                         * Put data to 12 array ila for 12 month
                         **********************************************************************************************/
                        switch ($m) {
                            case 1:
                                foreach($objILADB as $ila){
                                    if($ila->month == 1 && $ila->important_level != 0){
                                        $arrM1[$iA1]['goalId']    = $ila->goal_id;
                                        $arrM1[$iA1]['goalCode']  = $ila->goal_code;
                                        $arrM1[$iA1]['benchmark'] = $ila->benchmark;
                                        $arrM1[$iA1]['parentId']  = $ila->parent_id;
                                        $arrM1[$iA1]['isParent']  = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA1++;
                                    }
                                }
                                break;
                            case 2:
                                foreach($objILADB as $ila){
                                    if($ila->month == 2 && $ila->important_level != 0){
                                        $arrM2[$iA2]['goalId']   = $ila->goal_id;
                                        $arrM2[$iA2]['goalCode'] = $ila->goal_code;
                                        $arrM2[$iA2]['benchmark'] = $ila->benchmark;
                                        $arrM2[$iA2]['parentId'] = $ila->parent_id;
                                        $arrM2[$iA2]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA2++;
                                    }
                                }
                                break;
                            case 3:
                                foreach($objILADB as $ila){
                                    if($ila->month == 3 && $ila->important_level != 0){
                                        $arrM3[$iA3]['goalId']   = $ila->goal_id;
                                        $arrM3[$iA3]['goalCode'] = $ila->goal_code;
                                        $arrM3[$iA3]['benchmark'] = $ila->benchmark;
                                        $arrM3[$iA3]['parentId'] = $ila->parent_id;
                                        $arrM3[$iA3]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA3++;
                                    }
                                }
                                break;
                            case 4:
                                foreach($objILADB as $ila){
                                    if($ila->month == 4 && $ila->important_level != 0){
                                        $arrM4[$iA4]['goalId']   = $ila->goal_id;
                                        $arrM4[$iA4]['goalCode'] = $ila->goal_code;
                                        $arrM4[$iA4]['benchmark'] = $ila->benchmark;
                                        $arrM4[$iA4]['parentId'] = $ila->parent_id;
                                        $arrM4[$iA4]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA4++;
                                    }
                                }
                                break;
                            case 5:
                                foreach($objILADB as $ila){
                                    if($ila->month == 5 && $ila->important_level != 0){
                                        $arrM5[$iA5]['goalId']   = $ila->goal_id;
                                        $arrM5[$iA5]['goalCode'] = $ila->goal_code;
                                        $arrM5[$iA5]['benchmark'] = $ila->benchmark;
                                        $arrM5[$iA5]['parentId'] = $ila->parent_id;
                                        $arrM5[$iA5]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA5++;
                                    }
                                }
                                break;
                            case 6:
                                foreach($objILADB as $ila){
                                    if($ila->month == 6 && $ila->important_level != 0){
                                        $arrM6[$iA6]['goalId']   = $ila->goal_id;
                                        $arrM6[$iA6]['goalCode'] = $ila->goal_code;
                                        $arrM6[$iA6]['benchmark'] = $ila->benchmark;
                                        $arrM6[$iA6]['parentId'] = $ila->parent_id;
                                        $arrM6[$iA6]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA6++;
                                    }
                                }
                                break;
                            case 7:
                                foreach($objILADB as $ila){
                                    if($ila->month == 7 && $ila->important_level != 0){
                                        $arrM7[$iA7]['goalId']   = $ila->goal_id;
                                        $arrM7[$iA7]['goalCode'] = $ila->goal_code;
                                        $arrM7[$iA7]['benchmark'] = $ila->benchmark;
                                        $arrM7[$iA7]['parentId'] = $ila->parent_id;
                                        $arrM7[$iA7]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA7++;
                                    }
                                }
                                break;
                            case 8:
                                foreach($objILADB as $ila){
                                    if($ila->month == 8 && $ila->important_level != 0){
                                        $arrM8[$iA8]['goalId']   = $ila->goal_id;
                                        $arrM8[$iA8]['goalCode'] = $ila->goal_code;
                                        $arrM8[$iA8]['benchmark'] = $ila->benchmark;
                                        $arrM8[$iA8]['parentId'] = $ila->parent_id;
                                        $arrM8[$iA8]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA8++;
                                    }
                                }
                                break;
                            case 9:
                                foreach($objILADB as $ila){
                                    if($ila->month == 9 && $ila->important_level != 0){
                                        $arrM9[$iA9]['goalId']   = $ila->goal_id;
                                        $arrM9[$iA9]['goalCode'] = $ila->goal_code;
                                        $arrM9[$iA9]['benchmark'] = $ila->benchmark;
                                        $arrM9[$iA9]['parentId'] = $ila->parent_id;
                                        $arrM9[$iA9]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA9++;
                                    }
                                }
                                break;
                            case 10:
                                foreach($objILADB as $ila){
                                    if($ila->month == 10 && $ila->important_level != 0){
                                        $arrM10[$iA10]['goalId']   = $ila->goal_id;
                                        $arrM10[$iA10]['goalCode'] = $ila->goal_code;
                                        $arrM10[$iA10]['benchmark'] = $ila->benchmark;
                                        $arrM10[$iA10]['parentId'] = $ila->parent_id;
                                        $arrM10[$iA10]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA10++;
                                    }
                                }
                                break;
                            case 11:
                                foreach($objILADB as $ila){
                                    if($ila->month == 11 && $ila->important_level != 0){
                                        $arrM11[$iA11]['goalId']   = $ila->goal_id;
                                        $arrM11[$iA11]['goalCode'] = $ila->goal_code;
                                        $arrM11[$iA11]['benchmark'] = $ila->benchmark;
                                        $arrM11[$iA11]['parentId'] = $ila->parent_id;
                                        $arrM11[$iA11]['isParent'] = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA11++;
                                    }
                                }
                                break;
                            case 12:
                                foreach($objILADB as $ila){
                                    if($ila->month == 12 && $ila->important_level != 0){
                                        $arrM12[$iA12]['goalId']    = $ila->goal_id;
                                        $arrM12[$iA12]['goalCode']  = $ila->goal_code;
                                        $arrM12[$iA12]['benchmark'] = $ila->benchmark;
                                        $arrM12[$iA12]['parentId']  = $ila->parent_id;
                                        $arrM12[$iA12]['isParent']  = ($ila->parent_id != 0) ? 1 : 0;
                                        $iA12++;
                                    }
                                }
                                break;
                        }
                        /***********************************************************************************************
                         * Check have important level area
                         **********************************************************************************************/

                        $isILA = 0;
                        foreach($objILADB as $ila){
                            if($ila->month == $m){
                                $isILA = 1;
                                break;
                            }
                        }
                        if($isILA == 0){
                            if($listMonthNILA == ""){
                                $listMonthNILA = $m;
                            } else {
                                $listMonthNILA .= ', '.$m;
                            }


                        } else {

                            $arrHILA[$iHI]['arrSheet']  = $arrSheet;
                            $arrHILA[$iHI]['month']     = $m;
                            $arrHILA[$iHI]['year']      = $year;
                            $iHI++;
                        }
                    }

                    #get Index of Highest Column in current sheet
                    $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

                    $arrPositionValid = array();
                    $iPV = 0;

                    $listPosNull = "";
                    $listPosErr  = "";

                    for($c = 4; $c < $indexHighestColumn - 4; $c++) {
                        $currentColumn = PHPExcel_Cell::stringFromColumnIndex($c);
                        $comparePositionCode = trim($sheet->rangeToArray(trim($currentColumn . '8'))[0][0]);

                        if($comparePositionCode != ""){
                            $positionId = -1;
                            foreach($objPositionDB as $position){
                                if(
                                    commonUtils::compareTwoString($position->position_code, $comparePositionCode) == 1
                                    && commonUtils::compareTwoString('Tổng Kế hoạch', $comparePositionCode) != 1
                                ){
                                    $positionId = $position->id;
                                    break;
                                }
                            }

                            if($positionId != -1){
                                if(count($arrPositionValid) == 0){
                                    $arrPositionValid[$iPV]['positionId']   = $positionId;
                                    $arrPositionValid[$iPV]['positionCode'] = $comparePositionCode;
                                    $arrPositionValid[$iPV]['column']       = $currentColumn;
                                    $arrPositionValid[$iPV]['indexColumn']  = $c;
                                    $iPV++;
                                } else {
                                    $exist = 0;
                                    foreach($arrPositionValid as $positionCodeValid){
                                        if($positionCodeValid['positionId'] == $positionId){
                                            $exist = 1;
                                            break;
                                        }
                                    }
                                    if($exist == 0){
                                        $arrPositionValid[$iPV]['positionId']   = $positionId;
                                        $arrPositionValid[$iPV]['positionCode'] = $comparePositionCode;
                                        $arrPositionValid[$iPV]['column']       = $currentColumn;
                                        $arrPositionValid[$iPV]['indexColumn']  = $c;
                                        $iPV++;
                                    }
                                }
                            } else {
                                if($listPosErr == ""){
                                    $listPosErr = $currentColumn . '8';
                                } else {
                                    $listPosErr .= ', '.$currentColumn . '8';
                                }
                            }

                        } else {
                            if($listPosNull == ""){
                                $listPosNull = $currentColumn . '8';
                            } else {
                                $listPosNull .= ', '.$currentColumn . '8';
                            }
                        }

                    }

                    if($listPosNull != ""){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = "Mã chức danh rỗng tại: ".$listPosNull.'.';
                        $iDE++;
                    }
                    if($listPosErr != ""){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = "Mã chức danh lỗi tại: ".$listPosErr.'.';
                        $iDE++;
                    }

                    if(count($arrPositionValid) > 0 && count($arrUnlock) > 0 && count($arrHILA) > 0){
                        /* *********************************************************************************************
                         * get array goal valid in this sheet
                         * ********************************************************************************************/
                        $arrExcelGoal = array();
                        $iEG = 0;

                        $listGoalNull   = "";
                        $listGoalErrors = "";

                        for ($row = $startRow; $row <= $highestRow; $row++) {
                            #Read a row of data into an array
                            $dataSheet = $sheet->rangeToArray('A' . $row . ':B' . $row, NULL, TRUE, FALSE);

                            $no     = trim($dataSheet[0][0]);
                            $code   = trim($dataSheet[0][1]);

                            $no     = (isset($no) && $no != null) ? $no : '?';
                            $code   = (isset($code) && $code != null) ? $code : '';

                            if($code != ''){
                                $goalId   = -1;
                                $goalType = -1;
                                $formula  = -1;
                                $parentId = -1;

                                foreach($objGoalDB as $goal){
                                    if($goal->goal_code == $code){
                                        $goalId   = $goal->id;
                                        $goalType = $goal->goal_type;
                                        $formula  = $goal->formula;
                                        $parentId = $goal->parent_id;
                                        break;
                                    }
                                }
                                if($goalId != -1){
                                    if(count($arrExcelGoal) == 0){
                                        $arrExcelGoal[$iEG]['goalId']   = $goalId;
                                        $arrExcelGoal[$iEG]['goalCode'] = $code;
                                        $arrExcelGoal[$iEG]['goalType'] = $goalType;
                                        $arrExcelGoal[$iEG]['formula']  = $formula;
                                        $arrExcelGoal[$iEG]['parentId'] = $parentId;
                                        $iEG++;
                                    } else {
                                        $exist = 0;
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $goalId){
                                                $exist = 1;
                                                break;
                                            }
                                        }
                                        if($exist == 0){
                                            $arrExcelGoal[$iEG]['goalId']   = $goalId;
                                            $arrExcelGoal[$iEG]['goalCode'] = $code;
                                            $arrExcelGoal[$iEG]['goalType'] = $goalType;
                                            $arrExcelGoal[$iEG]['formula']  = $formula;
                                            $arrExcelGoal[$iEG]['parentId'] = $parentId;
                                            $iEG++;
                                        }
                                    }
                                } else {
                                    if($listGoalErrors == ""){
                                        $listGoalErrors = $no;
                                    } else {
                                        $listGoalErrors .= ', '.$no;
                                    }
                                }
                            } else {
                                if($listGoalNull == ""){
                                    $listGoalNull = $no;
                                } else {
                                    $listGoalNull .= ', '.$no;
                                }
                            }
                        }

                        if($listGoalNull != ""){
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = $this->config->get('constant.NULL_GOAL_CODE').$listGoalNull;
                            $iDE++;
                        }
                        if($listGoalErrors != ""){
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_GOAL_CODE').$listGoalErrors;
                            $iDE++;
                        }

                        /* *********************************************************************************************
                         * set data valid for array data import
                         * ********************************************************************************************/
                        $arrDataEachSheet = array();
                        $iDES = 0;

                        $arrORES = array(); /** Array override each sheet */
                        $iOR = 0;

                        foreach($arrPositionValid as $positionValid){
                            $pvCode          = $positionValid['positionCode'];
                            $pvId            = $positionValid['positionId'];
                            $pvColumn        = $positionValid['column'];
                            $pvIndexColumn   = $positionValid['indexColumn'];

                            $arrGM = array();/** array goal by month */
                            $iGM   = 0;

                            if(count($arrExcelGoal) > 0){

                                /* *************************************************************************************
                                 * get array data override for each sheet
                                 * ************************************************************************************/
                                $sqlILP = "
                                    SELECT ilp.*
                                    FROM important_level_position ilp
                                    WHERE ilp.inactive = 0
                                    AND ilp.year = ".$year."
                                    AND ilp.company_id = ".$companyId."
                                    AND ilp.area_id = ".$areaId."
                                    AND ilp.month >= ".$fromMonth."
                                    AND ilp.month <= ".$toMonth."
                                    GROUP BY ilp.month, ilp.position_id
                                 ";

                                $objILPDB = DB::select(DB::raw($sqlILP));

                                #$objILPDB = $this->getImportantLevelPosition($companyId, $areaId, 0, $year, $fromMonth, $toMonth);

                                /*********************************************************************************/
                                $listMonthOR = "";
                                for($im = $fromMonth; $im <= $toMonth; $im++){

                                    if(count($objILPDB) > 0){
                                        foreach($objILPDB as $ilp){
                                            if(
                                                $ilp->position_id == $pvId
                                                && $ilp->month == $im
                                            ){
                                                if($listMonthOR == ""){
                                                    $listMonthOR = $im;
                                                }else{
                                                    $listMonthOR .= ', '.$im;
                                                }
                                                break;
                                            }
                                        }
                                    }

                                    $arrEPBM = array();/** array Each Position by Month */
                                    $iEP = 0;

                                    $arrExcelGoalValid = array();
                                    $iVL = 0;

                                    $selectArray = "";

                                    switch ($im) {
                                        case 1:
                                            if(count($arrM1) > 0){
                                                foreach($arrM1 as $am1){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am1['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am1['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am1['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am1['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am1['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM1;
                                            }
                                            break;
                                        case 2:
                                            if(count($arrM2) > 0){
                                                foreach($arrM2 as $am2){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am2['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am2['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am2['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am2['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am2['isParent'];
                                                            $iVL++;
                                                        }
                                                    }

                                                }
                                                $selectArray = $arrM2;
                                            }

                                            break;
                                        case 3:
                                            if(count($arrM3) > 0){
                                                foreach($arrM3 as $am3){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am3['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am3['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am3['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am3['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am3['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM3;
                                            }
                                            break;
                                        case 4:
                                            if(count($arrM4) > 0){
                                                foreach($arrM4 as $am4){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am4['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am4['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am4['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am4['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am4['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM4;
                                            }
                                            break;
                                        case 5:
                                            if(count($arrM5) > 0){
                                                foreach($arrM5 as $am5){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am5['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am5['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am5['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am5['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am5['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM5;
                                            }
                                            break;
                                        case 6:
                                            if(count($arrM6) > 0){
                                                foreach($arrM6 as $am6){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am6['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am6['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am6['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am6['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am6['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM6;
                                            }
                                            break;
                                        case 7:
                                            if(count($arrM7) > 0){
                                                foreach($arrM7 as $am7){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am7['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am7['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am7['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am7['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am7['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM7;
                                            }
                                            break;
                                        case 8:
                                            if(count($arrM8) > 0){
                                                foreach($arrM8 as $am8){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am8['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am8['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am8['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am8['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am8['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM8;
                                            }
                                            break;
                                        case 9:
                                            if(count($arrM9) > 0){
                                                foreach($arrM9 as $am9){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am9['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am9['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am9['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am9['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am9['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM9;
                                            }
                                            break;
                                        case 10:
                                            if(count($arrM10) > 0){
                                                foreach($arrM10 as $am10){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am10['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am10['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am10['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am10['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am10['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM10;
                                            }
                                            break;

                                        case 11:
                                            if(count($arrM11) > 0){
                                                foreach($arrM11 as $am11){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am11['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am11['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am11['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am11['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am11['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM11;
                                            }
                                            break;

                                        case 12:
                                            if(count($arrM12) > 0){
                                                foreach($arrM12 as $am12){
                                                    foreach($arrExcelGoal as $excelGoal){
                                                        if($excelGoal['goalId'] == $am12['goalId']){
                                                            $arrExcelGoalValid[$iVL]['goalId']   = $am12['goalId'];
                                                            $arrExcelGoalValid[$iVL]['goalCode'] = $am12['goalCode'];
                                                            $arrExcelGoalValid[$iVL]['parentId'] = $am12['parentId'];
                                                            $arrExcelGoalValid[$iVL]['isParent'] = $am12['isParent'];
                                                            $iVL++;
                                                        }
                                                    }
                                                }
                                                $selectArray = $arrM12;
                                            }
                                            break;
                                    }

                                    if(count($arrExcelGoalValid) > 0){
                                        $listGoalNotPA = ""; /** list goal not priority for this area */
                                        for ($row = $startRow; $row <= $highestRow; $row++) {
                                            #Read a row of data into an array
                                            $dataSheet = $sheet->rangeToArray('A' . $row . ':'.$pvColumn . $row, NULL, TRUE, FALSE);

                                            $code           = trim($dataSheet[0][1]);
                                            $importantLevel = trim($dataSheet[0][$pvIndexColumn]);

                                            $code           = (isset($code) && $code != null) ? $code : '';
                                            $importantLevel = (isset($importantLevel) && $importantLevel != null) ? $importantLevel : 0;

                                            if($code != "" && $importantLevel != 0){

                                                $eGoalId    = -1;
                                                $eParentId  = -1;
                                                foreach($arrExcelGoalValid as $egv){
                                                    if($egv['goalCode'] == $code){
                                                        $eGoalId   = $egv['goalId'];
                                                        $eParentId = $egv['parentId'];
                                                        break;
                                                    }
                                                }
                                                if($eGoalId != -1){
                                                    if(count($arrEPBM) == 0){
                                                        $arrEPBM[$iEP]['goalId']            = $eGoalId;
                                                        $arrEPBM[$iEP]['goalCode']          = $code;
                                                        $arrEPBM[$iEP]['parentId']          = $eParentId;
                                                        $arrEPBM[$iEP]['isParent']          = ($eParentId != 0) ? 1 : 0;
                                                        $arrEPBM[$iEP]['importantLevel']    = $importantLevel;
                                                        $iEP++;
                                                    } else {
                                                        $exist = 0;
                                                        foreach($arrEPBM as $epbm){
                                                            if($epbm['goalId'] == $eGoalId){
                                                                $exist = 1;
                                                                break;
                                                            }
                                                        }
                                                        if($exist == 0){
                                                            $arrEPBM[$iEP]['goalId']            = $eGoalId;
                                                            $arrEPBM[$iEP]['goalCode']          = $code;
                                                            $arrEPBM[$iEP]['parentId']          = $eParentId;
                                                            $arrEPBM[$iEP]['isParent']          = ($eParentId != 0) ? 1 : 0;
                                                            $arrEPBM[$iEP]['importantLevel']    = $importantLevel;
                                                            $iEP++;
                                                        }
                                                    }

                                                } else {
                                                    if($listGoalNotPA == ""){
                                                        $listGoalNotPA = $code;
                                                    } else {
                                                        $listGoalNotPA .= ', '.$code;
                                                    }
                                                }
                                            }
                                        }
                                        if($listGoalNotPA != ""){
                                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                            $arrDataError[$iDE]['content'] = $areaCode.' không tồn tại tỷ trọng tháng '.$im.'/'.$year.' đối với các mục tiêu '.$listGoalNotPA;
                                            $iDE++;
                                        }

                                    } else {
                                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                        $arrDataError[$iDE]['content'] = $areaCode.' không tồn tại tỷ trọng tháng '.$im.'/'.$year.' đối với tất cả các mục tiêu '.$sheetNames[$arrSheet];
                                        $iDE++;
                                    }

                                    if(count($arrEPBM) > 0){
                                        $arrGM[$iGM]['month']       = $im;
                                        $arrGM[$iGM]['arrEPBM']     = $arrEPBM;
                                        $arrGM[$iGM]['selectArray'] = $selectArray;
                                        $iGM++;
                                    }
                                }

                                if($listMonthOR != ""){
                                    $arrORES[$iOR]['positionId']   = $pvId;
                                    $arrORES[$iOR]['positionCode'] = $pvCode;
                                    $arrORES[$iOR]['listMonth']    = $listMonthOR;
                                    $iOR++;
                                }
                            }

                            if(count($arrGM) > 0){
                                if(count($arrDataEachSheet) == 0){
                                    $arrDataEachSheet[$iDES]['positionId']    = $pvId;
                                    $arrDataEachSheet[$iDES]['positionCode']  = $pvCode;
                                    $arrDataEachSheet[$iDES]['arrGM']         = $arrGM;
                                    $iDES++;
                                } else {
                                    $exist = 0;
                                    foreach($arrDataEachSheet as $des){
                                        if($des['positionId'] == $pvId){
                                            $exist = 1;
                                            break;
                                        }
                                    }
                                    if($exist == 0){
                                        $arrDataEachSheet[$iDES]['positionId']    = $pvId;
                                        $arrDataEachSheet[$iDES]['positionCode']  = $pvCode;
                                        $arrDataEachSheet[$iDES]['arrGM']         = $arrGM;
                                        $iDES++;
                                    }
                                }
                            }
                        }

                        if(count($arrORES) > 0){
                            if(count($arrDataOverride) == 0){
                                $arrDataOverride[$iDO]['sheetIndex']       = $arrSheet;
                                $arrDataOverride[$iDO]['companyId']        = $companyId;
                                $arrDataOverride[$iDO]['companyCode']      = $companyCode;
                                $arrDataOverride[$iDO]['areaId']           = $areaId;
                                $arrDataOverride[$iDO]['areaCode']         = $areaCode;
                                $arrDataOverride[$iDO]['year']             = $year;
                                $arrDataOverride[$iDO]['arrORES']          = $arrORES;
                                $iDO++;
                            } else {
                                $exist = 0;
                                foreach($arrDataOverride as $dataOverride){
                                    if($dataOverride['companyId'] == $companyId
                                        && $dataOverride['areaId'] == $areaId
                                        && $dataOverride['year']   == $year){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrDataOverride[$iDO]['sheetIndex']       = $arrSheet;
                                    $arrDataOverride[$iDO]['companyId']        = $companyId;
                                    $arrDataOverride[$iDO]['companyCode']      = $companyCode;
                                    $arrDataOverride[$iDO]['areaId']           = $areaId;
                                    $arrDataOverride[$iDO]['areaCode']         = $areaCode;
                                    $arrDataOverride[$iDO]['year']             = $year;
                                    $arrDataOverride[$iDO]['arrORES']          = $arrORES;
                                    $iDO++;
                                }
                            }
                        }

                        if(count($arrDataEachSheet) > 0){
                            if(count($arrDataValid) == 0){
                                $arrDataValid[$iDV]['sheetIndex']       = $arrSheet;
                                $arrDataValid[$iDV]['companyId']        = $companyId;
                                $arrDataValid[$iDV]['companyCode']      = $companyCode;
                                $arrDataValid[$iDV]['areaId']           = $areaId;
                                $arrDataValid[$iDV]['areaCode']         = $areaCode;
                                $arrDataValid[$iDV]['year']             = $year;
                                $arrDataValid[$iDV]['arrDataEachSheet'] = $arrDataEachSheet;
                                $iDV++;
                            } else {
                                $exist = 0;
                                foreach($arrDataValid as $dataValid){
                                    if($dataValid['companyId'] == $companyId
                                        && $dataValid['areaId'] == $areaId
                                        && $dataValid['year']   == $year){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrDataValid[$iDV]['sheetIndex']       = $arrSheet;
                                    $arrDataValid[$iDV]['companyId']        = $companyId;
                                    $arrDataValid[$iDV]['companyCode']      = $companyCode;
                                    $arrDataValid[$iDV]['areaId']           = $areaId;
                                    $arrDataValid[$iDV]['areaCode']         = $areaCode;
                                    $arrDataValid[$iDV]['year']             = $year;
                                    $arrDataValid[$iDV]['arrDataEachSheet'] = $arrDataEachSheet;
                                    $iDV++;
                                }
                            }
                        }
                    }else{
                        if(count($arrUnlock) == 0){
                            $time = ($fromMonth != $toMonth) ? 'Dữ liệu áp dụng từ tháng <b>'.$fromMonth.'</b> đến tháng <b>'.$toMonth.'/'.$year.'</b> đang khóa': 'Dữ liệu áp dụng tháng <b>'.$fromMonth.'/'.$year.'</b> đang khóa.';
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = $time.' Vui lòng kiểm tra lại trước khi import!';
                            $iDE++;
                        }

                        if(count($arrPositionValid) == 0){
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = 'Toàn bộ mã chức danh lỗi.';
                            $iDE++;
                        }

                        if(count($arrHILA) == 0){
                            $time = ($fromMonth != $toMonth) ? ' áp dụng từ tháng <b>'.$fromMonth.'</b> đến tháng <b>'.$toMonth.'/'.$year.'</b> ': ' áp dụng tháng <b>'.$fromMonth.'/'.$year.'</b> ';
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = $areaCode.' chưa tồn tại tỷ trọng'.$time;
                            $iDE++;
                        }
                    }
                } else {
                    if($isValidTitle == 0){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_IMPORT_FILE_INVALID');
                        $iDE++;
                    }
                    if($companyId == -1){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_COMP_CODE_APPLY').' <b>'.$companyCode.'</b>.';
                        $iDE++;
                    }
                    if($areaId == -1){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_AREA_CODE_APPLY').' <b>'.$areaCode.'</b>.';
                        $iDE++;
                    }
                }
            } else {
                if($title == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content'] = $this->config->get('constant.NULL_TITLE_FILE');
                    $iDE++;
                }
                if($companyCode == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content'] = $this->config->get('constant.NULL_COMP_APPLY');
                    $iDE++;
                }
                if($areaCode == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content'] = $this->config->get('constant.NULL_AREA_APPLY');
                    $iDE++;
                }
                if($year == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_YEAR_APPLY');
                    $iDE++;
                }
                if($fromMonth == ""){
                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                    $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_MONTH_APPLY');
                    $iDE++;
                }
            }
        }

        /* *************************************************************************************************************
         * get string override
         * ************************************************************************************************************/
        $strOverride = "";
        if(count($arrDataOverride) > 0){
            foreach($arrDataOverride as $dataOverride){
                $strDetail = "";
                foreach($dataOverride['arrORES'] as $detailOR){
                    if($strDetail == ""){
                        $strDetail = '<br/>&nbsp;&nbsp;&nbsp;<b>- '.$detailOR['positionCode']
                            .'</b> đã tồn tại tỷ trọng tháng: <b>'.$detailOR['listMonth'].'/'.$dataOverride['year'].'</b>.';
                    }else{
                        $strDetail .= '<br/>&nbsp;&nbsp;&nbsp;<b>- '.$detailOR['positionCode']
                            .'</b> đã tồn tại tỷ trọng tháng: <b>'.$detailOR['listMonth'].'/'.$dataOverride['year'].'</b>.';
                    }
                }
                if($strDetail != ""){
                    if($strOverride == ""){
                        $strOverride = '<b>* '.$sheetNames[$dataOverride['sheetIndex']].':</b>'.$strDetail;
                    }else{
                        $strOverride .= '<br/><b>* '.$sheetNames[$dataOverride['sheetIndex']].':</b>'.$strDetail;
                    }
                }
            }
        }
        if($strOverride != ""){
            $strOverride .= "<br/><br/><b>*** Chú ý:</b> Khi ghi đè tỷ trọng Chức danh thì các dữ liệu liên quan: Kế hoạch/ Thực hiện Chức danh, Kế hoạch/Thực hiện Nhân viên"
                ." thuộc Chức danh vào các tháng/năm ghi đè sẽ bị xóa!"
            ;
        }
        /***************************************************************************************************************
         * send data to import function
         * ************************************************************************************************************/
        $data['arrDataValid']   = $arrDataValid;
        $data['arrDataOverride']= $arrDataOverride;
        $data['arrDataError']   = $arrDataError;
        $data['arrSheets']      = $arrSheets;
        $data['curExcelFile']   = $rename;
        $data['pathFile']       = $path;

        #Write session for action next
        Session::put('strIssetDataShow', $strOverride);
        Session::put('chooseImport', 1);
        Session::put('data', $data);
        Session::put('curType', $typeImport);
        Session::flash('type', 3);
        Session::save();
        return redirect('importGoal');
    }

    public function importMultiPriorityPosition(){

        DB::beginTransaction();
        try{

            /***********************************************************************************************************
             * get data from session
             * ********************************************************************************************************/
            $data               = Session::get('data');
            $actionUser         = Session::get('sid');

            $path               = $data['pathFile'];
            $curExcelFile       = $data['curExcelFile'];
            $arrSheets          = $data['arrSheets'];
            $arrDataError       = $data['arrDataError'];
            $arrDataOverride    = $data['arrDataOverride'];
            $arrDataValid       = $data['arrDataValid'];

            $dir = date('Y').'/'.date('m');
            $inputFileName = $path;

            /***********************************************************************************************************
             * Override data here
             * *********************************************************************************************************/

            if(count($arrDataOverride) > 0){
                foreach($arrDataOverride as $dOverride){
                    $doCompanyId    = $dOverride['companyId'];
                    $doAreaId       = $dOverride['areaId'];
                    $doYear         = $dOverride['year'];
                    $doArrORES      = $dOverride['arrORES'];

                    $arrMonth = array();

                    foreach($doArrORES as $ores){
                        $doPositionId   = $ores['positionId'];
                        $doListMonth    = explode(',', $ores['listMonth']);

                        foreach($doListMonth as $doMonth){

                            if(!in_array(trim($doMonth), $arrMonth)){
                                $arrMonth[] = trim($doMonth);
                            }

                            $this->deleteILP($doCompanyId, $doAreaId, $doPositionId, $doYear, trim($doMonth), trim($doMonth));
                            $this->deleteTP($doCompanyId, $doAreaId, $doPositionId, $doYear, trim($doMonth), trim($doMonth));
                            $this->deleteTE($doCompanyId, $doAreaId, $doPositionId, $doYear, trim($doMonth), trim($doMonth), 0);
                        }
                    }

                    if(count($arrMonth) > 0){
                        foreach($arrMonth as $eMonth){
                            $this->formatIPOArea($doCompanyId, $doAreaId, $doYear, $eMonth);
                            $this->calKpi4Area($doCompanyId, $doAreaId, $doYear, $eMonth, $actionUser);
                        }
                    }
                }

                $comApplyDate = $this->getApplyDate4Company($doCompanyId, $doYear, '');
                if($comApplyDate != ""){
                    $this->formatIPOCompany($doCompanyId, $comApplyDate);
                    $this->calKpi4Company($doCompanyId, $doYear, 1, 12, $comApplyDate, $actionUser);
                    $corApplyDate = $this->getApplyDate4Corporation($doYear);
                    if($corApplyDate != ""){
                        $this->formatIPOCorporation($corApplyDate);
                        $this->calKpi4Corporation($corApplyDate, $comApplyDate, $actionUser);
                    } else {
                        Session::flash('message-errors', '<b>Import Tỷ trọng Chức danh</b><hr>'.'Vui lòng import tỷ trọng cho Công ty Mobifone năm <b> '.$doYear.'</b> trước khi import tỷ trọng cho Chức danh.');
                        $this->clearSession();
                        Session::flash('type', 3);
                        return redirect('importGoal');
                    }
                } else {
                    Session::flash('message-errors', '<b>Import Tỷ trọng Chức danh</b><hr>'.'Vui lòng import tỷ trọng cho Phòng/Đài/MBF HCM năm <b> '.$doYear.'</b> trước khi import tỷ trọng cho Chức danh.');
                    $this->clearSession();
                    Session::flash('type', 3);
                    return redirect('importGoal');
                }

                /**
                 * Write log here
                 */
                $dataLog = array(
                    'functionName' => 'Tỷ trọng cho Chức danh (importMultiPriorityPosition)',
                    'action'       => commonUtils::ACTION_OVERRIDE,
                    'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'newValue'     => $curExcelFile,
                    'createdUser'  => $actionUser
                );
                $this->writeLog($dataLog);
            }

            /***********************************************************************************************************
             * begin import with data valid
             * ********************************************************************************************************/

            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
            $sheetNames = $objPHPExcel->getSheetNames();

            $arrUpdateCalBenchmark = array();
            $iUCB = 0;

            $arrSuccess = array();
            $iS = 0;

            $numSheetSuccess = 0;

            $arrInsertAll = array();
            $iIA = 0;

            if(count($arrDataValid) > 0){
                foreach($arrDataValid as $dataValid){

                    $sheetIndex       = $dataValid['sheetIndex'];
                    $companyId        = $dataValid['companyId'];
                    $companyCode      = $dataValid['companyCode'];
                    $areaId           = $dataValid['areaId'];
                    $areaCode         = $dataValid['areaCode'];
                    $year             = $dataValid['year'];
                    $arrDataEachSheet = $dataValid['arrDataEachSheet'];

                    $numSheetSuccess = $numSheetSuccess + 1;

                    $arrEachSuccess = array();
                    $iES = 0;

                    $arrDataInsert = array();

                    foreach($arrDataEachSheet as $des){
                        $positionId     = $des['positionId'];
                        $positionCode   = $des['positionCode'];
                        $arrGM          = $des['arrGM'];

                        foreach($arrGM as $gm){
                            $month          = $gm['month'];
                            $arrEPBM        = $gm['arrEPBM'];
                            $selectArray    = $gm['selectArray'];

                            /* *****************************************************************************************
                             * get array update calculator benchmark
                             * ****************************************************************************************/
                            if(count($arrUpdateCalBenchmark) == 0){
                                $arrUpdateCalBenchmark[$iUCB]['companyId']  = $companyId;
                                $arrUpdateCalBenchmark[$iUCB]['areaId']     = $areaId;
                                $arrUpdateCalBenchmark[$iUCB]['year']       = $year;
                                $arrUpdateCalBenchmark[$iUCB]['month']      = $month;
                                $arrUpdateCalBenchmark[$iUCB]['selectArray']= $selectArray;
                                $iUCB++;
                            } else {
                                $exist = 0;
                                foreach($arrUpdateCalBenchmark as $ucb){
                                    if(
                                        $ucb['companyId']       == $companyId
                                        && $ucb['areaId']       == $areaId
                                        && $ucb['year']         == $year
                                        && $ucb['month']        == $month
                                    ){
                                        $exist = 1;
                                        break;
                                    }
                                }
                                if($exist == 0){
                                    $arrUpdateCalBenchmark[$iUCB]['companyId']  = $companyId;
                                    $arrUpdateCalBenchmark[$iUCB]['areaId']     = $areaId;
                                    $arrUpdateCalBenchmark[$iUCB]['year']       = $year;
                                    $arrUpdateCalBenchmark[$iUCB]['month']      = $month;
                                    $arrUpdateCalBenchmark[$iUCB]['selectArray']= $selectArray;
                                    $iUCB++;
                                }
                            }

                            $totalParentIL   = 0;
                            foreach($arrEPBM as $bfEPBM){
                                if($bfEPBM['isParent'] == 0){
                                    $totalParentIL += $bfEPBM['importantLevel'];
                                }
                            }

                            if($totalParentIL != 0){
                                /* *************************************************************************************
                                 * import parent before
                                 * ************************************************************************************/
                                $arrParentBenchmark = array();
                                $iPB = 0;

                                $numSuccess = 0;
                                $listGoalImported = "";

                                foreach($arrEPBM as $pEPBM){
                                    $pGoalId         = $pEPBM['goalId'];
                                    $pGoalCode       = $pEPBM['goalCode'];
                                    $pParentId       = $pEPBM['parentId'];
                                    $pIsParent       = $pEPBM['isParent'];
                                    $pImportantLevel = $pEPBM['importantLevel'];

                                    if($pIsParent == 0){
                                        $pBenchmark = (100 / $totalParentIL) * $pImportantLevel;
                                        $arrParentBenchmark[$iPB]['parentId']  = $pGoalId;
                                        $arrParentBenchmark[$iPB]['benchmark'] = $pBenchmark;
                                        $iPB++;

                                        $importantLevelPosition = array(
                                            'company_id'        => $companyId,
                                            'area_id'           => $areaId,
                                            'position_id'       => $positionId,
                                            'goal_id'           => $pGoalId,
                                            'month'             => $month,
                                            'year'              => $year,
                                            'important_level'   => $pImportantLevel,
                                            'benchmark'         => $pBenchmark,
                                            'goal_level'        => 0,
                                            'created_user'      => $actionUser,
                                            'updated_user'      => 1);

                                        $arrDataInsert[] = $importantLevelPosition;
                                        $numSuccess++;

                                        if($listGoalImported == ""){
                                            $listGoalImported = $pGoalCode;
                                        } else {
                                            $listGoalImported .= ', '.$pGoalCode;
                                        }
                                    }
                                }

                                foreach($arrEPBM as $cEPBM){
                                    $cGoalId         = $cEPBM['goalId'];
                                    $cGoalCode       = $cEPBM['goalCode'];
                                    $cParentId       = $cEPBM['parentId'];
                                    $cIsParent       = $cEPBM['isParent'];
                                    $cImportantLevel = $cEPBM['importantLevel'];

                                    $parentBenchmark = 0;
                                    foreach($arrParentBenchmark as $pb){
                                        if($pb['parentId'] == $cParentId){
                                            $parentBenchmark = $pb['benchmark'];
                                            break;
                                        }
                                    }

                                    if($parentBenchmark != 0){
                                        $totalChildImportantLevel = 0;
                                        foreach($arrEPBM as $insEPBM){
                                            if($insEPBM['parentId'] == $cParentId){
                                                $totalChildImportantLevel += $insEPBM['importantLevel'];
                                            }
                                        }

                                        if($totalChildImportantLevel != 0){
                                            $cBenchmark = ($parentBenchmark / $totalChildImportantLevel) * $cImportantLevel;

                                            $importantLevelPosition = array(
                                                'company_id'        => $companyId,
                                                'area_id'           => $areaId,
                                                'position_id'       => $positionId,
                                                'goal_id'           => $cGoalId,
                                                'month'             => $month,
                                                'year'              => $year,
                                                'important_level'   => $cImportantLevel,
                                                'benchmark'         => $cBenchmark,
                                                'goal_level'        => 1,
                                                'created_user'      => $actionUser,
                                                'updated_user'      => 1);

                                            $arrDataInsert[] = $importantLevelPosition;
                                            $numSuccess++;
                                            $listGoalImported .= ', '.$cGoalCode;
                                        }
                                    }
                                }

                                if($numSuccess > 0){
                                    $arrEachSuccess[$iES]['positionCode']   = $positionCode;
                                    $arrEachSuccess[$iES]['month']          = $month;
                                    $arrEachSuccess[$iES]['numRow']         = $numSuccess;
                                    $arrEachSuccess[$iES]['listGoal']       = $listGoalImported;
                                    $iES++;
                                }
                            }
                        }
                    }

                    if(count($arrDataInsert) > 0){
                        DB::table('important_level_position')->insert($arrDataInsert);
                    }

                    if(count($arrEachSuccess) > 0){
                        $arrSuccess[$iS]['sheetIndex']      = $sheetIndex;
                        $arrSuccess[$iS]['companyCode']     = $companyCode;
                        $arrSuccess[$iS]['areaCode']        = $areaCode;
                        $arrSuccess[$iS]['year']            = $year;
                        $arrSuccess[$iS]['arrEachSuccess']  = $arrEachSuccess;
                        $iS++;
                    }
                }
            }

            if(count($arrUpdateCalBenchmark) > 0){
                foreach($arrUpdateCalBenchmark as $ucb){
                    $sqlILP = "
                        SELECT *
                        FROM important_level_position
                        WHERE inactive = 0
                        AND company_id = ".$ucb['companyId']."
                        AND area_id = ".$ucb['areaId']."
                        AND year = ".$ucb['year']."
                        AND month = ".$ucb['month']."";
                    $objILPDB = DB::select(DB::raw($sqlILP));

                    if(count($objILPDB) > 0){
                        foreach($objILPDB as $ilp){
                            $areaBenchmark = 0;
                            foreach($ucb['selectArray'] as $ila){
                                if($ilp->goal_id == $ila['goalId']){
                                    $areaBenchmark = $ila['benchmark'];
                                    break;
                                }
                            }

                            if($areaBenchmark != 0){
                                $totalImportantLevel = 0;
                                foreach($objILPDB as $insILP){
                                    if($insILP->goal_id == $ilp->goal_id){
                                        $totalImportantLevel += $insILP->important_level;
                                    }
                                }

                                if($totalImportantLevel != 0 && $ilp->important_level != 0){
                                    $calBenchmark = ($areaBenchmark / $totalImportantLevel) * $ilp->important_level;

                                    $dataCalBenchmark = array('cal_benchmark' => $calBenchmark);

                                    DB::table('important_level_position')
                                        ->where('company_id', $ilp->company_id)
                                        ->where('area_id', $ilp->area_id)
                                        ->where('position_id', $ilp->position_id)
                                        ->where('goal_id', $ilp->goal_id)
                                        ->where('year', $ilp->year)
                                        ->where('month', $ilp->month)
                                        ->where('inactive', 0)
                                        ->update($dataCalBenchmark);
                                }
                            }
                        }
                    }
                }
            }
            $strSuccess = "";
            if(count($arrSuccess) > 0){
                foreach($arrSuccess as $success){
                    $strEachSuccess = "";
                    foreach($success['arrEachSuccess'] as $eachSuccess){
                        if($strEachSuccess == ""){
                            $strEachSuccess = "<br/>&nbsp;&nbsp;&nbsp;<b>- "
                                .$eachSuccess['positionCode']."</b> đã import thành công tỷ trọng tháng <b>"
                                .$eachSuccess['month'].'/'.$success['year']."</b>: <b>".$eachSuccess['numRow'].'</b> dòng.'
                                .'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Danh sách mục tiêu đã import: '.$eachSuccess['listGoal'].'.'
                            ;
                        } else {
                            $strEachSuccess .= "<br/>&nbsp;&nbsp;&nbsp;<b>- "
                                .$eachSuccess['positionCode']."</b> đã import thành công tỷ trọng tháng <b>"
                                .$eachSuccess['month'].'/'.$success['year']."</b>: <b>".$eachSuccess['numRow'].'</b> dòng.'
                                .'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Danh sách mục tiêu đã import: '.$eachSuccess['listGoal'].'.'
                            ;
                        }
                    }

                    if($strEachSuccess != ""){
                        if($strSuccess == ""){
                            $strSuccess = '<b>'.commonUtils::TITLE_IMPORT_POSITION_GOAL.'</b><br/><br/>* Số sheet import: '.count($arrSheets)
                                .'<br/>* Số sheet import thành công: '.$numSheetSuccess
                                .'<hr/><b> * '.$sheetNames[$success['sheetIndex']]
                                .' &#187; '.$success['companyCode']
                                .' &#187; '.$success['areaCode'].'</b>'.$strEachSuccess
                            ;
                        } else {
                            $strSuccess .= '<br/><b>'.' * '.$sheetNames[$success['sheetIndex']]
                                .' &#187; '.$success['companyCode']
                                .' &#187; '.$success['areaCode'].'</b>'.$strEachSuccess
                            ;
                        }
                    }
                }
            }
            $strError = "";
            if(count($arrDataError) > 0){
                foreach($arrSheets as $sheetIndex){
                    $strSheetError = "";
                    foreach($arrDataError as $error){
                        if($sheetIndex == $error['sheetIndex']){
                            if($strSheetError == ""){
                                $strSheetError = '<br/>&nbsp;&nbsp;&nbsp; - '.$error['content'];
                            } else {
                                $strSheetError .= '<br/>&nbsp;&nbsp;&nbsp; - '.$error['content'];
                            }
                        }
                    }
                    if($strSheetError != ""){
                        if($strError == ""){
                            $strError = '<b>'.commonUtils::TITLE_IMPORT_POSITION_GOAL.'<hr/>'
                                .' * '.$sheetNames[$sheetIndex].'</b>'
                                .$strSheetError;
                        } else {
                            $strError .= '<br/><b> * '.$sheetNames[$sheetIndex].'</b>'.$strSheetError;
                        }
                    }
                }
            }

            if($strSuccess != ""){
                Session::flash('message-success', $strSuccess);
            }
            if($strError != ""){
                Session::flash('message-errors', $strError);
            }

            $dataLog = array(
                'functionName' => 'Tỷ trọng cho Chức danh (importMultiPriorityPosition)',
                'action'       => commonUtils::ACTION_IMPORT,
                'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                'newValue'     => $curExcelFile,
                'createdUser'  => $actionUser);

            $this->writeLog($dataLog);

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        $this->clearSession();
        Session::flash('type', 3);
        return redirect('importGoal');
    }

    public function beforeImportMultiGoalArea($path, $startRow, $typeImport, $rename, $arrDataSession){
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        $sCompanyId     = $arrDataSession['sCompanyId'];
        $sAreaId        = $arrDataSession['sAreaId'];
        $sPositionId    = $arrDataSession['sPositionId'];
        $sId            = $arrDataSession['sId'];

        if($sAccessLevel != 2 && $sId != 0){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 5);
            return redirect('importGoal');
        }

        $inputFileName = $path;
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheetNames = $objPHPExcel->getSheetNames();

        /* *************************************************************************************************************
         * get all data in header sheet
         * ************************************************************************************************************/
        $sheet              = $objPHPExcel->getSheet(0);
        $highestRow         = $sheet->getHighestRow();
        $highestColumn      = $sheet->getHighestColumn();

        $yearApply          = trim($sheet->rangeToArray('F' . 4)[0][0]);
        $fromMonthApply     = trim($sheet->rangeToArray('F' . 5)[0][0]);
        $toMonthApply       = trim($sheet->rangeToArray('F' . 6)[0][0]);
        $companyCodeApply   = trim($sheet->rangeToArray('B' . 6)[0][0]);
        $titleExcel         = trim($sheet->rangeToArray('C' . 2)[0][0]);

        $year               = (isset($yearApply) && is_numeric((int)$yearApply) && (int)$yearApply >= 2015) ? (int)$yearApply : '';
        $fromMonth          = (isset($fromMonthApply) && is_numeric((int)$fromMonthApply) && (int)$fromMonthApply >= 1 && (int)$fromMonthApply <= 12) ? (int)$fromMonthApply : '';
        $toMonth            = (isset($toMonthApply) && is_numeric((int)$toMonthApply) && (int)$toMonthApply >= 1 && (int)$toMonthApply <= 12 && (int)$toMonthApply > $fromMonth) ? (int)$toMonthApply : (int)$fromMonthApply;
        $companyCode        = (isset($companyCodeApply) && $companyCodeApply != null) ? $companyCodeApply : '';
        $title              = (isset($titleExcel) && $titleExcel != null) ? $titleExcel : '';

        $arrDataValid = array();
        $iDV = 0;

        $arrDataError = array();
        $iDE = 0;

        $arrDataOverride = array();
        $iDO = 0;

        if($title == ""){
            #Call back error when company code is not valid
            Session::flash('message-errors', $this->config->get('constant.NULL_TITLE_FILE'));
            $this->clearSession();
            Session::flash('type', 5);
            return redirect('importGoal');
        }

        if($title != "" && $companyCode != "" && $year != "" && $fromMonth != ""){
            /* *********************************************************************************************************
             * get all data before loop
             * check all data header is valid
             * ********************************************************************************************************/

            if(strtolower(trim($title)) != strtolower(commonUtils::TITLE_IMPORT_GOAL_AREA)){
                #Call back error when company code is not valid
                Session::flash('message-errors', $this->config->get('constant.ERR_IMPORT_FILE_INVALID'));
                $this->clearSession();
                Session::flash('type', 5);
                return redirect('importGoal');
            }

            $objLockDB = DB::table('lock')->where('inactive', 0)->get();

            #object company
            $objCompanyDB   = DB::table('company')->where('inactive', 0)->where('company_code', $companyCode)->first();
            $companyId      = (count($objCompanyDB) == 1) ? $objCompanyDB->id : -1;

            if($companyId == -1){
                #Call back error when company code is not valid
                Session::flash('message-errors', $this->config->get('constant.ERR_COMP_CODE_APPLY').$companyCode);
                $this->clearSession();
                Session::flash('type', 5);
                return redirect('importGoal');
            }

            if(
                $companyId != $sCompanyId
                && $sId != 0
                && $sId != 3
            ){
                Session::flash('message-errors', 'Bạn không thể import Kế hoạch cho các Tổ/Quận/Huyện thuộc Phòng/Đài/MBF HCM :<b>'.$companyCode.'</b><br/> Vui lòng liên hệ Quản trị viên để biết thên chi tiết!');
                $this->clearSession();
                Session::flash('type', 5);
                return redirect('importGoal');
            }

            #object area
            $objAreaDB = DB::table('area')->where('inactive', 0)->where('company_id', $companyId)->get();

            if(count($objAreaDB) == 0){
                Session::flash('message-errors', $this->config->get('constant.NULL_AREA_DB').$this->config->get('constant.SUFF_IMPORT_TA_NULL_DB'));
                $this->clearSession();
                Session::flash('type', 5);
                return redirect('importGoal');
            }

            #object goal
            $objGoalDB = DB::table('goal')->where('inactive', 0)->get();
            if(count($objGoalDB) == 0){
                Session::flash('message-errors', $this->config->get('constant.NULL_GOAL_DB').$this->config->get('constant.SUFF_IMPORT_TA_NULL_DB'));
                $this->clearSession();
                Session::flash('type', 5);
                return redirect('importGoal');
            }

            /* *********************************************************************************************************
             * get object important level area before import target area
             * check have important level area
             * *********************************************************************************************************/
            $sqlILA = "
                SELECT ila.*, g.goal_code, g.parent_id, a.area_code, g.goal_type
                FROM important_level_area ila
                LEFT JOIN goal g ON g.id = ila.goal_id
                LEFT JOIN area a ON a.id = ila.area_id
                WHERE ila.inactive = 0
                AND ila.year = ".$year."
                AND ila.month >= ".$fromMonth."
                AND ila.month <= ".$toMonth."
                AND ila.company_id = ".$companyId."
            ";
            $objILADB = DB::select(DB::raw($sqlILA));
            $alert = ($fromMonth != $toMonth) ? 'từ tháng '.$fromMonth.' đến tháng '.$toMonth : 'tháng '.$toMonth;
            if(count($objILADB) == 0){
                Session::flash('message-errors', 'Vui lòng import tỷ trọng '.$alert.' cho các Tổ/Quận/Huyện'.$this->config->get('constant.SUFF_IMPORT_TA_NULL_DB'));
                $this->clearSession();
                Session::flash('type', 5);
                return redirect('importGoal');
            }

            $arrHILA = array();/** array have important level area */
            $iHI = 0;

            $arrUnlock = array();
            $iUL = 0;

            $listMonthLock = "";
            $listMonthNILA = "";/** list month not important level area */

            $arrM1 = array();
            $arrM2 = array();
            $arrM3 = array();
            $arrM4 = array();
            $arrM5 = array();
            $arrM6 = array();
            $arrM7 = array();
            $arrM8 = array();
            $arrM9 = array();
            $arrM10 = array();
            $arrM11 = array();
            $arrM12 = array();

            $iA1 = 0;
            $iA2 = 0;
            $iA3 = 0;
            $iA4 = 0;
            $iA5 = 0;
            $iA6 = 0;
            $iA7 = 0;
            $iA8 = 0;
            $iA9 = 0;
            $iA10 = 0;
            $iA11 = 0;
            $iA12 = 0;

            for($m = $fromMonth; $m <= $toMonth; $m++){
                /*******************************************************************************************************
                 * Check locked
                 ******************************************************************************************************/
                $isLock = $this->checkLockData($year, $m, '', 3, $companyId);
                /*foreach($objLockDB as $lock){
                    if($lock->ofmonth == $m && $lock->ofyear == $year && $lock->lock == 1){
                        $isLock = 1;
                        break;
                    }
                }*/
                if($isLock == 1){
                    if($listMonthLock == ""){
                        $listMonthLock = $m;
                    } else {
                        $listMonthLock .= ', '.$m;
                    }
                } else {
                    $arrUnlock[$iUL]['month']= $m;
                    $arrUnlock[$iUL]['year']= $year;
                    $iUL++;
                }

                /*******************************************************************************************************
                 * Put data for 12 month
                 ******************************************************************************************************/
                switch ($m) {
                    case 1:
                        foreach($objILADB as $ila){
                            if($ila->month == 1 && $ila->important_level != 0){
                                $arrM1[$iA1]['areaId']          = $ila->area_id;
                                $arrM1[$iA1]['areaCode']        = $ila->area_code;
                                $arrM1[$iA1]['goalId']          = $ila->goal_id;
                                $arrM1[$iA1]['goalCode']        = $ila->goal_code;
                                $arrM1[$iA1]['benchmark']       = $ila->benchmark;
                                $arrM1[$iA1]['importantLevel']  = $ila->important_level;
                                $arrM1[$iA1]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM1[$iA1]['parentId']        = $ila->parent_id;
                                $arrM1[$iA1]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA1++;
                            }
                        }
                        break;
                    case 2:
                        foreach($objILADB as $ila){
                            if($ila->month == 2 && $ila->important_level != 0){
                                $arrM2[$iA2]['areaId']          = $ila->area_id;
                                $arrM2[$iA2]['areaCode']        = $ila->area_code;
                                $arrM2[$iA2]['goalId']          = $ila->goal_id;
                                $arrM2[$iA2]['goalCode']        = $ila->goal_code;
                                $arrM2[$iA2]['benchmark']       = $ila->benchmark;
                                $arrM2[$iA2]['importantLevel']  = $ila->important_level;
                                $arrM2[$iA2]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM2[$iA2]['parentId']        = $ila->parent_id;
                                $arrM2[$iA2]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA2++;
                            }
                        }
                        break;
                    case 3:
                        foreach($objILADB as $ila){
                            if($ila->month == 3 && $ila->important_level != 0){
                                $arrM3[$iA3]['areaId']          = $ila->area_id;
                                $arrM3[$iA3]['areaCode']        = $ila->area_code;
                                $arrM3[$iA3]['goalId']          = $ila->goal_id;
                                $arrM3[$iA3]['goalCode']        = $ila->goal_code;
                                $arrM3[$iA3]['benchmark']       = $ila->benchmark;
                                $arrM3[$iA3]['importantLevel']  = $ila->important_level;
                                $arrM3[$iA3]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM3[$iA3]['parentId']        = $ila->parent_id;
                                $arrM3[$iA3]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA3++;
                            }
                        }
                        break;
                    case 4:
                        foreach($objILADB as $ila){
                            if($ila->month == 4 && $ila->important_level != 0){
                                $arrM4[$iA4]['areaId']          = $ila->area_id;
                                $arrM4[$iA4]['areaCode']        = $ila->area_code;
                                $arrM4[$iA4]['goalId']          = $ila->goal_id;
                                $arrM4[$iA4]['goalCode']        = $ila->goal_code;
                                $arrM4[$iA4]['benchmark']       = $ila->benchmark;
                                $arrM4[$iA4]['importantLevel']  = $ila->important_level;
                                $arrM4[$iA4]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM4[$iA4]['parentId']        = $ila->parent_id;
                                $arrM4[$iA4]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA4++;
                            }
                        }
                        break;
                    case 5:
                        foreach($objILADB as $ila){
                            if($ila->month == 5 && $ila->important_level != 0){
                                $arrM5[$iA5]['areaId']          = $ila->area_id;
                                $arrM5[$iA5]['areaCode']        = $ila->area_code;
                                $arrM5[$iA5]['goalId']          = $ila->goal_id;
                                $arrM5[$iA5]['goalCode']        = $ila->goal_code;
                                $arrM5[$iA5]['benchmark']       = $ila->benchmark;
                                $arrM5[$iA5]['importantLevel']  = $ila->important_level;
                                $arrM5[$iA5]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM5[$iA5]['parentId']        = $ila->parent_id;
                                $arrM5[$iA5]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA5++;
                            }
                        }
                        break;
                    case 6:
                        foreach($objILADB as $ila){
                            if($ila->month == 6 && $ila->important_level != 0){
                                $arrM6[$iA6]['areaId']          = $ila->area_id;
                                $arrM6[$iA6]['areaCode']        = $ila->area_code;
                                $arrM6[$iA6]['goalId']          = $ila->goal_id;
                                $arrM6[$iA6]['goalCode']        = $ila->goal_code;
                                $arrM6[$iA6]['benchmark']       = $ila->benchmark;
                                $arrM6[$iA6]['importantLevel']  = $ila->important_level;
                                $arrM6[$iA6]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM6[$iA6]['parentId']        = $ila->parent_id;
                                $arrM6[$iA6]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA6++;
                            }
                        }
                        break;
                    case 7:
                        foreach($objILADB as $ila){
                            if($ila->month == 7 && $ila->important_level != 0){
                                $arrM7[$iA7]['areaId']          = $ila->area_id;
                                $arrM7[$iA7]['areaCode']        = $ila->area_code;
                                $arrM7[$iA7]['goalId']          = $ila->goal_id;
                                $arrM7[$iA7]['goalCode']        = $ila->goal_code;
                                $arrM7[$iA7]['benchmark']       = $ila->benchmark;
                                $arrM7[$iA7]['importantLevel']  = $ila->important_level;
                                $arrM7[$iA7]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM7[$iA7]['parentId']        = $ila->parent_id;
                                $arrM7[$iA7]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA7++;
                            }
                        }
                        break;
                    case 8:
                        foreach($objILADB as $ila){
                            if($ila->month == 8 && $ila->important_level != 0){
                                $arrM8[$iA8]['areaId']          = $ila->area_id;
                                $arrM8[$iA8]['areaCode']        = $ila->area_code;
                                $arrM8[$iA8]['goalId']          = $ila->goal_id;
                                $arrM8[$iA8]['goalCode']        = $ila->goal_code;
                                $arrM8[$iA8]['benchmark']       = $ila->benchmark;
                                $arrM8[$iA8]['importantLevel']  = $ila->important_level;
                                $arrM8[$iA8]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM8[$iA8]['parentId']        = $ila->parent_id;
                                $arrM8[$iA8]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA8++;
                            }
                        }
                        break;
                    case 9:
                        foreach($objILADB as $ila){
                            if($ila->month == 9 && $ila->important_level != 0){
                                $arrM9[$iA9]['areaId']          = $ila->area_id;
                                $arrM9[$iA9]['areaCode']        = $ila->area_code;
                                $arrM9[$iA9]['goalId']          = $ila->goal_id;
                                $arrM9[$iA9]['goalCode']        = $ila->goal_code;
                                $arrM9[$iA9]['benchmark']       = $ila->benchmark;
                                $arrM9[$iA9]['importantLevel']  = $ila->important_level;
                                $arrM9[$iA9]['calBenchmark']    = $ila->cal_benchmark;
                                $arrM9[$iA9]['parentId']        = $ila->parent_id;
                                $arrM9[$iA9]['isParent']        = ($ila->parent_id != 0) ? 1 : 0;
                                $iA9++;
                            }
                        }
                        break;
                    case 10:
                        foreach($objILADB as $ila){
                            if($ila->month == 10 && $ila->important_level != 0){
                                $arrM10[$iA10]['areaId']        = $ila->area_id;
                                $arrM10[$iA10]['areaCode']      = $ila->area_code;
                                $arrM10[$iA10]['goalId']        = $ila->goal_id;
                                $arrM10[$iA10]['goalCode']      = $ila->goal_code;
                                $arrM10[$iA10]['benchmark']     = $ila->benchmark;
                                $arrM10[$iA10]['importantLevel']= $ila->important_level;
                                $arrM10[$iA10]['calBenchmark']  = $ila->cal_benchmark;
                                $arrM10[$iA10]['parentId']      = $ila->parent_id;
                                $arrM10[$iA10]['isParent']      = ($ila->parent_id != 0) ? 1 : 0;
                                $iA10++;
                            }
                        }
                        break;
                    case 11:
                        foreach($objILADB as $ila){
                            if($ila->month == 11 && $ila->important_level != 0){
                                $arrM11[$iA11]['areaId']        = $ila->area_id;
                                $arrM11[$iA11]['areaCode']      = $ila->area_code;
                                $arrM11[$iA11]['goalId']        = $ila->goal_id;
                                $arrM11[$iA11]['goalCode']      = $ila->goal_code;
                                $arrM11[$iA11]['benchmark']     = $ila->benchmark;
                                $arrM11[$iA11]['importantLevel']= $ila->important_level;
                                $arrM11[$iA11]['calBenchmark']  = $ila->cal_benchmark;
                                $arrM11[$iA11]['parentId']      = $ila->parent_id;
                                $arrM11[$iA11]['isParent']      = ($ila->parent_id != 0) ? 1 : 0;
                                $iA11++;
                            }
                        }
                        break;
                    case 12:
                        foreach($objILADB as $ila){
                            if($ila->month == 12 && $ila->important_level != 0){
                                $arrM12[$iA12]['areaId']        = $ila->area_id;
                                $arrM12[$iA12]['areaCode']      = $ila->area_code;
                                $arrM12[$iA12]['goalId']        = $ila->goal_id;
                                $arrM12[$iA12]['goalCode']      = $ila->goal_code;
                                $arrM12[$iA12]['benchmark']     = $ila->benchmark;
                                $arrM12[$iA12]['importantLevel']= $ila->important_level;
                                $arrM12[$iA12]['calBenchmark']  = $ila->cal_benchmark;
                                $arrM12[$iA12]['parentId']      = $ila->parent_id;
                                $arrM12[$iA12]['isParent']      = ($ila->parent_id != 0) ? 1 : 0;
                                $iA12++;
                            }
                        }
                        break;
                }

                /***********************************************************************************************
                 * Check have important level area
                 **********************************************************************************************/

                $isILA = 0;
                foreach($objILADB as $ila){
                    if($ila->month == $m){
                        $isILA = 1;
                        break;
                    }
                }
                if($isILA == 0){
                    if($listMonthNILA == ""){
                        $listMonthNILA = $m;
                    } else {
                        $listMonthNILA .= ', '.$m;
                    }
                } else {

                    $arrHILA[$iHI]['month']     = $m;
                    $arrHILA[$iHI]['year']      = $year;
                    $iHI++;
                }
            }

            #get Index of Highest Column in current sheet
            $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

            $arrAreaValid = array();
            $iAV = 0;

            $listAreaNull = "";
            $listAreaErr  = "";
            for($c = 6; $c < $indexHighestColumn - 4; $c++) {
                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($c);
                $compareAreaCode = trim($sheet->rangeToArray(trim($currentColumn . '7'))[0][0]);
                if($compareAreaCode != ""){
                    $areaId = -1;
                    foreach($objAreaDB as $area){
                        if(
                            commonUtils::compareTwoString($area->area_code, $compareAreaCode) == 1
                            && commonUtils::compareTwoString('Tổng Kế hoạch', $compareAreaCode) != 1
                        ){
                            $areaId = $area->id;
                            break;
                        }
                    }
                    if($areaId != -1){
                        if(count($arrAreaValid) == 0){
                            $arrAreaValid[$iAV]['areaId']       = $areaId;
                            $arrAreaValid[$iAV]['areaCode']     = $compareAreaCode;
                            $arrAreaValid[$iAV]['column']       = $currentColumn;
                            $arrAreaValid[$iAV]['indexColumn']  = $c;
                            $iAV++;
                        } else {
                            $exist = 0;
                            foreach($arrAreaValid as $areaValid){
                                if($areaValid['areaId'] == $areaId){
                                    $exist = 1;
                                    break;
                                }
                            }
                            if($exist == 0){
                                $arrAreaValid[$iAV]['areaId']       = $areaId;
                                $arrAreaValid[$iAV]['areaCode']     = $compareAreaCode;
                                $arrAreaValid[$iAV]['column']       = $currentColumn;
                                $arrAreaValid[$iAV]['indexColumn']  = $c;
                                $iAV++;
                            }
                        }
                    } else {
                        if($listAreaErr == ""){
                            $listAreaErr = $currentColumn . '7';
                        } else {
                            $listAreaErr .= ', '.$currentColumn . '7';
                        }
                    }
                } else {
                    if($listAreaNull == ""){
                        $listAreaNull = $currentColumn . '7';
                    } else {
                        $listAreaNull .= ', '.$currentColumn . '7';
                    }
                }
            }

            if($listAreaErr != ""){
                $arrDataError[$iDE]['sheetIndex'] = 0;
                $arrDataError[$iDE]['content'] = "Mã Tổ/Quận/Huyện lỗi tại ô: ".$listAreaErr.'.';
                $iDE++;
            }
            if($listAreaNull != ""){
                $arrDataError[$iDE]['sheetIndex'] = 0;
                $arrDataError[$iDE]['content'] = "Mã Tổ/Quận/Huyện rỗng tại ô: ".$listAreaNull.'.';
                $iDE++;
            }

            /* *********************************************************************************************************
             * get array goal valid in this sheet
             * ********************************************************************************************************/
            $arrExcelGoal = array();
            $iEG = 0;

            $listGoalNull   = "";
            $listGoalErrors = "";

            $arrDataEachSheet = array();
            $iDES = 0;

            $arrORES = array(); /** Array override each sheet */
            $iOR = 0;

            for ($row = $startRow; $row <= $highestRow; $row++) {
                #Read a row of data into an array
                $dataSheet = $sheet->rangeToArray('A' . $row . ':B' . $row, NULL, TRUE, FALSE);

                $no     = trim($dataSheet[0][0]);
                $code   = trim($dataSheet[0][1]);

                $no     = (isset($no) && $no != null) ? $no : '?';
                $code   = (isset($code) && $code != null) ? $code : '';

                if($code != ''){
                    $goalId   = -1;
                    $goalType = -1;
                    $formula  = -1;
                    $parentId = -1;
                    $unitId   = -1;
                    foreach($objGoalDB as $goal){
                        if($goal->goal_code == $code){
                            $goalId   = $goal->id;
                            $goalType = $goal->goal_type;
                            $formula  = $goal->formula;
                            $parentId = $goal->parent_id;
                            $unitId   = $goal->unit_id;
                            break;
                        }
                    }
                    if($goalId != -1){
                        if(count($arrExcelGoal) == 0){
                            $arrExcelGoal[$iEG]['goalId']   = $goalId;
                            $arrExcelGoal[$iEG]['unitId']   = $unitId;
                            $arrExcelGoal[$iEG]['goalCode'] = $code;
                            $arrExcelGoal[$iEG]['goalType'] = $goalType;
                            $arrExcelGoal[$iEG]['formula']  = $formula;
                            $arrExcelGoal[$iEG]['parentId'] = $parentId;
                            $arrExcelGoal[$iEG]['isParent'] = ($parentId != 0) ? 1 : 0;
                            $iEG++;
                        } else {
                            $exist = 0;
                            foreach($arrExcelGoal as $excelGoal){
                                if($excelGoal['goalId'] == $goalId){
                                    $exist = 1;
                                    break;
                                }
                            }
                            if($exist == 0){
                                $arrExcelGoal[$iEG]['goalId']   = $goalId;
                                $arrExcelGoal[$iEG]['unitId']   = $unitId;
                                $arrExcelGoal[$iEG]['goalCode'] = $code;
                                $arrExcelGoal[$iEG]['goalType'] = $goalType;
                                $arrExcelGoal[$iEG]['formula']  = $formula;
                                $arrExcelGoal[$iEG]['parentId'] = $parentId;
                                $arrExcelGoal[$iEG]['isParent'] = ($parentId != 0) ? 1 : 0;
                                $iEG++;
                            }
                        }
                    } else {
                        if($listGoalErrors == ""){
                            $listGoalErrors = $no;
                        } else {
                            $listGoalErrors .= ', '.$no;
                        }
                    }
                } else {
                    if($listGoalNull == ""){
                        $listGoalNull = $no;
                    } else {
                        $listGoalNull .= ', '.$no;
                    }
                }
            }

            if($listGoalNull != ""){
                $arrDataError[$iDE]['sheetIndex'] = 0;
                $arrDataError[$iDE]['content'] = $this->config->get('constant.NULL_GOAL_CODE').$listGoalNull;
                $iDE++;
            }
            if($listGoalErrors != ""){
                $arrDataError[$iDE]['sheetIndex'] = 0;
                $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_GOAL_CODE').$listGoalErrors;
                $iDE++;
            }

            if(count($arrAreaValid) > 0 && count($arrUnlock) > 0 && count($arrExcelGoal) > 0 && count($arrHILA) > 0){
                foreach($arrAreaValid as $areaValid){
                    $avCode          = $areaValid['areaCode'];
                    $avId            = $areaValid['areaId'];
                    $avColumn        = $areaValid['column'];
                    $avIndexColumn   = $areaValid['indexColumn'];

                    $arrGM = array();/** array goal by month */
                    $iGM   = 0;

                    $sqlTA = "
                        SELECT ta.*, g.goal_code, g.parent_id
                        FROM target_area ta
                        LEFT JOIN goal g ON g.id = ta.goal_id
                        WHERE ta.inactive = 0
                        AND ta.year = ".$year."
                        AND ta.month >= ".$fromMonth."
                        AND ta.month <= ".$toMonth."
                        AND ta.company_id = ".$companyId."
                        AND ta.area_id = ".$avId."
                        GROUP BY ta.month, ta.area_id
                    ";
                    $objTADB = DB::select(DB::raw($sqlTA));

                    $listMonthOR = "";
                    for($im = $fromMonth; $im <= $toMonth; $im++){
                        if(count($objTADB) > 0){
                            foreach($objTADB as $targetArea){
                                if($targetArea->area_id == $avId && $targetArea->month == $im){
                                    if($listMonthOR == ""){
                                        $listMonthOR = $im;
                                    } else {
                                        $listMonthOR .= ', '.$im;
                                    }
                                    break;
                                }
                            }
                        }

                        $arrEABM = array();/** array Each Area by Month */
                        $iEA = 0;

                        $arrExcelGoalValid = array();
                        $iVL = 0;

                        $selectArray = "";

                        switch ($im) {
                            case 1:
                                if(count($arrM1) > 0){
                                    foreach($arrM1 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM1;
                                }
                                break;
                            case 2:
                                if(count($arrM2) > 0){
                                    foreach($arrM2 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM2;
                                }

                                break;
                            case 3:
                                if(count($arrM3) > 0){
                                    foreach($arrM3 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM3;
                                }
                                break;
                            case 4:
                                if(count($arrM4) > 0){
                                    foreach($arrM4 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM4;
                                }
                                break;
                            case 5:
                                if(count($arrM5) > 0){
                                    foreach($arrM5 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM5;
                                }
                                break;
                            case 6:
                                if(count($arrM6) > 0){
                                    foreach($arrM6 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM6;
                                }
                                break;
                            case 7:
                                if(count($arrM7) > 0){
                                    foreach($arrM7 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM7;
                                }
                                break;
                            case 8:
                                if(count($arrM8) > 0){
                                    foreach($arrM8 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM8;
                                }
                                break;
                            case 9:

                                if(count($arrM9) > 0){
                                    foreach($arrM9 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM9;
                                }

                                break;
                            case 10:
                                if(count($arrM10) > 0){
                                    foreach($arrM10 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM10;
                                }
                                break;
                            case 11:
                                if(count($arrM11) > 0){
                                    foreach($arrM11 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                $iVL++;
                                            }
                                        }
                                    }
                                    $selectArray = $arrM11;
                                }
                                break;
                            case 12:
                                if(count($arrM12) > 0){
                                    foreach($arrM12 as $am){
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $am['goalId'] && $am['areaId'] == $avId){
                                                if($excelGoal['goalId'] == $am['goalId']){
                                                    $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                    $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                    $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                    $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                    $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                    $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                    $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                    $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                    $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                    $iVL++;
                                                }
                                            }
                                        }
                                    }
                                    $selectArray = $arrM12;
                                }
                                break;
                        }

                        if(count($arrExcelGoalValid) > 0){
                            $listGoalNotPA = ""; /** list goal not priority for this area */
                            for ($row = $startRow; $row <= $highestRow; $row++) {
                                #Read a row of data into an array
                                $dataSheet = $sheet->rangeToArray('A' . $row . ':'.$avColumn . $row, NULL, TRUE, FALSE);

                                $code           = trim($dataSheet[0][1]);
                                $targetValue    = trim($dataSheet[0][$avIndexColumn]);

                                $code           = (isset($code) && $code != null) ? $code : '';
                                $targetValue    = (isset($targetValue) && $targetValue != null) ? $targetValue : 0;

                                if($code != ""){

                                    $eGoalId            = -1;
                                    $eParentId          = 0;
                                    $eUnitId            = -1;
                                    $eImportantLevel    = -1;
                                    $eBenchmark         = -1;
                                    $eCalBenchmark      = -1;

                                    foreach($arrExcelGoalValid as $egv){
                                        if($egv['goalCode'] == $code){
                                            if($targetValue == 0 && $egv['isParent'] == 1){
                                                Session::flash('message-errors', 'Tồn tại mục tiêu có <b>Tỷ trọng</b> mà chưa được phân bổ <b>Kế hoạch</b> tại Tổ/Quận/Huyện: '.$avCode.'</b>.<br/> Vui lòng kiểm tra lại tại <b>'.$avCode.'</b> và các Tổ/Quận/Huyện khác.');
                                                $this->clearSession();
                                                Session::flash('type', 5);
                                                return redirect('importGoal');
                                            } else {
                                                $eGoalId         = $egv['goalId'];
                                                $eParentId       = $egv['parentId'];
                                                $eUnitId         = $egv['unitId'];
                                                $eImportantLevel = $egv['importantLevel'];
                                                $eBenchmark      = $egv['benchmark'];
                                                $eCalBenchmark   = $egv['calBenchmark'];
                                                $eGoalType       = $egv['goalType'];
                                                break;
                                            }
                                        }
                                    }
                                    if($eGoalId != -1 && $eParentId != 0){
                                        if(count($arrEABM) == 0){
                                            $arrEABM[$iEA]['goalId']            = $eGoalId;
                                            $arrEABM[$iEA]['goalCode']          = $code;
                                            $arrEABM[$iEA]['parentId']          = $eParentId;
                                            $arrEABM[$iEA]['isParent']          = 1;
                                            $arrEABM[$iEA]['targetValue']       = $targetValue;
                                            $arrEABM[$iEA]['unitId']            = $eUnitId;
                                            $arrEABM[$iEA]['importantLevel']    = $eImportantLevel;
                                            $arrEABM[$iEA]['benchmark']         = $eBenchmark;
                                            $arrEABM[$iEA]['calBenchmark']      = $eCalBenchmark;
                                            $arrEABM[$iEA]['goalType']          = $eGoalType;
                                            $iEA++;
                                        } else {
                                            $exist = 0;
                                            foreach($arrEABM as $eabm){
                                                if($eabm['goalId'] == $eGoalId){
                                                    $exist = 1;
                                                    break;
                                                }
                                            }
                                            if($exist == 0){
                                                $arrEABM[$iEA]['goalId']            = $eGoalId;
                                                $arrEABM[$iEA]['goalCode']          = $code;
                                                $arrEABM[$iEA]['parentId']          = $eParentId;
                                                $arrEABM[$iEA]['isParent']          = 1;
                                                $arrEABM[$iEA]['targetValue']       = $targetValue;
                                                $arrEABM[$iEA]['unitId']            = $eUnitId;
                                                $arrEABM[$iEA]['importantLevel']    = $eImportantLevel;
                                                $arrEABM[$iEA]['benchmark']         = $eBenchmark;
                                                $arrEABM[$iEA]['calBenchmark']      = $eCalBenchmark;
                                                $arrEABM[$iEA]['goalType']          = $eGoalType;
                                                $iEA++;
                                            }
                                        }

                                    } else {
                                        if($listGoalNotPA == ""){
                                            $listGoalNotPA = $code;
                                        } else {
                                            $listGoalNotPA .= ', '.$code;
                                        }
                                    }
                                }
                            }
                        }

                        if(count($arrEABM) > 0){
                            $arrGM[$iGM]['month']       = $im;
                            $arrGM[$iGM]['arrEABM']     = $arrEABM;
                            $arrGM[$iGM]['selectArray'] = $selectArray;
                            $iGM++;
                        }
                    }

                    if($listMonthOR != ""){
                        $arrORES[$iOR]['areaId']   = $avId;
                        $arrORES[$iOR]['areaCode'] = $avCode;
                        $arrORES[$iOR]['listMonth']= $listMonthOR;
                        $iOR++;
                    }

                    if(count($arrGM) > 0){
                        if(count($arrDataEachSheet) == 0){
                            $arrDataEachSheet[$iDES]['areaId']    = $avId;
                            $arrDataEachSheet[$iDES]['areaCode']  = $avCode;
                            $arrDataEachSheet[$iDES]['arrGM']     = $arrGM;
                            $iDES++;
                        } else {
                            $exist = 0;
                            foreach($arrDataEachSheet as $des){
                                if($des['areaId'] == $avId){
                                    $exist = 1;
                                    break;
                                }
                            }
                            if($exist == 0){
                                $arrDataEachSheet[$iDES]['areaId']    = $avId;
                                $arrDataEachSheet[$iDES]['areaCode']  = $avCode;
                                $arrDataEachSheet[$iDES]['arrGM']     = $arrGM;
                                $iDES++;
                            }
                        }
                    }
                }

                if(count($arrORES) > 0){
                    $arrDataOverride[$iDO]['companyId']     = $companyId;
                    $arrDataOverride[$iDO]['companyCode']   = $companyCode;
                    $arrDataOverride[$iDO]['year']          = $year;
                    $arrDataOverride[$iDO]['arrORES']       = $arrORES;
                    $iDO++;
                }

                if(count($arrDataEachSheet) > 0){
                    $arrDataValid[$iDV]['companyId']        = $companyId;
                    $arrDataValid[$iDV]['companyCode']      = $companyCode;
                    $arrDataValid[$iDV]['year']             = $year;
                    $arrDataValid[$iDV]['arrDataEachSheet'] = $arrDataEachSheet;
                    $iDV++;
                }
            } else {

                $alert = ($fromMonth != $toMonth) ? 'từ tháng '.$fromMonth.' đến tháng '.$toMonth : 'tháng '.$toMonth;

                if(count($arrAreaValid) == 0){
                    Session::flash('message-errors', 'Tất cả mã Tổ/Quận/Huyện lỗi.');
                    $this->clearSession();
                    Session::flash('type', 5);
                    return redirect('importGoal');
                }

                if(count($arrUnlock) == 0){
                    Session::flash('message-errors', 'Dữ liệu '.$alert.' năm '. $year . ' đang khóa.');
                    $this->clearSession();
                    Session::flash('type', 5);
                    return redirect('importGoal');
                }

                if(count($arrExcelGoal) == 0){
                    Session::flash('message-errors', 'Tất cả mã Mục tiêu lỗi.');
                    $this->clearSession();
                    Session::flash('type', 5);
                    return redirect('importGoal');
                }

                if(count($arrHILA) == 0){
                    Session::flash('message-errors', 'Vui lòng import Tỷ trọng Tổ/Quận/Huyện '.$alert.' năm '.$year.' trước khi import kế hoạch.');
                    $this->clearSession();
                    Session::flash('type', 5);
                    return redirect('importGoal');
                }
            }
        }

        /* *************************************************************************************************************
         * get string override
         * ************************************************************************************************************/
        $strOverride = "";
        if(count($arrDataOverride) > 0){
            foreach($arrDataOverride as $dataOverride){

                $strDetail = "";
                foreach($dataOverride['arrORES'] as $detailOR){
                    if($strDetail == ""){
                        $strDetail = '<br/>&nbsp;&nbsp;&nbsp;<b>- '.$detailOR['areaCode']
                            .'</b> đã tồn tại kế hoạch tháng: <b>'.$detailOR['listMonth'].'/'.$dataOverride['year'].'</b>.';
                    }else{
                        $strDetail .= '<br/>&nbsp;&nbsp;&nbsp;<b>- '.$detailOR['areaCode']
                            .'</b> đã tồn tại kế hoạch tháng: <b>'.$detailOR['listMonth'].'/'.$dataOverride['year'].'</b>.';
                    }
                }
                if($strDetail != ""){
                    $strOverride ='<b>'.commonUtils::TITLE_IMPORT_GOAL_AREA.'<hr/>* '.$sheetNames[0].':</b>'.$strDetail;
                }
            }
        }

        if($strOverride != ""){
            $strOverride .= "<br/><br/><b>*** Chú ý:</b> Khi ghi đè Kế hoạch Tổ/Quận/Huyện thì các dữ liệu liên quan: Kế hoạch/ Thực hiện Tổ/Quận/Huyện, Chức danh, Nhân viên"
                ." thuộc Tổ/Quận/Huyện vào các tháng/năm ghi đè sẽ bị xóa!"
            ;
        }

        /***************************************************************************************************************
         * send data to import function
         * ************************************************************************************************************/

        $data['arrDataValid']   = $arrDataValid;
        $data['arrDataOverride']= $arrDataOverride;
        $data['arrDataError']   = $arrDataError;
        $data['curExcelFile']   = $rename;
        $data['pathFile']       = $path;

        #Write session for action next
        Session::put('strIssetDataShow', $strOverride);
        Session::put('chooseImport', 1);
        Session::put('data', $data);
        Session::put('curType', $typeImport);
        Session::flash('type', 5);
        Session::save();
        return redirect('importGoal');
    }

    public function importMultiGoalArea(){

        DB::beginTransaction();
        try{

            /***********************************************************************************************************
             * get data from session
             * ********************************************************************************************************/
            $data               = Session::get('data');
            $actionUser         = Session::get('sid');

            $path               = $data['pathFile'];
            $curExcelFile       = $data['curExcelFile'];
            $arrDataError       = $data['arrDataError'];
            $arrDataOverride    = $data['arrDataOverride'];
            $arrDataValid       = $data['arrDataValid'];

            $dir = date('Y').'/'.date('m');
            $inputFileName = $path;

            $this->clearSession();

            /***********************************************************************************************************
             * Override data here
             * *********************************************************************************************************/

            if(count($arrDataOverride) > 0){
                foreach($arrDataOverride as $dOverride){
                    $doCompanyId    = $dOverride['companyId'];
                    $doYear         = $dOverride['year'];
                    $doArrORES      = $dOverride['arrORES'];

                    foreach($doArrORES as $ores){
                        $doAreaId   = $ores['areaId'];
                        $doListMonth    = explode(',', $ores['listMonth']);

                        foreach($doListMonth as $doMonth){
                            /*$sqlOverride = "
                                DELETE FROM target_area
                                WHERE company_id = '".$doCompanyId."'
                                AND area_id = '".$doAreaId."'
                                AND year = '".$doYear."'
                                AND month = '".trim($doMonth)."'
                            ";
                            DB::delete(DB::raw($sqlOverride));*/

                            $this->deleteTA($doCompanyId, $doAreaId, $doYear, trim($doMonth), trim($doMonth));
                            $this->deleteTP($doCompanyId, $doAreaId, 0, $doYear, trim($doMonth), trim($doMonth));
                            $this->deleteTE($doCompanyId, $doAreaId, 0, $doYear, trim($doMonth), trim($doMonth), 0);
                        }
                    }

                    $ilcApplyDate = $this->getApplyDate4Company($doCompanyId, $doYear, '');
                    if($ilcApplyDate == ""){
                        Session::flash('message-errors', '<b>Import Kế hoạch Tổ/Quận/Huyện</b><hr>'.'Vui lòng import tỷ trọng cho Phòng/Đài/MBF HCM năm <b> '.$doYear.'</b> trước khi import tỷ trọng cho Tổ/Quận/Huyện.');
                        $this->clearSession();
                        Session::flash('type', 5);
                        return redirect('importGoal');
                    }

                    $this->formatIPOCompany($doCompanyId, $ilcApplyDate);
                    $this->calKpi4Company($doCompanyId, $doYear, 1, 12, $ilcApplyDate, $actionUser);
                    $corApplyDate = $this->getApplyDate4Corporation($doYear);
                    if($corApplyDate == ""){
                        Session::flash('message-errors', '<b>Import Tỷ trọng Tổ/Quận/Huyện</b><hr>'.'Vui lòng import tỷ trọng cho Công ty Mobifone năm <b> '.$doYear.'</b> trước khi import tỷ trọng cho Tổ/Quận/Huyện.');
                        $this->clearSession();
                        Session::flash('type', 5);
                        return redirect('importGoal');
                    }

                    $this->formatIPOCorporation($corApplyDate);
                    $this->calKpi4Corporation($corApplyDate, $ilcApplyDate, $actionUser);
                }

                $dataLog = array(
                    'functionName' => 'Kế hoạch cho Tổ/Quận/Huyện (importMultiGoalArea)',
                    'action'       => commonUtils::ACTION_OVERRIDE,
                    'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'newValue'     => $curExcelFile,
                    'createdUser'  => $actionUser
                );
                $this->writeLog($dataLog);
            }
            /***********************************************************************************************************
             * begin import with data valid
             * ********************************************************************************************************/
            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
            $sheetNames = $objPHPExcel->getSheetNames();

            $arrSuccess = array();
            $iS = 0;

            $arrDataInsert = array();

            if(count($arrDataValid) > 0){
                foreach($arrDataValid as $dataValid){
                    $companyId        = $dataValid['companyId'];
                    $companyCode      = $dataValid['companyCode'];
                    $year             = $dataValid['year'];
                    $arrDataEachSheet = $dataValid['arrDataEachSheet'];

                    $arrEachSuccess = array();
                    $iES = 0;

                    foreach($arrDataEachSheet as $des) {
                        $areaId = $des['areaId'];
                        $areaCode = $des['areaCode'];
                        $arrGM = $des['arrGM'];

                        foreach($arrGM as $gm){
                            $month          = $gm['month'];
                            $arrEABM        = $gm['arrEABM'];
                            $selectArray    = $gm['selectArray'];

                            $numSuccess = 0;
                            $listGoalImported = "";

                            foreach($arrEABM as $aEABM){
                                $aGoalId         = $aEABM['goalId'];
                                $aGoalCode       = $aEABM['goalCode'];
                                $aParentId       = $aEABM['parentId'];
                                $aIsParent       = $aEABM['isParent'];
                                $aTargetValue    = $aEABM['targetValue'];
                                $aUnitId         = $aEABM['unitId'];
                                $aImportantLevel = $aEABM['importantLevel'];
                                $aBenchmark      = $aEABM['benchmark'];
                                $aCalBenchmark   = $aEABM['calBenchmark'];
                                $aGoalType       = $aEABM['goalType'];

                                $targetArea = array(
                                    'company_id'        => $companyId,
                                    'area_id'           => $areaId,
                                    'goal_id'           => $aGoalId,
                                    'goal_type'         => $aGoalType,
                                    'unit_id'           => $aUnitId,
                                    'target_value'      => $aTargetValue,
                                    'month'             => $month,
                                    'year'              => $year,
                                    'important_level'   => $aImportantLevel,
                                    'benchmark'         => $aBenchmark,
                                    'cal_benchmark'     => $aCalBenchmark,
                                    'goal_level'        => $aIsParent,
                                    'created_user'      => $actionUser,
                                    'updated_user'      => 1);

                                $arrDataInsert[] = $targetArea;
                                $numSuccess++;

                                if($listGoalImported == ""){
                                    $listGoalImported = $aGoalCode;
                                } else {
                                    $listGoalImported .= ', '.$aGoalCode;
                                }
                            }
                            if($numSuccess > 0){
                                $arrEachSuccess[$iES]['areaCode']       = $areaCode;
                                $arrEachSuccess[$iES]['month']          = $month;
                                $arrEachSuccess[$iES]['numRow']         = $numSuccess;
                                $arrEachSuccess[$iES]['listGoal']       = $listGoalImported;
                                $iES++;
                            }
                        }
                    }
                    if(count($arrEachSuccess) > 0){
                        $arrSuccess[$iS]['sheetIndex']      = 0;
                        $arrSuccess[$iS]['companyCode']     = $companyCode;
                        $arrSuccess[$iS]['year']            = $year;
                        $arrSuccess[$iS]['arrEachSuccess']  = $arrEachSuccess;
                        $iS++;
                    }
                }
            }

            if(count($arrDataInsert) > 0){
                DB::table('target_area')->insert($arrDataInsert);
            }

            $strSuccess = "";
            if(count($arrSuccess) > 0){
                foreach($arrSuccess as $success){
                    $strEachSuccess = "";
                    foreach($success['arrEachSuccess'] as $eachSuccess){
                        if($strEachSuccess == ""){
                            $strEachSuccess = "<br/>&nbsp;&nbsp;&nbsp;<b>- "
                                .$eachSuccess['areaCode']."</b> đã import thành công kế hoạch tháng <b>"
                                .$eachSuccess['month'].'/'.$success['year']."</b>: <b>".$eachSuccess['numRow'].'</b> dòng.'
                                .'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Danh sách mục tiêu đã import: '.$eachSuccess['listGoal'].'.'
                            ;
                        }else{
                            $strEachSuccess .= "<br/>&nbsp;&nbsp;&nbsp;<b>- "
                                .$eachSuccess['areaCode']."</b> đã import thành công kế hoạch tháng <b>"
                                .$eachSuccess['month'].'/'.$success['year']."</b>: <b>".$eachSuccess['numRow'].'</b> dòng.'
                                .'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Danh sách mục tiêu đã import: '.$eachSuccess['listGoal'].'.'
                            ;
                        }
                    }

                    if($strEachSuccess != ""){
                        if($strSuccess == ""){
                            $strSuccess = '<b>'.commonUtils::TITLE_IMPORT_GOAL_AREA.'<hr/>'
                                .' * '.$sheetNames[$success['sheetIndex']]
                                .' &#187; '.$success['companyCode']
                                .'</b>'.$strEachSuccess
                            ;
                        } else {
                            $strSuccess .= '<br/><b>'.' * '.$sheetNames[$success['sheetIndex']]
                                .' &#187; '.$success['companyCode']
                                .'</b>'.$strEachSuccess
                            ;
                        }
                    }
                }
            }

            $arrSheets[] = 0;
            $strError = "";
            if(count($arrDataError) > 0){
                foreach($arrSheets as $sheetIndex){
                    $strSheetError = "";
                    foreach($arrDataError as $error){
                        if($sheetIndex == $error['sheetIndex']){
                            if($strSheetError == ""){
                                $strSheetError = '<br/>&nbsp;&nbsp;&nbsp; - '.$error['content'];
                            } else {
                                $strSheetError .= '<br/>&nbsp;&nbsp;&nbsp; - '.$error['content'];
                            }
                        }
                    }
                    if($strSheetError != ""){
                        if($strError == ""){
                            $strError = '<b>'.commonUtils::TITLE_IMPORT_GOAL_AREA.'<hr/>'
                                .' * '.$sheetNames[$sheetIndex].'</b>'
                                .$strSheetError;
                        } else {
                            $strError .= '<br/><b> * '.$sheetNames[$sheetIndex].'</b>'.$strSheetError;
                        }
                    }
                }
            }

            if($strSuccess != ""){
                Session::flash('message-success', $strSuccess);
            }
            if($strError != ""){
                Session::flash('message-errors', $strError);
            }

            $dataLog = array(
                'functionName' => 'Kế hoạch cho Tổ/Quận/Huyện (importMultiGoalArea)',
                'action'       => commonUtils::ACTION_IMPORT,
                'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                'newValue'     => $curExcelFile,
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
        Session::flash('type', 5);
        return redirect('importGoal');
    }

    public function beforeImportMultiGoalPosition($path, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession){
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        $sCompanyId     = $arrDataSession['sCompanyId'];
        $sAreaId        = $arrDataSession['sAreaId'];
        $sPositionId    = $arrDataSession['sPositionId'];
        $sId            = $arrDataSession['sId'];
        if(
            $sAccessLevel != 2
            && $sAccessLevel != 3
            && $sId != 0
            && $sId != 3
        ){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 9);
            return redirect('importGoal');
        }

        $inputFileName = $path;
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheetNames = $objPHPExcel->getSheetNames();
        if($listSheetIndex == ""){
            #Call back error when array index sheet is null
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_NULL'));
            $this->clearSession();
            Session::flash('type', 9);
            return redirect('importGoal');
        }

        $arrSheets = commonUtils::getArraySheets($listSheetIndex);

        if(count($arrSheets) == 0){
            #Call back error when array index sheet is not valid
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_INVALID'));
            $this->clearSession();
            Session::flash('type', 9);
            return redirect('importGoal');
        } else {
            $numOfSheet =  $objPHPExcel->getSheetCount();
            foreach($arrSheets as $checkExist){
                if($checkExist >= $numOfSheet){
                    Session::flash('message-errors', $this->config->get('constant.ERR_OVER_NUMBER_SHEET'));
                    $this->clearSession();
                    Session::flash('type', 9);
                    return redirect('importGoal');
                }
            }
        }

        /* *************************************************************************************************************
         * get all data before loop
         * ************************************************************************************************************/
        #object position
        $objPositionDB = DB::table('position')->where('inactive', 0)->get();

        #object goal
        $objGoalDB = DB::table('goal')->where('inactive', 0)->get();

        $objLockDB = DB::table('lock')->where('inactive', 0)->get();
        /* *************************************************************************************************************
         * Các ràng buộc trên file excel import data
         *  + Chỉ import cho duy nhất 1 Phòng/Đài/MBF HCM
         *  + Chỉ import cho duy nhất 1 Tổ/Quận/Huyện
         *  + Năm áp dụng trên các sheet phải giống nhau
         *  + Từ tháng, đến tháng trên các sheet phải giống nhau
         *  + Mã Tổ/Quận/Huyện trên các sheet không được lặp lại
         *  + Mã Chức danh trong 1 sheet không được lặp lại
         * ************************************************************************************************************/
        $arrDataError = array();
        $iDE = 0;

        $arrDataValid = array();
        $iDV = 0;

        $arrDataOverride = array();
        $iDO = 0;

        $preCompanyCode = "";
        $arrAreaCode    = array();
        $preYear        = "";
        $preFromMonth   = "";
        $preToMonth     = "";

        $companyId  = -1;

        foreach($arrSheets as $arrSheet){
            $sheet          = $objPHPExcel->getSheet($arrSheet);
            $yearApply      = trim($sheet->rangeToArray('E' . 4)[0][0]);
            $formMonthApply = trim($sheet->rangeToArray('E' . 5)[0][0]);
            $toMonthApply   = trim($sheet->rangeToArray('G' . 5)[0][0]);
            $companyCodeApply = trim($sheet->rangeToArray('B' . 6)[0][0]);
            $areaCodeApply  = trim($sheet->rangeToArray('B' . 7)[0][0]);
            $titleExcel     = trim($sheet->rangeToArray('C' . 2)[0][0]);
            $highestRow     = $sheet->getHighestRow();
            $highestColumn  = $sheet->getHighestColumn();

            $year           = commonUtils::checkYearValid($yearApply);
            $fromMonth      = commonUtils::checkMonthValid($formMonthApply);
            $toMonth        = (commonUtils::checkMonthValid($toMonthApply) != "") ? commonUtils::checkMonthValid($toMonthApply) : $fromMonth;
            $companyCode    = commonUtils::checkDataValid($companyCodeApply);
            $areaCode       = commonUtils::checkDataValid($areaCodeApply);
            $title          = commonUtils::checkDataValid($titleExcel);

            if($title != "" && $companyCode != "" && $areaCode != "" && $year != "" && $fromMonth != ""){
                if($preCompanyCode == ""
                    || commonUtils::compareTwoString($preCompanyCode, $companyCode) == 1){
                    $preCompanyCode = $companyCode;
                } else {
                    Session::flash('message-errors', "Mỗi file excel import chỉ duy nhất mỗi Phòng/Đài/MBF HCM.");
                    $this->clearSession();
                    Session::flash('type', 9);
                    return redirect('importGoal');
                }

                if(count($arrAreaCode) == 0 || !in_array($areaCode, $arrAreaCode)){
                    $arrAreaCode[] = $areaCode;
                } else {
                    Session::flash('message-errors', "Các sheet trên file import không được trùng Mã Tổ/Quận/Huyện.");
                    $this->clearSession();
                    Session::flash('type', 9);
                    return redirect('importGoal');
                }

                if($preYear == "" || $preYear == $year){
                    $preYear = $year;
                } else {
                    Session::flash('message-errors', "Năm áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 9);
                    return redirect('importGoal');
                }

                if($preFromMonth == "" || $preFromMonth == $fromMonth){
                    $preFromMonth = $fromMonth;
                } else {
                    Session::flash('message-errors', "Tháng áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 9);
                    return redirect('importGoal');
                }

                if($preToMonth == "" || $preToMonth == $toMonth){
                    $preToMonth = $toMonth;
                } else {
                    Session::flash('message-errors', "Tháng áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 9);
                    return redirect('importGoal');
                }
                /**
                 * Check title in excel file valid default tile sample
                 * If valid return 1 else return 0
                 */
                $isValidTitle = commonUtils::compareTwoString(commonUtils::TITLE_IMPORT_GOAL_POSITION, $title);
                /**
                 * Check companyCode in excel file valid companyCode in database
                 * Check areaCode in excel file valid areaCode in database
                 * If exist database return 1 else return 0
                 */
                if($companyId == -1){
                    $sqlCompanyDB = "
                        SELECT *
                        FROM company
                        WHERE inactive = 0
                        AND company_code = '".$companyCode."'
                    ";
                    $objCompanyDB = DB::select(DB::raw($sqlCompanyDB));

                    if(count($objCompanyDB) == 1){
                        $company    = $objCompanyDB[0];
                        $companyId  = $company->id;
                    }else{
                        Session::flash('message-errors', $this->config->get('constant.ERR_COMP_CODE_APPLY').$companyCode);
                        $this->clearSession();
                        Session::flash('type', 9);
                        return redirect('importGoal');
                    }
                }
                $areaId = -1;
                if($companyId != -1){
                    $sqlAreaDB = "
                        SELECT *
                        FROM area
                        WHERE inactive = 0
                        AND company_id = ".$companyId."
                        AND `area_code` = '".$areaCode."'
                    ";
                    $objAreaDB = DB::select(DB::raw($sqlAreaDB));

                    if(count($objAreaDB) == 1){
                        $area    = $objAreaDB[0];
                        $areaId  = $area->id;
                    }
                }

                if($isValidTitle == 1 && $companyId != -1 && $areaId != -1){

                    if($sAccessLevel == 2){
                        if($sCompanyId != $companyId){
                            Session::flash('message-errors', 'Bạn không thể import Kế hoạch cho các Chức danh thuộc Tổ/Quận/Huyện :<b>'.$areaCode.'</b> / Phòng/Đài/MBF HCM: <b>'.$companyCode.'</b> <br/> Vui lòng liên hệ Quản trị viên để biết thên chi tiết!');
                            $this->clearSession();
                            Session::flash('type', 9);
                            return redirect('importGoal');
                        }
                    }else{
                        if(
                            $areaId != $sAreaId
                            && $sId != 0
                            && $sId != 3
                        ){
                            Session::flash('message-errors', 'Bạn không thể import Kế hoạch cho các Chức danh thuộc Tổ/Quận/Huyện :<b>'.$areaCode.'</b><br/> Vui lòng liên hệ Quản trị viên để biết thên chi tiết!');
                            $this->clearSession();
                            Session::flash('type', 9);
                            return redirect('importGoal');
                        }
                    }

                    /* *************************************************************************************************
                     * get object target area
                     * check have target area
                     * ************************************************************************************************/

                    $sqlTA = "
                        SELECT *
                        FROM target_area
                        WHERE inactive = 0
                        AND `year` = ".$year."
                        AND `month` >= ".$fromMonth."
                        AND `month` <= ".$toMonth."
                        AND `company_id` = ".$companyId."
                        AND `area_id` = ".$areaId."
                    ";
                    $objTADB = DB::select(DB::raw($sqlTA));

                    for($m = $fromMonth; $m <= $toMonth; $m++){
                        $existTA = 0;
                        foreach($objTADB as $taCheck){
                            if($taCheck->target_value != 0 && $taCheck->month == $m){
                                $existTA = 1;
                                break;
                            }
                        }
                        if($existTA == 0){
                            $time = $m.'/'.$year;
                            $alertTime = ($fromMonth == $toMonth) ? 'trước khi import Kế hoạch chức danh tháng <b>'.$time.'</b>.' : 'và Kiểm tra lại Kế hoạch cho các tháng còn lại trước khi import Kế hoạch chức danh từ tháng <b>'.$fromMonth.'</b> đến tháng <b>'.$toMonth.'/'.$year.'</b>.';
                            Session::flash('message-errors', 'Vui lòng import Kế hoạch tháng <b>'.$time.'</b> cho Tổ/Quận/Huyện :<b>'.$areaCode.'</b> '.$alertTime);
                            $this->clearSession();
                            Session::flash('type', 9);
                            return redirect('importGoal');
                        }
                    }

                    /* *************************************************************************************************
                     * get object Important Level Position
                     * ************************************************************************************************/
                    $sqlILPDB = "
                        SELECT ilp.*, g.goal_code, g.goal_type, g.unit_id, p.position_code
                        FROM important_level_position ilp
                        LEFT JOIN goal g on g.id = ilp.goal_id
                        LEFT JOIN position p on p.id = ilp.position_id
                        WHERE ilp.inactive = 0
                        AND ilp.company_id = ".$companyId."
                        AND ilp.area_id = ".$areaId."
                        AND ilp.year = ".$year."
                        AND ilp.month >= ".$fromMonth."
                        AND ilp.month <= ".$toMonth."
                    ";
                    $objILPDB = DB::select(DB::raw($sqlILPDB));

                    if(count($objILPDB) > 0){

                        /* *********************************************************************************************
                         * check data from excel:
                         * check PositionCode
                         * check GoalCode
                         * check goal have important level
                         * check target value not null
                         * ********************************************************************************************/

                        #get Index of Highest Column in current sheet
                        $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

                        $arrPositionValid = array();
                        $iPV = 0;

                        $listPositionNull = "";
                        $listPositionErr  = "";

                        for($c = 6; $c < $indexHighestColumn - 4; $c++) {
                            $currentColumn       = PHPExcel_Cell::stringFromColumnIndex($c);
                            $comparePositionCode = trim($sheet->rangeToArray(trim($currentColumn . '8'))[0][0]);

                            if($comparePositionCode != ""){
                                $positionId = -1;
                                foreach($objPositionDB as $position){
                                    if($position->position_code == $comparePositionCode){
                                        $positionId = $position->id;
                                        break;
                                    }
                                }

                                if($positionId != -1){
                                    if(count($arrPositionValid) == 0){
                                        $arrPositionValid[$iPV]['positionId']   = $positionId;
                                        $arrPositionValid[$iPV]['positionCode'] = $comparePositionCode;
                                        $arrPositionValid[$iPV]['column']       = $currentColumn;
                                        $arrPositionValid[$iPV]['indexColumn']  = $c;
                                        $iPV++;
                                    } else {
                                        $exist = 0;
                                        foreach($arrPositionValid as $positionValid){
                                            if($positionValid['positionId'] == $positionId){
                                                $exist = 1;
                                                break;
                                            }
                                        }
                                        if($exist == 0){
                                            $arrPositionValid[$iPV]['positionId']   = $positionId;
                                            $arrPositionValid[$iPV]['positionCode'] = $comparePositionCode;
                                            $arrPositionValid[$iPV]['column']       = $currentColumn;
                                            $arrPositionValid[$iPV]['indexColumn']  = $c;
                                            $iPV++;
                                        }
                                    }
                                } else {
                                    if($listPositionErr == ""){
                                        $listPositionErr = $currentColumn . '8';
                                    } else {
                                        $listPositionErr .= ', '.$currentColumn . '8';
                                    }
                                }
                            }else{
                                if($listPositionNull == ""){
                                    $listPositionNull = $currentColumn . '8';
                                } else {
                                    $listPositionNull .= ', '.$currentColumn . '8';
                                }
                            }
                        }

                        if($listPositionNull != ""){
                            $arrDataError[$iDE]['sheetIndex'] = 0;
                            $arrDataError[$iDE]['content'] = "Mã chức danh rỗng tại: ".$listPositionNull;
                            $iDE++;
                        }
                        if($listPositionErr != ""){
                            $arrDataError[$iDE]['sheetIndex'] = 0;
                            $arrDataError[$iDE]['content'] = "Mã chức danh lỗi tại: ".$listPositionErr;
                            $iDE++;
                        }

                        /* *********************************************************************************************
                         * get array goal valid in this sheet
                         * ********************************************************************************************/
                        $arrExcelGoal = array();
                        $iEG = 0;

                        $arrDataEachSheet = array();
                        $iDES = 0;

                        $arrORES = array(); /** Array override each sheet */
                        $iOR = 0;

                        $listGoalNull   = "";
                        $listGoalErrors = "";
                        for ($row = $startRow; $row <= $highestRow; $row++) {
                            #Read a row of data into an array
                            $dataSheet = $sheet->rangeToArray('A' . $row . ':B' . $row, NULL, TRUE, FALSE);

                            $no     = trim($dataSheet[0][0]);
                            $code   = trim($dataSheet[0][1]);

                            $no     = (isset($no) && $no != null) ? $no : '?';
                            $code   = (isset($code) && $code != null) ? $code : '';

                            if($code != ''){
                                $goalId   = -1;
                                $goalType = -1;
                                $formula  = -1;
                                $parentId = -1;
                                $unitId   = -1;

                                foreach($objGoalDB as $goal){
                                    if($goal->goal_code == $code){
                                        $goalId   = $goal->id;
                                        $goalType = $goal->goal_type;
                                        $formula  = $goal->formula;
                                        $parentId = $goal->parent_id;
                                        $unitId   = $goal->unit_id;
                                        break;
                                    }
                                }
                                if($goalId != -1){
                                    if(count($arrExcelGoal) == 0){
                                        $arrExcelGoal[$iEG]['goalId']   = $goalId;
                                        $arrExcelGoal[$iEG]['unitId']   = $unitId;
                                        $arrExcelGoal[$iEG]['goalCode'] = $code;
                                        $arrExcelGoal[$iEG]['goalType'] = $goalType;
                                        $arrExcelGoal[$iEG]['formula']  = $formula;
                                        $arrExcelGoal[$iEG]['parentId'] = $parentId;
                                        $arrExcelGoal[$iEG]['isParent'] = ($parentId != 0) ? 1 : 0;
                                        $iEG++;
                                    } else {
                                        $exist = 0;
                                        foreach($arrExcelGoal as $excelGoal){
                                            if($excelGoal['goalId'] == $goalId){
                                                $exist = 1;
                                                break;
                                            }
                                        }
                                        if($exist == 0){
                                            $arrExcelGoal[$iEG]['goalId']   = $goalId;
                                            $arrExcelGoal[$iEG]['unitId']   = $unitId;
                                            $arrExcelGoal[$iEG]['goalCode'] = $code;
                                            $arrExcelGoal[$iEG]['goalType'] = $goalType;
                                            $arrExcelGoal[$iEG]['formula']  = $formula;
                                            $arrExcelGoal[$iEG]['parentId'] = $parentId;
                                            $arrExcelGoal[$iEG]['isParent'] = ($parentId != 0) ? 1 : 0;
                                            $iEG++;
                                        }
                                    }
                                } else {
                                    if($listGoalErrors == ""){
                                        $listGoalErrors = $no;
                                    } else {
                                        $listGoalErrors .= ', '.$no;
                                    }
                                }
                            } else {
                                if($listGoalNull == ""){
                                    $listGoalNull = $no;
                                } else {
                                    $listGoalNull .= ', '.$no;
                                }
                            }
                        }

                        if($listGoalNull != ""){
                            $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                            $arrDataError[$iDE]['content']      = $this->config->get('constant.NULL_GOAL_CODE').$listGoalNull;
                            $iDE++;
                        }
                        if($listGoalErrors != ""){
                            $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                            $arrDataError[$iDE]['content']      = $this->config->get('constant.ERR_GOAL_CODE').$listGoalErrors;
                            $iDE++;
                        }

                        if(count($arrPositionValid) > 0
                            && count($arrExcelGoal) > 0){
                            $arrHILP = array();/** array have important level position */
                            $iHI = 0;

                            $arrUnlock = array();
                            $iUL = 0;

                            $listMonthLock = "";
                            $listMonthNILP = "";/** list month not important level position */

                            $arrM1 = array();
                            $arrM2 = array();
                            $arrM3 = array();
                            $arrM4 = array();
                            $arrM5 = array();
                            $arrM6 = array();
                            $arrM7 = array();
                            $arrM8 = array();
                            $arrM9 = array();
                            $arrM10 = array();
                            $arrM11 = array();
                            $arrM12 = array();

                            $iA1 = 0;
                            $iA2 = 0;
                            $iA3 = 0;
                            $iA4 = 0;
                            $iA5 = 0;
                            $iA6 = 0;
                            $iA7 = 0;
                            $iA8 = 0;
                            $iA9 = 0;
                            $iA10 = 0;
                            $iA11 = 0;
                            $iA12 = 0;

                            for($m = $fromMonth; $m <= $toMonth; $m++){
                                /***************************************************************************************
                                 * Check locked
                                 **************************************************************************************/
                                $isLock = $this->checkLockData($year, $m, '', 3, $companyId);
                                /*foreach($objLockDB as $lock){
                                    if(
                                        $lock->ofmonth == $m
                                        && $lock->ofyear == $year
                                        && $lock->lock == 1
                                    ){
                                        $isLock = 1;
                                        break;
                                    }
                                }*/
                                if($isLock == 1){
                                    if($listMonthLock == ""){
                                        $listMonthLock = $m;
                                    } else {
                                        $listMonthLock .= ', '.$m;
                                    }
                                } else {
                                    $arrUnlock[$iUL]['month']= $m;
                                    $arrUnlock[$iUL]['year']= $year;
                                    $iUL++;
                                }
                                /***************************************************************************************
                                 * Put data for 12 month
                                 **************************************************************************************/
                                switch ($m) {
                                    case 1:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 1 && $ilp->important_level != 0){
                                                $arrM1[$iA1]['positionId']      = $ilp->position_id;
                                                $arrM1[$iA1]['positionCode']    = $ilp->position_code;
                                                $arrM1[$iA1]['goalId']          = $ilp->goal_id;
                                                $arrM1[$iA1]['benchmark']       = $ilp->benchmark;
                                                $arrM1[$iA1]['importantLevel']  = $ilp->important_level;
                                                $arrM1[$iA1]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA1++;
                                            }
                                        }
                                        break;
                                    case 2:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 2 && $ilp->important_level != 0){
                                                $arrM2[$iA2]['positionId']      = $ilp->position_id;
                                                $arrM2[$iA2]['positionCode']    = $ilp->position_code;
                                                $arrM2[$iA2]['goalId']          = $ilp->goal_id;
                                                $arrM2[$iA2]['benchmark']       = $ilp->benchmark;
                                                $arrM2[$iA2]['importantLevel']  = $ilp->important_level;
                                                $arrM2[$iA2]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA2++;
                                            }
                                        }
                                        break;
                                    case 3:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 3 && $ilp->important_level != 0){
                                                $arrM3[$iA3]['positionId']      = $ilp->position_id;
                                                $arrM3[$iA3]['positionCode']    = $ilp->position_code;
                                                $arrM3[$iA3]['goalId']          = $ilp->goal_id;
                                                $arrM3[$iA3]['benchmark']       = $ilp->benchmark;
                                                $arrM3[$iA3]['importantLevel']  = $ilp->important_level;
                                                $arrM3[$iA3]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA3++;
                                            }
                                        }
                                        break;
                                    case 4:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 4 && $ilp->important_level != 0){
                                                $arrM4[$iA4]['positionId']      = $ilp->position_id;
                                                $arrM4[$iA4]['positionCode']    = $ilp->position_code;
                                                $arrM4[$iA4]['goalId']          = $ilp->goal_id;
                                                $arrM4[$iA4]['benchmark']       = $ilp->benchmark;
                                                $arrM4[$iA4]['importantLevel']  = $ilp->important_level;
                                                $arrM4[$iA4]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA4++;
                                            }
                                        }
                                        break;
                                    case 5:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 5 && $ilp->important_level != 0){
                                                $arrM5[$iA5]['positionId']      = $ilp->position_id;
                                                $arrM5[$iA5]['positionCode']    = $ilp->position_code;
                                                $arrM5[$iA5]['goalId']          = $ilp->goal_id;
                                                $arrM5[$iA5]['benchmark']       = $ilp->benchmark;
                                                $arrM5[$iA5]['importantLevel']  = $ilp->important_level;
                                                $arrM5[$iA5]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA5++;
                                            }
                                        }
                                        break;
                                    case 6:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 6 && $ilp->important_level != 0){
                                                $arrM6[$iA6]['positionId']      = $ilp->position_id;
                                                $arrM6[$iA6]['positionCode']    = $ilp->position_code;
                                                $arrM6[$iA6]['goalId']          = $ilp->goal_id;
                                                $arrM6[$iA6]['benchmark']       = $ilp->benchmark;
                                                $arrM6[$iA6]['importantLevel']  = $ilp->important_level;
                                                $arrM6[$iA6]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA6++;
                                            }
                                        }
                                        break;
                                    case 7:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 7 && $ilp->important_level != 0){
                                                $arrM7[$iA7]['positionId']      = $ilp->position_id;
                                                $arrM7[$iA7]['positionCode']    = $ilp->position_code;
                                                $arrM7[$iA7]['goalId']          = $ilp->goal_id;
                                                $arrM7[$iA7]['benchmark']       = $ilp->benchmark;
                                                $arrM7[$iA7]['importantLevel']  = $ilp->important_level;
                                                $arrM7[$iA7]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA7++;
                                            }
                                        }
                                        break;
                                    case 8:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 8 && $ilp->important_level != 0){
                                                $arrM8[$iA8]['positionId']      = $ilp->position_id;
                                                $arrM8[$iA8]['positionCode']    = $ilp->position_code;
                                                $arrM8[$iA8]['goalId']          = $ilp->goal_id;
                                                $arrM8[$iA8]['benchmark']       = $ilp->benchmark;
                                                $arrM8[$iA8]['importantLevel']  = $ilp->important_level;
                                                $arrM8[$iA8]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA8++;
                                            }
                                        }
                                        break;
                                    case 9:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 9 && $ilp->important_level != 0){
                                                $arrM9[$iA9]['positionId']      = $ilp->position_id;
                                                $arrM9[$iA9]['positionCode']    = $ilp->position_code;
                                                $arrM9[$iA9]['goalId']          = $ilp->goal_id;
                                                $arrM9[$iA9]['benchmark']       = $ilp->benchmark;
                                                $arrM9[$iA9]['importantLevel']  = $ilp->important_level;
                                                $arrM9[$iA9]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA9++;
                                            }
                                        }
                                        break;
                                    case 10:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 10 && $ilp->important_level != 0){
                                                $arrM10[$iA10]['positionId']      = $ilp->position_id;
                                                $arrM10[$iA10]['positionCode']    = $ilp->position_code;
                                                $arrM10[$iA10]['goalId']          = $ilp->goal_id;
                                                $arrM10[$iA10]['benchmark']       = $ilp->benchmark;
                                                $arrM10[$iA10]['importantLevel']  = $ilp->important_level;
                                                $arrM10[$iA10]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA10++;
                                            }
                                        }
                                        break;
                                    case 11:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 11 && $ilp->important_level != 0){
                                                $arrM11[$iA11]['positionId']      = $ilp->position_id;
                                                $arrM11[$iA11]['positionCode']    = $ilp->position_code;
                                                $arrM11[$iA11]['goalId']          = $ilp->goal_id;
                                                $arrM11[$iA11]['benchmark']       = $ilp->benchmark;
                                                $arrM11[$iA11]['importantLevel']  = $ilp->important_level;
                                                $arrM11[$iA11]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA11++;
                                            }
                                        }
                                        break;
                                    case 12:
                                        foreach($objILPDB as $ilp){
                                            if($ilp->month == 12 && $ilp->important_level != 0){
                                                $arrM12[$iA12]['positionId']      = $ilp->position_id;
                                                $arrM12[$iA12]['positionCode']    = $ilp->position_code;
                                                $arrM12[$iA12]['goalId']          = $ilp->goal_id;
                                                $arrM12[$iA12]['benchmark']       = $ilp->benchmark;
                                                $arrM12[$iA12]['importantLevel']  = $ilp->important_level;
                                                $arrM12[$iA12]['calBenchmark']    = $ilp->cal_benchmark;
                                                $iA12++;
                                            }
                                        }
                                        break;
                                }

                                /***********************************************************************************************
                                 * Check have important level position
                                 **********************************************************************************************/

                                $isILP = 0;
                                foreach($objILPDB as $ilp){
                                    if($ilp->month == $m){
                                        $isILP = 1;
                                        break;
                                    }
                                }
                                if($isILP == 0){
                                    if($listMonthNILP == ""){
                                        $listMonthNILP = $m;
                                    }else{
                                        $listMonthNILP .= ', '.$m;
                                    }
                                }else{
                                    $arrHILP[$iHI]['month']     = $m;
                                    $arrHILP[$iHI]['year']      = $year;
                                    $iHI++;
                                }
                            }

                            if(count($arrUnlock) > 0
                                && count($arrHILP) > 0){
                                foreach($arrPositionValid as $positionValid){
                                    $pvCode          = $positionValid['positionCode'];
                                    $pvId            = $positionValid['positionId'];
                                    $pvColumn        = $positionValid['column'];
                                    $pvIndexColumn   = $positionValid['indexColumn'];

                                    $arrGM = array();/** array goal by month */
                                    $iGM   = 0;

                                    /* *********************************************************************************
                                     * get object Target Position
                                     * ********************************************************************************/
                                    $sqlTPDB = "
                                        SELECT tp.*
                                        FROM target_position tp
                                        WHERE tp.inactive = 0
                                        AND tp.company_id = ".$companyId."
                                        AND tp.area_id = ".$areaId."
                                        AND tp.position_id = ".$pvId."
                                        AND tp.year = ".$year."
                                        AND tp.month >= ".$fromMonth."
                                        AND tp.month <= ".$toMonth."
                                        GROUP BY tp.position_id, tp.month
                                    ";
                                    $objTPDB = DB::select(DB::raw($sqlTPDB));

                                    $listMonthOR = "";
                                    for($im = $fromMonth; $im <= $toMonth; $im++){

                                        if(count($objTPDB) > 0){
                                            foreach($objTPDB as $targetPosition){
                                                if(
                                                    $targetPosition->position_id == $pvId
                                                    && $targetPosition->month == $im
                                                ){
                                                    if($listMonthOR == ""){
                                                        $listMonthOR = $im;
                                                    }else{
                                                        $listMonthOR .= ', '.$im;
                                                    }
                                                    break;
                                                }
                                            }
                                        }

                                        $arrEPBM = array();/** array Each Position by Month */
                                        $iEP = 0;

                                        $arrExcelGoalValid = array();
                                        $iVL = 0;

                                        $selectArray = "";
                                        switch ($im) {
                                            case 1:
                                                if(count($arrM1) > 0){
                                                    foreach($arrM1 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }
                                                    }
                                                    $selectArray = $arrM1;
                                                }
                                                break;
                                            case 2:
                                                if(count($arrM2) > 0){
                                                    foreach($arrM2 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }
                                                    }
                                                    $selectArray = $arrM2;
                                                }

                                                break;
                                            case 3:
                                                if(count($arrM3) > 0){
                                                    foreach($arrM3 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }
                                                    }
                                                    $selectArray = $arrM3;
                                                }
                                                break;
                                            case 4:
                                                if(count($arrM4) > 0){
                                                    foreach($arrM4 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }
                                                    }
                                                    $selectArray = $arrM4;
                                                }
                                                break;
                                            case 5:
                                                if(count($arrM5) > 0){
                                                    foreach($arrM5 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }
                                                    }
                                                    $selectArray = $arrM5;
                                                }
                                                break;
                                            case 6:
                                                if(count($arrM6) > 0){
                                                    foreach($arrM6 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }

                                                    }
                                                    $selectArray = $arrM6;
                                                }
                                                break;
                                            case 7:
                                                if(count($arrM7) > 0){
                                                    foreach($arrM7 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }

                                                    }
                                                    $selectArray = $arrM7;
                                                }
                                                break;
                                            case 8:
                                                if(count($arrM8) > 0){
                                                    foreach($arrM8 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }

                                                    }
                                                    $selectArray = $arrM8;
                                                }
                                                break;
                                            case 9:

                                                if(count($arrM9) > 0){
                                                    foreach($arrM9 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }

                                                    }
                                                    $selectArray = $arrM9;
                                                }

                                                break;
                                            case 10:
                                                if(count($arrM10) > 0){
                                                    foreach($arrM10 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }

                                                    }
                                                    $selectArray = $arrM10;
                                                }
                                                break;
                                            case 11:
                                                if(count($arrM11) > 0){
                                                    foreach($arrM11 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                $iVL++;
                                                            }
                                                        }

                                                    }
                                                    $selectArray = $arrM11;
                                                }
                                                break;
                                            case 12:
                                                if(count($arrM12) > 0){
                                                    foreach($arrM12 as $am){
                                                        foreach($arrExcelGoal as $excelGoal){
                                                            if($excelGoal['goalId'] == $am['goalId'] && $am['positionId'] == $pvId){
                                                                if($excelGoal['goalId'] == $am['goalId']){
                                                                    $arrExcelGoalValid[$iVL]['goalId']          = $excelGoal['goalId'];
                                                                    $arrExcelGoalValid[$iVL]['goalCode']        = $excelGoal['goalCode'];
                                                                    $arrExcelGoalValid[$iVL]['parentId']        = $excelGoal['parentId'];
                                                                    $arrExcelGoalValid[$iVL]['isParent']        = $excelGoal['isParent'];
                                                                    $arrExcelGoalValid[$iVL]['goalType']        = $excelGoal['goalType'];
                                                                    $arrExcelGoalValid[$iVL]['unitId']          = $excelGoal['unitId'];
                                                                    $arrExcelGoalValid[$iVL]['importantLevel']  = $am['importantLevel'];
                                                                    $arrExcelGoalValid[$iVL]['benchmark']       = $am['benchmark'];
                                                                    $arrExcelGoalValid[$iVL]['calBenchmark']    = $am['calBenchmark'];
                                                                    $iVL++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $selectArray = $arrM12;
                                                }
                                                break;
                                        }

                                        if(count($arrExcelGoalValid) > 0){
                                            $listGoalNotPP = ""; /** list goal not priority for this position */
                                            for ($row = $startRow; $row <= $highestRow; $row++) {
                                                #Read a row of data into an array
                                                $dataSheet = $sheet->rangeToArray('A' . $row . ':'.$pvColumn . $row, NULL, TRUE, FALSE);

                                                $code        = commonUtils::checkDataValid(trim($dataSheet[0][1]));
                                                $targetValue = commonUtils::checkValueNumeric(trim($dataSheet[0][$pvIndexColumn]));

                                                if($code != ""){
                                                    $eGoalId            = -1;
                                                    $eParentId          = 0;
                                                    $eUnitId            = -1;
                                                    $eImportantLevel    = -1;
                                                    $eBenchmark         = -1;
                                                    $eCalBenchmark      = -1;

                                                    foreach($arrExcelGoalValid as $egv){
                                                        if($egv['goalCode'] == $code){
                                                            if($targetValue == 0 && $egv['isParent'] == 1){
                                                                Session::flash('message-errors', 'Tồn tại mục tiêu có <b>Tỷ trọng</b> mà chưa được phân bổ <b>Kế hoạch</b> tại Chức danh: '.$pvCode.'</b>.<br/> Vui lòng kiểm tra lại tại <b>'.$pvCode.'</b> và các Chức danh khác.');
                                                                $this->clearSession();
                                                                Session::flash('type', 9);
                                                                return redirect('importGoal');
                                                            }else{
                                                                $eGoalId         = $egv['goalId'];
                                                                $eParentId       = $egv['parentId'];
                                                                $eUnitId         = $egv['unitId'];
                                                                $eImportantLevel = $egv['importantLevel'];
                                                                $eBenchmark      = $egv['benchmark'];
                                                                $eCalBenchmark   = $egv['calBenchmark'];
                                                                $eGoalType       = $egv['goalType'];
                                                                break;
                                                            }
                                                        }
                                                    }

                                                    if($eGoalId != -1 && $eParentId != 0){
                                                        if(count($arrEPBM) == 0){
                                                            $arrEPBM[$iEP]['goalId']            = $eGoalId;
                                                            $arrEPBM[$iEP]['goalCode']          = $code;
                                                            $arrEPBM[$iEP]['parentId']          = $eParentId;
                                                            $arrEPBM[$iEP]['isParent']          = 1;
                                                            $arrEPBM[$iEP]['targetValue']       = $targetValue;
                                                            $arrEPBM[$iEP]['unitId']            = $eUnitId;
                                                            $arrEPBM[$iEP]['importantLevel']    = $eImportantLevel;
                                                            $arrEPBM[$iEP]['benchmark']         = $eBenchmark;
                                                            $arrEPBM[$iEP]['calBenchmark']      = $eCalBenchmark;
                                                            $arrEPBM[$iEP]['goalType']          = $eGoalType;
                                                            $iEP++;
                                                        }else{
                                                            $exist = 0;
                                                            foreach($arrEPBM as $epbm){
                                                                if($epbm['goalId'] == $eGoalId){
                                                                    $exist = 1;
                                                                    break;
                                                                }
                                                            }
                                                            if($exist == 0){
                                                                $arrEPBM[$iEP]['goalId']            = $eGoalId;
                                                                $arrEPBM[$iEP]['goalCode']          = $code;
                                                                $arrEPBM[$iEP]['parentId']          = $eParentId;
                                                                $arrEPBM[$iEP]['isParent']          = 1;
                                                                $arrEPBM[$iEP]['targetValue']       = $targetValue;
                                                                $arrEPBM[$iEP]['unitId']            = $eUnitId;
                                                                $arrEPBM[$iEP]['importantLevel']    = $eImportantLevel;
                                                                $arrEPBM[$iEP]['benchmark']         = $eBenchmark;
                                                                $arrEPBM[$iEP]['calBenchmark']      = $eCalBenchmark;
                                                                $arrEPBM[$iEP]['goalType']          = $eGoalType;
                                                                $iEP++;
                                                            }
                                                        }
                                                    }else{
                                                        if($listGoalNotPP == ""){
                                                            $listGoalNotPP = $code;
                                                        }else{
                                                            $listGoalNotPP .= ', '.$code;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        if(count($arrEPBM) > 0){
                                            $arrGM[$iGM]['month']       = $im;
                                            $arrGM[$iGM]['arrEPBM']     = $arrEPBM;
                                            //$arrGM[$iGM]['selectArray'] = $selectArray;
                                            $iGM++;
                                        }
                                    }

                                    if($listMonthOR != ""){
                                        $arrORES[$iOR]['positionId']    = $pvId;
                                        $arrORES[$iOR]['positionCode']  = $pvCode;
                                        $arrORES[$iOR]['listMonth']     = $listMonthOR;
                                        $iOR++;
                                    }

                                    if(count($arrGM) > 0){
                                        if(count($arrDataEachSheet) == 0){
                                            $arrDataEachSheet[$iDES]['positionId']    = $pvId;
                                            $arrDataEachSheet[$iDES]['positionCode']  = $pvCode;
                                            $arrDataEachSheet[$iDES]['arrGM']         = $arrGM;
                                            $iDES++;
                                        }else{
                                            $exist = 0;
                                            foreach($arrDataEachSheet as $des){
                                                if($des['positionId'] == $pvId){
                                                    $exist = 1;
                                                    break;
                                                }
                                            }
                                            if($exist == 0){
                                                $arrDataEachSheet[$iDES]['positionId']    = $pvId;
                                                $arrDataEachSheet[$iDES]['positionCode']  = $pvCode;
                                                $arrDataEachSheet[$iDES]['arrGM']         = $arrGM;
                                                $iDES++;
                                            }
                                        }
                                    }
                                }

                                if(count($arrORES) > 0){
                                    $arrDataOverride[$iDO]['companyId']     = $companyId;
                                    $arrDataOverride[$iDO]['companyCode']   = $companyCode;
                                    $arrDataOverride[$iDO]['areaId']        = $areaId;
                                    $arrDataOverride[$iDO]['areaCode']      = $areaCode;
                                    $arrDataOverride[$iDO]['year']          = $year;
                                    $arrDataOverride[$iDO]['toMonth']       = $toMonth;
                                    $arrDataOverride[$iDO]['arrORES']       = $arrORES;
                                    $iDO++;
                                }

                            }else{
                                $alert = ($fromMonth != $toMonth) ? 'từ tháng '.$fromMonth.' đến tháng '.$toMonth : 'tháng '.$toMonth;
                                if(count($arrUnlock) == 0){
                                    Session::flash('message-errors', 'Dữ liệu '.$alert.' năm '. $year . ' đang khóa.');
                                    $this->clearSession();
                                    Session::flash('type', 9);
                                    return redirect('importGoal');
                                }
                                if(count($arrHILP) == 0){
                                    $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                    $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_GOAL_CODE').$listGoalErrors;
                                    $iDE++;
                                }
                            }
                        }

                        if(count($arrDataEachSheet) > 0){
                            $arrDataValid[$iDV]['sheetIndex']       = $arrSheet;
                            $arrDataValid[$iDV]['companyId']        = $companyId;
                            $arrDataValid[$iDV]['companyCode']      = $companyCode;
                            $arrDataValid[$iDV]['areaId']           = $areaId;
                            $arrDataValid[$iDV]['areaCode']         = $areaCode;
                            $arrDataValid[$iDV]['year']             = $year;
                            $arrDataValid[$iDV]['toMonth']          = $toMonth;
                            $arrDataValid[$iDV]['arrDataEachSheet'] = $arrDataEachSheet;
                            $iDV++;
                        }

                    }else{
                        $alert = ($fromMonth != $toMonth) ? 'từ tháng '.$fromMonth.' đến tháng '.$toMonth : 'tháng '.$toMonth;
                        $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                        $arrDataError[$iDE]['content']      = "Vui lòng import tỷ trọng cho các chức danh thuộc <b>".$areaCode."</b> ".$alert." năm ".$year ;
                        $iDE++;
                    }

                }else{
                    if($isValidTitle != 1 ){
                        $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                        $arrDataError[$iDE]['content']      = $this->config->get('constant.ERR_IMPORT_FILE_INVALID');
                        $iDE++;
                    }

                    if($companyId == -1 ){
                        $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                        $arrDataError[$iDE]['content']      = $this->config->get('constant.ERR_COMP_CODE_APPLY').$companyCode;
                        $iDE++;
                    }

                    if($areaId == -1 ){
                        $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                        $arrDataError[$iDE]['content']      = $this->config->get('constant.ERR_AREA_CODE_APPLY').$areaCode;
                        $iDE++;
                    }
                }
            }else{
                if($title == ""){
                    $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                    $arrDataError[$iDE]['content']      = $this->config->get('constant.NULL_TITLE_FILE');
                    $iDE++;
                }
                if($companyCode == ""){
                    $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                    $arrDataError[$iDE]['content']      = $this->config->get('constant.NULL_COMP_APPLY');
                    $iDE++;
                }
                if($areaCode == ""){
                    $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                    $arrDataError[$iDE]['content']      = $this->config->get('constant.NULL_AREA_APPLY');
                    $iDE++;
                }
                if($year == ""){
                    $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                    $arrDataError[$iDE]['content']      = $this->config->get('constant.NULL_YEAR_APPLY');
                    $iDE++;
                }
                if($fromMonth == ""){
                    $arrDataError[$iDE]['sheetIndex']   = $arrSheet;
                    $arrDataError[$iDE]['content']      = $this->config->get('constant.NULL_MONTH_APPLY');
                    $iDE++;
                }
            }
        }
        $strOverride = "";
        if(count($arrDataOverride) > 0){
            foreach($arrDataOverride as $dataOverride){
                $companyCode    = $dataOverride['companyCode'];
                $areaCode       = $dataOverride['areaCode'];
                $year           = $dataOverride['year'];
                $arrORES        = $dataOverride['arrORES'];

                $strIOR = "";
                foreach($arrORES as $ores){
                    $positionCode   = $ores['positionCode'];
                    $listMonth      = $ores['listMonth'];

                    if($strIOR == ""){
                        $strIOR = "<br><b> &nbsp;&nbsp;&nbsp; + ".$positionCode."</b> "
                            .$this->config->get('constant.WARN_OVERRIDE_DATA')." <b>".$listMonth."/".$year."</b>."
                        ;
                    }else{
                        $strIOR .= "<br><b> &nbsp;&nbsp;&nbsp; + ".$positionCode."</b> "
                            .$this->config->get('constant.WARN_OVERRIDE_DATA')." <b>".$listMonth."/".$year."</b>."
                        ;
                    }
                }

                if($strIOR != ""){
                    if($strOverride == ""){
                        $strOverride = "<b>".commonUtils::TITLE_IMPORT_GOAL_POSITION."<hr/>"
                            .$companyCode." &raquo; ".$areaCode."</b>".$strIOR
                        ;
                    }else{
                        $strOverride .= "<b><br/><br/>".$companyCode." &raquo; ".$areaCode."</b>".$strIOR
                        ;
                    }
                }
            }
        }

        if($strOverride != ""){
            $strOverride .= "<br/><br/><b>*** Chú ý:</b> Khi ghi đè Kế hoạch Chức danh thì các dữ liệu liên quan: Kế hoạch/ Thực hiện Nhân viên"
                ." thuộc Chức danh vào các tháng/năm ghi đè sẽ bị xóa!"
            ;
        }

        /* *************************************************************************************************************
         * get all data before loop
         * ************************************************************************************************************/

        /* *************************************************************************************************************
         * send data to session
         * ************************************************************************************************************/
        $data['arrDataValid']   = $arrDataValid;
        $data['arrDataOverride']= $arrDataOverride;
        $data['arrDataError']   = $arrDataError;
        $data['curExcelFile']   = $rename;
        $data['pathFile']       = $path;
        $data['objPositionDB']  = $objPositionDB;
        $data['arrSheets']      = $arrSheets;

        #Write session for action next
        Session::put('strIssetDataShow', $strOverride);
        Session::put('chooseImport', 1);
        Session::put('data', $data);
        Session::put('curType', $typeImport);
        Session::flash('type', 9);
        Session::save();
        return redirect('importGoal');
    }

    public function importMultiGoalPosition(){

        DB::beginTransaction();
        try{
            $startRow       = Session::get('startRow');
            $actionUser     = Session::get('sid');
            $data           = Session::get('data');

            $arrDataValid   = $data['arrDataValid'];
            $arrDataOverride= $data['arrDataOverride'];
            $path           = $data['pathFile'];
            $objPositionDB  = $data['objPositionDB'];
            $arrSheets      = $data['arrSheets'];
            $arrDataError   = $data['arrDataError'];
            $curExcelFile   = $data['curExcelFile'];

            $dir            = date('Y').'/'.date('m');
            $inputFileName  = $path;

            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
            $sheetNames = $objPHPExcel->getSheetNames();
            /* *************************************************************************************************************
             * Override data
             * ************************************************************************************************************/
            if(count($arrDataOverride) > 0){
                $posTQId = -1;
                foreach($objPositionDB as $position){
                    if($position->position_code == commonUtils::POSITION_CODE_TQ){
                        $posTQId = $position->id;
                        break;
                    }
                }

                foreach($arrDataOverride as $dataOverride){
                    $companyId    = $dataOverride['companyId'];
                    $areaId       = $dataOverride['areaId'];
                    $year         = $dataOverride['year'];
                    $arrORES      = $dataOverride['arrORES'];

                    $arrDMonth = array();

                    foreach($arrORES as $ores){
                        $positionId   = $ores['positionId'];
                        $listMonth    = $ores['listMonth'];

                        foreach(explode(',', $listMonth)  as $month){
                            if(!in_array(trim($month), $arrDMonth)){
                                $arrDMonth[] = trim($month);
                            }

                            $this->deleteTP($companyId, $areaId, $positionId, $year, trim($month), trim($month));
                            $this->deleteTE($companyId, $areaId, $positionId, $year, trim($month), trim($month), 0);
                        }
                    }

                    foreach($arrDMonth as $dMonth){
                        $this->formatIPOPosition($companyId, $areaId, $posTQId, $year, $dMonth);
                        $this->cal4PositionTQ($companyId, $areaId, $posTQId, $year, $dMonth, $actionUser);

                        $this->formatIPOArea($companyId, $areaId, $year, $dMonth);
                        $this->calKpi4Area($companyId, $areaId, $year, $dMonth, $actionUser);
                    }
                }
                $comApplyDate = $this->getApplyDate4Company($companyId, $year, '');
                if($comApplyDate != ""){
                    $this->formatIPOCompany($companyId, $comApplyDate);
                    $this->calKpi4Company($companyId, $year, 1, 12, $comApplyDate, $actionUser);
                    $corApplyDate = $this->getApplyDate4Corporation($year);
                    if($corApplyDate != ""){
                        $this->formatIPOCorporation($corApplyDate);
                        $this->calKpi4Corporation($corApplyDate, $comApplyDate, $actionUser);
                    }else{
                        Session::flash('message-errors', '<b>Import Kế hoạch Chức danh</b><hr>'.'Vui lòng import tỷ trọng cho Công ty Mobifone năm <b> '.$year.'</b> trước khi import kế hoạch cho Chức danh.');
                        $this->clearSession();
                        Session::flash('type', 9);
                        return redirect('importGoal');
                    }
                }else{
                    Session::flash('message-errors', '<b>Import Kế hoạch Chức danh</b><hr>'.'Vui lòng import tỷ trọng cho Phòng/Đài/MBF HCM năm <b> '.$year.'</b> trước khi import kế hoạch cho Chức danh.');
                    $this->clearSession();
                    Session::flash('type', 9);
                    return redirect('importGoal');
                }

                $dataLog = array(
                    'functionName' => 'Kế hoạch cho Chức danh (importMultiGoalPosition)',
                    'action'       => commonUtils::ACTION_OVERRIDE,
                    'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'newValue'     => $curExcelFile,
                    'createdUser'  => $actionUser);
                $this->writeLog($dataLog);
            }
            /* *************************************************************************************************************
             * get object employee db
             * ************************************************************************************************************/
            $sqlEmployee = "
                SELECT u.*, p.position_code
                FROM users u
                LEFT JOIN position p ON p.id = u.position_id
                WHERE u.admin = 0
                AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
            ";
            $objEmployeeDB = DB::select(DB::raw($sqlEmployee));
            /* *************************************************************************************************************
             * Import with data valid
             * ************************************************************************************************************/
            $arrSuccess = array();
            $iS = 0;

            if(count($arrDataValid) > 0){
                foreach($arrDataValid as $dataValid){
                    $sheetIndex         = $dataValid['sheetIndex'];
                    $companyId          = $dataValid['companyId'];
                    $companyCode        = $dataValid['companyCode'];
                    $areaId             = $dataValid['areaId'];
                    $areaCode           = $dataValid['areaCode'];
                    $year               = $dataValid['year'];
                    $toMonth            = $dataValid['toMonth'];
                    $arrDataEachSheet   = $dataValid['arrDataEachSheet'];

                    $tqId = -1;

                    $arrDataInsert   = array();
                    $arrDataInsertTE = array();

                    $preTerminateYear = 0;
                    $preTerminateMonth = 0;

                    $diffYear = 0;
                    $diffMonth = 0;

                    $arrTemp = array();
                    $iT = 0;

                    foreach($objEmployeeDB as $employee){

                        $createdMonth   = (int)substr($employee->created_date, 5, 2);
                        $createdYear   = (int)substr($employee->created_date, 0, 4);

                        $terminateMonth   = (int)substr($employee->terminate_date, 5, 2);
                        $terminateYear   = (int)substr($employee->terminate_date, 0, 4);

                        if(
                            $employee->company_id   == $companyId
                            && $employee->area_id   == $areaId
                            && $createdYear         <= $year
                            && $createdMonth        <= $toMonth
                            && ($terminateYear       >= $year  || $terminateYear == 0)
                            && ($terminateMonth      >= $toMonth || $terminateMonth == 0)

                        ){



                            $tqId = $employee->id;

                            $arrTemp[$iT]['terminateYear']  = $terminateYear;
                            $arrTemp[$iT]['terminateMonth'] = $terminateMonth;
                            $arrTemp[$iT]['tqId']           = $tqId;
                            $iT++;

                        }
                    }

                    if(count($arrTemp) == 0){
                        $tqId = -1;
                    }else{
                        if(count($arrTemp) == 1){
                            $tqId = $arrTemp[0]['tqId'];
                        }else{
                            foreach($arrTemp as $temp){
                                for($i=1; $i<count($arrTemp); $i++){
                                    if(
                                        $temp['terminateYear'] <= $arrTemp[$i]['terminateYear']
                                        && $temp['terminateMonth'] <= $arrTemp[$i]['terminateMonth']
                                    ){
                                        $tqId = $temp['tqId'];
                                    }else{
                                        $tqId = $arrTemp[$i]['tqId'];
                                    }
                                }
                            }
                        }
                    }

                    if($tqId != -1){
                        $arrSEP = array();
                        $iSEP = 0;

                        foreach($arrDataEachSheet as $des){

                            $positionId     = $des['positionId'];
                            $positionCode   = $des['positionCode'];
                            $arrGM          = $des['arrGM'];

                            $arrSEM = array();
                            $iSEM = 0;

                            foreach($arrGM as $gm){
                                $month   = $gm['month'];
                                $arrEPBM = $gm['arrEPBM'];

                                $listGoalIPTP = "";
                                $listGoalIPTE = "";
                                foreach($arrEPBM as $epbm){

                                    $goalId         = $epbm['goalId'];
                                    $goalCode       = $epbm['goalCode'];
                                    $parentId       = $epbm['parentId'];
                                    $isParent       = $epbm['isParent'];
                                    $targetValue    = $epbm['targetValue'];
                                    $unitId         = $epbm['unitId'];
                                    $importantLevel = $epbm['importantLevel'];
                                    $benchmark      = $epbm['benchmark'];
                                    $calBenchmark   = $epbm['calBenchmark'];
                                    $goalType       = $epbm['goalType'];

                                    if($isParent == 1){
                                        $targetPosition = array(
                                            'company_id'        => $companyId,
                                            'area_id'           => $areaId,
                                            'position_id'       => $positionId,
                                            'goal_id'           => $goalId,
                                            'month'             => $month,
                                            'year'              => $year,
                                            'cal_benchmark'     => $calBenchmark,
                                            'important_level'   => $importantLevel,
                                            'target_value'      => $targetValue,
                                            'unit_id'           => $unitId,
                                            'benchmark'         => $benchmark,
                                            'goal_type'         => $goalType,
                                            'goal_level'        => 1,
                                            'created_user'      => $actionUser,
                                            'updated_user'      => 1);
                                        $arrDataInsert[] = $targetPosition;

                                        if($listGoalIPTP == ""){
                                            $listGoalIPTP = $goalCode;
                                        }else{
                                            $listGoalIPTP .= ', '.$goalCode;
                                        }

                                        if($positionCode == commonUtils::POSITION_CODE_TQ){
                                            $targetEmployee = array(
                                                'company_id'        => $companyId,
                                                'area_id'           => $areaId,
                                                'position_id'       => $positionId,
                                                'user_id'           => $tqId,
                                                'goal_id'           => $goalId,
                                                'month'             => $month,
                                                'year'              => $year,
                                                'important_level'   => $importantLevel,
                                                'target_value'      => $targetValue,
                                                'unit_id'           => $unitId,
                                                'benchmark'         => $benchmark,
                                                'cal_benchmark'     => $calBenchmark,
                                                'goal_type'         => $goalType,
                                                'goal_level'        => 1,
                                                'created_user'      => $actionUser,
                                                'updated_user'      => 1);

                                            $arrDataInsertTE[] = $targetEmployee;
                                            if($listGoalIPTE == ""){
                                                $listGoalIPTE = $goalCode;
                                            }else{
                                                $listGoalIPTE .= ', '.$goalCode;
                                            }
                                        }
                                    }
                                }

                                if($listGoalIPTP != ""){
                                    $arrSEM[$iSEM]['time']      = $month.'/'.$year;
                                    $arrSEM[$iSEM]['numRow']    = count(explode(',', $listGoalIPTP));
                                    $arrSEM[$iSEM]['listGoal']  = $listGoalIPTP;
                                    $iSEM++;
                                }

                            }
                            if(count($arrSEM) > 0){
                                $arrSEP[$iSEP]['positionCode']  = $positionCode;
                                $arrSEP[$iSEP]['arrSEM']        = $arrSEM;
                                $iSEP++;
                            }
                        }

                        if(count($arrSEP) > 0){
                            $arrSuccess[$iS]['sheetIndex']  = $sheetIndex;
                            $arrSuccess[$iS]['companyCode'] = $companyCode;
                            $arrSuccess[$iS]['areaCode']    = $areaCode;
                            $arrSuccess[$iS]['arrSEP']      = $arrSEP;
                            $iS++;
                        }
                    }else{
                        Session::flash('message-errors', '<b>Import Kế hoạch Chức danh</b><hr>'.'Tổ/Quận/Huyện: <b> '.$areaCode.'</b> không tồn tại nhân viên có chức danh Trưởng Mobifone quận. Vui lòng kiểm tra lại!');
                        $this->clearSession();
                        Session::flash('type', 9);
                        return redirect('importGoal');
                    }

                    if(count($arrDataInsert) > 0){
                        DB::table('target_position')->insert($arrDataInsert);
                    }
                    if(count($arrDataInsertTE) > 0){
                        DB::table('target_employee')->insert($arrDataInsertTE);
                    }
                }
                $strSuccess = "";
                if(count($arrSuccess) > 0){
                    foreach($arrSuccess as $success){

                        $sheetIndex  = (int)$success['sheetIndex'];
                        $companyCode = $success['companyCode'];
                        $areaCode    = $success['areaCode'];
                        $arrSEP      = $success['arrSEP'];

                        $strSLv1 = "";
                        foreach($arrSEP as $sep){
                            $positionCode   = $sep['positionCode'];
                            $arrSEM         = $sep['arrSEM'];

                            $strSLv2 = "";
                            foreach($arrSEM as $sem){

                                $time       = $sem['time'];
                                $numRow     = $sem['numRow'];
                                $listGoal   = $sem['listGoal'];

                                if($strSLv2 == ""){
                                    $strSLv2 =  "<br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Import thành công kế hoạch tháng <b>".$time."</b>: <b>".$numRow."</b> dòng."
                                        ."<br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Danh sách mục tiêu import: ".$listGoal
                                    ;
                                }else{
                                    $strSLv2 .=  "<br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Import thành công kế hoạch tháng <b>".$time."</b>: <b>".$numRow."</b> dòng."
                                        ."<br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Danh sách mục tiêu import: ".$listGoal
                                    ;
                                }
                            }
                            if($strSLv2 != ""){
                                if($strSLv1 == ""){
                                    $strSLv1 =  "<br/><b> &nbsp;&nbsp;&nbsp;- ".$positionCode."</b>".$strSLv2;
                                }else{
                                    $strSLv1 .=  "<br/><b> &nbsp;&nbsp;&nbsp;- ".$positionCode."</b>".$strSLv2;
                                }
                            }
                        }
                        if($strSLv1 != ""){
                            if($strSuccess == ""){
                                $strSuccess =   "<b>".commonUtils::TITLE_IMPORT_GOAL_POSITION."<hr/>"
                                    .$sheetNames[$sheetIndex]."<br/>"
                                    .$companyCode." &raquo; ".$areaCode."</b>".$strSLv1
                                ;
                            }else{
                                $strSuccess .=
                                    '<br/><b>'.$sheetNames[$sheetIndex]."<br/>"
                                    .$companyCode." &raquo; ".$areaCode."</b>".$strSLv1
                                ;
                            }
                        }
                    }
                }

                if($strSuccess != ""){
                    Session::flash('message-success', $strSuccess);
                }
            }

            $strErrors = "";
            if(count($arrDataError) > 0){
                foreach($arrSheets as $sheetIndex){
                    $strSheetErr = "";
                    foreach($arrDataError as $dataError){
                        if($dataError['sheetIndex'] == $sheetIndex){
                            if($strSheetErr == ""){
                                $strSheetErr = '<br/>&nbsp;&nbsp;&nbsp; - '.$dataError['content'];
                            }else{
                                $strSheetErr .= '<br/>&nbsp;&nbsp;&nbsp; - '.$dataError['content'];
                            }
                        }
                    }
                    if($strSheetErr != ""){
                        if($strErrors == ""){
                            $strErrors = "<b>".commonUtils::TITLE_IMPORT_GOAL_POSITION.'<hr>* '
                                .$sheetNames[$sheetIndex].':</b>'.$strSheetErr;
                            ;
                        }else{
                            $strErrors .= '<br/><b>* '.$sheetNames[$sheetIndex].':</b>'.$strSheetErr;
                            ;
                        }
                    }
                }
            }
            if($strErrors != ""){
                Session::flash('message-errors', $strErrors);
            }


            $dataLog = array(
                'functionName' => 'Kế hoạch cho Chức danh (importMultiGoalPosition)',
                'action'       => commonUtils::ACTION_IMPORT,
                'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                'newValue'     => $curExcelFile,
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        $this->clearSession();
        Session::flash('type', 9);
        return redirect('importGoal');
    }

    public  function beforeImportMultiGoalEmployee($path, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession)
    {
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        $sCompanyId     = $arrDataSession['sCompanyId'];
        $sAreaId        = $arrDataSession['sAreaId'];
        $sPositionId    = $arrDataSession['sPositionId'];
        $sId            = $arrDataSession['sId'];

        $deniedLevel = ($sAccessLevel == 5 || $sAccessLevel == 1) ? 1 : 0;

        if(
            $deniedLevel    == 1
            && $sId         != 0
            && $sId         != 3
        ){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 6);
            return redirect('importGoal');
        }

        $inputFileName = $path;
        try {
            $inputFileType  = PHPExcel_IOFactory::identify($inputFileName);
            $objReader      = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel    = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheetNames = $objPHPExcel->getSheetNames();

        if($listSheetIndex == ""){
            #Call back error when array index sheet is null
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_NULL'));
            $this->clearSession();
            Session::flash('type', 6);
        }

        $arrSheets = commonUtils::getArraySheets($listSheetIndex);

        if(count($arrSheets) == 0){
            #Call back error when array index sheet is not valid
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_INVALID'));
            $this->clearSession();
            Session::flash('type', 6);
            return redirect('importGoal');
        }else{
            $numOfSheet =  $objPHPExcel->getSheetCount();
            foreach($arrSheets as $checkExist){
                if($checkExist >= $numOfSheet){
                    Session::flash('message-errors', $this->config->get('constant.ERR_OVER_NUMBER_SHEET'));
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }
            }
        }
        $strIssetDataShow = "";
        /************************************************************************************************************/
        /* Get All data from database
        /************************************************************************************************************/
        $objLockDB = DB::table('lock')->where('inactive', 0)->get();

        #object company
        $objCompanyDB = DB::table('company')->where('inactive', 0)->get();

        #object area
        $objAreaDB = DB::table('area')->where('inactive', 0)->get();

        #object position
        $objPositionDB = DB::table('position')->where('inactive', 0)->get();

        #object goal
        $objGoalDB = DB::table('goal')->where('inactive', 0)->get();

        #Get object employee
        $sqlEmployee = "
                SELECT us.*, co.company_code, co.company_name, po.position_code, po.position_name, ar.area_code, ar.area_name
                FROM users us
                LEFT JOIN company co ON co.id = us.company_id
                LEFT JOIN position po ON po.id = us.position_id
                LEFT JOIN area ar ON ar.id = us.area_id

            ";

        $objEmployeeDB = DB::select(DB::raw($sqlEmployee));

        /************************************************************************************************************/
        /* Define variable */
        $arrCompanyCode = array();
        $arrAreaCode = array();
        $arrPositionCode = array();

        $arrDataNull = array();
        $iDN = 0;

        $arrDataError = array();
        $iDE = 0;

        $arrDataOverride = array();
        $iDO = 0;

        $arrLock = array();
        $iL = 0;

        $arrDataValid = array();
        $iDV = 0;

        $preCompanyCode = "";
        $preAreaCode    = "";
        $arrDPos        = array();
        $preYear        = "";
        $preFromMonth   = "";
        $preToMonth     = "";

        /************************************************************************************************************/
        foreach($objCompanyDB as $company){
            $arrCompanyCode[] = $company->company_code;
        }

        foreach($objAreaDB as $area){
            $arrAreaCode[] = $area->area_code;
        }

        foreach($objPositionDB as $position){
            $arrPositionCode[] = $position->position_code;
        }

        foreach($objGoalDB as $goal){
            $arrGoalCode[] = $goal->goal_code;
        }
        /************************************************************************************************************/


        foreach ($arrSheets as $arrSheet) {
            $sheet              = $objPHPExcel->getSheet($arrSheet);
            $highestRow         = $sheet->getHighestRow();
            $highestColumn      = $sheet->getHighestColumn();

            $yearApply          = $sheet->rangeToArray('G' . 4);
            $fromMonthApply     = $sheet->rangeToArray('G' . 5);
            $toMonthApply       = $sheet->rangeToArray('G' . 6);
            $companyCodeApply   = $sheet->rangeToArray('C' . 6);
            $areaCodeApply      = $sheet->rangeToArray('C' . 7);
            $positionCodeApply  = $sheet->rangeToArray('C' . 8);
            $titleExcel         = $sheet->rangeToArray('D' . 2);

            $year = (
                isset($yearApply[0][0])
                && $yearApply[0][0] != ''
                && $yearApply[0][0] != null
                && (int)$yearApply[0][0] >= 2015
            ) ? (int)$yearApply[0][0] : '';

            $fromMonth = (
                isset($fromMonthApply[0][0])
                && $fromMonthApply[0][0] != ''
                && $fromMonthApply[0][0] != null
                && (int)$fromMonthApply[0][0] > 0
                && (int)$fromMonthApply[0][0] <= 12
            ) ? (int)$fromMonthApply[0][0] : '';

            $toMonth = (
                isset($toMonthApply[0][0])
                && $toMonthApply[0][0] != ''
                && $toMonthApply[0][0] != null
                && (int)$toMonthApply[0][0] > 0
                && (int)$toMonthApply[0][0] <= 12
                && (int)$toMonthApply[0][0] >= $fromMonth
            ) ? (int)$toMonthApply[0][0] : $fromMonth;

            $companyCode = (
                isset($companyCodeApply[0][0])
                && $companyCodeApply[0][0] != ''
                && $companyCodeApply[0][0] != null
            ) ? $companyCodeApply[0][0] : '';

            $areaCode = (
                isset($areaCodeApply[0][0])
                && $areaCodeApply[0][0] != ''
                && $areaCodeApply[0][0] != null
            ) ? $areaCodeApply[0][0] : '';

            $positionCode = (
                isset($positionCodeApply[0][0])
                && $positionCodeApply[0][0] != ''
                && $positionCodeApply[0][0] != null
            ) ? $positionCodeApply[0][0] : '';

            $title = (
                isset($titleExcel[0][0])
                && $titleExcel[0][0] != ''
                && $titleExcel[0][0] != null
            ) ? $titleExcel[0][0] : '';

            #get Index of Highest Column in current sheet
            $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

            if($title           != ""
                && $year         != ""
                && $fromMonth    != ""
                && $companyCode  != ""
                && $areaCode     != ""
                && $positionCode != ""){
                if($preCompanyCode == ""
                    || strtolower(trim($companyCode)) == strtolower(trim($preCompanyCode))){
                    $preCompanyCode = $companyCode;
                }else{
                    Session::flash('message-errors', "Mỗi file excel import chỉ duy nhất mỗi Phòng/Đài/MBF HCM.");
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }

                if($preAreaCode == ""
                    || strtolower(trim($areaCode)) == strtolower(trim($preAreaCode))){
                    $preAreaCode = $areaCode;
                }else{
                    Session::flash('message-errors', "Mỗi file excel import chỉ duy nhất mỗi Tổ/Quận/Huyện.");
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }

                if($preYear == "" || $preYear == $year){
                    $preYear = $year;
                }else{
                    Session::flash('message-errors', "Năm áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }

                if($preFromMonth == "" || $preFromMonth == $fromMonth){
                    $preFromMonth = $fromMonth;
                }else{
                    Session::flash('message-errors', "Tháng bắt đầu áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }

                if($preToMonth == "" || $preToMonth == $toMonth){
                    $preToMonth = $toMonth;
                }else{
                    Session::flash('message-errors', "Tháng kết thúc áp dụng trên các sheet phải giống nhau.");
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }

                if(!in_array($positionCode, $arrDPos)){
                    $arrDPos[] = $positionCode;
                }else{
                    Session::flash('message-errors', "Chức danh trên các sheet không được trùng.");
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }

                /*******************************************************************************************************
                 * Kiểm tra với từng sheet tìm goal hợp lệ và những goal không hợp lệ
                 * Kiểm tra chức danh hợp lệ và không hợp lệ
                 * Kiểm tra chức danh đã tồn tại tỷ trọng
                 * Kiểm tra dữ liệu tại tháng đã khóa
                 * *****************************************************************************************************/
                /**
                 * Check title in excel file valid default tile sample
                 * If valid return 1 else return 0
                 */
                $isValidTitle = 0;
                if(strtolower($title) == strtolower(commonUtils::TITLE_IMPORT_GOAL_EMPLOYEE)){
                    $isValidTitle = 1;
                }

                /**
                 * Check companyCode in excel file valid companyCode in database
                 * If exist database return 1 else return 0
                 */
                $sheetCompanyId = -1;
                $isValidCompanyCode = 0;
                $sheetCompanyCode = '';
                foreach($objCompanyDB as $company){
                    if($company->company_code == $companyCode){
                        $isValidCompanyCode = 1;
                        $sheetCompanyId = $company->id;
                        $sheetCompanyCode = $company->company_code;
                        break;
                    }
                }

                /**
                 * Check areaCode in excel file valid areaCode in database
                 * If exist database return 1 else return 0
                 */
                $isValidAreaCode = 0;
                $sheetAreaId = -1;
                $sheetAreaCode = "";

                foreach($objAreaDB as $area){
                    if(
                        $area->area_code == $areaCode
                        && $sheetCompanyId != -1
                        && $area->company_id == $sheetCompanyId

                    ){
                        $isValidAreaCode = 1;
                        $sheetAreaId = $area->id;
                        $sheetAreaCode = $area->area_code;
                        break;
                    }
                }

                /**
                 * Check positionCode in excel file valid positionCode in database
                 * If exist database return 1 else return 0
                 */
                $isValidPositionCode = 0;
                $sheetPositionId = -1;
                $sheetPositionCode = "";
                foreach($objPositionDB as $position){
                    if($position->position_code == $positionCode){
                        $sheetPositionId = $position->id;
                        $sheetPositionCode = $position->position_code;
                        $isValidPositionCode = 1;
                        break;
                    }
                }

                $arrUnlock = array();
                $iUL = 0;

                /**
                 * Check data is locked
                 * if data is locked in all month, return array null data.
                 */
                $listMonthUnlock = "";
                $listMonthLock = "";
                for($m = $fromMonth; $m <= $toMonth; $m++){
                    /***************************************************************************************************
                     * Check locked
                     **************************************************************************************************/
                    $isLock = $this->checkLockData($year, $m, '', 3, $sheetCompanyId);
                    /*foreach($objLockDB as $lock){
                        if($lock->ofmonth == $m && $lock->ofyear == $year && $lock->lock == 1){
                            $isLock = 1;
                            break;
                        }
                    }*/
                    if($isLock == 1){
                        if($listMonthLock == ""){
                            $listMonthLock = $m;
                        }else{
                            $listMonthLock .= ', '.$m;
                        }

                    }else{
                        if($listMonthUnlock == ""){
                            $listMonthUnlock = $m;
                        }else{
                            $listMonthUnlock .= ','.$m;
                        }
                        $arrUnlock[$iUL]['arrSheet']= $arrSheet;
                        $arrUnlock[$iUL]['month']= $m;
                        $arrUnlock[$iUL]['year']= $year;
                        $iUL++;
                    }

                }

                if($listMonthLock != ""){
                    $arrLock[$iL]['sheetIndex']= $arrSheet;
                    $arrLock[$iL]['content']= 'Dữ liệu tháng '.$listMonthLock.' áp dụng năm '.$year.' đang khóa.';
                    $iL++;
                }

                $strLEmployee = ""; /*Chuỗi chứa vị trí chức danh cùng label column*/
                $listEmployeeHaveIL = "";
                $listEmployeeCodeNValid = "";
                $listEmployeeCodeValid = "";

                if($isValidTitle == 1
                    && $isValidCompanyCode == 1
                    && $isValidAreaCode == 1
                    && $isValidPositionCode == 1
                    && $positionCode != commonUtils::POSITION_CODE_TQ /*Không import kế hoạch cho nhân viên TQ vì đã import cùng lúc với import chức danh rồi*/
                    && count($arrUnlock) > 0){
                    $deniedArea = ($sAccessLevel ==  2 && $sCompanyId == $sheetCompanyId) ? 0 : 1;

                    if(
                        $sheetAreaId    != $sAreaId
                        && $deniedArea  == 1
                        && $sId         != 0
                        && $sId         != 3
                    ){
                        Session::flash('message-errors', 'Bạn không thể import Kế hoạch cho các Nhân viên thuộc Tổ/Quận/Huyện :<b>'.$areaCode.'</b><br/> Vui lòng liên hệ Quản trị viên để biết thên chi tiết!');
                        $this->clearSession();
                        Session::flash('type', 6);
                        return redirect('importGoal');
                    }
                    /***************************************************************************************************
                     * Get data to compare exist data in excel with data in database
                     **************************************************************************************************/
                    #Get object target position
                    $sqlTargetPosition = "
                        SELECT tp.*, co.company_code, co.company_name, po.position_code, po.position_name, ar.area_code, ar.area_name, g.goal_code
                        FROM target_position tp
                        LEFT JOIN company co ON co.id = tp.company_id
                        LEFT JOIN position po ON po.id = tp.position_id
                        LEFT JOIN goal g ON g.id = tp.goal_id
                        LEFT JOIN area ar ON ar.id = tp.area_id
                        WHERE tp.inactive = 0
                        AND tp.company_id = ".$sheetCompanyId."
                        AND tp.area_id = ".$sheetAreaId."
                        AND tp.position_id = ".$sheetPositionId."
                        AND tp.year = ".$year."
                        AND tp.month >=  ".$fromMonth."
                        AND tp.month <= ".$toMonth."

                    ";
                    $objTargetPositionDB = DB::select(DB::raw($sqlTargetPosition));

                    #Get object target employee
                    $sqlTargetEmployee = "
                        SELECT te.*, co.company_code, co.company_name, po.position_code, po.position_name, ar.area_code, ar.area_name, us.code, us.name
                        FROM target_employee te
                        LEFT JOIN company co ON co.id = te.company_id
                        LEFT JOIN position po ON po.id = te.position_id
                        LEFT JOIN area ar ON ar.id = te.area_id
                        LEFT JOIN users us ON us.id = te.user_id
                        WHERE te.inactive = 0
                        AND te.company_id = ".$sheetCompanyId."
                        AND te.area_id = ".$sheetAreaId."
                        AND te.position_id = ".$sheetPositionId."
                        AND te.year = ".$year."
                        AND te.month >=  ".$fromMonth."
                        AND te.month <= ".$toMonth."

                    ";
                    $objTargetEmployeeDB = DB::select(DB::raw($sqlTargetEmployee));
                    /*************************************************************************************************/
                    $arrEmployee = array();
                    $iEmp = 0;
                    foreach($objEmployeeDB as $employee){
                        if(
                            $employee->company_id == $sheetCompanyId
                            && $employee->area_id == $sheetAreaId
                            && $employee->position_id == $sheetPositionId
                        ){

                            $terminateDate = $employee->terminate_date;
                            $validTerDate = 1;

                            if($terminateDate != '0000-00-00'){
                                $terminateYear  = (int)substr($employee->terminate_date, 0, 4);
                                $terminateMonth = (int)substr($employee->terminate_date, 5, 2);

                                if($terminateYear <= $year && $terminateMonth >= $toMonth){
                                    $validTerDate = 0;
                                }
                            }else{
                                $validTerDate = 0;
                            }


                            if($validTerDate == 0){
                                $arrEmployee[$iEmp]['employeeId']   = $employee->id;
                                $arrEmployee[$iEmp]['employeeCode'] = $employee->code;
                                $iEmp++;
                            }
                        }
                    }

                    /*************************************************************************************************/
                    if(count($objTargetPositionDB) > 0 && count($arrEmployee) > 0){
                        for($c = 7; $c < $indexHighestColumn - 4; $c++){
                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($c);
                            $compareEmployeeCode = trim($sheet->rangeToArray(trim($currentColumn . '10'))[0][0]);

                            if(commonUtils::compareTwoString($compareEmployeeCode, 'Tổng Kế hoạch') != 1){
                                $existEmp = 0;
                                $dEmployeeId = -1;
                                foreach($arrEmployee as $dEmployee){
                                    if(commonUtils::compareTwoString($dEmployee['employeeCode'], $compareEmployeeCode) == 1){
                                        $existEmp = 1;
                                        $dEmployeeId = $dEmployee['employeeId'];
                                        break;
                                    }
                                }
                                if($existEmp == 1 && $dEmployeeId != -1){
                                    if($listEmployeeCodeValid == ""){
                                        $listEmployeeCodeValid =  $compareEmployeeCode.'@'.$dEmployeeId;
                                        $strLEmployee = $compareEmployeeCode.'@'.$currentColumn;
                                    }else{
                                        $listEmployeeCodeValid .=  ','.$compareEmployeeCode.'@'.$dEmployeeId;
                                        $strLEmployee .= ','.$compareEmployeeCode.'@'.$currentColumn;
                                    }
                                }else{
                                    if($listEmployeeCodeNValid == ""){
                                        $listEmployeeCodeNValid =  $compareEmployeeCode;
                                    }else{
                                        $listEmployeeCodeNValid .=  ', '.$compareEmployeeCode;
                                    }
                                }
                            }
                        }

                        if($listEmployeeCodeNValid != ""){
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_LIST_EMPLOYEE').'<b>'.$listEmployeeCodeNValid.'</b>.';
                            $iDE++;
                        }

                        /***********************************************************************************************
                         * Check and get list goal is valid
                         ***********************************************************************************************/

                        $listExcelGoalCodeValid = "";
                        $listExcelGoalCodeNotValid = "";
                        $listNullGoalCode = "";

                        for($r = $startRow; $r <= $highestRow; $r++){
                            $dataSheet = $sheet->rangeToArray('B' . $r . ':' . 'C' . $r, NULL, TRUE, FALSE);
                            $no = trim($dataSheet[0][0]);
                            $goalCode = trim($dataSheet[0][1]);

                            $goalCode = (isset($goalCode) && $goalCode != "" && $goalCode != null) ? $goalCode : '';

                            if($goalCode != ""){
                                if(in_array($goalCode, $arrGoalCode)){
                                    if($listExcelGoalCodeValid == ""){
                                        $listExcelGoalCodeValid = $goalCode;
                                    }else{
                                        $listExcelGoalCodeValid .= ','.$goalCode;
                                    }
                                }else{
                                    if($listExcelGoalCodeNotValid == ""){
                                        $listExcelGoalCodeNotValid = $no;
                                    }else{
                                        $listExcelGoalCodeNotValid .= ', '.$no;
                                    }
                                }
                            }else{
                                if($listNullGoalCode == ""){
                                    $listNullGoalCode = $no;
                                }else{
                                    $listNullGoalCode .= ', '.$no;
                                }
                            }

                        }

                        if($listNullGoalCode != ""){
                            $arrDataNull[$iDN]['sheetIndex'] = $arrSheet;
                            $arrDataNull[$iDN]['content'] = $this->config->get('constant.NULL_GOAL_CODE').$listNullGoalCode.'.';
                            $iDN++;
                        }

                        if($listExcelGoalCodeNotValid != ""){
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_LIST_GOAL_CODE').$listExcelGoalCodeNotValid.'.';
                            $iDE++;
                        }

                        if(
                            $listExcelGoalCodeValid != ""
                            && $listEmployeeCodeValid != ""
                        ){
                            $arrGoalHaveTarget = array();
                            $iGHT = 0;

                            $arrPHT = array();/*Array Position have target*/
                            $iPHT = 0;
                            for($m = $fromMonth; $m <= $toMonth; $m++){

                                $arrInsideGHT = array();
                                $iInside = 0;
                                foreach($objTargetPositionDB as $targetPosition){
                                    if(
                                        $targetPosition->month == $m
                                        && $targetPosition->target_value != 0
                                    ){
                                        if(count($arrInsideGHT) == 0){
                                            $arrInsideGHT[$iInside]['goalId']           = $targetPosition->goal_id;
                                            $arrInsideGHT[$iInside]['goalCode']         = $targetPosition->goal_code;
                                            $arrInsideGHT[$iInside]['importantLevel']   = $targetPosition->important_level;
                                            $arrInsideGHT[$iInside]['benchmark']        = $targetPosition->benchmark;
                                            $arrInsideGHT[$iInside]['calBenchmark']     = $targetPosition->cal_benchmark;
                                            $arrInsideGHT[$iInside]['goalType']         = $targetPosition->goal_type;
                                            $arrInsideGHT[$iInside]['unitId']           = $targetPosition->unit_id;
                                            $iInside++;
                                        }else{
                                            $existInside = 0;
                                            foreach($arrInsideGHT as $ght){
                                                if($ght['goalId'] == $targetPosition->goal_id){
                                                    $existInside = 1;
                                                    break;
                                                }
                                            }
                                            if($existInside == 0){
                                                $arrInsideGHT[$iInside]['goalId']           = $targetPosition->goal_id;
                                                $arrInsideGHT[$iInside]['goalCode']         = $targetPosition->goal_code;
                                                $arrInsideGHT[$iInside]['importantLevel']   = $targetPosition->important_level;
                                                $arrInsideGHT[$iInside]['benchmark']        = $targetPosition->benchmark;
                                                $arrInsideGHT[$iInside]['calBenchmark']     = $targetPosition->cal_benchmark;
                                                $arrInsideGHT[$iInside]['goalType']         = $targetPosition->goal_type;
                                                $arrInsideGHT[$iInside]['unitId']           = $targetPosition->unit_id;
                                                $iInside++;
                                            }
                                        }

                                        $arrPHT[$iPHT]['companyId']  = $sheetCompanyId;
                                        $arrPHT[$iPHT]['areaId']     = $sheetAreaId;
                                        $arrPHT[$iPHT]['positionId'] = $sheetPositionId;
                                        $arrPHT[$iPHT]['year']       = $year;
                                        $arrPHT[$iPHT]['month']      = $m;
                                        $iPHT++;
                                    }
                                }

                                if(count($arrInsideGHT) > 0){
                                    $arrGoalHaveTarget[$iGHT]['month'] = $m;
                                    $arrGoalHaveTarget[$iGHT]['arrGoalValid'] = $arrInsideGHT;
                                    $iGHT++;
                                }
                            }

                            if(count($arrPHT) == 0){
                                $dir = $fromMonth.' ➤ '.$toMonth.'/'.$year;
                                $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                $arrDataError[$iDE]['content'] = '<b>'.$positionCode.'</b> '.$this->config->get('constant.WARN_NOT_TARGET_POS').'<b>'.$dir.'</b>.';
                                $iDE++;
                            }else{

                                $arrMonthNP = array();
                                for($m = $fromMonth; $m <= $toMonth; $m++){
                                    $arrMonthNP[] = $m;
                                }

                                foreach($arrPHT as $phtData){

                                    for($np = 0; $np < count($arrMonthNP); $np++){
                                        if($arrMonthNP[$np] == $phtData['month']){
                                            $arrMonthNP[$np] = 0;
                                        }
                                    }
                                }

                                $arrDistinctMonth = array();

                                if(count($arrMonthNP) > 0){
                                    $listNot = "";
                                    foreach($arrMonthNP as $monthNP){
                                        if($monthNP != 0){
                                            if(!in_array($monthNP, $arrDistinctMonth)){
                                                $arrDistinctMonth[] = $monthNP;

                                                if($listNot == ""){
                                                    $listNot = $monthNP;
                                                }else{
                                                    $listNot .= ', '.$monthNP;
                                                }
                                            }
                                        }
                                    }
                                    if($listNot != ""){
                                        $dir = $listNot.'/'.$year;
                                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                                        $arrDataError[$iDE]['content'] = '<b>'.$positionCode.'</b> '.$this->config->get('constant.WARN_NOT_TARGET_POS').' <b> '.$dir.'</b>.';
                                        $iDE++;
                                    }
                                }
                            }

                            /*******************************************************************************************
                             * Get array employee have target
                             * Get list employee valid with
                             *******************************************************************************************/

                            foreach(explode(',', $listEmployeeCodeValid) as $iEmployeeCode){
                                $arrMonthO = array(); /* array Month Override*/
                                $oEmployeeId = "";
                                for($m = $fromMonth; $m <= $toMonth; $m++){
                                    foreach($objTargetEmployeeDB as $targetEmployee){
                                        if(
                                            $targetEmployee->code == explode('@',$iEmployeeCode)[0]
                                            && $targetEmployee->month == $m
                                        ){
                                            $oEmployeeId = $targetEmployee->user_id;
                                            if(!in_array($m, $arrMonthO)){
                                                $arrMonthO[] = $m;
                                            }
                                        }
                                    }
                                }

                                if(count($arrMonthO) > 0){
                                    $listMonthO = "";
                                    foreach($arrMonthO as $monthO){
                                        if($listMonthO == ""){
                                            $listMonthO = $monthO;
                                        }else{
                                            $listMonthO .= ', '.$monthO;
                                        }
                                    }
                                    $arrDataOverride[$iDO]['indexSheet'] = $arrSheet;
                                    $arrDataOverride[$iDO]['companyId'] = $sheetCompanyId;
                                    $arrDataOverride[$iDO]['companyCode'] = $companyCode;
                                    $arrDataOverride[$iDO]['areaId'] = $sheetAreaId;
                                    $arrDataOverride[$iDO]['areaCode'] = $areaCode;
                                    $arrDataOverride[$iDO]['positionId'] = $sheetPositionId;
                                    $arrDataOverride[$iDO]['positionCode'] = $positionCode;
                                    $arrDataOverride[$iDO]['employeeCode'] = $iEmployeeCode;
                                    $arrDataOverride[$iDO]['employeeId'] = $oEmployeeId;
                                    $arrDataOverride[$iDO]['year'] = $year;
                                    $arrDataOverride[$iDO]['listMonth'] = $listMonthO;
                                    $iDO++;
                                }
                                /***************************************************************************************/

                            }
                            $arrMonthValid = array();
                            foreach($arrUnlock as $unlock){
                                foreach($objTargetPositionDB as $mTP){
                                    if($unlock['month'] == $mTP->month && !in_array($mTP->month, $arrMonthValid)){
                                        $arrMonthValid[] = $mTP->month;
                                    }
                                }
                            }
                            /******************************************************************************************/
                            if(count($arrGoalHaveTarget) > 0 && count($arrMonthValid) > 0){
                                $arrDataValid[$iDV]['sheetIndex']            = $arrSheet;
                                $arrDataValid[$iDV]['companyId']             = $sheetCompanyId;
                                $arrDataValid[$iDV]['companyCode']           = $companyCode;
                                $arrDataValid[$iDV]['areaId']                = $sheetAreaId;
                                $arrDataValid[$iDV]['areaCode']              = $areaCode;
                                $arrDataValid[$iDV]['positionId']            = $sheetPositionId;
                                $arrDataValid[$iDV]['positionCode']          = $positionCode;
                                $arrDataValid[$iDV]['year']                  = $year;
                                $arrDataValid[$iDV]['arrMonthValid']         = $arrMonthValid;
                                $arrDataValid[$iDV]['listEmployeeCodeValid'] = $listEmployeeCodeValid;
                                $arrDataValid[$iDV]['arrGoalHaveTarget']     = $arrGoalHaveTarget;

                                $iDV++;
                            }
                        }
                    }else{
                        if(count($objTargetPositionDB) == 0){

                            $time = ($toMonth != $fromMonth) ? 'từ tháng <b>'.$fromMonth.'</b> đến tháng <b>'.$toMonth.'/'.$year.'</b>' : 'tháng <b>'.$fromMonth.'/'.$year.'</b>';

                            Session::flash('message-errors', 'Vui lòng import Kế hoạch cho các chức danh '.$time.' trước khi import kế hoạch cho nhân viên.');
                            $this->clearSession();
                            Session::flash('type', 6);
                            return redirect('importGoal');
                        }
                    }
                }else{
                    if($isValidTitle == 0){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_IMPORT_FILE_INVALID');
                        $iDE++;
                    }
                    if($isValidCompanyCode == 0){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_COMP_CODE_APPLY').' <b>'.$companyCode.'</b>.';
                        $iDE++;
                    }
                    if($isValidAreaCode == 0){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_AREA_CODE_APPLY').' <b>'.$areaCode.'</b>.';
                        $iDE++;
                    }
                    if($isValidTitle == 0){
                        $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                        $arrDataError[$iDE]['content'] = $this->config->get('constant.ERR_POS_CODE_APPLY').' <b>'.$positionCode.'</b>.';
                        $iDE++;
                    }
                    if(count($arrUnlock)  == 0){
                        if($fromMonth == $toMonth){
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = 'Dữ liệu tháng <b>'.$fromMonth.'</b> đang khóa.';
                            $iDE++;
                        }else{
                            $arrDataError[$iDE]['sheetIndex'] = $arrSheet;
                            $arrDataError[$iDE]['content'] = 'Dữ liệu từ tháng <b>'.$fromMonth.' </b>đến tháng <b>'.$toMonth.'</b> đang khóa.';
                            $iDE++;
                        }

                    }
                }
            }else{
                if($title == ""){
                    $arrDataNull[$iDN]['sheetIndex'] = $arrSheet;
                    $arrDataNull[$iDN]['content'] = $this->config->get('constant.NULL_TITLE_FILE');
                    $iDN++;
                }
                if($companyCode == ""){
                    $arrDataNull[$iDN]['sheetIndex'] = $arrSheet;
                    $arrDataNull[$iDN]['content'] = $this->config->get('constant.NULL_COMP_APPLY');
                    $iDN++;
                }
                if($areaCode == ""){
                    $arrDataNull[$iDN]['sheetIndex'] = $arrSheet;
                    $arrDataNull[$iDN]['content'] = $this->config->get('constant.NULL_AREA_APPLY');
                    $iDN++;
                }
                if($positionCode == ""){
                    $arrDataNull[$iDN]['sheetIndex'] = $arrSheet;
                    $arrDataNull[$iDN]['content'] = $this->config->get('constant.NULL_POS_APPLY');
                    $iDN++;
                }
                if($year == ""){
                    $arrDataNull[$iDN]['sheetIndex'] = $arrSheet;
                    $arrDataNull[$iDN]['content'] = $this->config->get('constant.NULL_YEAR_APPLY');
                    $iDN++;
                }
                if($fromMonth == ""){
                    $arrDataNull[$iDN]['sheetIndex'] = $arrSheet;
                    $arrDataNull[$iDN]['content'] = $this->config->get('constant.NULL_MONTH_APPLY');
                    $iDN++;
                }
            }
        }

        /************************************************************************************************************/
        if(count($arrDataOverride) > 0){
            foreach($arrSheets as $sheetIndexB){
                $strOverride = "";
                foreach($arrDataOverride as $override){
                    if($override['indexSheet'] == $sheetIndexB){
                        if($strOverride == ""){
                            $strOverride = '<b>&nbsp;&nbsp;&nbsp;- '.explode('@', $override['employeeCode'])[0] .'</b> đã tồn tại kế hoạch tháng: <b>'.$override['listMonth'].'/'.$override['year'].'</b>.';
                        }else{
                            $strOverride .= '<br/><b>&nbsp;&nbsp;&nbsp;- '.explode('@', $override['employeeCode'])[0].'</b> đã tồn tại kế hoạch tháng: <b>'.$override['listMonth'].'/'.$override['year'].'</b>.';
                        }
                    }
                }
                if($strOverride != ""){
                    if($strIssetDataShow == ""){
                        $strIssetDataShow = '<b>'.commonUtils::TITLE_IMPORT_GOAL_EMPLOYEE.'<hr>* '.$sheetNames[$sheetIndexB].'</b><br/>'.$strOverride;
                    }else{
                        $strIssetDataShow .= '<br/><b>* '.$sheetNames[$sheetIndexB].'</b><br/>'.$strOverride;
                    }
                }
            }
        }

        /************************************************************************************************************/
        $arrData = array();
        $arrData['arrDataValid']    = $arrDataValid;
        $arrData['arrSheets']       = $arrSheets;
        $arrData['arrDataOverride'] = $arrDataOverride;
        $arrData['arrDataError']    = $arrDataError;
        $arrData['arrDataNull']     = $arrDataNull;
        $arrData['objPositionDB']   = $objPositionDB;

        #Write session for action next
        Session::put('pathFile', $path);
        Session::put('startRow', $startRow);
        Session::put('curType', $typeImport);
        Session::put('strIssetDataShow', $strIssetDataShow);
        Session::put('curExcelFile', $rename);
        Session::put('chooseImport', 1);
        Session::put('dataImportMultiTE', $arrData);

        Session::flash('type', 6);
        return redirect('importGoal');
    }

    public function importMultiGoalEmployee(){
        $path          = Session::get('pathFile');
        $startRow      = Session::get('startRow');
        $actionUser    = Session::get('sid');
        $curExcelFile  = Session::get('curExcelFile');

        $arrData       = Session::get('dataImportMultiTE');

        $dir           = date('Y').'/'.date('m');
        $inputFileName = $path;

        $objPHPExcel = new PHPExcel();
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheetNames = $objPHPExcel->getSheetNames();

        $arrSheets          = $arrData['arrSheets'];
        $arrDataOverride    = $arrData['arrDataOverride'];
        $arrDataValid       = $arrData['arrDataValid'];
        $arrDataNull        = $arrData['arrDataNull'];
        $arrDataError       = $arrData['arrDataError'];
        $objPositionDB      = $arrData['objPositionDB'];

        $numOfSheet = count($arrSheets);
        $excelCompanyCode   = "";
        $excelAreaCode      = "";
         DB::beginTransaction();
         try{

        /**************************************************************************************************************/

            if(count($arrDataOverride) > 0){
                #Thực hiện xóa trùng

                $arrBack = array();
                $iB = 0;

                foreach ($arrDataOverride as $override) {
                    $iCompanyId     = $override['companyId'];
                    $iPositionId    = $override['positionId'];
                    $iAreaId        = $override['areaId'];
                    $iListMonth     = $override['listMonth'];
                    $iYear          = $override['year'];
                    $iEmployeeId    = $override['employeeId'];
                    foreach(explode(',', $iListMonth) as $iMonth){

                        if(count($arrBack) == 0){
                            $arrBack[$iB]['companyId']  = $iCompanyId;
                            $arrBack[$iB]['areaId']     = $iAreaId;
                            $arrBack[$iB]['positionId'] = $iPositionId;
                            $arrBack[$iB]['year']       = $iYear;
                            $arrBack[$iB]['month']      = $iMonth;
                            $iB++;
                        }else{
                            $exist = 0;
                            foreach($arrBack as $back){
                                if(
                                    $back['companyId']      == $iCompanyId
                                    && $back['areaId']      == $iAreaId
                                    && $back['positionId']  == $iPositionId
                                    && $back['year']        == $iYear
                                    && $back['month']       == $iMonth
                                ){
                                    $exist = 1;
                                }
                            }
                            if($exist == 0){
                                $arrBack[$iB]['companyId']  = $iCompanyId;
                                $arrBack[$iB]['areaId']     = $iAreaId;
                                $arrBack[$iB]['positionId'] = $iPositionId;
                                $arrBack[$iB]['year']       = $iYear;
                                $arrBack[$iB]['month']      = $iMonth;
                                $iB++;
                            }
                        }

                        $sqlDeleteExist = "
                        DELETE 	FROM target_employee
                        WHERE company_id = '" . $iCompanyId . "'
                        AND area_id = '" . $iAreaId . "'
                        AND position_id = '" . $iPositionId . "'
                        AND user_id = '" . $iEmployeeId . "'
                        AND year = '" . $iYear . "'
                        AND month = '" . $iMonth . "';
                    ";
                        DB::delete(DB::raw($sqlDeleteExist));
                    }
                }

                $positionTQId = -1;
                foreach($objPositionDB as $pos){
                    if($pos->position_code == commonUtils::POSITION_CODE_TQ){
                        $positionTQId = $pos->id;
                        break;
                    }
                }

                foreach($arrBack as $back){
                    $this->formatIPOPosition($back['companyId'], $back['areaId'], $back['positionId'], $back['year'], $back['month']);
                    $this->cal4PositionDiffTQ($back['companyId'], $back['areaId'], $back['positionId'], $back['year'], $back['month']);

                    if($positionTQId != -1){
                        $this->formatIPOPosition($back['companyId'], $back['areaId'], $positionTQId, $back['year'], $back['month']);
                        $this->cal4PositionTQ($back['companyId'], $back['areaId'], $positionTQId, $back['year'], $back['month'], $actionUser);
                    }
                }

                $this->formatIPOArea($back['companyId'], $back['areaId'], $back['year'], $back['month']);
                $this->calKpi4Area($back['companyId'], $back['areaId'], $back['year'], $back['month'], $actionUser);

                $comApplyDate = $this->getApplyDate4Company($back['companyId'], $back['year'], '');
                if($comApplyDate != ""){
                    $this->formatIPOCompany($back['companyId'], $comApplyDate);
                    $this->calKpi4Company($back['companyId'], $back['year'], 1, 12, $comApplyDate, $actionUser);
                    $corApplyDate = $this->getApplyDate4Corporation($back['year']);
                    if($corApplyDate != ""){
                        $this->formatIPOCorporation($corApplyDate);
                        $this->calKpi4Corporation($corApplyDate, $comApplyDate, $actionUser);
                    }else{
                        Session::flash('message-errors', '<b>Import Kế hoạch cho Nhân viên</b><hr>'.'Vui lòng import tỷ trọng cho Công ty Mobifone năm <b> '.$back['year'].'</b> trước khi import Kế hoạch cho Nhân viên.');
                        $this->clearSession();
                        Session::flash('type', 6);
                        return redirect('importGoal');
                    }
                }else{
                    Session::flash('message-errors', '<b>Import Kế hoạch cho Nhân viên</b><hr>'.'Vui lòng import tỷ trọng cho Phòng/Đài/MBF HCM năm <b> '.$back['year'].'</b> trước khi import Kế hoạch cho Nhân viên.');
                    $this->clearSession();
                    Session::flash('type', 6);
                    return redirect('importGoal');
                }

                /***********************************************************************************************************/
                #Write log override here
                $dataLog = array(
                    'functionName' => 'Kế hoạch cho nhân viên (importMultiGoalEmployee)',
                    'action'       => commonUtils::ACTION_OVERRIDE,
                    'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'newValue'     => $curExcelFile,
                    'createdUser'  => $actionUser
                );
                $this->writeLog($dataLog);
            }

            $arrSuccess = array();
            $iSuccess = 0;
                 $numSheetSuccess = 0;
            /**************************************************************************************************************/
            foreach($arrSheets as $sheetIndex){

                foreach($arrDataValid as $dataValid){

                    $dvSheetIndex            = $dataValid['sheetIndex'];
                    $dvCompanyId             = $dataValid['companyId'];
                    $dvAreaId                = $dataValid['areaId'];
                    $dvAreaCode              = $dataValid['areaCode'];
                    $dvPositionId            = $dataValid['positionId'];
                    $dvPositionCode          = $dataValid['positionCode'];
                    $dvYear                  = $dataValid['year'];
                    $dvArrMonthValid         = $dataValid['arrMonthValid'];
                    $dvListEmployeeCodeValid = $dataValid['listEmployeeCodeValid'];
                    $dvArrGoalHaveTarget     = $dataValid['arrGoalHaveTarget'];

                    $excelAreaCode = $dvAreaCode;

                    $arrDataInsert = array();

                    if($sheetIndex == $dvSheetIndex){

                        $sheet          = $objPHPExcel->getSheet($sheetIndex);
                        $highestRow     = $sheet->getHighestRow();
                        $highestColumn  = $sheet->getHighestColumn();

                        #get Index of Highest Column in current sheet
                        $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

                        for($c = 6; $c < $indexHighestColumn - 4; $c++) {
                            $currentColumn = PHPExcel_Cell::stringFromColumnIndex($c);
                            $compareEmployeeCode = trim($sheet->rangeToArray(trim($currentColumn . '10'))[0][0]);

                            foreach(explode(',', $dvListEmployeeCodeValid) as $employeeCodeValid){
                                if(explode('@', $employeeCodeValid)[0] == $compareEmployeeCode
                                ){
                                    $numSuccess = 0;
                                    for($r = $startRow; $r <= $highestRow; $r++){
                                        $dataSheet = $sheet->rangeToArray('C' . $r . ':' . $currentColumn . $r, NULL, TRUE, FALSE);

                                        $code = trim($dataSheet[0][0]);
                                        $targetValue = trim($dataSheet[0][$c-2]);/* $c-2 do chỉ lặp từ cột C bỏ 2 cột A,B nên index max phải giảm 2*/
                                        $targetValue = (isset($targetValue) && $targetValue != '' && $targetValue != null && is_numeric($targetValue)) ? $targetValue : 0;


                                        foreach($dvArrMonthValid as $monthValid){
                                            foreach($dvArrGoalHaveTarget as $mGoalHaveTarget){
                                                $mgMonth        = $mGoalHaveTarget['month'];
                                                $mgArrGoalValid = $mGoalHaveTarget['arrGoalValid'];
                                                if($mgMonth == $monthValid){

                                                    foreach($mgArrGoalValid as $goalValid){
                                                        if($goalValid['goalCode'] == $code && $targetValue != 0){
                                                            $dataTargetEmployee = array(
                                                                'company_id'        => $dvCompanyId,
                                                                'area_id'           => $dvAreaId,
                                                                'position_id'       => $dvPositionId,
                                                                'user_id'           => explode('@', $employeeCodeValid)[1],
                                                                'unit_id'           => $goalValid['unitId'],
                                                                'goal_id'           => $goalValid['goalId'],
                                                                'goal_type'         => $goalValid['goalType'],
                                                                'month'             => $mgMonth,
                                                                'year'              => $dvYear,
                                                                'benchmark'         => $goalValid['benchmark'],
                                                                'cal_benchmark'     => $goalValid['calBenchmark'],
                                                                'goal_level'        => 1,
                                                                'target_value'      => $targetValue,
                                                                'important_level'   => $goalValid['importantLevel'],
                                                                'created_user'      => $actionUser
                                                            );
                                                            $arrDataInsert[] = $dataTargetEmployee;
                                                            $numSuccess++;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($numSuccess > 0){
                                        $arrSuccess[$iSuccess]['sheetIndex'] = $sheetIndex;
                                        $arrSuccess[$iSuccess]['employeeCode'] = $compareEmployeeCode;
                                        $arrSuccess[$iSuccess]['arrMonth'] = $dvArrMonthValid;
                                        $arrSuccess[$iSuccess]['year'] = $dvYear;
                                        $arrSuccess[$iSuccess]['numRows'] = $numSuccess;
                                        $iSuccess++;
                                    }
                                }
                            }
                        }
                    }

                    if(count($arrDataInsert) > 0){
                        $numSheetSuccess = $numSheetSuccess + 1;
                        DB::table('target_employee')->insert($arrDataInsert);
                    }
                }
            }

            /**************************************************************************************************************/

             #Write log override here
             $dataLog = array(
                 'functionName' => 'Kế hoạch cho nhân viên (importMultiGoalEmployee)',
                 'action'       => commonUtils::ACTION_IMPORT,
                 'url'          => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                 'newValue'     => $curExcelFile,
                 'createdUser'  => $actionUser
             );
             $this->writeLog($dataLog);
        DB::commit();
    }catch (Exception $e) {
        DB::rollback();
    }
        $strDataError = "";
        $strDataSuccess = "";
        if(count($arrDataNull) > 0 || count($arrDataError) > 0 || count($arrSuccess) > 0){
            foreach($arrSheets as $sheetIndex){
                $strNull = "";
                if(count($arrDataNull) > 0){
                    foreach($arrDataNull as $dataNull){
                        if($dataNull['sheetIndex'] == $sheetIndex){
                            $strNull .= '<br/>&nbsp;&nbsp;&nbsp;- '.$dataNull['content'];
                        }
                    }
                }
                $strError = "";
                if(count($arrDataError) > 0){
                    foreach($arrDataError as $dataError){
                        if($dataError['sheetIndex'] == $sheetIndex){
                            $strError .= '<br/>&nbsp;&nbsp;&nbsp;- '.$dataError['content'];
                        }
                    }
                }
                if($strError != "" || $strNull != ""){
                    if($strDataError == ""){
                        //echo 1; die;
                        $strDataError = '<b>'.commonUtils::TITLE_IMPORT_GOAL_EMPLOYEE.'<hr/>* '.$sheetNames[$sheetIndex].':</b>';
                        //echo $strDataError; die;
                    }else{
                        $strDataError .= '<br/><b>* '.$sheetNames[$sheetIndex].':</b>';
                    }
                    if($strNull != ""){
                        $strDataError .= $strNull;
                    }
                    if($strError != ""){
                        $strDataError .= $strError;
                    }
                }
                /******************************************************************************************************/
                $strSuccess = "";
                if(count($arrSuccess) > 0){
                    foreach($arrSuccess as $dataSuccess){
                        if($dataSuccess['sheetIndex'] == $sheetIndex){
                            $listMonth = "";
                            foreach($dataSuccess['arrMonth'] as $month){
                                if($listMonth == ""){
                                    $listMonth = $month;
                                }else{
                                    $listMonth .= ', '.$month;
                                }
                            }
                            $listMonth .= '/'.$dataSuccess['year'];
                            if($strSuccess == ""){
                                $strSuccess = '&nbsp;&nbsp;&nbsp;- <b>'.$dataSuccess['employeeCode'].'</b> đã import kế hoạch tháng <b>'.$listMonth.': '.$dataSuccess['numRows'].'</b> dòng.';
                            }else{
                                $strSuccess .= '<br/>&nbsp;&nbsp;&nbsp;- <b>'.$dataSuccess['employeeCode'].'</b> đã import kế hoạch tháng <b>'.$listMonth.': '.$dataSuccess['numRows'].'</b> dòng.';
                            }
                        }
                    }
                }

                if($strSuccess != ""){
                    if($strDataSuccess == ""){
                        //echo 1; die;
                        $strDataSuccess = '<b>'.commonUtils::TITLE_IMPORT_GOAL_EMPLOYEE.'</b> '
                            .'<br/>* Tổ/Quận/Huyện: '.$excelAreaCode
                            .'<br/>* Số sheet đã import: '.$numOfSheet
                            .'<br/>* Số sheet đã import thành công: '.$numSheetSuccess
                            .'<hr/><b>* '.$sheetNames[$sheetIndex].':</b><br>';
                        //echo $strDataError; die;
                    }else{
                        $strDataSuccess .= '<br/><b>* '.$sheetNames[$sheetIndex].':</b><br>';
                    }
                    $strDataSuccess .= $strSuccess;
                }
            }
        }
        if($strDataError != ""){
            Session::flash('message-errors', $strDataError);
        }

        if($strDataSuccess != ""){
            Session::flash('message-success', $strDataSuccess);
        }
        $this->clearSession();
        Session::flash('type', 6);
        return redirect('importGoal');
    }

    public function importPerformPositionCTV($path, $startRow, $typeImport, $rename, $arrDataSession)
    {
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        $sCompanyId     = $arrDataSession['sCompanyId'];
        $sAreaId        = $arrDataSession['sAreaId'];
        $sPositionId    = $arrDataSession['sPositionId'];

        if($sAccessLevel != 3 && $sAccessLevel != 4){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 11);
            return redirect('importGoal');
        }

        #Load by Sheet Name
        $inputFileName = $path;
        $objPHPExcel = new PHPExcel();
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);

        #Check exist data in this month of year
        $title = $sheet->rangeToArray('C' . 2);
        $like = $title[0][0];
        if (strtolower(trim($like)) != strtolower(commonUtils::TITLE_IMPORT_PERFORM_CTV)) {
            Session::flash('message-errors', '<b>Import Thực hiện cho Cộng Tác Viên</b><hr>'.$this->config->get('constant.ERR_IMPORT_FILE_INVALID'));
            $this->clearSession();
            Session::flash('type', 11);
            return redirect('importGoal');
        }

        /* *************************************************************************************************************
         * get all data before loop
         * ************************************************************************************************************/

        $actionUser = Session::get('sid');

        $objLockDB = DB::table('lock')->where('inactive', 0)->get();

        #object company
        $objCompanyDB = DB::table('company')->where('inactive', 0)->get();

        #object area
        $objAreaDB = DB::table('area')->where('inactive', 0)->get();

        #object position
        $objPositionDB = DB::table('position')->where('inactive', 0)->get();

        #object goal
        $objGoalDB = DB::table('goal')->where('inactive', 0)->get();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $yearApply          = $sheet->rangeToArray('E' . 4)[0][0];
        $monthApply         = $sheet->rangeToArray('E' . 5)[0][0];
        $companyCodeApply   = trim($sheet->rangeToArray('B' . 6)[0][0]);
        $areaCodeApply      = trim($sheet->rangeToArray('B' . 7)[0][0]);
        $positionCodeApply  = trim($sheet->rangeToArray('B' . 8)[0][0]);

        $year               = (isset($yearApply) && is_numeric((int)$yearApply)) ? $yearApply : '';
        $month              = (isset($monthApply) && is_numeric((int)$monthApply) && (int)$monthApply >= 1 && (int)$monthApply <= 12) ? (int)$monthApply : '';
        $companyCode        = (isset($companyCodeApply) && $companyCodeApply != null) ? $companyCodeApply : '';
        $areaCode           = (isset($areaCodeApply) && $areaCodeApply != null) ? $areaCodeApply : '';
        $positionCode       = (isset($positionCodeApply) && $positionCodeApply != null) ? $positionCodeApply : '';


        if($year               != ""
            && $month           != ""
            && $companyCode     != ""
            && $areaCode        != ""
            && $positionCode    != ""){

            /* *****************************************************************************************************
             * check companyCode is valid
             * If exist companyCode return companyId else return -1
             * ****************************************************************************************************/
            $companyId = -1;
            foreach($objCompanyDB as $company){
                if($company->company_code == $companyCode){
                    $companyId = $company->id;
                    break;
                }
            }

            /* *****************************************************************************************************
             * check areaCode is valid
             * If exist areaCode return areaId else return -1
             * ****************************************************************************************************/
            $areaId = -1;
            foreach($objAreaDB as $area){
                if(
                    $area->area_code == $areaCode
                    && $area->company_id == $companyId
                    && $companyId != -1
                ){
                    $areaId = $area->id;
                    break;
                }
            }

            if($areaId != $sAreaId){
                Session::flash('message-errors', 'Bạn không thể import Kế hoạch/ Thực hiện cho chức danh Cộng tác viên thuộc Tổ/Quận/Huyện :<b>'.
                                                $areaCode.'</b><br/> Vui lòng liên hệ Quản trị viên để biết thên chi tiết!');
                $this->clearSession();
                Session::flash('type', 11);
                return redirect('importGoal');
            }

            /* *****************************************************************************************************
             * check positionCode is valid
             * If exist positionCode return positionId else return -1
             * ****************************************************************************************************/
            $positionId = -1;
            foreach($objPositionDB as $position){
                if($position->position_code == $positionCode){
                    $positionId = $position->id;
                    break;
                }
            }
            $idCVKHCN = -1;
            foreach($objPositionDB as $position){
                if($position->position_code == commonUtils::POSITION_CODE_CV_KHCN){
                    $idCVKHCN = $position->id;
                    break;
                }
            }
            /* *****************************************************************************************************
             * check this month is locked
             * ****************************************************************************************************/
            $isLock = 0;
            foreach($objLockDB as $lock){
                if( $lock->ofmonth   == $month
                    && $lock->ofyear == $year
                    && $lock->lock   == 1
                ){
                    $isLock = 1;
                    break;
                }
            }

            if($companyId          != -1
                && $areaId          != -1
                && $positionId      != -1
                && $idCVKHCN        != -1
                && $isLock          != 1){
                $sqlILP = "
                        SELECT *
                        FROM important_level_position
                        WHERE inactive = 0
                        AND company_id  = ".$companyId."
                        AND area_id  = ".$areaId."
                        AND position_id  = ".$idCVKHCN."
                        AND year  = ".$year."
                        AND month  = ".$month."";
                $objILPDB = DB::select(DB::raw($sqlILP));

                $sqlTargetCTV = "
                    SELECT *
                    FROM target_position
                    WHERE inactive = 0
                    AND company_id  = ".$companyId."
                    AND area_id  = ".$areaId."
                    AND position_id  = ".$positionId."
                    AND year  = ".$year."
                    AND month  = ".$month."";
                $objTargetCTV = DB::select(DB::raw($sqlTargetCTV));

                if(count($objILPDB) > 0){
                    $arrDataInsert = array();
                    $listGoalImported = "";
                    $numUpdate = 0;
                    for ($row = $startRow; $row <= $highestRow; $row++) {
                        #Read a row of data into an array
                        $dataSheet = $sheet->rangeToArray('A' . $row . ':G' . $row, NULL, TRUE, FALSE);

                        $no          = trim($dataSheet[0][0]);
                        $code        = trim($dataSheet[0][1]);
                        $targetValue = trim($dataSheet[0][5]);
                        $implement   = trim($dataSheet[0][6]);

                        $no          = (isset($no) && $no != null) ? $no : '?';
                        $code        = (isset($code) && $code != null) ? $code : '';
                        $implement   = (isset($implement) && is_numeric($implement) && $implement != 0) ? $implement : 0;
                        $targetValue = (isset($targetValue) && is_numeric($targetValue) && $targetValue != 0) ? $targetValue : 0;
                        if($code!= "" && $implement != 0){
                            $goalId     = -1;
                            $goalType   = -1;
                            $unitId     = -1;
                            foreach($objGoalDB as $goal){
                                if($goal->goal_code == $code){
                                    $goalId     = $goal->id;
                                    $goalType   = $goal->goal_type;
                                    $unitId     = $goal->unit_id;
                                    break;
                                }
                            }

                            if($goalId != -1){
                                $validGoal = -1;
                                foreach($objILPDB as $ilp){
                                    if($ilp->goal_id == $goalId){
                                        $validGoal = 1;
                                        break;
                                    }
                                }

                                $existPerform = -1;
                                foreach($objTargetCTV as $targetCTV){
                                    if(
                                        $targetCTV->goal_id == $goalId
                                        && $targetCTV->implement != 0
                                    ){
                                        $existPerform = $targetCTV->id;
                                        break;
                                    }
                                }

                                if($validGoal == 1){
                                    if($existPerform != -1){

                                        $uPerformCTV = array(
                                            'implement'         => $implement,
                                            'target_value'      => $targetValue,
                                            'updated_user'      => $actionUser
                                        );

                                        DB::table('target_position')->where('id', $existPerform)->update($uPerformCTV);

                                        if($listGoalImported == ""){
                                            $listGoalImported = $code;
                                        }else{
                                            $listGoalImported .= ', '.$code;
                                        }
                                        $numUpdate++;
                                    }else{
                                        $iPerformCTV = array(
                                            'company_id'        => $companyId,
                                            'area_id'           => $areaId,
                                            'position_id'       => $positionId,
                                            'year'              => $year,
                                            'month'             => $month,
                                            'goal_id'           => $goalId,
                                            'goal_type'         => $goalType,
                                            'goal_level'        => 1,
                                            'unit_id'           => $unitId,
                                            'goal_id'           => $goalId,
                                            'implement'         => $implement,
                                            'target_value'      => $targetValue,
                                            'updated_user'      => $actionUser
                                        );
                                        $arrDataInsert[] = $iPerformCTV;

                                        if($listGoalImported == ""){
                                            $listGoalImported = $code;
                                        }else{
                                            $listGoalImported .= ', '.$code;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if(count($arrDataInsert) > 0){
                        DB::table('target_position')->insert($arrDataInsert);
                    }
                    if($listGoalImported != ""){
                        $num = count($arrDataInsert)+$numUpdate;
                        $str = "<b>".commonUtils::TITLE_IMPORT_PERFORM_CTV."<hr/></b>Đã import thành công thực hiện CTV tháng <b>".$month.'/'.$year."</b> : <b>".$num."</b> dòng."
                            ."<br/>&nbsp;&nbsp;&nbsp; + Danh sách mục tiêu đã import: ".$listGoalImported
                        ;
                        Session::flash('message-success', $str);
                    }

                    $this->formatIPOPosition($companyId, $areaId, $idCVKHCN, $year, $month);
                    $this->cal4PositionDiffTQ($companyId, $areaId, $idCVKHCN, $year, $month);

                    $posTQId = -1;
                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_TQ){
                            $posTQId = $position->id;
                            break;
                        }
                    }
                    $this->formatIPOPosition($companyId, $areaId, $posTQId, $year, $month);
                    $this->cal4PositionTQ($companyId, $areaId, $posTQId, $year, $month, $actionUser);

                    $this->formatIPOArea($companyId, $areaId, $year, $month);
                    $this->calKpi4Area($companyId, $areaId, $year, $month, $actionUser);

                    $comApplyDate = $this->getApplyDate4Company($companyId, $year, '');
                    $this->formatIPOCompany($companyId, $comApplyDate);
                    $this->calKpi4Company($companyId, $year, 1, 12, $comApplyDate, $actionUser);

                    $corApplyDate = $this->getApplyDate4Corporation($year);
                    $this->formatIPOCorporation($corApplyDate);
                    $this->calKpi4Corporation($corApplyDate, $comApplyDate, $actionUser);

                }
            }
        }else{
        }

        $this->clearSession();
        Session::flash('type', 11);
        return redirect('importGoal');
    }

    public  function beforeImportMultiPerformForEmployee($path, $startRow, $typeImport, $rename, $monthYear, $listSheetIndex, $arrDataSession)
    {
        $sAccessLevel   = $arrDataSession['sAccessLevel'];
        $sCompanyId     = $arrDataSession['sCompanyId'];
        $sAreaId        = $arrDataSession['sAreaId'];
        $sPositionId    = $arrDataSession['sPositionId'];
        $sId            = $arrDataSession['sId'];

        if(
            $sAccessLevel != 3
            && $sAccessLevel != 4
            && $sAccessLevel != 2
            && $sId != 0
            && $sId != 3
        ){
            Session::flash('message-errors', $this->config->get('constant.ACCESS_DENIED'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }

        $inputFileName = $path;
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheetNames = $objPHPExcel->getSheetNames();

        if($listSheetIndex == ""){
            #Call back error when array index sheet is null
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_NULL'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }

        $arrSheets = commonUtils::getArraySheets($listSheetIndex);

        if(count($arrSheets) == 0){
            #Call back error when array index sheet is not valid
            Session::flash('message-errors', $this->config->get('constant.ERR_INDEX_SHEET_INVALID'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }else{
            $numOfSheet =  $objPHPExcel->getSheetCount();
            foreach($arrSheets as $checkExist){
                if($checkExist >= $numOfSheet){
                    Session::flash('message-errors', $this->config->get('constant.ERR_OVER_NUMBER_SHEET'));
                    $this->clearSession();
                    Session::flash('type', 8);
                    return redirect('importGoal');
                }
            }
        }
        /* *************************************************************************************************************
         * get all data before loop
         * ************************************************************************************************************/

        $objLockDB = DB::table('lock')->where('inactive', 0)->get();

        #object company
        $objCompanyDB = DB::table('company')->where('inactive', 0)->get();

        #object area
        $objAreaDB = DB::table('area')->where('inactive', 0)->get();

        #object position
        $objPositionDB = DB::table('position')->where('inactive', 0)->get();

        #object goal
        $objGoalDB = DB::table('goal')->where('inactive', 0)->get();

        #object goal
        $sqlEmployee = "
            SELECT u.*, p.position_code
            FROM users u
            LEFT JOIN position p ON p.id = u.position_id
            WHERE u.admin = 0
        ";
        $objEmployeeDB = DB::select(DB::raw($sqlEmployee));
        //$objEmployeeDB = DB::table('users')->where('inactive', 0)->where('admin', 0)->get();

        if(count($objCompanyDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_COMPANY_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }

        if(count($objAreaDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_AREA_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }

        if(count($objPositionDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_POSITION_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }

        if(count($objGoalDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_GOAL_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }

        if(count($objEmployeeDB) == 0){
            Session::flash('message-errors', $this->config->get('constant.NULL_EMPLOYEE_DB').$this->config->get('constant.SUFF_IMPORT_PE_NULL_DB'));
            $this->clearSession();
            Session::flash('type', 8);
            return redirect('importGoal');
        }

        /* *************************************************************************************************************
         * Begin loop array sheets
         * Get array data valid for import perform
         * ************************************************************************************************************/

        $arrDataError = array();
        $iDE = 0;

        $arrDataValid = array();
        $iDV = 0;

        $arrDataOverride = array();
        $iDO = 0;
        $preAreaCode = "";

        foreach ($arrSheets as $arrSheet) {

            $sheet              = $objPHPExcel->getSheet($arrSheet);
            $yearApply          = $sheet->rangeToArray('F' . 4)[0][0];
            $monthApply         = $sheet->rangeToArray('F' . 5)[0][0];
            $companyCodeApply   = trim($sheet->rangeToArray('C' . 6)[0][0]);
            $areaCodeApply      = trim($sheet->rangeToArray('C' . 7)[0][0]);
            $positionCodeApply  = trim($sheet->rangeToArray('C' . 8)[0][0]);
            $titleExcel         = trim($sheet->rangeToArray('D' . 2)[0][0]);

            $highestRow         = $sheet->getHighestRow();
            $highestColumn      = $sheet->getHighestColumn();

            $year               = (isset($yearApply) && is_numeric((int)$yearApply)) ? (int)$yearApply : '';
            $month              = (isset($monthApply) && is_numeric((int)$monthApply) && (int)$monthApply >= 1 && (int)$monthApply <= 12) ? (int)$monthApply : '';
            $companyCode        = (isset($companyCodeApply) && $companyCodeApply != null) ? $companyCodeApply : '';
            $areaCode           = (isset($areaCodeApply) && $areaCodeApply != null) ? $areaCodeApply : '';
            $positionCode       = (isset($positionCodeApply) && $positionCodeApply != null) ? $positionCodeApply : '';
            $title              = (isset($titleExcel) && $titleExcel != null) ? $titleExcel : '';

            if($title           != ""
                && $companyCode  != ""
                && $areaCode     != ""
                && $positionCode != ""
                && $year         != ""
                && $month        != ""){

                /* *****************************************************************************************************
                 * check type import is valid
                 * ****************************************************************************************************/
                $isValidTitle = 0;
                if(strtolower(trim($title)) == strtolower(commonUtils::TITLE_IMPORT_PERFORM_EMPLOYEE)){
                    $isValidTitle = 1;
                }
                /* *****************************************************************************************************
                 * check companyCode is valid
                 * If exist companyCode return companyId else return -1
                 * ****************************************************************************************************/
                $companyId = -1;
                foreach($objCompanyDB as $company){
                    if($company->company_code == $companyCode){
                        $companyId = $company->id;
                        break;
                    }
                }

                /* *****************************************************************************************************
                 * check areaCode is valid
                 * If exist areaCode return areaId else return -1
                 * ****************************************************************************************************/
                $areaId = -1;
                foreach($objAreaDB as $area){
                    if($area->area_code == $areaCode
                        && $area->company_id == $companyId
                        && $companyId != -1){
                        $areaId = $area->id;
                        break;
                    }
                }

                /* *****************************************************************************************************
                 * check positionCode is valid
                 * If exist positionCode return positionId else return -1
                 * ****************************************************************************************************/
                $positionId = -1;
                foreach($objPositionDB as $position){
                    if($position->position_code == $positionCode){
                        $positionId = $position->id;
                        break;
                    }
                }

                /* *****************************************************************************************************
                 * check this month is locked
                 * ****************************************************************************************************/
                $isLock = $this->checkLockData($year, $month, '', 4, $companyId);

                /* *****************************************************************************************************
                 * check area have employee with position TQ
                 * If exist return 1 else return -1
                 * ****************************************************************************************************/
                $isTQ = -1;

                foreach($objEmployeeDB as $employee){
                    if(
                        $employee->position_code == commonUtils::POSITION_CODE_TQ
                        && $employee->company_id == $companyId
                        && $employee->area_id    == $areaId){
                        $isTQ = 1;
                        break;
                    }
                }

                if($companyId          != -1
                    && $areaId          != -1
                    && $positionId      != -1
                    && $isValidTitle    != 0
                    && $isLock          != 1
                    && $isTQ            != -1){

                    if($sAccessLevel == 2){
                        if(
                            $sCompanyId != $companyId
                        ){
                            Session::flash('message-errors', 'Bạn không thể import Thực hiện cho các Nhân viên thuộc Tổ/Quận/Huyện :<b>'.
                                $areaCode.'</b> / Phòng/Đài/MBF HCM: <b>'.$companyCode.'</b><br/> Vui lòng liên hệ Quản trị viên để biết thên chi tiết!');
                            $this->clearSession();
                            Session::flash('type', 8);
                            return redirect('importGoal');
                        }
                    }else{
                        if(
                            $areaId != $sAreaId
                            && $sId != 0
                            && $sId != 3
                        ){
                            Session::flash('message-errors', 'Bạn không thể import Thực hiện cho các Nhân viên thuộc Tổ/Quận/Huyện :<b>'.
                                $areaCode.'</b><br/> Vui lòng liên hệ Quản trị viên để biết thên chi tiết!');
                            $this->clearSession();
                            Session::flash('type', 8);
                            return redirect('importGoal');
                        }
                    }



                    /* *************************************************************************************************
                     * check have targetEmployee
                     * ************************************************************************************************/
                    $sqlTargetEmployee = "
                        SELECT *
                        FROM target_employee
                        WHERE inactive = 0
                        AND company_id  = ".$companyId."
                        AND area_id  = ".$areaId."
                        AND position_id  = ".$positionId."
                        AND year  = ".$year."
                        AND month  = ".$month."
                    ";
                    $objTargetEmployeeDB = DB::select(DB::raw($sqlTargetEmployee));

                    if(count($objTargetEmployeeDB) > 0){
                        /* *********************************************************************************************
                         * get array goal valid in this sheet.
                         * ********************************************************************************************/
                        $arrExcelGoalValid = array();
                        $iEGV = 0;

                        $listGoalNull   = "";
                        $listGoalErrors = "";

                        for ($row = $startRow; $row <= $highestRow; $row++) {
                            #Read a row of data into an array
                            $dataSheet = $sheet->rangeToArray('B' . $row . ':C' . $row, NULL, TRUE, FALSE);

                            $no     = trim($dataSheet[0][0]);
                            $code   = trim($dataSheet[0][1]);

                            $no     = (isset($no) && $no != null) ? $no : '?';
                            $code   = (isset($code) && $code != null) ? $code : '';

                            if($code != ''){
                                $goalId   = -1;
                                $goalType = -1;
                                $formula  = -1;
                                $parentId = -1;

                                foreach($objGoalDB as $goal){
                                    if($goal->goal_code == $code){
                                        $goalId   = $goal->id;
                                        $goalType = $goal->goal_type;
                                        $formula  = $goal->formula;
                                        $parentId = $goal->parent_id;
                                        break;
                                    }
                                }
                                if($goalId != -1){
                                    if(count($arrExcelGoalValid) == 0){
                                        $arrExcelGoalValid[$iEGV]['goalId']   = $goalId;
                                        $arrExcelGoalValid[$iEGV]['goalCode'] = $code;
                                        $arrExcelGoalValid[$iEGV]['goalType'] = $goalType;
                                        $arrExcelGoalValid[$iEGV]['formula']  = $formula;
                                        $arrExcelGoalValid[$iEGV]['parentId'] = $parentId;
                                        $iEGV++;
                                    }else{
                                        $exist = 0;
                                        foreach($arrExcelGoalValid as $excelGoalValid){
                                            if($excelGoalValid['goalId'] == $goalId){
                                                $exist = 1;
                                                break;
                                            }
                                        }
                                        if($exist == 0){
                                            $arrExcelGoalValid[$iEGV]['goalId']   = $goalId;
                                            $arrExcelGoalValid[$iEGV]['goalCode'] = $code;
                                            $arrExcelGoalValid[$iEGV]['goalType'] = $goalType;
                                            $arrExcelGoalValid[$iEGV]['formula']  = $formula;
                                            $arrExcelGoalValid[$iEGV]['parentId'] = $parentId;
                                            $iEGV++;
                                        }
                                    }
                                }else{
                                    if($listGoalErrors == ""){
                                        $listGoalErrors = $no;
                                    }else{
                                        $listGoalErrors .= ', '.$no;
                                    }
                                }
                            }else{
                                break;
                            }
                        }

                        if($listGoalNull != ""){
                            $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                            $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_GOAL_CODE').$listGoalNull.'.';
                            $iDE++;
                        }

                        if($listGoalErrors != ""){
                            $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                            $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_LIST_GOAL_CODE').$listGoalErrors.'.';
                            $iDE++;
                        }

                        if(count($arrExcelGoalValid) > 0){
                            #get Index of Highest Column in current sheet
                            $indexHighestColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);

                            $arrDataESheet = array();
                            $iD = 0;

                            /* *****************************************************************************************
                             * get object employee exist perform
                             * ****************************************************************************************/
                            $sqlExistPerform = "
                                SELECT te.*, u.code
                                FROM target_employee te
                                LEFT JOIN users u ON u.id = te.user_id
                                WHERE te.inactive = 0
                                AND te.company_id = ".$companyId."
                                AND te.area_id = ".$areaId."
                                AND te.position_id = ".$positionId."
                                AND te.year = ".$year."
                                AND te.month = ".$month."
                            ";
                            $objPerformEmployeeDB = DB::select(DB::raw($sqlExistPerform));

                            $arrEmployeeHP = array();

                            $listEmployeeCodeNull = "";
                            $listEmployeeCodeErr  = "";

                            for($c = 5; $c < $indexHighestColumn - 4; $c++) {
                                $currentColumn = PHPExcel_Cell::stringFromColumnIndex($c);
                                $compareEmployeeCode = trim($sheet->rangeToArray(trim($currentColumn . '10'))[0][0]);
                                $compareEmployeeCode = (isset($compareEmployeeCode) && $compareEmployeeCode != null) ? $compareEmployeeCode : '';
                                /* *************************************************************************************
                                 * Check employee have perform
                                 * ************************************************************************************/
                                $isPerform = 0; /** Tồn tại thực hiện */
                                if(count($objPerformEmployeeDB) > 0){
                                    foreach($objPerformEmployeeDB as $performEmployee){
                                        if(
                                            commonUtils::compareTwoString($performEmployee ->code, $compareEmployeeCode) == 1
                                            && commonUtils::compareTwoString('Tổng Thực hiện', $compareEmployeeCode) != 1
                                            && $compareEmployeeCode != ""
                                            && $performEmployee ->implement != 0){
                                            $isPerform = 1;
                                            break;
                                        }
                                    }
                                }
                                if($isPerform == 1
                                    && !in_array($compareEmployeeCode, $arrEmployeeHP)){
                                    $arrEmployeeHP[] = $compareEmployeeCode;
                                }
                                /* ************************************************************************************/

                                if($compareEmployeeCode != ""){
                                    $employeeId = -1;
                                    foreach($objEmployeeDB as $employee){
                                        if($employee->code == $compareEmployeeCode
                                            && $employee->company_id  == $companyId
                                            && $employee->area_id     == $areaId
                                            && $employee->position_id == $positionId){

                                            $terminateDate = $employee->terminate_date;
                                            $validTerDate = 1;
                                            if($terminateDate != '0000-00-00'){
                                                $terminateYear  = (int)substr($employee->terminate_date, 0, 4);
                                                $terminateMonth = (int)substr($employee->terminate_date, 5, 2);

                                                if($terminateYear <= $year && $terminateMonth >= $month){
                                                    $validTerDate = 0;
                                                }
                                            }else{
                                                $validTerDate = 0;
                                            }
                                            if($validTerDate == 0){
                                                $employeeId = $employee->id;
                                                break;
                                            }
                                        }
                                    }

                                    if($employeeId != -1){
                                        $arrGoalTarget = array();
                                        $iGT = 0;

                                        foreach($objTargetEmployeeDB as $targetEmployee){
                                            if( $targetEmployee->user_id == $employeeId){
                                                if(count($arrGoalTarget) == 0){
                                                    $arrGoalTarget[$iGT]['goalId']      = $targetEmployee->goal_id;
                                                    $arrGoalTarget[$iGT]['benchmark']   = $targetEmployee->benchmark;
                                                    $arrGoalTarget[$iGT]['calBenchmark']= $targetEmployee->cal_benchmark;
                                                    $arrGoalTarget[$iGT]['targetValue'] = $targetEmployee->target_value;
                                                    $iGT++;
                                                }else{
                                                    $exist = 0;
                                                    foreach($arrGoalTarget as $goalTarget){
                                                        if($goalTarget['goalId'] == $targetEmployee->goal_id){
                                                            $exist = 1;
                                                            break;
                                                        }
                                                    }
                                                    if($exist == 0){
                                                        $arrGoalTarget[$iGT]['goalId']      = $targetEmployee->goal_id;
                                                        $arrGoalTarget[$iGT]['benchmark']   = $targetEmployee->benchmark;
                                                        $arrGoalTarget[$iGT]['calBenchmark']= $targetEmployee->cal_benchmark;
                                                        $arrGoalTarget[$iGT]['targetValue'] = $targetEmployee->target_value;
                                                        $iGT++;
                                                    }
                                                }

                                            }
                                        }
                                        if(count($arrGoalTarget) > 0){
                                            $arrEIE = array();/** Array for each perfrom employee */
                                            $iEIE = 0;

                                            $listGENotTarget = ""; /** List Goal of employee hasn't target */

                                            for ($r = $startRow; $r <= $highestRow; $r++) {
                                                $dataSheet = $sheet->rangeToArray('B' . $r . ':'. $currentColumn . $r, NULL, TRUE, FALSE);

                                                $no         = trim($dataSheet[0][0]);
                                                $code       = trim($dataSheet[0][1]);
                                                $implement  = trim($dataSheet[0][$c-1]);

                                                $implement  = (isset($implement) && is_numeric($implement)) ? $implement : 0;

                                                if(commonUtils::compareTwoString($code, 'KH14') == 1){
                                                    $implement  = round($implement, 6);
                                                }

                                                $code       = (isset($code) && $code != null) ? $code : '';
                                                if(/*$implement  != 0
                                                    &&*/ $code    != ''){
                                                    $insideGoalId   = -1;
                                                    $insideGoalType = -1;
                                                    $insideFormula  = -1;
                                                    $insideParentId = -1;
                                                    foreach($arrExcelGoalValid as $excelGoalValid){
                                                        if($excelGoalValid['goalCode'] == $code){
                                                            $insideGoalId   = $excelGoalValid['goalId'];
                                                            $insideGoalType = $excelGoalValid['goalType'];
                                                            $insideFormula  = $excelGoalValid['formula'];
                                                            $insideParentId = $excelGoalValid['parentId'];
                                                            break;
                                                        }
                                                    }

                                                    $benchmark      = -1;
                                                    $calBenchmark   = -1;
                                                    $targetValue    = -1;

                                                    foreach($arrGoalTarget as $goalTarget){
                                                        if($goalTarget['goalId'] == $insideGoalId
                                                            && $insideGoalId != -1){

                                                            $benchmark   = $goalTarget['benchmark'];
                                                            $calBenchmark= $goalTarget['calBenchmark'];
                                                            $targetValue = $goalTarget['targetValue'];
                                                            break;
                                                        }
                                                    }

                                                    if(
                                                        $benchmark != -1
                                                    ){
                                                        if(count($arrEIE) == 0){
                                                            $arrEIE[$iEIE]['goalId']      = $insideGoalId;
                                                            $arrEIE[$iEIE]['goalCode']    = $code;
                                                            $arrEIE[$iEIE]['goalType']    = $insideGoalType;
                                                            $arrEIE[$iEIE]['formula']     = $insideFormula;
                                                            $arrEIE[$iEIE]['parentId']    = $insideParentId;
                                                            $arrEIE[$iEIE]['implement']   = $implement;
                                                            $arrEIE[$iEIE]['benchmark']   = $benchmark;
                                                            $arrEIE[$iEIE]['calBenchmark']= $calBenchmark;
                                                            $arrEIE[$iEIE]['targetValue'] = $targetValue;
                                                            $iEIE++;
                                                        }else{
                                                            $exist = 0;
                                                            foreach($arrEIE as $eie){
                                                                if($eie['goalId'] == $insideGoalId){
                                                                    $exist = 1;
                                                                    break;
                                                                }
                                                            }
                                                            if($exist == 0){
                                                                $arrEIE[$iEIE]['goalId']      = $insideGoalId;
                                                                $arrEIE[$iEIE]['goalCode']    = $code;
                                                                $arrEIE[$iEIE]['goalType']    = $insideGoalType;
                                                                $arrEIE[$iEIE]['formula']     = $insideFormula;
                                                                $arrEIE[$iEIE]['parentId']    = $insideParentId;
                                                                $arrEIE[$iEIE]['implement']   = $implement;
                                                                $arrEIE[$iEIE]['benchmark']   = $benchmark;
                                                                $arrEIE[$iEIE]['calBenchmark']= $calBenchmark;
                                                                $arrEIE[$iEIE]['targetValue'] = $targetValue;
                                                                $iEIE++;
                                                            }
                                                        }
                                                    }else{

                                                        if($insideParentId != 0){
                                                            if($implement != 0){
                                                                if($listGENotTarget == ""){
                                                                    $listGENotTarget = $code;
                                                                }else{
                                                                    $listGENotTarget .= ', '.$code;
                                                                }
                                                            }
                                                        }

                                                    }

                                                }
                                            }
                                            if($listGENotTarget != ""){
                                                $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                                                $arrDataError[$iDE]['content']    = 'Nhân viên <b>'.$compareEmployeeCode.'</b> không tồn tại kế hoạch đối với các mục tiêu: '.$listGENotTarget.'.';
                                                $iDE++;
                                            }

                                            if(count($arrEIE) > 0){
                                                if(count($arrDataESheet) == 0){
                                                    $arrDataESheet[$iD]['employeeId']   = $employeeId;
                                                    $arrDataESheet[$iD]['employeeCode'] = $compareEmployeeCode;
                                                    $arrDataESheet[$iD]['arrEIE']       = $arrEIE;
                                                    $iD++;
                                                }else{
                                                    $exist = 0;
                                                    foreach($arrDataESheet as $dataESheet){
                                                        if($dataESheet['employeeId'] == $employeeId){
                                                            $exist = 1;
                                                            break;
                                                        }
                                                    }
                                                    if($exist == 0){
                                                        $arrDataESheet[$iD]['employeeId']   = $employeeId;
                                                        $arrDataESheet[$iD]['employeeCode'] = $compareEmployeeCode;
                                                        $arrDataESheet[$iD]['arrEIE']       = $arrEIE;
                                                        $iD++;
                                                    }
                                                }
                                            }

                                        }

                                    }else{

                                        if(commonUtils::compareTwoString('Tổng Thực hiện', $compareEmployeeCode) != 1){
                                            if($listEmployeeCodeErr == ""){
                                                $listEmployeeCodeErr = $compareEmployeeCode;
                                            }else{
                                                $listEmployeeCodeErr .= ', '.$compareEmployeeCode;
                                            }
                                        }
                                    }
                                }else{
                                    if($listEmployeeCodeNull == ""){
                                        $listEmployeeCodeNull = $currentColumn . '10';
                                    }else{
                                        $listEmployeeCodeNull .= ', '.$currentColumn . '10';
                                    }
                                }

                            }


                            if($listEmployeeCodeNull != ""){
                                $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                                $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_EMPLOYEE_CODE').$listEmployeeCodeNull;
                                $iDE++;
                            }

                            if($listEmployeeCodeErr != ""){
                                $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                                $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_LIST_EMPLOYEE').$listEmployeeCodeErr;
                                $iDE++;
                            }

                            if(count($arrEmployeeHP) > 0){
                                $arrDataOverride[$iDO]['indexSheet']    = $arrSheet;
                                $arrDataOverride[$iDO]['companyId']     = $companyId;
                                $arrDataOverride[$iDO]['companyCode']   = $companyCode;
                                $arrDataOverride[$iDO]['areaId']        = $areaId;
                                $arrDataOverride[$iDO]['areaCode']      = $areaCode;
                                $arrDataOverride[$iDO]['positionId']    = $positionId;
                                $arrDataOverride[$iDO]['positionCode']  = $positionCode;
                                $arrDataOverride[$iDO]['year']          = $year;
                                $arrDataOverride[$iDO]['month']         = $month;
                                $arrDataOverride[$iDO]['arrEmployeeHP'] = $arrEmployeeHP;
                                $iDO++;
                            }

                            if(count($arrDataESheet) > 0){
                                $arrDataValid[$iDV]['indexSheet']    = $arrSheet;
                                $arrDataValid[$iDV]['companyId']     = $companyId;
                                $arrDataValid[$iDV]['companyCode']   = $companyCode;
                                $arrDataValid[$iDV]['areaId']        = $areaId;
                                $arrDataValid[$iDV]['areaCode']      = $areaCode;
                                $arrDataValid[$iDV]['positionId']    = $positionId;
                                $arrDataValid[$iDV]['positionCode']  = $positionCode;
                                $arrDataValid[$iDV]['year']          = $year;
                                $arrDataValid[$iDV]['month']         = $month;
                                $arrDataValid[$iDV]['arrDataESheet'] = $arrDataESheet;
                                $iDV++;
                            }
                        }

                    }else{
                        $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = 'Dữ liệu kế hoạch nhân viên tháng '.$month.'/'.$year.' không tồn tại.';
                        $iDE++;
                    }
                }else{
                    if($companyId == -1){
                        $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_COMP_CODE_APPLY');
                        $iDE++;
                    }
                    $curAreaCode = $areaCode;

                    if($isTQ == -1 && $curAreaCode != $preAreaCode){
                        $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = 'Vui lòng cập nhật nhân viên có chức danh TQ :<b>'.$areaCode.'</b>';
                        $iDE++;

                        $preAreaCode = $areaCode;
                    }
                    if($areaId == -1){
                        $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_AREA_CODE_APPLY');
                        $iDE++;
                    }
                    if($positionId == -1){
                        $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_POS_CODE_APPLY');
                        $iDE++;
                    }
                    $timeLock = $month.'/'.$year;
                    if($isLock == 1){
                        $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = 'Dữ liệu áp dụng tháng: '.$timeLock.' đang khóa.';
                        $iDE++;
                    }
                    if($isValidTitle == 0){
                        $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                        $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_IMPORT_FILE_INVALID');
                        $iDE++;
                    }
                }
            }else{
                if($title == ""){
                    $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_TITLE_FILE');
                    $iDE++;
                }
                if($companyCode == ""){
                    $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_COMP_APPLY');
                    $iDE++;
                }
                if($areaCode == ""){
                    $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_AREA_APPLY');
                    $iDE++;
                }
                if($positionCode == ""){
                    $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.NULL_POS_APPLY');
                    $iDE++;
                }
                if($year == ""){
                    $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_YEAR_APPLY');
                    $iDE++;
                }
                if($month == ""){
                    $arrDataError[$iDE]['indexSheet'] = $arrSheet;
                    $arrDataError[$iDE]['content']    = $this->config->get('constant.ERR_MONTH_APPLY');
                    $iDE++;
                }
            }
        }

        /***************************************************************************************************************
         * Write string override
         * ************************************************************************************************************/
        $str = "";
        if(count($arrDataOverride) > 0){
            foreach($arrDataOverride as $dataOverride){
                $time = $dataOverride['month'].'/'.$dataOverride['year'];
                $strEmployeeCode = "";
                foreach($dataOverride['arrEmployeeHP'] as $employeeHP){
                    if($strEmployeeCode == ""){
                        $strEmployeeCode = $employeeHP;
                    }else{
                        $strEmployeeCode .= ', '.$employeeHP;
                    }
                }
                if($strEmployeeCode != ""){
                    if($str == ""){
                        $str = "<b>* ".$sheetNames[$dataOverride['indexSheet']].": </b><br/>"
                            ."&nbsp;&nbsp;&nbsp;- Danh sách nhân viên tồn tại thực hiện tháng <b>".$time."</b>: ".$strEmployeeCode.'.';
                        ;
                    }else{
                        $str .= "<br/><b>* ".$sheetNames[$dataOverride['indexSheet']].": </b><br/>"
                            ."&nbsp;&nbsp;&nbsp;- Danh sách nhân viên tồn tại thực hiện tháng <b>".$time."</b>: ".$strEmployeeCode.'.';
                        ;
                    }
                }
            }
        }
        //commonUtils::pr($arrDataValid); die;
        //echo $str; die;
        /***************************************************************************************************************
         * send data to import function
         * ************************************************************************************************************/
        $data['arrDataValid']   = $arrDataValid;
        $data['arrDataError']   = $arrDataError;
        $data['arrSheets']      = $arrSheets;
        $data['curExcelFile']   = $rename;
        $data['pathFile']       = $path;
        $data['objEmployeeDB']  = $objEmployeeDB;
        $data['objPositionDB']  = $objPositionDB;

        Session::flash('type', 8);
        #Write session for action next
        Session::put('curType', $typeImport);
        Session::put('strIssetDataShow', $str);
        Session::put('chooseImport', 1);
        Session::put('data', $data);
        return redirect('importGoal');

    }

    public function importMultiPerformEmployee(){

        DB::beginTransaction();
        try{

            $actionUser    = Session::get('sid');
            $data          = Session::get('data');

            $arrDataValid  = $data['arrDataValid'];
            $arrDataError  = $data['arrDataError'];
            $arrSheets     = $data['arrSheets'];
            $curExcelFile  = $data['curExcelFile'];
            $path          = $data['pathFile'];
            $objEmployeeDB = $data['objEmployeeDB'];
            $objPositionDB = $data['objPositionDB'];
            $dir = date('Y').'/'.date('m');
            $inputFileName = $path;

            $objPHPExcel = new PHPExcel();
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
            $sheetNames = $objPHPExcel->getSheetNames();

            /* *********************************************************************************************************
             * Cập nhật thực hiện cho nhân viên
             * *********************************************************************************************************/
            $arrInfoHeader = array();
            $iIH = 0;

            $arrSuccess = array();
            $iS = 0;

            if(count($arrDataValid) > 0){
                foreach($arrDataValid as $dataValid){
                    $indexSheet     = $dataValid['indexSheet'];
                    $companyId      = $dataValid['companyId'];
                    $companyCode    = $dataValid['companyCode'];
                    $areaId         = $dataValid['areaId'];
                    $areaCode       = $dataValid['areaCode'];
                    $positionId     = $dataValid['positionId'];
                    $positionCode   = $dataValid['positionCode'];
                    $year           = $dataValid['year'];
                    $month          = $dataValid['month'];
                    $arrDataESheet  = $dataValid['arrDataESheet'];

                    $time = $month.'/'.$year;

                    if(count($arrInfoHeader) == 0){
                        $arrInfoHeader[$iIH]['companyId']    = $companyId;
                        $arrInfoHeader[$iIH]['companyCode']  = $companyCode;
                        $arrInfoHeader[$iIH]['areaId']       = $areaId;
                        $arrInfoHeader[$iIH]['areaCode']     = $areaCode;
                        $arrInfoHeader[$iIH]['positionId']   = $positionId;
                        $arrInfoHeader[$iIH]['positionCode'] = $positionCode;
                        $arrInfoHeader[$iIH]['year']         = $year;
                        $arrInfoHeader[$iIH]['month']        = $month;
                        $iIH++;
                    }else{
                        $exist = 0;
                        foreach($arrInfoHeader as $infoHeader){
                            if(
                                $infoHeader['companyId']        == $companyId
                                && $infoHeader['areaId']        == $areaId
                                && $infoHeader['positionId']    == $positionId
                                && $infoHeader['year']          == $year
                                && $infoHeader['month']         == $month
                            ){
                                $exist = 1;
                                break;
                            }
                        }
                        if($exist == 0){
                            $arrInfoHeader[$iIH]['companyId']    = $companyId;
                            $arrInfoHeader[$iIH]['companyCode']  = $companyCode;
                            $arrInfoHeader[$iIH]['areaId']       = $areaId;
                            $arrInfoHeader[$iIH]['areaCode']     = $areaCode;
                            $arrInfoHeader[$iIH]['positionId']   = $positionId;
                            $arrInfoHeader[$iIH]['positionCode'] = $positionCode;
                            $arrInfoHeader[$iIH]['year']         = $year;
                            $arrInfoHeader[$iIH]['month']        = $month;
                            $iIH++;
                        }
                    }

                    $arrSES = array();
                    $iSES = 0;

                    foreach($arrDataESheet as $dataSheet){
                        $employeeId   = $dataSheet['employeeId'];
                        $employeeCode = $dataSheet['employeeCode'];
                        $arrEIE       = $dataSheet['arrEIE'];

                        $numUpdatePESuccess = 0;
                        $numUpdatePPSuccess = 0;
                        $listGoalImported   = "";

                        foreach($arrEIE as $eie){
                            $goalId      = $eie['goalId'];
                            $goalCode    = $eie['goalCode'];
                            $goalType    = $eie['goalType'];
                            $formula     = $eie['formula'];
                            $parentId    = $eie['parentId'];
                            $implement   = $eie['implement'];
                            $benchmark   = $eie['benchmark'];
                            $targetValue = $eie['targetValue'];

                            $implementPoint = commonUtils::calculatorIP($targetValue, $implement, $benchmark, $goalType);
                            if($positionCode == commonUtils::POSITION_CODE_TQ){
                                if($formula == commonUtils::FORMULA_TU_NHAP){
                                    $performEmployee = array(
                                        'implement'         => $implement,
                                        'implement_point'   => $implementPoint,
                                        'updated_user'      => $actionUser);

                                    /* *********************************************************************************
                                     * Update perform for employee TQ
                                     * ********************************************************************************/
                                    DB::table('target_employee')
                                        ->where('company_id', $companyId)
                                        ->where('area_id', $areaId)
                                        ->where('position_id', $positionId)
                                        ->where('goal_id', $goalId)
                                        ->where('user_id', $employeeId)
                                        ->where('year', $year)
                                        ->where('month', $month)
                                        ->where('inactive', 0)
                                        ->update($performEmployee);
                                    $numUpdatePESuccess++;

                                    if($listGoalImported == ""){
                                        $listGoalImported = $goalCode;
                                    }else{
                                        $listGoalImported .= ', '.$goalCode;
                                    }

                                    /* *********************************************************************************
                                     * Update perform for position TQ
                                     * ********************************************************************************/
                                    $calBenchmark = $eie['calBenchmark'];

                                    $implementPoint2 = commonUtils::calculatorIP($targetValue, $implement, $calBenchmark, $goalType);
                                    $performPosition2 = array(
                                        'implement' => $implement,
                                        'implement_point' => $implementPoint2,
                                        'updated_user' => $actionUser
                                    );

                                    DB::table('target_position')
                                        ->where('company_id', $companyId)
                                        ->where('area_id', $areaId)
                                        ->where('position_id', $positionId)
                                        ->where('goal_id', $goalId)
                                        ->where('year', $year)
                                        ->where('month', $month)
                                        ->where('inactive', 0)
                                        ->update($performPosition2);
                                    $numUpdatePESuccess++;
                                }
                            }else{
                                /* *************************************************************************************
                                 * Update perform for employee with position different TQ
                                 * ************************************************************************************/
                                $performEmployee = array(
                                    'implement' => $implement,
                                    'implement_point' => $implementPoint,
                                    'updated_user' => $actionUser
                                );

                                DB::table('target_employee')
                                    ->where('company_id', $companyId)
                                    ->where('area_id', $areaId)
                                    ->where('position_id', $positionId)
                                    ->where('goal_id', $goalId)
                                    ->where('user_id', $employeeId)
                                    ->where('year', $year)
                                    ->where('month', $month)
                                    ->where('inactive', 0)
                                    ->update($performEmployee);
                                $numUpdatePESuccess++;

                                if($listGoalImported == ""){
                                    $listGoalImported = $goalCode;
                                }else{
                                    $listGoalImported .= ', '.$goalCode;
                                }
                            }
                        }

                        if($numUpdatePESuccess > 0){
                            $arrSES[$iSES]['employeeCode']  = $employeeCode;
                            $arrSES[$iSES]['numRows']       = count(explode(',', $listGoalImported));
                            $arrSES[$iSES]['listGoal']      = $listGoalImported;
                            $iSES++;
                        }
                    }
                    if(count($arrSES) > 0){
                        $arrSuccess[$iS]['indexSheet']   = $indexSheet;
                        $arrSuccess[$iS]['companyCode']  = $companyCode;
                        $arrSuccess[$iS]['areaCode']     = $areaCode;
                        $arrSuccess[$iS]['positionCode'] = $positionCode;
                        $arrSuccess[$iS]['arrSES']       = $arrSES;
                        $arrSuccess[$iS]['time']         = $time;
                        $iS++;
                    }
                }
                // commonUtils::pr($arrDataValid); die;
                #Write log override here
                $dataLogIP = array('function_name' => 'Thực hiện cho nhân viên (importMultiPerformEmployee)',
                    'action' => 'Import Thực hiện cho nhân viên',
                    'url' => 'upload/' . date('Y') . '/' . date('m') . '/' . $curExcelFile,
                    'id_row' => 0,
                    'old_value' => '',
                    'new_value' => $curExcelFile,
                    'created_user' => $actionUser);
                DB::table('kpi_log')->insert($dataLogIP);

                /* *****************************************************************************************************
                 * Tính ngược thực hiện từ Nhân viên > Chức danh > Tổ/Quận/Huyện > Phòng/Đài/MBF HCM > Công Ty
                 * ****************************************************************************************************/

                foreach($arrInfoHeader as $infoHeader){
                    $companyId      = $infoHeader['companyId'];
                    $companyCode    = $infoHeader['companyCode'];
                    $areaId         = $infoHeader['areaId'];
                    $areaCode       = $infoHeader['areaCode'];
                    $positionId     = $infoHeader['positionId'];
                    $positionCode   = $infoHeader['positionCode'];
                    $year           = $infoHeader['year'];
                    $month          = $infoHeader['month'];

                    $userTQId   = -1; /** Id trưởng quận */
                    $userTQCode = "";
                    /*foreach($objEmployeeDB as $employee){
                        if($employee->position_code == commonUtils::POSITION_CODE_TQ
                            && $employee->company_id == $companyId
                            && $employee->area_id    == $areaId
                        ){
                            $userTQId   = $employee->id;
                            $userTQCode = $employee->code;
                        }
                    }*/

                    $arrTemp = array();
                    $iT = 0;

                    foreach($objEmployeeDB as $employee){

                        $createdMonth   = (int)substr($employee->created_date, 5, 2);
                        $createdYear   = (int)substr($employee->created_date, 0, 4);

                        $terminateMonth   = (int)substr($employee->terminate_date, 5, 2);
                        $terminateYear   = (int)substr($employee->terminate_date, 0, 4);

                        if(
                            $employee->company_id   == $companyId
                            && $employee->area_id   == $areaId
                            && commonUtils::compareTwoString($employee->position_code, commonUtils::POSITION_CODE_TQ) == 1
                            && $createdYear         <= $year
                            && $createdMonth        <= $month
                            && ($terminateYear       >= $year  || $terminateYear == 0)
                            && ($terminateMonth      >= $month || $terminateMonth == 0)

                        ){



                            $userTQId = $employee->id;

                            $arrTemp[$iT]['terminateYear']  = $terminateYear;
                            $arrTemp[$iT]['terminateMonth'] = $terminateMonth;
                            $arrTemp[$iT]['tqId']           = $userTQId;
                            $iT++;

                        }
                    }



                    if(count($arrTemp) == 0){
                        $userTQId = -1;
                    }else{
                        if(count($arrTemp) == 1){
                            $userTQId = $arrTemp[0]['tqId'];
                        }else{
                            foreach($arrTemp as $temp){
                                for($i=1; $i<count($arrTemp); $i++){
                                    if(
                                        $temp['terminateYear'] <= $arrTemp[$i]['terminateYear']
                                        && $temp['terminateMonth'] <= $arrTemp[$i]['terminateMonth']
                                    ){
                                        $userTQId = $temp['tqId'];
                                    }else{
                                        $userTQId = $arrTemp[$i]['tqId'];
                                    }
                                }
                            }
                        }
                    }

                    if($userTQId != -1){
                        /* *********************************************************************************************
                        * Update perform for position different TQ
                        * *********************************************************************************************/
                        $this->cal4PositionDiffTQ($companyId, $areaId, $positionId, $year, $month);
                        /* *********************************************************************************************
                         * Update perform for position TQ and employee TQ
                         * ********************************************************************************************/
                        $conditionTQId = -1;
                        foreach($objPositionDB as $position){
                            if($position->position_code == commonUtils::POSITION_CODE_TQ){
                                $conditionTQId = $position->id;
                                break;
                            }
                        }
                        $this->cal4PositionTQ($companyId, $areaId, $conditionTQId, $year, $month, $actionUser);
                    }

                    /* *************************************************************************************************
                     * Update perform for area
                     * ************************************************************************************************/
                    $this->formatIPOArea($companyId, $areaId, $year, $month);
                    $this->calKpi4Area($companyId, $areaId, $year, $month, $actionUser);
                    /* *************************************************************************************************
                     * Tính điểm thực hiện cho Phòng/Đài/MBF HCM
                     * ************************************************************************************************/
                    $sqlGetApplyDateComp =  "
                        SELECT apply_date
                        FROM important_level_company
                        WHERE inactive = 0
                        AND company_id = ".$companyId."
                        AND year(apply_date) = ".$year."
                        ORDER BY apply_date DESC
                        LIMIT 0,1
                    ";
                    $objApplyDateComp = DB::select(DB::raw($sqlGetApplyDateComp));

                    $comApplyDate = "";
                    if(count($objApplyDateComp) > 0){
                        $comApplyDate = commonUtils::objectToArray($objApplyDateComp)[0]['apply_date'];
                    }

                    if($comApplyDate != '') {
                        $this->formatIPOCompany($companyId, $comApplyDate);
                        $this->calKpi4Company($companyId, $year, 1, 12, $comApplyDate, $actionUser);
                    }

                    /* *************************************************************************************************
                     * Tính điểm thực hiện cho Công Ty
                     * ************************************************************************************************/
                    $sqlGetApplyDateCor =  "
                        SELECT apply_date
                        FROM important_level_corporation
                        WHERE inactive = 0
                        AND year(apply_date) = ".$year."
                        ORDER BY apply_date DESC
                        LIMIT 0,1
                    ";
                    $objApplyDateCor = DB::select(DB::raw($sqlGetApplyDateCor));

                    $corApplyDate = "";
                    if(count($objApplyDateCor) > 0){
                        $corApplyDate = commonUtils::objectToArray($objApplyDateCor)[0]['apply_date'];
                    }

                    if($corApplyDate != ''){
                        $this->formatIPOCorporation($corApplyDate);
                        $this->calKpi4Corporation($corApplyDate, $comApplyDate, $actionUser);
                    }
                }

                $strSuccess = "";
                if(count($arrSuccess) > 0){
                    foreach($arrSuccess as $success){
                        $strISuccess = "";
                        foreach($success['arrSES'] as $ses){
                            if($strISuccess == ""){
                                $strISuccess = '<br/><b>&nbsp;&nbsp;&nbsp; - '.$ses['employeeCode']
                                    .'</b> '.$this->config->get('constant.ALERT_SUCCESS_MMS_PP').' <b>'.$success['time'].'</b>: <b>'.$ses['numRows'].'</b> dòng.'
                                    .'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Danh sách mục tiêu đã import: '.$ses['listGoal'].'.'
                                ;
                            }else{
                                $strISuccess .= '<br/><b>&nbsp;&nbsp;&nbsp; - '.$ses['employeeCode']
                                    .'</b> '.$this->config->get('constant.ALERT_SUCCESS_MMS_PP').' <b>'.$success['time'].'</b>: <b>'.$ses['numRows'].'</b> dòng.'
                                    .'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+ Danh sách mục tiêu đã import: '.$ses['listGoal'].'.'
                                ;
                            }
                        }
                        if($strISuccess != ""){
                            if($strSuccess == ""){
                                $strSuccess = "<b>".commonUtils::TITLE_IMPORT_PERFORM_EMPLOYEE.'<hr>* '
                                    .$sheetNames[$success['indexSheet']].':</b>'.$strISuccess;
                                ;
                            }else{
                                $strSuccess .= '<br/><b>* '.$sheetNames[$success['indexSheet']].':</b>'.$strISuccess;
                                ;
                            }
                        }
                    }
                }

                if($strSuccess != ""){
                    Session::flash('message-success', $strSuccess);
                }
            }

            $strErrors = "";
            if(count($arrDataError) > 0){
                foreach($arrSheets as $sheetIndex){
                    $strSheetErr = "";
                    foreach($arrDataError as $dataError){
                        if($dataError['indexSheet'] == $sheetIndex){
                            if($strSheetErr == ""){
                                $strSheetErr = '<br/>&nbsp;&nbsp;&nbsp; - '.$dataError['content'];
                            }else{
                                $strSheetErr .= '<br/>&nbsp;&nbsp;&nbsp; - '.$dataError['content'];
                            }
                        }
                    }
                    if($strSheetErr != ""){
                        if($strErrors == ""){
                            $strErrors = "<b>".commonUtils::TITLE_IMPORT_PERFORM_EMPLOYEE.'<hr>* '
                                .$sheetNames[$sheetIndex].':</b>'.$strSheetErr;
                            ;
                        }else{
                            $strErrors .= '<br/><b>* '.$sheetNames[$sheetIndex].':</b>'.$strSheetErr;
                            ;
                        }
                    }
                }
            }
            if($strErrors != ""){
                Session::flash('message-errors', $strErrors);
            }

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        $this->clearSession();
        Session::flash('type', 8);
        return redirect('importGoal');
    }
    /*----------------------------------------------------------------------------------------------------------------*/
    public function clearSession()
    {
        Session::flash('type', Session::get('curType'));
        Session::put('numRow', -1);
        Session::put('numRowArea', -1);
        Session::forget('dayCheck');
        Session::forget('fDayCheck');
        Session::forget('pathFile');
        Session::forget('startRow');
        Session::forget('curExcelFile');
        Session::forget('monthYear');
        Session::forget('monthArea');
        Session::forget('yearArea');
        Session::forget('companyEmp');
        Session::forget('areaEmp');
        Session::forget('positionEmp');
        Session::forget('chooseImport');
        Session::forget('companyId');
        Session::forget('arrIssetData');
        Session::forget('strIssetData');
        Session::forget('strIssetDataShow');
        Session::forget('arrSheets');
        Session::forget('curType');
        Session::forget('listEmployeeExist');
        Session::forget('dataImportMultiTE');
        Session::forget('data');
        Session::forget('areaChoose');
        Session::forget('areaDetailChoose');

        return redirect('importGoal');
    }

    private function deleteILC($companyId, $year, $applyDate){
        $sqlDelete = "
            DELETE FROM important_level_company
            WHERE year(apply_date) = ".$year."
            AND apply_date = '".$applyDate."'
        ";
        if($companyId != 0){
            $sqlDelete .= " AND `company_id` = ".$companyId;
        }

        DB::delete(DB::raw($sqlDelete));
    }
    private function deleteILA($companyId, $areaId, $year, $fromMonth, $toMonth){
        $sqlDelete = "
            DELETE FROM important_level_area
            WHERE year = ".$year."
            AND month >= ".$fromMonth."
            AND month <= ".$toMonth."
        ";
        if($companyId != 0){
            $sqlDelete .= " AND `company_id` = ".$companyId;
        }
        if($areaId != 0){
            $sqlDelete .= " AND `area_id` = ".$areaId;
        }
        DB::delete(DB::raw($sqlDelete));
    }
    private function deleteTA($companyId, $areaId, $year, $fromMonth, $toMonth){
        $sqlDelete = "
            DELETE FROM target_area
            WHERE year = ".$year."
            AND month >= ".$fromMonth."
            AND month <= ".$toMonth."
        ";
        if($companyId != 0){
            $sqlDelete .= " AND `company_id` = ".$companyId;
        }
        if($areaId != 0){
            $sqlDelete .= " AND `area_id` = ".$areaId;
        }
        DB::delete(DB::raw($sqlDelete));
    }
    private function deleteILP($companyId, $areaId, $positionId, $year, $fromMonth, $toMonth){
        $sqlDelete = "
            DELETE FROM important_level_position
            WHERE year = ".$year."
            AND month >= ".$fromMonth."
            AND month <= ".$toMonth."
        ";
        if($companyId != 0){
            $sqlDelete .= " AND `company_id` = ".$companyId;
        }
        if($areaId != 0){
            $sqlDelete .= " AND `area_id` = ".$areaId;
        }
        if($positionId != 0){
            $sqlDelete .= " AND `position_id` = ".$positionId;
        }
        DB::delete(DB::raw($sqlDelete));
    }
    private function deleteTP($companyId, $areaId, $positionId, $year, $fromMonth, $toMonth){
        $sqlDelete = "
            DELETE FROM target_position
            WHERE year = ".$year."
            AND month >= ".$fromMonth."
            AND month <= ".$toMonth."
        ";
        if($companyId != 0){
            $sqlDelete .= " AND `company_id` = ".$companyId;
        }
        if($areaId != 0){
            $sqlDelete .= " AND `area_id` = ".$areaId;
        }
        if($positionId != 0){
            $sqlDelete .= " AND `position_id` = ".$positionId;
        }
        DB::delete(DB::raw($sqlDelete));
    }
    private function deleteTE($companyId, $areaId, $positionId, $year, $fromMonth, $toMonth, $employeeId){
        $sqlDelete = "
            DELETE FROM target_employee
            WHERE year = ".$year."
            AND month >= ".$fromMonth."
            AND month <= ".$toMonth."
        ";
        if($companyId != 0){
            $sqlDelete .= " AND `company_id` = ".$companyId;
        }
        if($areaId != 0){
            $sqlDelete .= " AND `area_id` = ".$areaId;
        }
        if($positionId != 0){
            $sqlDelete .= " AND `position_id` = ".$positionId;
        }
        if($employeeId != 0){
            $sqlDelete .= " AND `user_id` = ".$employeeId;
        }
        DB::delete(DB::raw($sqlDelete));
    }
    private function getApplyDate4Corporation($year){
        $sql = "SELECT apply_date
            FROM important_level_corporation
            WHERE inactive = 0
            AND year(apply_date) = ".$year."
            ORDER BY apply_date DESC
            LIMIT 0,1";
        $objDB = DB::select(DB::raw($sql));
        return (count($objDB) == 1) ? $objDB[0]->apply_date : '';
    }
    private function getApplyDate4Company($companyId, $year, $applyDate){
        $sql = "SELECT apply_date
            FROM important_level_company
            WHERE inactive = 0
            AND `company_id` = ".$companyId."
            AND year(apply_date) = ".$year."";

        if($applyDate != ""){
            $sql .= " AND apply_date < '".$applyDate."'";
        }

        $sql .= "
            ORDER BY apply_date DESC
            LIMIT 0,1
        ";

        $objDB = DB::select(DB::raw($sql));
        return (count($objDB) == 1) ? $objDB[0]->apply_date : '';
    }
    private function getImportantLevelCompany($companyId, $applyDate){

        $sql = " SELECT ilc.*, g.goal_code, g.parent_id, c.company_code
            FROM important_level_company ilc
            LEFT JOIN goal g ON g.id = ilc.goal_id
            LEFT JOIN company c ON c.id = ilc.company_id
            WHERE ilc.inactive = 0";
        if($companyId != 0){
            $sql .= " AND ilc.company_id = ".$companyId;
        }
        if($applyDate != ""){
            $sql .= " AND ilc.apply_date = '".$applyDate."'";
        }

        $objDB = DB::select(DB::raw($sql));
        return  $objDB;
    }
    private function getImportantLevelArea($companyId, $areaId, $year, $month){

        $sql = "SELECT ila.*, g.goal_code, g.parent_id, c.company_code
            FROM important_level_area ila
            LEFT JOIN goal g ON g.id = ila.goal_id
            LEFT JOIN company c ON c.id = ila.company_id
            LEFT JOIN area a ON a.id = ila.area_id
            WHERE ila.inactive = 0
            AND ila.company_id = ".$companyId."
            AND ila.year = ".$year;

        if($areaId != 0){
            $sql .= " AND ila.area_id = ".$areaId." ";
        }

        if($month != 0){
            $sql .= " AND ila.month = ".$month." ";
        }

        $objDB = DB::select(DB::raw($sql));
        return  $objDB;
    }
    private function getImportantLevelPosition($companyId, $areaId, $positionId, $year, $fromMonth, $toMonth){
        $sql = "
            SELECT ilp.*
            FROM important_level_position ilp
            WHERE ilp.inactive = 0
            AND ilp.company_id = ".$companyId."
            AND ilp.area_id = ".$areaId."
            AND ilp.year = ".$year."
         ";

        if($positionId != 0){
            $sql .= " AND ilp.position_id = ".$positionId;
        }

        if($fromMonth != 0){
            $sql .= " AND ilp.month >= ".$fromMonth;
        }

        if($toMonth != 0){
            $sql .= " AND ilp.month <= ".$toMonth;
        }

        $objDB = DB::select(DB::raw($sql));
        return $objDB;
    }
    /* *****************************************************************************************************************
     * @param $companyId
     * @param $applyDate
     * format all implement point to 0
     * ****************************************************************************************************************/
    private function formatIPOCompany($companyId, $applyDate){
        $sql = "UPDATE important_level_company
            SET implement_point = 0, real_percent = 0
            WHERE company_id = ".$companyId."
            AND apply_date = '".$applyDate."'";
        DB::update(DB::raw($sql));
    }
    private function formatIPOCorporation($applyDate){
        $sql = "
            UPDATE important_level_corporation
            SET implement_point = 0, percent_complete = 0
            WHERE apply_date = '".$applyDate."'
        ";
        DB::update(DB::raw($sql));
    }
    private function formatIPOArea($companyId, $areaId, $year, $month){
        $sql = "
            UPDATE target_area
            SET implement_point = 0, real_percent = 0
            WHERE inactive = 0
            AND company_id = ".$companyId."
            AND area_id = ".$areaId."
            AND year = ".$year."
            AND month = ".$month."
        ";
        DB::update(DB::raw($sql));
    }
    private function formatIPOPosition($companyId, $areaId, $positionId, $year, $month){
        $sql = "
            UPDATE target_position
            SET implement = 0, implement_point = 0
            WHERE inactive = 0
            AND company_id = ".$companyId."
            AND area_id = ".$areaId."
            AND position_id = ".$positionId."
            AND year = ".$year."
            AND month = ".$month."
        ";
        DB::update(DB::raw($sql));
    }
    private function calKpi4Corporation($corApplyDate, $comApplyDate, $actionUser){
        $sqlILCOr = "
            SELECT *
            FROM important_level_corporation
            WHERE inactive = 0
            AND apply_date = '".$corApplyDate."'
        ";
        $objILCorDB = DB::select(DB::raw($sqlILCOr));

        $sqlILC = "
            SELECT *
            FROM important_level_company
            WHERE inactive = 0
            AND apply_date = '".$comApplyDate."'
        ";
        $objILCDB = DB::select(DB::raw($sqlILC));

        foreach($objILCorDB as $ilcor){

            $implementPoint = 0;
            foreach($objILCDB as $ilc){
                if($ilc->goal_id == $ilcor->goal_id){
                    $implementPoint += $ilc->cal_implement_point;
                }
            }
            $percentComplete = ($ilcor->benchmark != 0) ? $implementPoint / $ilcor->benchmark : 0;
            if($implementPoint != 0){
                $dataILCUpdate = array(
                    'implement'                 => 0,
                    'implement_point'           => $implementPoint,
                    'percent_complete'          => $percentComplete,
                    'updated_user'              => $actionUser
                );
                DB::table('important_level_corporation')->where('id', $ilcor->id)->update($dataILCUpdate);
            }
        }
    }
    private function calKpi4Company($companyId, $year, $fromMonth, $toMonth, $applyDate, $actionUser){
        $sql = "
            SELECT *
            FROM target_area
            WHERE company_id = ".$companyId."
            AND year = ".$year."
            AND month <= ".$toMonth."
        ";
        if($fromMonth != 0){
            $sql .= " AND month >= ".$fromMonth;
        }

        $objTargetAreaDB = DB::select(DB::raw($sql));

        $sqlILC = "
            SELECT *
            FROM important_level_company
            WHERE inactive = 0
            AND company_id = ".$companyId."
            AND apply_date = '".$applyDate."'
        ";

        $objILCDB = DB::select(DB::raw($sqlILC));


        $sqlArea = "
            SELECT *
            FROM area
            WHERE inactive = 0
            AND company_id = ".$companyId."
        ";

        $objAreaDB = DB::select(DB::raw($sqlArea));

        $sqlGoal = "
            SELECT *
            FROM goal
            WHERE inactive = 0
        ";

        $objGoalDB = DB::select(DB::raw($sqlGoal));

        $arrRoundTA = array();
        $iRTA = 0;

        foreach($objAreaDB as $area){

            foreach($objGoalDB as $goal){

                $totalBenchmark       = 0;
                $totalImplementPoint  = 0;
                $rCalBenchmark        = 0;
                $totalCalIP           = 0;
                foreach($objTargetAreaDB as $targetArea){
                    if(
                        $targetArea->area_id == $area->id
                        && $targetArea->goal_id == $goal->id
                    ){

                        $rCalBenchmark        = $targetArea->cal_benchmark;
                        $totalBenchmark       += $targetArea->benchmark;
                        $totalImplementPoint  += $targetArea->implement_point;
                        $totalCalIP           = $targetArea->cal_implement_point;

                    }
                }

                $rPercentComplete = ($totalBenchmark != 0) ? $totalImplementPoint / $totalBenchmark : 0;
                $rImplementPoint  = ($rCalBenchmark != 0) ? $rPercentComplete * $rCalBenchmark : 0;

                if($totalImplementPoint != 0 ){
                    $arrRoundTA[$iRTA]['goalId']              = $goal->id;
                    $arrRoundTA[$iRTA]['areaId']              = $area->id;
                    $arrRoundTA[$iRTA]['areaCode']            = $area->area_code;
                    $arrRoundTA[$iRTA]['totalBenchmark']      = $totalBenchmark;
                    $arrRoundTA[$iRTA]['totalImplementPoint'] = $totalImplementPoint;
                    $arrRoundTA[$iRTA]['rPercentComplete']    = $rPercentComplete;
                    $arrRoundTA[$iRTA]['rCalBenchmark']       = $rCalBenchmark;
                    $arrRoundTA[$iRTA]['rImplementPoint']     = $rImplementPoint;
                    $arrRoundTA[$iRTA]['totalCalIP']          = $totalCalIP;
                    $iRTA++;
                }
            }

        }

        foreach($objILCDB as $ilc){

            $implementPoint = 0;

            foreach($arrRoundTA as $rTAAll){
                if($rTAAll['goalId'] == $ilc->goal_id){

                    $implementPoint += $rTAAll['totalCalIP'];
                }
            }

            $percentComplete = ($ilc->benchmark != 0) ? $implementPoint / $ilc->benchmark : 0;
            $calImplementPoint =  $percentComplete * $ilc->cal_benchmark;

            $dataILCUpdate = array(
                'implement'                 => 0,
                'implement_point'           => $implementPoint,
                'real_percent'              => $percentComplete,
                'cal_implement_point'       => $calImplementPoint,
                'updated_user'              => $actionUser
            );
            DB::table('important_level_company')->where('id', $ilc->id)->update($dataILCUpdate);

        }
    }
    private function calKpi4Area($companyId, $areaId, $year, $month, $actionUser){
        $sqlTargetArea = "
            SELECT *
            FROM target_area
            WHERE inactive = 0
            AND company_id = ".$companyId."
            AND area_id = ".$areaId."
            AND year = ".$year."
            AND month = ".$month."
        ";
        $objTargetAreaDB = DB::select(DB::raw($sqlTargetArea));

        $sqlPerformPosition = "
            SELECT *
            FROM target_position
            WHERE inactive = 0
            AND company_id = ".$companyId."
            AND area_id = ".$areaId."
            AND year = ".$year."
            AND month = ".$month."
        ";
        $objPerformPositionDB = DB::select(DB::raw($sqlPerformPosition));

        if(count($objTargetAreaDB) > 0
            && count($objPerformPositionDB) > 0){
            foreach($objTargetAreaDB as $targetArea){
                $implementPoint = 0;
                foreach($objPerformPositionDB as $performPosition){
                    if($performPosition->goal_id == $targetArea->goal_id){
                        $implementPoint += $performPosition->implement_point;
                    }
                }
                $realPercent        = ($targetArea->benchmark != 0) ? $implementPoint / $targetArea->benchmark : 0;
                //$calImplementPoint  = ($targetArea->cal_benchmark != 0) ? $realPercent * $targetArea->cal_benchmark : 0;

                if($implementPoint != 0){
                    $dataTAUpdate = array(
                        'implement'                 => 0,
                        'implement_point'           => $implementPoint,
                        'real_percent'              => $realPercent,
                        'cal_implement_point'       => 0,
                        'updated_user'              => $actionUser
                    );
                    DB::table('target_area')->where('id', $targetArea->id)->update($dataTAUpdate);
                }
            }
        }

        $sqlGoal = "
            SELECT *
            FROM goal
            WHERE inactive = 0
        ";

        $objGoalDB = DB::select(DB::raw($sqlGoal));

        $sqlPerformArea = "
            SELECT *
            FROM target_area
            WHERE inactive = 0
            AND year = ".$year."
            AND company_id = ".$companyId."
            AND area_id = ".$areaId."
        ";
        $objPerformAreaDB = DB::select(DB::raw($sqlPerformArea));

        foreach($objGoalDB as $goal){
            $totalBenchmark      = 0;
            $totalImplementPoint = 0;

            $paCalBenchmark = 0;
            foreach($objPerformAreaDB as $pa){
                if($pa->goal_id == $goal->id){
                    $totalBenchmark      += $pa->benchmark;
                    $totalImplementPoint += $pa->implement_point;
                    $paCalBenchmark      = $pa->cal_benchmark;
                }
            }

            $rRealPercent       = ($totalBenchmark != 0) ? $totalImplementPoint / $totalBenchmark : 0;
            $calImplementPoint  = $rRealPercent * $paCalBenchmark;

            $dataPAUpdate = array(
                'cal_implement_point'       => $calImplementPoint
            );

            DB::table('target_area')
                ->where('company_id', $companyId)
                ->where('area_id', $areaId)
                ->where('year', $year)
                ->where('goal_id', $goal->id)
                ->update($dataPAUpdate);
        }
    }
    private function cal4PositionDiffTQ($companyId, $areaId, $positionId, $year, $month){
        $sqlTargetEmployeeRS = "
            SELECT te.*, p.position_code
            FROM target_employee te
            LEFT JOIN position p ON p.id = te.position_id
            WHERE te.inactive = 0
            AND te.company_id = ".$companyId."
            AND te.area_id = ".$areaId."
            AND te.position_id = ".$positionId."
            AND te.year = ".$year."
            AND te.month = ".$month."
        ";

        $objTargetEmployeeRS = DB::select(DB::raw($sqlTargetEmployeeRS));

        $sqlTargetPositionRS = "
                            SELECT tp.*, p.position_code, g.formula, g.goal_type, g.goal_code
                            FROM target_position tp
                            LEFT JOIN goal g ON g.id = tp.goal_id
                            LEFT JOIN position p ON p.id = tp.position_id
                            WHERE tp.inactive = 0
                            AND tp.company_id = ".$companyId."
                            AND tp.area_id = ".$areaId."
                            AND tp.position_id = ".$positionId."
                            AND tp.year = ".$year."
                            AND tp.month = ".$month."
                        ";

        $objTargetPositionRS = DB::select(DB::raw($sqlTargetPositionRS));

        $sqlTargetCTVRS = "
                            SELECT tp.*, p.position_code, g.formula, g.goal_type, g.goal_code
                            FROM target_position tp
                            LEFT JOIN goal g ON g.id = tp.goal_id
                            LEFT JOIN position p ON p.id = tp.position_id
                            WHERE tp.inactive = 0
                            AND tp.company_id = ".$companyId."
                            AND tp.area_id = ".$areaId."
                            AND tp.position_id = (SELECT id from position where position_code = '".commonUtils::POSITION_CODE_CTV."' LIMIT 0,1)
                            AND tp.year = ".$year."
                            AND tp.month = ".$month."
                        ";

        $objTargetCTVRS = DB::select(DB::raw($sqlTargetCTVRS));

        if(
            count($objTargetEmployeeRS) > 0
            && count($objTargetPositionRS) > 0
        ){

            foreach($objTargetPositionRS as $rsTargetPosition){

                if($rsTargetPosition->position_code != commonUtils::POSITION_CODE_TQ){


                    if(
                        $rsTargetPosition->formula == commonUtils::FORMULA_LAY1SO
                    ){
                        $implement = 0;
                        foreach($objTargetEmployeeRS as $rsTargetEmployee){
                            if(
                                $rsTargetEmployee->goal_id == $rsTargetPosition->goal_id
                                && $rsTargetEmployee->implement != 0
                            ){
                                $implement = $rsTargetEmployee->implement;
                                break;
                            }
                        }
                        if($implement == 0){
                            if($rsTargetPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN){
                                foreach($objTargetCTVRS as $rsCTV){
                                    if(
                                        $rsCTV->goal_id == $rsTargetPosition->goal_id
                                        && $rsCTV->implement != 0
                                    ){
                                        $implement = $rsCTV->implement;
                                        break;
                                    }
                                }
                            }
                        }
                    }elseif($rsTargetPosition->formula == commonUtils::FORMULA_TRUNG_BINH_CONG){

                        $implementTemp = 0;
                        $count = 0;
                        foreach($objTargetEmployeeRS as $rsTargetEmployee){
                            if(
                                $rsTargetEmployee->goal_id == $rsTargetPosition->goal_id
                                && $rsTargetEmployee->target_value != 0
                            ){
                                $implementTemp += $rsTargetEmployee->implement;
                                $count++;
                            }
                        }

                        if($implementTemp == 0){
                            if($rsTargetPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN){
                                foreach($objTargetCTVRS as $rsCTV){
                                    if(
                                        $rsCTV->goal_id == $rsTargetPosition->goal_id
                                        && $rsCTV->target_value != 0
                                    ){
                                        $implementTemp += $rsCTV->implement;
                                        $count++;
                                    }
                                }
                            }
                        }

                        $implement = ($count != 0) ? $implementTemp / $count : 0;

                    }else{
                        $implement = 0;
                        foreach($objTargetEmployeeRS as $rsTargetEmployee){
                            if($rsTargetEmployee->goal_id == $rsTargetPosition->goal_id){
                                $implement += $rsTargetEmployee->implement;
                            }
                        }

                        if($rsTargetPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN){
                            foreach($objTargetCTVRS as $rsCTV){
                                if($rsCTV->goal_id == $rsTargetPosition->goal_id){
                                    $implement += $rsCTV->implement;
                                }
                            }
                        }
                    }


                    $implementPoint = commonUtils::calculatorIP(
                        $rsTargetPosition->target_value
                        , $implement
                        , $rsTargetPosition->cal_benchmark
                        , $rsTargetPosition->goal_type
                    );

                    $arrTargetPosition = array(
                        'implement'           => $implement
                        , 'implement_point'   => $implementPoint
                    );

                    DB::table('target_position')->where('id', $rsTargetPosition->id)->update($arrTargetPosition);


                }

            }
        }
    }
    private function cal4PositionTQ($companyId, $areaId, $positionTQId, $year, $month, $actionUser){

        $sqlTPTQRS = "
            SELECT tp.*, p.position_code, g.formula, g.goal_type, g.goal_code
            FROM target_position tp
            LEFT JOIN goal g ON g.id = tp.goal_id
            LEFT JOIN position p ON p.id = tp.position_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND tp.area_id = ".$areaId."
            AND tp.position_id = ".$positionTQId."
            AND tp.year = ".$year."
            AND tp.month = ".$month."
        ";

        $objTPTQRS = DB::select(DB::raw($sqlTPTQRS));

        $sqlTETQRS = "
            SELECT te.*, p.position_code
            FROM target_employee te
            LEFT JOIN position p ON p.id = te.position_id
            WHERE te.inactive = 0
            AND te.company_id = ".$companyId."
            AND te.area_id = ".$areaId."
            AND te.position_id = ".$positionTQId."
            AND te.year = ".$year."
            AND te.month = ".$month."
        ";

        $objTETQRS = DB::select(DB::raw($sqlTETQRS));

        $sqlTPRS = "
            SELECT tp.*, p.position_code, g.formula, g.goal_type, g.goal_code
            FROM target_position tp
            LEFT JOIN goal g ON g.id = tp.goal_id
            LEFT JOIN position p ON p.id = tp.position_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND tp.area_id = ".$areaId."
            AND tp.position_id != ".$positionTQId."
            AND tp.year = ".$year."
            AND tp.month = ".$month."
        ";
        $objPerformPositionRS = DB::select(DB::raw($sqlTPRS));

        foreach($objTPTQRS as $rsTPTQ){

            $goalId         = $rsTPTQ->goal_id;
            $formula        = $rsTPTQ->formula;
            $pTargetValue   = $rsTPTQ->target_value;
            $benchmark      = $rsTPTQ->benchmark;
            $calBenchmark   = $rsTPTQ->cal_benchmark;
            $goalType       = $rsTPTQ->goal_type;

            $pImplement = 0;
            switch ($formula) {

                case CommonUtils::FORMULA_TRUNG_BINH_CONG:
                    $pImplementTemp = 0;
                    $count = 0;
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if(
                            $rsPerformPosition->goal_id == $goalId
                            && $rsPerformPosition->target_value != 0
                        ){
                            $pImplementTemp += $rsPerformPosition->implement;
                            $count++;

                        }
                    }

                    $pImplement = ($count > 0) ? $pImplementTemp / $count : 0;

                    break;

                case CommonUtils::FORMULA_LAY1SO:
                    /**
                     * Lấy bất kỳ  thực hiện của một chức danh có thực hiện khác 0 và thuộc mục tiêu đang xét
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if(
                            $rsPerformPosition->goal_id == $goalId
                        ){
                            $pImplement = $rsPerformPosition->implement;
                            if($pImplement != 0){
                                break;
                            }
                        }
                    }

                    break;
                case CommonUtils::FORMULA_TONG_NVBH:
                    /**
                     * Lấy thực hiện của chức danh nhân viên bán hàng
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id
                            && $rsPerformPosition->position_code == commonUtils::POSITION_CODE_NVBH
                        ){
                            $pImplement = $rsPerformPosition->implement;
                            break;
                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_KAM_AM:
                    /**
                     * Lấy tổng thực hiện của chức danh chuyên viên quận KHDN
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id
                            && $rsPerformPosition->position_code == commonUtils::POSITION_CODE_KAM_AM
                        ){
                            $pImplement = $rsPerformPosition->implement;
                            break;
                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_CVKHCN_CVKHDN:
                    /**
                     * Lấy tổng thực hiện của chức danh Chuyên viên quận KHCN + Chuyên viên quận KHDN
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                                || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }

                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_CVKHCN_CHT:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                                || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CHT
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }

                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_GDV:
                /**
                 * Calculate target_value here
                 */
                foreach($objPerformPositionRS as $rsPerformPosition){
                    if( $goalId == $rsPerformPosition->goal_id){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_GDV
                            || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_GDV_K
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }

                    }
                }
                break;

                case CommonUtils::FORMULA_TONG_CVKHCN:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }

                        }
                    }
                    break;

                case CommonUtils::FORMULA_TONG_CVKHDN:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }

                        }
                    }
                    break;

                case CommonUtils::FORMULA_TONG_CVKHDN_CHT:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                                || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CHT
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }

                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_CVKHCN_CVKHDN_CHT:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionRS as $rsPerformPosition){
                        if( $goalId == $rsPerformPosition->goal_id){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                                || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CHT
                                || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }

                        }
                    }
                    break;

            }

            $pImplementPoint = commonUtils::calculatorIP($pTargetValue, $pImplement, $calBenchmark, $goalType);
            $pPerformPosition = array(
                'implement'         => $pImplement,
                'implement_point'   => $pImplementPoint,
                'updated_user'      => $actionUser
            );

            if($formula != commonUtils::FORMULA_TU_NHAP){
                DB::table('target_position')->where('id', $rsTPTQ->id)->update($pPerformPosition);
            }



            $eTargetValue = 0;
            $eBenchmark = 0;
            $eTETQId = -1;
            foreach($objTETQRS as $reTETQ){
                if($reTETQ->goal_id == $goalId){
                    $eTargetValue   = $reTETQ->target_value;
                    $eTETQId        = $reTETQ->id;
                    $eBenchmark     = $reTETQ->benchmark;
                }
            }

            $eImplementPoint = commonUtils::calculatorIP($eTargetValue, $pImplement, $eBenchmark, $goalType);
            $ePerformEmployee = array(
                'implement'         => $pImplement,
                'implement_point'   => $eImplementPoint,
                'updated_user'      => $actionUser
            );

            if($formula != commonUtils::FORMULA_TU_NHAP){
                DB::table('target_employee')->where('id', $eTETQId)->update($ePerformEmployee);
            }
        }
    }

    private function writeLog($dataLog){
        $functionName   = $dataLog['functionName'];
        $action         = $dataLog['action'];
        $url            = $dataLog['url'];
        $newValue       = $dataLog['newValue'];
        $createdUser    = $dataLog['createdUser'];

        $data = array(
            'function_name' => $functionName,
            'action'        => $action,
            'url'           => $url,
            'id_row'        => 0,
            'old_value'     => '',
            'new_value'     => $newValue,
            'created_user'  => $createdUser
        );

        #Write log override here
        DB::table('kpi_log')->insert($data);
    }

    private function checkLockData($year, $month, $applyDate, $type, $companyId){

        switch($type) {
            case 0:

                $sqlLock = "
                    SELECT *
                    FROM `important_level_corporation`
                    WHERE inactive = 0
                    AND `lock` = 1
                    AND `apply_date` = '".$applyDate."'
                ";

                $objLockDB = DB::select(DB::raw($sqlLock));

                if(count($objLockDB) > 0){
                    return 1;
                }

                break;
            case 1:

                $sqlLock = "
                    SELECT *
                    FROM `important_level_company`
                    WHERE inactive = 0
                    AND `lock` = 1
                    AND `apply_date` = '".$applyDate."'
                ";

                $objLockDB = DB::select(DB::raw($sqlLock));

                if(count($objLockDB) > 0){
                    return 1;
                }

                break;
            case 2:

                $sqlLock = "
                    SELECT DATE_FORMAT(expire_date_il,'%Y-%m-%d') as date_lock, `lock`, id
                    FROM `lock`
                    WHERE  `ofyear` = ".$year."
                    AND `ofmonth` = ".$month."
                    AND `company_id` = ".$companyId."
                ";

                $objLockDB = DB::select(DB::raw($sqlLock));

                if(count($objLockDB) == 1){

                    if($objLockDB[0]->lock == 1){
                        if(strtotime(date('Y-m-d')) > strtotime($objLockDB[0]->date_lock)){
                            return 1;
                        }
                    }

                }

                return 0;

                break;
            case 3:

                $sqlLock = "
                    SELECT DATE_FORMAT(expire_date_target,'%Y-%m-%d') as date_lock, `target_lock`, id
                    FROM `lock`
                    WHERE  `ofyear` = ".$year."
                    AND `ofmonth` = ".$month."
                    AND `company_id` = ".$companyId."
                ";

                $objLockDB = DB::select(DB::raw($sqlLock));

                if(count($objLockDB) == 1){

                    if($objLockDB[0]->target_lock == 1){
                        if(strtotime(date('Y-m-d')) > strtotime($objLockDB[0]->date_lock)){
                            return 1;
                        }
                    }

                }

                break;
            case 4:

                $sqlLock = "
                    SELECT DATE_FORMAT(expire_date_perform,'%Y-%m-%d') as date_lock, `perform_lock`, id
                    FROM `lock`
                    WHERE  `ofyear` = ".$year."
                    AND `ofmonth` = ".$month."
                    AND `company_id` = ".$companyId."
                ";

                $objLockDB = DB::select(DB::raw($sqlLock));

                if(count($objLockDB) == 1){

                    if($objLockDB[0]->perform_lock == 1){
                        if(strtotime(date('Y-m-d')) > strtotime($objLockDB[0]->date_lock)){
                            return 1;
                        }
                    }

                }

                break;
        }

        return  0;
    }
}

?>