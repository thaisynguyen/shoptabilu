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
	private function viewProduct()
	{        
		$data = DB::table('product')
						->leftJoin('producer', 'product.producer_id', '=', 'producer.producer_id')
						->leftJoin('product_type', 'product.product_type_id', '=', 'product_type.product_type_id')
						->where('product.inactive', 0)
                        ->orderby('product.product_id', 'asc')->get();
//                        ->paginate(commonUtils::ITEM_PER_PAGE_DEFAULT);
		
		//print_r($data); die;
        return $data;
    }

    public function productCategories(Request $request){
        $this->clearSession();
        $data = self::viewProduct();
        return view('admin.categories.product.productCategories')
						->with('data',$data);						
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

    public function updateProduct(Request $request){
        $this->clearSession();
        $data = self::viewProduct();
        return view('admin.categories.product.updateProduct')
            ->with('data',$data);
    }
	
	
}
