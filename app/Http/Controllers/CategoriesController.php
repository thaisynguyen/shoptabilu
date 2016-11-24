<?php namespace App\Http\Controllers;
date_default_timezone_set('Asia/Ho_Chi_Minh');
use DB;
use Illuminate\Support\Facades\Route;
use Mockery\Exception;
use Session;
use Illuminate\Http\Request;
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
     * Controller for user ******************************************************************
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
                $arr = self::selectAndSortDataFromTable($request, 'unit');
                $unitHtml = view('admin.categories.unit.unitContent', ['data' => $arr])->render();
                return json_encode(array(
                    "success"               => true
                    , "alert"               => commonUtils::INSERT_SUCCESSFULLY
                    , "unit"                => $unitHtml
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
     * Controller for Product Type ******************************************************************
     */

    public function productTypeCategories(Request $request){
        $this->clearSession();

        return view('admin.categories.productType.productTypeCategories');
    }

    public function buildTreeComboProductType($data, $parent = null, $parentName)
    {
        $ret = '';
        foreach($data as $index => $category)
        {
//        echo 'id:'.$category['id'] . '-parent: ' .$parent . "<pre>";
            if($category['parent_id'] == $parent)
            {
                if($parentName != ''){
                    $ret .= '<option value="' . $category['id']  . '">' . $parentName . ' > ' .$category['text']  . '</option>';

                } else {
                    $ret .= '<option value="' . $category['id']  . '">' . $parentName . $category['text']  . '</option>';

                }
                if(isset($category['children'])){
                    $ret .= self::buildTreeComboProductType($category['children'], $category['id'], $category['text']);
                }

            }
        }
        return $ret;
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
//        commonUtils::pr($data);
        $optionHtml = '<select id="parent_id" class="bs-select form-control bs-select-hidden">
                        <option value="">(none)</option>';
        $optionHtml .= self::buildTreeComboProductType($data, 0, '');
        $optionHtml .= '</select>';
//        commonUtils::pr($optionHtml);die;
        return json_encode(array(
            "success"   => true
            , "data"    => $data
            , "option"  => $optionHtml
        ));

//        commonUtils::pr($data);die;
//        return view('admin.categories.productType.productTypeCategories')->with('data',$data);
    }

    public function saveProductType(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $data = array(  'parent_id'         => $post['parent_id'],
                        'product_type_name' => $post['product_type_name'],
                        'created_user'      => $createdUser);

        $check = DB::table('product_type')->where('product_type_name', $post['product_type_name'])->get();
        if(count($check) > 0){
            $arr = self::productTypeTree($request);
            return json_encode(array(
                "success"  => false
                , "alert"  => 'Tên loại sản phẩm đã bị trùng. Vui lòng thử lại.'
                , "productType" => $arr
            ));
        } else {
            $productTypeId = DB::table('product_type')->insertGetId($data);
            if($productTypeId > 0) {
                $smsSuccess = commonUtils::INSERT_SUCCESSFULLY;
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


        }
    }

    public function deleteProductType(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');

        try{
            $data = array(
                'inactive' 	      => 1,
                'deleted_user'    => $createdUser,
                'deleted_at'      => date("Y-m-d h:i:sa"));
            $i = DB::table('product_type')->where('product_type_id', $post['id'])->update($data);

            return json_encode(array(
                "success"  => true
                , "alert"  => commonUtils::DELETE_SUCCESSFULLY
            ));
        }catch(Exception $e){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::DELETE_UNSUCCESSFULLY
            ));
        }

    }


    /*
     * Controller for Subject ******************************************************************
     */
    public function subjectCategories(Request $request){
        $this->clearSession();
        $data = self::selectAndSortDataFromTable($request, 'subject');
//        commonUtils::pr($data);die;
        return view('admin.categories.subject.subjectCategories')->with('data',$data);
    }

    public function addSubject(){
        return view('admin.categories.subject.addSubject');
    }

    public function saveSubject(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $data = array(  'subject_name' 	    => $post['subject_name'],
                        'subject_code' 	    => $post['subject_code'],
                        'subject_address' 	=> $post['subject_address'],
                        'subject_telephone' => $post['subject_telephone'],
                        'is_supplier'       => $post['is_supplier'],
                        'is_customer'       => $post['is_customer'],
                        'created_user'      => $createdUser);

        $check = DB::table('subject')->where('subject_code', $post['subject_code'])->get();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => 'Mã đã bị trùng. Vui lòng thử lại.'
            ));
        } else {
            $subjectId = DB::table('subject')->insertGetId($data);
            if($subjectId > 0) {
                $arr = self::selectAndSortDataFromTable($request, 'subject');
                $subjectHtml = view('admin.categories.subject.subjectContent', ['data' => $arr])->render();
                return json_encode(array(
                    "success"               => true
                    , "alert"               => commonUtils::INSERT_SUCCESSFULLY
                    , "subject"             => $subjectHtml
                ));
            } else {
//                Session::flash('message-errors', commonUtils::INSERT_UNSUCCESSFULLY);
                return json_encode(array(
                    "success"  => false
                    , "alert"  => commonUtils::INSERT_UNSUCCESSFULLY
                ));
            }


        }
    }

    public function updateSubject(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $id = $post['id'];
        $data = array(  'subject_name' 	    => $post['name'],
                        'subject_code' 	    => $post['code'],
                        'subject_telephone' => $post['phone'],
                        'subject_address' 	=> $post['address'],
                        'is_customer' 	    => $post['is_customer'],
                        'is_supplier' 	    => $post['is_supplier'],
                        'updated_user'      => $createdUser,
                        'updated_at'        => date("Y-m-d h:i:sa"));


        $check = DB::table('subject')->where('subject_code', $post['code'])
            ->where('subject_code','!=', $post['hiddencode'])
            ->first();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY . 'Mã đơn vị tính đã bị trùng. Vui lòng thử lại.'
            ));
        } else {
            try {
                $arr = self::selectAndSortDataFromTable($request, 'subject');
                $subjectHtml = view('admin.categories.subject.subjectContent', ['data' => $arr])->render();
                $i = DB::table('subject')->where('subject_id', $post['id'])->update($data);
                return json_encode(array(
                    "success"  => true
                    , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                    , "subject"  => $subjectHtml
                ));
            } catch (Exception $e) {
                return json_encode(array(
                    "success"  => false
                    , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
                ));
            }

        }
    }

    public function deleteSubject(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');

        try{
            $data = array(
                'inactive' 	      => 1,
                'deleted_user'    => $createdUser,
                'deleted_at'      => date("Y-m-d h:i:sa"));
            $i = DB::table('subject')->where('subject_id', $post['id'])->update($data);

            $arr = self::selectAndSortDataFromTable($request, 'subject');
            $subjectHtml = view('admin.categories.subject.subjectContent', ['data' => $arr])->render();
            return json_encode(array(
                "success"  => true
                , "alert"  => commonUtils::DELETE_SUCCESSFULLY
                , "subject"  => $subjectHtml
            ));
        }catch(Exception $e){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::DELETE_UNSUCCESSFULLY
            ));
        }

    }

    /*
     * Controller for Producer ******************************************************************
     */
    public function producerCategories(Request $request){
        $this->clearSession();
        $data = self::selectAndSortDataFromTable($request, 'producer');
//        commonUtils::pr($data);die;
        return view('admin.categories.producer.producerCategories')->with('data',$data);
    }

    public function addProducer(){
        return view('admin.categories.producer.addProducer');
    }

    public function saveProducer(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $data = array(  'producer_name' 	    => $post['producer_name'],
                        'producer_code' 	    => $post['producer_code'],
                        'created_user'          => $createdUser);

        $check = DB::table('producer')->where('producer_code', $post['producer_code'])->get();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => 'Mã nhà cung cấp đã bị trùng. Vui lòng thử lại.'
            ));
