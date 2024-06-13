<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', Password::defaults()],
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'token' => $user->createToken('default_token')->plainTextToken,
                'message' => 'User registered successfully'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to register user',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $profile = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number' => $user->phone
            ];

            return response()->json([
                'token' => $user->createToken('default_token')->plainTextToken,
                'profile' => $profile
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to login',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // logout method remains the same
    public function logout(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $user?->tokens()->delete();
        return response()->noContent();
    }
}
