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
        $data = self::selectAndSortDataFromTable($request, 'sales_invoice');

        $sql = 'SELECT p.product_type_id AS id
                      , p.parent_id
                      , p.product_type_name AS text
                FROM product_type AS p
                WHERE p.inactive = 0
        ';
        $productType = DB::select(DB::raw($sql));
        $sqlProduct = 'SELECT DISTINCT p.*
                FROM product AS p
                WHERE p.inactive = 0
        ';
        $product = DB::select(DB::raw($sqlProduct));

        $productTypeObject = commonUtils::objectToArray($productType);
//        commonUtils::pr($productTypeObject);die;
        $productType = commonUtils::buildTreeProductType($productTypeObject, 0);
        $optionProductType = '<select id="parent_id" class="bs-select form-control bs-select-hidden">
                                <option value="">(none)</option>';
        $optionProductType .= commonUtils::buildTreeComboProductType($productType, 0, '');
        $optionProductType .= '</select>';

//        $productObject = commonUtils::objectToArray($product);
        $optionProduct = '<select id="product_id" class="bs-select form-control bs-select-hidden">
                                <option value="">(none)</option>';

        foreach($product as $p){
            $optionProduct .= '<option value="' . $p->product_id . '" > ' . $p->product_name  . '</option>';
        }
        $optionProduct .= '</select>';

        return view('admin.sale.saleInvoice')->with('data', $data)
                                             ->with('optionProductType', $optionProductType)
                                             ->with('optionProduct', $optionProduct)
                                            ;
    }



}
