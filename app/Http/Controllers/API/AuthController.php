<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivationToken;
use App\Notifications\ActivateAccountNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // Gerar token de ativação
        $activationToken = Str::random(60);
        ActivationToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $activationToken),
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        // Enviar e-mail
        try {
            $user->notify(new ActivateAccountNotification($activationToken));
        } catch (\Exception $e) {
            Log::error('Falha ao enviar e-mail de ativação:', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return response()->json(['error' => 'Usuário criado, mas falhou ao enviar e-mail de ativação.'], 500);
        }

        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'register',
            'details' => 'Usuário registrado: ' . $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso. Verifique seu e-mail para ativar a conta.',
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

        $user = JWTAuth::user(); //Pega o user onde tem o id do token
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

    public function activate(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $tokenHash = hash('sha256', $request->token);
        $activationToken = ActivationToken::where('token', $tokenHash)
            ->where('expires_at', '>', now())
            ->first();

        if (!$activationToken) {
            return response()->json(['error' => 'Token inválido ou expirado.'], 400); //Bad Request
        }

        $user = $activationToken->user;
        $user->ativo = 1;
        $user->save();

        // Remover todos os tokens de ativação do usuário
        ActivationToken::where('user_id', $user->id)->delete();

        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'activate',
            'details' => 'Conta ativada: ' . $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json(['message' => 'Conta ativada com sucesso. Faça login.'], 200);
    }

    public function resendActivation(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'E-mail não encontrado.'], 404);
        }

        if ($user->ativo) {
            return response()->json(['error' => 'Conta já está ativada.'], 400);
        }

        // Remover tokens antigos
        ActivationToken::where('user_id', $user->id)->delete();

        // Gerar novo token
        $token = Str::random(60);
        ActivationToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        // Enviar e-mail
        $user->notify(new ActivateAccountNotification($token));

        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'resend_activation',
            'details' => 'Novo e-mail de ativação enviado: ' . $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json(['message' => 'Novo link de ativação enviado para seu e-mail.'], 200);
    }

    public function resendPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'E-mail não encontrado.'], 404);
        }

        $token = Str::random(60);

        // Salvar token na tabela password_resets
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => hash('sha256', $token),
                'created_at' => now(),
                'expires_at' => Carbon::now()->addMinutes(30),
            ]
        );

        // Enviar e-mail
        $user->notify(new ResetPasswordNotification($token));

        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'resend_activation',
            'details' => 'Link de redefinição de senha enviado para: ' . $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json(['message' => 'Novo link de redefinição de senha enviado para seu e-mail.'], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', hash('sha256', $request->token))
            ->where('expires_at', '>', now())
            ->first();

        if (!$reset) {
            return response()->json(['error' => 'Token inválido ou expirado.'], 400); //Bad Request
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        Log::channel('activity')->info('Atividade registrada', [
            'user_id' => $user->id,
            'action' => 'password_reset',
            'details' => 'Senha redefinida para o usuário: ' . $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json(['message' => 'Senha redefinida com sucesso.'], 200);
    }
}
