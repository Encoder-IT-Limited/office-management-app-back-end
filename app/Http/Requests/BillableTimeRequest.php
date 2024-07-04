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
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'sometimes|required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'task' => 'sometimes|required|string',
            'site' => 'sometimes|required|string',
            'time_spent' => 'required',
//            'time_spent' => 'required|array',
//            'time_spent.hours' => 'required|integer',
//            'time_spent.minutes' => 'required|integer',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'comment' => 'sometimes|required|string',
            'screenshot' => 'sometimes|required|string',
            'given_time' => 'sometimes|required|date',
//            'given_time' => 'sometimes|required|array',
//            'given_time.hours' => 'sometimes|required|integer',
//            'given_time.minutes' => 'sometimes|required|integer',
            'is_freelancer' => 'sometimes|required|boolean',
        ];
    }
}
