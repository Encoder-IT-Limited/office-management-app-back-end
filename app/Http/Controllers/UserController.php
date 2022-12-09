<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find(1);
        dd($user->user_role);
        dd($user->roles);
        $users = User::latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status' => 'Success',
            'users'   => $users
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users',
            'phone'       => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'password'    => 'required|confirmed',
            'role_id'     => 'required|exists:roles,id',
            'designation' => 'sometimes|required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $data = $validator->validated();
        $data['password'] =  Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users,email,' . $request->user_id,
            'phone'       => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'user_id'     => 'required|exists:users,id',
            'designation' => 'sometimes|required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $user = User::findOrFail($request->user_id);

        $user->update($validator->validated());

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 201);
    }

    public function destroy($id)
    {
        $user = User::destroy($id);

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
