<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContactProfileController;

Route::prefix('v1')->group(function () {

    Route::get('/home', function () {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'version' => '0.1.0',
        ]);
    })->name('home');

    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        Route::apiResource('contactos', ContactController::class);

        Route::prefix('contactos/{contacto}')->group(function () {
            Route::get('/perfil', [ContactProfileController::class, 'show'])->name('api.v1.contactos.perfil.show');
            Route::post('/perfil', [ContactProfileController::class, 'store'])->name('api.v1.contactos.perfil.store');
        });
    });
});