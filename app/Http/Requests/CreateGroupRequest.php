<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class CreateGroupRequest  extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [
            'group_name'  => 'required',
            'group_code'   => 'required'
        ];
    }

}

