<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
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
            'id' => 'sometimes|required|exists:tasks,id',

            'title' => 'required|string',
            'description' => 'required|string',
            'reference' => 'sometimes|required|string',
            'project_id' => 'required|exists:projects,id',
            'assignee_id' => 'sometimes|required|exists:users,id',
            'start_date' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'end_date' => 'sometimes|required|date_format:Y-m-d H:i:s',

            'priority' => 'sometimes|required|string|in:Low,Medium,High,Urgent',
            'site' => 'sometimes|required|string',
            'estimated_time' => 'sometimes|required|string',
        ];
    }
}
