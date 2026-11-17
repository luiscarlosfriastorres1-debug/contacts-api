<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request){

        if(!Auth::attempt($request->validate(['email'=>'required|email','password'=>'required']))){

            return response()->json([
                'message'=>'Credenciales incorrectas, verifique'
                ], 401);

        }

        $user = Auth::user();

        return response()->json(['user'=>$user, 'token'=>$user->createToken('api')->plainTextToken]);

    }

    public function logout(Request $request){

        if ($token = $request->user()?->currentAccessToken()) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada',
        ]); 

    }

    public function me(Request $request){

        return response()->json([
            'nombre'=>$request->user()->name,
            'fecha_creacion'=>$request->user()->created_at
            ]);

    }
}