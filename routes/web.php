<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta principal - Página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

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