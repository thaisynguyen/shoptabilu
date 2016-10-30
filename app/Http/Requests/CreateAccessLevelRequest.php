<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class CreateAccessLevelRequest extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [
            'access_level_name'  => 'required',
            'access_level_code'   => 'required',
            'level' => 'required'
        ];
    }

}

