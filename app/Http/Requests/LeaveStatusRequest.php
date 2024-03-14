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
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'exists:leaves,id',
            'reason' => 'sometimes|required|string',
            'accepted_start_date' => 'sometimes|required|date',
            'accepted_end_date' => 'sometimes|required|date'
        ];
    }
}
