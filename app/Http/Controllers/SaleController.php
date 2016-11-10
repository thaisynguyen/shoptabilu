<?php namespace App\Http\Controllers;
date_default_timezone_set('Asia/Ho_Chi_Minh');
use DB;
use Illuminate\Support\Facades\Route;
use Mockery\Exception;
use Session;
use Illuminate\Http\Request;
use Utils\commonUtils;
use App\Services\CustomPaginator;

class SaleController extends AppController {

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

    public function saleInvoice(Request $request){
        $this->clearSession();
        $data = self::selectAndSortDataFromTable($request, 'unit');
//        commonUtils::pr($data);die;
        return view('admin.sale.saleInvoice')->with('data',$data);
    }



}
