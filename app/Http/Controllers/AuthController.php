<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use function Laravel\Prompts\password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validUser = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        if ($validUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validUser->errors()
            ], 401);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'token' => $user->createToken('API TOKEN')->plainTextToken
        ], 200);
    }
    public function login(Request $request)
    {
        try {
            $validUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );
            if ($validUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validUser->errors()
                ], 401);
            }
            if (!auth()->attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match any record'
                ], 401);
            }
            $user = User::where('email', $request->email)->first();
            return response()->json([
                'status' => true,
                'message' => 'User Logged in Successfully',
                'token' => $user->createToken('API TOKEN')->plainTextToken
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reset_password(Request $request)
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:6|max:15',
            'confirm_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Fails',
                'errors' => $validator->errors()
            ], 422);
        }

        if (password_verify($request->old_password, $user->password)) {
            $user->update([
                'password' => bcrypt($request->password)
            ]);
            return response()->json([
                'message' => 'Password successfully Updated',
            ], 200);
        } else {
            return response()->json([
                'message' => 'old password does not matched',

            ], 400);
        }
    }
    public function logout(Request $request)
    {
        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        try {
            // Revoke all tokens for the authenticated user
            $user->tokens()->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to revoke tokens'], 500);
        }
        return response()->json(['message' => 'Tokens revoked successfully']);
    }
}
