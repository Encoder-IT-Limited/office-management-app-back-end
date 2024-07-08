<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
            'user_id' => 'sometimes|nullable|exists:users,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'start_date' => 'required|date|before:end_date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date|after_or_equal:today',
            'reason' => 'required|string',
            'accepted_start_date' => 'nullable|date|after_or_equal:start_date|before:accepted_end_date',
            'accepted_end_date' => 'nullable|date|before_or_equal:end_date',
//            'accepted_end_date' => 'nullable|date|after:accepted_start_date|before_or_equal:end_date',
        ];
    }
}
