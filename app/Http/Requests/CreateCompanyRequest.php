<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class createCompanyRequest extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [
            'company_name'  => 'required',
            'company_code'   => 'required',
            'manager' => 'required'
        ];
    }

}

