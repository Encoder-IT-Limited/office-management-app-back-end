<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectStoreUpdateRequest extends FormRequest
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
            'id' => 'sometimes|required|exists:projects,id',
            'name' => 'required|string|unique:projects,name,' . $this->project->id,
            'budget' => 'sometimes|required',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'client_id' => 'sometimes|required|exists:users,id',
            'status_id' => 'sometimes|required|exists:label_statuses,id',

            'teams' => 'sometimes|required|array',
            'teams.*.id' => 'sometimes|required|exists:teams,id',
            'teams.*.title' => 'sometimes|required',
            'teams.*.user_ids' => 'sometimes|required|array',

            'user_ids' => 'sometimes|required|array',
            'user_ids.*' => 'sometimes|required|exists:users,id',

            'tasks' => 'sometimes|required|array',
            'tasks.*.id' => 'sometimes|required|exists:tasks,id',
            'tasks.*.title' => 'required|string',
            'tasks.*.description' => 'required|string',
            'tasks.*.reference' => 'sometimes|required|string',
            'tasks.*.assignee_id' => 'sometimes|required|exists:users,id',
            'tasks.*.start_date' => 'required',
            'tasks.*.end_date' => 'required',

            'tasks.*.labels' => 'sometimes|required|array',

            'notes' => 'sometimes|nullable|array',
            'notes.*' => 'nullable|string',

            'reminders' => 'sometimes|required|array',
            'reminders.*.user_id' => 'sometimes|required|exists:users,id',
            'reminders.*.title' => 'sometimes|required|string',
            'reminders.*.description' => 'sometimes|required|string',
            'reminders.*.remind_at' => 'sometimes|required|date:Y/m/d H:i:s|after:now',
            'reminders.*.message' => 'sometimes|required|string',

        ];
    }
}
