<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class CreateAreaRequest  extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [
            'area_name'  => 'required',
            'area_code'   => 'required'
        ];
    }

}

