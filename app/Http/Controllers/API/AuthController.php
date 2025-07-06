<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivationToken;
use App\Models\TwoFactorCode;
use App\Notifications\ActivateAccountNotification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Log as LogModel;
use Illuminate\Support\Facades\RateLimiter;
use ReCaptcha\ReCaptcha;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Valida se os parâmetros passados no front correspondem a esses
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).+$/'
            ],
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

        $user->assignRole('user');

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
        // Validar apenas email e password inicialmente
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $key = 'login-attempts:' . $ip;

        // Debug: Logar número de tentativas
        // Log::info('Tentativa de login', [
        //     'ip' => $ip,
        //     'email' => $request->email,
        //     'attempts' => RateLimiter::attempts($key),
        //     'too_many_attempts' => RateLimiter::tooManyAttempts($key, 5),
        // ]);

        // Verificar se excedeu limite de tentativas
        if ($this->isRateLimited($request)) {
            // Exigir e validar reCAPTCHA
            $request->validate([
                'g-recaptcha-response' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $recaptcha = new ReCaptcha(env('RECAPTCHA_SECRET_KEY'));
                        $response = $recaptcha->verify($value, $request->ip());
                        if (!$response->isSuccess()) {
                            $fail('Falha na verificação do CAPTCHA.');
                        }
                    },
                ],
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            // Incrementar tentativas
            RateLimiter::hit($key, 900); // 15 minutos

            $attempts = RateLimiter::attempts($key);
            $tooManyAttempts = RateLimiter::tooManyAttempts($key, 5);

            // Registrar tentativa falha
            Log::info([
                'id_user' => null,
                'rota' => 'api/login',
                'detalhe' => json_encode([
                    'email' => $request->email,
                    'status' => 'failed',
                    'method' => 'POST',
                    'attempts' => $attempts,
                ], JSON_UNESCAPED_UNICODE),
                'ip' => $ip,
            ]);

            // Verificar se excedeu limite
            return response()->json([
                'message' => $tooManyAttempts
                    ? 'Limite de tentativas excedido. Complete o CAPTCHA para continuar.'
                    : 'As credenciais estão incorretas.',
                'attempts_remaining' => max(0, 5 - $attempts),
                'show_captcha' => $tooManyAttempts || $attempts >= 5,
            ], 401);
        }

        // Resetar tentativas após login bem-sucedido
        RateLimiter::clear($key);

        $user = JWTAuth::user();

         // Gerar código para 2fa
        $code = rand(100000, 999999);

        TwoFactorCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        $user->notify(new TwoFactorCodeNotification($code));

        return response()->json([
            'token' => $token,
            'message' => 'Código de verificação enviado.',
            'user_id' => $user->id,
        ], 200);
    }

    protected function isRateLimited(Request $request)
    {
        $key = 'login-attempts:' . $request->ip();
        $tooManyAttempts = RateLimiter::tooManyAttempts($key, 5);
        
        // Debug: Logar resultado de isRateLimited
        Log::info('Verificação de RateLimiter', [
            'ip' => $request->ip(),
            'key' => $key,
            'attempts' => RateLimiter::attempts($key),
            'too_many_attempts' => $tooManyAttempts,
        ]);

        return $tooManyAttempts;
    }

    public function verifyCaptcha(Request $request)
    {
        $request->validate([
            'g-recaptcha-response' => 'required',
        ]);

        try {
            $recaptcha = new ReCaptcha(env('RECAPTCHA_SECRET_KEY'));
            $response = $recaptcha->verify($request->input('g-recaptcha-response'), $request->ip());

            if ($response->isSuccess()) {
                // Resetar tentativas após captcha válido
                RateLimiter::clear('login-attempts:' . $request->ip());
                return response()->json(['message' => 'CAPTCHA verificado com sucesso'], 200);
            }

            return response()->json(['error' => 'Falha na verificação do CAPTCHA'], 422);
        } catch (\Exception $e) {
            Log::info('Erro ao verificar CAPTCHA: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Erro ao verificar CAPTCHA'], 500);
        }
    }

    public function checkLoginRequirements(Request $request)
    {
        $ip = $request->ip();
        $key = 'login-attempts:' . $ip;

        return response()->json([
            'show_captcha' => RateLimiter::tooManyAttempts($key, 5),
            'attempts_remaining' => max(0, 5 - RateLimiter::attempts($key)),
        ]);
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string',
        ]);

        $record = TwoFactorCode::where('user_id', $request->user_id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            Log::warning('Tentativa de 2FA inválida', [
                'user_id' => $request->user_id,
                'code' => $request->code,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Código inválido ou expirado.'], 422);
        }

        // Código correto: deletar registro
        $record->delete();

        // Autenticar o usuário: agora sim gerar token
        $user = User::find($request->user_id);

       // Autenticar o usuário: gerar token JWT
        $user = User::find($request->user_id);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Autenticação em 2 fatores bem-sucedida.',
            'token' => $token,
        ]);
    }


    public function logout(Request $request)
    {
        $user = JWTAuth::user();
        LogModel::channel('activity')->info('Atividade registrada', [
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
