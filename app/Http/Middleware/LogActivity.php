<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Log as LogModel;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (JWTAuth::getToken()) {
            try {
                $user = JWTAuth::user();
                if ($user) {
                    LogModel::create([
                        'id_user' => $user->id,
                        'rota' => $request->method() . ' ' . $request->path(),
                        'detalhe' => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
                        'ip' => $request->ip(),
                    ]);
                }
            } catch (\Exception $e) {
                // Ignora erros de token inválido
            }
        }

        return $response;
    }
}
