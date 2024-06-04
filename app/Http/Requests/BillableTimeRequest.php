<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillableTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'sometimes|required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'task' => 'sometimes|required|string',
            'site' => 'sometimes|required|string',
            'time_spent' => 'required|numeric',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'comment' => 'sometimes|required|string',
            'screenshot' => 'sometimes|required|string',
            'given_time' => 'sometimes|required|numeric',
            'is_freelancer' => 'sometimes|required|boolean',
        ];
    }
}
