<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\AsientoContableController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:api'])->group(function () {
    // Clientes
    Route::apiResource('clientes', ClienteController::class);
    
    // Vehículos
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::get('vehiculos-disponibles', [VehiculoController::class, 'disponibles']);
    
    // Proveedores
    Route::apiResource('proveedores', ProveedorController::class);
    
    // Compras
    Route::apiResource('compras', CompraController::class);
    
    // Ventas
    Route::apiResource('ventas', VentaController::class);
    
    // Contabilidad
    Route::get('asientos-contables', [AsientoContableController::class, 'index']);
    Route::get('asientos-contables/{id}', [AsientoContableController::class, 'show']);
    
    // Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('libro-diario', [ReporteController::class, 'libroDiario']);
        Route::get('mayor-cuentas', [ReporteController::class, 'mayorCuentas']);
        Route::get('balance-general', [ReporteController::class, 'balanceGeneral']);
        Route::get('estado-resultados', [ReporteController::class, 'estadoResultados']);
    });
});