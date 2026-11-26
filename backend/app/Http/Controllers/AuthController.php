<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new ApiException('Email atau kata sandi salah', 401);
        }

        return ApiResponse::success([
            'token' => $user->createToken('mobile')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logout berhasil');
    }

    public function me(Request $request)
    {
        return ApiResponse::success($this->userPayload($request->user()));
    }

    private function userPayload(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'role']);
    }
}
