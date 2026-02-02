<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthSignUpRequest;
use App\Http\Requests\AuthLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function signUp(AuthSignUpRequest $request)
    {
        $user = User::create($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'authToken' => $token,
        ]);
    }

    public function login(AuthLoginRequest $request)
    {
        if (!auth()->attempt($request->validated())) {
            return response()->json([
                'success' => false,
                'messages' => ['メールアドレスまたはパスワードが正しくありません。'],
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'authToken' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
