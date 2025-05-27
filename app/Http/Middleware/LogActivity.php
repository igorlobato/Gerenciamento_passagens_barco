<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (JWTAuth::getToken()) {
            try {
                $user = JWTAuth::user();
                if ($user) {
                    Log::channel('activity')->info('Atividade registrada', [
                        'user_id' => $user->id,
                        'action' => $request->method() . ' ' . $request->path(),
                        'details' => $request->all(),
                        'ip_address' => $request->ip(),
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                }
            } catch (\Exception $e) {
                // Ignora erros de token inválido
            }
        }

        return $response;
    }
}
