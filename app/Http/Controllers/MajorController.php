<?php namespace App\Http\Controllers;

date_default_timezone_set('Asia/Ho_Chi_Minh');

use DB;
use Utils\commonUtils;
use Illuminate\Http\Request;
use Session;
use Illuminate\Pagination\LengthAwarePaginator;

class MajorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function clearSession()
    {
        Session::forget('areaChoose');
        Session::forget('areaDetailChoose');
    }

    /* *****************************************************************************************************************
     * Priority Corporation
     * ****************************************************************************************************************/
    public function managePriorityCorporation($goalId, $applyDate)
    {


        $this->clearSession();
        $isParent = -1;
        $data = array();
        $select = 'SELECT
                            ilc.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , unit.unit_code
                        FROM  important_level_corporation AS ilc
                        LEFT JOIN goal ON goal.id = ilc.goal_id
                        LEFT JOIN unit ON unit.id = goal.unit_id
                        WHERE ilc.inactive = 0
                    ';


        if($goalId != 0){
            $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id',0)->count();
            if($isParent == 1){
                $select .= ' AND ilc.goal_id in (SELECT id FROM  goal WHERE id = '.$goalId.' or parent_id = '.$goalId.' ) ';
            }else{
                $isParent = 0;
                $select .= ' AND ilc.goal_id = ' . $goalId;
            }
        }

        if ($applyDate != 0) {
            $select .= " AND ilc.apply_date = '" . $applyDate . "'";
            $data = DB::select(DB::raw($select));
        }

        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $date = DB::table('important_level_corporation')->select('apply_date')->distinct()->get();
        //commonUtils::pr($data); die;
        return view('major.managePriorityCorporation')
            ->with('gTwos', $gTwos)
            ->with('gOnes', $gOnes)
            ->with('data', $data)
            ->with('date', $date)
            ->with('isParent', $isParent)
            ->with('selectedGoal', $goalId)
            ->with('selectedApplyDate', $applyDate);
    }

    public function updatePriorityCorporation($id){

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Mã Tỷ trọng không hợp lệ!');
            return redirect('managePriorityCorporation/0/0');
        }

        $sqlILCor = "
            SELECT ilcor.*, cor.corporation_name, g.goal_name, g.formula, g.goal_type, g.parent_id
            FROM important_level_corporation ilcor
            LEFT JOIN goal g ON g.id = ilcor.goal_id
            LEFT JOIN corporation cor ON cor.id = ilcor.corporation_id
            WHERE ilcor.inactive = 0
            AND ilcor.id = ".$id."
        ";
        $objILCorDB  = DB::select(DB::raw($sqlILCor));

        if(count($objILCorDB) == 0){
            Session::flash('message-errors', 'Mã Tỷ trọng không tồn tại!');
            return redirect('managePriorityCompany/0/0/0');
        }

        $isLocked = $this->checkLockData('', '', $objILCorDB[0]->apply_date, 0, '');

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('managePriorityCorporation/0/0');
        }

        return view('major.updatePriorityCorporation')
            ->with('priorityCorporation', $objILCorDB[0])
            ;

    }

    public function editPriorityCorporation(Request $request){



            $actionUser     = Session::get('sid');
            $post           = $request->all();
            $importantLevel = (int)trim($post['txtImportantLevel']);
            $targetValue    = trim($post['txtTargetValue']);
            $defaultData    = $post['defaultData'];
            $arrDefaultData = explode(',', $defaultData);

            $id             = $arrDefaultData[0];
            $corId          = $arrDefaultData[1];
            $applyDate      = $arrDefaultData[2];
            $goalType       = $arrDefaultData[3];
            $goalId         = $arrDefaultData[4];
            $oldIL          = $arrDefaultData[5];
            $benchmark      = $arrDefaultData[6];
            $formula        = $arrDefaultData[7];
            $corName        = $arrDefaultData[8];
            $parentId       = $arrDefaultData[9];
            $oldTargetValue = $arrDefaultData[10];

            if(!is_numeric($importantLevel) || !is_numeric($targetValue) || $importantLevel <= 0 || !is_int($importantLevel)){
                Session::flash('message-errors', 'Dữ liệu không hợp lệ!');
                return redirect(
                    'managePriorityCorporation'
                    .'/'.$goalId
                    .'/'.$applyDate
                );
            }


            if($oldIL == $importantLevel){
                if($oldTargetValue != $targetValue
                    && $targetValue > 0
                ){
                    DB::beginTransaction();
                    try {
                        $sqlUpdate = "
                            UPDATE `important_level_corporation` SET `target_value`=".$targetValue." WHERE id = ".$id."
                        ";

                        DB::update(DB::raw($sqlUpdate));
                        DB::commit();
                    }catch (Exception $e) {
                        DB::rollback();
                    }

                }

                return redirect(
                    'managePriorityCorporation'
                    .'/'.$goalId
                    .'/'.$applyDate
                );
            }

                DB::beginTransaction();
                try {

            $dataUpdateIL = array(
                'important_level' 	=> $importantLevel,
                'target_value' 	    => $targetValue,
                'updated_user'      => $actionUser,
                'updated_date'      => date("Y-m-d h:i:sa")
            );

            DB::table('important_level_corporation')
                ->where('id', $id)
                ->update($dataUpdateIL);
            /* *********************************************************************************************************
             * Update benchmark cho toàn bộ mục tiêu với Cty tại của table tỷ trọng
             * ********************************************************************************************************/

            $year = date('Y', strtotime($applyDate));

            $this->calBenchmark4Corporation($applyDate, $parentId);

            $sqlIL = "
                SELECT ilc.*, g.goal_name, g.formula, g.goal_type, g.parent_id, c.corporation_name
                FROM important_level_corporation ilc
                LEFT JOIN goal g ON g.id = ilc.goal_id
                LEFT JOIN corporation c ON c.id = ilc.corporation_id
                WHERE ilc.inactive = 0
                AND ilc.apply_date = '".$applyDate."'
            ";

            if($parentId != 0){
                $sqlIL .= " AND ( ilc.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ilc.goal_id = ".$parentId." )";
            }

            $objILDB  = DB::select(DB::raw($sqlIL));

            $comApplyDate = $this->getApplyDate4Company("", $year, "");

            if($comApplyDate != ""){

                $arrGoalId = array();
                foreach($objILDB as $ilcor){
                    $this->calKPIBenchmark4Company($ilcor->goal_id, $comApplyDate, $applyDate);

                    if(!in_array($ilcor->goal_id, $arrGoalId)){
                        $arrGoalId[] = $ilcor->goal_id;
                    }
                }

                $sqlPerformCom = "
                    SELECT ilc.*, g.goal_name, g.formula, g.goal_type, g.parent_id
                    FROM important_level_company ilc
                    LEFT JOIN goal g ON g.id = ilc.goal_id
                    WHERE ilc.inactive = 0
                    AND ilc.apply_date = '".$comApplyDate."'
                ";

                if($parentId != 0){
                    $sqlPerformCom .= " AND ( ilc.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ilc.goal_id = ".$parentId." )";
                }

                $objPerformComDB  = DB::select(DB::raw($sqlPerformCom));

                $sqlCompany = "
                    SELECT *
                    FROM company
                    WHERE inactive = 0
                ";

                $objComDB  = DB::select(DB::raw($sqlCompany));

                foreach($objComDB as $company){
                    foreach($objPerformComDB as $performCom){
                        if($performCom->company_id == $company->id) {

                            $calIP = $performCom->real_percent * $performCom->cal_benchmark;

                            $dataUpdateCIP = array(
                                'cal_implement_point' 	=> $calIP
                            );

                            DB::table('important_level_company')
                                ->where('id', $performCom->id)
                                ->update($dataUpdateCIP);

                        }
                    }
                }

                $sqlPerformComRS = "
                    SELECT ilc.*, g.goal_name, g.formula, g.goal_type, g.parent_id
                    FROM important_level_company ilc
                    LEFT JOIN goal g ON g.id = ilc.goal_id
                    WHERE ilc.inactive = 0
                    AND ilc.apply_date = '".$comApplyDate."'
                ";

                if($parentId != 0){
                    $sqlPerformComRS .= " AND ( ilc.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ilc.goal_id = ".$parentId." )";
                }

                $objPerformComRSDB  = DB::select(DB::raw($sqlPerformComRS));


                foreach($objILDB as $ilcor){

                    $implementPoint = 0;
                    foreach($objPerformComRSDB as $rsPerform){
                        if($rsPerform->goal_id == $ilcor->goal_id){
                            $implementPoint += $rsPerform->cal_implement_point;
                        }
                    }

                    $dataUpdateIP = array(
                        'implement_point' 	=> $implementPoint
                    );

                    DB::table('important_level_corporation')
                        ->where('id', $ilcor->id)
                        ->update($dataUpdateIP);
                }

            }



            /* *********************************************************************************************************
             * Write log
             * ********************************************************************************************************/
            $dataLog = array(
                'functionName' => 'Cập nhật Tỷ trọng cho Chức danh (updatePriorityCompany)',
                'action'       => 'Cập nhật Tỷ trọng cho Phòng/Đài/MBF HCM',
                'url'          => 'updatePriorityPosition',
                'newValue'     => 'Kế hoạch: '.$targetValue.', Tỷ trọng: '.$importantLevel,
                'oldValue'     => '',
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);

            Session::flash('message-success', 'Cập nhật thành công!');

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /** managePriorityPosition/{cId}/{aId}/{pId}/{gId}/{y}/{m}*/
        return redirect(
            'managePriorityCorporation'
            .'/'.$goalId
            .'/'.$applyDate
        );

    }

    public function deletePriorityCorporation(Request $request){

        $sDataUser = Session::get('sDataUser');
        $post = $request->all();
        $id   = $post['id'];

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Tỷ trọng không hợp lệ!');
            return redirect('managePriorityCompany/0/0/0');
        }

        $sqlILCor = "
            SELECT ilcor.*, cor.corporation_name, g.parent_id, g.goal_code
            FROM important_level_corporation ilcor
            LEFT JOIN goal g ON g.id = ilcor.goal_id
            LEFT JOIN corporation cor ON cor.id = ilcor.corporation_id
            WHERE ilcor.id = ".$id."
        ";

        $objILCorDB  = DB::select(DB::raw($sqlILCor));

        if(count($objILCorDB) == 0){
            Session::flash('message-errors', 'Tỷ trọng không tồn tại!');
            return redirect('managePriorityCorporation/0/0');
        }

        $arrDefaultMonth = commonUtils::defaultMonth();

        $ilcor       = $objILCorDB[0];
        $corId       = $ilcor->corporation_id;
        $corName     = $ilcor->corporation_name;
        $goalId      = $ilcor->goal_id;
        $parentId    = $ilcor->parent_id;
        $applyDate   = $ilcor->apply_date;
        $year        = date('Y', strtotime($applyDate));
        $fDate       = commonUtils::formatDate($applyDate);

        $oldValue = '['.$sDataUser->code.'] '.$sDataUser->name.' đã xóa tỷ trọng mục tiêu <b>'.$ilcor->goal_code.'</b> có ngày áp dụng <b>'.$fDate.'</b> thuộc <b> Công ty Mobifone </b> vào lúc <b>'.date("h:i:sa").'</b> ngày <b>'.date("d/m/Y").'</b>';
        $oldValue .= "<br/>* Các giá trị đã bị xóa: [Kế hoạch: ".$ilcor->target_value."], [Tỷ trọng: ".$ilcor->important_level."], [Điểm chuẩn: ".$ilcor->benchmark."],  [Điểm thực hiện: ".$ilcor->implement_point."].";

        /* *************************************************************************************************************
         * Delete constrain
         * Update constrain
         * Cal KPI for  all
         * ************************************************************************************************************/

        DB::beginTransaction();
        try {

            $isLockedCor = $this->checkLockData( '', '', $applyDate, 0, '' );
            if($isLockedCor == 1){
                Session::flash('message-errors', 'Dữ liệu Cty Mobifone áp dụng từ ngày <b>'.$fDate.'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                return redirect('managePriorityCorporation/'.$goalId.'/'.$applyDate);
            }
            /**********************************************************************************************************/

            $comApplyDate = $this->getApplyDate4Company('', $year, '');

            $isLockedCom = $this->checkLockData( '', '', $applyDate, 1, '' );

            if($isLockedCom == 1){
                Session::flash('message-errors', 'Dữ liệu <b>Phòng/Đài/MBF HCM</b> áp dụng từ ngày <b>'.commonUtils::formatDate($comApplyDate).'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                return redirect('managePriorityCorporation/'.$goalId.'/'.$applyDate);
            }

            $sqlCompanies = "
                SELECT *
                FROM company
                WHERE inactive = 0
            ";

            $objCompaniesDB  = DB::select(DB::raw($sqlCompanies));

            foreach($objCompaniesDB as $company){

                $companyId = $company->id;

                foreach($arrDefaultMonth as $ml){

                    $isLockedI = $this->checkLockData( $year, $ml, '', 2, $companyId );
                    $isLockedT = $this->checkLockData( $year, $ml, '', 3, $companyId );
                    $isLockedP = $this->checkLockData( $year, $ml, '', 4, $companyId );

                    if(
                        $isLockedI    == 1
                        || $isLockedT == 1
                        || $isLockedP == 1
                    ){
                        Session::flash('message-errors', 'Tồn tại tháng dữ liệu đang khóa áp dụng năm <b>'.$year.'</b>. Vui lòng kiểm tra lại!');
                        return redirect('managePriorityCorporation/'.$goalId.'/'.$applyDate);
                    }

                }

            }



            if($parentId != 0){
                $sqlCheckExistChild = "
                    SELECT *
                    FROM important_level_corporation
                    WHERE inactive = 0
                    AND apply_date = '".$applyDate."'
                    AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId.")
                ";

                $objExistChildDB  = DB::select(DB::raw($sqlCheckExistChild));

                if(count($objExistChildDB) == 1){
                    $goalId   = $parentId;
                    $parentId = 0;


                }
            }

            $this->deleteILCor($id, $year, $applyDate, $goalId, $parentId);

            $this->deleteILC('', '', $year, $comApplyDate, $goalId, $parentId);

            $this->deleteILA('', '', '', $year, $goalId, $parentId);

            $this->deleteILP('', '', '', '', '', $year, '', $goalId, $parentId);

            $this->deleteTA('', '', $year, $goalId, $parentId);

            $this->deleteTP('', '','', '', $year, '', $goalId, $parentId);

            $this->deleteTE('', '','', '', $year, '', $goalId, $parentId);
            /**********************************************************************************************************/

            $this->calBenchmark4Corporation($applyDate, $parentId);

            $this->calKPIBenchmark4Company('', $comApplyDate, $applyDate);

            foreach($objCompaniesDB as $company){

                $companyId = $company->id;

                $this->calBenchmark4EachCompany($companyId, $applyDate, $parentId);

                $sqlAreas = "
                    SELECT a.id, a.company_id, c.company_name, a.area_code
                    FROM area a
                    LEFT JOIN company c ON c.id = a.company_id
                    WHERE a.inactive = 0
                    AND company_id = ".$companyId."
                ";

                $objAreasDB  = DB::select(DB::raw($sqlAreas));

                foreach($objAreasDB as $area){
                    $this->calBenchmark4EachArea($companyId, $area->id, $year, '', $parentId);

                }

                $this->calKPIBenchmark4Area($companyId, '', $year, '', $comApplyDate);

                $sqlILP = "
                    SELECT  position_id, `month`, area_id
                    FROM important_level_position
                    WHERE inactive = 0
                    AND company_id = ".$companyId."
                    AND `year` = ".$year."
                    GROUP BY `month`, position_id, area_id
                ";
                $objILPDB  = DB::select(DB::raw($sqlILP));

                foreach($objAreasDB as $area){

                    $arrILPEM = array();

                    foreach($arrDefaultMonth as $m){

                        foreach($objILPDB as $ilp){
                            if(
                                $ilp->month == $m
                                && $ilp->area_id == $area->id
                            ){
                                if(!in_array($ilp->position_id, $arrILPEM)){
                                    $arrILPEM[] = $ilp->position_id;
                                }
                            }
                        }

                        foreach($arrILPEM as $iI){
                            $this->calBenchmark4EachPosition($companyId, $area->id, $iI, $year, $m, $parentId);
                        }

                    }


                    $this->calKPIBenchmark4Position($companyId, $area->id, '', $year, '', 1);
                    $this->updateBenchmark4TargetA($companyId, $area->id, $year, 1, $sDataUser->id);
                    $this->updateBenchmark4TargetPE($companyId, $area->id, '', $year, '', $sDataUser->id, 1);

                }

                $this->updateCBenchmark4TargetA($companyId, '', $year, 1, 1);

                $sqlEmployee = "
                    SELECT us.*
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    WHERE us.inactive = 0
                    AND us.admin = 0
                    AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                    AND us.id > 5
                    AND us.company_id = ".$companyId."
                ";
                $objUsersDB  = DB::select(DB::raw($sqlEmployee));

                foreach($objAreasDB as $area){

                    $idTQ = -1;
                    foreach($objUsersDB as $user){
                        if($user->area_id == $area->id){
                            $idTQ = $user->id;
                        }
                    }

                    if($idTQ == -1){
                        Session::flash('message-errors', 'Lỗi: <b>'.$area->area_code.'</b> không tồn tại nhân viên có chức danh <b>'.commonUtils::POSITION_CODE_TQ.'</b>. Vui lòng kiểm tra lại và Kiểm tra cho các Tổ/Quận/Huyện khác.');
                        return redirect('managePriorityCorporation/'.$goalId.'/'.$applyDate);
                    }

                    $this->calKPI4Employee($companyId, $area->id, '', $year, '', 1 );
                    $this->calKPI4Position($companyId, $area->id, '', $idTQ, $year, '', '', 0, $sDataUser->id, 1);
                    $this->calKPI4Area($companyId, $area->id, $year, '', '', $sDataUser->id, 1);

                }

                $this->calKPI4Company($companyId, $year, $comApplyDate, '', $sDataUser->id, 1);
                $this->calKPI4Corporation($applyDate, $comApplyDate, '', $sDataUser->id, 1);

                $dataLog = array(
                    'functionName' => 'Xóa Tỷ trọng Công Ty (deletePriorityCorporation)',
                    'action'       => commonUtils::ACTION_DELETE,
                    'url'          => 'deletePriorityCorporation',
                    'newValue'     => '',
                    'oldValue'     => $oldValue,
                    'createdUser'  => $sDataUser->id
                );
                $this->writeLog($dataLog);

            }


            /**********************************************************************************************************/
            Session::flash('message-success', '1 dòng đã được xóa!');

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        return redirect('managePriorityCorporation/0/'.$applyDate);


    }

    /* *****************************************************************************************************************
     * Priority Company
     * ****************************************************************************************************************/
    public function managePriorityCompany($companyId, $goalId, $applyDate)
    {
        $this->clearSession();
        $isParent = -1;
        $data = array();
        $select = 'SELECT
                        ilc.*
                        , c.company_name
                        , g.goal_code
                        , g.parent_id
                        , g.goal_name
                        , u.unit_code
                    FROM  important_level_company ilc
                    LEFT JOIN goal g ON g.id = ilc.goal_id
                    LEFT JOIN unit u ON u.id = g.unit_id
                    LEFT JOIN company c ON c.id = ilc.company_id
                    WHERE ilc.inactive = 0
                    ';

        if ($companyId != 0) {
            $select .= ' AND ilc.company_id = ' . $companyId;

            if($goalId != 0){
                $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id',0)->count();
                if($isParent == 1){
                    $select .= ' AND ilc.goal_id in (SELECT id FROM  goal WHERE id = '.$goalId.' or parent_id = '.$goalId.' ) ';
                }else{
                    $isParent = 0;
                    $select .= ' AND ilc.goal_id = ' . $goalId;
                }
            }

            if ($applyDate != 0) {
                $select .= " AND ilc.apply_date = '" . $applyDate . "'";
            }
            $data = DB::select(DB::raw($select));
        }

        $companies = DB::table('company')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $date = DB::table('important_level_company')->select('apply_date')->distinct()->get();
        //commonUtils::pr($data); die;
        return view('major.managePriorityCompany')
            ->with('companies', $companies)
            ->with('gTwos', $gTwos)
            ->with('gOnes', $gOnes)
            ->with('data', $data)
            ->with('date', $date)
            ->with('isParent', $isParent)
            ->with('selectedCompany', $companyId)
            ->with('selectedGoal', $goalId)
            ->with('selectedApplyDate', $applyDate);
    }

    public function updatePriorityCompany($id){

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Mã Tỷ trọng không hợp lệ!');
            return redirect('managePriorityCompany/0/0/0');
        }

        $sqlILC = "
            SELECT ilc.*, c.company_name, g.goal_name, g.formula, g.goal_type, g.parent_id
            FROM important_level_company ilc
            LEFT JOIN goal g ON g.id = ilc.goal_id
            LEFT JOIN company c ON c.id = ilc.company_id
            WHERE ilc.inactive = 0
            AND ilc.id = ".$id."
        ";
        $objILCDB  = DB::select(DB::raw($sqlILC));

        if(count($objILCDB) == 0){
            Session::flash('message-errors', 'Mã Tỷ trọng không tồn tại!');
            return redirect('managePriorityCompany/0/0/0');
        }

        $isLocked = $this->checkLockData('', '', $objILCDB[0]->apply_date, 1, '');

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('managePriorityCompany/0/0/0');
        }

        return view('major.updatePriorityCompany')
            ->with('priorityCompany', $objILCDB[0])
            ;
    }

    public function editPriorityCompany(Request $request){



            $actionUser     = Session::get('sid');
            $post           = $request->all();
            $importantLevel = (int)trim($post['txtImportantLevel']);
            $targetValue    = trim($post['txtTargetValue']);
            $defaultData    = $post['defaultData'];
            $arrDefaultData = explode(',', $defaultData);

            $id             = $arrDefaultData[0];
            $companyId      = $arrDefaultData[1];
            $applyDate      = $arrDefaultData[2];
            $goalType       = $arrDefaultData[3];
            $goalId         = $arrDefaultData[4];
            $oldIL          = $arrDefaultData[5];
            $benchmark      = $arrDefaultData[6];
            $formula        = $arrDefaultData[7];
            $calBenchmark   = $arrDefaultData[8];
            $companyName    = $arrDefaultData[9];
            $parentId       = $arrDefaultData[10];
            $oldTargetValue = $arrDefaultData[11];

            if($oldIL == $importantLevel){

                if(
                    $oldTargetValue != $targetValue
                    && $targetValue > 0
                ){
                    DB::beginTransaction();
                    try {
                        $sqlUpdate = "
                            UPDATE `important_level_company` SET `target_value`=".$targetValue." WHERE id = ".$id."
                        ";

                        DB::update(DB::raw($sqlUpdate));
                        DB::commit();
                    }catch (Exception $e) {
                        DB::rollback();
                    }

                }

                return redirect(
                    'managePriorityCompany'
                    .'/'.$companyId
                    .'/'.$goalId
                    .'/'.$applyDate
                );
            }

        DB::beginTransaction();
        try {

            if(!is_numeric($importantLevel) || !is_numeric($targetValue)||  $importantLevel <= 0 || !is_int($importantLevel)){
                Session::flash('message-errors', 'Dữ liệu không hợp lệ!');
                return redirect(
                    'managePriorityCompany'
                    .'/'.$companyId
                    .'/'.$goalId
                    .'/'.$applyDate
                );
            }

            $dataUpdateIL = array(
                'important_level' 	=> $importantLevel,
                'target_value' 	    => $targetValue,
                'updated_user'      => $actionUser,
                'updated_date'      => date("Y-m-d h:i:sa")
            );

            DB::table('important_level_company')
                ->where('id', $id)
                ->update($dataUpdateIL);
            /* *********************************************************************************************************
             * Update benchmark cho toàn bộ mục tiêu với Phòng/Đài tại của table tỷ trọng
             * ********************************************************************************************************/

            $year = date('Y', strtotime($applyDate));

            $this->calBenchmark4EachCompany($companyId, $applyDate, $parentId);
            $corApplyDate = $this->getApplyDate4Corporation($year);
            $this->calKPIBenchmark4Company($goalId, $applyDate, $corApplyDate);

            $sqlILC = "
                SELECT ilc.*
                FROM important_level_company ilc
                WHERE ilc.inactive = 0
                AND ilc.company_id = ".$companyId."
                AND year(ilc.apply_date) = ".$year."
                AND ilc.apply_date = '".$applyDate."'
            ";

            $objILCDB  = DB::select(DB::raw($sqlILC));

            $arrMonth = commonUtils::defaultMonth();

            foreach($arrMonth as $m){
                foreach($objILCDB as $ilc){
                    $this->calKPIBenchmark4Area($companyId, $ilc->goal_id, $year, $m, $applyDate);
                    $this->updateCBenchmark4TargetA($companyId, $ilc->goal_id, $year, $m, 0);
                }
            }

            $sqlArea = "
                SELECT area_id
                FROM target_area
                WHERE company_id = ".$companyId."
                AND `year` = ".$year."
                GROUP BY area_id
            ";
            $objAreaDB  = DB::select(DB::raw($sqlArea));

            foreach($objAreaDB as $area){
                foreach($arrMonth as $m){
                    foreach($objILCDB as $ilc){
                        $this->calKPI4Area($companyId, $area->area_id, $year, $m, $ilc->goal_id, $actionUser, 0);
                    }
                }
            }

            foreach($objILCDB as $ilc){
                $this->calKPI4Company($companyId, $year, $applyDate, $ilc->goal_id, $actionUser, 0);
            }

            $sqlDiffILC = "
                SELECT *
                FROM important_level_company
                WHERE inactive = 0
                AND company_id != ".$companyId."
                AND apply_date = '".$applyDate."'
                AND goal_id = ".$goalId."
            ";

            $objDiffILCDB  = DB::select(DB::raw($sqlDiffILC));

            foreach($objDiffILCDB as $diffILC){
                $this->calKPI4Company($diffILC->company_id, $year, $applyDate, $diffILC->goal_id, $actionUser, 0);
            }

            $sqlILCor = "
                SELECT *
                FROM important_level_corporation
                WHERE inactive = 0
                AND apply_date = '".$applyDate."'
            ";

            $objILCorDB  = DB::select(DB::raw($sqlILCor));

            foreach($objILCorDB as $ilcor){
                $this->calKPI4Corporation($corApplyDate, $applyDate, $ilcor->goal_id, $actionUser, 0);
            }

            /* *********************************************************************************************************
             * Write log
             * ********************************************************************************************************/
            $dataLog = array(
                'functionName' => 'Cập nhật Tỷ trọng cho Chức danh (updatePriorityCompany)',
                'action'       => 'Cập nhật Tỷ trọng cho Phòng/Đài/MBF HCM',
                'url'          => 'updatePriorityPosition',
                'newValue'     => $importantLevel,
                'oldValue'     => '',
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);

            Session::flash('message-success', 'Cập nhật thành công!');

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /** managePriorityPosition/{cId}/{aId}/{pId}/{gId}/{y}/{m}*/
        return redirect(
            'managePriorityCompany'
            .'/'.$companyId
            .'/'.$goalId
            .'/'.$applyDate
        );

    }
    public function deletePriorityCompany(Request $request){

        $sDataUser = Session::get('sDataUser');
        $post = $request->all();
        $id   = $post['id'];


        if(!is_numeric($id)){
            Session::flash('message-errors', 'Tỷ trọng không hợp lệ!');
            return redirect('managePriorityCompany/0/0/0');
        }

        $sqlILC = "
            SELECT ilc.*, c.company_name, g.parent_id, g.goal_code
            FROM important_level_company ilc
            LEFT JOIN goal g ON g.id = ilc.goal_id
            LEFT JOIN company c ON c.id = ilc.company_id
            WHERE ilc.id = ".$id."
        ";

        $objILCDB  = DB::select(DB::raw($sqlILC));

        if(count($objILCDB) == 0){
            Session::flash('message-errors', 'Tỷ trọng không tồn tại!');
            return redirect('managePriorityCompany/0/0/0');
        }

        $arrDefaultMonth = commonUtils::defaultMonth();




        $ilc         = $objILCDB[0];
        $companyId   = $ilc->company_id;
        $companyName = $ilc->company_name;
        $goalId      = $ilc->goal_id;
        $parentId    = $ilc->parent_id;
        $applyDate   = $ilc->apply_date;
        $year        = date('Y', strtotime($applyDate));
        $fDate       = commonUtils::formatDate($applyDate);

        $oldValue = '['.$sDataUser->code.'] '.$sDataUser->name.' đã xóa tỷ trọng mục tiêu <b>'.$ilc->goal_code.'</b> có ngày áp dụng <b>'.$fDate.'</b> thuộc Phòng/Đài/MBF HCM: <b>'.$companyName.'</b> vào lúc <b>'.date("h:i:sa").'</b> ngày <b>'.date("d/m/Y").'</b>';
        $oldValue .= "<br/>* Các giá trị đã bị xóa: [Kế hoạch: ".$ilc->target_value."], [Tỷ trọng: ".$ilc->important_level."], [Điểm chuẩn: ".$ilc->benchmark."], [Điểm chuẩn KPI: ".$ilc->cal_benchmark."], [Điểm thực hiện: ".$ilc->implement_point."], [Điểm thực hiện KPI: ".$ilc->cal_implement_point."].";
        /* *************************************************************************************************************
         * Delete constrain
         * Update constrain
         * Cal KPI for  all
         * ************************************************************************************************************/
        DB::beginTransaction();
        try {

            $isLockedCom = $this->checkLockData( '', '', $applyDate, 1, '' );

            if($isLockedCom == 1){
                Session::flash('message-errors', 'Dữ liệu Phòng/Đài/MBF HCM <b>'.$companyName.'</b> áp dụng từ ngày <b>'.$fDate.'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                return redirect('managePriorityCompany/'.$companyId.'/'.$goalId.'/'.$applyDate);
            }

            foreach($arrDefaultMonth as $ml){

                $isLockedI = $this->checkLockData( $year, $ml, '', 2, $companyId );
                $isLockedT = $this->checkLockData( $year, $ml, '', 3, $companyId );
                $isLockedP = $this->checkLockData( $year, $ml, '', 4, $companyId );

                if(
                    $isLockedI    == 1
                    || $isLockedT == 1
                    || $isLockedP == 1
                ){
                    Session::flash('message-errors', 'Tồn tại tháng dữ liệu đang khóa áp dụng năm <b>'.$year.'</b>. Vui lòng kiểm tra lại!');
                    return redirect('managePriorityCompany/'.$companyId.'/'.$goalId.'/'.$applyDate);
                }

            }

            if($parentId != 0){
                $sqlCheckExistChild = "
                    SELECT *
                    FROM important_level_company
                    WHERE inactive = 0
                    AND apply_date = '".$applyDate."'
                    AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId.")
                ";

                $objExistChildDB  = DB::select(DB::raw($sqlCheckExistChild));

                if(count($objExistChildDB) == 1){
                    $goalId   = $parentId;
                    $parentId = 0;


                }
            }

            $this->deleteILC($id, $companyId, $year, $applyDate, $goalId, $parentId);

            $this->deleteILA('', $companyId, '', $year, $goalId, $parentId);

            $this->deleteILP('', $companyId, '', '', '', $year, '', $goalId, $parentId);

            $this->deleteTA($companyId, '', $year, $goalId, $parentId);

            $this->deleteTP($companyId, '','', '', $year, '', $goalId, $parentId);

            $this->deleteTE($companyId, '','', '', $year, '', $goalId, $parentId);

            $sqlILC = "
                SELECT *
                FROM important_level_company
                WHERE inactive = 0
                AND goal_id = ".$goalId."
                AND apply_date = '".$applyDate."'
            ";

            $objILCExistDB  = DB::select(DB::raw($sqlILC));

            $corAppLyDate = $this->getApplyDate4Corporation($year);

            if(count($objILCExistDB) == 0){

                $isLockedCor = $this->checkLockData( '', '', $corAppLyDate, 0, '' );

                if($isLockedCor == 1){
                    Session::flash('message-errors', 'Dữ liệu Cty Mobifone áp dụng từ ngày <b>'.commonUtils::formatDate($corAppLyDate).'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                    return redirect('managePriorityCompany/'.$companyId.'/'.$goalId.'/'.$applyDate);
                }

                $this->deleteILCor('', $year, $corAppLyDate, $goalId, $parentId);
                $this->calBenchmark4Corporation($corAppLyDate, $parentId);

            }

            $this->calBenchmark4EachCompany($companyId, $applyDate, $parentId);
            $this->calKPIBenchmark4Company('', $applyDate, $corAppLyDate);

            $sqlAreas = "
                SELECT a.id, a.company_id, c.company_name, a.area_code
                FROM area a
                LEFT JOIN company c ON c.id = a.company_id
                WHERE a.inactive = 0
                AND company_id = ".$companyId."
            ";

            $objAreasDB  = DB::select(DB::raw($sqlAreas));

            foreach($objAreasDB as $area){
                $this->calBenchmark4EachArea($companyId, $area->id, $year, '', $parentId);

            }

            $this->calKPIBenchmark4Area($companyId, '', $year, '', $applyDate);


            $sqlILP = "
                SELECT  position_id, `month`, area_id
                FROM important_level_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND `year` = ".$year."
                GROUP BY `month`, position_id, area_id
            ";
            $objILPDB  = DB::select(DB::raw($sqlILP));

            foreach($objAreasDB as $area){

                $arrILPEM = array();

                foreach($arrDefaultMonth as $m){

                    foreach($objILPDB as $ilp){
                        if(
                            $ilp->month == $m
                            && $ilp->area_id == $area->id
                        ){
                            if(!in_array($ilp->position_id, $arrILPEM)){
                                $arrILPEM[] = $ilp->position_id;
                            }
                        }
                    }

                    foreach($arrILPEM as $iI){
                        $this->calBenchmark4EachPosition($companyId, $area->id, $iI, $year, $m, $parentId);
                    }

                }


                $this->calKPIBenchmark4Position($companyId, $area->id, '', $year, '', 1);
                $this->updateBenchmark4TargetA($companyId, $area->id, $year, 1, $sDataUser->id);
                $this->updateBenchmark4TargetPE($companyId, $area->id, '', $year, '', $sDataUser->id, 1);

            }

            $this->updateCBenchmark4TargetA($companyId, '', $year, 1, 1);


            $sqlEmployee = "
                SELECT us.*
                FROM users us
                LEFT JOIN `position` p ON p.id = us.position_id
                WHERE us.inactive = 0
                AND us.admin = 0
                AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                AND us.id > 5
                AND us.company_id = ".$companyId."
            ";
            $objUsersDB  = DB::select(DB::raw($sqlEmployee));

            foreach($objAreasDB as $area){

                $idTQ = -1;
                foreach($objUsersDB as $user){
                    if($user->area_id == $area->id){
                        $idTQ = $user->id;
                    }
                }

                if($idTQ == -1){
                    Session::flash('message-errors', 'Lỗi: <b>'.$area->area_code.'</b> không tồn tại nhân viên có chức danh <b>'.commonUtils::POSITION_CODE_TQ.'</b>. Vui lòng kiểm tra lại và Kiểm tra cho các Tổ/Quận/Huyện khác.');
                    return redirect('managePriorityCompany/0/0/0');
                }

                $this->calKPI4Employee($companyId, $area->id, '', $year, '', 1 );
                $this->calKPI4Position($companyId, $area->id, '', $idTQ, $year, '', '', 0, $sDataUser->id, 1);
                $this->calKPI4Area($companyId, $area->id, $year, '', '', $sDataUser->id, 1);

            }

            $this->calKPI4Company($companyId, $year, $applyDate, '', $sDataUser->id, 1);
            $this->calKPI4Corporation($corAppLyDate, $applyDate, '', $sDataUser->id, 1);

            $dataLog = array(
                'functionName' => 'Xóa Tỷ trọng Phòng/Đài/MBF HCM (deletePriorityCompany)',
                'action'       => commonUtils::ACTION_DELETE,
                'url'          => 'deletePriorityCompany',
                'newValue'     => '',
                'oldValue'     => $oldValue,
                'createdUser'  => $sDataUser->id
            );
            $this->writeLog($dataLog);

            /**********************************************************************************************************/
            Session::flash('message-success', '1 dòng đã được xóa!');

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }


        return redirect('managePriorityCompany/'.$companyId.'/0/'.$applyDate);

    }

    /* *****************************************************************************************************************
     * Priority Area
     * ****************************************************************************************************************/
    public function managePriorityArea($companyId, $areaId, $goalId, $year, $month)
    {
        $this->clearSession();
        $isParent = -1;
        $data = array();
        $select = 'SELECT
                        ila.*
                        , goal.goal_code
                        , goal.parent_id
                        , goal.goal_name
                    FROM  important_level_area AS ila
                    LEFT JOIN goal ON goal.id = ila.goal_id
                    WHERE ila.inactive = 0
                    ';
        $selectYear = 'SELECT
                        ila.*
                        , goal.goal_code
                        , goal.parent_id
                        , goal.goal_name
                    FROM  important_level_area AS ila
                    LEFT JOIN goal ON goal.id = ila.goal_id
                    WHERE ila.inactive = 0
                    GROUP BY ila.year
                    ';
        if ($companyId != 0 && $areaId != 0) {
            $select .= ' AND ila.company_id = ' . $companyId;
            $select .= " AND ila.area_id = " . $areaId;

            if($goalId != 0){
                $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id',0)->count();
                if($isParent == 1){
                    $select .= ' AND ila.goal_id in (SELECT id FROM  goal WHERE id = '.$goalId.' or parent_id = '.$goalId.' ) ';
                }else{
                    $isParent = 0;
                    $select .= ' AND ila.goal_id = ' . $goalId;
                }
            }
            if ($year != 0) {
                $select .= " AND ila.year = " . $year;
            }

            if ($month != 0) {
                $select .= " AND ila.month = " . $month;
            }

            $data = DB::select(DB::raw($select));
            //
        }

        $dataYears = DB::select(DB::raw($selectYear));

        $companies = DB::table('company')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('inactive', 0)->where('parent_id', 0)->get();
        $gTwos = DB::table('goal')->where('inactive', 0)->where('parent_id', '<>', 0)->get();
        $areas = DB::table('area')->where('inactive', 0)->where('company_id', '=', $companyId)->get();

        return view('major.managePriorityArea')
            ->with('companies', $companies)
            ->with('gOnes', $gOnes)
            ->with('areas', $areas)
            ->with('gTwos', $gTwos)
            ->with('data', $data)
            ->with('isParent', $isParent)
            ->with('selectedCompany', $companyId)
            ->with('selectedGoal', $goalId)
            ->with('selectedArea', $areaId)
            ->with('selectedYear', $year)
            ->with('selectedMonth', $month)
            ->with('dataYears', $dataYears)
            ;
    }

    public function updatePriorityArea($id){

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Mã Tỷ trọng không hợp lệ!');
            return redirect('managePriorityArea/0/0/0/0/0');
        }

        $sqlILA = "
            SELECT ila.*, c.company_name, g.goal_name, a.area_name, g.formula, g.goal_type, g.parent_id
            FROM important_level_area ila
            LEFT JOIN goal g ON g.id = ila.goal_id
            LEFT JOIN company c ON c.id = ila.company_id
            LEFT JOIN area a ON a.id = ila.area_id
            WHERE ila.inactive = 0
            AND ila.id = ".$id."
        ";
        $objILADB  = DB::select(DB::raw($sqlILA));

        if(count($objILADB) == 0){
            Session::flash('message-errors', 'Mã Tỷ trọng không tồn tại!');
            return redirect('managePriorityArea/0/0/0/0/0');
        }

        $isLocked = $this->checkLockData($objILADB[0]->year, $objILADB[0]->month, '', 2, $objILADB[0]->company_id);

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('managePriorityArea/0/0/0/0/0');
        }

        return view('major.updatePriorityArea')
            ->with('priorityArea', $objILADB[0])
            ;
    }

    public function editPriorityArea(Request $request){

        DB::beginTransaction();
        try {

            $actionUser     = Session::get('sid');
            $post           = $request->all();
            $importantLevel = (int)trim($post['txtImportantLevel']);
            $defaultData    = $post['defaultData'];
            $arrDefaultData = explode(',', $defaultData);

            $id             = $arrDefaultData[0];
            $companyId      = $arrDefaultData[1];
            $areaId         = $arrDefaultData[2];
            $month          = $arrDefaultData[3];
            $year           = $arrDefaultData[4];
            $goalType       = $arrDefaultData[5];
            $goalId         = $arrDefaultData[6];
            $oldIL          = $arrDefaultData[7];
            $benchmark      = $arrDefaultData[8];
            $formula        = $arrDefaultData[9];
            $calBenchmark   = $arrDefaultData[10];
            $areaName       = $arrDefaultData[11];
            $parentId       = $arrDefaultData[12];

            if($oldIL == $importantLevel){
                return redirect(
                    'managePriorityArea'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$goalId
                    .'/'.$year
                    .'/'.$month
                );
            }

            if(!is_numeric($importantLevel) || $importantLevel <= 0 || !is_int($importantLevel)){
                Session::flash('message-errors', 'Tỷ trọng không hợp lệ!');
                return redirect(
                    'managePriorityArea'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$goalId
                    .'/'.$year
                    .'/'.$month
                );
            }

            $dataUpdateILA = array(
                'important_level' 	=> $importantLevel,
                'updated_user'      => $actionUser,
                'updated_date'      => date("Y-m-d h:i:sa")
            );

            DB::table('important_level_area')
                ->where('company_id', $companyId)
                ->where('area_id', $areaId)
                ->where('year', $year)
                ->where('goal_id', $goalId)
                ->update($dataUpdateILA);
            /* *********************************************************************************************************
             * Update benchmark cho toàn bộ mục tiêu với Tố/Quận hiện tại của table tỷ trọng và kế hoạch nv/chuc danh
             * Update cal_benchmark cho toàn bộ chức danh dối với mục tiêu hiện tại của table tỷ trọng và kế hoạch nv/chuc danh
             * Tính kpi cho toàn bộ nhân viên trong tháng hiện tại với chức danh đang xét trên toàn bộ mục tiêu
             * Tính KPI cho toàn bộ chức danh trong tháng hien tại với mục tiêu dang xét cho toàn bộ chức danh
             * Tính KPI của quận với mục tiêu đang xét
             * Tính kpi phòng với mục tiêu đang xét
             * Tính KPI Cty với mục tiêu đang xét
             * ********************************************************************************************************/


            $this->calBenchmark4EachArea($companyId, $areaId, $year, $month, $parentId);
            $companyApplyDate = $this->getApplyDate4Company($companyId, $year, '');
            $this->calKPIBenchmark4Area($companyId, $goalId, $year, $month, $companyApplyDate);
            $this->updateBenchmark4TargetA($companyId, $areaId, $year, $month, $actionUser);

            if($parentId != 0){
                $this->updateCBenchmark4TargetA($companyId, $goalId, $year, $month, 0);
            }

            $sqlILA = "
                SELECT ila.*, c.company_name, g.goal_name, a.area_name, g.formula, g.goal_type, g.parent_id
                FROM important_level_area ila
                LEFT JOIN goal g ON g.id = ila.goal_id
                LEFT JOIN company c ON c.id = ila.company_id
                LEFT JOIN area a ON a.id = ila.area_id
                WHERE ila.inactive = 0
                AND ila.company_id = ".$companyId."
                AND ila.area_id = ".$areaId."
                AND ila.year = ".$year."
                AND ila.month = ".$month."
            ";

            if($parentId != 0){
                $sqlILA .= " AND ( ila.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ila.goal_id = ".$parentId." )";
            }

            $objILADB  = DB::select(DB::raw($sqlILA));
            $arrMonth  = commonUtils::defaultMonth();

            foreach($objILADB as $ila){
                foreach($arrMonth as $m){
                    $this->calKPIBenchmark4Position($companyId, $areaId, $ila->goal_id, $year, $m, 0);
                    $this->updateCBenchmark4TargetPE($companyId, $areaId, $ila->goal_id, $year, $m);
                }
            }


            $sqlILP = "
                SELECT ilp.position_id
                FROM important_level_position ilp
                LEFT JOIN area a ON a.id = ilp.area_id
                WHERE ilp.inactive = 0
                AND ilp.company_id = ".$companyId."
                AND ilp.area_id = ".$areaId."
                AND ilp.goal_id = ".$goalId."
                AND ilp.year = ".$year."
                AND ilp.month = ".$month."
                GROUP BY ilp.position_id
            ";

            $objILPDB  = DB::select(DB::raw($sqlILP));

            $sqlEmployee = "
                    SELECT us.*, p.position_code
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    WHERE us.admin = 0
                    AND us.company_id = ".$companyId."
                    AND us.area_id = ".$areaId."
                    AND us.inactive = 0
                    AND us.id != 0
                    AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                ";
            $objEmployeeDB  = DB::select(DB::raw($sqlEmployee));

            if(count($objEmployeeDB) == 0){
                Session::flash('message-errors', $areaName.' không tồn tại nhân viên có chức danh '.commonUtils::POSITION_CODE_TQ.'. Vui lòng kiểm tra lại!');
                return redirect(
                    'managePriorityArea'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$goalId
                    .'/'.$year
                    .'/'.$month
                );
            }

            foreach($objILPDB as $ilp){

                $sqlDSPosition = "
                    SELECT tp.*, p.position_code
                    FROM target_position tp
                    LEFT JOIN `position` p ON p.id = tp.position_id
                    WHERE tp.inactive = 0
                    AND tp.company_id = ".$companyId."
                    AND tp.area_id = ".$areaId."
                    AND tp.position_id = ".$ilp->position_id."
                    AND tp.year = ".$year."
                    AND tp.month = ".$month."
                ";

                $objTargetPositionDB  = DB::select(DB::raw($sqlDSPosition));

                foreach($objTargetPositionDB as $dsTP){
                    $isTQ = (commonUtils::compareTwoString($dsTP->position_code, commonUtils::POSITION_CODE_TQ) == 1) ? 1 : 0;
                    $this->calKPI4Position($companyId, $areaId, $dsTP->position_id, $objEmployeeDB[0]->id, $year, $month, $dsTP->goal_id, $isTQ, $actionUser, 0);
                }
            }

            foreach($objILADB as $ila){
                foreach($arrMonth as $m){
                    $this->calKPI4Area($companyId, $areaId, $year, $m, $ila->goal_id, $actionUser, 0);
                }
            }

            $sqlIL = "
                SELECT ilc.*, c.company_name, g.goal_name, g.formula, g.goal_type, g.parent_id
                FROM important_level_company ilc
                LEFT JOIN goal g ON g.id = ilc.goal_id
                LEFT JOIN company c ON c.id = ilc.company_id
                WHERE ilc.inactive = 0
                AND ilc.company_id = ".$companyId."
                AND ilc.apply_date = '".$companyApplyDate."'
            ";

            if($parentId != 0){
                $sqlIL .= " AND ( ilc.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ilc.goal_id = ".$parentId." )";
            }

            $objILDB  = DB::select(DB::raw($sqlIL));

            foreach($objILDB as $ilc){
                $this->calKPI4Company($companyId, $year, $companyApplyDate, $ilc->goal_id, $actionUser, 0);
            }


            $corApplyDate = $this->getApplyDate4Corporation($year);


            $sqlILCOr = "
                SELECT *
                FROM important_level_corporation
                WHERE inactive = 0
                AND apply_date = '".$corApplyDate."'
            ";

            if($parentId != 0){
                $sqlILCOr .= " AND ( goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or goal_id = ".$parentId." )";
            }

            $objILCorDB = DB::select(DB::raw($sqlILCOr));

            foreach($objILCorDB as $ilcor){
                $this->calKPI4Corporation($corApplyDate, $companyApplyDate, $ilcor->goal_id, $actionUser, 0);
            }



            $dataLog = array(
                'functionName' => 'Cập nhật Tỷ trọng cho Chức danh (updatePriorityPosition)',
                'action'       => commonUtils::ACTION_EDIT,
                'url'          => 'updatePriorityPosition',
                'newValue'     => $importantLevel,
                'oldValue'     => '',
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);

            Session::flash('message-success', 'Cập nhật thành công!');

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /** managePriorityPosition/{cId}/{aId}/{pId}/{gId}/{y}/{m}*/
        return redirect(
            'managePriorityArea'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$goalId
            .'/'.$year
            .'/'.$month
        );

    }

    public function deletePriorityArea(Request $request){

        /*Session::flash('message-success', 'Coming soon!');
        return redirect('managePriorityArea/0/0/0/0/0');*/

        $sDataUser = Session::get('sDataUser');
        $post = $request->all();
        $id   = $post['id'];

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Tỷ trọng không hợp lệ!');
            return redirect('managePriorityArea/0/0/0/0/0');
        }

        $sqlILA = "
            SELECT ila.*, c.company_name, a.area_name, a.area_code, g.parent_id, g.goal_code
            FROM important_level_area ila
            LEFT JOIN goal g ON g.id = ila.goal_id
            LEFT JOIN company c ON c.id = ila.company_id
            LEFT JOIN area a ON a.id = ila.area_id
            WHERE ila.id = ".$id."
        ";

        $objILADB  = DB::select(DB::raw($sqlILA));

        if(count($objILADB) == 0){
            Session::flash('message-errors', 'Tỷ trọng không tồn tại!');
            return redirect('managePriorityArea/0/0/0/0/0');
        }

        $arrDefaultMonth = commonUtils::defaultMonth();

        $ila         = $objILADB[0];
        $companyId   = $ila->company_id;
        $areaId      = $ila->area_id;
        $areaCode    = $ila->area_code;
        $areaName    = $ila->area_name;
        $companyName = $ila->company_name;
        $goalId      = $ila->goal_id;
        $parentId    = $ila->parent_id;
        $year        = $ila->year;
        $month       = $ila->month;

        $corAppLyDate = $this->getApplyDate4Corporation($year);
        $comApplyDate = $this->getApplyDate4Company($companyId, $year, '');

        $oldValue = '['.$sDataUser->code.'] '.$sDataUser->name.' đã xóa tỷ trọng mục tiêu <b>'.$ila->goal_code.'</b> áp dụng tháng <b>'.$month.'/'.$year.'</b> cho Tổ/Quận/Huyện <b>'.$areaName.'</b> thuộc Phòng/Đài/MBF HCM: <b>'.$companyName.'</b> vào lúc <b>'.date("h:i:sa").'</b> ngày <b>'.date("d/m/Y").'</b>';
        $oldValue .= "<br/>* Các giá trị đã bị xóa: [Tỷ trọng: ".$ila->important_level."], [Điểm chuẩn: ".$ila->benchmark."], [Điểm chuẩn KPI: ".$ila->cal_benchmark."].";
        /* *************************************************************************************************************
         * Delete constrain
         * Update constrain
         * Cal KPI for  all
         * ************************************************************************************************************/
        DB::beginTransaction();
        try {

            if($parentId != 0){
                $sqlCheckExistChild = "
                    SELECT *
                    FROM important_level_area
                    WHERE inactive = 0
                    AND `company_id` = ".$companyId."
                    AND `area_id` = ".$areaId."
                    AND `year` = ".$year."
                    AND `month` = ".$month."
                    AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId.")
                ";

                $objExistChildDB  = DB::select(DB::raw($sqlCheckExistChild));

                if(count($objExistChildDB) == 1){
                    $goalId   = $parentId;
                    $parentId = 0;
                }
            }


            foreach($arrDefaultMonth as $ml){

                $isLockedI = $this->checkLockData( $year, $ml, '', 2, $companyId );
                $isLockedT = $this->checkLockData( $year, $ml, '', 3, $companyId );
                $isLockedP = $this->checkLockData( $year, $ml, '', 4, $companyId );

                if(
                    $isLockedI    == 1
                    || $isLockedT == 1
                    || $isLockedP == 1
                ){
                    Session::flash('message-errors', 'Tồn tại tháng dữ liệu đang khóa áp dụng năm <b>'.$year.'</b>. Vui lòng kiểm tra lại!');
                    return redirect('managePriorityArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$year.'/'.$month);
                }

            }


            $this->deleteILA($id, $companyId, $areaId, $year, $goalId, $parentId);

            $this->deleteILP('', $companyId, $areaId, '', '', $year, '', $goalId, $parentId);

            $this->deleteTA($companyId, $areaId, $year, $goalId, $parentId);

            $this->deleteTP($companyId, $areaId,'', '', $year, '', $goalId, $parentId);

            $this->deleteTE($companyId, $areaId,'', '', $year, '', $goalId, $parentId);


            $sqlILAExist = "
                SELECT *
                FROM important_level_area
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND goal_id = ".$goalId."
                AND `year` = ".$year."
                AND `month` = ".$month."
            ";

            $objILAExistDB  = DB::select(DB::raw($sqlILAExist));

            if(count($objILAExistDB) == 0){

                $isLockedCom = $this->checkLockData( '', '', $comApplyDate, 1, '' );

                if($isLockedCom == 1){
                    Session::flash('message-errors', 'Dữ liệu Phòng/Đài/MBF HCM <b>'.$companyName.'</b> áp dụng từ ngày <b>'.commonUtils::formatDate($comApplyDate).'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                    return redirect('managePriorityArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$year.'/'.$month);
                }

                $this->deleteILC('', $companyId, $year, $comApplyDate, $goalId, $parentId);
                $this->calBenchmark4EachCompany($companyId, $comApplyDate, $parentId);
                $this->calKPIBenchmark4Area($companyId, '', $year, '', $comApplyDate);
                $this->updateCBenchmark4TargetA($companyId, '', $year, 1, 1);

                $sqlILCExist = "
                    SELECT *
                    FROM important_level_company
                    WHERE inactive = 0
                    AND goal_id = ".$goalId."
                    AND apply_date = '".$comApplyDate."'
                ";

                $objILCExistDB  = DB::select(DB::raw($sqlILCExist));

                if(count($objILCExistDB) == 0){

                    $isLockedCor = $this->checkLockData( '', '', $corAppLyDate, 0, '' );

                    if($isLockedCor == 1){
                        Session::flash('message-errors', 'Dữ liệu Cty Mobifone áp dụng từ ngày <b>'.commonUtils::formatDate($corAppLyDate).'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                        return redirect('managePriorityArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$year.'/'.$month);
                    }

                    $this->deleteILCor('', $year, $corAppLyDate, $goalId, $parentId);
                    $this->calBenchmark4Corporation($corAppLyDate, $parentId);
                    $this->calKPIBenchmark4Company('', $comApplyDate, $corAppLyDate);

                }

            }

            $this->calBenchmark4EachArea($companyId, $areaId, $year, '', $parentId);

            $this->calKPIBenchmark4Area($companyId, '', $year, '', $comApplyDate);

            $sqlILP = "
                SELECT  position_id, `month`, area_id
                FROM important_level_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND `year` = ".$year."
                AND `area_id` = ".$areaId."
                GROUP BY `month`, position_id
            ";
            $objILPDB  = DB::select(DB::raw($sqlILP));

            $arrILPEM = array();

            foreach($arrDefaultMonth as $m){

                foreach($objILPDB as $ilp){
                    if(
                        $ilp->month == $m
                    ){
                        if(!in_array($ilp->position_id, $arrILPEM)){
                            $arrILPEM[] = $ilp->position_id;
                        }
                    }
                }

                foreach($arrILPEM as $iI){
                    $this->calBenchmark4EachPosition($companyId, $areaId, $iI, $year, $m, $parentId);
                }

            }

            foreach($arrDefaultMonth as $tm){
                $this->calKPIBenchmark4Position($companyId, $areaId, '', $year, $tm, 1);
            }

            $this->updateBenchmark4TargetA($companyId, $areaId, $year, 1, $sDataUser->id);

            $this->updateBenchmark4TargetPE($companyId, $areaId, '', $year, '', $sDataUser->id, 1);

            $sqlEmployee = "
                SELECT us.*
                FROM users us
                LEFT JOIN `position` p ON p.id = us.position_id
                WHERE us.inactive = 0
                AND us.admin = 0
                AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                AND us.id > 5
                AND us.company_id = ".$companyId."
                AND us.area_id = ".$areaId."
            ";
            $objUsersDB  = DB::select(DB::raw($sqlEmployee));

            if(count($objUsersDB) == 0){
                Session::flash('message-errors', 'Lỗi: <b>'.$areaCode.'</b> không tồn tại nhân viên có chức danh <b>'.commonUtils::POSITION_CODE_TQ.'</b>. Vui lòng kiểm tra lại và Kiểm tra cho các Tổ/Quận/Huyện khác.');
                return redirect('managePriorityArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$year.'/'.$month);
            }

            $idTQ = $objUsersDB[0]->id;

            $this->calKPI4Employee($companyId, $areaId, '', $year, '', 1 );
            $this->calKPI4Position($companyId, $areaId, '', $idTQ, $year, '', '', 0, $sDataUser->id, 1);
            $this->calKPI4Area($companyId, $areaId, $year, '', '', $sDataUser->id, 1);

            $this->calKPI4Company($companyId, $year, $comApplyDate, '', $sDataUser->id, 1);
            $this->calKPI4Corporation($corAppLyDate, $comApplyDate, '', $sDataUser->id, 1);

            $dataLog = array(
                'functionName' => 'Xóa Tỷ trọng Tổ/Quận/Huyện (deletePriorityArea)',
                'action'       => commonUtils::ACTION_DELETE,
                'url'          => 'deletePriorityArea',
                'newValue'     => '',
                'oldValue'     => $oldValue,
                'createdUser'  => $sDataUser->id
            );
            $this->writeLog($dataLog);

            Session::flash('message-success', '1 dòng đã được xóa!');
            /* ********************************************************************************************************/

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        return redirect('managePriorityArea/'.$companyId.'/'.$areaId.'/0/'.$year.'/'.$month);

    }
    /* *****************************************************************************************************************
     * Priority Position
     * ****************************************************************************************************************/
    public function managePriorityPosition($companyId, $areaId, $positionId, $goalId, $year, $month)
    {
        $this->clearSession();
        $isParent = -1;
        $data = array();
        $select = 'SELECT
                            ilp.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                        FROM  important_level_position AS ilp
                        LEFT JOIN goal ON goal.id = ilp.goal_id
                        WHERE ilp.inactive = 0
                    ';

        $selectYears = "
            SELECT ilp.year
            FROM  important_level_position  ilp
            LEFT JOIN goal g ON g.id = ilp.goal_id
            WHERE ilp.inactive = 0
            GROUP BY ilp.year
            ";

        if ($companyId != 0 && $positionId != 0 && $areaId != 0) {
            $select .= ' AND ilp.company_id = ' . $companyId;

            if($goalId != 0){
                $isParent = DB::table('goal')->where('id', $goalId)->where('inactive', 0)->where('parent_id',0)->count();

                if($isParent == 1){
                    $select .= ' AND ilp.goal_id in (SELECT id FROM  goal WHERE id = '.$goalId.' or parent_id = '.$goalId.' ) ';
                }else{
                    $isParent = 0;
                    $select .= ' AND ilp.goal_id = ' . $goalId;
                }
            }

            if ($areaId != 0) {
                $select .= " AND ilp.area_id = " . $areaId;
            }

            if ($positionId != 0) {
                $select .= " AND ilp.position_id = " . $positionId;
            }

            if ($year != 0) {
                $select .= " AND ilp.year = " . $year;
            }

            if ($month != 0) {
                $select .= " AND ilp.month = " . $month;
            }

            $data = DB::select(DB::raw($select));
        }

        $dataYears = DB::select(DB::raw($selectYears));

        $gOnes = DB::table('goal')->where('inactive', 0)->where('parent_id', 0)->get();
        $gTwos = DB::table('goal')->where('inactive', 0)->where('parent_id', '<>', 0)->get();
        $areas = DB::table('area')->where('inactive', 0)->where('company_id', '=', $companyId)->get();
        $companies = DB::table('company')->where('inactive', 0)->get();
        $positions = DB::table('position')->where('inactive', 0)->get();

        return view('major.managePriorityPosition')
            ->with('companies', $companies)
            ->with('positions', $positions)
            ->with('gTwos', $gTwos)
            ->with('areas', $areas)
            ->with('gOnes', $gOnes)
            ->with('data', $data)
            ->with('isParent', $isParent)
            ->with('selectedCompany', $companyId)
            ->with('selectedGoal', $goalId)
            ->with('selectedArea', $areaId)
            ->with('selectedPosition', $positionId)
            ->with('selectedYear', $year)
            ->with('selectedMonth', $month)
            ->with('dataYears', $dataYears)
            ;
    }

    public function updatePriorityPosition($id){

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Mã Tỷ trọng không hợp lệ!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        $sqlILP = "
            SELECT ilp.*, c.company_name, g.goal_name, p.position_name, a.area_name, g.formula, p.position_code, g.goal_type, g.parent_id
            FROM important_level_position ilp
            LEFT JOIN goal g ON g.id = ilp.goal_id
            LEFT JOIN position p ON p.id = ilp.position_id
            LEFT JOIN company c ON c.id = ilp.company_id
            LEFT JOIN area a ON a.id = ilp.area_id
            WHERE ilp.inactive = 0
            AND ilp.id = ".$id."
        ";
        $objILPDB  = DB::select(DB::raw($sqlILP));

        if(count($objILPDB) == 0){
            Session::flash('message-errors', 'Mã Tỷ trọng không tồn tại!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        $isLocked = $this->checkLockData($objILPDB[0]->year, $objILPDB[0]->month, '', 2, $objILPDB[0]->company_id);

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        return view('major.updatePriorityPosition')
            ->with('priorityPosition', $objILPDB[0])
            ;
    }

    public function editPriorityPosition(Request $request){

        DB::beginTransaction();
        try {

            $actionUser     = Session::get('sid');
            $post           = $request->all();
            $importantLevel = (int)trim($post['txtImportantLevel']);
            $defaultData    = $post['defaultData'];
            $arrDefaultData = explode(',', $defaultData);

            $id             = $arrDefaultData[0];
            $companyId      = $arrDefaultData[1];
            $areaId         = $arrDefaultData[2];
            $positionId     = $arrDefaultData[3];
            $month          = $arrDefaultData[4];
            $year           = $arrDefaultData[5];
            $goalType       = $arrDefaultData[6];
            $goalId         = $arrDefaultData[7];
            $oldIL          = $arrDefaultData[8];
            $benchmark      = $arrDefaultData[9];
            $positionCode   = $arrDefaultData[10];
            $formula        = $arrDefaultData[11];
            $calBenchmark   = $arrDefaultData[12];
            $areaName       = $arrDefaultData[13];
            $positionName   = $arrDefaultData[14];
            $parentId       = $arrDefaultData[15];

            if($oldIL == $importantLevel){
                return redirect(
                    'managePriorityPosition'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$positionId
                    .'/'.$goalId
                    .'/'.$year
                    .'/'.$month
                );
            }

            if(!is_numeric($importantLevel) || $importantLevel <= 0 || !is_int($importantLevel)){
                Session::flash('message-errors', 'Tỷ trọng không hợp lệ!');
                return redirect(
                    'managePriorityPosition'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$positionId
                    .'/'.$goalId
                    .'/'.$year
                    .'/'.$month
                );
            }

            $dataUpdateILP = array(
                'important_level' 	=> $importantLevel,
                'updated_user'      => $actionUser,
                'updated_date'      => date("Y-m-d h:i:sa")
            );

            DB::table('important_level_position')->where('id', $id)->update($dataUpdateILP);
            /* *********************************************************************************************************
             * Update benchmark cho toàn bộ mục tiêu với chức danh hiện tại của table tỷ trọng và kế hoạch nv/chuc danh
             * Update cal_benchmark cho toàn bộ chức danh dối với mục tiêu hiện tại của table tỷ trọng và kế hoạch nv/chuc danh
             * Tính kpi cho toàn bộ nhân viên trong tháng hiện tại với chức danh đang xét trên toàn bộ mục tiêu
             * Tính KPI cho toàn bộ chức danh trong tháng hien tại với mục tiêu dang xét cho toàn bộ chức danh
             * Tính KPI của quận với mục tiêu đang xét
             * Tính kpi phòng với mục tiêu đang xét
             * Tính KPI Cty với mục tiêu đang xét
             * ********************************************************************************************************/
            $this->calBenchmark4EachPosition($companyId, $areaId, $positionId, $year, $month, $parentId);
            $this->calKPIBenchmark4Position($companyId, $areaId, $goalId, $year, $month, 0);
            $this->updateBenchmark4TargetPE($companyId, $areaId, $positionId, $year, $month, $actionUser, 0);

            if($parentId != 0){
                $this->updateCBenchmark4TargetPE($companyId, $areaId, $goalId, $year, $month);
            }

            $sqlDiffTP = "
                SELECT tp.*, p.position_code
                FROM target_position tp
                LEFT JOIN `position` p ON p.id = tp.position_id
                LEFT JOIN goal g ON g.id = tp.goal_id
                WHERE tp.inactive = 0
                AND tp.company_id = ".$companyId."
                AND tp.area_id = ".$areaId."
                AND tp.position_id != ".$positionId."
                AND tp.year = ".$year."
                AND tp.month = ".$month."
                AND tp.goal_id = ".$goalId."
            ";

            $objDiffTPDB  = DB::select(DB::raw($sqlDiffTP));

            $sqlDSPosition = "
                SELECT tp.*, p.position_code
                FROM target_position tp
                LEFT JOIN `position` p ON p.id = tp.position_id
                WHERE tp.inactive = 0
                AND tp.company_id = ".$companyId."
                AND tp.area_id = ".$areaId."
                AND tp.position_id = ".$positionId."
                AND tp.year = ".$year."
                AND tp.month = ".$month."
            ";

            $objTargetPositionDB  = DB::select(DB::raw($sqlDSPosition));

            $sqlDSTEmployee = "
                SELECT *
                FROM target_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND position_id = ".$positionId."
                AND `year` = ".$year."
                AND `month` = ".$month."
            ";

            $objTargetEmployeeDB  = DB::select(DB::raw($sqlDSTEmployee));



            if(count($objTargetPositionDB) > 0 && count($objTargetEmployeeDB) > 0){

                $this->calKPI4Employee($companyId, $areaId, $positionId, $year, $month, 0);

                $sqlEmployee = "
                    SELECT us.*, p.position_code
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    WHERE us.admin = 0
                    AND us.company_id = ".$companyId."
                    AND us.area_id = ".$areaId."
                    AND us.inactive = 0
                    AND us.id != 0
                    AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                ";
                $objEmployeeDB  = DB::select(DB::raw($sqlEmployee));

                if(count($objEmployeeDB) == 0){
                    Session::flash('message-errors', $areaName.' không tồn tại nhân viên có chức danh '.commonUtils::POSITION_CODE_TQ.'. Vui lòng kiểm tra lại!');
                    return redirect('managePriorityPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$year.'/'.$month);
                }

                foreach($objTargetPositionDB as $dsTP){
                    $isTQ = (commonUtils::compareTwoString($dsTP->position_code, commonUtils::POSITION_CODE_TQ) == 1) ? 1 : 0;
                    $this->calKPI4Position($companyId, $areaId, $dsTP->position_id, $objEmployeeDB[0]->id, $year, $month, $dsTP->goal_id, $isTQ, $actionUser, 0);
                }

                foreach($objDiffTPDB as $diffTP){
                    $isTQ = (commonUtils::compareTwoString($diffTP->position_code, commonUtils::POSITION_CODE_TQ) == 1) ? 1 : 0;
                    $this->calKPI4Position($companyId, $areaId, $diffTP->position_id, $objEmployeeDB[0]->id, $year, $month, $diffTP->goal_id, $isTQ, $actionUser, 0);
                }

                $this->calKPI4Area($companyId, $areaId, $year, $month, $goalId, $actionUser, 0);
                $companyApplyDate = $this->getApplyDate4Company($companyId, $year, '');
                $this->calKPI4Company($companyId, $year, $companyApplyDate, $goalId, $actionUser, 0);
                $corApplyDate = $this->getApplyDate4Corporation($year);
                $this->calKPI4Corporation($corApplyDate, $companyApplyDate, $goalId, $actionUser, 0);

            }

            $dataLog = array(
                'functionName' => 'Cập nhật Tỷ trọng cho Chức danh (updatePriorityPosition)',
                'action'       => commonUtils::ACTION_EDIT,
                'url'          => 'updatePriorityPosition',
                'newValue'     => $importantLevel,
                'oldValue'     => '',
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);

            Session::flash('message-success', 'Cập nhật thành công!');

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /** managePriorityPosition/{cId}/{aId}/{pId}/{gId}/{y}/{m}*/
        return redirect(
            'managePriorityPosition'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$positionId
            .'/'.$goalId
            .'/'.$year
            .'/'.$month
        );

    }

    public function deletePriorityPosition(Request $request){


        Session::flash('message-success', 'Đang kiểm lại!');
        return redirect('managePriorityPosition/0/0/0/0/0/0');

        $sDataUser = Session::get('sDataUser');
        $post = $request->all();
        $id   = $post['id'];

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Tỷ trọng không hợp lệ!');
            return redirect('managePriorityPosition/0/0/0/0/0/0');
        }

        $sqlILP = "
            SELECT ilp.*, c.company_name, a.area_name, a.area_code, p.position_code, p.position_name, g.parent_id, g.goal_code
            FROM important_level_position ilp
            LEFT JOIN goal g ON g.id = ilp.goal_id
            LEFT JOIN company c ON c.id = ilp.company_id
            LEFT JOIN area a ON a.id = ilp.area_id
            LEFT JOIN `position` p ON p.id = ilp.area_id
            WHERE ilp.id = ".$id."
        ";

        $objILPDB  = DB::select(DB::raw($sqlILP));

        if(count($objILPDB) == 0){
            Session::flash('message-errors', 'Tỷ trọng không tồn tại!');
            return redirect('managePriorityPosition/0/0/0/0/0/0');
        }

        $arrDefaultMonth = commonUtils::defaultMonth();
        $ilp         = $objILPDB[0];
        $companyId   = $ilp->company_id;
        $areaId      = $ilp->area_id;
        $areaCode    = $ilp->area_code;
        $areaName    = $ilp->area_name;
        $positionId  = $ilp->position_id;
        $positionCode= $ilp->position_code;
        $positionName= $ilp->position_name;
        $companyName = $ilp->company_name;
        $goalId      = $ilp->goal_id;
        $parentId    = $ilp->parent_id;
        $year        = $ilp->year;
        $month       = $ilp->month;

        $corAppLyDate = $this->getApplyDate4Corporation($year);
        $comApplyDate = $this->getApplyDate4Company($companyId, $year, '');


        $oldValue = '['.$sDataUser->code.'] '.$sDataUser->name.' đã xóa tỷ trọng mục tiêu <b>'.$ilp->goal_code.'</b> áp dụng tháng <b>'.$month.'/'.$year.'</b> cho Chức danh <b>'.$positionName.'</b> thuộc Tổ/Quận/Huyện <b>'.$areaName.'</b>, Phòng/Đài/MBF HCM: <b>'.$companyName.'</b> vào lúc <b>'.date("h:i:sa").'</b> ngày <b>'.date("d/m/Y").'</b>';
        $oldValue .= "<br/>* Các giá trị đã bị xóa: [Tỷ trọng: ".$ilp->important_level."], [Điểm chuẩn: ".$ilp->benchmark."], [Điểm chuẩn KPI: ".$ilp->cal_benchmark."].";
        /* *************************************************************************************************************
         * Delete constrain
         * Update constrain
         * Cal KPI for  all
         * ************************************************************************************************************/

        DB::beginTransaction();
        try {

            if($parentId != 0){
                $sqlCheckExistChild = "
                    SELECT *
                    FROM important_level_position
                    WHERE inactive = 0
                    AND `company_id` = ".$companyId."
                    AND `area_id` = ".$areaId."
                    AND `position_id` = ".$positionId."
                    AND `year` = ".$year."
                    AND `month` = ".$month."
                    AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId.")
                ";

                $objExistChildDB  = DB::select(DB::raw($sqlCheckExistChild));

                if(count($objExistChildDB) == 1){
                    $goalId   = $parentId;
                    $parentId = 0;
                }
            }

            foreach($arrDefaultMonth as $ml){

                $isLockedI = $this->checkLockData( $year, $ml, '', 2, $companyId );
                $isLockedT = $this->checkLockData( $year, $ml, '', 3, $companyId );
                $isLockedP = $this->checkLockData( $year, $ml, '', 4, $companyId );

                if(
                    $isLockedI    == 1
                    || $isLockedT == 1
                    || $isLockedP == 1
                ){
                    Session::flash('message-errors', 'Tồn tại tháng dữ liệu đang khóa áp dụng năm <b>'.$year.'</b>. Vui lòng kiểm tra lại!');
                    return redirect('managePriorityPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$year.'/'.$month);
                }

            }

            $existILP = 1;
            $existILA = 1;
            $existILC = 1;

            if(commonUtils::compareTwoString($positionCode, commonUtils::POSITION_CODE_TQ) == 1){
                $this->deleteILP('', $companyId, $areaId, '', '', $year, '', $goalId, $parentId);
                $this->deleteTP($companyId, $areaId,'', '', $year, '', $goalId, $parentId);
                $this->deleteTE($companyId, $areaId,'', '', $year, '', $goalId, $parentId);
            }else{

                $this->deleteILP('', $companyId, $areaId, $positionId, $positionCode, $year, $month, $goalId, $parentId);
                $this->deleteTP($companyId, $areaId,$positionId, $positionCode, $year, $month, $goalId, $parentId);
                $this->deleteTE($companyId, $areaId,$positionId, $positionCode, $year, $month, $goalId, $parentId);
            }

            $sqlILPExist = "
                SELECT *
                FROM important_level_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND goal_id = ".$goalId."
                AND `year` = ".$year."
                AND `month` = ".$month."
            ";

            $objILPExistDB  = DB::select(DB::raw($sqlILPExist));

            if(count($objILPExistDB) == 0){

                $existILP = 0;

                $this->deleteILA('', $companyId, $areaId, $year, $goalId, $parentId);
                $this->deleteTA($companyId, $areaId, $year, $goalId, $parentId);
                $this->calBenchmark4EachArea($companyId, $areaId, $year, '', $parentId);
                $sqlILAExist = "
                    SELECT *
                    FROM important_level_area
                    WHERE inactive = 0
                    AND company_id = ".$companyId."
                    AND goal_id = ".$goalId."
                    AND `year` = ".$year."
                    AND `month` = ".$month."
                ";

                $objILAExistDB  = DB::select(DB::raw($sqlILAExist));

                if(count($objILAExistDB) == 0){

                    $existILA = 0;

                    $isLockedCom = $this->checkLockData( '', '', $comApplyDate, 1, '' );

                    if($isLockedCom == 1){
                        Session::flash('message-errors', 'Dữ liệu Phòng/Đài/MBF HCM <b>'.$companyName.'</b> áp dụng từ ngày <b>'.commonUtils::formatDate($comApplyDate).'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                        return redirect('managePriorityPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$year.'/'.$month);
                    }

                    $this->deleteILC('', $companyId, $year, $comApplyDate, $goalId, $parentId);
                    $this->calBenchmark4EachCompany($companyId, $comApplyDate, $parentId);

                    $sqlILCExist = "
                        SELECT *
                        FROM important_level_company
                        WHERE inactive = 0
                        AND goal_id = ".$goalId."
                        AND apply_date = '".$comApplyDate."'
                    ";

                    $objILCExistDB  = DB::select(DB::raw($sqlILCExist));

                    if(count($objILCExistDB) == 0){
                        $existILC = 0;
                        $isLockedCor = $this->checkLockData( '', '', $corAppLyDate, 0, '' );

                        if($isLockedCor == 1){
                            Session::flash('message-errors', 'Dữ liệu Cty Mobifone áp dụng từ ngày <b>'.commonUtils::formatDate($corAppLyDate).'</b> năm <b>'.$year.'</b> đang khóa. Vui lòng kiểm tra lại!');
                            return redirect('managePriorityPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$year.'/'.$month);
                        }

                        $this->deleteILCor('', $year, $corAppLyDate, $goalId, $parentId);
                        $this->calBenchmark4Corporation($corAppLyDate, $parentId);
                    }

                    $this->calKPIBenchmark4Company('', $comApplyDate, $corAppLyDate);

                }

                $this->calKPIBenchmark4Area($companyId, '', $year, '', $comApplyDate);
                $this->updateCBenchmark4TargetA($companyId, '', $year, 1, 1);
            }

            $sqlILP = "
                SELECT  position_id, `month`, area_id
                FROM important_level_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND `year` = ".$year."
                AND `area_id` = ".$areaId."
                GROUP BY `month`, position_id
            ";
            $objILPDB  = DB::select(DB::raw($sqlILP));

            foreach($arrDefaultMonth as $m){

                $arrILPEM = array();

                foreach($objILPDB as $ilp){
                    if(
                        $ilp->month == $m
                    ){
                        if(!in_array($ilp->position_id, $arrILPEM)){
                            $arrILPEM[] = $ilp->position_id;
                        }
                    }
                }

                foreach($arrILPEM as $iI){
                    $this->calBenchmark4EachPosition($companyId, $areaId, $iI, $year, $m, $parentId);
                }
            }

            $this->calKPIBenchmark4Position($companyId, $areaId, '', $year, $m, 1);

            $this->updateBenchmark4TargetPE($companyId, $areaId, '', $year, '', $sDataUser->id, 1);

            $sqlKPICompany = "
                SELECT * FROM company WHERE inactive = 0
            ";
            $objKPICompanyDB  = DB::select(DB::raw($sqlKPICompany));

            $sqlKPIArea = "
                SELECT * FROM area WHERE inactive = 0
            ";
            $objKPIAreaDB  = DB::select(DB::raw($sqlKPIArea));

            $sqlEmployee = "
                SELECT us.*
                FROM users us
                LEFT JOIN `position` p ON p.id = us.position_id
                WHERE us.inactive = 0
                AND us.admin = 0
                AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                AND us.id > 5
            ";
            $objUsersDB  = DB::select(DB::raw($sqlEmployee));

            foreach($objKPICompanyDB as $kpiCom){
                foreach($objKPIAreaDB as $kpiArea){
                    if($kpiArea->company_id == $kpiCom->id){
                        $idTQ = -1;
                        foreach($objUsersDB as $userTQ){
                            if($userTQ->area_id == $kpiArea->id){
                                $idTQ = $userTQ->id;
                            }
                        }
                        if($idTQ == -1){
                            Session::flash('message-errors', 'Lỗi: <b>'.$kpiArea->area_code.'</b> không tồn tại nhân viên có chức danh <b>'.commonUtils::POSITION_CODE_TQ.'</b>. Vui lòng kiểm tra lại và Kiểm tra cho các Tổ/Quận/Huyện khác.');
                            return redirect('managePriorityPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$year.'/'.$month);
                        }

                        $this->calKPI4Employee($kpiCom->id, $kpiArea->id, '', $year, '', 1 );
                        $this->calKPI4Position($kpiCom->id, $kpiArea->id, '', $idTQ, $year, '', '', 0, $sDataUser->id, 1);
                        $this->calKPI4Area($kpiCom->id, $kpiArea->id, $year, '', '', $sDataUser->id, 1);
                    }
                }
                $this->calKPI4Company($kpiCom->id, $year, $comApplyDate, '', $sDataUser->id, 1);
            }

            $this->calKPI4Corporation($corAppLyDate, $comApplyDate, '', $sDataUser->id, 1);

            $dataLog = array(
                'functionName' => 'Xóa Tỷ trọng Chức Danh (deletePriorityPosition)',
                'action'       => commonUtils::ACTION_DELETE,
                'url'          => 'deletePriorityPosition',
                'newValue'     => '',
                'oldValue'     => $oldValue,
                'createdUser'  => $sDataUser->id
            );
            $this->writeLog($dataLog);

            Session::flash('message-success', '1 dòng đã được xóa!');
            /* ********************************************************************************************************/

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        return redirect('managePriorityPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/0/'.$year.'/'.$month);

    }
    /* *****************************************************************************************************************
     * Goal Area
     * ****************************************************************************************************************/
    public function manageGoalArea($companyId, $areaId, $goalId, $goalType, $year, $month)
    {
        $this->clearSession();
        $data = array();
        $isParent = -1;

        $sqlILA = "
            SELECT ila.*, g.goal_name
            FROM important_level_area ila
            LEFT JOIN goal g ON g.id = ila.goal_id
            WHERE ila.inactive = 0
            AND ila.goal_id in (select id from goal where parent_id = 0)
        ";
        $sqlILAYear = "
            SELECT ila.*, g.goal_name
            FROM important_level_area ila
            LEFT JOIN goal g ON g.id = ila.goal_id
            WHERE ila.inactive = 0
            AND ila.goal_id in (select id from goal where parent_id = 0)
            GROUP BY ila.year
        ";
        $select = 'SELECT
                            ta.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , unit.unit_name
                            , unit.id as unit_id
                            , unit.unit_code
                        FROM  target_area AS ta
                        LEFT JOIN goal ON goal.id = ta.goal_id
                        LEFT JOIN unit ON unit.id = ta.unit_id
                        WHERE ta.inactive = 0
                    ';
        $arrILA = array();

        if ($companyId != 0 && $areaId != 0) {
            $select .= ' AND ta.company_id = ' . $companyId;
            $select .= " AND ta.area_id = '" . $areaId . "'";

            $sqlILA .= " AND ila.company_id = '".$companyId."' AND ila.area_id = '".$areaId."' ";

            if($goalId != 0){
                $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id',0)->count();
                if($isParent == 1){
                    $select .= ' AND ta.goal_id in (SELECT id FROM  goal WHERE id = '.$goalId.' or parent_id = '.$goalId.' ) ';

                    $sqlILA .= " AND ila.goal_id = '".$goalId."' ";

                }else{
                    $isParent = 0;
                    $select .= ' AND ta.goal_id = ' . $goalId;
                }

            }

            if ($goalType != 0 && $goalType != -1 ) {
                $select .= " AND ta.goal_type = '" . $goalType . "'";
            }

            if ($year != 0) {
                $select .= " AND ta.year = '" . $year . "'";
                $sqlILA .= " AND ila.year = '".$year."' ";
            }

            if ($month != 0) {
                $select .= " AND ta.month = '" . $month . "'";
                $sqlILA .= " AND ila.month = '".$month."' ";
            }


            $data = DB::select(DB::raw($select));
            $dataILA = DB::select(DB::raw($sqlILA));

            if(count($dataILA) > 0 && count($data) > 0){
                foreach($dataILA as $ila){
                    $ilaGoalId          = $ila->goal_id;
                    $ilaGoalName        = $ila->goal_name;
                    $ilaImportantLevel  = $ila->important_level;
                    $ilaBenchmark       = $ila->benchmark;
                    $ilaCalBenchmark    = $ila->cal_benchmark;
                    $totalGV = 0;
                    $isExists = 0;
                    foreach($data as $ta){
                        $taParentId = $ta->parent_id;
                        $taGatherValue = $ta->gather_value;
                        if($taParentId == $ilaGoalId){
                            $isExists = 1;
                            $totalGV += $taGatherValue;
                        }
                    }
                    if($isExists == 1){
                        $index = count($arrILA);
                        $arrILA[$index]['goalId'] = $ilaGoalId;
                        $arrILA[$index]['goalName'] = $ilaGoalName;
                        $arrILA[$index]['importantLevel'] = $ilaImportantLevel;
                        $arrILA[$index]['benchmark'] = $ilaBenchmark;
                        $arrILA[$index]['cal_benchmark'] = $ilaCalBenchmark;
                        $arrILA[$index]['gatherValue'] = $totalGV;
                    }
                }
            }
        }

        $dataYears = DB::select(DB::raw($sqlILAYear));

        $companies = DB::table('company')->where('inactive', 0)->get();
        $areas = DB::table('area')->where('inactive', 0)->where('company_id', '=', $companyId)->get();
        $gOnes = DB::table('goal')->where('inactive', 0)->where('parent_id', 0)->get();
        $gTwos = DB::table('goal')->where('inactive', 0)->where('parent_id', '<>', 0)->get();

        return view('major.manageGoalArea')
            ->with('companies', $companies)
            ->with('areas', $areas)
            ->with('data', $data)
            ->with('isParent', $isParent)
            ->with('selectedCompany', $companyId)
            ->with('selectedArea', $areaId)
            ->with('selectedGoal', $goalId)
            ->with('selectedGoalType', $goalType)
            ->with('selectedYear', $year)
            ->with('selectedMonth', $month)
            ->with('dataYears', $dataYears)
            ->with('arrILA', $arrILA)
            ->with('gTwos', $gTwos)
            ->with('gOnes', $gOnes);
    }

    public function updateGoalArea($id){
        if(!is_numeric($id)){
            Session::flash('message-errors', 'Mã Kế hoạch/ Thực hiện không hợp lệ!');
            return redirect('manageGoalArea/0/0/0/-1/0/0');
        }

        $selectDataGoalArea = 'select ta.*, g.goal_name, g.goal_code, c.company_name,
                                      a.area_name, u.unit_name, a.area_code, g.formula
                                from target_area as ta
                                join goal as g on ta.goal_id = g.id
                                join company as c on c.id = ta.company_id
                                join area as a on a.id = ta.area_id
                                join unit as u on u.id = g.unit_id
                                where ta.inactive = 0 and ta.id = '.$id;
        $dataGoalArea = DB::select(DB::raw($selectDataGoalArea));
        if(count($dataGoalArea) == 0){
            Session::flash('message-errors', 'Mã Kế hoạch/ Thực hiện không tồn tại!');
            return redirect('manageGoalArea/0/0/0/-1/0/0');
        }

        $isLocked = $this->checkLockData($dataGoalArea[0]->year, $dataGoalArea[0]->month, '', 3, $dataGoalArea[0]->company_id);

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('manageGoalArea/0/0/0/-1/0/0');
        }

        return view('major.updateGoalArea')->with('targetAreaDB', $dataGoalArea)->with('targetAreaDB', $dataGoalArea);
    }

    public function editGoalArea(Request $request){
        $post = $request->all();
        $defaultData = $post['defaultData'];
        $data = explode(',', $defaultData);
        $targetValue = $post['txtTargetValue'];

        if(!is_numeric($targetValue) || $targetValue < 0){
            Session::flash('message-errors', 'Cập nhật không thành công! Số Kế hoạch/Thực hiện phải là số và lớn hơn 0.');
            return redirect('updateGoalArea/'.$data[0]);
        }
        if($data[6] == $targetValue){
            Session::flash('message-success', 'Cập nhật thành công!');
            return redirect('manageGoalArea/'.$data[1].'/'.$data[2].'/0/-1/'.$data[5].'/'.$data[4]);
        }
        $dataUpdate = array('target_value' => $targetValue);
        $update = DB::table('target_area')
            ->where('id', $data[0])
            ->update($dataUpdate);

        if(count($update) == 0){
            Session::flash('message-errors', 'Cập nhật không thành công!');
            return redirect('updateGoalArea/'.$data[0]);
        } else {
            Session::flash('message-success', 'Cập nhật thành công!');
            return redirect('manageGoalArea/'.$data[1].'/'.$data[2].'/'.$data[3].'/-1/'.$data[5].'/'.$data[4]);
        }
    }

    public function addGoalArea($companyId, $areaId, $goalId, $goalType, $month ){
        $year = date('Y');
        if(
            !is_numeric($companyId)
            || !is_numeric($areaId)
            || !is_numeric($goalType)
            || !is_numeric($month)
            || !is_numeric($goalId)
        ){
            Session::flash('message-errors', 'Dữ liệu không hợp lệ!');
            return redirect('manageGoalArea/0/0/0/-1/0/0');
        }

        $isLocked = $this->checkLockData($year, $month, '', 3, $companyId);

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('manageGoalArea/0/0/0/-1/0/0');
        }

        $sqlCompany = "
            SELECT *
            FROM company
            WHERE inactive = 0
            AND id = ".$companyId."
        ";
        $objCompanyDB  = DB::select(DB::raw($sqlCompany));

        if(count($objCompanyDB) == 0){
            Session::flash('message-errors', 'Phòng/Đài/MBF HCM không tồn tại!');
            return redirect('manageGoalArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($goalType == -1){
            Session::flash('message-errors', 'Vui lòng chọn 1 loại mục tiêu!');
            return redirect('manageGoalArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlArea = "
                SELECT *
                FROM area
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND id = ".$areaId."
            ";

        $objAreaDB  = DB::select(DB::raw($sqlArea));

        if(count($objAreaDB) == 0){
            Session::flash('message-errors', 'Tổ/Quận/Huyện không tồn tại!');
            return redirect('manageGoalArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($goalId == 0){
            Session::flash('message-errors', 'Vui lòng chọn 1 mục tiêu!');
            return redirect('manageGoalArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlGoal = "
            SELECT g.*, u.unit_name
            FROM goal g
            LEFT JOIN unit u ON u.id = g.unit_id
            WHERE g.inactive = 0
            AND g.id = ".$goalId."
        ";

        if(
            $month      != 0
            && $year    != 0
        ){
            $sqlGoal .= " AND g.id NOT IN (
                SELECT distinct goal_id
                FROM target_area
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND year = ".$year."
                AND month = ".$month."
            ) "
            ;

            $sqlGoal .= " AND g.id IN (
                SELECT distinct goal_id
                FROM important_level_area
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND year = ".$year."
                AND month = ".$month."
                AND goal_id = ".$goalId."
            ) "
            ;
        }

        $objGoalDB  = DB::select(DB::raw($sqlGoal));

        if(count($objGoalDB) == 0){
            Session::flash('message-errors', 'Mục tiêu đang chọn đã tồn tại Kế hoạch hoặc chưa tồn tại Tỷ trọng! Vui lòng kiểm tra lại!');
            return redirect('manageGoalArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($objGoalDB[0]->parent_id == 0){
            Session::flash('message-errors', 'Vui lòng chọn mục tiêu con!');
            return redirect('manageGoalArea/'.$companyId.'/'.$areaId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        return view('major.addGoalArea')
            ->with('objCompanyDB', $objCompanyDB[0])
            ->with('objAreaDB', $objAreaDB[0])
            ->with('objGoalDB', $objGoalDB[0])
            ->with('month', $month)
            ->with('year', $year)
            ;
    }

    public function saveGoalArea(Request $request){
        DB::beginTransaction();
        try{
            $actionUser  = Session::get('sid');
            $post        = $request->all();

            $defaultData = $post['defaultData'];
            $targetValue = $post['targetValue'];

            $arrDefaultData = explode(',', $defaultData);

            $companyId   = $arrDefaultData[0];
            $companyName = $arrDefaultData[1];
            $areaId      = $arrDefaultData[2];
            $areaName    = $arrDefaultData[3];
            $goalId      = $arrDefaultData[4];
            $goalType    = $arrDefaultData[5];
            $unitId      = $arrDefaultData[6];
            $year        = $arrDefaultData[7];
            $month       = $arrDefaultData[8];

            /* *************************************************************************************************************
             * Check data input
             * ************************************************************************************************************/

            /* *************************************************************************************************************
             * Insert database
             * ************************************************************************************************************/
            $sqlILA = "
                SELECT *
                FROM important_level_area
                WHERE company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND goal_id = ".$goalId."
                AND year = ".$year."
                AND month = ".$month."
            ";

            $objILADB  = DB::select(DB::raw($sqlILA));

            $ila = $objILADB[0];

            $targetArea = array(
                'company_id'        => $companyId,
                'area_id'           => $areaId,
                'goal_id'           => $goalId,
                'goal_type'         => $goalType,
                'unit_id'           => $unitId,
                'target_value'      => $targetValue,
                'month'             => $month,
                'year'              => $year,
                'important_level'   => $ila->important_level,
                'benchmark'         => $ila->benchmark,
                'cal_benchmark'     => $ila->cal_benchmark,
                'goal_level'        => 1,
                'created_user'      => $actionUser,
                'updated_user'      => 1
            );
            DB::table('target_area')->insert($targetArea);

            $dataLog = array(
                'functionName' => 'Kế hoạch cho Tổ/Quận/Huyện (addGoalArea)',
                'action'       => commonUtils::ACTION_INSERT,
                'url'          => 'addGoalArea',
                'newValue'     => $targetValue,
                'oldValue'     => '',
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);


            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /* *************************************************************************************************************
         * redirect home page
         * manageGoalArea/{cId}/{aId}/{gId}/{gtId}/{y}/{m}
         * ************************************************************************************************************/
        Session::flash('message-success', 'Đã thêm thành công 1 dòng kế hoạch tháng '.$month.'/'.$year.' cho Tổ/Quận/Huyện: '.$areaName.' thuộc Phòng/Đài/MBF HCM: '.$companyName.'!');
        return redirect(
            'manageGoalArea'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$goalId
            .'/'.$goalType
            .'/'.$year
            .'/'.$month
        );

    }
    /* *****************************************************************************************************************
     * Goal Position
     * ****************************************************************************************************************/
    public function manageGoalPosition($companyId, $areaId, $positionId, $goalId, $goalType, $year, $month)
    {
        $this->clearSession();
        $isParent = -1;
        $companies = DB::table('company')->where('inactive', 0)->get();
        $areas = DB::table('area')->where('inactive', 0)->where('company_id', '=', $companyId)->get();
        $goalOnes = DB::table('goal')->where('inactive', 0)->where('parent_id', 0)->get();
        $goalTwos = DB::table('goal')->where('inactive', 0)->where('parent_id', '<>', 0)->get();
        $positions = DB::table('position')->where('inactive', 0)->get();

        $data = array();
        $arrParentId = array();
        $arrParent = array();

        $select = 'SELECT
                            tp.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , unit.unit_name
                            , unit.unit_code
                            , unit.id as unit_id
                        FROM  target_position AS tp
                        LEFT JOIN goal ON goal.id = tp.goal_id
                        LEFT JOIN unit ON unit.id = tp.unit_id
                        WHERE tp.inactive = 0
                    ';

        $selectILGoal = 'select ilp.important_level, ilp.benchmark, ilp.goal_id, ilp.cal_benchmark
                        from important_level_position as ilp
                        join goal on goal.id = ilp.goal_id
                        where ilp.inactive = 0 ';
        $selectILPYear = 'select ilp.year
                        from important_level_position as ilp
                        where ilp.inactive = 0
                        group by ilp.year
                        order by ilp.year
                        ';
        if ($companyId != 0 && $positionId != 0 && $month != 0) {
            $select .= ' AND tp.company_id = ' . $companyId;
            $selectILGoal .= ' AND ilp.company_id = ' . $companyId;

            if($goalId != 0){
                $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id',0)->count();
                if($isParent == 1){
                    $select .= ' AND tp.goal_id in (SELECT id FROM  goal WHERE id = '.$goalId.' or parent_id = '.$goalId.' ) ';
                }else{
                    $isParent = 0;
                    $select .= ' AND tp.goal_id = ' . $goalId;
                }

            }

            if ($areaId != 0) {
                $select .= " AND tp.area_id = '" . $areaId . "'";
                $selectILGoal .= " AND ilp.area_id = '" . $areaId . "'";
            }

            if ($positionId != 0) {
                $select .= " AND tp.position_id = '" . $positionId . "'";
                $selectILGoal .= " AND ilp.position_id = '" . $positionId . "'";
            }

            if ($goalType != 0 && $goalType != -1) {
                $select .= " AND tp.goal_type = '" . $goalType . "'";
            }

            if ($year != 0) {
                $select .= " AND tp.year = '" . $year . "'";
                $selectILGoal .= " AND ilp.year = '" . $year . "'";
            }

            if ($month != 0) {
                $select .= " AND tp.month = '" . $month . "'";
                $selectILGoal .= " AND ilp.month = '" . $month . "'";
            }
            // echo $select;
            $data = DB::select(DB::raw($select));
            $dataILP = DB::select(DB::raw($selectILGoal));

            foreach($data as $ge){
                if($ge->parent_id != 0 && !in_array($ge->parent_id, $arrParentId)){
                    $arrParentId[] = $ge->parent_id;
                }
            }

            for ($pi = 0; $pi< count($arrParentId); $pi++){
                $arrParent[$pi]['pId'] = $arrParentId[$pi];
                $arrParent[$pi]['pName'] = $this->getGoalName($arrParentId[$pi])->goal_name;
                $ILP = 0;
                $BM = 0;
                $CBM = 0;
                foreach($dataILP as $rowILP){
                    if($rowILP->goal_id == $arrParentId[$pi]){
                        $ILP = $rowILP->important_level;
                        $BM = $rowILP->benchmark;
                        $CBM = $rowILP->cal_benchmark;
                    }
                }
                $arrParent[$pi]['il'] = $ILP;
                $arrParent[$pi]['bm'] = $BM;
                $arrParent[$pi]['cbm'] = $CBM;
            }
        }

        $dataYears = DB::select(DB::raw($selectILPYear));

        return view('major.manageGoalPosition')
            ->with('companies', $companies)
            ->with('data', $data)
            ->with('arrParent', $arrParent)
            ->with('areas', $areas)
            ->with('gOnes', $goalOnes)
            ->with('positions', $positions)
            ->with('gTwos', $goalTwos)
            ->with('isParent', $isParent)
            ->with('selectedCompany', $companyId)
            ->with('selectedArea', $areaId)
            ->with('selectedPosition', $positionId)
            ->with('selectedGoal', $goalId)
            ->with('selectedGoalType', $goalType)
            ->with('selectedYear', $year)
            ->with('dataYears', $dataYears)
            ->with('selectedMonth', $month);
    }

    public function addGoalPosition($companyId, $areaId, $positionId, $goalId, $goalType, $month ){
        $year = date('Y');
        if(
            !is_numeric($companyId)
            || !is_numeric($areaId)
            || !is_numeric($positionId)
            || !is_numeric($goalType)
            || !is_numeric($month)
            || !is_numeric($goalId)
        ){
            Session::flash('message-errors', 'Dữ liệu không hợp lệ!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        $isLocked = $this->checkLockData($year, $month, '', 3, $companyId);

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        $sqlCompany = "
            SELECT *
            FROM company
            WHERE inactive = 0
            AND id = ".$companyId."
        ";
        $objCompanyDB  = DB::select(DB::raw($sqlCompany));

        if(count($objCompanyDB) == 0){
            Session::flash('message-errors', 'Phòng/Đài/MBF HCM không tồn tại!');
            return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($goalType == -1){
            Session::flash('message-errors', 'Vui lòng chọn 1 loại mục tiêu!');
            return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlArea = "
                SELECT *
                FROM area
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND id = ".$areaId."
            ";

        $objAreaDB  = DB::select(DB::raw($sqlArea));

        if(count($objAreaDB) == 0){
            Session::flash('message-errors', 'Tổ/Quận/Huyện không tồn tại!');
            return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlPosition = "
                SELECT *
                FROM position
                WHERE inactive = 0
                AND id = ".$positionId."
            ";

        $objPositionDB  = DB::select(DB::raw($sqlPosition));

        if(count($objPositionDB) == 0){
            Session::flash('message-errors', 'Chức danh không tồn tại!');
            return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($goalId == 0){
            Session::flash('message-errors', 'Vui lòng chọn 1 mục tiêu!');
            return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlGoal = "
            SELECT g.*, u.unit_name
            FROM goal g
            LEFT JOIN unit u ON u.id = g.unit_id
            WHERE g.inactive = 0
            AND g.id = ".$goalId."
        ";

        if(
            $month      != 0
            && $year    != 0
        ){
            $sqlGoal .= " AND g.id NOT IN (
                SELECT distinct goal_id
                FROM target_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND position_id = ".$positionId."
                AND year = ".$year."
                AND month = ".$month."
            ) "
            ;

            $sqlGoal .= " AND g.id IN (
                SELECT distinct goal_id
                FROM important_level_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND position_id = ".$positionId."
                AND year = ".$year."
                AND month = ".$month."
                AND goal_id = ".$goalId."
            ) "
            ;
        }

        $objGoalDB  = DB::select(DB::raw($sqlGoal));


        if(count($objGoalDB) == 0){
            Session::flash('message-errors', 'Mục tiêu đang chọn đã tồn tại Kế hoạch hoặc chưa tồn tại Tỷ trọng! Vui lòng kiểm tra lại!');
            return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($objGoalDB[0]->parent_id == 0){
            Session::flash('message-errors', 'Vui lòng chọn mục tiêu con!');
            return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        return view('major.addGoalPosition')
            ->with('objCompanyDB', $objCompanyDB[0])
            ->with('objAreaDB', $objAreaDB[0])
            ->with('objPositionDB', $objPositionDB[0])
            ->with('objGoalDB', $objGoalDB[0])
            ->with('month', $month)
            ->with('year', $year)
            ;
    }

    public function saveGoalPosition(Request $request){
        DB::beginTransaction();
        try{
            $actionUser  = Session::get('sid');
            $post        = $request->all();

            $defaultData = $post['defaultData'];
            $targetValue = $post['targetValue'];

            $arrDefaultData = explode(',', $defaultData);

            $companyId   = $arrDefaultData[0];
            $companyName = $arrDefaultData[1];
            $areaId      = $arrDefaultData[2];
            $areaName    = $arrDefaultData[3];
            $positionId  = $arrDefaultData[4];
            $positionName= $arrDefaultData[5];
            $goalId      = $arrDefaultData[6];
            $goalType    = $arrDefaultData[7];
            $unitId      = $arrDefaultData[8];
            $year        = $arrDefaultData[9];
            $month       = $arrDefaultData[10];
            $positionCode= $arrDefaultData[11];

            /* *************************************************************************************************************
             * Check data input
             * ************************************************************************************************************/

            /* *************************************************************************************************************
             * Insert database
             * ************************************************************************************************************/
            $sqlILP = "
                SELECT *
                FROM important_level_position
                WHERE company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND goal_id = ".$goalId."
                AND year = ".$year."
                AND month = ".$month."
            ";

            $objILPDB  = DB::select(DB::raw($sqlILP));

            $ilp = $objILPDB[0];

            $targetPosition = array(
                'company_id'        => $companyId,
                'area_id'           => $areaId,
                'position_id'       => $positionId,
                'goal_id'           => $goalId,
                'month'             => $month,
                'year'              => $year,
                'cal_benchmark'     => $ilp->cal_benchmark,
                'important_level'   => $ilp->important_level,
                'target_value'      => $targetValue,
                'unit_id'           => $unitId,
                'benchmark'         => $ilp->benchmark,
                'goal_type'         => $goalType,
                'goal_level'        => 1,
                'created_user'      => $actionUser,
                'updated_user'      => 1
            );
            DB::table('target_position')->insert($targetPosition);

            if(commonUtils::compareTwoString($positionCode, commonUtils::POSITION_CODE_TQ) == 1){

                $sqlEmployee = "
                    SELECT *
                    FROM users
                    WHERE admin = 0
                    AND company_id = ".$companyId."
                    AND area_id = ".$areaId."
                    AND position_id = ".$positionId."
                    AND inactive = 0
                    AND id != 0
                ";
                $objEmployeeDB  = DB::select(DB::raw($sqlEmployee));

                if(count($objEmployeeDB) != 1){
                    Session::flash('message-errors', $areaName .' chưa tồn tại nhân viên có chức danh '.$positionName.', Vui lòng kiểm tra lại!');
                    return redirect('manageGoalPosition/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
                }

                $targetEmployee = array(
                    'company_id'        => $companyId,
                    'area_id'           => $areaId,
                    'position_id'       => $positionId,
                    'user_id'           => $objEmployeeDB[0]->id,
                    'goal_id'           => $goalId,
                    'month'             => $month,
                    'year'              => $year,
                    'important_level'   => $ilp->important_level,
                    'target_value'      => $targetValue,
                    'unit_id'           => $unitId,
                    'benchmark'         => $ilp->benchmark,
                    'cal_benchmark'     => $ilp->cal_benchmark,
                    'goal_type'         => $goalType,
                    'goal_level'        => 1,
                    'created_user'      => $actionUser,
                    'updated_user'      => 1
                );
                DB::table('target_employee')->insert($targetEmployee);

            }


            $dataLog = array(
                'functionName' => 'Kế hoạch cho Chức danh (addGoalPosition)',
                'action'       => commonUtils::ACTION_INSERT,
                'url'          => 'addGoalPosition',
                'newValue'     => $targetValue,
                'oldValue'     => '',
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);


            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /* *************************************************************************************************************
         * redirect home page
         * ************************************************************************************************************/
        Session::flash('message-success', 'Đã thêm thành công 1 dòng kế hoạch tháng '.$month.'/'.$year.' cho Chức danh '.$positionName.' thuộc Tổ/Quận/Huyện: '.$areaName.' & Phòng/Đài/MBF HCM: '.$companyName.'!');
        return redirect(
            'manageGoalPosition'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$positionId
            .'/'.$goalId
            .'/'.$goalType
            .'/'.$year
            .'/'.$month
        );

    }

    public function updateGoalPosition($id){

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Mã Kế hoạch/ Thực hiện không hợp lệ!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        $sqlTargetPosition = "
            SELECT tp.*, c.company_name, g.goal_name, p.position_name, a.area_name, u.unit_name, g.formula, p.position_code
            FROM target_position tp
            LEFT JOIN goal g ON g.id = tp.goal_id
            LEFT JOIN position p ON p.id = tp.position_id
            LEFT JOIN company c ON c.id = tp.company_id
            LEFT JOIN area a ON a.id = tp.area_id
            LEFT JOIN unit u ON u.id = tp.unit_id
            WHERE tp.inactive = 0
            AND tp.id = ".$id."
        ";
        $targetPositionDB  = DB::select(DB::raw($sqlTargetPosition));

        if(count($targetPositionDB) == 0){
            Session::flash('message-errors', 'Mã Kế hoạch/ Thực hiện không tồn tại!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        $isLocked = $this->checkLockData($targetPositionDB[0]->year, $targetPositionDB[0]->month, '', 3, $targetPositionDB[0]->company_id);

        if($isLocked == 1){
            Session::flash('message-errors', 'Dữ liệu đang khóa!');
            return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
        }

        return view('major.updateGoalPosition')
            ->with('targetPositionDB', $targetPositionDB);
    }

    public function editGoalPosition(Request $request){

        DB::beginTransaction();
        try {

            $actionUser = Session::get('sid');

            $post = $request->all();

            $defaultData = $post['defaultData'];
            $targetValue = $post['txtTargetValue'];

            $arrDefaultData = explode(',', $defaultData);

            $idTE           = $arrDefaultData[0];
            $companyId      = $arrDefaultData[1];
            $areaId         = $arrDefaultData[2];
            $positionId     = $arrDefaultData[3];
            $month          = $arrDefaultData[4];
            $year           = $arrDefaultData[5];
            $goalType       = $arrDefaultData[6];
            $goalId         = $arrDefaultData[7];
            $unitId         = $arrDefaultData[8];
            $importantLevel = $arrDefaultData[9];
            $targetValueOld = $arrDefaultData[10];
            $implementOld   = $arrDefaultData[11];
            $benchmark      = $arrDefaultData[12];
            $positionCode   = $arrDefaultData[13];
            $formula        = $arrDefaultData[14];
            $calBenchmark   = $arrDefaultData[15];
            $areaName       = $arrDefaultData[16];

            if(
                !is_numeric($targetValue)
                || !is_numeric($implementOld)
                || !is_numeric($calBenchmark)
                || !is_numeric($goalType)
            ){
                Session::flash('message-errors', 'Dữ liệu không hợp lệ!');
                return redirect('manageGoalPosition/0/0/0/0/-1/0/0');
            }

            if($targetValueOld != $targetValue){
                /* *****************************************************************************************************
                 * Cập nhật Kế hoạch cho chức danh
                 * ****************************************************************************************************/
                $dataUpdateTP = array(
                    'target_value' 	    => $targetValue,
                    'updated_user'      => $actionUser,
                    'updated_date'      => date("Y-m-d h:i:sa")
                );

                DB::table('target_position')->where('id', $idTE)->update($dataUpdateTP);

                if($positionCode == commonUtils::POSITION_CODE_TQ ){
                    DB::table('target_employee')
                        ->where('company_id', $companyId)
                        ->where('area_id', $areaId)
                        ->where('position_id', $positionId)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('goal_id', $goalId)
                        ->update($dataUpdateTP);
                }

                $sqlEmployee = "
                    SELECT us.*, p.position_code
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    WHERE us.admin = 0
                    AND us.company_id = ".$companyId."
                    AND us.area_id = ".$areaId."
                    AND us.inactive = 0
                    AND us.id != 0
                    AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                ";
                $objEmployeeDB  = DB::select(DB::raw($sqlEmployee));

                if(count($objEmployeeDB) == 0){
                    Session::flash('message-errors', $areaName.' không tồn tại nhân viên có chức danh '.commonUtils::POSITION_CODE_TQ.'. Vui lòng kiểm tra lại!');
                    return redirect(
                        'manageGoalPosition'
                        .'/'.$companyId
                        .'/'.$areaId
                        .'/'.$positionId
                        .'/'.$goalId
                        .'/'.$goalType
                        .'/'.$year
                        .'/'.$month

                    );
                }

                $isTQ = (commonUtils::compareTwoString($positionCode, commonUtils::POSITION_CODE_TQ) == 1) ? 1 : 0;

                $this->calKPI4Position($companyId, $areaId, $positionId, $objEmployeeDB[0]->id, $year, $month, $goalId, $isTQ, $actionUser, 0);
                $this->calKPI4Area($companyId, $areaId, $year, $month, $goalId, $actionUser, 0);
                $companyApplyDate = $this->getApplyDate4Company($companyId, $year, '');

                if($companyApplyDate == ""){
                    Session::flash('message-errors', 'Vui lòng mở khóa tỷ trọng Phòng đài trước khi cập nhật!');
                    return redirect(
                        'manageGoalPosition'
                        .'/'.$companyId
                        .'/'.$areaId
                        .'/'.$positionId
                        .'/'.$goalId
                        .'/'.$goalType
                        .'/'.$year
                        .'/'.$month

                    );
                }

                $this->calKPI4Company($companyId, $year, $companyApplyDate, $goalId, $actionUser, 0);
                $corApplyDate = $this->getApplyDate4Corporation($year);

                if($corApplyDate == ""){
                    Session::flash('message-errors', 'Vui lòng mở khóa tỷ trọng Công ty trước khi cập nhật!');
                    return redirect(
                        'manageGoalPosition'
                        .'/'.$companyId
                        .'/'.$areaId
                        .'/'.$positionId
                        .'/'.$goalId
                        .'/'.$goalType
                        .'/'.$year
                        .'/'.$month

                    );
                }

                $this->calKPI4Corporation($corApplyDate, $companyApplyDate, $goalId, $actionUser, 0);

                Session::flash('message-success', 'Cập nhật thành công!');

            }
            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /** manageGoalPosition/{cId}/{aId}/{pId}/{gId}/{gtId}/{y}/{m}*/
        return redirect(
            'manageGoalPosition'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$positionId
            .'/'.$goalId
            .'/'.$goalType
            .'/'.$year
            .'/'.$month

        );

    }
    /* *****************************************************************************************************************
     * Goal Employee
     * ****************************************************************************************************************/
    public function manageGoalEmployee($companyId, $areaId, $positionId, $userId, $goalId, $goalType, $year, $month)
    {
        $this->clearSession();
        $isParent = -1;
        //load from root data
        $companies = DB::table('company')->where('inactive', 0)->get();
        $areas = DB::table('area')->where('inactive', 0)->where('company_id', '=', $companyId)->get();
        $gOnes = DB::table('goal')->where('inactive', 0)->where('parent_id', 0)->get();
        $gTwos = DB::table('goal')->where('inactive', 0)->where('parent_id', '<>', 0)->get();
        $positions = DB::table('position')->where('inactive', 0)->get();

        $sqlUser = "SELECT * FROM users WHERE inactive = 0 AND admin = 0 and id > 5 ";

        $users = array();
        if($companyId != 0 ){
            $sqlUser .= " AND company_id = ". $companyId;
            $sqlUser .= " AND area_id = ". $areaId;
            if($positionId != 0 ){
                $sqlUser .= " AND position_id = ". $positionId;
                $users = DB::select(DB::raw($sqlUser));
            }
        }
        if($areaId != 0 ){

        }
        $data = array();
        $arrParentId = array();
        $arrParent = array();

        $select = 'SELECT
                            te.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , unit.unit_name
                            , unit.id as unit_id
                        FROM  target_employee AS te
                        LEFT JOIN goal ON goal.id = te.goal_id
                        LEFT JOIN unit ON unit.id = te.unit_id
                        WHERE te.inactive = 0
                    ';
        $selectY = 'SELECT
                            te.*
                            , goal.goal_code
                            , goal.parent_id
                            , goal.goal_name
                            , unit.unit_name
                            , unit.id as unit_id
                        FROM  target_employee AS te
                        LEFT JOIN goal ON goal.id = te.goal_id
                        LEFT JOIN unit ON unit.id = te.unit_id
                        WHERE te.inactive = 0
                    ';
        if ($companyId != 0 && $userId != 0) {
            $select .= ' AND te.company_id = ' . $companyId;

            if($goalId != 0){
                $isParent = DB::table('goal')->where('id', $goalId)->where('parent_id',0)->count();
                if($isParent == 1){
                    $select .= ' AND te.goal_id in (SELECT id FROM  goal WHERE id = '.$goalId.' or parent_id = '.$goalId.' ) ';
                }else{
                    $isParent = 0;
                    $select .= ' AND te.goal_id = ' . $goalId;
                }

            }

            if ($areaId != 0) {
                $select .= " AND te.area_id = '" . $areaId . "'";
            }

            if ($positionId != 0) {
                $select .= " AND te.position_id = '" . $positionId . "'";
            }

            if ($userId != 0) {
                $select .= " AND te.user_id = '" . $userId . "'";
            }

            if ($goalType != -1) {
                $select .= " AND te.goal_type = '" . $goalType . "'";
            }

            if ($year != 0) {
                $select .= " AND te.year = '" . $year . "'";
            }

            if ($month != 0) {
                $select .= " AND te.month = '" . $month . "'";
            }
            $data = DB::select(DB::raw($select));
            // $ige = 0;
            foreach($data as $ge){
                if($ge->parent_id != 0 && !in_array($ge->parent_id, $arrParentId)){
                    $arrParentId[] = $ge->parent_id;
                }
            }
            for ($pi = 0; $pi< count($arrParentId); $pi++){
                $arrParent[$pi]['pId'] = $arrParentId[$pi];
                $arrParent[$pi]['pName'] = $this->getGoalName($arrParentId[$pi])->goal_name;
            }
        }
        $dataYears = DB::select(DB::raw($selectY));

        return view('major.manageGoalEmployee')
            ->with('companies', $companies)
            ->with('positions', $positions)
            ->with('areas', $areas)
            ->with('users', $users)
            ->with('gOnes', $gOnes)
            ->with('gTwos', $gTwos)
            ->with('data', $data)
            ->with('isParent', $isParent)
            ->with('arrParent', $arrParent)
            ->with('selectedCompany', $companyId)
            ->with('selectedArea', $areaId)
            ->with('selectedGoal', $goalId)
            ->with('selectedGoalType', $goalType)
            ->with('dataYears', $dataYears)
            ->with('selectedYear', $year)
            ->with('selectedMonth', $month)
            ->with('selectedUser', $userId)
            ->with('selectedPosition', $positionId);
    }

    public function addGoalEmployee($companyId, $areaId, $positionId, $employeeId, $goalId, $goalType, $month ){
        $year = date('Y');
        if(
            !is_numeric($companyId)
            || !is_numeric($areaId)
            || !is_numeric($positionId)
            || !is_numeric($employeeId)
            || !is_numeric($goalType)
            || !is_numeric($month)
            || !is_numeric($goalId)
        ){
            Session::flash('message-errors', 'Dữ liệu không hợp lệ!');
            return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
        }


        $sqlCompany = "
            SELECT *
            FROM company
            WHERE inactive = 0
            AND id = ".$companyId."
        ";
        $objCompanyDB  = DB::select(DB::raw($sqlCompany));

        if(count($objCompanyDB) == 0){
            Session::flash('message-errors', 'Phòng/Đài/MBF HCM không tồn tại!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($goalType == -1){
            Session::flash('message-errors', 'Vui lòng chọn 1 loại mục tiêu!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlArea = "
                SELECT *
                FROM area
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND id = ".$areaId."
            ";

        $objAreaDB  = DB::select(DB::raw($sqlArea));

        if(count($objAreaDB) == 0){
            Session::flash('message-errors', 'Tổ/Quận/Huyện không tồn tại!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlPosition = "
                SELECT *
                FROM position
                WHERE inactive = 0
                AND id = ".$positionId."
            ";

        $objPositionDB  = DB::select(DB::raw($sqlPosition));

        if(count($objPositionDB) == 0){
            Session::flash('message-errors', 'Chức danh không tồn tại!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }



        if($goalId == 0){
            Session::flash('message-errors', 'Vui lòng chọn 1 mục tiêu!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }



        $sqlGoal = "
            SELECT g.*, u.unit_name
            FROM goal g
            LEFT JOIN unit u ON u.id = g.unit_id
            WHERE g.inactive = 0
            AND g.id = ".$goalId."
        ";

        if(
            $month      != 0
            && $year    != 0
        ){
            if(commonUtils::compareTwoString($objPositionDB[0]->position_code, commonUtils::POSITION_CODE_TQ) == 1){
                $sqlGoal .= " AND g.id NOT IN (
                        SELECT distinct goal_id
                        FROM target_position
                        WHERE inactive = 0
                        AND company_id = ".$companyId."
                        AND area_id = ".$areaId."
                        AND position_id = ".$positionId."
                        AND year = ".$year."
                        AND month = ".$month."
                    ) "
                ;

                $sqlGoal .= " AND g.id IN (
                        SELECT distinct goal_id
                        FROM important_level_position
                        WHERE inactive = 0
                        AND company_id = ".$companyId."
                        AND area_id = ".$areaId."
                        AND position_id = ".$positionId."
                        AND year = ".$year."
                        AND month = ".$month."
                        AND goal_id = ".$goalId."
                    ) "
                ;
            }else{
                $sqlGoal .= " AND g.id IN (
                        SELECT distinct goal_id
                        FROM target_position
                        WHERE inactive = 0
                        AND company_id = ".$companyId."
                        AND area_id = ".$areaId."
                        AND position_id = ".$positionId."
                        AND year = ".$year."
                        AND month = ".$month."
                        AND goal_id = ".$goalId."
                    ) "
                ;
            }

            $sqlGoal .= " AND g.id NOT IN (
                        SELECT distinct goal_id
                        FROM target_employee
                        WHERE inactive = 0
                        AND company_id = ".$companyId."
                        AND area_id = ".$areaId."
                        AND position_id = ".$positionId."
                        AND year = ".$year."
                        AND month = ".$month."
                        AND goal_id = ".$goalId."
                        AND user_id = ".$employeeId."
                    ) "
            ;


        }

        $objGoalDB  = DB::select(DB::raw($sqlGoal));

        if(count($objGoalDB) == 0){
            Session::flash('message-errors', 'Mục tiêu đang chọn đã tồn tại Kế hoạch hoặc chưa tồn tại Tỷ trọng/Kế hoạch! Vui lòng kiểm tra lại!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        if($objGoalDB[0]->parent_id == 0){
            Session::flash('message-errors', 'Vui lòng chọn mục tiêu con!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $sqlEmployee = "
                    SELECT us.*, p.position_code
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    WHERE us.admin = 0
                    AND us.company_id = ".$companyId."
                    AND us.area_id = ".$areaId."
                    AND us.position_id = ".$positionId."
                    AND us.inactive = 0
                    AND us.id != 0
                    AND us.id = ".$employeeId."
                ";
        $objEmployeeDB  = DB::select(DB::raw($sqlEmployee));

        if(count($objEmployeeDB) != 1){
            Session::flash('message-errors', 'Nhân viên không tồn tại!');
            return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
        }

        $isTQ = (commonUtils::compareTwoString($objEmployeeDB[0]->position_code, commonUtils::POSITION_CODE_TQ) == 1) ? 1 : 0;

        return view('major.addGoalEmployee')
            ->with('objCompanyDB', $objCompanyDB[0])
            ->with('objAreaDB', $objAreaDB[0])
            ->with('objPositionDB', $objPositionDB[0])
            ->with('objEmployeeDB', $objEmployeeDB[0])
            ->with('objGoalDB', $objGoalDB[0])
            ->with('month', $month)
            ->with('year', $year)
            ->with('isTQ', $isTQ)
            ;
    }

    public function saveGoalEmployee(Request $request){
        DB::beginTransaction();
        try{
            $actionUser  = Session::get('sid');
            $post        = $request->all();

            $defaultData = $post['defaultData'];
            $targetValue = (is_numeric($post['targetValue']) || (int)$post['targetValue'] != 0) ? $post['targetValue'] : 0;
            $implement   = (is_numeric($post['implement']) && $targetValue != 0) ? $post['implement'] : 0;

            $arrDefaultData = explode(',', $defaultData);

            $companyId   = $arrDefaultData[0];
            $companyName = $arrDefaultData[1];
            $areaId      = $arrDefaultData[2];
            $areaName    = $arrDefaultData[3];
            $positionId  = $arrDefaultData[4];
            $positionName= $arrDefaultData[5];
            $goalId      = $arrDefaultData[6];
            $goalType    = $arrDefaultData[7];
            $unitId      = $arrDefaultData[8];
            $year        = $arrDefaultData[9];
            $month       = $arrDefaultData[10];
            $positionCode= $arrDefaultData[11];
            $employeeId  = $arrDefaultData[12];
            $employeeName= $arrDefaultData[13];
            $isTQ        = $arrDefaultData[14];
            $formula     = $arrDefaultData[15];
            $goalType    = $arrDefaultData[16];

            if($targetValue == 0){
                Session::flash('message-errors', 'Kế hoạch phải khác 0!');
                return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
            }

            $isLocked = $this->checkLockData($year, $month, '', 3, $companyId);

            if($isLocked == 1){
                Session::flash('message-errors', 'Dữ liệu đang khóa!');
                return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
            }

            if($implement != 0){

                $isLocked = $this->checkLockData($year, $month, '', 4, $companyId);

                if($isLocked == 1){
                    Session::flash('message-errors', 'Dữ liệu đang khóa!');
                    return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
                }
            }

            /* *************************************************************************************************************
             * Check data input
             * ************************************************************************************************************/

            /* *************************************************************************************************************
             * Insert database
             * ************************************************************************************************************/
            $sqlILP = "
                SELECT *
                FROM important_level_position
                WHERE company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND position_id = ".$positionId."
                AND goal_id = ".$goalId."
                AND year = ".$year."
                AND month = ".$month."
            ";

            $objILPDB  = DB::select(DB::raw($sqlILP));

            $ilp = $objILPDB[0];

            $implementPoint = 0;
            if($isTQ == 1){
                if($formula != commonUtils::FORMULA_TU_NHAP){
                    if($implement != 0){
                        Session::flash('message-errors', 'Trưởng quận chỉ thêm mới thực hiện cho các mục tiêu tự nhập!');
                        return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
                    }
                }else{
                    $implementPoint = commonUtils::calculatorIP($targetValue, $implement, $ilp->benchmark, $goalType);
                }
            }else{
                $implementPoint = commonUtils::calculatorIP($targetValue, $implement, $ilp->benchmark, $goalType);
            }

            $targetEmployee = array(
                'company_id'        => $companyId,
                'area_id'           => $areaId,
                'position_id'       => $positionId,
                'user_id'           => $employeeId,
                'goal_id'           => $goalId,
                'month'             => $month,
                'year'              => $year,
                'important_level'   => $ilp->important_level,
                'target_value'      => $targetValue,
                'implement'         => $implement,
                'implement_point'   => $implementPoint,
                'unit_id'           => $unitId,
                'benchmark'         => $ilp->benchmark,
                'cal_benchmark'     => $ilp->cal_benchmark,
                'goal_type'         => $goalType,
                'goal_level'        => 1,
                'created_user'      => $actionUser,
                'updated_user'      => 1
            );
            DB::table('target_employee')->insert($targetEmployee);

            if($isTQ == 1){
                $implementPointPos = commonUtils::calculatorIP($targetValue, $implement, $ilp->cal_benchmark, $goalType);
                $targetPosition = array(
                    'company_id'        => $companyId,
                    'area_id'           => $areaId,
                    'position_id'       => $positionId,
                    'goal_id'           => $goalId,
                    'month'             => $month,
                    'year'              => $year,
                    'cal_benchmark'     => $ilp->cal_benchmark,
                    'important_level'   => $ilp->important_level,
                    'target_value'      => $targetValue,
                    'implement'         => $implement,
                    'implement_point'   => $implementPointPos,
                    'unit_id'           => $unitId,
                    'benchmark'         => $ilp->benchmark,
                    'goal_type'         => $goalType,
                    'goal_level'        => 1,
                    'created_user'      => $actionUser,
                    'updated_user'      => 1
                );
                DB::table('target_position')->insert($targetPosition);

            }


            $dataLog = array(
                'functionName' => 'Kế hoạch/Thực hiện cho Nhân viên (addGoalEmployee)',
                'action'       => commonUtils::ACTION_INSERT,
                'url'          => 'addGoalEmployee',
                'newValue'     => 'Kế hoạch: '.$targetValue.', Thực hiện: '.$implement,
                'oldValue'     => '',
                'createdUser'  => $actionUser
            );
            $this->writeLog($dataLog);

            /**---------------------------------------------------------------------------------------------------------
             * Calculator KPI
             * -------------------------------------------------------------------------------------------------------*/
            if($implement != 0){

                $sqlEmployee = "
                    SELECT us.*, p.position_code
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    WHERE us.admin = 0
                    AND us.company_id = ".$companyId."
                    AND us.area_id = ".$areaId."
                    AND us.inactive = 0
                    AND us.id != 0
                    AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                ";
                $objEmployeeDB  = DB::select(DB::raw($sqlEmployee));

                if(count($objEmployeeDB) == 0){
                    Session::flash('message-errors', $areaName.' không tồn tại nhân viên có chức danh '.commonUtils::POSITION_CODE_TQ.'. Vui lòng kiểm tra lại!');
                    return redirect('manageGoalEmployee/'.$companyId.'/'.$areaId.'/'.$positionId.'/'.$employeeId.'/'.$goalId.'/'.$goalType.'/'.$year.'/'.$month);
                }

                $this->calKPI4Position($companyId, $areaId, $positionId, $objEmployeeDB[0]->id, $year, $month, $goalId, $isTQ, $actionUser, 0);
                $this->calKPI4Area($companyId, $areaId, $year, $month, $goalId, $actionUser, 0);
                $companyApplyDate = $this->getApplyDate4Company($companyId, $year, '');
                if($companyApplyDate == ""){
                    Session::flash('message-errors', 'Vui lòng mở khóa tỷ trọng phòng đài trước khi thêm mới!');
                    return redirect(
                        'manageGoalEmployee'
                        .'/'.$companyId
                        .'/'.$areaId
                        .'/'.$positionId
                        .'/'.$employeeId
                        .'/'.$goalId
                        .'/'.$goalType
                        .'/'.$year
                        .'/'.$month
                    );
                }
                $this->calKPI4Company($companyId, $year, $companyApplyDate, $goalId, $actionUser, 0);
                $corApplyDate = $this->getApplyDate4Corporation($year);
                if($corApplyDate == ""){
                    Session::flash('message-errors', 'Vui lòng mở khóa tỷ trọng Công ty trước khi thêm mới!');
                    return redirect(
                        'manageGoalEmployee'
                        .'/'.$companyId
                        .'/'.$areaId
                        .'/'.$positionId
                        .'/'.$employeeId
                        .'/'.$goalId
                        .'/'.$goalType
                        .'/'.$year
                        .'/'.$month
                    );
                }
                $this->calKPI4Corporation($corApplyDate, $companyApplyDate, $goalId, $actionUser, 0);
            }
            /* -------------------------------------------------------------------------------------------------------*/

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }
        /* *************************************************************************************************************
         * redirect home page
         * ************************************************************************************************************/
        Session::flash('message-success', 'Đã thêm thành công 1 dòng Kế hoạch/ Thực hiện tháng '.$month.'/'.$year.' cho Nhân viên '.$employeeName.' có Chức danh '.$positionName.' thuộc Tổ/Quận/Huyện: '.$areaName.' & Phòng/Đài/MBF HCM: '.$companyName.'!');
        return redirect(
            'manageGoalEmployee'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$positionId
            .'/'.$employeeId
            .'/'.$goalId
            .'/'.$goalType
            .'/'.$year
            .'/'.$month
        );

    }

    public function updateGoalEmployee($id){

        if(!is_numeric($id)){
            Session::flash('message-errors', 'Mã Kế hoạch/ Thực hiện không hợp lệ!');
            return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
        }

        $sqlTargetEmployee = "
            SELECT ta.*, c.company_name, us.name, g.goal_name, p.position_name, a.area_name, u.unit_name, g.formula, p.position_code
            FROM target_employee ta
            LEFT JOIN goal g ON g.id = ta.goal_id
            LEFT JOIN `position` p ON p.id = ta.position_id
            LEFT JOIN company c ON c.id = ta.company_id
            LEFT JOIN area a ON a.id = ta.area_id
            LEFT JOIN unit u ON u.id = ta.unit_id
            LEFT JOIN users us ON us.id = ta.user_id
            WHERE ta.inactive = 0
            AND ta.id = ".$id."
        ";
        $targetEmployeeDB  = DB::select(DB::raw($sqlTargetEmployee));

        if(count($targetEmployeeDB) == 0){
            Session::flash('message-errors', 'Mã Kế hoạch/ Thực hiện không tồn tại!');
            return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
        }

        return view('major.updateGoalEmployee')
            ->with('targetEmployeeDB', $targetEmployeeDB[0])
            ;
    }

    public function editGoalEmployee(Request $request){
        DB::beginTransaction();
        try {
        $currentUser = Session::get('sid');

        $post = $request->all();

        $defaultData = $post['defaultData'];
        $targetValue = $post['txtTargetValue'];
        $implement   = $post['txtImplement'];

        $arrDefaultData = explode(',', $defaultData);

        $idTE           = $arrDefaultData[0];
        $companyId      = $arrDefaultData[1];
        $areaId         = $arrDefaultData[2];
        $positionId     = $arrDefaultData[3];
        $employeeId     = $arrDefaultData[4];
        $month          = $arrDefaultData[5];
        $year           = $arrDefaultData[6];
        $goalType       = $arrDefaultData[7];
        $goalId         = $arrDefaultData[8];
        $unitId         = $arrDefaultData[9];
        $importantLevel = $arrDefaultData[10];
        $targetValueOld = $arrDefaultData[11];
        $implementOld   = $arrDefaultData[12];
        $benchmark      = $arrDefaultData[13];
        $positionCode   = $arrDefaultData[14];
        $formula        = $arrDefaultData[15];
        $calBenchmark   = $arrDefaultData[16];
        $areaName       = $arrDefaultData[17];

        if(
            $targetValueOld != $targetValue
            || $implementOld != $implement
        ){

            if($targetValueOld != $targetValue){
                $isLocked = $this->checkLockData($year, $month, '', 3, $companyId);

                if($isLocked == 1){
                    Session::flash('message-errors', 'Dữ liệu đang khóa!');
                    return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
                }
            }

            if($implementOld != $implement){
                $isLocked = $this->checkLockData($year, $month, '', 4, $companyId);

                if($isLocked == 1){
                    Session::flash('message-errors', 'Dữ liệu đang khóa!');
                    return redirect('manageGoalEmployee/0/0/0/0/0/-1/0/0');
                }
            }

            /* *****************************************************************************************************
             * Cập nhật thực hiện/Kế hoạch cho nhân viên
             * ****************************************************************************************************/

            if($positionCode == commonUtils::POSITION_CODE_TQ ){
                if($formula == commonUtils::FORMULA_TU_NHAP){

                    $implementPoint = commonUtils::calculatorIP($targetValue, $implement, $benchmark, $goalType);

                    $dataUpdateTE = array(
                        'target_value' 	    => $targetValue,
                        'implement' 	    => $implement,
                        'implement_point' 	=> $implementPoint,
                        'updated_user'      => $currentUser,
                        'updated_date'      =>date("Y-m-d h:i:sa")
                    );

                    DB::table('target_employee')->where('id', $idTE)->update($dataUpdateTE);

                    $implementPointPos = commonUtils::calculatorIP($targetValue, $implement, $calBenchmark, $goalType);
                    $dataUpdateTP = array(
                        'target_value' 	    => $targetValue,
                        'implement' 	    => $implement,
                        'implement_point' 	=> $implementPointPos,
                        'updated_user'      => $currentUser,
                        'updated_date'      =>date("Y-m-d h:i:sa")
                    );

                    DB::table('target_position')
                        ->where('company_id', $companyId)
                        ->where('area_id', $areaId)
                        ->where('position_id', $positionId)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('goal_id', $goalId)
                        ->update($dataUpdateTP);

                }else{

                    $dataUpdateTE = array(
                        'target_value' 	    => $targetValue,
                        'updated_user'      => $currentUser,
                        'updated_date'      =>date("Y-m-d h:i:sa")
                    );

                    DB::table('target_employee')->where('id', $idTE)->update($dataUpdateTE);

                    $dataUpdateTP = array(
                        'target_value' 	    => $targetValue,
                        'updated_user'      => $currentUser,
                        'updated_date'      =>date("Y-m-d h:i:sa")
                    );

                    DB::table('target_position')
                        ->where('company_id', $companyId)
                        ->where('area_id', $areaId)
                        ->where('position_id', $positionId)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('goal_id', $goalId)
                        ->update($dataUpdateTP);
                }

            }else{

                $implementPoint = commonUtils::calculatorIP($targetValue, $implement, $benchmark, $goalType);

                $dataUpdateTE = array(
                    'target_value' 	    => $targetValue,
                    'implement' 	    => $implement,
                    'implement_point' 	=> $implementPoint,
                    'updated_user'      => $currentUser,
                    'updated_date'      =>date("Y-m-d h:i:sa")
                );

                DB::table('target_employee')->where('id', $idTE)->update($dataUpdateTE);
            }
            /* *****************************************************************************************************
             * Cập nhật thực hiện/Kế hoạch cho chức danh
             * Tính điểm thực hiện cho chức danh
             * Tính điểm thực hiện của quận
             * Tính điểm thực hiện của phòng
             * Tính điểm thực hiện của Cty
             * ****************************************************************************************************/
            $isTQ = (commonUtils::compareTwoString($positionCode, commonUtils::POSITION_CODE_TQ) == 1) ? 1 : 0;

            $sqlEmployee = "
                    SELECT us.*, p.position_code
                    FROM users us
                    LEFT JOIN `position` p ON p.id = us.position_id
                    WHERE us.admin = 0
                    AND us.company_id = ".$companyId."
                    AND us.area_id = ".$areaId."
                    AND us.inactive = 0
                    AND us.id != 0
                    AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
                ";
            $objEmployeeDB  = DB::select(DB::raw($sqlEmployee));

            if(count($objEmployeeDB) == 0){
                Session::flash('message-errors', $areaName.' không tồn tại nhân viên có chức danh '.commonUtils::POSITION_CODE_TQ.'. Vui lòng kiểm tra lại!');
                return redirect(
                    'manageGoalEmployee'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$positionId
                    .'/'.$employeeId
                    .'/'.$goalId
                    .'/'.$goalType
                    .'/'.$year
                    .'/'.$month

                );
            }



            $this->calKPI4Position($companyId, $areaId, $positionId, $objEmployeeDB[0]->id, $year, $month, $goalId, $isTQ, $currentUser, 0);
            $this->calKPI4Area($companyId, $areaId, $year, $month, $goalId, $currentUser, 0);
            $companyApplyDate = $this->getApplyDate4Company($companyId, $year, '');
            if($companyApplyDate == "" ){
                Session::flash('message-errors', 'Vui lòng mở khóa tỷ trọng phòng đài trước khi cập nhật!');
                return redirect(
                    'manageGoalEmployee'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$positionId
                    .'/'.$employeeId
                    .'/'.$goalId
                    .'/'.$goalType
                    .'/'.$year
                    .'/'.$month

                );
            }
            $this->calKPI4Company($companyId, $year, $companyApplyDate, $goalId, $currentUser, 0);
            $corApplyDate = $this->getApplyDate4Corporation($year);
            if($corApplyDate == "" ){
                Session::flash('message-errors', 'Vui lòng mở khóa tỷ trọng công ty trước khi cập nhật!');
                return redirect(
                    'manageGoalEmployee'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$positionId
                    .'/'.$employeeId
                    .'/'.$goalId
                    .'/'.$goalType
                    .'/'.$year
                    .'/'.$month

                );
            }
            $this->calKPI4Corporation($corApplyDate, $companyApplyDate, $goalId, $currentUser, 0);

            Session::flash('message-success', 'Cập nhật thành công!');
        }
        DB::commit();
    }catch (Exception $e) {
        DB::rollback();
    }
        /** {cId}/{aId}/{pId}/{uId}/{gId}/{gtId}/{y}/{m} */
        return redirect(
            'manageGoalEmployee'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$positionId
            .'/'.$employeeId
            .'/'.$goalId
            .'/'.$goalType
            .'/'.$year
            .'/'.$month

        );
    }
    /* *****************************************************************************************************************
     * Manage Import
     * ****************************************************************************************************************/
    public function importGoal()
    {
        $this->clearSession();
        $accessLevel = DB::table('access_level')->where('id', Session::get('saccess_level'))->first();
        $level = !empty($accessLevel) ? $accessLevel->level : 0;
        if ($level > 3) {
            return redirect('home');
        }
        return view('major.importGoal');
    }
    /* *****************************************************************************************************************
     * Set permission
     * ****************************************************************************************************************/
    public function manageUserPermission()
    {
        $this->clearSession();
        $user = DB::table('users')->where('inactive', 0)->get();
        return view('permission.manageUserPermission')->with('user', $user);
    }

    public function managePermissionFunction()
    {
        $this->clearSession();
        return view('permission.managePermissionFunction');
    }

    public function viewDetailPermission()
    {
        $this->clearSession();
        return view('permission.viewDetailPermission');
    }
    /* *****************************************************************************************************************
     * Write log
     * ****************************************************************************************************************/
    private function writeLog($dataLog){

        $functionName   = $dataLog['functionName'];
        $action         = $dataLog['action'];
        $url            = $dataLog['url'];
        $newValue       = $dataLog['newValue'];
        $oldValue       = $dataLog['oldValue'];
        $createdUser    = $dataLog['createdUser'];

        $data = array(
            'function_name' => $functionName,
            'action'        => $action,
            'url'           => $url,
            'id_row'        => 0,
            'old_value'     => $oldValue,
            'new_value'     => $newValue,
            'created_user'  => $createdUser
        );

        #Write log override here
        DB::table('kpi_log')->insert($data);

    }
    /* *****************************************************************************************************************
     * Function calculator benchmark & KPI
     * ****************************************************************************************************************/
    private function getGoalName($goalId){
        return DB::table('goal')->select('goal_name')->where('inactive', 0)->where('id', $goalId)->first();
    }

    private function getApplyDate4Company($companyId, $year, $applyDate){
        $sql = "
            SELECT apply_date
            FROM important_level_company
            WHERE inactive = 0
            AND year(apply_date) = ".$year."
        ";

        if($companyId != ""){
            $sql .= " AND `company_id` = ".$companyId." ";
        }

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

    // cập nhật cho chức danh trưởng quận và chức danh đã bị tác động với mục tiêu đó.
    private function calKPI4Position($companyId, $areaId, $positionId, $idTQ, $year, $month, $goalId, $isTQ, $actionUser, $type){

        if($type == 0){
            /*calculator for position different TQ*/
            if($isTQ == 0){
                $sqlPerformEmployee = "
                SELECT te.*, g.formula
                FROM target_employee te
                LEFT JOIN goal g ON g.id = te.goal_id
                WHERE te.implement != 0
                AND te.inactive = 0
                AND te.user_id != ".$idTQ."
                AND te.company_id = ".$companyId."
                AND te.area_id = ".$areaId."
                AND te.position_id = ".$positionId."
                AND te.year = ".$year."
                AND te.month = ".$month."
                AND te.goal_id = ".$goalId."
            ";

                $objPerformEmployeeDB  = DB::select(DB::raw($sqlPerformEmployee));

                $sqlTargetPosition = "
                SELECT tp.*
                FROM target_position tp
                LEFT JOIN goal g ON g.id = tp.goal_id
                WHERE tp.inactive = 0
                AND tp.company_id = ".$companyId."
                AND tp.area_id = ".$areaId."
                AND tp.position_id = ".$positionId."
                AND tp.year = ".$year."
                AND tp.month = ".$month."
                AND tp.goal_id = ".$goalId."
            ";

                $objTargetPositionDB  = DB::select(DB::raw($sqlTargetPosition));

                $totalImplement = 0;

                foreach($objPerformEmployeeDB as $performEmployee){
                    $totalImplement += $performEmployee->implement;

                    if(
                        $performEmployee->formula == commonUtils::FORMULA_TRUNG_BINH_CONG
                        || $performEmployee->formula == commonUtils::FORMULA_LAY1SO
                    ){
                        if($totalImplement != 0){
                            break;
                        }
                    }

                }

                $implementPoint = commonUtils::calculatorIP(
                    $objTargetPositionDB[0]->target_value
                    , $totalImplement
                    , $objTargetPositionDB[0]->cal_benchmark
                    , $objTargetPositionDB[0]->goal_type
                );

                $duPerformPosition = array (
                    'implement'         => $totalImplement
                , 'implement_point' => $implementPoint
                , 'updated_user'     => $actionUser
                );

                DB::table('target_position')
                    ->where('company_id', $companyId)
                    ->where('area_id', $areaId)
                    ->where('position_id', $positionId)
                    ->where('goal_id', $goalId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('inactive', 0)
                    ->update($duPerformPosition);
            }

            /*calculator for position and employee TQ*/
            $sqlPerformPosition = "
            SELECT tp.*, p.position_code
            FROM target_position tp
            LEFT JOIN position p ON p.id = tp.position_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND tp.area_id = ".$areaId."
            AND p.position_code != '".commonUtils::POSITION_CODE_TQ."'
            AND tp.year = ".$year."
            AND tp.month = ".$month."
            AND tp.goal_id = ".$goalId."
        ";
            $objPerformPositionDB  = DB::select(DB::raw($sqlPerformPosition));

            $sqlTarGetPosTQ = "
            SELECT tp.*, g.formula
            FROM target_position tp
            LEFT JOIN position p ON p.id = tp.position_id
            LEFT JOIN goal g ON g.id = tp.goal_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND tp.area_id = ".$areaId."
            AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
            AND tp.year = ".$year."
            AND tp.month = ".$month."
            AND tp.goal_id = ".$goalId."
        ";
            $objTarGetPosTQDB  = DB::select(DB::raw($sqlTarGetPosTQ));

            $sqlTarGetEmpTQ = "
            SELECT te.*
            FROM target_employee te
            LEFT JOIN position p ON p.id = te.position_id
            WHERE te.inactive = 0
            AND te.company_id = ".$companyId."
            AND te.area_id = ".$areaId."
            AND te.user_id = ".$idTQ."
            AND te.year = ".$year."
            AND te.month = ".$month."
            AND te.goal_id = ".$goalId."
        ";

            $objTarGetEmpTQDB  = DB::select(DB::raw($sqlTarGetEmpTQ));

            $formula    = $objTarGetPosTQDB[0]->formula;
            $pImplement = 0;

            switch ($formula) {

                case CommonUtils::FORMULA_LAY1SO:
                case CommonUtils::FORMULA_TRUNG_BINH_CONG:
                    /**
                     * Lấy bất kỳ  thực hiện của một chức danh có thực hiện khác 0 và thuộc mục tiêu đang xét
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        $pImplement = $rsPerformPosition->implement;
                        if($pImplement != 0){
                            break;
                        }
                    }

                    break;
                case CommonUtils::FORMULA_TONG_NVBH:
                    /**
                     * Lấy thực hiện của chức danh nhân viên bán hàng
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_NVBH
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
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_KAM_AM
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
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                            || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_CVKHCN_CHT:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                            || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CHT
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_GDV:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_GDV
                            || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_GDV_K
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }
                    }
                    break;

                case CommonUtils::FORMULA_TONG_CVKHCN:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }
                    }
                    break;

                case CommonUtils::FORMULA_TONG_CVKHDN:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }
                    }
                    break;

                case CommonUtils::FORMULA_TONG_CVKHDN_CHT:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                            || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CHT
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }
                    }
                    break;
                case CommonUtils::FORMULA_TONG_CVKHCN_CVKHDN_CHT:
                    /**
                     * Calculate target_value here
                     */
                    foreach($objPerformPositionDB as $rsPerformPosition){
                        if(
                            $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                            || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CHT
                            || $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                        ){
                            $pImplement += $rsPerformPosition->implement;
                        }
                    }
                    break;


                case CommonUtils::FORMULA_TU_NHAP:
                    $pImplement = $objTarGetPosTQDB[0]->implement;
                    break;
            }

            $pImplementPoint = commonUtils::calculatorIP($objTarGetPosTQDB[0]->target_value, $pImplement, $objTarGetPosTQDB[0]->cal_benchmark, $objTarGetPosTQDB[0]->goal_type);
            $pPerformPosition = array(
                'implement'         => $pImplement,
                'implement_point'   => $pImplementPoint,
                'updated_user'      => $actionUser
            );

            DB::table('target_position')->where('id', $objTarGetPosTQDB[0]->id)->update($pPerformPosition);

            /*if($formula != commonUtils::FORMULA_TU_NHAP){
                DB::table('target_position')->where('id', $objTarGetPosTQDB[0]->id)->update($pPerformPosition);
            }*/

            $eImplementPoint = commonUtils::calculatorIP($objTarGetEmpTQDB[0]->target_value, $pImplement, $objTarGetEmpTQDB[0]->benchmark, $objTarGetEmpTQDB[0]->goal_type);
            $ePerformEmployee = array(
                'implement'         => $pImplement,
                'implement_point'   => $eImplementPoint,
                'updated_user'      => $actionUser
            );

            DB::table('target_employee')->where('id', $objTarGetEmpTQDB[0]->id)->update($ePerformEmployee);

        }elseif($type == 1){
            if($isTQ == 0){
                $sqlPerformEmployee = "
                    SELECT te.*, g.formula, p.position_code
                    FROM target_employee te
                    LEFT JOIN goal g ON g.id = te.goal_id
                    LEFT JOIN `position` p ON p.id = te.position_id
                    WHERE te.implement != 0
                    AND te.inactive = 0
                    AND te.user_id != ".$idTQ."
                    AND te.company_id = ".$companyId."
                    AND te.year = ".$year."
                ";

                $sqlTargetPosition = "
                    SELECT tp.*, p.position_code
                    FROM target_position tp
                    LEFT JOIN goal g ON g.id = tp.goal_id
                    LEFT JOIN `position` p ON p.id = tp.position_id
                    WHERE tp.inactive = 0
                    AND tp.company_id = ".$companyId."
                    AND p.position_code != '".commonUtils::POSITION_CODE_TQ."'
                    AND tp.year = ".$year."
                ";

                if($areaId != ""){
                    $sqlPerformEmployee .= "  AND te.area_id = ".$areaId." ";
                    $sqlTargetPosition  .= "  AND tp.area_id = ".$areaId." ";
                }

                if($positionId != ""){
                    $sqlPerformEmployee .= "  AND te.position_id = ".$positionId." ";
                    $sqlTargetPosition  .= "  AND tp.position_id = ".$positionId." ";
                }

                if($month != ""){
                    $sqlPerformEmployee .= "  AND te.month = ".$month." ";
                    $sqlTargetPosition  .= "  AND tp.month = ".$month." ";
                }

                if($goalId != ""){
                    $sqlPerformEmployee .= "  AND te.goal_id = ".$goalId." ";
                    $sqlTargetPosition  .= "  AND tp.goal_id = ".$goalId." ";
                }

                $objPerformEmployeeDB  = DB::select(DB::raw($sqlPerformEmployee));
                $objTargetPositionDB   = DB::select(DB::raw($sqlTargetPosition));

                $arrMonth = commonUtils::defaultMonth();

                foreach($arrMonth as $m){

                    foreach($objTargetPositionDB as $targetPosition){

                        if($targetPosition->month == $m){

                            $totalImplement = 0;
                            foreach($objPerformEmployeeDB as $performEmployee){

                                if(
                                    $performEmployee->month          == $m
                                    && $performEmployee->area_id     == $targetPosition->area_id
                                    && $performEmployee->position_id == $targetPosition->position_id
                                    && $performEmployee->goal_id     == $targetPosition->goal_id
                                ){
                                    $totalImplement += $performEmployee->implement;

                                    if(
                                        $performEmployee->formula == commonUtils::FORMULA_TRUNG_BINH_CONG
                                        || $performEmployee->formula == commonUtils::FORMULA_LAY1SO
                                    ){
                                        if($totalImplement != 0){
                                            break;
                                        }
                                    }
                                }

                            }
                            $implementPoint = commonUtils::calculatorIP(
                                $targetPosition->target_value
                                , $totalImplement
                                , $targetPosition->cal_benchmark
                                , $targetPosition->goal_type
                            );

                            $duPerformPosition = array (
                                'implement'         => $totalImplement
                            , 'implement_point' => $implementPoint
                            , 'updated_user'     => $actionUser
                            );

                            DB::table('target_position')
                                ->where('id', $targetPosition->id)
                                ->update($duPerformPosition);

                        }
                    }

                }

            }

            /*calculator for position and employee TQ*/
            $sqlPerformPosition = "
            SELECT tp.*, p.position_code
            FROM target_position tp
            LEFT JOIN `position` p ON p.id = tp.position_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND p.position_code != '".commonUtils::POSITION_CODE_TQ."'
            AND tp.year = ".$year."
        ";


            $sqlTarGetPosTQ = "
            SELECT tp.*, g.formula
            FROM target_position tp
            LEFT JOIN `position` p ON p.id = tp.position_id
            LEFT JOIN goal g ON g.id = tp.goal_id
            WHERE tp.inactive = 0
            AND tp.company_id = ".$companyId."
            AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
            AND tp.year = ".$year."
        ";


            $sqlTarGetEmpTQ = "
            SELECT te.*
            FROM target_employee te
            LEFT JOIN `position` p ON p.id = te.position_id
            WHERE te.inactive = 0
            AND te.company_id = ".$companyId."
            AND p.position_code = '".commonUtils::POSITION_CODE_TQ."'
            AND te.user_id = ".$idTQ."
            AND te.year = ".$year."
        ";

            if($areaId != ""){
                $sqlTarGetPosTQ     .= "  AND tp.area_id = ".$areaId." ";
                $sqlPerformPosition .= "  AND tp.area_id = ".$areaId." ";
                $sqlTarGetEmpTQ     .= "  AND te.area_id = ".$areaId." ";
            }



            if($month != ""){
                $sqlTarGetPosTQ     .= "  AND tp.month = ".$month." ";
                $sqlPerformPosition .= "  AND tp.month = ".$month." ";
                $sqlTarGetEmpTQ     .= "  AND te.month = ".$month." ";

            }

            if($goalId != ""){

                $sqlTarGetPosTQ     .= "  AND tp.goal_id = ".$goalId." ";
                $sqlPerformPosition .= "  AND tp.goal_id = ".$goalId." ";
                $sqlTarGetEmpTQ     .= "  AND te.goal_id = ".$goalId." ";
            }

            $objPerformPositionDB   = DB::select(DB::raw($sqlPerformPosition));
            $objTarGetPosTQDB       = DB::select(DB::raw($sqlTarGetPosTQ));
            $objTarGetEmpTQDB       = DB::select(DB::raw($sqlTarGetEmpTQ));

            foreach($objTarGetPosTQDB as $targetPosTQ){

                $formula    = $targetPosTQ->formula;
                $pImplement = 0;

                switch ($formula) {

                    case CommonUtils::FORMULA_TRUNG_BINH_CONG:
                        $pImplementTemp = 0;
                        $count = 0;
                        foreach($objPerformPositionDB as $rsPerformPosition){

                            if(
                                $rsPerformPosition->goal_id     == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id  == $targetPosTQ->area_id
                                && $rsPerformPosition->month    == $targetPosTQ->month
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
                        foreach($objPerformPositionDB as $rsPerformPosition){

                            if(
                                $rsPerformPosition->goal_id     == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id  == $targetPosTQ->area_id
                                && $rsPerformPosition->month    == $targetPosTQ->month
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
                        foreach($objPerformPositionDB as $rsPerformPosition){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_NVBH
                                && $rsPerformPosition->goal_id    == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id    == $targetPosTQ->area_id
                                && $rsPerformPosition->month      == $targetPosTQ->month
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
                        foreach($objPerformPositionDB as $rsPerformPosition){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_KAM_AM
                                && $rsPerformPosition->goal_id    == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id    == $targetPosTQ->area_id
                                && $rsPerformPosition->month      == $targetPosTQ->month
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
                        foreach($objPerformPositionDB as $rsPerformPosition){

                            if(
                                 $rsPerformPosition->goal_id       == $targetPosTQ->goal_id
                                 && $rsPerformPosition->area_id    == $targetPosTQ->area_id
                                 && $rsPerformPosition->month      == $targetPosTQ->month
                            ){
                                if(
                                    $rsPerformPosition->position_code    == commonUtils::POSITION_CODE_CV_KHCN
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
                        foreach($objPerformPositionDB as $rsPerformPosition){

                            if(
                                $rsPerformPosition->goal_id       == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id    == $targetPosTQ->area_id
                                && $rsPerformPosition->month      == $targetPosTQ->month
                            ){
                                if(
                                    $rsPerformPosition->position_code    == commonUtils::POSITION_CODE_CV_KHCN
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
                        foreach($objPerformPositionDB as $rsPerformPosition){

                            if(
                                $rsPerformPosition->goal_id       == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id    == $targetPosTQ->area_id
                                && $rsPerformPosition->month      == $targetPosTQ->month
                            ){
                                if(
                                    $rsPerformPosition->position_code    == commonUtils::POSITION_CODE_GDV
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
                        foreach($objPerformPositionDB as $rsPerformPosition){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHCN
                                && $rsPerformPosition->goal_id    == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id    == $targetPosTQ->area_id
                                && $rsPerformPosition->month      == $targetPosTQ->month
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }
                        }
                        break;

                    case CommonUtils::FORMULA_TONG_CVKHDN:
                        /**
                         * Calculate target_value here
                         */
                        foreach($objPerformPositionDB as $rsPerformPosition){
                            if(
                                $rsPerformPosition->position_code == commonUtils::POSITION_CODE_CV_KHDN
                                && $rsPerformPosition->goal_id    == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id    == $targetPosTQ->area_id
                                && $rsPerformPosition->month      == $targetPosTQ->month
                            ){
                                $pImplement += $rsPerformPosition->implement;
                            }
                        }
                        break;

                    case CommonUtils::FORMULA_TONG_CVKHDN_CHT:
                        /**
                         * Calculate target_value here
                         */
                        foreach($objPerformPositionDB as $rsPerformPosition){

                            if(
                                 $rsPerformPosition->goal_id    == $targetPosTQ->goal_id
                                 && $rsPerformPosition->area_id == $targetPosTQ->area_id
                                 && $rsPerformPosition->month   == $targetPosTQ->month
                            ){
                                if(
                                    $rsPerformPosition->position_code    == commonUtils::POSITION_CODE_CV_KHDN
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
                        foreach($objPerformPositionDB as $rsPerformPosition){

                            if(
                                $rsPerformPosition->goal_id    == $targetPosTQ->goal_id
                                && $rsPerformPosition->area_id == $targetPosTQ->area_id
                                && $rsPerformPosition->month   == $targetPosTQ->month
                            ){
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
                    case CommonUtils::FORMULA_TU_NHAP:
                        $pImplement = $targetPosTQ->implement;
                        break;
                }

                $pImplementPoint = commonUtils::calculatorIP($targetPosTQ->target_value, $pImplement, $targetPosTQ->cal_benchmark, $targetPosTQ->goal_type);

                $pPerformPosition = array(
                    'implement'         => $pImplement,
                    'implement_point'   => $pImplementPoint,
                    'updated_user'      => $actionUser
                );

                DB::table('target_position')->where('id', $targetPosTQ->id)->update($pPerformPosition);

                $eImplementPoint = commonUtils::calculatorIP($targetPosTQ->target_value, $pImplement, $targetPosTQ->benchmark, $targetPosTQ->goal_type);
                $ePerformEmployee = array(
                    'implement'         => $pImplement,
                    'implement_point'   => $eImplementPoint,
                    'updated_user'      => $actionUser
                );

                DB::table('target_employee')
                    ->where('company_id', $targetPosTQ->company_id)
                    ->where('area_id', $targetPosTQ->area_id)
                    ->where('position_id', $targetPosTQ->position_id)
                    ->where('user_id', $idTQ)
                    ->where('goal_id', $targetPosTQ->goal_id)
                    ->where('year', $targetPosTQ->year)
                    ->where('month', $targetPosTQ->month)
                    ->update($ePerformEmployee);

            }

        }

    }

    // cập nhật cho 1 khu vực với 1 mục tiêu.
    private function calKPI4Area($companyId, $areaId, $year, $month, $goalId, $actionUser, $type){

        if($type == 0){
            $sqlTargetArea = "
            SELECT *
                FROM target_area
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND year = ".$year."
                AND month = ".$month."
                AND goal_id = ".$goalId."
            ";
            $objTargetAreaDB = DB::select(DB::raw($sqlTargetArea));

            $sqlPerformPosition = "
            SELECT *
                FROM target_position
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND `year` = ".$year."
                AND `month` = ".$month."
                AND goal_id = ".$goalId."
            ";
            $objPerformPositionDB = DB::select(DB::raw($sqlPerformPosition));

            $implementPoint = 0;
            foreach($objPerformPositionDB as $performPosition){
                $implementPoint += $performPosition->implement_point;
            }
            if($implementPoint != 0){
                $dataTAUpdate = array(
                    'implement'                 => 0,
                    'implement_point'           => $implementPoint,
                    'real_percent'              => ($objTargetAreaDB[0]->benchmark != 0) ? $implementPoint / $objTargetAreaDB[0]->benchmark: 0,
                    'cal_implement_point'       => 0,
                    'updated_user'              => $actionUser
                );
                DB::table('target_area')->where('id', $objTargetAreaDB[0]->id)->update($dataTAUpdate);
            }

            $sqlPerformArea = "
            SELECT *
                FROM target_area
                WHERE inactive = 0
                AND year = ".$year."
                AND company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND goal_id = ".$goalId."
            ";
            $objPerformAreaDB = DB::select(DB::raw($sqlPerformArea));

            $totalBenchmark      = 0;
            $totalImplementPoint = 0;

            $paCalBenchmark = 0;
            foreach($objPerformAreaDB as $pa){
                $totalBenchmark      += $pa->benchmark;
                $totalImplementPoint += $pa->implement_point;
                $paCalBenchmark      = $pa->cal_benchmark;
            }

            $rRealPercent       = ($totalBenchmark != 0) ? $totalImplementPoint / $totalBenchmark : 0;
            $calImplementPoint  = $rRealPercent * $paCalBenchmark;

            $dataPAUpdate = array(
                'cal_implement_point'  => $calImplementPoint
            );

            DB::table('target_area')
                ->where('company_id', $companyId)
                ->where('area_id', $areaId)
                ->where('year', $year)
                ->where('goal_id', $goalId)
                ->update($dataPAUpdate);
        }elseif($type == 1){

            $sqlTargetArea = "
                SELECT *
                    FROM target_area
                    WHERE inactive = 0
                    AND company_id = ".$companyId."
                    AND area_id = ".$areaId."
                    AND `year` = ".$year."
                ";

            $sqlPerformPosition = "
                SELECT *
                    FROM target_position
                    WHERE inactive = 0
                    AND company_id = ".$companyId."
                    AND area_id = ".$areaId."
                    AND `year` = ".$year."
                ";

            if($goalId != ""){
                $sqlPerformPosition .= " AND goal_id = ".$goalId." ";
                $sqlTargetArea .= " AND goal_id = ".$goalId." ";
            }

            if($month != ""){
                $sqlPerformPosition .= " AND `month` = ".$month." ";
                $sqlTargetArea .= " AND `month` = ".$month." ";
            }

            $objTargetAreaDB = DB::select(DB::raw($sqlTargetArea));

            $objPerformPositionDB = DB::select(DB::raw($sqlPerformPosition));

            $arrGoalId = array();

            foreach($objTargetAreaDB as $targetArea){

                if(!in_array($targetArea->goal_id, $arrGoalId)){
                    $arrGoalId[] = $targetArea->goal_id;
                }

                $implementPoint = 0;
                foreach($objPerformPositionDB as $performPosition){

                    if(
                        $performPosition->goal_id == $targetArea->goal_id
                        && $performPosition->month == $targetArea->month
                    ){
                        $implementPoint += $performPosition->implement_point;
                    }

                }

                $dataTAUpdate = array(
                    'implement'                 => 0,
                    'implement_point'           => $implementPoint,
                    'real_percent'              => ($targetArea->benchmark != 0) ? $implementPoint / $targetArea->benchmark: 0,
                    'cal_implement_point'       => 0,
                    'updated_user'              => $actionUser
                );
                DB::table('target_area')->where('id', $targetArea->id)->update($dataTAUpdate);


            }


            $sqlPerformArea = "
                SELECT *
                    FROM target_area
                    WHERE inactive = 0
                    AND `year` = ".$year."
                    AND company_id = ".$companyId."
                    AND area_id = ".$areaId."
                ";

            if($goalId != ""){
                $sqlPerformArea .= " AND goal_id = ".$goalId." ";
            }

            $objPerformAreaDB = DB::select(DB::raw($sqlPerformArea));



            foreach($arrGoalId as $pgGoalId){

                $totalBenchmark      = 0;
                $totalImplementPoint = 0;
                $paCalBenchmark = 0;

                foreach($objPerformAreaDB as $performArea){
                    if($performArea->goal_id == $pgGoalId){

                        $totalBenchmark      += $performArea->benchmark;
                        $totalImplementPoint += $performArea->implement_point;
                        $paCalBenchmark      = $performArea->cal_benchmark;
                    }
                }

                $rRealPercent       = ($totalBenchmark != 0) ? $totalImplementPoint / $totalBenchmark : 0;
                $calImplementPoint  = $rRealPercent * $paCalBenchmark;

                $dataPAUpdate = array(
                    'cal_implement_point'  => $calImplementPoint
                );

                DB::table('target_area')
                    ->where('company_id', $companyId)
                    ->where('area_id', $areaId)
                    ->where('year', $year)
                    ->where('goal_id', $pgGoalId)
                    ->update($dataPAUpdate);

            }


        }

    }

    // cập nhật cho 1 công ty với một mục tiêu duy nhất.
    private function calKPI4Company($companyId, $year, $applyDate, $goalId, $actionUser, $type){

        if($type == 0){
            $sqlILC = "
                SELECT *
                FROM important_level_company
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND apply_date = '".$applyDate."'
                AND goal_id = ".$goalId."
            ";

            $objILCDB = DB::select(DB::raw($sqlILC));

            $sqlPerformArea = "
                SELECT *
                FROM target_area
                WHERE company_id = ".$companyId."
                AND`year` = ".$year."
                AND goal_id = ".$goalId."
                AND `month` = 1
            ";

            $objPerformAreDB = DB::select(DB::raw($sqlPerformArea));

            $totalCalIPArea = 0;
            foreach($objPerformAreDB as $performArea){
                $totalCalIPArea += $performArea->cal_implement_point;
            }

            $percentComplete = ($objILCDB[0]->benchmark != 0) ? $totalCalIPArea / $objILCDB[0]->benchmark : 0;
            $calImplementPoint =  $percentComplete * $objILCDB[0]->cal_benchmark;
            $dataILCUpdate = array(
                'implement'                 => 0,
                'implement_point'           => $totalCalIPArea,
                'real_percent'              => $percentComplete,
                'cal_implement_point'       => $calImplementPoint,
                'updated_user'              => $actionUser
            );
            DB::table('important_level_company')->where('id', $objILCDB[0]->id)->update($dataILCUpdate);
        }elseif($type == 1){
            $sqlILC = "
                SELECT *
                FROM important_level_company
                WHERE inactive = 0
                AND company_id = ".$companyId."
                AND apply_date = '".$applyDate."'
            ";

            $sqlPerformArea = "
                SELECT *
                FROM target_area
                WHERE company_id = ".$companyId."
                AND`year` = ".$year."
                AND `month` = 1
            ";

            if($goalId != ""){
                $sqlILC .= " AND goal_id = ".$goalId." ";
                $sqlPerformArea .= " AND goal_id = ".$goalId." ";
            }

            $objILCDB = DB::select(DB::raw($sqlILC));
            $objPerformAreDB = DB::select(DB::raw($sqlPerformArea));

            foreach($objILCDB as $ilc){

                $totalCalIPArea = 0;

                foreach($objPerformAreDB as $performArea){
                    if($performArea->goal_id == $ilc->goal_id){
                        $totalCalIPArea += $performArea->cal_implement_point;
                    }
                }

                $percentComplete = ($ilc->benchmark != 0) ? $totalCalIPArea / $ilc->benchmark : 0;
                $calImplementPoint =  $percentComplete * $ilc->cal_benchmark;

                $dataILCUpdate = array(
                    'implement'                 => 0,
                    'implement_point'           => $totalCalIPArea,
                    'real_percent'              => $percentComplete,
                    'cal_implement_point'       => $calImplementPoint,
                    'updated_user'              => $actionUser
                );
                DB::table('important_level_company')->where('id', $ilc->id)->update($dataILCUpdate);

            }

        }

    }

    // lấy ngày áp dụng cao nhất.
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

    // cập nhật cho 1 mục tiêu.
    private function calKPI4Corporation($corApplyDate, $comApplyDate, $goalId, $actionUser, $type){
        if($type == 0){
            $sqlILCOr = "
                SELECT *
                FROM important_level_corporation
                WHERE inactive = 0
                AND apply_date = '".$corApplyDate."'
                AND goal_id = ".$goalId."
            ";
            $objILCorDB = DB::select(DB::raw($sqlILCOr));

            $sqlILC = "
                SELECT sum(cal_implement_point) as totalIP
                FROM important_level_company
                WHERE inactive = 0
                AND apply_date = '".$comApplyDate."'
                AND goal_id = ".$goalId."
            ";
            $corIP = DB::select(DB::raw($sqlILC))[0]->totalIP;

            if($corIP != 0){

                $percentComplete = ($objILCorDB[0]->benchmark != 0) ? $corIP / $objILCorDB[0]->benchmark : 0;

                $dataILCUpdate = array(
                    'implement'                 => 0,
                    'implement_point'           => $corIP,
                    'percent_complete'          => $percentComplete,
                    'updated_user'              => $actionUser
                );
                DB::table('important_level_corporation')->where('id', $objILCorDB[0]->id)->update($dataILCUpdate);
            }
        }elseif($type == 1){
            $sqlILCOr = "
                SELECT *
                FROM important_level_corporation
                WHERE inactive = 0
                AND apply_date = '".$corApplyDate."'
            ";

            $sqlILC = "
                SELECT *
                FROM important_level_company
                WHERE inactive = 0
                AND apply_date = '".$comApplyDate."'
            ";

            if($goalId != ""){
                $sqlILCOr .= " goal_id = ".$goalId." ";
                $sqlILC .= " goal_id = ".$goalId." ";
            }

            $objILCorDB = DB::select(DB::raw($sqlILCOr));
            $objILCDB = DB::select(DB::raw($sqlILC));

            foreach($objILCorDB as $ilcor){

                $implementPoint = 0;
                foreach($objILCDB as $ilc){
                    if($ilc->goal_id == $ilcor->goal_id){
                        $implementPoint += $ilc->cal_implement_point;
                    }
                }
                $percentComplete = ($ilcor->benchmark != 0) ? $implementPoint / $ilcor->benchmark : 0;

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

    private function calKPI4Employee($companyId, $areaId, $positionId, $year, $month, $type ){
        if($type == 0){
            $sqlTE = "
                SELECT te.*, p.position_code, g.formula
                FROM target_employee te
                LEFT JOIN `position` p ON p.id = te.position_id
                LEFT JOIN goal g ON g.id = te.goal_id
                WHERE te.inactive = 0
                AND te.company_id = ".$companyId."
                AND te.area_id = ".$areaId."
                AND te.position_id = ".$positionId."
                AND te.year = ".$year."
                AND te.month = ".$month."
            ";

            $objTargetEmployeeDB  = DB::select(DB::raw($sqlTE));

            foreach($objTargetEmployeeDB as $targetEmployee){

                if(commonUtils::compareTwoString($targetEmployee->position_code, commonUtils::POSITION_CODE_TQ) == 1){
                    if($targetEmployee->formula == commonUtils::FORMULA_TU_NHAP){
                        $implementPointE = commonUtils::calculatorIP($targetEmployee->target_value, $targetEmployee->implement, $targetEmployee->benchmark, $targetEmployee->goal_type);

                        $dataUpdateIPE = array(
                            'implement_point' 	    => $implementPointE
                        );
                        DB::table('target_employee')->where('id', $targetEmployee->id)->update($dataUpdateIPE);

                        $implementPointP = commonUtils::calculatorIP($targetEmployee->target_value, $targetEmployee->implement, $targetEmployee->cal_benchmark, $targetEmployee->goal_type);

                        $dataUpdateIPP = array(
                            'implement_point' 	    => $implementPointP
                        );

                        DB::table('target_position')
                            ->where('company_id', $targetEmployee->company_id)
                            ->where('area_id', $targetEmployee->area_id)
                            ->where('position_id', $targetEmployee->position_id)
                            ->where('year', $targetEmployee->year)
                            ->where('month', $targetEmployee->month)
                            ->where('goal_id', $targetEmployee->goal_id)
                            ->update($dataUpdateIPP);
                    }
                }else{
                    $implementPointE = commonUtils::calculatorIP($targetEmployee->target_value, $targetEmployee->implement, $targetEmployee->benchmark, $targetEmployee->goal_type);

                    $dataUpdateIPE = array(
                        'implement_point' 	    => $implementPointE
                    );
                    DB::table('target_employee')->where('id', $targetEmployee->id)->update($dataUpdateIPE);
                }

            }
        }else if($type == 1){
            $sqlTE = "
                SELECT te.*, p.position_code, g.formula
                FROM target_employee te
                LEFT JOIN `position` p ON p.id = te.position_id
                LEFT JOIN goal g ON g.id = te.goal_id
                WHERE te.inactive = 0
                AND te.company_id = ".$companyId."
                AND te.area_id = ".$areaId."
                AND te.year = ".$year."
            ";

            if($positionId != ""){
                $sqlTE .= " AND te.position_id = ".$positionId." ";
            }

            if($month != ""){
                $sqlTE .= " AND te.month = ".$month." ";
            }

            $objTargetEmployeeDB  = DB::select(DB::raw($sqlTE));

            foreach($objTargetEmployeeDB as $targetEmployee){

                if(commonUtils::compareTwoString($targetEmployee->position_code, commonUtils::POSITION_CODE_TQ) == 1){
                    if($targetEmployee->formula == commonUtils::FORMULA_TU_NHAP){

                        $implementPointE = commonUtils::calculatorIP($targetEmployee->target_value, $targetEmployee->implement, $targetEmployee->benchmark, $targetEmployee->goal_type);
                        $implementPointP = commonUtils::calculatorIP($targetEmployee->target_value, $targetEmployee->implement, $targetEmployee->cal_benchmark, $targetEmployee->goal_type);

                        $dataUpdateIPE = array(
                            'implement_point' 	    => $implementPointE
                        );
                        DB::table('target_employee')->where('id', $targetEmployee->id)->update($dataUpdateIPE);

                        $dataUpdateIPP = array(
                            'implement_point' 	    => $implementPointP
                        );

                        DB::table('target_position')
                            ->where('company_id', $targetEmployee->company_id)
                            ->where('area_id', $targetEmployee->area_id)
                            ->where('position_id', $targetEmployee->position_id)
                            ->where('year', $targetEmployee->year)
                            ->where('month', $targetEmployee->month)
                            ->where('goal_id', $targetEmployee->goal_id)
                            ->update($dataUpdateIPP);
                    }
                }else{
                    $implementPointE = commonUtils::calculatorIP($targetEmployee->target_value, $targetEmployee->implement, $targetEmployee->benchmark, $targetEmployee->goal_type);
                    $dataUpdateIPE = array(
                        'implement_point' => $implementPointE
                    );
                    DB::table('target_employee')->where('id', $targetEmployee->id)->update($dataUpdateIPE);
                }

            }
        }
    }

    /**
     * @param $year
     * @param $month
     * @param $applyDate
     * @param $type
     * 0: lock important level corporation
     * 1: lock important level company
     * 2: lock important level area/position
     * 3: lock target
     * 4: lock perform
     */
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

    private function calBenchmark4Corporation($applyDate, $parentId){

        /** format benchmark all goal of this position */

        if($parentId != 0){

            $sqlGoal = " SELECT id FROM goal WHERE parent_id = ".$parentId." ";

            $objGoalFDB  = DB::select(DB::raw($sqlGoal));

            foreach($objGoalFDB as $goal){
                $sqlFormatBenchmark = "
                    UPDATE important_level_corporation
                    SET benchmark = 0
                    WHERE inactive = 0
                    AND `apply_date` = '".$applyDate."'
                    AND goal_id = ".$goal->id."
                ";
                DB::update(DB::raw($sqlFormatBenchmark));
            }
        }else{
            $sqlFormatBenchmark = "
                UPDATE important_level_corporation
                SET benchmark = 0
                WHERE inactive = 0
                AND `apply_date` = '".$applyDate."'
            ";
            DB::update(DB::raw($sqlFormatBenchmark));
        }



        $sqlIL = "
                SELECT ilc.*, g.goal_name, g.formula, g.goal_type, g.parent_id, c.corporation_name
                FROM important_level_corporation ilc
                LEFT JOIN goal g ON g.id = ilc.goal_id
                LEFT JOIN corporation c ON c.id = ilc.corporation_id
                WHERE ilc.inactive = 0
                AND ilc.apply_date = '".$applyDate."'
            ";

        if($parentId != 0){
            $sqlIL .= " AND ( ilc.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ilc.goal_id = ".$parentId." )";
        }

        $objILDB  = DB::select(DB::raw($sqlIL));

        if($parentId != 0){
            $parentBenchmark = 0;

            foreach($objILDB as $il){
                if($il->parent_id == 0){
                    $parentBenchmark = $il->benchmark;
                    break;
                }
            }

            $totalIPC = 0;

            foreach($objILDB as $il){
                if($il->parent_id != 0){
                    $totalIPC += $il->important_level;
                }
            }

            if($totalIPC != 0){

                foreach($objILDB as $il){
                    if($il->parent_id != 0){

                        $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $totalIPC) * $il->important_level) : 0;
                        $dataUpdateBM = array(
                            'benchmark' 	=> $benchmark
                        );

                        DB::table('important_level_corporation')
                            ->where('id', $il->id)
                            ->update($dataUpdateBM);
                    }
                }

            }

        }else{
            $arrParent = array();
            $ip = 0;
            $totalParentIL = 0;

            foreach($objILDB as $il){
                if($il->parent_id == 0){
                    $totalParentIL += $il->important_level;

                    $totalILC = 0;
                    foreach($objILDB as $ilChild){
                        if($ilChild->parent_id == $il->goal_id){
                            $totalILC += $ilChild->important_level;
                        }
                    }

                    $arrParent[$ip]['id']               = $il->id;
                    $arrParent[$ip]['goalId']           = $il->goal_id;
                    $arrParent[$ip]['importantLevel']   = $il->important_level;
                    $arrParent[$ip]['totalILChild']     = $totalILC;
                    $ip++;
                }
            }


            if($totalParentIL != 0){

                foreach($arrParent as $parent){

                    $parentBenchmark = ((100 / $totalParentIL) * $parent['importantLevel']);
                    $dataUpdateBM = array(
                        'benchmark' 	=> $parentBenchmark
                    );

                    DB::table('important_level_corporation')->where('id', $parent['id'])->update($dataUpdateBM);

                    foreach($objILDB as $ilChild){
                        if($ilChild->parent_id == $parent['goalId']){

                            $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $parent['totalILChild']) * $ilChild->important_level) : 0;
                            $dataUpdateBM = array(
                                'benchmark' 	=> $benchmark
                            );

                            DB::table('important_level_corporation')
                                ->where('id', $ilChild->id)
                                ->update($dataUpdateBM);

                        }
                    }

                }

            }
        }

    }

    private function calBenchmark4EachCompany($companyId, $applyDate, $parentId){


        /** format benchmark all goal of this position */
        $sqlFormatBenchmark = "
            UPDATE important_level_company
            SET benchmark = 0
            WHERE company_id = ".$companyId."
            AND `apply_date` = '".$applyDate."'
        ";

        if($parentId != 0){
            $sqlFormatBenchmark .= " AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) ";
        }

        DB::update(DB::raw($sqlFormatBenchmark));

        $sqlIL = "
                SELECT ilc.*, c.company_name, g.goal_name, g.formula, g.goal_type, g.parent_id
                FROM important_level_company ilc
                LEFT JOIN goal g ON g.id = ilc.goal_id
                LEFT JOIN company c ON c.id = ilc.company_id
                WHERE ilc.inactive = 0
                AND ilc.company_id = ".$companyId."
                AND ilc.apply_date = '".$applyDate."'
            ";

        if($parentId != 0){
            $sqlIL .= " AND ( ilc.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ilc.goal_id = ".$parentId." )";
        }

        $objILDB  = DB::select(DB::raw($sqlIL));

        if($parentId != 0){
            $parentBenchmark = 0;

            foreach($objILDB as $il){
                if($il->parent_id == 0){
                    $parentBenchmark = $il->benchmark;
                    break;
                }
            }

            $totalIPC = 0;

            foreach($objILDB as $il){
                if($il->parent_id != 0){
                    $totalIPC += $il->important_level;
                }
            }

            if($totalIPC != 0){

                foreach($objILDB as $il){
                    if($il->parent_id != 0){

                        $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $totalIPC) * $il->important_level) : 0;
                        $dataUpdateBM = array(
                            'benchmark' 	=> $benchmark
                        );

                        DB::table('important_level_company')
                            ->where('id', $il->id)
                            ->update($dataUpdateBM);
                    }
                }

            }

        }else{
            $arrParent = array();
            $ip = 0;
            $totalParentIL = 0;

            foreach($objILDB as $il){
                if($il->parent_id == 0){
                    $totalParentIL += $il->important_level;

                    $totalILC = 0;
                    foreach($objILDB as $ilChild){
                        if($ilChild->parent_id == $il->goal_id){
                            $totalILC += $ilChild->important_level;
                        }
                    }

                    $arrParent[$ip]['id']               = $il->id;
                    $arrParent[$ip]['goalId']           = $il->goal_id;
                    $arrParent[$ip]['importantLevel']   = $il->important_level;
                    $arrParent[$ip]['totalILChild']     = $totalILC;
                    $ip++;
                }
            }

            if($totalParentIL != 0){

                foreach($arrParent as $parent){

                    $parentBenchmark = ((100 / $totalParentIL) * $parent['importantLevel']);
                    $dataUpdateBM = array(
                        'benchmark' 	=> $parentBenchmark
                    );

                    DB::table('important_level_company')->where('id', $parent['id'])->update($dataUpdateBM);

                    foreach($objILDB as $ilChild){
                        if($ilChild->parent_id == $parent['goalId']){

                            $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $parent['totalILChild']) * $ilChild->important_level) : 0;
                            $dataUpdateBM = array(
                                'benchmark' 	=> $benchmark
                            );

                            DB::table('important_level_company')
                                ->where('id', $ilChild->id)
                                ->update($dataUpdateBM);

                        }
                    }

                }

            }
        }

    }

    private function calKPIBenchmark4Company($goalId, $comApplyDate, $corApplyDate){

        $sqlFormatCBenchmark = "
            UPDATE important_level_company
            SET cal_benchmark = 0
            WHERE apply_date = '".$comApplyDate."'
        ";

        if($goalId != ""){
            $sqlFormatCBenchmark .= " AND `goal_id` = ".$goalId." ";
        }

        DB::update(DB::raw($sqlFormatCBenchmark));

        $sqlIL = "
                SELECT ilcor.*
                FROM important_level_corporation ilcor
                WHERE ilcor.inactive = 0
                AND ilcor.apply_date = '".$corApplyDate."'
            ";

        $sqlILC = "
                SELECT ilc.*
                FROM important_level_company ilc
                WHERE ilc.inactive = 0
                AND ilc.apply_date = '".$comApplyDate."'
            ";

        if($goalId != ""){
            $sqlIL  .= " AND ilcor.goal_id = ".$goalId." ";
            $sqlILC .= " AND ilc.goal_id = ".$goalId." ";
        }

        $objILCDB  = DB::select(DB::raw($sqlILC));
        $objILDB  = DB::select(DB::raw($sqlIL));


        foreach($objILDB as $ilcor){

            $totalIL = 0;
            foreach($objILCDB as $ilc){

                if($ilc->goal_id == $ilcor->goal_id){
                    $totalIL += $ilc->important_level;
                }
            }

            if($totalIL != 0){
                foreach($objILCDB as $ilci){

                    if($ilci->goal_id == $ilcor->goal_id){

                        $calBenchmark = ($ilcor->benchmark / $totalIL) * $ilci->important_level;
                        $dataUpdateCBM = array(
                            'cal_benchmark' 	=> $calBenchmark
                        );

                        DB::table('important_level_company')
                            ->where('id', $ilci->id)
                            ->update($dataUpdateCBM);

                    }
                }
            }

        }
    }

    private function calBenchmark4EachArea($companyId, $areaId, $year, $month, $parentId){


        $sqlFormatBenchmark = "
            UPDATE important_level_area
            SET benchmark = 0
            WHERE company_id = ".$companyId."
            AND area_id = ".$areaId."
            AND `year` = ".$year."
        ";

        if($month != ""){
            $sqlFormatBenchmark .= " AND `month` = ".$month." ";
        }

        if($parentId != 0){
            $sqlFormatBenchmark .= " AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) ";
        }

        DB::update(DB::raw($sqlFormatBenchmark));

        $sqlILA = "
                SELECT ila.*, c.company_name, g.goal_name, a.area_name, g.formula, g.goal_type, g.parent_id
                FROM important_level_area ila
                LEFT JOIN goal g ON g.id = ila.goal_id
                LEFT JOIN company c ON c.id = ila.company_id
                LEFT JOIN area a ON a.id = ila.area_id
                WHERE ila.inactive = 0
                AND ila.company_id = ".$companyId."
                AND ila.area_id = ".$areaId."
                AND ila.year = ".$year."
            ";

        if($month != ""){
            $sqlILA .= " AND ila.month = ".$month." ";
        }else{
            $sqlILA .= " AND ila.month = 1 ";
        }

        if($parentId != 0){
            $sqlILA .= " AND ( ila.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ila.goal_id = ".$parentId." )";
        }

        $objILADB  = DB::select(DB::raw($sqlILA));

        if($parentId != 0){
            $parentBenchmark = 0;

            foreach($objILADB as $ila){
                if($ila->parent_id == 0){
                    $parentBenchmark = $ila->benchmark;
                    break;
                }
            }

            $totalIPC = 0;

            foreach($objILADB as $ila){
                if($ila->parent_id != 0){
                    $totalIPC += $ila->important_level;
                }
            }

            if($totalIPC != 0){

                foreach($objILADB as $ila){
                    if($ila->parent_id != 0){

                        $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $totalIPC) * $ila->important_level) : 0;
                        $dataUpdateBM = array(
                            'benchmark'  => $benchmark
                        );

                        DB::table('important_level_area')
                            ->where('company_id', $ila->company_id)
                            ->where('area_id', $ila->area_id)
                            ->where('year', $ila->year)
                            ->where('goal_id', $ila->goal_id)
                            ->update($dataUpdateBM);
                    }
                }

            }

        }else{
            $arrParent = array();
            $ip = 0;
            $totalParentIL = 0;

            foreach($objILADB as $ila){
                if($ila->parent_id == 0){
                    $totalParentIL += $ila->important_level;

                    $totalILC = 0;
                    foreach($objILADB as $ilaChild){
                        if($ilaChild->parent_id == $ila->goal_id){
                            $totalILC += $ilaChild->important_level;
                        }
                    }

                    $arrParent[$ip]['id']               = $ila->id;
                    $arrParent[$ip]['goalId']           = $ila->goal_id;
                    $arrParent[$ip]['importantLevel']   = $ila->important_level;
                    $arrParent[$ip]['totalILChild']     = $totalILC;
                    $ip++;
                }
            }


            if($totalParentIL != 0){

                foreach($arrParent as $parent){

                    $parentBenchmark = ((100 / $totalParentIL) * $parent['importantLevel']) / 12;
                    $dataUpdateBM = array(
                        'benchmark'  => $parentBenchmark
                    );

                    DB::table('important_level_area')
                        ->where('goal_id', $parent['goalId'])
                        ->where('company_id', $companyId)
                        ->where('area_id', $areaId)
                        ->where('year', $year)
                        ->update($dataUpdateBM);

                    foreach($objILADB as $ilaChild){
                        if($ilaChild->parent_id == $parent['goalId']){

                            $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $parent['totalILChild']) * $ilaChild->important_level) : 0;
                            $dataUpdateBM = array(
                                'benchmark'  => $benchmark
                            );

                            DB::table('important_level_area')
                                ->where('company_id', $ilaChild->company_id)
                                ->where('area_id', $ilaChild->area_id)
                                ->where('year', $ilaChild->year)
                                ->where('goal_id', $ilaChild->goal_id)
                                ->update($dataUpdateBM);

                        }
                    }

                }

            }
        }

    }

    private function calKPIBenchmark4Area($companyId, $goalId, $year, $month, $applyDate){

        $sqlFormatCBenchmark = "
            UPDATE important_level_area
            SET cal_benchmark = 0
            WHERE company_id = ".$companyId."
            AND `year` = ".$year."

        ";

        if($goalId != ""){
            $sqlFormatCBenchmark .= " AND `goal_id` = ".$goalId." ";
        }

        DB::update(DB::raw($sqlFormatCBenchmark));

        $sqlILC = "
                SELECT ilc.*
                FROM important_level_company ilc
                WHERE ilc.inactive = 0
                AND ilc.company_id = ".$companyId."
                AND year(ilc.apply_date) = ".$year."
                AND ilc.apply_date = '".$applyDate."'
            ";

        $sqlILA = "
                SELECT ila.*
                FROM important_level_area ila
                WHERE ila.inactive = 0
                AND ila.company_id = ".$companyId."
                AND ila.year = ".$year."
            ";

        if($month != ""){
            $sqlILA .= " AND ila.month = ".$month." ";
        }else{
            $sqlILA .= " AND ila.month = 1 ";
        }

        if($goalId != ""){
            $sqlILC .= " AND ilc.goal_id = ".$goalId." ";
            $sqlILA .= " AND ila.goal_id = ".$goalId." ";

        }


        $objILCDB  = DB::select(DB::raw($sqlILC));
        $objILADB  = DB::select(DB::raw($sqlILA));

        foreach($objILCDB as $ilc){

            $totalIL = 0;
            foreach($objILADB as $ila){

                if($ila->goal_id == $ilc->goal_id){
                    $totalIL += $ila->important_level;
                }
            }

            if($totalIL != 0){

                foreach($objILADB as $ila){
                    if($ila->goal_id == $ilc->goal_id){
                        $calBenchmark = ($ilc->benchmark / $totalIL) * $ila->important_level;
                        $dataUpdateCBM = array(
                            'cal_benchmark' 	=> $calBenchmark
                        );

                        DB::table('important_level_area')
                            ->where('company_id', $ila->company_id)
                            ->where('area_id', $ila->area_id)
                            ->where('year', $ila->year)
                            ->where('goal_id', $ila->goal_id)
                            ->update($dataUpdateCBM);
                    }
                }

            }
        }

    }

    private function calBenchmark4EachPosition($companyId, $areaId, $positionId, $year, $month, $parentId){


        /** format benchmark all goal of this position */
        $sqlFormatBenchmark = "
            UPDATE important_level_position
            SET benchmark = 0
            WHERE company_id = ".$companyId."
            AND area_id = ".$areaId."
            AND position_id = ".$positionId."
            AND `year` = ".$year."
            AND `month` = ".$month."
        ";

        if($parentId != 0){
            $sqlFormatBenchmark .= " AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) ";
        }

        DB::update(DB::raw($sqlFormatBenchmark));

        $sqlILP = "
                SELECT ilp.*, c.company_name, g.goal_name, p.position_name, a.area_name, g.formula, p.position_code, g.goal_type, g.parent_id
                FROM important_level_position ilp
                LEFT JOIN goal g ON g.id = ilp.goal_id
                LEFT JOIN position p ON p.id = ilp.position_id
                LEFT JOIN company c ON c.id = ilp.company_id
                LEFT JOIN area a ON a.id = ilp.area_id
                WHERE ilp.inactive = 0
                AND ilp.company_id = ".$companyId."
                AND ilp.area_id = ".$areaId."
                AND ilp.position_id = ".$positionId."
                AND ilp.year = ".$year."
                AND ilp.month = ".$month."
            ";

        if($parentId != 0){
            $sqlILP .= " AND ( ilp.goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." ) or ilp.goal_id = ".$parentId." )";
        }

        $objILPDB  = DB::select(DB::raw($sqlILP));

        if($parentId != 0){
            $parentBenchmark = 0;

            foreach($objILPDB as $ilp){
                if($ilp->parent_id == 0){
                    $parentBenchmark = $ilp->benchmark;
                    break;
                }
            }

            $totalIPC = 0;

            foreach($objILPDB as $ilp){
                if($ilp->parent_id != 0){
                    $totalIPC += $ilp->important_level;
                }
            }

            if($totalIPC != 0){

                foreach($objILPDB as $ilp){
                    if($ilp->parent_id != 0){

                        $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $totalIPC) * $ilp->important_level) : 0;
                        $dataUpdateBM = array(
                            'benchmark' 	=> $benchmark
                        );

                        DB::table('important_level_position')->where('id', $ilp->id)->update($dataUpdateBM);
                    }
                }

            }

        }else{
            $arrParent = array();
            $ip = 0;
            $totalParentIL = 0;

            foreach($objILPDB as $ilp){
                if($ilp->parent_id == 0){
                    $totalParentIL += $ilp->important_level;

                    $totalILC = 0;
                    foreach($objILPDB as $ilpChild){
                        if($ilpChild->parent_id == $ilp->goal_id){
                            $totalILC += $ilpChild->important_level;
                        }
                    }

                    $arrParent[$ip]['id']               = $ilp->id;
                    $arrParent[$ip]['goalId']           = $ilp->goal_id;
                    $arrParent[$ip]['importantLevel']   = $ilp->important_level;
                    $arrParent[$ip]['totalILChild']     = $totalILC;
                    $ip++;
                }
            }


            if($totalParentIL != 0){

                foreach($arrParent as $parent){

                    $parentBenchmark = (100 / $totalParentIL) * $parent['importantLevel'];
                    $dataUpdateBM = array(
                        'benchmark' 	=> $parentBenchmark
                    );

                    DB::table('important_level_position')->where('id', $parent['id'])->update($dataUpdateBM);

                    foreach($objILPDB as $ilpChild){
                        if($ilpChild->parent_id == $parent['goalId']){

                            $benchmark = ($parentBenchmark != 0) ? (($parentBenchmark / $parent['totalILChild']) * $ilpChild->important_level) : 0;
                            $dataUpdateBM = array(
                                'benchmark' 	=> $benchmark
                            );

                            DB::table('important_level_position')->where('id', $ilpChild->id)->update($dataUpdateBM);

                        }
                    }

                }

            }
        }

    }

    private function calKPIBenchmark4Position($companyId, $areaId, $goalId, $year, $month, $type){

        if($type == 0){

            $sqlFormatCBenchmark = "
                UPDATE important_level_position
                SET cal_benchmark = 0
                WHERE company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND `year` = ".$year."
                AND `month` = ".$month."
                AND `goal_id` = ".$goalId."
            ";

            DB::update(DB::raw($sqlFormatCBenchmark));

            $sqlILA = "
                SELECT ila.*
                FROM important_level_area ila
                LEFT JOIN area a ON a.id = ila.area_id
                WHERE ila.inactive = 0
                AND ila.company_id = ".$companyId."
                AND ila.area_id = ".$areaId."
                AND ila.goal_id = ".$goalId."
                AND ila.year = ".$year."
                AND ila.month = ".$month."
            ";

            $objILADB  = DB::select(DB::raw($sqlILA));

            if(count($objILADB) == 1){
                $ila = $objILADB[0];

                $sqlILP = "
                SELECT ilp.*
                FROM important_level_position ilp
                LEFT JOIN area a ON a.id = ilp.area_id
                WHERE ilp.inactive = 0
                AND ilp.company_id = ".$companyId."
                AND ilp.area_id = ".$areaId."
                AND ilp.goal_id = ".$goalId."
                AND ilp.year = ".$year."
                AND ilp.month = ".$month."
            ";

                $objILPDB  = DB::select(DB::raw($sqlILP));

                $totalIL = 0;
                foreach($objILPDB as $ilp){
                    $totalIL += $ilp->important_level;
                }

                if($totalIL != 0){

                    foreach($objILPDB as $ilp){

                        $calBenchmark = ($ila->benchmark / $totalIL) * $ilp->important_level;
                        $dataUpdateCBM = array(
                            'cal_benchmark' 	=> $calBenchmark
                        );

                        DB::table('important_level_position')->where('id', $ilp->id)->update($dataUpdateCBM);

                    }

                }
            }
        }else if($type == 1){

            $sqlFormatCBenchmark = "
                UPDATE important_level_position
                SET cal_benchmark = 0
                WHERE company_id = ".$companyId."
                AND area_id = ".$areaId."
                AND `year` = ".$year."
            ";

            if($month != ""){
                $sqlFormatCBenchmark .= " AND `month` = ".$month." ";
            }

            if($goalId != ""){
                $sqlFormatCBenchmark .= " AND `goal_id` = ".$goalId." ";
            }

            DB::update(DB::raw($sqlFormatCBenchmark));

            $sqlILA = "
                SELECT ila.*
                FROM important_level_area ila
                LEFT JOIN area a ON a.id = ila.area_id
                WHERE ila.inactive = 0
                AND ila.company_id = ".$companyId."
                AND ila.area_id = ".$areaId."
                AND ila.year = ".$year."
            ";

            if($month != ""){
                $sqlILA .= " AND ila.month = ".$month." ";
            }

            if($goalId != ""){
                $sqlILA .= " AND ila.goal_id = ".$goalId." ";
            }

            $objILADB  = DB::select(DB::raw($sqlILA));

            $arrMonth = commonUtils::defaultMonth();

            if(count($objILADB) > 0){

                $sqlILP = "
                SELECT ilp.*
                    FROM important_level_position ilp
                    LEFT JOIN area a ON a.id = ilp.area_id
                    WHERE ilp.inactive = 0
                    AND ilp.company_id = ".$companyId."
                    AND ilp.area_id = ".$areaId."
                    AND ilp.year = ".$year."
                ";

                if($month != ""){
                    $sqlILP .= " AND ilp.month = ".$month." ";
                }

                if($goalId != ""){
                    $sqlILP .= " AND ilp.goal_id = ".$goalId." ";
                }

                $objILPDB  = DB::select(DB::raw($sqlILP));

                $sqlPosition = " SELECT * FROM `position` WHERE inactive = 0 ";

                $objPositionDB  = DB::select(DB::raw($sqlPosition));

                foreach($arrMonth as $m){

                    foreach($objILADB as $ila){

                        if($ila->month == $m){
                            $totalIL = 0;

                            foreach($objILPDB as $ilp){

                                if(
                                    $ilp->month      == $m
                                    && $ilp->goal_id == $ila->goal_id
                                ){
                                    $totalIL += $ilp->important_level;
                                }
                            }

                            if($totalIL != 0){

                                foreach($objILPDB as $islp){

                                    if(
                                        $islp->month      == $m
                                        && $islp->goal_id == $ila->goal_id
                                    ){
                                        $calBenchmark = ($ila->benchmark / $totalIL) * $islp->important_level;
                                        $dataUpdateCBM = array(
                                            'cal_benchmark' 	=> $calBenchmark
                                        );

                                        DB::table('important_level_position')->where('id', $islp->id)->update($dataUpdateCBM);
                                    }
                                }

                            }
                        }

                    }
                }
            }
        }



    }

    private function updateBenchmark4TargetPE($companyId, $areaId, $positionId, $year, $month, $actionUser, $type){

        if($type == 0){

            $sqlILP = "
                SELECT *
                FROM important_level_position
                WHERE inactive = 0
                AND `year` = ".$year."
                AND `month` = ".$month."
                AND `company_id` = ".$companyId."
                AND `area_id` = ".$areaId."
                AND `position_id` = ".$positionId."
            ";

            $objILPDB  = DB::select(DB::raw($sqlILP));

            foreach($objILPDB as $ilp){

                $dataUpdateBM = array(
                'benchmark' 	    => $ilp->benchmark
                , 'updated_user'    => $actionUser
                , 'updated_date'    => date("Y-m-d h:i:sa")
                );

                DB::table('target_position')
                    ->where('company_id', $ilp->company_id)
                    ->where('area_id', $ilp->area_id)
                    ->where('position_id', $ilp->position_id)
                    ->where('year', $ilp->year)
                    ->where('month', $ilp->month)
                    ->where('goal_id', $ilp->goal_id)
                    ->update($dataUpdateBM);

                DB::table('target_employee')
                    ->where('company_id', $ilp->company_id)
                    ->where('area_id', $ilp->area_id)
                    ->where('position_id', $ilp->position_id)
                    ->where('year', $ilp->year)
                    ->where('month', $ilp->month)
                    ->where('goal_id', $ilp->goal_id)
                    ->update($dataUpdateBM);

            }

        }else if($type == 1){
            $sqlILP = "
                SELECT *
                FROM important_level_position
                WHERE inactive = 0
                AND `year` = ".$year."
                AND `company_id` = ".$companyId."
                AND `area_id` = ".$areaId."
            ";

            if($month != ""){
                $sqlILP .= " AND `month` = ".$month." ";
            }

            if($positionId != ""){
                $sqlILP .= " AND `position_id` = ".$positionId." ";
            }

            $objILPDB  = DB::select(DB::raw($sqlILP));

            foreach($objILPDB as $ilp){

                $dataUpdateBM = array(
                'benchmark' 	    => $ilp->benchmark
                ,'cal_benchmark' 	=> $ilp->cal_benchmark
                , 'updated_user'    => $actionUser
                , 'updated_date'    => date("Y-m-d h:i:sa")
                );

                DB::table('target_position')
                    ->where('company_id', $ilp->company_id)
                    ->where('area_id', $ilp->area_id)
                    ->where('position_id', $ilp->position_id)
                    ->where('year', $ilp->year)
                    ->where('month', $ilp->month)
                    ->where('goal_id', $ilp->goal_id)
                    ->update($dataUpdateBM);

                DB::table('target_employee')
                    ->where('company_id', $ilp->company_id)
                    ->where('area_id', $ilp->area_id)
                    ->where('position_id', $ilp->position_id)
                    ->where('year', $ilp->year)
                    ->where('month', $ilp->month)
                    ->where('goal_id', $ilp->goal_id)
                    ->update($dataUpdateBM);

            }
        }



    }

    private function updateCBenchmark4TargetPE($companyId, $areaId, $goalId, $year, $month){
        $sqlILP = "
            SELECT *
            FROM important_level_position
            WHERE inactive = 0
            AND `year` = ".$year."
            AND `month` = ".$month."
            AND `company_id` = ".$companyId."
            AND `area_id` = ".$areaId."
            AND `goal_id` = ".$goalId."
        ";

        $objILPDB  = DB::select(DB::raw($sqlILP));

        foreach($objILPDB as $ilp){

            $dataUpdateBM = array(
                'cal_benchmark' 	    => $ilp->cal_benchmark
            );

            DB::table('target_position')
                ->where('company_id', $ilp->company_id)
                ->where('area_id', $ilp->area_id)
                ->where('position_id', $ilp->position_id)
                ->where('year', $ilp->year)
                ->where('month', $ilp->month)
                ->where('goal_id', $ilp->goal_id)
                ->update($dataUpdateBM);

            DB::table('target_employee')
                ->where('company_id', $ilp->company_id)
                ->where('area_id', $ilp->area_id)
                ->where('position_id', $ilp->position_id)
                ->where('year', $ilp->year)
                ->where('month', $ilp->month)
                ->where('goal_id', $ilp->goal_id)
                ->update($dataUpdateBM);

        }
    }

    private function updateBenchmark4TargetA($companyId, $areaId, $year, $month, $actionUser){
        $sqlIL = "
            SELECT *
            FROM important_level_area
            WHERE inactive = 0
            AND `year` = ".$year."
            AND `month` = ".$month."
            AND `company_id` = ".$companyId."
            AND `area_id` = ".$areaId."
        ";

        $objILDB  = DB::select(DB::raw($sqlIL));

        foreach($objILDB as $il){

            $dataUpdateBM = array(
                'benchmark' 	    => $il->benchmark
            , 'updated_user'    => $actionUser
            , 'updated_date'    => date("Y-m-d h:i:sa")
            );

            DB::table('target_area')
                ->where('company_id', $il->company_id)
                ->where('area_id', $il->area_id)
                ->where('year', $il->year)
                ->where('goal_id', $il->goal_id)
                ->update($dataUpdateBM);



        }
    }

    private function updateCBenchmark4TargetA($companyId, $goalId, $year, $month, $type){

        if($type == 0){
            $sqlIL = "
                SELECT *
                FROM important_level_area
                WHERE inactive = 0
                AND `year` = ".$year."
                AND `month` = ".$month."
                AND `company_id` = ".$companyId."
                AND `goal_id` = ".$goalId."
            ";

            $objILDB  = DB::select(DB::raw($sqlIL));

            foreach($objILDB as $il){

                $dataUpdateBM = array(
                    'cal_benchmark' => $il->cal_benchmark
                );

                DB::table('target_area')
                    ->where('company_id', $il->company_id)
                    ->where('area_id', $il->area_id)
                    ->where('year', $il->year)
                    ->where('goal_id', $il->goal_id)
                    ->update($dataUpdateBM);
            }
        }else if($type == 1){
            $sqlIL = "
                SELECT *
                FROM important_level_area
                WHERE inactive = 0
                AND `year` = ".$year."
                AND `month` = ".$month."
                AND `company_id` = ".$companyId."
            ";

            $objILDB  = DB::select(DB::raw($sqlIL));

            foreach($objILDB as $il){

                $dataUpdateBM = array(
                    'cal_benchmark' => $il->cal_benchmark
                );

                DB::table('target_area')
                    ->where('company_id', $il->company_id)
                    ->where('area_id', $il->area_id)
                    ->where('year', $il->year)
                    ->where('goal_id', $il->goal_id)
                    ->update($dataUpdateBM);
            }
        }


    }

    private function deleteILCor($id, $year, $applyDate, $goalId, $parentId){

        if($id != ""){
            DB::table('important_level_corporation')->where('id', $id)->delete();
        }

        if($applyDate == ""){
            $applyDate = $this->getApplyDate4Corporation($year);
        }

        if($applyDate != ""){

            $sqlDelete = "  DELETE FROM important_level_corporation WHERE apply_date = '".$applyDate."' ";


            if($parentId == 0){
                $tempSQL = "select id from goal where parent_id = ".$goalId." ";
                $objGoalDelete = DB::select(DB::raw($tempSQL));

                if(count($objGoalDelete) > 0){
                    $subDel = " AND ( goal_id = ".$goalId." ";
                    foreach($objGoalDelete as $goalDel){
                        $subDel .= " OR goal_id = ".$goalDel->id." ";
                    }
                    $sqlDelete .= $subDel." ) ";
                }

            }else{
                $sqlDelete .= " AND goal_id = ".$goalId." ";
            }

            DB::delete(DB::raw($sqlDelete));
        }
    }

    private function deleteILC($id, $companyId, $year, $applyDate, $goalId, $parentId){

        if($id != ""){
            DB::table('important_level_company')->where('id', $id)->delete();
        }

        if($applyDate == ""){
            $applyDate = $this->getApplyDate4Company('', $year, '');
        }

        if($applyDate != ""){

            $sqlDelete = "  DELETE FROM important_level_company WHERE apply_date = '".$applyDate."' ";

            if($companyId != ""){
                $sqlDelete .= " AND company_id = ".$companyId." ";
            }

            if($parentId == 0){
                $tempSQL = "select id from goal where parent_id = ".$goalId." ";
                $objGoalDelete = DB::select(DB::raw($tempSQL));

                if(count($objGoalDelete) > 0){
                    $subDel = " AND ( goal_id = ".$goalId." ";
                    foreach($objGoalDelete as $goalDel){
                        $subDel .= " OR goal_id = ".$goalDel->id." ";
                    }
                    $sqlDelete .= $subDel." ) ";
                }

            }else{
                $sqlDelete .= " AND goal_id = ".$goalId." ";
            }

            DB::delete(DB::raw($sqlDelete));
        }
    }

    private function deleteILA($id, $companyId, $areaId, $year, $goalId, $parentId){

        if($id != ""){
            DB::table('important_level_area')->where('id', $id)->delete();
        }

        $sqlDelete = "  DELETE FROM important_level_area WHERE `year` = ".$year." ";

        if($companyId != ""){
            $sqlDelete .= " AND company_id = ".$companyId." ";
        }

        if($areaId != ""){
            $sqlDelete .= " AND area_id = ".$areaId." ";
        }

        if($parentId == 0){
            $tempSQL = "select id from goal where parent_id = ".$goalId." ";
            $objGoalDelete = DB::select(DB::raw($tempSQL));

            if(count($objGoalDelete) > 0){
                $subDel = " AND ( goal_id = ".$goalId." ";
                foreach($objGoalDelete as $goalDel){
                    $subDel .= " OR goal_id = ".$goalDel->id." ";
                }
                $sqlDelete .= $subDel." ) ";
            }

        }else{
            $sqlDelete .= " AND goal_id = ".$goalId." ";
        }

        DB::delete(DB::raw($sqlDelete));

    }

    private function deleteILP($id, $companyId, $areaId,$positionId, $positionCode, $year, $month, $goalId, $parentId){

        if($id != ""){
            DB::table('important_level_position')->where('id', $id)->delete();
        }

        $sqlDelete = "  DELETE FROM important_level_position WHERE `year` = ".$year." ";

        if($companyId != ""){
            $sqlDelete .= " AND company_id = ".$companyId." ";
        }

        if($areaId != ""){
            $sqlDelete .= " AND area_id = ".$areaId." ";
        }

        if($parentId == 0){
            $tempSQL = "select id from goal where parent_id = ".$goalId." ";
            $objGoalDelete = DB::select(DB::raw($tempSQL));

            if(count($objGoalDelete) > 0){
                $subDel = " AND ( goal_id = ".$goalId." ";
                foreach($objGoalDelete as $goalDel){
                    $subDel .= " OR goal_id = ".$goalDel->id." ";
                }
                $sqlDelete .= $subDel." ) ";
            }

        }else{
            $sqlDelete .= " AND goal_id = ".$goalId." ";
        }

        if($month != ""){
            $sqlDelete .= " AND month = ".$month." ";
        }

        $subPositionId = -1;
        if($positionId != ""){

            $sqlPosition = " SELECT * FROM `position` WHERE inactive = 0 ";
            $objPositionDB = DB::select(DB::raw($sqlPosition));


            switch($positionCode) {

                case commonUtils::POSITION_CODE_CHT:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_GDV){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;
                case commonUtils::POSITION_CODE_CV_KHDN:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_KAM_AM){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;
                case commonUtils::POSITION_CODE_CV_KHCN:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_NVBH){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;

            }

            if($subPositionId != -1){
                DB::delete(DB::raw($sqlDelete." AND  position_id = ".$subPositionId." "));
            }

            $sqlDelete .= " AND  position_id = ".$positionId." ";
        }

        DB::delete(DB::raw($sqlDelete));

        if($subPositionId != -1){
            $sqlCheckExistChild = "
                    SELECT *
                    FROM important_level_position
                    WHERE inactive = 0
                    AND `company_id` = ".$companyId."
                    AND `area_id` = ".$areaId."
                    AND `position_id` = ".$subPositionId."
                    AND `year` = ".$year."
                    AND `month` = ".$month."
                    AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." OR id = ".$parentId.")
                ";
            $objExistChildDB  = DB::select(DB::raw($sqlCheckExistChild));
            if(count($objExistChildDB) == 1){
                $sqlDeleteSup = "
                    DELETE FROM important_level_position
                    WHERE id = ".$objExistChildDB[0]->id."
                ";
                DB::delete(DB::raw($sqlDeleteSup));
            }
        }else{

            $sqlPosition = " SELECT * FROM `position` WHERE inactive = 0 ";
            $objPositionDB = DB::select(DB::raw($sqlPosition));

            foreach($objPositionDB as $position){

                $arrMonth = commonUtils::defaultMonth();
                foreach($arrMonth as $am){
                    $sqlCheckExistChild = "
                    SELECT *
                    FROM important_level_position
                    WHERE inactive = 0
                    AND `company_id` = ".$companyId."
                    AND `area_id` = ".$areaId."
                    AND `position_id` = ".$position->id."
                    AND `year` = ".$year."
                    AND `month` = ".$am."
                    AND goal_id IN (SELECT id FROM goal WHERE parent_id = ".$parentId." OR id = ".$parentId.")
                ";
                    $objExistChildDB  = DB::select(DB::raw($sqlCheckExistChild));
                    if(count($objExistChildDB) == 1){
                        $sqlDeleteSup = "
                            DELETE FROM important_level_position
                            WHERE id = ".$objExistChildDB[0]->id."
                        ";
                        DB::delete(DB::raw($sqlDeleteSup));
                    }
                }


            }
        }
    }

    private function deleteTA($companyId, $areaId, $year, $goalId, $parentId){


        $sqlDelete = "  DELETE FROM target_area WHERE `year` = ".$year." ";

        if($companyId != ""){
            $sqlDelete .= " AND company_id = ".$companyId." ";
        }

        if($areaId != ""){
            $sqlDelete .= " AND area_id = ".$areaId." ";
        }

        if($parentId == 0){
            $tempSQL = "select id from goal where parent_id = ".$goalId." ";
            $objGoalDelete = DB::select(DB::raw($tempSQL));

            if(count($objGoalDelete) > 0){
                $subDel = " AND ( goal_id = ".$goalId." ";
                foreach($objGoalDelete as $goalDel){
                    $subDel .= " OR goal_id = ".$goalDel->id." ";
                }
                $sqlDelete .= $subDel." ) ";
            }

        }else{
            $sqlDelete .= " AND goal_id = ".$goalId." ";
        }

        DB::delete(DB::raw($sqlDelete));

    }

    private function deleteTP($companyId, $areaId,$positionId, $positionCode, $year, $month, $goalId, $parentId){

        $sqlDelete = "  DELETE FROM target_position WHERE `year` = ".$year." ";

        if($companyId != ""){
            $sqlDelete .= " AND company_id = ".$companyId." ";
        }

        if($areaId != ""){
            $sqlDelete .= " AND area_id = ".$areaId." ";
        }

        if($positionId != ""){

            $sqlPosition = " SELECT * FROM `position` WHERE inactive = 0 ";
            $objPositionDB = DB::select(DB::raw($sqlPosition));
            $subPositionId = -1;

            switch($positionCode) {

                case commonUtils::POSITION_CODE_CHT:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_GDV){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;
                case commonUtils::POSITION_CODE_CV_KHDN:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_KAM_AM){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;
                case commonUtils::POSITION_CODE_CV_KHCN:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_NVBH){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;

            }

            if($subPositionId != -1){
                $sqlDelete .= " AND ( position_id = ".$positionId." OR position_id = ".$subPositionId." ) ";
            }

        }

        if($month != ""){
            $sqlDelete .= " AND month = ".$month." ";
        }

        if($parentId == 0){
            $tempSQL = "select id from goal where parent_id = ".$goalId." ";
            $objGoalDelete = DB::select(DB::raw($tempSQL));

            if(count($objGoalDelete) > 0){
                $subDel = " AND ( goal_id = ".$goalId." ";
                foreach($objGoalDelete as $goalDel){
                    $subDel .= " OR goal_id = ".$goalDel->id." ";
                }
                $sqlDelete .= $subDel." ) ";
            }

        }else{
            $sqlDelete .= " AND goal_id = ".$goalId." ";
        }

        DB::delete(DB::raw($sqlDelete));

    }

    private function deleteTE($companyId, $areaId,$positionId, $positionCode, $year, $month, $goalId, $parentId){

        $sqlDelete = "  DELETE FROM target_employee WHERE `year` = ".$year." ";

        if($companyId != ""){
            $sqlDelete .= " AND company_id = ".$companyId." ";
        }

        if($areaId != ""){
            $sqlDelete .= " AND area_id = ".$areaId." ";
        }

        if($positionId != ""){
            $sqlPosition = " SELECT * FROM `position` WHERE inactive = 0 ";
            $objPositionDB = DB::select(DB::raw($sqlPosition));
            $subPositionId = -1;

            switch($positionCode) {

                case commonUtils::POSITION_CODE_CHT:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_GDV){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;
                case commonUtils::POSITION_CODE_CV_KHDN:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_KAM_AM){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;
                case commonUtils::POSITION_CODE_CV_KHCN:

                    foreach($objPositionDB as $position){
                        if($position->position_code == commonUtils::POSITION_CODE_NVBH){
                            $subPositionId = $position->id;
                            break;
                        }
                    }

                    break;

            }

            if($subPositionId != -1){
                $sqlDelete .= " AND ( position_id = ".$positionId." OR position_id = ".$subPositionId." ) ";
            }
        }

        if($month != ""){
            $sqlDelete .= " AND month = ".$month." ";
        }

        if($parentId == 0){
            $tempSQL = "select id from goal where parent_id = ".$goalId." ";
            $objGoalDelete = DB::select(DB::raw($tempSQL));

            if(count($objGoalDelete) > 0){
                $subDel = " AND ( goal_id = ".$goalId." ";
                foreach($objGoalDelete as $goalDel){
                    $subDel .= " OR goal_id = ".$goalDel->id." ";
                }
                $sqlDelete .= $subDel." ) ";
            }

        }else{
            $sqlDelete .= " AND goal_id = ".$goalId." ";
        }

        DB::delete(DB::raw($sqlDelete));

    }

    public function calculateAdminKPI($companyId, $areaId, $year, $month){

        $sDataUser  = Session::get('sDataUser');

        $sqlCompanies = "
            SELECT *
            FROM company
            WHERE inactive = 0
        ";

        $sqlAreas = "
            SELECT *
            FROM area
            WHERE inactive = 0
        ";

        if($sDataUser->id != 0){
            $sqlCompanies .= " AND id = ".$sDataUser->company_id." ";
            $sqlAreas .= " AND company_id = ".$sDataUser->company_id." ";
            $sqlAreas .= " AND id = ".$sDataUser->area_id." ";
        }

        if($companyId != 0){
            $sqlAreas .= " AND company_id = ".$companyId." ";
        }
        $companies = DB::select(DB::raw($sqlCompanies));
        $areas = DB::select(DB::raw($sqlAreas));

        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $data = DB::table('target_area')->where('inactive', 0)->get();

        $sqlYear = "
            SELECT year  FROM target_area GROUP BY year
        ";

        $arrYear = DB::select(DB::raw($sqlYear));

        return view('major.calculateAdminKPI')
            ->with('companies', $companies)
            ->with('areas', $areas)
            ->with('gOnes', $gOnes)
            ->with('dataYears', $arrYear)
            ->with('gTwos', $gTwos)
            ->with('selectedCompany', $companyId)
            ->with('selectedArea', $areaId)
            ->with('selectedYear', $year)
            ->with('selectedMonth', $month)
            ;
    }

    public function saveAdminKPI(Request $request){
        DB::beginTransaction();
        try {
            $currentUser = Session::get('sid');

            $post = $request->all();

            $companyId = $post['company_id'];
            $areaId = $post['area_id'];
            $year = $post['year'];
            $month = $post['month'];

            $arrMonth = ($month != 'All') ? array(0 => $month) : commonUtils::defaultMonth();

            $sqlCompany = "
                SELECT *
                FROM company
                WHERE inactive = 0
                AND id = ".$companyId."
            ";
            $objCompanyDB = DB::select(DB::raw($sqlCompany));

            if($areaId == 'All'){
                $sqlArea = "
                    SELECT *
                    FROM area
                    WHERE inactive = 0
                    AND company_id = ".$companyId."
                ";
            }else{
                $sqlArea = "
                    SELECT *
                    FROM area
                    WHERE inactive = 0
                    AND id = ".$areaId."
                ";
            }

            $objAreaDB = DB::select(DB::raw($sqlArea));

            $objPositionDB = DB::table('position')->where('position_code', commonUtils::POSITION_CODE_TQ)->where('inactive', 0)->first();

            $positionTQId = $objPositionDB->id;

            /**
             * Check data post valid
             */
            $strDept = "Phòng/Đài/MBF HCM";
            if($companyId == 0){
                Session::flash('message-errors', 'Vui lòng chọn '.$strDept.' trước khi tính toán!');
                return redirect(
                    'calculateAdminKPI'
                    .'/'.$companyId
                    .'/'.$areaId
                    .'/'.$year
                    .'/'.$month

                );
            }

            $applyDateILCor = $this->getApplyDate4Corporation($year);
            $applyDateILC = $this->getApplyDate4Company($companyId, $year, '');

            /**
             * Format KPI in this month - year - company - area
             */

            $sqlFormatILCor = "
                UPDATE important_level_corporation
                SET implement_point = 0, percent_complete = 0
                WHERE apply_date = '".$applyDateILCor."'
            ";
            DB::update(DB::raw($sqlFormatILCor));


            $sqlFormatILC = "
                UPDATE important_level_company
                SET implement_point = 0, real_percent = 0, cal_implement_point = 0
                WHERE apply_date = '".$applyDateILC."'
            ";
            DB::update(DB::raw($sqlFormatILC));

            $conditionTA    = "";
            $conditionRSTE  = "";
            $conditionRSTP  = "";
            $conditionPFTA  = "";
            if($areaId != 'All'){
                $conditionTA .= " AND area_id = ".$areaId." ";
                $conditionRSTE .= " AND te.area_id = ".$areaId." ";
                $conditionRSTP .= " AND tp.area_id = ".$areaId." ";
                $conditionPFTA .= " AND ta.area_id = ".$areaId." ";
            }

            if($month != 'All'){
                $conditionTA .= " AND month = ".$month." ";
                $conditionRSTE .= " AND te.month = ".$month." ";
                $conditionRSTP .= " AND tp.month = ".$month." ";
                $conditionPFTA .= " AND ta.month = ".$month." ";
            }

            $sqlFormatTA = "
                UPDATE target_area
                SET implement_point = 0, real_percent = 0, cal_implement_point = 0
                WHERE company_id = ".$companyId."
                AND `year` = ".$year."
            ".$conditionTA;
            DB::update(DB::raw($sqlFormatTA));

            $sqlFormatTP = "
                UPDATE target_position
                SET implement_point = 0
                WHERE company_id = ".$companyId."
                AND `year` = ".$year."
            ".$conditionTA;
            DB::update(DB::raw($sqlFormatTP));

            $sqlFormatTE = "
                UPDATE target_employee
                SET implement_point = 0
                WHERE company_id = ".$companyId."
                AND `year` = ".$year."
            ".$conditionTA;
            DB::update(DB::raw($sqlFormatTE));



            /**
             * Cal KPI employee
             */

            $sqlTEResource = "
                SELECT te.*, p.position_code, g.formula
                FROM target_employee te
                LEFT JOIN `position` p ON p.id = te.position_id
                LEFT JOIN `goal` g ON g.id = te.goal_id
                WHERE te.inactive = 0
                AND te.company_id = ".$companyId."
                AND te.year = ".$year."
                AND p.position_code != '".commonUtils::POSITION_CODE_TQ."'
            ".$conditionRSTE;
            $objTEResource = DB::select(DB::raw($sqlTEResource));

            foreach($objTEResource as $rsTE){
                $targetValue    = $rsTE->target_value;
                $implement      = $rsTE->implement;
                $goalType       = $rsTE->goal_type;
                $benchmark      = $rsTE->benchmark;

                $implementPoint = commonUtils::calculatorIP($targetValue, $implement, $benchmark, $goalType);

                $sqlUpdateTE = "
                    UPDATE target_employee
                    SET implement_point = ".$implementPoint."
                    WHERE id = ".$rsTE->id."
                ";
                DB::update(DB::raw($sqlUpdateTE));
            }

            /**
             * format implement TQ
             * calculator implement_point TQ
             */
            $sqlFormatTETQ = "
                UPDATE target_employee
                SET implement_point = 0, implement = 0
                WHERE company_id = ".$companyId."
                AND `year` = ".$year."
                AND `position_id` = ".$positionTQId."
                AND goal_id NOT IN (SELECT id FROM goal WHERE inactive = 0 AND formula = ".commonUtils::FORMULA_TU_NHAP.")
            ".$conditionTA;

            DB::update(DB::raw($sqlFormatTETQ));

            $sqlTEPerform = "
                SELECT te.*, p.position_code, g.formula
                FROM target_employee te
                LEFT JOIN `position` p ON p.id = te.position_id
                LEFT JOIN `goal` g ON g.id = te.goal_id
                WHERE te.inactive = 0
                AND te.company_id = ".$companyId."
                AND te.year = ".$year."
                AND p.position_code != '".commonUtils::POSITION_CODE_TQ."'
            ".$conditionRSTE;
            $objTEPerform = DB::select(DB::raw($sqlTEPerform));

            /**
             * Cal KPI position
             */

            $sqlPositionResource = "
                SELECT tp.*, p.position_code, g.formula
                FROM target_position tp
                LEFT JOIN `position` p ON p.id = tp.position_id
                LEFT JOIN `goal` g ON g.id = tp.goal_id
                WHERE tp.inactive = 0
                AND tp.company_id = ".$companyId."
                AND tp.year = ".$year."
                AND tp.position_id != ".$positionTQId."

            ".$conditionRSTP;
            $objPositionResource = DB::select(DB::raw($sqlPositionResource));

            foreach($objPositionResource as $rsPosition){

                $rspPositionId      = $rsPosition->position_id;

                $rspId              = $rsPosition->id;
                $rspGoalType        = $rsPosition->goal_type;
                $rspGoalId          = $rsPosition->goal_id;
                $rspAreaId          = $rsPosition->area_id;
                $rspTargetValue     = $rsPosition->target_value;
                $rspCalBenchmark    = $rsPosition->cal_benchmark;
                $rspFormula         = $rsPosition->formula;
                $rspMonth           = $rsPosition->month;

                $rspImplement = 0;
                if($rspFormula == commonUtils::FORMULA_TRUNG_BINH_CONG){
                    $numRows = 0;
                    $rspImplementTemp = 0;
                    foreach($objTEPerform as $performTE){
                        if(
                            $performTE->goal_id         == $rspGoalId
                            && $performTE->area_id      == $rspAreaId
                            && $performTE->position_id  == $rspPositionId
                            && $performTE->month        == $rspMonth
                        ){
                            $rspImplementTemp += $performTE->implement;
                            $numRows++;
                        }
                    }

                    $rspImplement = ($numRows != 0) ? $rspImplementTemp / $numRows : 0;
                }elseif($rspFormula == commonUtils::FORMULA_LAY1SO){
                    foreach($objTEPerform as $performTE){
                        if(
                            $performTE->goal_id         == $rspGoalId
                            && $performTE->area_id      == $rspAreaId
                            && $performTE->position_id  == $rspPositionId
                            && $performTE->month        == $rspMonth
                        ){
                            $rspImplement = $performTE->implement;

                            if($rspImplement != 0){
                                break;
                            }
                        }
                    }
                }else{
                    foreach($objTEPerform as $performTE){
                        if(
                            $performTE->goal_id         == $rspGoalId
                            && $performTE->area_id      == $rspAreaId
                            && $performTE->position_id  == $rspPositionId
                            && $performTE->month        == $rspMonth
                        ){
                            $rspImplement += $performTE->implement;
                        }
                    }
                }

                $rspImplementPoint = commonUtils::calculatorIP($rspTargetValue, $rspImplement, $rspCalBenchmark, $rspGoalType);

                $sqlUpdateTP = "
                        UPDATE target_position
                        SET implement_point = ".$rspImplementPoint.", implement = ".$rspImplement."
                        WHERE id = ".$rspId."
                    ";
                DB::update(DB::raw($sqlUpdateTP));

            }

            $sqlPositionPerform = "
                SELECT tp.*, p.position_code, g.formula
                FROM target_position tp
                LEFT JOIN `position` p ON p.id = tp.position_id
                LEFT JOIN `goal` g ON g.id = tp.goal_id
                WHERE tp.inactive = 0
                AND tp.company_id = ".$companyId."
                AND tp.year = ".$year."
                AND tp.position_id != ".$positionTQId."
            ".$conditionRSTP;
            $objPositionPerform = DB::select(DB::raw($sqlPositionPerform));

            $sqlPositionTPTQ = "
                SELECT tp.*, p.position_code, g.formula
                FROM target_position tp
                LEFT JOIN `position` p ON p.id = tp.position_id
                LEFT JOIN `goal` g ON g.id = tp.goal_id
                WHERE tp.inactive = 0
                AND tp.company_id = ".$companyId."
                AND tp.year = ".$year."
                AND tp.position_id = ".$positionTQId."
            ".$conditionRSTP;
            $objPositionTPTQ = DB::select(DB::raw($sqlPositionTPTQ));

            $sqlTETuNhapTQ = "
                SELECT te.*, p.position_code, g.formula
                FROM target_employee te
                LEFT JOIN `position` p ON p.id = te.position_id
                LEFT JOIN `goal` g ON g.id = te.goal_id
                WHERE te.inactive = 0
                AND te.company_id = ".$companyId."
                AND te.year = ".$year."
                AND te.position_id = ".$positionTQId."
                AND te.goal_id IN (SELECT id FROM goal WHERE inactive = 0 AND formula = ".commonUtils::FORMULA_TU_NHAP.")
            ".$conditionRSTE;
            $objTETuNhapTQ = DB::select(DB::raw($sqlTETuNhapTQ));



            foreach($objPositionTPTQ as $tpTQ){
                $tpId              = $tpTQ->id;
                $tpGoalType        = $tpTQ->goal_type;
                $tpGoalId          = $tpTQ->goal_id;
                $tpAreaId          = $tpTQ->area_id;
                $tpTargetValue     = $tpTQ->target_value;
                $tpCalBenchmark    = $tpTQ->cal_benchmark;
                $tpBenchmark       = $tpTQ->benchmark;
                $tpFormula         = $tpTQ->formula;
                $tpMonth           = $tpTQ->month;

                $ppImplement = 0;
                switch($tpFormula) {
                    case commonUtils::FORMULA_TRUNG_BINH_CONG:

                        $numRows = 0;
                        $tpImplementTemp = 0;
                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                            ){
                                $tpImplementTemp += $performPosition->implement;
                                $numRows++;
                            }
                        }
                        $ppImplement = ($numRows != 0) ? $tpImplementTemp / $numRows : 0;
                        break;

                    case commonUtils::FORMULA_LAY1SO:
                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                            ){
                                $ppImplement = $performPosition->implement;

                                if($ppImplement != 0){
                                    break;
                                }
                            }
                        }
                        break;

                    case commonUtils::FORMULA_TONG_NVBH:

                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_NVBH) == 1
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }

                        break;
                    case commonUtils::FORMULA_TONG_KAM_AM:

                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_KAM_AM) == 1
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }

                        break;

                    case commonUtils::FORMULA_TONG_CVKHCN_CVKHDN:

                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && (
                                    commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHCN) == 1
                                    || commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHDN) == 1
                                )
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }

                        break;

                    case commonUtils::FORMULA_TONG_CVKHCN_CHT:
                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && (commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHCN) == 1 || commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CHT) == 1 )
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }
                        break;
                    case commonUtils::FORMULA_TONG_GDV:

                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && (commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_GDV) == 1 || commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_GDV_K) == 1 )
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }

                        break;

                    case commonUtils::FORMULA_TONG_CVKHCN_CVKHDN_CHT:
                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && (commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHCN) == 1
                                    || commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHDN) == 1
                                    || commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CHT) == 1
                                )
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }
                        break;

                    case commonUtils::FORMULA_TONG_CVKHCN:

                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHCN) == 1
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }

                        break;
                    case commonUtils::FORMULA_TONG_CVKHDN:
                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHDN) == 1
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }
                        break;

                    case commonUtils::FORMULA_TONG_CVKHDN_CHT:

                        foreach($objPositionPerform as $performPosition){
                            if(
                                $performPosition->goal_id == $tpGoalId
                                && $performPosition->month == $tpMonth
                                && $performPosition->area_id == $tpAreaId
                                && (commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CV_KHDN) == 1
                                    || commonUtils::compareTwoString($performPosition->position_code, commonUtils::POSITION_CODE_CHT) == 1 )
                            ){
                                $ppImplement += $performPosition->implement;
                            }
                        }

                        break;

                    case commonUtils::FORMULA_TU_NHAP:

                        foreach($objTETuNhapTQ as $tqTuNhap){
                            if(
                                $tqTuNhap->goal_id      == $tpGoalId
                                && $tqTuNhap->month     == $tpMonth
                                && $tqTuNhap->area_id   == $tpAreaId
                            ){
                                $ppImplement = $tqTuNhap->implement;
                                break;
                            }
                        }

                        break;

                }

                $ppImplementPoint   = commonUtils::calculatorIP($tpTargetValue, $ppImplement, $tpCalBenchmark, $tpGoalType);
                $peTQImplementPoint = commonUtils::calculatorIP($tpTargetValue, $ppImplement, $tpBenchmark, $tpGoalType);

                $sqlUpdateTP = "
                    UPDATE target_position
                    SET implement_point = ".$ppImplementPoint.", implement = ".$ppImplement."
                    WHERE id = ".$tpId."
                ";
                DB::update(DB::raw($sqlUpdateTP));

                $sqlUpdateTETQ = "
                    UPDATE target_employee
                    SET implement_point = ".$peTQImplementPoint.", implement = ".$ppImplement."
                    WHERE company_id = ".$companyId."
                    AND area_id = ".$tpAreaId."
                    AND position_id = ".$positionTQId."
                    AND `year` = ".$year."
                    AND `month` = ".$tpMonth."
                    AND goal_id = ".$tpGoalId."
                ";
                DB::update(DB::raw($sqlUpdateTETQ));


            }

            /**
             * Cal KPI area
             */

            $sqlTAPositionPerform = "
                SELECT tp.*, p.position_code, g.formula
                FROM target_position tp
                LEFT JOIN `position` p ON p.id = tp.position_id
                LEFT JOIN `goal` g ON g.id = tp.goal_id
                WHERE tp.inactive = 0
                AND tp.company_id = ".$companyId."
                AND tp.year = ".$year."
            ".$conditionRSTP;
            $objTAPositionPerform = DB::select(DB::raw($sqlTAPositionPerform));

            $sqlTargetArea = "
                SELECT ta.*
                FROM target_area ta
                WHERE ta.inactive = 0
                AND ta.company_id = ".$companyId."
                AND ta.year = ".$year."
            ".$conditionPFTA;
            $objTargetAreaDB = DB::select(DB::raw($sqlTargetArea));

            foreach($objTargetAreaDB as $targetArea){

                $taBenchmark    = $targetArea->benchmark;
                $taCalBenchmark = $targetArea->cal_benchmark;
                $taGoalId       = $targetArea->goal_id;
                $taMonth        = $targetArea->month;
                $taAreaId       = $targetArea->area_id;
                $taId           = $targetArea->id;

                $taImplementPoint = 0;
                foreach($objTAPositionPerform as $taPP){
                    if(
                        $taPP->area_id == $taAreaId
                        && $taPP->goal_id == $taGoalId
                        && $taPP->month == $taMonth
                    ){
                        $taImplementPoint += $taPP->implement_point;
                    }
                }

                $taRealPercent = ($taBenchmark != 0) ? $taImplementPoint / $taBenchmark : 0;

                $sqlUpdateTAP = "
                    UPDATE target_area
                    SET implement_point = ".$taImplementPoint.", real_percent = ".$taRealPercent."
                    WHERE id = ".$taId."
                ";
                DB::update(DB::raw($sqlUpdateTAP));

            }

            $sqlCalTargetArea = "
                SELECT ta.*
                FROM target_area ta
                WHERE ta.inactive = 0
                AND ta.company_id = ".$companyId."
                AND ta.year = ".$year."
            ";
            if($areaId != 'All'){
                $sqlCalTargetArea .= " AND ta.area_id = ".$areaId." ";
            }
            $objCalTargetAreaDB = DB::select(DB::raw($sqlCalTargetArea));

            $sqlGoal = "
                SELECT * FROM goal WHERE inactive = 0
            ";

            $objGoalDB =DB::select(DB::raw($sqlGoal));

            foreach($objAreaDB as $area){

                $cAreaId = $area->id;

                foreach($objGoalDB as $goal){

                    $goalId = $goal->id;
                    $totalImplementPoint = 0;
                    $totalBenchmark      = 0;
                    $cCalBenchmark       = 0;

                    foreach($objCalTargetAreaDB as $calTA){
                        if(
                            $cAreaId == $calTA->area_id
                            && $goalId == $calTA->goal_id
                        ){
                            $totalImplementPoint    += $calTA->implement_point;
                            $totalBenchmark         += $calTA->benchmark;
                            $cCalBenchmark          = $calTA->cal_benchmark;
                        }
                    }

                    $cRealPercent       = ($totalBenchmark != 0) ? $totalImplementPoint / $totalBenchmark : 0;
                    $cCalImplementPoint = $cRealPercent * $cCalBenchmark;

                    $sqlUpdateCTAP = "
                        UPDATE target_area
                        SET cal_implement_point = ".$cCalImplementPoint."
                        WHERE company_id = ".$companyId."
                        AND area_id = ".$cAreaId."
                        AND `year` = ".$year."
                        AND `goal_id` = ".$goalId."
                    ";
                    DB::update(DB::raw($sqlUpdateCTAP));
                }
            }

            /**
             * Cal KPI company
             */
            $sqlTAResource = "
                SELECT ta.*
                FROM target_area ta
                WHERE ta.inactive = 0
                AND ta.company_id = ".$companyId."
                AND ta.year = ".$year."
                AND ta.month = 1
            ";

            $objTAResource = DB::select(DB::raw($sqlTAResource));

            $sqlILC = "
                SELECT *
                FROM important_level_company
                WHERE apply_date = '".$applyDateILC."'
                AND company_id = ".$companyId."
            ";
            $objILC = DB::select(DB::raw($sqlILC));

            foreach($objILC as $ilc){

                $totalILCI = 0;

                foreach($objTAResource as $rsTA){
                    if($rsTA->goal_id == $ilc->goal_id){
                        $totalILCI += $rsTA->cal_implement_point;
                    }
                }

                $dRealPerCent = ($ilc->benchmark != 0) ? $totalILCI / $ilc->benchmark : 0;
                $dCalImplementPoint =  $dRealPerCent * $ilc->cal_benchmark;
                $sqlUpdateILCI = "
                        UPDATE important_level_company
                        SET implement_point = ".$totalILCI.", real_percent = ".$dRealPerCent.", cal_implement_point = ".$dCalImplementPoint."
                        WHERE id = ".$ilc->id."
                    ";
                DB::update(DB::raw($sqlUpdateILCI));

            }

            /**
             * Cal KPI corporation
             */
            $sqlILCResource = "
                SELECT *
                FROM important_level_company
                WHERE apply_date = '".$applyDateILC."';
            ";
            $objILCResource = DB::select(DB::raw($sqlILCResource));

            $sqlILCor = "
                SELECT *
                FROM important_level_corporation
                WHERE apply_date = '".$applyDateILCor."';
            ";
            $objILCor = DB::select(DB::raw($sqlILCor));

            foreach($objILCor as $ilcor){
                $ilcTotalIP = 0;
                foreach($objILCResource as $rsILC){
                    if($rsILC->goal_id == $ilcor->goal_id){
                        $ilcTotalIP += $rsILC->cal_implement_point;
                    }
                }

                $ilcorPercentComplete = ($ilcor->benchmark != 0) ?  $ilcTotalIP / $ilcor->benchmark : 0;

                $sqlUpdateILCor = "
                        UPDATE important_level_corporation
                        SET implement_point = ".$ilcTotalIP.", percent_complete = ".$ilcorPercentComplete."
                        WHERE id = ".$ilcor->id."
                    ";
                DB::update(DB::raw($sqlUpdateILCor));
            }

            /**
             * End and output sms update
             */

            Session::flash('message-success', 'Dữ liệu đã được tính toán!');

            DB::commit();
        }catch (Exception $e) {
            DB::rollback();
        }

        return redirect(
            'calculateAdminKPI'
            .'/'.$companyId
            .'/'.$areaId
            .'/'.$year
            .'/'.$month

        );
    }
}