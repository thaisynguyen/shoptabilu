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
                                        ->paginate(2);

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
        return view('admin.categories.unit.unitCategories')->with('data',$data);
    }

    public function addUnit(){
        return view('categories.addUnit');
    }

    public function saveUnit(createUnitRequest $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $data = array(  'unit_name' 	    => $post['unit_name'],
                        'unit_code' 	    => $post['unit_code'],
                        'unit_description' 	=> $post['unit_description'],
                        'created_user'      => $createdUser);

        $check = DB::table('unit')->where('unit_code', $post['unit_code'])->get();
        if(count($check) > 0){
            Session::flash('message-errors', 'Mã đơn vị đã bị trùng. Vui lòng thử lại.');
            return redirect('addUnit');
        } else {
            DB::beginTransaction();
            try {
                $unitId = DB::table('unit')->insertGetId($data);
                if($unitId > 0) {
                    $functionName = 'Thêm đơn vị (addUnit)';
                    $value = 'Mã đơn vị: '.$post['unit_code']
                            .', Tên đơn vị: '.$post['unit_name']
                            .', Mô tả: '.$post['unit_description'];
                    $dataLog = array('function_name' => $functionName,
                                    'action'         => commonUtils::ACTION_INSERT,
                                    'url'            => $_SERVER['REQUEST_URI'],
                                    'id_row'         => $unitId,
                                    'old_value'      => $value,
                                    'new_value'      => $value,
                                    'created_user'   => $createdUser,
                                    'created_date'   => date("Y-m-d h:i:sa"));
                    $log = DB::table('kpi_log')->insert($dataLog);
                }
            } catch (Exception $e) {
                DB::rollback();
                Session::flash('message-errors', commonUtils::INSERT_UNSUCCESSFULLY);
                return redirect('addUnit');
            }
            DB::commit();
            Session::flash('message-success', commonUtils::INSERT_SUCCESSFULLY);
            return redirect('addUnit');
        }
    }

    public function updateUnit($id){
        $row = DB::table('unit')->where('id', $id)->first();
        return view('categories.updateUnit')->with('row',$row);
    }

    public function editUnit(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $data = array(  'unit_name' 	    => $post['unit_name'],
                        'unit_code' 	    => $post['unit_code'],
                        'unit_description' 	=> $post['unit_description'],
                        'updated_user'      => $createdUser,
                        'updated_date'      =>date("Y-m-d h:i:sa"));

        $oldId = DB::table('unit')->where('unit_code','=',$post['unit_code_hide'])
            ->where('inactive',0)
            ->first();
        $check = DB::table('unit')->where('unit_code', $post['unit_code'])
            ->where('unit_code','!=',$post['unit_code_hide'])
            ->first();
        if(count($check) > 0){
            Session::flash('message-errors', 'Mã đơn vị tính đã bị trùng. Vui lòng thử lại.');
            return redirect('updateUnit/'.$oldId->id);
        } else {
            DB::beginTransaction();
            try {
                $i = DB::table('unit')->where('id', $post['id'])->update($data);
                $unit = DB::table('unit')->where('unit_code', $post['unit_code'])->first();
                if(count($i)) {
                    $functionName = 'Sửa đơn vị tính (editUnit)';
                    $dataLog = array('function_name' => $functionName,
                                    'action'         => commonUtils::ACTION_EDIT,
                                    'url'            => $_SERVER['REQUEST_URI'],
                                    'id_row'         => $unit->id,
                                    'old_value'      => 'Mã đơn vị: '.$post['unit_code_hide']
                                                        .', Tên đơn vị: '.$post['unit_name_hide']
                                                        .', Mô tả: '.$post['unit_description_hide'],
                                    'new_value'      => 'Mã đơn vị: '.$post['unit_code']
                                                        .', Tên đơn vị: '.$post['unit_name']
                                                        .', Mô tả: '.$post['unit_description'],
                                    'created_user'   => $createdUser,
                                    'created_date'   => date("Y-m-d"));
                    $log = DB::table('kpi_log')->insert($dataLog);
                }
            } catch (Exception $e) {
                DB::rollback();
                Session::flash('message-errors', commonUtils::EDIT_UNSUCCESSFULLY);
                return redirect('updateUnit'.$oldId->id);
            }
            DB::commit();
            Session::flash('message-success', commonUtils::EDIT_SUCCESSFULLY);
            return redirect('unitCategories');
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


}
