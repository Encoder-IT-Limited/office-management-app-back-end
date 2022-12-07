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
        $users = User::latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status' => 'Success',
            'users'   => $users
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'phone'    => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'password' => 'required|confirmed',
            'role_id'  => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $user = User::create([
            'role_id'     => $request->role_id,
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => Hash::make($request->password),
            'designation' => $request->designation,
        ]);

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 201);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user)
            return response()->json(['status' => 'User Not Found'], 404);

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required',
            'email'   => 'required|email|unique:users,email,' . $request->user_id,
            'phone'   => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $user = User::find($request->user_id);
        if (!$user)
            return response()->json(['status' => 'User Not Found'], 404);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->designation = $request->designation;
        $user->save();

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 201);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user)
            return response()->json(['status' => 'User Not Found'], 404);
        $user->delete();

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
