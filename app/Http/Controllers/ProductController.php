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

class ProductController extends AppController {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function clearSession()
    {
        Session::forget('areaChoose');
        Session::forget('areaDetailChoose');
    }


    /*
     * Controller for Products
     */
    public function listProduct(Request $request)
	{        
		$post = $request->all();			
		
		// get name of sort column
		$sortColumn = $post['order'][0]['column'];
        $sortColumn = ($sortColumn != 0) ? $sortColumn : 1;
		$sortColumn = $post['columns'][$sortColumn]['data'];
		
		// get direction of sort column
		$sortDirection = $post['order'][0]['dir'];
		
		// get search value
		$searchValue = $post['search']['value'];

		// query data follow datatable
        $data = DB::table('product')
                    ->select('product.product_id', 'product.product_code', 'product.product_name', 'product.barcode', 'product_type.product_type_name', 'producer.producer_name', 'product.weight', 'product.color')
					->leftJoin('producer', 'product.producer_id', '=', 'producer.producer_id')
					->leftJoin('product_type', 'product.product_type_id', '=', 'product_type.product_type_id')
					->where('product.inactive', 0)
                    ->where(function($query) use ($searchValue){
                         $query->where('product.product_name', 'like', '%'.$searchValue.'%')
                               ->orWhere('product_type.product_type_name', 'like', '%'.$searchValue.'%');
                    })
                    ->orderby($sortColumn, $sortDirection)
                    ->offset($post['start'])
                    ->limit($post['length'])
                    ->get();

		// sum of $data
        $dataTotal = DB::table('product')
                        ->select('product.product_id')
						->leftJoin('producer', 'product.producer_id', '=', 'producer.producer_id')
						->leftJoin('product_type', 'product.product_type_id', '=', 'product_type.product_type_id')
                        ->where('product.inactive', 0)
                        ->where(function($query) use ($searchValue){
                             $query->where('product.product_name', 'like', '%'.$searchValue.'%')
                                ->orWhere('product_type.product_type_name', 'like', '%'.$searchValue.'%');
                        })
                        ->get();
        $recordsTotal = count($dataTotal);

		// convert to array
        $data = commonUtils::objectToArray($data);
		
		// convert to json
        $data_json = json_encode(array(
            "recordsTotal" => $recordsTotal
        , "recordsFiltered" => $recordsTotal		
        , "data" => $data
        ));

        return $data_json;
    }

    public function productCategories(Request $request){
        $this->clearSession();        
        return view('admin.categories.product.productCategories');						
    }

    public function deleteProduct(Request $request)
    {
        $post = $request->all();
        $createdUser = Session::get('sid');

        try{
            $data = array(
                'inactive' 	      => 1,
                'deleted_user'    => $createdUser,
                'deleted_at'      => date("Y-m-d h:i:sa"));
									
            $i = DB::table('product')->where('product_id', $post['id'])->update($data);			
            if($i > 0){
                return json_encode(array(
                    "success"  => true
                    , "alert"  => commonUtils::DELETE_SUCCESSFULLY
                ));
            } else {
                return json_encode(array(
                    "success"  => false
                    , "alert"  => commonUtils::DELETE_UNSUCCESSFULLY
                ));
            }
        }catch(Exception $e){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::DELETE_UNSUCCESSFULLY
            ));
        }
    }

    public function viewProduct($id){
        $this->clearSession();			
        //$createdUser = Session::get('sid');
		
        // get product by id
        $product = DB::table('product')                    
					->leftJoin('producer', 'product.producer_id', '=', 'producer.producer_id')
					->leftJoin('product_type', 'product.product_type_id', '=', 'product_type.product_type_id')
					->where('product.product_id', '=', $id)
                    ->get();
		
		$arrayUnit = DB::table('unit')
					->select('unit_id AS key', 'unit_name AS value')
					->where('inactive', '=', 0)
                    ->get();
					
		$arrayProducer = DB::table('producer')
					->select('producer_id AS key', 'producer_name AS value')
					->where('inactive', '=', 0)
                    ->get();

        $arrayProductType = DB::table('product_type')
            ->select('product_type_id AS key', 'parent_id AS key_parent', 'product_type_name AS value')
            ->where('inactive', '=', 0)
            ->get();
					
		// convert to array
        $arrayUnit = commonUtils::objectToArray($arrayUnit);
		$arrayProducer = commonUtils::objectToArray($arrayProducer);
        $arrayProductType = commonUtils::objectToArray($arrayProductType);
					
		// get product detail
		//commonUtils::pr($arrayProductType); die;
		
        return view('admin.categories.product.updateProduct')
						->with('product', $product[0])
						->with('arrayUnit', $arrayUnit)
						->with('arrayProducer', $arrayProducer)
                        ->with('arrayProductType', $arrayProductType);
    }
	
	public function updateProduct(Request $request)
    {
        $post = $request->all();
        $createdUser = Session::get('sid');
        
        $data = array(  'product_name' 	    => $post['product_name'],
                        'product_code' 	    => $post['product_code'],
						'product_type_id' 	=> $post['product_type_id'],
						'producer_id' 	    => $post['producer_id'],
						'base_unit_id' 	    => $post['base_unit_id'],
						'barcode' 	    	=> $post['barcode'],
						'trademark' 	    => $post['trademark'],
						'model' 	    	=> $post['model'],
						'color' 	    	=> $post['color'],
						'weight' 	    	=> $post['weight'],
						'length' 	    	=> $post['length'],
						'width' 	    	=> $post['width'],
						'height' 	    	=> $post['height'],
						'short_description' => $post['short_description'],
						'long_description' 	=> $post['long_description'],
                        'updated_user'      => $createdUser,
                        'updated_at'      	=> date("Y-m-d h:i:sa"));

//        $check = DB::table('product')
//							->where('product_code', '=',$post['product_code'])
//                            ->where('product_id', '!=',$post['product_id'])
//							->first();

        try {
            $i = DB::table('product')
                    ->where('product_id', '=', $post['product_id'])
                    ->update($data);
            if($i > 0){
//                    echo '222'; die;
                return json_encode(array(
                    "success"  => true
                , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                ));
            } else {
            //    echo '333'; die;
                return json_encode(array(
                    "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
                ));
            }
        } catch (Exception $e) {
            //echo '444'; die;
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
            ));
        }
    }
	
}
