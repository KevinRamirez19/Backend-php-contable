<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta principal - Página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Ruta de login para evitar el error "Route [login] not defined"
Route::get('/login', function () {
    return response()->json([
        'message' => 'Sistema de API - Use endpoints de autenticación',
        'endpoints' => [
            'POST /api/auth/login' => 'Iniciar sesión',
            'POST /api/auth/register' => 'Registrar usuario',
            'POST /api/auth/logout' => 'Cerrar sesión',
            'GET /api/auth/me' => 'Obtener perfil de usuario'
        ],
        'note' => 'Todas las rutas de API requieren autenticación JWT excepto /api/auth/login y /api/auth/register'
    ]);
})->name('login'); // ← ESTA LÍNEA ES IMPORTANTE

// Ruta de health check para monitoreo
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'Concesionario API',
        'version' => '1.0.0',
        'environment' => app()->environment(),
    ]);
});

// Ruta para ver información del sistema
Route::get('/system-info', function () {
    return response()->json([
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'timezone' => config('app.timezone'),
        'debug' => config('app.debug'),
        'maintenance' => app()->isDownForMaintenance(),
    ]);
});

// Ruta de fallback para SPA (si se usa frontend)
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');