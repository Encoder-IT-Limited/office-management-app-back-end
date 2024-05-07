<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:11',
            'designation' => 'sometimes|required',
            'password' => 'sometimes|required|confirmed',
            'role_id' => 'required|exists:roles,id',
            'skills.*.skill_id' => 'sometimes|required|exists:skills,id',
            'skills.*.experience' => 'sometimes|required|max:10',
            'users[]' => 'sometimes|nullable|array',
            'users.*' => 'sometimes|nullable|exists:users,id',
            'document' => 'sometimes|nullable|mimes:doc,pdf,docx,zip,jpeg,png,jpg,gif,svg,webp,avif|max:20480',

            'notes' => 'sometimes|required|array',
            'notes.*' => 'required|string'
        ];
    }
}
