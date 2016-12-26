<?php namespace App\Http\Controllers;
date_default_timezone_set('Asia/Ho_Chi_Minh');
use DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Input;
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
                    ->orderBy($sortColumn, $sortDirection)
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
		
		if ($id != -1) // check is update or add View   -1: Add, other: Update
		{
			// get product by id
			$product = DB::table('product')                    
						->leftJoin('producer', 'product.producer_id', '=', 'producer.producer_id')
						->leftJoin('product_type', 'product.product_type_id', '=', 'product_type.product_type_id')
						->where('product.product_id', '=', $id)
						->get();
			
			return view('admin.categories.product.updateProduct')
							->with('product', $product[0])
							->with('arrayUnit', $arrayUnit)
							->with('arrayProducer', $arrayProducer)
							->with('arrayProductType', $arrayProductType);
		}
		else
		{
			return view('admin.categories.product.addProduct')
							->with('arrayUnit', $arrayUnit)
							->with('arrayProducer', $arrayProducer)
							->with('arrayProductType', $arrayProductType);
		}
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
                return json_encode(array(
                    "success"  => true
                , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                ));
            } else {                
                return json_encode(array(
                    "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
                ));	
            }
        } catch (Exception $e) {            
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
            ));
        }
    }
	
	public function addProduct(Request $request)
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
						
		try {
			$productId = DB::table('product')->insertGetId($data);
			if($productId > 0) {
				return json_encode(array(
					"success"               => true
					, "alert"               => commonUtils::INSERT_SUCCESSFULLY
				));
			} else {
				return json_encode(array(
					"success"  => false
					, "alert"  => commonUtils::INSERT_UNSUCCESSFULLY
				));
			}			
        } catch (Exception $e) {            
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::INSERT_UNSUCCESSFULLY
            ));
        }
    }
	
	public function getArrUnit()
	{
		$arrayUnit = DB::table('unit')
					->select('unit_id AS key', 'unit_name AS value')
					->where('inactive', '=', 0)
                    ->get();
					
		// convert to array
        $arrayUnit = commonUtils::objectToArray($arrayUnit);
		
		return $arrayUnit;
	}
	
	public function getArrCurrency()
	{
		$arrayCurrency = DB::table('currency')
					->select('currency_id AS key', 'currency_code AS value')
					->where('inactive', '=', 0)
                    ->get();
					
		// convert to array
        $arrayCurrency = commonUtils::objectToArray($arrayCurrency);
		
		return $arrayCurrency;
	}
	
	public function getAllArrayCombobox(Request $request)
	{
		$data_json = json_encode(array(           
			"arrUnit" => self::getArrUnit()
			, "arrCurrency" => self::getArrCurrency()
        ));

        return $data_json;
	}
	
	
	/*
     * Controller for Products Detail
     */
    public function listProductDetail(Request $request)
	{        
		$post = $request->all();

		// query data follow datatable
        $data = DB::table('product_detail')
                    ->select(	'product_detail.product_detail_id',
								'product_detail.barcodeid',
								'product_detail.quantity',								
								'unit.unit_code',													
								'currency.currency_code',
								'product_detail.purchase_price',
								'product_detail.sale_price',
								DB::raw("DATE_FORMAT(product_detail.apply_date, '%d-%m-%Y') as apply_date"),
								//'product_detail.apply_date',
								'product_detail.warranty_label',
								'product_detail.warranty_period',
								'product_detail.description',
								'unit.unit_id',
								'currency.currency_id')
					->leftJoin('product', 'product.product_id', '=', 'product_detail.product_id')
					->leftJoin('unit', 'unit.unit_id', '=', 'product_detail.unit_id')
					->leftJoin('currency', 'currency.currency_id', '=', 'product_detail.currency_id')
					->where('product_detail.inactive', 0)
					->where('product_detail.product_id', $post['product_id'])                    
                    ->orderBy('product_detail_id', 'asc')                    
                    ->get();

		// convert to array
        $data = commonUtils::objectToArray($data);
		$data = commonUtils::arrayToIndexedArray($data);
		
		//commonUtils::pr($data); die;			
		
		// convert to json
        $data_json = json_encode(array(          
			"data" => $data
        ));

        return $data_json;
    }
	
	public function deleteProductDetail(Request $request)
    {
        $post = $request->all();
        $createdUser = Session::get('sid');

        try{
            $data = array(
                'inactive' 	      => 1,
                'deleted_user'    => $createdUser,
                'deleted_at'      => date("Y-m-d h:i:sa"));
									
            $i = DB::table('product_detail')->where('product_detail_id', $post['id'])->update($data);			
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
	
	public function updateProductDetail(Request $request)
    {
        $post = $request->all();
        $createdUser = Session::get('sid');
		
        $data = array(  'product_detail_id' => $post['product_detail_id'],
                        'barcodeid'  		=> $post['barcodeid'],
						'quantity'  		=> $post['quantity'],
						'unit_id'  			=> $post['unit_id'],
						'currency_id'  		=> $post['currency_id'],
						'purchase_price'  	=> $post['purchase_price'],
						'sale_price'  		=> $post['sale_price'],
						'apply_date'  		=> commonUtils::formatDateYMDDelimiter($post['apply_date'],"-"),
						'warranty_label'  	=> $post['warranty_label'],
						'warranty_period'  	=> $post['warranty_period'],
						'description'   	=> $post['description'],
                        'updated_user'      => $createdUser,
                        'updated_at'      	=> date("Y-m-d h:i:sa"));

//        $check = DB::table('product')
//							->where('product_code', '=',$post['product_code'])
//                            ->where('product_id', '!=',$post['product_id'])
//							->first();

        try {
            $i = DB::table('product_detail')
                    ->where('product_detail_id', '=', $post['product_detail_id'])
                    ->update($data);
					
            if($i > 0){
                return json_encode(array(
                    "success"  => true
                , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                ));
            } else {                
                return json_encode(array(
                    "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
                ));	
            }
        } catch (Exception $e) {            
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
            ));
        }
    }
	
	public function addProductDetail(Request $request)
    {
        $post = $request->all();
        $createdUser = Session::get('sid');
				
        $data = array(  'product_id'  		=> $post['product_id'],
						'barcodeid'  		=> $post['barcodeid'],
						'quantity'  		=> $post['quantity'],
						'unit_id'  			=> $post['unit_id'],
						'currency_id'  		=> $post['currency_id'],
						'purchase_price'  	=> $post['purchase_price'],
						'sale_price'  		=> $post['sale_price'],
						'apply_date'  		=> commonUtils::formatDateYMDDelimiter($post['apply_date'],"-"),
						'warranty_label'  	=> $post['warranty_label'],
						'warranty_period'  	=> $post['warranty_period'],
						'description'   	=> $post['description'],
                        'updated_user'      => $createdUser,
                        'updated_at'      	=> date("Y-m-d h:i:sa"));
       
        try {
			$productDetailId = DB::table('product_detail')->insertGetId($data);
			if($productDetailId > 0) {				
				return json_encode(array(
					"success"               => true
					, "product_detail_id"	=> $productDetailId
					, "alert"               => commonUtils::INSERT_SUCCESSFULLY
				));
			} else {
				return json_encode(array(
					"success"  => false
					, "alert"  => commonUtils::INSERT_UNSUCCESSFULLY
				));
			}			
        } catch (Exception $e) {            
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::INSERT_UNSUCCESSFULLY
            ));
        }
    }

    public function uploadProductImage(Request $request)
    {
        //$post = $request->all();
        //$file = $post['filename'];
        /*$file = Input::file('file');

        $upload = new Upload;


        try {
            $upload->process($file);
        } catch(Exception $exception){
            // Something went wrong. Log it.
            Log::error($exception);
            $error = array(
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'error' => $exception->getMessage(),
            );
            // Return error
            return Response::json($error, 400);
        }

        // If it now has an id, it should have been successful.
        if ( $upload->id ) {
            $newurl = URL::asset($upload->publicpath().$upload->filename);

            // this creates the response structure for jquery file upload
            $success = new stdClass();
            $success->name = $upload->filename;
            $success->size = $upload->size;
            $success->url = $newurl;
            $success->thumbnailUrl = $newurl;
            $success->deleteUrl = action('UploadController@delete', $upload->id);
            $success->deleteType = 'DELETE';
            $success->fileID = $upload->id;

            return Response::json(array( 'files'=> array($success)), 200);
        } else {
            return Response::json('Error', 400);
        }*/

/*        $post = $request->all();
        $file = $post['files'];

        $json = array(
            'files' => array()
        );

        $filename = $file[0]->getClientOriginalName().".".$file[0]->getClientOriginalExtension();
        $json['files'][] = array(
            'name' => $filename,
            'size' => $file[0]->getSize(),
            'type' => 'image/jpeg',
            'url' => '/uploads/files/'.$filename,
            'deleteType' => 'DELETE',
            'deleteUrl' => self::$route.'/deleteFile/'.$filename,
        );

        $upload = $file[0]->move( public_path().'\\assets\\admintheme\\upload\\images\\', $filename );
        //$upload = $file->move( public_path().'/uploads/files', $filename );

        return Response::json($json);
*/


        $post = $request->all();
        //commonUtils::pr($post);die;

        $uploaddir = public_path() . '\\assets\\admintheme\\upload\\images\\';
        if(isset($post['files'])){
            $file = $post['files'];
            $localFileName  = $file[0]->getClientOriginalName();
            //commonUtils::pr($localFileName);die;
//            Image::make($file->getRealPath())->resize(154, 30)->save($uploaddir);
            $result = $file[0]->move($uploaddir, $localFileName);


            try {

                return json_encode(array(
                    "files"  => $uploaddir . $localFileName
                , "thumbnailUrl"  => $uploaddir . $localFileName
                , "name"  => $localFileName
                , "type" => "image/jpeg"
                , "size" => 620888
                , "deleteUrl" => "https://jquery-file-upload.appspot.com/image%2Fjpeg/1499352104/Tulips.jpg"
                , "deleteType" => "DELETE"

                ));

            } catch (Exception $e) {
                return json_encode(array(
                    "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
                ));
            }
        }


    }
}
