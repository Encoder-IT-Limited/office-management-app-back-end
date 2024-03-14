<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveStatusRequest extends FormRequest
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
            'reason' => 'sometimes|nullable|string',
            'accepted_start_date' => 'sometimes|nullable|date',
            'accepted_end_date' => 'sometimes|nullable|date',
            'message' => 'required|in:accepted,rejected',
            'accepted_by' => 'sometimes|nullable|exists:users,id',
            'last_updated_by' => 'sometimes|nullable|exists:users,id',
        ];
    }
}
