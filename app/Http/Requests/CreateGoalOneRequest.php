<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
class createGoalOneRequest extends Request{
    public function authorize(){
        return true;
    }

    public function rules()
    {
        return [
            'goal_name'  => 'required',
            'goal_code'   => 'required|unique:goal,goal_code'
        ];
    }

}

