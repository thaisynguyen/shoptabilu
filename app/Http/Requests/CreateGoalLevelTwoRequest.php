<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class CreateGoalLevelTwoRequest extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [
            'goal_name'  => 'required',
            'parent_id'  => 'required',
            'goal_type'  => 'required',
            'goal_code'  => 'required'
        ];
    }

}

