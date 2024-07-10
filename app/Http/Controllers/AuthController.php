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
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            if (Auth::attempt($request->only(['email', 'password']))) {
                $user = User::find(Auth::id());

                if ($user->status == 'inactive') {
                    return response()->json([
                        'message' => "Your account is inactive, please contact admin",
                    ], 401);
                }

                return response()->json([
                    'message' => "Successfully Login",
                    'token' => $user->createToken('Api Token')->plainTextToken,
                ], 200);
            }

            return response()->json([
                'message' => false,
                'message' => "Credentials doesn't match",
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'message' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::user()->tokens()->delete();

            return response()->json([
                'message' => true,
                'message' => 'User Logged Out Successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
