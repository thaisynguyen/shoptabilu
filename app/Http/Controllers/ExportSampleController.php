<?php namespace App\Http\Controllers;
date_default_timezone_set('Asia/Ho_Chi_Minh');
use Illuminate\Http\Request;
use Session;
use DB;
use Utils\commonUtils;
use Convenient\excelUtils;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Cell;

class ExportSampleController extends AppController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function outputFile($fileName, PHPExcel $objPHPExcel)
    {
        $filename = $fileName . '_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //downloadable file is in Excel 2003 format (.xls)
        $objWriter->save('php://output');
    }
    /* *****************************************************************************************************************
     * Define function exportSamplePriorityCorporation bellow
     * exportSamplePriorityCorporation : Tỷ trọng Công ty Mobifone
     * ****************************************************************************************************************/
    public function exportSamplePriorityCorporation()
    {


        $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/sampleImportPriorityCorporation.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);


        //prepare download
        $fileName = "sampleImportPriorityCorporation";
        $this->outputFile($fileName, $objPHPExcel);
    }
    /* *****************************************************************************************************************
     * Define function exportSamplePriorityCompany bellow
     * exportSamplePriorityCompany : Tỷ trọng Phòng/Đài/MBF HCM
     * ****************************************************************************************************************/
    public function exportSamplePriorityCompany()
    {


        $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/sampleImportPriorityCompany.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);


        //prepare download
        $fileName = "sampleImportPriorityCompany";
        $this->outputFile($fileName, $objPHPExcel);
    }
    /* *****************************************************************************************************************
     * Define function exportSamplePriorityArea bellow
     * exportSamplePriorityArea : Tỷ trọng Tổ/Quận/Huyện
     * ****************************************************************************************************************/
    public function exportSamplePriorityArea()
    {


        $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/sampleImportPriorityArea.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);


        //prepare download
        $fileName = "sampleImportPriorityArea";
        $this->outputFile($fileName, $objPHPExcel);
    }
    /* *****************************************************************************************************************
     * Define function exportSamplePriorityPosition bellow
     * exportSamplePriorityPosition : Tỷ trọng chức danh
     * ****************************************************************************************************************/
    public function exportSamplePriorityPosition()
    {


        $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/sampleImportPriorityPosition.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);


        //prepare download
        $fileName = "sampleImportPriorityPosition";
        $this->outputFile($fileName, $objPHPExcel);
    }

    /* *****************************************************************************************************************
     * Define function exportSampleGoalArea bellow
     * exportSampleGoalArea : Kế hoạch Tổ/Quận/Huyện
     * ****************************************************************************************************************/
    public function exportSampleGoalArea()
    {
        try{
            $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/sampleImportGoalArea.xlsx");
            $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(80);
            $sheet = $objPHPExcel->setActiveSheetIndex(0);
            $currentUser = Session::get('sDataUser');
            $currentYear  = (int)date("Y");
            $currentMonth = (int)date("m") - 1;
            //write company
            $objPHPExcel->getActiveSheet()->setCellValue('B6', $currentUser->company_code)
                ->setCellValue('C6', $currentUser->company_name)
                ->setCellValue('F4', $currentYear)
                ->setCellValue('F5', $currentMonth)
                ->setCellValue('F6', $currentMonth)
                ->setCellValue('F7', $currentUser->company_code)
            ;
            $objPHPExcel->getActiveSheet()->getStyle('F4:F6')->getFont()->getColor()->setRGB(commonUtils::COLOR_RED);
            $objPHPExcel->getActiveSheet()->getStyle('B6')->getFont()->getColor()->setRGB(commonUtils::COLOR_RED);
            // get data
            $select = 'select ila.*, g.goal_name, g.goal_code, g.parent_id, a.area_name,
                          a.area_code, u.unit_code, u.unit_name, g.formula
                   from important_level_area as ila
                   left join goal as g on g.id = ila.goal_id
                   left join area as a on a.id = ila.area_id
                   left join unit as u on u.id = g.unit_id
                   where ila.company_id = '.$currentUser->company_id.'
                   and ila.year = '.$currentYear;

            $data = DB::select(DB::raw($select));
            if(count($data) > 0){
                $arrayArea = array();
                $arrayDataArea = array();
                foreach($data as $rowData){
                    if(in_array($rowData->area_code, $arrayArea) != 1){
                        $arrayArea[count($arrayArea)] = $rowData->area_code;
                        $index = count($arrayDataArea);
                        $arrayDataArea[$index]['area_code'] = $rowData->area_code;
                        $arrayDataArea[$index]['area_name'] = $rowData->area_name;
                    }
                }

                $arrayGoal              = array();
                $arrayDataGoalParent    = array();
                $arrayDataGoalChild     = array();

                foreach($data as $rowData){
                    if(in_array($rowData->goal_code, $arrayGoal) != 1){
                        $arrayGoal[count($arrayGoal)] = $rowData->goal_code;
                        if($rowData->parent_id == 0){
                            $index = count($arrayDataGoalParent);
                            $arrayDataGoalParent[$index]['goal_code'] = $rowData->goal_code;
                            $arrayDataGoalParent[$index]['goal_name'] = $rowData->goal_name;
                            $arrayDataGoalParent[$index]['parent_id'] = $rowData->parent_id;
                            $arrayDataGoalParent[$index]['unit_code'] = $rowData->unit_name;
                            $arrayDataGoalParent[$index]['id']        = $rowData->goal_id;
                        } else {
                            $index = count($arrayDataGoalChild);
                            $arrayDataGoalChild[$index]['goal_code'] = $rowData->goal_code;
                            $arrayDataGoalChild[$index]['goal_name'] = $rowData->goal_name;
                            $arrayDataGoalChild[$index]['parent_id'] = $rowData->parent_id;
                            $arrayDataGoalChild[$index]['unit_code'] = $rowData->unit_name;
                            $arrayDataGoalChild[$index]['id']        = $rowData->goal_id;
                            $arrayDataGoalChild[$index]['formula']   = $rowData->formula;
                        }
                    }
                }

                // lấy dữ liệu của Phòng/Đài/MBF HCM dựa vào apply_date cao nhất, sai hay đúng thì bên import sẽ chặn lại
                $applyDate   = $this->getApplyDate4Company($currentUser->company_id, $currentYear, date("Y-m-d"));
                $dataCompany = $this->getImportantLevelCompany($currentUser->company_id, $applyDate);

                //write goal_code, goal_name, type, unit, A->E
                $starRow = 9;
                $index   = 1;
                $arrayIndexParent = array();
                $arrChildRow = array();

                $arrChildGoal = array();
                $iCG = 0;

                foreach($arrayDataGoalParent as $parent){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$starRow, $index++)
                        ->setCellValue('B'.$starRow, $parent['goal_code'])
                        ->setCellValue('C'.$starRow, $parent['goal_name'])
                        ->setCellValue('D'.$starRow, 0)
                        ->setCellValue('E'.$starRow, $parent['unit_code']);
                    $arrayIndexParent[count($arrayIndexParent)] = $starRow;
                    $starRow++;
                    foreach($arrayDataGoalChild as $child){
                        if($child['parent_id'] == $parent['id']){

                            $arrChildRow[]  = $starRow;

                            $arrChildGoal[$iCG]['indexRow'] = $starRow;
                            $arrChildGoal[$iCG]['goalId']   = $child['id'];
                            $arrChildGoal[$iCG]['formula']  = $child['formula'];
                            $iCG++;

                            $objPHPExcel->getActiveSheet()
                                ->setCellValue('A'.$starRow, $index++)
                                ->setCellValue('B'.$starRow, $child['goal_code'])
                                ->setCellValue('C'.$starRow, $child['goal_name'])
                                ->setCellValue('D'.$starRow, 1)
                                ->setCellValue('E'.$starRow, $child['unit_code']);
                            $starRow++;
                        }
                    }
                }

                // write area_name, area_code
                $countArea = count($arrayDataArea);
                $sheet->insertNewColumnBefore('G', $countArea);

                $rowArea            = 7;
                $rowNodeArea        = 11;
                $indexColumnAreaCode= 6;
                $columnAreaCodeNode = PHPExcel_Cell::stringFromColumnIndex(7 + $countArea);
                $columnAreaNameNode = PHPExcel_Cell::stringFromColumnIndex(8 + $countArea);
                $columnAreaCode = '';
                foreach($arrayDataArea as $dArea){
                    $columnAreaCode = PHPExcel_Cell::stringFromColumnIndex($indexColumnAreaCode);
                    $indexColumnAreaCode++;
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnAreaCode)->setWidth(16);

                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($columnAreaCode.$rowArea, $dArea['area_code'])
                        ->setCellValue($columnAreaCode.($rowArea+1), 'Kế hoạch')
                        ->setCellValue($columnAreaCodeNode.$rowNodeArea, $dArea['area_code'])
                        ->setCellValue($columnAreaNameNode.$rowNodeArea, $dArea['area_name']);
                    $rowNodeArea++;
                }

                $endLabelColumn = $columnAreaCode;

                /* *********************************************************************************************************
                 * Draw check column
                 * ********************************************************************************************************/
                $labelCheck = excelUtils::getLabelColumn(excelUtils::getIndexColumn($columnAreaCode));
                excelUtils::setColumnWidth($objPHPExcel, $labelCheck, 22);
                $indexHeader = 7;
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($labelCheck.$indexHeader, 'Tổng Kế hoạch');

                /*foreach($arrChildRow as $childRow){
                    $checkValue = 'G'.$childRow.':'.$columnAreaCode.$childRow;
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($labelCheck.$childRow, '=sum('.$checkValue.')');
                }*/

                foreach($arrChildGoal as $childRow){
                    if(
                        $childRow['formula']    != commonUtils::FORMULA_LAY1SO
                        && $childRow['formula'] != commonUtils::FORMULA_TRUNG_BINH_CONG
                    ){
                        $checkValue = 'G'.$childRow['indexRow'].':'.$columnAreaCode.$childRow['indexRow'];
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($labelCheck.$childRow['indexRow'], '=sum('.$checkValue.')');
                    }else{
                        $checkValue = excelUtils::getLabelColumn((excelUtils::getIndexColumn('G') - 1)).$childRow['indexRow'];
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($labelCheck.$childRow['indexRow'], '='.$checkValue);
                    }
                }

                /* *******************************************************************************************************/



                excelUtils::fillBackGroundColor($objPHPExcel,'A7:'.$labelCheck.'8', excelUtils::COLOR_DARK);

                excelUtils::mergeCells($objPHPExcel, $labelCheck.'7:'.$labelCheck.'8');
                $sheet->getStyle("F10:".$labelCheck.($starRow-1))->getNumberFormat()->setFormatCode(excelUtils::STYLE_NUMBER);
                $objPHPExcel->getActiveSheet()->getStyle('B1:F6')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A7:' .$labelCheck.'7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A7:' .$labelCheck.'7')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                // border
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->getStyle('A7:'.$labelCheck.($starRow-1))->applyFromArray($styleArray);

                $objPHPExcel->getActiveSheet()->getStyle($columnAreaCodeNode.'11:'.$columnAreaNameNode.($rowNodeArea - 1))->applyFromArray($styleArray);

                $objPHPExcel->getActiveSheet()->getStyle($columnAreaCodeNode.'21:'.$columnAreaNameNode.($rowNodeArea-1))->applyFromArray($styleArray);
                foreach($arrayIndexParent as $in){
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$in.':'.$labelCheck.$in)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$in.':'.$labelCheck.$in)->getFill()
                        ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)->getStartColor()->setRGB('00FF00');
                }

                $startRow = 9;
                //data of company
                foreach($arrayDataGoalParent as $parent){
                    $target = 0;
                    foreach($dataCompany as $dComp){
                        if($dComp->goal_id == $parent['id']){
                            $target = $dComp->target_value;
                            break;
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$startRow++, $target);
                    foreach($arrayDataGoalChild as $child){
                        if($child['parent_id'] == $parent['id']){
                            $target = 0;
                            foreach($dataCompany as $dComp){
                                if($dComp->goal_id == $child['id']){
                                    $target = $dComp->target_value;
                                    break;
                                }
                            }
                            $objPHPExcel->getActiveSheet()->setCellValue('F'.$startRow++, $target);
                        }
                    }
                }
            }

            //prepare download
            $fileName = "sampleImportGoalArea";
            $this->outputFile($fileName, $objPHPExcel);
        }catch (Exception $e){
            $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/blank.xlsx");
            $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(80);
            $fileName = "sampleImportGoalArea";
            $this->outputFile($fileName, $objPHPExcel);
        }

    }

    /* *****************************************************************************************************************
     * Define function exportSampleGoalPosition bellow
     * exportSampleGoalPosition : Kế hoạch Chức danh
     * ****************************************************************************************************************/
    public function exportSampleGoalPosition()
    {
        $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/blank.xlsx");
        $currentYear = (int)date("Y");
        $currentMonth= (int)date("m");
//        $currentMonth= (int)date("m") - 1;
        $currentUser = Session::get('sDataUser');

        //commonUtils::pr($currentUser); die;

        //get data of important_level_position
        $select = 'select ilp.*, g.goal_name, g.goal_code, g.parent_id, a.area_name, a.area_code,
                          p.position_name, p.position_code, u.unit_code, u.unit_name, g.formula
                   from important_level_position as ilp
                   left join goal as g on g.id = ilp.goal_id
                   left join area as a on a.id = ilp.area_id
                   left join unit as u on u.id = g.unit_id
                   left join `position` as p on p.id = ilp.position_id
                   where ilp.company_id = '.$currentUser->company_id.'
                   and ilp.year = '.$currentYear.'
                   and ilp.month = '.$currentMonth
        ;
        if($currentUser->id != 0){
            $select .= " AND ilp.area_id =  ".$currentUser->area_id." ";
        }


        $data = DB::select(DB::raw($select));

        if(count($data) > 0){

            $sqlGoalArea = "
                SELECT *
                FROM target_area
                WHERE inactive = 0
                AND company_id = ".$currentUser->company_id."
                AND year = ".$currentYear."
                AND month = ".$currentMonth."
            ";
            if($currentUser->id != 0){
                $sqlGoalArea .= " AND area_id =  ".$currentUser->area_id." ";
            }
            $dataGoalArea = DB::select(DB::raw($sqlGoalArea));
            //get unique area
            $arrayArea = array();
            $arrayDataArea = array();
            foreach($data as $da){
                if(in_array($da->area_code, $arrayArea) != 1){
                    $arrayArea[count($arrayArea)] = $da->area_code;
                    $arrayPos = array();
                    $arrayDataPos = array();
                    foreach($data as $daPos){
                        if(in_array($daPos->position_code, $arrayPos) != 1 && $daPos->area_code == $da->area_code){
                            $arrayPos[count($arrayPos)] = $daPos->position_code;
                            $index = count($arrayDataPos);
                            $arrayDataPos[$index]['position_code'] = $daPos->position_code;
                            $arrayDataPos[$index]['position_name'] = $daPos->position_name;
                        }
                    }
                    $arrayGoal = array();
                    $arrayDataGoalParent = array();
                    $arrayDataGoalChild = array();
                    foreach($data as $daPos){
                        if(
                            in_array($daPos->goal_code, $arrayGoal) != 1
                            && $daPos->area_code == $da->area_code
                            && $daPos->position_code == $da->position_code
                        ){
                            $arrayGoal[count($arrayGoal)] = $daPos->goal_code;
                            if($daPos->parent_id == 0){
                                $index = count($arrayDataGoalParent);
                                $arrayDataGoalParent[$index]['goal_code'] = $daPos->goal_code;
                                $arrayDataGoalParent[$index]['goal_name'] = $daPos->goal_name;
                                $arrayDataGoalParent[$index]['unit_name'] = $daPos->unit_name;
                                $arrayDataGoalParent[$index]['parent_id'] = 0;
                                $arrayDataGoalParent[$index]['goal_id']   = $daPos->goal_id;
                            } else {
                                $index = count($arrayDataGoalChild);
                                $arrayDataGoalChild[$index]['goal_code'] = $daPos->goal_code;
                                $arrayDataGoalChild[$index]['goal_name'] = $daPos->goal_name;
                                $arrayDataGoalChild[$index]['unit_name'] = $daPos->unit_name;
                                $arrayDataGoalChild[$index]['parent_id'] = $daPos->parent_id;
                                $arrayDataGoalChild[$index]['goal_id']   = $daPos->goal_id;
                                $arrayDataGoalChild[$index]['formula']   = $daPos->formula;
                            }

                        }
                    }


                    $index = count($arrayDataArea);
                    $arrayDataArea[$index]['area_id']         = $da->area_id;
                    $arrayDataArea[$index]['area_name']       = $da->area_name;
                    $arrayDataArea[$index]['area_code']       = $da->area_code;
                    $arrayDataArea[$index]['arrayPosition']   = $arrayDataPos;
                    $arrayDataArea[$index]['arrayGoalParent'] = $arrayDataGoalParent;
                    $arrayDataArea[$index]['arrayGoalChild']  = $arrayDataGoalChild;
                }
            }


            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );

            for($a = 0; $a < count($arrayDataArea); $a++){
                if($a > 0){
                    $objWorkSheet = $objPHPExcel->createSheet($a);
                }
                $objPHPExcel->setActiveSheetIndex($a);
                $objPHPExcel->getActiveSheet()->setTitle($this->convertNameSheet($this->convertNameSheet($arrayDataArea[$a]['area_code'])));
                $arrayPos        = $arrayDataArea[$a]['arrayPosition'];
                $arrayGoalParent = $arrayDataArea[$a]['arrayGoalParent'];
                $arrayGoalChild  = $arrayDataArea[$a]['arrayGoalChild'];

                $starColumnNode     = 7 + count($arrayPos);
                $firstColumnNode    = PHPExcel_Cell::stringFromColumnIndex($starColumnNode++);
                $secondColumnNode   = PHPExcel_Cell::stringFromColumnIndex($starColumnNode++);
                $thirdColumnNode    = PHPExcel_Cell::stringFromColumnIndex($starColumnNode++);

                // paint default
                $objPHPExcel->getActiveSheet()->setCellValue('C2', 'IMPORT KẾ HOẠCH(CHỈ TIÊU) CHO CHỨC DANH')
                                              ->setCellValue('D4','Năm')
                                              ->setCellValue('E4',$currentYear)
                                              ->setCellValue('C4',excelUtils::ALERT_MONTH_YEAR)
                                              ->setCellValue('D5','Từ tháng')
                                              ->setCellValue('E5',$currentMonth)
                                              ->setCellValue('F5','Đến tháng')
                                              ->setCellValue('G5',$currentMonth)
                                              ->setCellValue('B6',$currentUser->company_code)
                                              ->setCellValue('C6',$currentUser->company_name)
                                              ->setCellValue('B7',$arrayDataArea[$a]['area_code'])
                                              ->setCellValue('C7',$arrayDataArea[$a]['area_name'])
                                              ->setCellValue('A8','STT')
                                              ->setCellValue('B8','Mã')
                                              ->setCellValue('C8','Mục tiêu')
                                              ->setCellValue('D8','Loại')
                                              ->setCellValue('E8','Đơn vị tính')
                                              ->setCellValue('F8','Kế hoạch Tổ/Quận/Huyện')
                                              ;
                $startNodePos   = $this->paintNodeTypeUnit($firstColumnNode, $secondColumnNode, $thirdColumnNode, $objPHPExcel, $styleArray, 10);
                $startColumnPos = 6;
                $startRowPos    = $startNodePos + 1;

                $objPHPExcel->getActiveSheet()->setCellValue($firstColumnNode.'24', 'Chức danh');

                $columnPos = '';

                if($currentUser->id != 0){
                    $objILPDB = $this->getImportantLevelPosition($currentUser->company_id, $currentUser->area_id, 0, $currentYear, $currentMonth, $currentMonth);
                }else{
                    $objILPDB = $this->getImportantLevelPosition($currentUser->company_id, 0, 0, $currentYear, $currentMonth, $currentMonth);
                }


                $arrCP = array();
                $iCP = 0;

                foreach($arrayPos as $arrPos){
                    $columnPos = PHPExcel_Cell::stringFromColumnIndex($startColumnPos++);
                    excelUtils::setColumnWidth($objPHPExcel, $columnPos, 17);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnPos.'8', $arrPos['position_code'])
                                                  ->setCellValue($secondColumnNode.$startRowPos, $arrPos['position_code'])
                                                  ->setCellValue($thirdColumnNode.$startRowPos, $arrPos['position_name']);
                    $arrCP[$iCP]['areaCode']     = $arrayDataArea[$a]['area_code'];
                    $arrCP[$iCP]['positionCode'] = $arrPos['position_code'];
                    $arrCP[$iCP]['column']       = $columnPos;
                    $iCP++;

                    $startRowPos++;
                }

                $index = 0;
                $startRow = 8;

                $arrChildGoal = array();
                $iCG = 0;

                $labelCheck = excelUtils::getLabelColumn($startColumnPos);

                foreach($arrayGoalParent as $parent){
                    $index++;
                    $startRow++;
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$startRow, $index)
                                                    ->setCellValue('B'.$startRow, $parent['goal_code'])
                                                    ->setCellValue('C'.$startRow, $parent['goal_name'])
                                                    ->setCellValue('D'.$startRow, 0)
                                                    ->setCellValue('E'.$startRow, $parent['unit_name']);

                    excelUtils::fillBackGroundColor($objPHPExcel, 'A'.$startRow.':'.$labelCheck.$startRow, commonUtils::COLOR_GREEN);

                    foreach($arrayGoalChild as $child){
                        if($child['parent_id'] == $parent['goal_id']){
                            $index++;
                            $startRow++;
                            $targetArea = 0;
                            foreach($dataGoalArea as $gA){
                                if($gA->area_id == $arrayDataArea[$a]['area_id'] && $gA->goal_id == $child['goal_id']){
                                    $targetArea = $gA->target_value;
                                    break;
                                }
                            }

                            $arrChildGoal[$iCG]['indexRow'] = $startRow;
                            $arrChildGoal[$iCG]['goalId']   = $child['goal_id'];
                            $arrChildGoal[$iCG]['formula']  = $child['formula'];
                            $iCG++;

                            $objPHPExcel->getActiveSheet()
                                ->setCellValue('A'.$startRow, $index)
                                ->setCellValue('B'.$startRow, $child['goal_code'])
                                ->setCellValue('C'.$startRow, $child['goal_name'])
                                ->setCellValue('D'.$startRow, 1)
                                ->setCellValue('F'.$startRow, $targetArea)
                                ->setCellValue('E'.$startRow, $child['unit_name']);
                        }
                    }
                }

                $arrFormulaRow = array();
                $iFR = 0;

                foreach($arrCP as $cp){
                    foreach($objILPDB as $ilp){
                        if(
                            commonUtils::compareTwoString($ilp->position_code , $cp['positionCode']) == 1
                            && commonUtils::compareTwoString($ilp->area_code , $cp['areaCode']) == 1
                        ){
                            foreach($arrChildGoal as $childGoal){
                                if($ilp->goal_id == $childGoal['goalId']){
                                    excelUtils::fillBackGroundColor($objPHPExcel, $cp['column'].$childGoal['indexRow'], excelUtils::COLOR_DARK);

                                    $arrFormulaRow[$iFR]['goalId']       = $childGoal['goalId'];
                                    $arrFormulaRow[$iFR]['formula']      = $childGoal['formula'];
                                    $arrFormulaRow[$iFR]['indexRow']     = $childGoal['indexRow'];
                                    $arrFormulaRow[$iFR]['positionCode'] = $cp['positionCode'];
                                    $arrFormulaRow[$iFR]['cellColor']    = $cp['column'].$childGoal['indexRow'];
                                    $iFR++;

                                }
                            }
                        }
                    }

                }



                /* *****************************************************************************************************
                 * Draw sum column
                 * *****************************************************************************************************/

                excelUtils::setCellValue($objPHPExcel, $labelCheck.'8', 'Tổng Kế hoạch');


                foreach($arrFormulaRow as $fRow){

                    $fGoalId        = $fRow['goalId'];
                    $fFormula       = $fRow['formula'];
                    $fIndexRow      = $fRow['indexRow'];
                    $fPositionCode  = $fRow['positionCode'];
                    $fCellColor     = $fRow['cellColor'];
                    $checkValue = " ";

                    switch ($fFormula) {
                        case commonUtils::FORMULA_LAY1SO:
                            $checkValue = '='.$fCellColor;
                            break;
                        case commonUtils::FORMULA_TU_NHAP:

                            foreach($arrFormulaRow as $findRow){
                                if(
                                    commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_TQ) == 1
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    $checkValue = '='.$findRow['cellColor'];
                                    break;
                                }
                            }

                            break;
                        case commonUtils::FORMULA_TRUNG_BINH_CONG:

                            $div = 0;
                            $strSum = "";
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    $findRow['indexRow'] == $fIndexRow
                                ){
                                    $div++;
                                    if($strSum == ""){
                                        $strSum = $findRow['cellColor'];
                                    }else{
                                        $strSum .= "+".$findRow['cellColor'];
                                    }
                                }
                            }

                            if($div != 0){
                                $checkValue = '=('.$strSum.')/'.$div;
                            }

                            break;
                        case commonUtils::FORMULA_TONG_NVBH:

                            foreach($arrFormulaRow as $findRow){
                                if(
                                    commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_NVBH) == 1
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    $checkValue = '='.$findRow['cellColor'];
                                    break;
                                }
                            }

                            break;
                        case commonUtils::FORMULA_TONG_KAM_AM:
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_KAM_AM) == 1
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    $checkValue = '='.$findRow['cellColor'];
                                    break;
                                }
                            }
                            break;
                        case commonUtils::FORMULA_TONG_CVKHCN_CVKHDN:
                            $strSum = "";
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    (   commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHDN) == 1
                                        || commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHCN) == 1
                                    )
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    if($strSum == ""){
                                        $strSum = $findRow['cellColor'];
                                    }else{
                                        $strSum .= "+".$findRow['cellColor'];
                                    }

                                }
                            }

                            $checkValue = '='.$strSum;

                            break;
                        case commonUtils::FORMULA_TONG_CVKHCN_CHT:
                            $strSum = "";
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    (   commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHCN) == 1
                                        || commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CHT) == 1
                                    )
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    if($strSum == ""){
                                        $strSum = $findRow['cellColor'];
                                    }else{
                                        $strSum .= "+".$findRow['cellColor'];
                                    }

                                }
                            }

                            $checkValue = '='.$strSum;
                            break;
                        case commonUtils::FORMULA_TONG_GDV:
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_GDV) == 1
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    $checkValue = '='.$findRow['cellColor'];
                                    break;
                                }
                            }
                            break;
                        case commonUtils::FORMULA_TONG_CVKHCN_CVKHDN_CHT:
                            $strSum = "";
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    (   commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHCN) == 1
                                        || commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CHT) == 1
                                        || commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHDN) == 1
                                    )
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    if($strSum == ""){
                                        $strSum = $findRow['cellColor'];
                                    }else{
                                        $strSum .= "+".$findRow['cellColor'];
                                    }

                                }
                            }

                            $checkValue = '='.$strSum;
                            break;
                        case commonUtils::FORMULA_TONG_CVKHCN:
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHCN) == 1
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    $checkValue = '='.$findRow['cellColor'];
                                    break;
                                }
                            }
                            break;
                        case commonUtils::FORMULA_TONG_CVKHDN:
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHDN) == 1
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    $checkValue = '='.$findRow['cellColor'];
                                    break;
                                }
                            }
                            break;
                        case commonUtils::FORMULA_TONG_CVKHDN_CHT:
                            $strSum = "";
                            foreach($arrFormulaRow as $findRow){
                                if(
                                    (   commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CV_KHDN) == 1
                                        || commonUtils::compareTwoString($findRow['positionCode'], commonUtils::POSITION_CODE_CHT) == 1
                                    )
                                    && $findRow['indexRow'] == $fIndexRow
                                ){
                                    if($strSum == ""){
                                        $strSum = $findRow['cellColor'];
                                    }else{
                                        $strSum .= "+".$findRow['cellColor'];
                                    }

                                }
                            }

                            $checkValue = '='.$strSum;
                            break;
                    }

                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($labelCheck.$fIndexRow, $checkValue);

                }



                /* ****************************************************************************************************/

                excelUtils::setColumnWidth($objPHPExcel, $labelCheck, 20);
                excelUtils::setBorderCell($objPHPExcel, $firstColumnNode.'24:'.$thirdColumnNode.($startRowPos-1), $styleArray);
                excelUtils::setBorderCell($objPHPExcel, 'A8:'.$labelCheck.$startRow, $styleArray);
                excelUtils::mergeCells($objPHPExcel, $firstColumnNode.'24:'.$firstColumnNode.($startRowPos-1));
                excelUtils::setHorizontal($objPHPExcel,$firstColumnNode.'24:'.$firstColumnNode.($startRowPos-1), \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                excelUtils::setVertical($objPHPExcel,$firstColumnNode.'24:'.$firstColumnNode.($startRowPos-1), \PHPExcel_Style_Alignment::VERTICAL_CENTER);
                excelUtils::setBold($objPHPExcel, $firstColumnNode.'24');
                excelUtils::setBold($objPHPExcel, 'A1:'.$labelCheck.'8');
                excelUtils::setColumnWidth($objPHPExcel,$firstColumnNode, 10);
                excelUtils::setColumnWidth($objPHPExcel,$secondColumnNode, 20);
                excelUtils::setColumnWidth($objPHPExcel,$thirdColumnNode, 43);

                /*------------------------------------------------------------------------------------------------------------*/
                #Set width for column
                $hashColumn = range('A', 'F');
                $count = 0;
                foreach ($hashColumn as $key => $value) {
                    $dimension = 0;
                    switch ($value) {
                        case 'A':
                        case 'D':
                            $dimension = 10;
                            break;
                        case 'B':
                        case 'E':
                            $dimension = 12;
                            break;
                        case 'F':
                            $dimension = 18;
                            break;
                        case 'C':
                            $dimension = 35;
                            break;
                    }

                    excelUtils::setColumnWidth($objPHPExcel, $value, $dimension);
                    $count++;
                }


                excelUtils::setHorizontal($objPHPExcel, 'A8:'.$columnPos.'8', \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                excelUtils::setHorizontal($objPHPExcel, 'A9:B'.$startRow, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                excelUtils::setHorizontal($objPHPExcel, 'D9:D'.$startRow, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                excelUtils::setVertical($objPHPExcel, 'A8:'.$labelCheck.'8', \PHPExcel_Style_Alignment::VERTICAL_CENTER);
                excelUtils::setFontSize($objPHPExcel, 'C2', 18);
                excelUtils::fillBackGroundColor($objPHPExcel, 'A8:'.$labelCheck.'8', excelUtils::COLOR_DARK);
                excelUtils::setWrapText($objPHPExcel, 'A8:'.$columnPos.'8');
                excelUtils::setRowHeight($objPHPExcel, 8, 30);
                excelUtils::setZoomSheet($objPHPExcel, 80);
                excelUtils::setFontColor($objPHPExcel, 'B6:B7', excelUtils::COLOR_RED);
                excelUtils::setFontColor($objPHPExcel, 'E4:E5', excelUtils::COLOR_RED);
                excelUtils::setFontColor($objPHPExcel, 'G5', excelUtils::COLOR_RED);
                excelUtils::setFontColor($objPHPExcel, 'C2', excelUtils::COLOR_RED);
                excelUtils::setFontColor($objPHPExcel, 'C4', excelUtils::COLOR_RED);
                excelUtils::formatCell($objPHPExcel, 'F9:'.$labelCheck.$startRow, 1, excelUtils::STYLE_NUMBER);

            }



        }
        //prepare download
        $fileName = "sampleImportGoalPosition";
        $this->outputFile($fileName, $objPHPExcel);
    }

    /* *****************************************************************************************************************
     * Define function exportSampleGoalEmployee bellow
     * exportSampleGoalEmployee : Kế hoạch Nhân viên
     * ****************************************************************************************************************/
    public function exportSampleGoalEmployee()
    {
        $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/blank.xlsx");
        $currentYear = (int)date("Y");
//        $currentYear = 2015;
//        $currentMonth= 1;
        $currentMonth= (int)date("m");
        $currentUser = Session::get('sDataUser');

        $objTargetPositionDB = $this->getTargetPosition(
            $currentUser->company_id
            , $currentUser->area_id
            , 0
            , $currentYear
            , $currentMonth
            , $currentMonth
        );

        if(count($objTargetPositionDB) == 0){
            $fileName = "sampleImportGoalEmployee";
            $this->outputFile($fileName, $objPHPExcel);
        }

        $arrPosition = array();
        $iP = 0;

        $sqlGoal = "
            SELECT g.*, u.unit_name
            FROM goal g
            LEFT JOIN unit u ON u.id = g.unit_id
            WHERE g.inactive = 0
        ";

        $objGoalDB = DB::select(DB::raw($sqlGoal));



        foreach($objTargetPositionDB as $targetPosition){
            if(commonUtils::compareTwoString($targetPosition->position_code, commonUtils::POSITION_CODE_TQ) == 0){
                if(count($arrPosition) == 0){
                    $arrPosition[$iP]['positionId']   = $targetPosition->position_id;
                    $arrPosition[$iP]['positionCode'] = $targetPosition->position_code;
                    $arrPosition[$iP]['positionName'] = $targetPosition->position_name;
                    $iP++;
                }else{
                    $exist =  0;
                    foreach($arrPosition as $position){
                        if($position['positionId'] == $targetPosition->position_id){
                            $exist = 1;
                            break;
                        }
                    }
                    if($exist == 0){
                        $arrPosition[$iP]['positionId']   = $targetPosition->position_id;
                        $arrPosition[$iP]['positionCode'] = $targetPosition->position_code;
                        $arrPosition[$iP]['positionName'] = $targetPosition->position_name;
                        $iP++;
                    }
                }
            }
        }

        $objEmployeeDB = $this->getEmployees($currentUser->company_id, $currentUser->area_id, 0);

        $indexSheet = 0;
        foreach($arrPosition as $position){

            $positionId   = $position['positionId'];
            $positionCode = $position['positionCode'];
            $positionName = $position['positionName'];

            $arrParent = array();
            $iGP = 0;

            foreach($objTargetPositionDB as $targetPosition){

                if($targetPosition->position_id == $positionId){
                    if($targetPosition->parent_id != 0){

                        $goalName   = "";
                        $parentCode = "";
                        foreach($objGoalDB as $goal){
                            if($goal->id == $targetPosition->parent_id ){
                                $goalName   = $goal->goal_name;
                                $parentCode = $goal->goal_code;
                                break;
                            }
                        }

                        if(count($arrParent) == 0){
                            $arrParent[$iGP]['goalId']   = $targetPosition->parent_id;
                            $arrParent[$iGP]['goalCode'] = $parentCode;
                            $arrParent[$iGP]['goalName'] = $goalName;
                            $iGP++;
                        }else{
                            $exist = 0;
                            foreach($arrParent as $parent){
                                if($parent['goalId'] == $targetPosition->parent_id){
                                    $exist = 1;
                                    break;
                                }
                            }
                            if($exist == 0){
                                $arrParent[$iGP]['goalId']   = $targetPosition->parent_id;
                                $arrParent[$iGP]['goalCode'] = $parentCode;
                                $arrParent[$iGP]['goalName'] = $goalName;
                                $iGP++;
                            }
                        }
                    }
                }

            }

            if($indexSheet != 0){
                excelUtils::createSheet($objPHPExcel, $indexSheet);
            }

            excelUtils::activeSheet($objPHPExcel, $indexSheet);
            excelUtils::setDefaultFont($objPHPExcel, excelUtils::FONT_CALIBRI);
            excelUtils::setDefaultFontSize($objPHPExcel, 10);
            excelUtils::setZoomSheet($objPHPExcel, 80);
            excelUtils::setTitle($objPHPExcel, $this->convertNameSheet($positionCode));

            $objPHPExcel->getActiveSheet()
                ->setCellValue('D2', commonUtils::TITLE_IMPORT_GOAL_EMPLOYEE)
                ->setCellValue('D4', excelUtils::ALERT_MONTH_YEAR)
                ->setCellValue('C6', $currentUser->company_code)
                ->setCellValue('C7', $currentUser->area_code)
                ->setCellValue('C8', $positionCode)
                ->setCellValue('D6', $currentUser->company_name)
                ->setCellValue('D7', $currentUser->area_name)
                ->setCellValue('D8', $positionName)
                ->setCellValue('F4', 'Năm')
                ->setCellValue('F5', 'Từ tháng')
                ->setCellValue('F6', 'Đến tháng')
                ->setCellValue('G4', $currentYear)
                ->setCellValue('G5', $currentMonth)
                ->setCellValue('G6', $currentMonth)
            ;

            $indexHeader = 10;

            $objPHPExcel->getActiveSheet()
                ->setCellValue('B'.$indexHeader, 'STT')
                ->setCellValue('C'.$indexHeader, 'Mã')
                ->setCellValue('D'.$indexHeader, 'Tên mục tiêu')
                ->setCellValue('E'.$indexHeader, 'Loại')
                ->setCellValue('F'.$indexHeader, 'Đơn vị tính')
                ->setCellValue('G'.$indexHeader, $positionCode)
                ->setCellValue('G'.($indexHeader + 1), 'Kế hoạch')
            ;

            $startRow = 12;
            $no       = 1;

            $arrPRow     = array();
            $arrChildRow = array();
            $iCR = 0;

            foreach($arrParent as $parent){

                $objPHPExcel->getActiveSheet()
                    ->setCellValue('B'.$startRow, $no++)
                    ->setCellValue('C'.$startRow, $parent['goalCode'])
                    ->setCellValue('D'.$startRow, $parent['goalName'])
                    ->setCellValue('E'.$startRow, 0)
                ;

                $arrPRow[]      = $startRow;
                $childRow       = $startRow + 1;

                foreach($objTargetPositionDB as $targetPosition){
                    if(
                        $targetPosition->position_id == $positionId
                        && $targetPosition->parent_id == $parent['goalId']
                    ) {

                        $unitName = "";
                        $formula = "";
                        foreach($objGoalDB as $goal){
                            if($goal->id == $targetPosition->goal_id ){
                                $unitName = $goal->unit_name;
                                $formula  = $goal->formula;
                                break;
                            }
                        }

                        $arrChildRow[$iCR]['indexRow']  = $childRow;
                        $arrChildRow[$iCR]['formula']   = $formula;
                        $iCR++;

                        $objPHPExcel->getActiveSheet()
                            ->setCellValue('B'.$childRow, $no++)
                            ->setCellValue('C'.$childRow, $targetPosition->goal_code)
                            ->setCellValue('D'.$childRow, $targetPosition->goal_name)
                            ->setCellValue('E'.$childRow, 1)
                            ->setCellValue('F'.$childRow, $unitName)
                            ->setCellValue('G'.$childRow, $targetPosition->target_value)
                        ;
                        $childRow++;
                    }
                }
                $startRow = $childRow;

            }

            $indexColumn = excelUtils::getIndexColumn('H') - 1;

            $arrEmployee = array();
            $iE = 0;
            $lastColumn = "B";

            foreach($objEmployeeDB as $employee){

                $validTerminate = $this->checkTerminateDate($employee->terminate_date, $currentYear, $currentMonth);

                if(
                    $employee->admin == 0
                    && $employee->position_id == $positionId
                    && $employee->id != 0
                    && $validTerminate == 0
                ){

                    $arrEmployee[$iE]['employeeCode'] = $employee->code;
                    $arrEmployee[$iE]['employeeName'] = $employee->name;
                    $iE++;

                    $labelColumn = excelUtils::getLabelColumn($indexColumn);
                    $lastColumn = $labelColumn;

                    excelUtils::setColumnWidth($objPHPExcel, $labelColumn, 20);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($labelColumn.$indexHeader, $employee->code)
                        ->setCellValue($labelColumn.($indexHeader + 1), $employee->name)
                        ;
                    $indexColumn++;
                }
            }

            /* *********************************************************************************************************
             * Draw check column
             * ********************************************************************************************************/
            $labelCheck = excelUtils::getLabelColumn($indexColumn);
            excelUtils::setColumnWidth($objPHPExcel, $labelCheck, 22);

            $objPHPExcel->getActiveSheet()
                ->setCellValue($labelCheck.$indexHeader, 'Tổng Kế hoạch');

            foreach($arrChildRow as $childRow){
                if(
                    $childRow['formula']    != commonUtils::FORMULA_LAY1SO
                    && $childRow['formula'] != commonUtils::FORMULA_TRUNG_BINH_CONG
                ){
                    $checkValue = 'H'.$childRow['indexRow'].':'.$lastColumn.$childRow['indexRow'];
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($labelCheck.$childRow['indexRow'], '=sum('.$checkValue.')');
                }elseif($childRow['formula'] == commonUtils::FORMULA_TRUNG_BINH_CONG){
                    $checkValue = 'H'.$childRow['indexRow'].':'.$lastColumn.$childRow['indexRow'];
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($labelCheck.$childRow['indexRow'], '=IFERROR(AVERAGE('.$checkValue.'), H'.$childRow['indexRow'].')');
                }else{
                    $checkValue = excelUtils::getLabelColumn((excelUtils::getIndexColumn('H') - 1)).$childRow['indexRow'];
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($labelCheck.$childRow['indexRow'], '='.$checkValue);
                }
            }
            /* *******************************************************************************************************/

            $indexNote = 8;
            $stColumn = $indexColumn + 1;
            $ndColumn = $indexColumn + 2;
            $rdColumn = $indexColumn + 3;

            $stNoteLabel = excelUtils::getLabelColumn($stColumn);
            $ndNoteLabel = excelUtils::getLabelColumn($ndColumn);
            $rdNoteLabel = excelUtils::getLabelColumn($rdColumn);

            $objPHPExcel->getActiveSheet()
                ->setCellValue($stNoteLabel.$indexNote, "Không xóa các cột chú thích")
                ->setCellValue($stNoteLabel.($indexNote + 2), "Loại")
                ->setCellValue($ndNoteLabel.($indexNote + 2), 0)
                ->setCellValue($ndNoteLabel.($indexNote + 3), 1)
                ->setCellValue($rdNoteLabel.($indexNote + 2), 'Mục tiêu cấp 1')
                ->setCellValue($rdNoteLabel.($indexNote + 3), 'Mục tiêu cấp 2')
            ;
            /*$noteEmployee = $indexNote + 5;
            foreach($arrEmployee as $nEmployee){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($stNoteLabel.$noteEmployee, $nEmployee['employeeCode'])
                    ->setCellValue($ndNoteLabel.$noteEmployee, $nEmployee['employeeName'])
                    ;
                $noteEmployee++;
            }*/

            /* *********************************************************************************************************
             * Format sheet
             * ********************************************************************************************************/
            excelUtils::mergeCells($objPHPExcel,'D2:F2');
            excelUtils::setBold($objPHPExcel, 'A1:'.$stNoteLabel.'11');
            excelUtils::setHorizontal($objPHPExcel, $stNoteLabel.($indexNote +2), \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel, $stNoteLabel.$indexNote, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel, 'B12:B'.$startRow, \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel, 'B10:'.$labelCheck.'11', \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::setHorizontal($objPHPExcel, 'D2', \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            excelUtils::setVertical($objPHPExcel, 'B1:'.$rdNoteLabel.$startRow, \PHPExcel_Style_Alignment::VERTICAL_CENTER);
            excelUtils::setBorderCell($objPHPExcel,'B10:'.$labelCheck.($startRow - 1), excelUtils::styleBorder());
            //excelUtils::setBorderCell($objPHPExcel,$stNoteLabel.($indexNote + 5).':'.$ndNoteLabel.($noteEmployee - 1), excelUtils::styleBorder());
            excelUtils::setBorderCell($objPHPExcel,$stNoteLabel.($indexNote + 2).':'.$rdNoteLabel.($indexNote + 3), excelUtils::styleBorder());
            excelUtils::setColumnWidth($objPHPExcel, $ndNoteLabel, 22);
            excelUtils::setColumnWidth($objPHPExcel, $rdNoteLabel, 15);
            excelUtils::mergeCells($objPHPExcel, 'B'.$indexHeader.':B'.($indexHeader + 1));
            excelUtils::mergeCells($objPHPExcel, 'C'.$indexHeader.':C'.($indexHeader + 1));
            excelUtils::mergeCells($objPHPExcel, 'D'.$indexHeader.':D'.($indexHeader + 1));
            excelUtils::mergeCells($objPHPExcel, 'E'.$indexHeader.':E'.($indexHeader + 1));
            excelUtils::mergeCells($objPHPExcel, 'F'.$indexHeader.':F'.($indexHeader + 1));
            excelUtils::mergeCells($objPHPExcel, $labelCheck.$indexHeader.':'.$labelCheck.($indexHeader + 1));
            excelUtils::mergeCells($objPHPExcel, $stNoteLabel.$indexNote.':'.$rdNoteLabel.$indexNote);
            excelUtils::mergeCells($objPHPExcel, $stNoteLabel.($indexNote +2).':'.$stNoteLabel.( $indexNote + 3));
            excelUtils::formatCell($objPHPExcel,'G12:'.$labelCheck.($startRow - 1), 1, excelUtils::STYLE_NUMBER);
            excelUtils::setFontColor($objPHPExcel, 'C6:C8', excelUtils::COLOR_RED);
            excelUtils::setFontColor($objPHPExcel, 'D2:D4', excelUtils::COLOR_RED);
            excelUtils::setFontColor($objPHPExcel, 'G4:G6', excelUtils::COLOR_RED);
            excelUtils::setFontColor($objPHPExcel, $stNoteLabel.$indexNote, excelUtils::COLOR_RED);
            excelUtils::fillBackGroundColor($objPHPExcel, 'B10:'.$labelCheck.'11', excelUtils::COLOR_DARK);
            foreach($arrPRow as $pRow){
                excelUtils::fillBackGroundColor($objPHPExcel,'B'.$pRow.':'.$labelCheck.$pRow, excelUtils::COLOR_GREEN);
            }
            /*------------------------------------------------------------------------------------------------------------*/
            #Set width for column
            $hashColumn = range('A', 'G');
            $count = 0;
            foreach ($hashColumn as $key => $value) {
                $dimension = 0;
                switch ($value) {
                    case 'A':
                        $dimension = 5;
                        break;
                    case 'B':
                    case 'C':
                    case 'E':
                    case 'F':
                        $dimension = 12;
                        break;
                    case 'G':
                        $dimension = 18;
                        break;
                    case 'D':
                        $dimension = 35;
                        break;
                }

                excelUtils::setColumnWidth($objPHPExcel, $value, $dimension);
                $count++;
            }
             /* *******************************************************************************************************/
            $indexSheet++;
        }

        //prepare download
        $fileName = "sampleImportGoalEmployee";
        $this->outputFile($fileName, $objPHPExcel);
    }

    private function checkTerminateDate($terminateDate, $year, $month){
        $terminateYear = (int)substr($terminateDate, 0, 4);
        $terminateMonth = (int)substr($terminateDate, 5, 2);

        if($terminateYear == 0 || $terminateMonth == 0){
            return 0;
        }

        if($terminateYear >= $year){
            if($terminateMonth >= $month){
                return 0;
            }else{
                return 1;
            }
        }else{
            return 1;
        }

    }

    /* *****************************************************************************************************************
     * Define function exportSamplePerformEmployee bellow
     * exportSamplePerformEmployee : Thực hiện Nhân viên
     * ****************************************************************************************************************/
    public function exportSamplePerformEmployee()
    {

        $objPHPExcel = PHPExcel_IOFactory::load("public/excelTemplate/blank.xlsx");

        $sDataUser = Session::get('sDataUser');

        $sAccessLevel   = $sDataUser->access_level;
        $sCompanyId     = $sDataUser->company_id;
        $sCompanyCode   = $sDataUser->company_code;
        $sCompanyName   = $sDataUser->company_name;
        $sAreaId        = $sDataUser->area_id;
        $sAreaCode      = $sDataUser->area_code;
        $sAreaName      = $sDataUser->area_name;
        $sPositionId    = $sDataUser->position_id;
        $sPositionCode  = $sDataUser->position_code;
        $sPositionName  = $sDataUser->position_name;
        $sGroupId       = $sDataUser->group_id;

        $tempMonth   = (int)date('m') ;

        $currentMonth = ($tempMonth == 1) ? 12 : $tempMonth - 1;
//        $currentMonth   = 1;
        $currentYear    = ($tempMonth == 1) ? (int)date('Y') - 1: (int)date('Y');
//        $currentYear    = 2016;


        $objEmployeeDB  = $this->getEmployees($sCompanyId, $sAreaId, 0);

        $objTEDB        = $this->getTargetEmployee($sCompanyId, $sAreaId, 0, $currentYear, $currentMonth);

        $arrPosition = array();
        $iP = 0;



        foreach($objTEDB as $te){
            if(count($arrPosition) == 0){
                $arrPosition[$iP]['indexSheet']   = $iP;
                $arrPosition[$iP]['positionId']   = $te->position_id;
                $arrPosition[$iP]['positionCode'] = $te->position_code;
                $arrPosition[$iP]['positionName'] = $te->position_name;
                $iP++;
            }else{
                $exist = 0;
                foreach($arrPosition as $position){
                    if($position['positionId'] == $te->position_id){
                        $exist = 1;
                        break;
                    }
                }

                if($exist == 0){
                    $arrPosition[$iP]['indexSheet']   = $iP;
                    $arrPosition[$iP]['positionId']   = $te->position_id;
                    $arrPosition[$iP]['positionCode'] = $te->position_code;
                    $arrPosition[$iP]['positionName'] = $te->position_name;
                    $iP++;
                }
            }
        }



        if(count($arrPosition) != 0){
            foreach($arrPosition as $position){

                $indexSheet     = $position['indexSheet'];
                $positionId     = $position['positionId'];
                $positionCode   = $position['positionCode'];
                $positionName   = $position['positionName'];

                $objILPDB       = $this->getImportantLevelPosition($sCompanyId, $sAreaId, $positionId, $currentYear, $currentMonth, $currentMonth);

                $sqlITE = "
                    SELECT te.*, p.position_code, g.goal_code, g.goal_name, g.parent_id, p.position_name, u.code, u.name, g.formula
                    FROM target_employee te
                    LEFT JOIN `position` p ON p.id = te.position_id
                    LEFT JOIN goal g ON g.id = te.goal_id
                    LEFT JOIN users u ON u.id = te.user_id
                    WHERE te.inactive = 0
                    AND te.company_id = ".$sCompanyId."
                    AND te.area_id = ".$sAreaId."
                    AND te.year = ".$currentYear."
                    AND te.month = ".$currentMonth."
                    AND te.position_id = ".$positionId."
                ";

                if(commonUtils::compareTwoString($positionCode, commonUtils::POSITION_CODE_TQ) == 1){
                    $sqlITE .= " AND g.formula = ".commonUtils::FORMULA_TU_NHAP;
                }

                $objITEDB = DB::select(DB::raw($sqlITE));

                $arrGoalParent = array();
                $iGP = 0;

                $arrParentCompare = array();
                foreach($objITEDB as $getParent){
                    if(
                        !in_array($getParent->parent_id, $arrParentCompare)
                    ){
                        $arrParentCompare[] = $getParent->parent_id;
                    }
                }

                foreach($objILPDB as $ilc){

                    if($ilc->parent_id == 0 && in_array($ilc->goal_id, $arrParentCompare)){
                        if(count($arrGoalParent) == 0){
                            $arrGoalParent[$iGP]['goalId']   = $ilc->goal_id;
                            $arrGoalParent[$iGP]['goalCode'] = $ilc->goal_code;
                            $arrGoalParent[$iGP]['goalName'] = $ilc->goal_name;
                            $iGP++;
                        }else{
                            $exist = 0;
                            foreach($arrGoalParent as $goalParent){
                                if($goalParent['goalId'] == $ilc->goal_id){
                                    $exist = 1;
                                    break;
                                }
                            }

                            if($exist == 0){
                                $arrGoalParent[$iGP]['goalId']   = $ilc->goal_id;
                                $arrGoalParent[$iGP]['goalCode'] = $ilc->goal_code;
                                $arrGoalParent[$iGP]['goalName'] = $ilc->goal_name;
                                $iGP++;
                            }
                        }
                    }

                }

                $arrDistinctGoal = array();
                $iDG = 0;

                $arrEmployee = array();
                $iE = 0;

                foreach($objITEDB as $ite){
                    if(count($arrDistinctGoal) == 0){
                        $arrDistinctGoal[$iDG]['goalId']   = $ite->goal_id;
                        $arrDistinctGoal[$iDG]['parentId'] = $ite->parent_id;
                        $arrDistinctGoal[$iDG]['goalCode'] = $ite->goal_code;
                        $arrDistinctGoal[$iDG]['goalName'] = $ite->goal_name;
                        $arrDistinctGoal[$iDG]['formula']  = $ite->formula;
                        $iDG++;
                    }else{
                        $exist = 0;
                        foreach($arrDistinctGoal as $dg){
                            if($dg['goalId'] == $ite->goal_id){
                                $exist = 1;
                                break;
                            }
                        }

                        if($exist == 0){
                            $arrDistinctGoal[$iDG]['goalId']   = $ite->goal_id;
                            $arrDistinctGoal[$iDG]['parentId'] = $ite->parent_id;
                            $arrDistinctGoal[$iDG]['goalCode'] = $ite->goal_code;
                            $arrDistinctGoal[$iDG]['goalName'] = $ite->goal_name;
                            $arrDistinctGoal[$iDG]['formula']  = $ite->formula;
                            $iDG++;
                        }
                    }

                    if(count($arrEmployee) == 0){
                        $arrEmployee[$iE]['employeeId']   = $ite->user_id;
                        $arrEmployee[$iE]['employeeCode'] = $ite->code;
                        $arrEmployee[$iE]['employeeName'] = $ite->name;
                        $iE++;
                    }else{
                        $exist = 0;
                        foreach($arrEmployee as $de){
                            if($de['employeeId'] == $ite->user_id){
                                $exist = 1;
                                break;
                            }
                        }

                        if($exist == 0){
                            $arrEmployee[$iE]['employeeId']   = $ite->user_id;
                            $arrEmployee[$iE]['employeeCode'] = $ite->code;
                            $arrEmployee[$iE]['employeeName'] = $ite->name;
                            $iE++;
                        }
                    }
                }

                if($indexSheet != 0){
                    $objPHPExcel->createSheet($indexSheet);
                }
                $objPHPExcel->setActiveSheetIndex($indexSheet);
                $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Calibri');
                $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
                $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(82);
                $objPHPExcel->getActiveSheet()->setTitle($this->convertNameSheet($positionCode));

                $objPHPExcel->getActiveSheet()->mergeCells('D2:F2');
                $objPHPExcel->getActiveSheet()->setCellValue('D2', commonUtils::TITLE_IMPORT_PERFORM_EMPLOYEE);

                $objPHPExcel->getActiveSheet()->setCellValue('C6', $sCompanyCode);
                $objPHPExcel->getActiveSheet()->setCellValue('C7', $sAreaCode);
                $objPHPExcel->getActiveSheet()->setCellValue('C8', $positionCode);

                $objPHPExcel->getActiveSheet()->setCellValue('D6', $sCompanyName);
                $objPHPExcel->getActiveSheet()->setCellValue('D7', $sAreaName);
                $objPHPExcel->getActiveSheet()->setCellValue('D8', $positionName);

                $objPHPExcel->getActiveSheet()->setCellValue('E4', 'Năm');
                $objPHPExcel->getActiveSheet()->setCellValue('E5', 'Tháng');

                $objPHPExcel->getActiveSheet()->setCellValue('F4', $currentYear);
                $objPHPExcel->getActiveSheet()->setCellValue('F5', $currentMonth);

                $objPHPExcel->getActiveSheet()
                    ->setCellValue('B10' , 'STT')
                    ->setCellValue('C10' , 'Mã')
                    ->setCellValue('D10' , 'Mục tiêu')
                    ->setCellValue('E10' , 'Loại')
                ;
                $no = 1;
                $parentRow = 12;

                $arrParentRow = array();

                $arrChildGoal = array();
                $iCG = 0;

                foreach($arrGoalParent as $goalParent){
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('B'.$parentRow , $no++)
                        ->setCellValue('C'.$parentRow , $goalParent['goalCode'])
                        ->setCellValue('D'.$parentRow , $goalParent['goalName'])
                        ->setCellValue('E'.$parentRow , 0)
                    ;
                    $arrParentRow[] =  $parentRow;

                    $childRow = $parentRow + 1;
                    foreach($arrDistinctGoal as $dig){
                        if($dig['parentId'] == $goalParent['goalId']){

                            $arrChildGoal[$iCG]['indexRow'] = $childRow;
                            $arrChildGoal[$iCG]['goalId']   = $dig['goalId'];
                            $arrChildGoal[$iCG]['formula']  = $dig['formula'];
                            $iCG++;

                            $objPHPExcel->getActiveSheet()
                                ->setCellValue('B'.$childRow , $no++)
                                ->setCellValue('C'.$childRow , $dig['goalCode'])
                                ->setCellValue('D'.$childRow , $dig['goalName'])
                                ->setCellValue('E'.$childRow , 1)
                            ;
                            $childRow++;
                        }
                    }

                    $parentRow = $childRow;

                }

                $lastRow = $childRow - 1;
                #get Index of Highest Column in current sheet

                $endLabelColumn = 'E';
                $indexHeader = 10;


                foreach($arrEmployee as $employee){
                    $endIndexColumn = \PHPExcel_Cell::columnIndexFromString($endLabelColumn);

                    $column   = $endIndexColumn;
                    $uLabelEmployee = \PHPExcel_Cell::stringFromColumnIndex($column++);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($uLabelEmployee)->setWidth(20);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue($uLabelEmployee.$indexHeader, $employee['employeeCode'])
                        ->setCellValue($uLabelEmployee.($indexHeader +1), $employee['employeeName'])
                    ;
                    /* *************************************************************************************************
                     * Set value to cell here
                     * ************************************************************************************************/
                    foreach($objITEDB as $iTE){
                        if($iTE->user_id == $employee['employeeId']){

                            $indexRow = "";
                            foreach($arrChildGoal as $childGoal){
                                if($childGoal['goalId'] == $iTE->goal_id){
                                    $indexRow = $childGoal['indexRow'];
                                    break;
                                }
                            }

                            if($indexRow != ""){

                                if(commonUtils::compareTwoString($iTE->position_code, commonUtils::POSITION_CODE_TQ) == 1){
                                    if($childGoal['formula'] == commonUtils::FORMULA_TU_NHAP){
                                        $objPHPExcel->getActiveSheet()
                                            ->setCellValue($uLabelEmployee.$indexRow, $iTE->target_value)
                                        ;
                                    }
                                }else{
                                    $objPHPExcel->getActiveSheet()
                                        ->setCellValue($uLabelEmployee.$indexRow, $iTE->target_value)
                                    ;
                                }

                            }
                        }
                    }
                    /* ***********************************************************************************************/


                    $endLabelColumn = $uLabelEmployee;

                }

                $line = \PHPExcel_Cell::columnIndexFromString($endLabelColumn) + 1;
                $uLabelLine = \PHPExcel_Cell::stringFromColumnIndex($line);
                $uLabelNext = \PHPExcel_Cell::stringFromColumnIndex($line + 1);
                $uLabelEnd  = \PHPExcel_Cell::stringFromColumnIndex($line + 2);

                $rowNote = 8;
                $objPHPExcel->getActiveSheet()->mergeCells($uLabelLine.$rowNote.':'.$uLabelEnd.$rowNote);
                $objPHPExcel->getActiveSheet()->setCellValue($uLabelLine.$rowNote, 'Không xóa các cột chú thích');
                $objPHPExcel->getActiveSheet()->mergeCells($uLabelLine.($rowNote + 2).':'.$uLabelLine.($rowNote + 3));
                $objPHPExcel->getActiveSheet()->setCellValue($uLabelLine.($rowNote + 2), 'Loại');

                $objPHPExcel->getActiveSheet()->setCellValue($uLabelNext.($rowNote + 2), '0');
                $objPHPExcel->getActiveSheet()->setCellValue($uLabelNext.($rowNote + 3), '1');

                $objPHPExcel->getActiveSheet()->setCellValue($uLabelEnd.($rowNote + 2), 'Mục tiêu cấp 1');
                $objPHPExcel->getActiveSheet()->setCellValue($uLabelEnd.($rowNote + 3), 'Mục tiêu cấp 2');

                $objPHPExcel->getActiveSheet()->mergeCells('D4:D5');
                $objPHPExcel->getActiveSheet()->setCellValue('D4', 'Không thay đổi tiêu đề file, vị trí dữ liệu Năm, Tháng, Mã phòng ban, Mã Khu vực, Mã chức danh.');

                /* *****************************************************************************************************
                 * Draw check column
                 * ****************************************************************************************************/
                $labelCheck = excelUtils::getLabelColumn(excelUtils::getIndexColumn($endLabelColumn));

                excelUtils::setColumnWidth($objPHPExcel, $labelCheck, 22);

                $objPHPExcel->getActiveSheet()
                    ->setCellValue($labelCheck.$indexHeader, 'Tổng Thực hiện');

                foreach($arrChildGoal as $childRow){
                    if(
                        $childRow['formula']    != commonUtils::FORMULA_LAY1SO
                        && $childRow['formula'] != commonUtils::FORMULA_TRUNG_BINH_CONG
                    ){
                        $checkValue = 'F'.$childRow['indexRow'].':'.$endLabelColumn.$childRow['indexRow'];
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($labelCheck.$childRow['indexRow'], '=sum('.$checkValue.')');
                    /*}else{
                        $checkValue = excelUtils::getLabelColumn((excelUtils::getIndexColumn('F') - 1)).$childRow['indexRow'];
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($labelCheck.$childRow['indexRow'], '='.$checkValue);
                    }*/
                    }elseif($childRow['formula'] == commonUtils::FORMULA_TRUNG_BINH_CONG){

                        $checkValue = 'F'.$childRow['indexRow'].':'.$endLabelColumn.$childRow['indexRow'];
                        $formulaCel = '=IFERROR(AVERAGE('.$checkValue.'), F'.$childRow['indexRow'].')';
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($labelCheck.$childRow['indexRow'], $formulaCel);
                    }else{
                        $checkValue = excelUtils::getLabelColumn((excelUtils::getIndexColumn('F') - 1)).$childRow['indexRow'];
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($labelCheck.$childRow['indexRow'], '='.$checkValue);
                    }
                }
                /* *******************************************************************************************************/


                /* *****************************************************************************************************
                 * Format sample
                 * ****************************************************************************************************/
                excelUtils::setBold($objPHPExcel, 'B1:'.$labelCheck.$indexHeader);
                $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->getColor()->setRGB(commonUtils::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->getColor()->setRGB(commonUtils::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle('C6:C8')->getFont()->getColor()->setRGB(commonUtils::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle('F4:F5')->getFont()->getColor()->setRGB(commonUtils::COLOR_RED);

                $objPHPExcel->getActiveSheet()->getStyle($uLabelLine.$rowNote)
                    ->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setRGB(commonUtils::COLOR_RED)
                ;

                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
                $objPHPExcel->getActiveSheet()->getColumnDimension($uLabelNext)->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension($uLabelEnd)->setWidth(20);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$indexHeader.':'.$endLabelColumn.$indexHeader)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B1:B'.$lastRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($uLabelLine.$rowNote)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($uLabelNext.($rowNote + 2).':'.$uLabelNext.($rowNote + 3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )

                    )
                );

                $objPHPExcel->getActiveSheet()->getStyle('B'.$indexHeader.':'.$labelCheck.$lastRow)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($uLabelLine.($rowNote + 2).':'.$uLabelEnd.($rowNote + 3))->applyFromArray($styleArray);

                foreach($arrParentRow as $pRow){
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('B' . $pRow. ':' .$labelCheck. $pRow)
                        ->getFill()
                        ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB(commonUtils::COLOR_GREEN);
                }

                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setVisible(false);
                excelUtils::mergeCells($objPHPExcel, 'B'.$indexHeader.':B'.($indexHeader + 1));
                excelUtils::mergeCells($objPHPExcel, $labelCheck.$indexHeader.':'.$labelCheck.($indexHeader + 1));
                excelUtils::mergeCells($objPHPExcel, 'C'.$indexHeader.':C'.($indexHeader + 1));
                excelUtils::mergeCells($objPHPExcel, 'D'.$indexHeader.':D'.($indexHeader + 1));
                excelUtils::mergeCells($objPHPExcel, 'E'.$indexHeader.':E'.($indexHeader + 1));
                excelUtils::setVertical($objPHPExcel,'B1:'.$uLabelEnd.$lastRow, \PHPExcel_Style_Alignment::VERTICAL_CENTER);
                excelUtils::setHorizontal($objPHPExcel, 'B'.$indexHeader.':'.$labelCheck.($indexHeader + 1), \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                excelUtils::fillBackGroundColor($objPHPExcel, 'B'.$indexHeader.':'.$labelCheck.($indexHeader + 1), excelUtils::COLOR_DARK);
                excelUtils::formatCell($objPHPExcel,'F12:'.$labelCheck.$lastRow, 1, excelUtils::STYLE_NUMBER);
            }
        }

        //prepare download
        $fileName = "sampleImportPerformForEmployee";
        $this->outputFile($fileName, $objPHPExcel);
    }
    /* *****************************************************************************************************************
     * Define all private function used for this Controller
     * ****************************************************************************************************************/

    /* *****************************************************************************************************************
     * Types
     * 0: LEFT JOIN
     * 1: RIGHT JOIN
     * 2: INNER JOIN
     * ****************************************************************************************************************/
    private function requestDatabase($arrTable, $arrColumn, $arrCondition, $type){

        $sql = " ";
        $mainTable = "";
        $object = array();
        switch($type) {
            case 1:

                foreach($arrColumn as $column){

                }

                foreach($arrTable as $table){
                    if($table['index'] == 0){
                        $sql .= " FROM  `".$table['tableName']."`";
                        $mainTable = $table['tableName'];
                    }else{
                        $sql .= " LEFT JOIN  `".$table['tableName']."` ON `".$table['tableName']."`.id = `".$mainTable."`.".$table['tableName']."_id ";
                    }
                }

                foreach($arrCondition as  $condition){
                    $sql .= " AND  `".$mainTable."`.`".$condition['column']."` ".$condition['compare'].$condition['value']." ";
                }



                break;
            case 2:

                break;
            case 3:

                break;

        }






    }
    private function getTargetEmployee($companyId, $areaId, $positionId, $year, $month){
        $sql = "
            SELECT te.*, p.position_code, g.goal_code, g.goal_name, g.parent_id, p.position_name, u.code, u.name, g.formula
            FROM target_employee te
            LEFT JOIN `position` p ON p.id = te.position_id
            LEFT JOIN goal g ON g.id = te.goal_id
            LEFT JOIN users u ON u.id = te.user_id
            WHERE te.inactive = 0
            AND te.company_id = ".$companyId."
            AND te.area_id = ".$areaId."
            AND te.year = ".$year."
         ";

        if($positionId != 0){
            $sql .= " AND te.position_id = ".$positionId;
        }

        if($month != 0){
            $sql .= " AND te.month = ".$month;
        }
        //echo $sql;
        $objDB = DB::select(DB::raw($sql));
        return $objDB;
    }
    private function getTargetPosition($companyId, $areaId, $positionId, $year, $fromMonth, $toMonth){
        $sql = "
            SELECT tp.*, p.position_code, g.goal_code, g.goal_name, g.parent_id, p.position_name
            FROM target_position tp
            LEFT JOIN `position` p ON p.id = tp.position_id
            LEFT JOIN goal g ON g.id = tp.goal_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND tp.area_id = ".$areaId."
            AND tp.year = ".$year."
         ";

        if($positionId != 0){
            $sql .= " AND tp.position_id = ".$positionId;
        }

        if($fromMonth != 0){
            $sql .= " AND tp.month >= ".$fromMonth;
        }

        if($toMonth != 0){
            $sql .= " AND tp.month <= ".$toMonth;
        }

        $objDB = DB::select(DB::raw($sql));
        return $objDB;
    }

    private function getImportantLevelPosition($companyId, $areaId, $positionId, $year, $fromMonth, $toMonth){
        $sql = "
            SELECT ilp.*, p.position_code, g.goal_code, g.goal_name, g.parent_id, a.area_code
            FROM important_level_position ilp
            LEFT JOIN `position` p ON p.id = ilp.position_id
            LEFT JOIN `area` a ON a.id = ilp.area_id
            LEFT JOIN goal g ON g.id = ilp.goal_id
            WHERE ilp.inactive = 0
            AND ilp.company_id = ".$companyId."
            AND ilp.year = ".$year."
         ";

        if($areaId != 0){
            $sql .= " AND ilp.area_id = ".$areaId;
        }

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

    private function getApplyDate4Company($companyId, $year, $applyDate){
        $sql = "
            SELECT apply_date
            FROM important_level_company
            WHERE inactive = 0
            AND `lock` = 0
            AND `company_id` = ".$companyId."
            AND year(apply_date) = ".$year."
        ";

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

        $sql = "
            SELECT ilc.*, g.goal_code, g.parent_id, c.company_code
            FROM important_level_company ilc
            LEFT JOIN goal g ON g.id = ilc.goal_id
            LEFT JOIN company c ON c.id = ilc.company_id
            WHERE ilc.inactive = 0
            AND ilc.`lock` = 0
        ";
        if($companyId != 0){
            $sql .= " AND ilc.company_id = ".$companyId;
        }
        if($applyDate != ""){
            $sql .= " AND ilc.apply_date = '".$applyDate."'";
        }

        $objDB = DB::select(DB::raw($sql));
        return  $objDB;
    }

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

    private function getEmployees($companyId, $areaId, $positionId){

        $sql = "
            SELECT u.*, c.company_code, c.company_name, a.area_code, a.area_name, p.position_code, p.position_name
            FROM users u
            LEFT JOIN company c ON c.id = u.company_id
            LEFT JOIN area a ON a.id = u.area_id
            LEFT JOIN `position` p ON p.id = u.position_id
            WHERE u.inactive = 0
            AND u.admin = 0
            AND u.company_id = ".$companyId."
            AND u.area_id = ".$areaId."
            AND u.company_id != 0
            AND u.area_id != 0
            AND u.position_id != 0
        ";
        if($positionId != 0){
            $sql .= " AND u.position_id = ".$positionId;
        }
        $sql .= " ORDER BY u.position_id ";
        $objDB = DB::select(DB::raw($sql));
        return  $objDB;
    }

    /* *****************************************************************************************************************
     * Format excel template
     * ****************************************************************************************************************/

    private function paintNodeTypeUnit($firstColumn, $secondColumn, $thirdColumn, $objPHPExcel, $styleArray, $startRow){
        $unit = DB::table('unit')->where('inactive', 0)->get();
        $objPHPExcel->getActiveSheet()->setCellValue($firstColumn.$startRow,'Ghi chú');
        $startRow += 2;
        $startF = $startRow;
        $objPHPExcel->getActiveSheet()->setCellValue($firstColumn.$startRow,'Loại')
                                    ->setCellValue($secondColumn.$startRow,'0')
                                    ->setCellValue($thirdColumn.$startRow++,'Mục tiêu cấp 1')
                                    ->setCellValue($secondColumn.$startRow,'1')
                                    ->setCellValue($thirdColumn.$startRow++,'Mục tiêu cấp 2');
        $objPHPExcel->getActiveSheet()->getStyle($firstColumn.$startF.':'.$thirdColumn.($startRow - 1))->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->mergeCells($firstColumn.$startF.':'.$firstColumn.($startRow - 1));
        $objPHPExcel->getActiveSheet()->getStyle($firstColumn.$startF.':'.$firstColumn.($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($firstColumn.$startF.':'.$firstColumn.($startRow - 1))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $startRow += 2;
        $startS = $startRow;
        $objPHPExcel->getActiveSheet()->setCellValue($firstColumn.$startRow,'ĐVT');
        foreach($unit as $u){
            $objPHPExcel->getActiveSheet()->setCellValue($secondColumn.$startRow, $u->unit_code)
                ->setCellValue($thirdColumn.$startRow, $u->unit_name);
            $startRow++;
        }

        $objPHPExcel->getActiveSheet()->getStyle($firstColumn.$startS.':'.$thirdColumn.($startRow - 1))->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle($firstColumn.($startF - 2).':'.$firstColumn.($startRow - 1))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->mergeCells($firstColumn.$startS.':'.$firstColumn.($startRow - 1));

        $objPHPExcel->getActiveSheet()->getStyle($firstColumn.$startS.':'.$firstColumn.($startRow - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($firstColumn.$startS.':'.$firstColumn.($startRow - 1))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        return $startRow;
    }

    private function setWidth4Column($objPHPExcel){



    }
}

?>