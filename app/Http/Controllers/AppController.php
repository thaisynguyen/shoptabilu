<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Session;

abstract class AppController extends BaseController {

	use DispatchesCommands, ValidatesRequests;
	public function __construct()
	{
		//echo Session::get('checkin');
		//die;
		if(Session::get('checkin')!=1){
			return redirect('/');
		}

	}
}
