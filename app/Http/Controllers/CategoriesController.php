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
//        print_r($data);die;
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
        $row = DB::table('unit')->where('id', $post['id'])->first();

        DB::beginTransaction();
        try{
            DB::table('unit')->where('id', $post['id'])->delete();
            #Write log
            $functionName = 'Xóa đơn vị tính (deleteUnit)';
            $value = 'Mã đơn vị: '.$row->unit_code
                    .', Tên đơn vị: '.$row->unit_name
                    .', Mô tả: '.$row->unit_description;
            $dataLog = array('function_name' => $functionName,
                            'action'         => commonUtils::ACTION_DELETE,
                            'url'            => $_SERVER['REQUEST_URI'],
                            'id_row'         => $row->id,
                            'old_value'      => $value,
                            'new_value'      => $value,
                            'created_user'   => $createdUser,
                            'created_date'   => date("Y-m-d h:i:sa"));
            DB::table('kpi_log')->insert($dataLog);
        }catch(\Exception $e){
            DB::rollback();
            Session::flash('message-errors', commonUtils::DELETE_ISSET_CHILD);
            return redirect('unitCategories');
        }
        DB::commit();
        Session::flash('message-success', commonUtils::DELETE_SUCCESSFULLY);
        return redirect('unitCategories');
    }

    /*
     * Controller for Products
     */

    public function productCategories(Request $request){
        $this->clearSession();
        $data = self::selectAndSortDataFromTable($request, 'product');
        return view('admin.categories.product.productCategories')->with('data',$data);
    }
}
