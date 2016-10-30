<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class createEmployeeRequest extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [

            'code'          => 'required',
            'name'          => 'required',
            'username'      => 'required',
            'password'      => 'required',
            'company_id'    => 'required',
            'position_id'   => 'required',
            'access_level'  => 'required',
            'area_id'       => 'required',
            'password'      => 'required'
        ];
    }

}

