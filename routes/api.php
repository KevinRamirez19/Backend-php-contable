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

Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API Concesionario Vehículos funcionando correctamente',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0'
    ]);
});

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

    // CRUD
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::get('vehiculos-disponibles', [VehiculoController::class, 'disponibles']);
    Route::apiResource('proveedores', ProveedorController::class);
    Route::apiResource('compra', CompraController::class);
    Route::post('compra/{id}/pagar', [CompraController::class, 'marcarComoPagada']);
    Route::post('compra/{id}/anular', [CompraController::class, 'marcarComoAnulada']);

    // ✅ Ventas (solo una vez)
    Route::apiResource('ventas', VentaController::class);
    Route::post('ventas/{id}/reenviar-dian', [VentaController::class, 'reenviarDian']);

    // Contabilidad
    Route::apiResource('asientos-contables', AsientoContableController::class)->only(['index', 'show']);
    Route::get('asientos-contables/libro-diario', [AsientoContableController::class, 'libroDiario']);
    Route::get('asientos-contables/mayor-cuentas', [AsientoContableController::class, 'mayorCuentas']);

    // Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('libro-diario', [ReporteController::class, 'libroDiario']);
        Route::get('mayor-cuentas', [ReporteController::class, 'mayorCuentas']);
        Route::get('balance-general', [ReporteController::class, 'balanceGeneral']);
        Route::get('estado-resultados', [ReporteController::class, 'estadoResultados']);
        Route::get('ventas-periodo', [ReporteController::class, 'ventasPorPeriodo']);
        Route::get('inventario', [ReporteController::class, 'inventario']);
    });
});

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint no encontrado',
        'available_endpoints' => [
            'GET /api/health',
            'POST /api/auth/register',
            'POST /api/auth/login',
            'GET /api/dashboard (protected)',
            'GET /api/clientes (protected)',
            'GET /api/vehiculos (protected)',
            'GET /api/proveedores (protected)',
            'GET /api/compra (protected)',
            'GET /api/ventas (protected)',
            'GET /api/asientos-contables (protected)',
            'GET /api/reportes/* (protected)'
        ]
    ], 404);
});
