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
Route::any('productTypeTree','CategoriesController@productTypeTree');
Route::any('addProductType','CategoriesController@addProductType');
Route::any('saveProductType','CategoriesController@saveProductType');
Route::any('updateProductType','CategoriesController@updateProductType');
Route::any('deleteProductType','CategoriesController@deleteProductType');

Route::any('productCategories','ProductController@productCategories');
Route::any('listProduct','ProductController@listProduct');
Route::any('deleteProduct','ProductController@deleteProduct');
Route::any('addProduct','ProductController@addProduct');
Route::any('updateProduct','ProductController@updateProduct');
Route::any('viewProduct/{id}','ProductController@viewProduct');
Route::any('getAllArrayCombobox','ProductController@getAllArrayCombobox');

Route::any('listProductDetail','ProductController@listProductDetail');
Route::any('deleteProductDetail','ProductController@deleteProductDetail');
Route::any('addProductDetail','ProductController@addProductDetail');
Route::any('updateProductDetail','ProductController@updateProductDetail');


Route::any('subjectCategories','CategoriesController@subjectCategories');
Route::any('addSubject','CategoriesController@addSubject');
Route::any('saveSubject','CategoriesController@saveSubject');
Route::any('updateSubject','CategoriesController@updateSubject');
Route::any('deleteSubject','CategoriesController@deleteSubject');

Route::any('producerCategories','CategoriesController@producerCategories');
Route::any('addProducer','CategoriesController@addProducer');
Route::any('saveProducer','CategoriesController@saveProducer');
Route::any('updateProducer','CategoriesController@updateProducer');
Route::any('deleteProducer','CategoriesController@deleteProducer');

Route::any('companyProfile','CategoriesController@companyProfile');
Route::any('saveCompanyProfile','CategoriesController@saveCompanyProfile');

Route::any('languageTranslator','CategoriesController@languageTranslator');
Route::any('languageTranslatorList','CategoriesController@languageTranslatorList');

Route::any('userManagement','CategoriesController@userCategories');
Route::any('saveUser','CategoriesController@saveUser');
Route::any('updateUser','CategoriesController@updateUser');
Route::any('deleteUser','CategoriesController@deleteUser');

Route::any('currencyCategories','CategoriesController@currencyCategories');
Route::any('addCurrency','CategoriesController@addCurrency');
Route::any('saveCurrency','CategoriesController@saveCurrency');
Route::any('updateCurrency','CategoriesController@updateCurrency');
Route::any('deleteCurrency','CategoriesController@deleteCurrency');

// SALE

Route::any('saleInvoice','SaleController@saleInvoice');
Route::any('getLastSaleInvoiceId','SaleController@getLastSaleInvoiceId');

//EXPORT
Route::get('exportTargetCorporation/{gId}/{d}','ExportExcelController@exportTargetCorporation');
