<?php namespace App\Http\Controllers;
use DB;
use Utils\commonUtils;
use Illuminate\Http\Request;
use Session;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller {

    //reportSummaryByArea
    public function reportSummaryByArea(){
        $companies = DB::table('company')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();

        return view('report.reportSummaryByArea')
            ->with('company', $companies)
            ->with('gOnes', $gOnes)
            ->with('gTwos', $gTwos);
    }

    //reportDetailByArea
    public function reportDetailByArea(){
        $sDataUser  = Session::get('sDataUser');

        if($sDataUser->id == 0){
            $companies = DB::table('company')->where('inactive', 0)->get();
        }else{
            $companies = DB::table('company')->where('inactive', 0)->where('id', $sDataUser->company_id)->get();
        }

        $area = array();

        if(count($companies) > 0){
            if($sDataUser->id != 0){
                $area = DB::table('area')->where('inactive', 0)->where('company_id', $sDataUser->company_id)->get();
            }else{
                $area = DB::table('area')->where('inactive', 0)->get();
            }

        }
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $data = DB::table('target_area')->where('inactive', 0)->get();
        $year = array();
        if(count($data) > 0){
            $year = commonUtils::getArrYear($data);
        }
        return view('report.reportDetailByArea')
            ->with('company', $companies)
            ->with('area', $area)
            ->with('gOnes', $gOnes)
            ->with('year', $year)
            ->with('gTwos', $gTwos);
    }

    //reportPlanByArea
    public function reportPlanByArea($companyId, $areaId, $month, $year, $goalId){

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


        return view('report.reportPlanByArea')
            ->with('companies', $companies)
            ->with('areas', $areas)
            ->with('gOnes', $gOnes)
            ->with('dataYears', $arrYear)
            ->with('gTwos', $gTwos)
            ->with('selectedCompany', $companyId)
            ->with('selectedArea', $areaId)
            ->with('selectedGoal', $goalId)
            ->with('selectedYear', $year)
            ->with('selectedMonth', $month)
            ;
    }
    public function reportImplementByArea($companyId, $areaId, $month, $year, $goalId){

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


        return view('report.reportImplementByArea')
            ->with('companies', $companies)
            ->with('areas', $areas)
            ->with('gOnes', $gOnes)
            ->with('dataYears', $arrYear)
            ->with('gTwos', $gTwos)
            ->with('selectedCompany', $companyId)
            ->with('selectedArea', $areaId)
            ->with('selectedGoal', $goalId)
            ->with('selectedYear', $year)
            ->with('selectedMonth', $month)
            ;
    }

    //reportDetailByPosition
    public function reportDetailByPosition(){
        $companies = DB::table('company')->where('inactive', 0)->get();
        $area = DB::table('area')->where('inactive', 0)->get();
        $positions = DB::table('position')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $data = DB::table('target_position')->where('inactive', 0)->get();
        $year = array();
        if(count($data) > 0){
            $year = commonUtils::getArrYear($data);
        }
        return view('report.reportDetailByPosition')
            ->with('company', $companies)
            ->with('positions', $positions)
            ->with('area', $area)
            ->with('year', $year)
            ->with('gOnes', $gOnes)
            ->with('gTwos', $gTwos);
    }

    //reportKPIResult
    public function reportKPIResult(){
        $companies = DB::table('company')->where('inactive', 0)->get();
        $area = DB::table('area')->where('inactive', 0)->get();
        $positions = DB::table('position')->where('inactive', 0)->get();
        $group = DB::table('group')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $data = DB::table('target_employee')->where('inactive', 0)->get();
        $year = array();
        if(count($data) > 0){
            $year = commonUtils::getArrYear($data);
        }
        return view('report.reportKPIResult')
            ->with('company', $companies)
            ->with('positions', $positions)
            ->with('group', $group)
            ->with('year', $year)
            ->with('gOnes', $gOnes)
            ->with('area', $area)
            ->with('gTwos', $gTwos);
    }

    //reportImplementByCompany
    public function reportImplementByCompany(){
        $company = DB::table('company')->where('inactive', 0)->get();
        $sql = 'SELECT DISTINCT apply_date FROM important_level_company ORDER BY apply_date';
        $data = DB::select(DB::raw($sql));
        return view('report.reportImplementByCompany')->with('company', $company)->with('applyDate', $data);
    }

    public function reportShowAreaAffectToCompany(){
        $sDataUser  = Session::get('sDataUser');

        if($sDataUser->id == 0){
            $companies = DB::table('company')->where('inactive', 0)->get();
        }else{
            $companies = DB::table('company')->where('inactive', 0)->where('id', $sDataUser->company_id)->get();
        }

        $area = array();
        $applyDate = array();
        if(count($companies) > 0){
            if($sDataUser->id != 0){
                $area = DB::table('area')->where('inactive', 0)->where('company_id', $sDataUser->company_id)->get();
                $selectApplyDate = 'select distinct apply_date
                            from important_level_company
                            where company_id = '.$sDataUser->company_id;
                $applyDate = DB::select(DB::raw($selectApplyDate));
            }else{
                $area = DB::table('area')->where('inactive', 0)->get();
                $selectApplyDate = 'select distinct apply_date
                            from important_level_company';
                $applyDate = DB::select(DB::raw($selectApplyDate));
            }

        }

        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();

        return view('report.reportShowAreaAffectToCompany')
            ->with('company', $companies)
            ->with('area', $area)
            ->with('gOnes', $gOnes)
            ->with('apply_date', $applyDate)
            ->with('gTwos', $gTwos);
    }

    //reportImplementByEmployee
    public function reportImplementByEmployee(){
        $sDataUser  = Session::get('sDataUser');

        if($sDataUser->id == 0){
            $companies = DB::table('company')->where('inactive', 0)->get();
        }else{
            $companies = DB::table('company')->where('inactive', 0)->where('id', $sDataUser->company_id)->get();
        }

        $area = array();

        if(count($companies) > 0){
            if($sDataUser->id != 0){
                $area = DB::table('area')->where('inactive', 0)->where('company_id', $sDataUser->company_id)->get();
            }else{
                $area = DB::table('area')->where('inactive', 0)->get();
            }

        }
        $positions = DB::table('position')->where('inactive', 0)->get();
        $group = DB::table('group')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $data = DB::table('target_employee')->where('inactive', 0)->get();
        $year = array();
        if(count($data) > 0){
            $year = commonUtils::getArrYear($data);
        }
        return view('report.reportImplementByEmployee')
            ->with('company', $companies)
            ->with('positions', $positions)
            ->with('year', $year)
            ->with('group', $group)
            ->with('area', $area)
            ->with('gOnes', $gOnes)
            ->with('gTwos', $gTwos);
    }

    //reportImplementByPosition
    public function reportImplementByPosition(){
        $sDataUser  = Session::get('sDataUser');

        if($sDataUser->id == 0){
            $companies = DB::table('company')->where('inactive', 0)->get();
        }else{
            $companies = DB::table('company')->where('inactive', 0)->where('id', $sDataUser->company_id)->get();
        }

        $area = array();

        if(count($companies) > 0){
            if($sDataUser->id != 0){
                $area = DB::table('area')->where('inactive', 0)->where('company_id', $sDataUser->company_id)->get();
            }else{
                $area = DB::table('area')->where('inactive', 0)->get();
            }

        }
        $group = DB::table('group')->where('inactive', 0)->get();
        $positions = DB::table('position')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();
        $data = DB::table('target_position')->where('inactive', 0)->get();
        $year = array();
        if(count($data) > 0){
            $year = commonUtils::getArrYear($data);
        }

        return view('report.reportImplementByPosition')
            ->with('company', $companies)
            ->with('area', $area)
            ->with('positions', $positions)
            ->with('year', $year)
            ->with('group', $group)
            ->with('gOnes', $gOnes)
            ->with('gTwos', $gTwos);
    }

    //reportImplementByMonth
    public function reportImplementByMonth(){
        $companies = DB::table('company')->where('inactive', 0)->get();
        $gOnes = DB::table('goal')->where('parent_id', 0)->where('inactive', 0)->get();
        $gTwos = DB::table('goal')->where('parent_id', '<>', 0)->where('inactive', 0)->get();

        $sql = 'SELECT DISTINCT apply_date FROM important_level_company ORDER BY apply_date';
        $data = DB::select(DB::raw($sql));

        return view('report.reportImplementByMonth')
            ->with('company', $companies)
            ->with('applyDate', $data)
            ->with('gOnes', $gOnes)
            ->with('gTwos', $gTwos);
    }

}