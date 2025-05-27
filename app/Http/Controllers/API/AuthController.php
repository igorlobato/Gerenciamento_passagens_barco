<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Valida se os parâmetros passados no front correspondem a esses
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'cpf' => 'required|string|size:14|unique:users',
            'numero' => 'required|string|size:15|unique:users',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'cpf' => $request->cpf,
            'numero' => $request->numero,
        ]);

        $token = JWTAuth::fromUser($user);

        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'register',
            'details' => 'Usuário registrado: ' . $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }

        $user = JWTAuth::user();
        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'login',
            'details' => 'Usuário logado: ' . $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json(['token' => $token], 200);// Ok
    }

    public function logout(Request $request)
    {
        $user = JWTAuth::user();
        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'logout',
            'details' => 'Usuário deslogado: ' . $user->email,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logout realizado com sucesso'], 200);
    }
}
