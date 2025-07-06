<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\LogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/activate', [AuthController::class, 'activate']);
Route::post('/resend-activation', [AuthController::class, 'resendActivation']);
Route::post('/reset-password', [AuthController::class, 'resendPassword']);
Route::post('/reset-password/confirm', [AuthController::class, 'resetPassword']);
Route::post('/verify-captcha', [AuthController::class, 'verifyCaptcha']);
Route::get('/check-login-requirements', [AuthController::class, 'checkLoginRequirements']);
Route::post('/verify-2fa', [AuthController::class, 'verify2fa']);

//Rotas protregidas por token JWT em auth.php
Route::middleware('auth:api', 'log.activity')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::middleware('permission:papel_permissao')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{role}/permissions', [RoleController::class, 'show']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/{id}', [PermissionController::class, 'show']);
        Route::post('/permissions', [PermissionController::class, 'store']);
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
        Route::post('/roles/assign-permission', [RoleController::class, 'assignPermission']);
        Route::post('/roles/revoke-permission', [RoleController::class, 'revokePermission']);
        Route::post('/roles/assign-role', [RoleController::class, 'assignRole']);
        Route::post('/roles/revoke-role', [RoleController::class, 'revokeRole']);

        Route::get('/logs', [LogController::class, 'index']);
        Route::get('/logs/{id}', [LogController::class, 'show']);
    });
});