//            return redirect('addUnit');
        } else {
            $producerId = DB::table('producer')->insertGetId($data);
            if($producerId > 0) {
                $arr = self::selectAndSortDataFromTable($request, 'producer');
                $producerHtml = view('admin.categories.producer.producerContent', ['data' => $arr])->render();
                return json_encode(array(
                    "success"               => true
                    , "alert"               => commonUtils::INSERT_SUCCESSFULLY
                    , "producer"            => $producerHtml
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

    public function updateProducer(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $id = $post['id'];
        $data = array(  'producer_name' 	    => $post['name'],
                        'producer_code' 	    => $post['code'],
                        'updated_user'          => $createdUser,
                        'updated_at'            =>date("Y-m-d h:i:sa"));


        $check = DB::table('producer')->where('producer_code', $post['code'])
                                    ->where('producer_code','!=', $post['hiddencode'])
                                    ->first();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY . 'Mã đơn vị tính đã bị trùng. Vui lòng thử lại.'
            ));
        } else {
            try {
                $i = DB::table('producer')->where('producer_id', $post['id'])->update($data);
                if($i > 0){
                    return json_encode(array(
                        "success"  => true
                        , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                        , "producer"  => $data
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
    }

    public function deleteProducer(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');

        try{
            $data = array(
                'inactive' 	      => 1,
                'deleted_user'    => $createdUser,
                'deleted_at'      => date("Y-m-d h:i:sa"));
            $i = DB::table('producer')->where('producer_id', $post['id'])->update($data);
            if($i > 0){
                $arr = self::selectAndSortDataFromTable($request, 'producer');
                $producerHtml = view('admin.categories.producer.producerContent', ['data' => $arr])->render();
                return json_encode(array(
                    "success"  => true
                    , "alert"  => commonUtils::DELETE_SUCCESSFULLY
                    , "producer"  => $producerHtml
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


    /*
     * Controller for Company Profile ******************************************************************
     */
    public function companyProfile(Request $request){
        $this->clearSession();
        $tableName = 'company_profile';

        $data = DB::table($tableName)->first();
//        commonUtils::pr($data->subject);die;
        return view('admin.categories.companyProfile.companyProfile')->with('data', $data);
    }

    public function saveCompanyProfile(Request $request){
        $post = $request->all();

        $image = $localFileName = $post['image_file'];

        $id = $post['company_id'];

        $uploaddir = public_path() . '\\assets\\admintheme\\upload\\images\\';
        if(isset($post['file'])){
            $file = $post['file'];
            $localFileName  = $file->getClientOriginalName();
//            Image::make($file->getRealPath())->resize(154, 30)->save($uploaddir);
            $file->move($uploaddir, $localFileName);
        }

//        echo $uploaddir; die;

        $data = array(  'subject' 	    => $post['subject'],
                        'title' 	    => $post['title'],
                        'address' 	    => $post['address'],
                        'phone_number' 	=> $post['phone_number'],
                        'fax' 	        => $post['fax'],
                        'tax_code' 	    => $post['tax_code'],
                        'website' 	    => $post['website'],
                        'email' 	    => $post['email'],
                        'image_name' 	=> $localFileName,
                        'modified'      => date("Y-m-d h:i:sa"));



            try {
                $i = DB::table('company_profile')->where('company_id', $id)->update($data);
                if($i > 0){
                    return json_encode(array(
                        "success"  => true
                        , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                        , "producer"  => $data
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

    /*
     * Controller for User ******************************************************************
     */

    public function userCategories(Request $request){
        $this->clearSession();
        $alias = 'user';
        $sortDimension = ($request->get('sort') != '') ? $request->get('sort') : 'asc';
        $sortColumn = ($request->get('column') != '') ? $request->get('column') : $alias . '_id';

        $sortDimension = ($sortDimension == '0' || $sortDimension == 'desc') ? $sortDimension : 'asc';
        $sortColumn = ($sortColumn != $alias . '_id') ? $sortColumn : $alias . '_id';

        $data = DB::table('users')   ->where('inactive', 0)
            ->orderby($sortColumn, $sortDimension)
            ->paginate(commonUtils::ITEM_PER_PAGE_DEFAULT);

        $parametersSort = array(
            'sort'      => $sortDimension,
            'column'    => $sortColumn
        );

        $data->appends($parametersSort);
        return view('admin.categories.user.userCategories')->with('data',$data);
    }

    public function addUser(){
        return view('admin.categories.unit.addUser');
    }

    public function saveUser(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $data = array(  'user_name' 	    => $post['user_name'],
                        'user_code' 	    => $post['user_code'],
                        'email' 	        => $post['email'],
                        'is_admin' 	        => $post['is_admin'],
                        'created_user'      => $createdUser);

        $check = DB::table('user')->where('email', $post['email'])->get();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => 'Email đã bị trùng. Vui lòng thử lại.'
            ));
//            return redirect('addUnit');
        } else {
            $userId = DB::table('user')->insertGetId($data);
            if($userId > 0) {
                $arr = self::selectAndSortDataFromTable($request, 'user');
                $userHtml = view('admin.categories.user.userContent', ['data' => $arr])->render();
                return json_encode(array(
                    "success"               => true
                    , "alert"               => commonUtils::INSERT_SUCCESSFULLY
                    , "user"                => $userHtml
                ));
            } else {
//                Session::flash('message-errors', commonUtils::INSERT_UNSUCCESSFULLY);
                return json_encode(array(
                    "success"  => false
                    , "alert"  => commonUtils::INSERT_UNSUCCESSFULLY
                ));
            }
        }
    }

    public function updateUser(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');
        $id = $post['id'];
        $data = array(  'user_name' 	    => $post['name'],
                        'user_code' 	    => $post['code'],
                        'email' 	        => $post['email'],
                        'is_admin' 	        => $post['is_admin'],
                        'updated_user'      => $createdUser,
                        'updated_at'        => date("Y-m-d h:i:sa"));


        $check = DB::table('user')->where('email', $post['email'])
                ->where('email','!=', $post['hiddencode'])
                ->first();
        if(count($check) > 0){
            return json_encode(array(
                "success"  => false
                , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY . 'Mã đơn vị tính đã bị trùng. Vui lòng thử lại.'
            ));
        } else {
            try {
                $i = DB::table('user')->where('user_id', $post['id'])->update($data);
                return json_encode(array(
                    "success"  => true
                    , "alert"  => commonUtils::EDIT_SUCCESSFULLY
                    , "user"  => $data
                ));
            } catch (Exception $e) {
                return json_encode(array(
                    "success"  => false
                    , "alert"  => commonUtils::EDIT_UNSUCCESSFULLY
                ));
            }

        }
    }

    public function deleteUser(Request $request){
        $post = $request->all();
        $createdUser = Session::get('sid');

        try{
            $data = array(
                'inactive' 	      => 1,
                'deleted_user'    => $createdUser,
                'deleted_at'      => date("Y-m-d h:i:sa"));
            $i = DB::table('user')->where('user_id', $post['id'])->update($data);

            $arr = self::selectAndSortDataFromTable($request, 'user');
            $userHtml = view('admin.categories.user.userContent', ['data' => $arr])->render();
            return json_encode(array(
                "success"  => true
            , "alert"  => commonUtils::DELETE_SUCCESSFULLY
            , "user"  => $userHtml
            ));
        }catch(Exception $e){
            return json_encode(array(
                "success"  => false
            , "alert"  => commonUtils::DELETE_UNSUCCESSFULLY
            ));
        }

    }
}
