<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AsientoContableController;
use Illuminate\Support\Facades\Route;

// 🔹 Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// 🔹 Ruta de salud de la API
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API Concesionario Vehículos funcionando correctamente',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0'
    ]);
});

// 🔒 Rutas protegidas por token
Route::middleware(['auth:api'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'total_vehiculos' => \App\Models\Vehiculo::count(),
                'total_clientes' => \App\Models\Cliente::count(),
                'total_ventas' => \App\Models\Venta::count(),
                'ventas_mes' => \App\Models\Venta::whereMonth('created_at', now()->month)->count(),
                'ingresos_mes' => \App\Models\Venta::whereMonth('created_at', now()->month)->sum('total'),
            ]
        ]);
    });

    // Recursos principales
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::get('vehiculos-disponibles', [VehiculoController::class, 'disponibles']);
    Route::apiResource('proveedores', ProveedorController::class);
    Route::apiResource('compras', CompraController::class);
    Route::post('compras/{id}/pagar', [CompraController::class, 'marcarComoPagada']);
    Route::post('compras/{id}/anular', [CompraController::class, 'marcarComoAnulada']);
    Route::apiResource('ventas', VentaController::class);
    Route::post('ventas/{id}/reenviar-dian', [VentaController::class, 'reenviarDian']);

    // Cuentas contables
    Route::apiResource('cuentas', CuentaController::class);

    // Asientos contables
    Route::get('/asientos', [AsientoContableController::class, 'index']);
    Route::get('/asientos/{id}', [AsientoContableController::class, 'show']);
    Route::post('/asientos', [AsientoContableController::class, 'store']);
    Route::put('/asientos/{id}', [AsientoContableController::class, 'update']);
    Route::delete('/asientos/{id}', [AsientoContableController::class, 'destroy']);

    // 🔹 NUEVAS RUTAS: Partidas contables
    Route::get('partidas', [\App\Http\Controllers\PartidaContableController::class, 'index']);
    Route::get('partidas/{id}', [\App\Http\Controllers\PartidaContableController::class, 'show']);
    Route::post('partidas', [\App\Http\Controllers\PartidaContableController::class, 'store']);
    Route::put('partidas/{id}', [\App\Http\Controllers\PartidaContableController::class, 'update']);
    Route::delete('partidas/{id}', [\App\Http\Controllers\PartidaContableController::class, 'destroy']);

    Route::prefix('reportes')->group(function () {
        Route::post('libro-diario', [ReporteController::class, 'libroDiario']);
        Route::post('mayor-cuentas', [ReporteController::class, 'mayorCuentas']);
        Route::post('balance-general', [ReporteController::class, 'balanceGeneral']);
        Route::post('estado-resultados', [ReporteController::class, 'estadoResultados']);
        Route::post('ventas-periodo', [ReporteController::class, 'ventasPorPeriodo']);
        Route::post('inventario', [ReporteController::class, 'inventario']);
        Route::get('reportes/libro-diario/pdf', [ReporteController::class, 'descargarLibroDiarioPDF']);
        Route::get('reportes/libro-diario/excel', [ReporteController::class, 'descargarLibroDiarioExcel']);

    });
});

