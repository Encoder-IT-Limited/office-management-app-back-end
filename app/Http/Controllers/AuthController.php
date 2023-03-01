<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()]);
            }

            if (Auth::attempt($request->only(['email', 'password']))) {
                $user = User::with(['roles' => function ($role) {
                    $role->with('permissions');
                }])->find(Auth::id());

                if ($user->hasRole('developer')) {
                    (new AttendanceController)->checkIn(new Request($request->all()));
                }

                return response()->json([
                    'status' => true,
                    'message' => "Successfully Login",
                    'token' => $user->createToken('Api Token')->plainTextToken,
                    'user' => $user
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => "Credentials doesn't match",
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::user()->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'User Logged Out Successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
