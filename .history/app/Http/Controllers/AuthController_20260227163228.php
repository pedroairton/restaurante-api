<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request){
        $credentials = $request->only('email', 'password');

        if(!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado',
            'user' => $user,
            'token' => $token,
        ]);
    }
    public function logout(){
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado',
        ]);
    }

    public function me(){
        return response()->json(auth()->user());
    }
}
