<?php

use Utils\commonUtils;
//use PHPExcel;
//use PHPExcel_IOFactory;
//use PHPExcel_Cell;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

///use Session;

// ADMIN
Route::get('/admin', function()
{
    return View::make('login');
});


//echo Session::get('checkin'); die;
Route::post('/Log','LoginController@Log');
Route::get('/Logout','LoginController@Logout');
Route::any('/ChangePassword/{username}/{oldPass}/{newPass}/{confirmPass}','LoginController@ChangePassword');
Route::get('/adminhome','HomeController@index');

// FRONTEND
Route::get('/', function()
{
    return View::make('frontend');
});

// CATEGORIES
Route::any('currencyCategories','CategoriesController@currencyCategories');

Route::any('unitCategories','CategoriesController@unitCategories');
Route::any('addUnit','CategoriesController@addUnit');
Route::any('saveUnit','CategoriesController@saveUnit');
Route::any('updateUnit','CategoriesController@updateUnit');
Route::any('deleteUnit','CategoriesController@deleteUnit');

Route::any('productTypeCategories','CategoriesController@productTypeCategories');

Route::any('productCategories','ProductController@productCategories');
Route::any('deleteProduct','ProductController@deleteProduct');
Route::any('updateProduct','ProductController@updateProduct');
Route::any('addProduct','ProductController@addProduct');

Route::any('subjectCategories','CategoriesController@subjectCategories');
Route::any('producerCategories','CategoriesController@producerCategories');
Route::any('companyProfile','CategoriesController@companyProfile');
Route::any('languageTranslator','CategoriesController@languageTranslator');
Route::any('languageTranslatorList','CategoriesController@languageTranslatorList');
Route::any('userManagement','CategoriesController@userManagement');

Route::get('/quickSavePosition', function(){
    if(Request::ajax()){
        $post = Input::all();
        $data = array('position_name' => $post['position_name'],
            'position_code' => $post['position_code']);
        $row = DB::table('position')->insertGetId($data);
        if($row > 0){
            return Response::json($row);
        } else {
            return Response::json(false);
        }
    }
});




//EXPORT
Route::get('exportTargetCorporation/{gId}/{d}','ExportExcelController@exportTargetCorporation');
