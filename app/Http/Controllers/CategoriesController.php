<?php namespace App\Http\Controllers;
date_default_timezone_set('Asia/Ho_Chi_Minh');
use DB;
use Illuminate\Support\Facades\Route;
use Mockery\Exception;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\CreateAccessLevelRequest;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\CreateAreaRequest;
use App\Http\Requests\CreatePositionRequest;
use App\Http\Requests\CreateUnitRequest;
use App\Http\Requests\CreateGoalLevelOneRequest;
use App\Http\Requests\CreateGoalLevelTwoRequest;
use Utils\commonUtils;
use App\Services\CustomPaginator;

class CategoriesController extends AppController {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function clearSession()
    {
        Session::forget('areaChoose');
        Session::forget('areaDetailChoose');
    }

    private function selectAndSortDataFromTable(Request $request, $tableName){

        $sortDimension = ($request->get('sort') != '') ? $request->get('sort') : 'asc';
        $sortColumn = ($request->get('column') != '') ? $request->get('column') : $tableName . '_id';

        $sortDimension = ($sortDimension == '0' || $sortDimension == 'desc') ? $sortDimension : 'asc';
        $sortColumn = ($sortColumn != $tableName . '_id') ? $sortColumn : $tableName . '_id';

        $data = DB::table($tableName)   ->where('inactive', 0)
                                        ->orderby($sortColumn, $sortDimension)
                                        ->paginate(commonUtils::ITEM_PER_PAGE_DEFAULT);

        $parametersSort = array(
            'sort'      => $sortDimension,
            'column'    => $sortColumn
        );

        $data->appends($parametersSort);

        return $data;
    }


    /*
     * Controller for Unit
     */

    public function unitCategories(Request $request){
        $this->clearSession();
        $data = self::selectAndSortDataFromTable($request, 'unit');
//        commonUtils::pr($data);die;
        return view('admin.categories.unit.unitCategories')->with('data',$data);
    }

    public function addUnit(){
        return view('admin.categories.unit.addUnit');
    }

    public function saveUnit(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $data = array(  'unit_name' 	    => $post['unit_name'],
                        'unit_code' 	    => $post['unit_code'],
                        'created_user'      => $createdUser);

        $check = DB::table('unit')->where('unit_code', $post['unit_code'])->get();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => 'Mã đơn vị tính đã bị trùng. Vui lòng thử lại.'
            ));
//            return redirect('addUnit');
        } else {
            $unitId = DB::table('unit')->insertGetId($data);
            if($unitId > 0) {
                $smsSuccess = commonUtils::INSERT_SUCCESSFULLY;
                return json_encode(array(
                    "success"               => true
                    , "alert"               => commonUtils::INSERT_SUCCESSFULLY
//                , "contentPositionHtml" => $contentPositionHtml
                ));
            } else {
//                Session::flash('message-errors', commonUtils::INSERT_UNSUCCESSFULLY);
                return json_encode(array(
                    "success"  => false
                    , "alert"  => commonUtils::INSERT_UNSUCCESSFULLY
                ));
            }

//            return redirect('addUnit');

        }
    }


    public function updateUnit(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $id = $post['id'];
        $data = array(  'unit_name' 	    => $post['name'],
                        'unit_code' 	    => $post['code'],
                        'updated_user'      => $createdUser,
                        'updated_at'      =>date("Y-m-d h:i:sa"));


        $check = DB::table('unit')->where('unit_code', $post['code'])
            ->where('unit_code','!=', $post['hiddencode'])
            ->first();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY . 'Mã đơn vị tính đã bị trùng. Vui lòng thử lại.'
            ));
        } else {
            try {
                $i = DB::table('unit')->where('unit_id', $post['id'])->update($data);
                return json_encode(array(
                    "success"  => true
                    , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                    , "unit"  => $data
                ));
            } catch (Exception $e) {
                return json_encode(array(
                    "success"  => false
                    , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
                ));
            }

        }
    }

    public function deleteUnit(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');

        try{
            $data = array(
                            'inactive' 	      => 1,
                            'deleted_user'    => $createdUser,
                            'deleted_at'      => date("Y-m-d h:i:sa"));
            $i = DB::table('unit')->where('unit_id', $post['id'])->update($data);

            $arr = self::selectAndSortDataFromTable($request, 'unit');
            $unitHtml = view('admin.categories.unit.unitContent', ['data' => $arr])->render();
            return json_encode(array(
                "success"  => true
                , "alert"  => commonUtils::DELETE_SUCCESSFULLY
                , "unit"  => $unitHtml
            ));
        }catch(Exception $e){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::DELETE_UNSUCCESSFULLY
            ));
        }

    }

    /*
     * Controller for Product Type
     */

    public function productTypeCategories(Request $request){
        $this->clearSession();

        return view('admin.categories.productType.productTypeCategories');
    }

    public function productTypeTree(Request $request){
        $this->clearSession();
        $sql = 'SELECT p.product_type_id AS id
                      , p.parent_id
                      , p.product_type_name AS text
                FROM product_type AS p
                WHERE p.inactive = 0
        ';
        $data = DB::select(DB::raw($sql));

        $data = commonUtils::objectToArray($data);

        $data = commonUtils::buildTreeProductType($data, 0);

        return json_encode(array(
            "success"   => true
        , "data"    => $data
        ));

//        commonUtils::pr($data);die;
//        return view('admin.categories.productType.productTypeCategories')->with('data',$data);
    }
}
