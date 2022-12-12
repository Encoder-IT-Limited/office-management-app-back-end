<?php

namespace App\Http\Controllers;

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
                $user = Auth::user();
                return response()->json([
                    'status' => true,
                    'message' => "Successfully Login",
                    'token' => $user->createToken('Api Token')->plainTextToken
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => "Credentials are doen't match",
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
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
