<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class CreatePositionRequest extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [
            'position_name'  => 'required',
            'position_code'   => 'required'
        ];
    }

}

