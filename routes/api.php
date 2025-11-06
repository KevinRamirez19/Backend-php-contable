<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    ClienteController,
    VehiculoController,
    ProveedorController,
    CompraController,
    VentaController,
    CuentaController,
    ReporteController,
    AsientoContableController,
    PartidaContableController,
    DashboardController
};

/*
|--------------------------------------------------------------------------
| API Routes nsoe hsdk
|--------------------------------------------------------------------------
*/

// 🔹 Rutas de autenticación (públicas)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// 🔹 Ruta de salud del sistema
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => '✅ API Concesionario Vehículos funcionando correctamente',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0'
    ]);
});

// 🔒 Rutas protegidas (requieren autenticación JWT)
Route::middleware(['auth:api'])->group(function () {

    // 📊 Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'getStats']);
        Route::get('chart', [DashboardController::class, 'getChartData']);
    });

    // 👥 Clientes
    Route::apiResource('clientes', ClienteController::class);

    // 🚗 Vehículos
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::get('vehiculos-disponibles', [VehiculoController::class, 'disponibles']);

    // 🏢 Proveedores
    Route::apiResource('proveedores', ProveedorController::class);

    // 🧾 Compras
    Route::apiResource('compras', CompraController::class);
    Route::post('compras/{id}/pagar', [CompraController::class, 'marcarComoPagada']);
    Route::post('compras/{id}/anular', [CompraController::class, 'marcarComoAnulada']);

    // 💰 Ventas
    Route::apiResource('ventas', VentaController::class);
    Route::post('ventas/{id}/reenviar-dian', [VentaController::class, 'reenviarDian']);

    // ✅ Facturas protegidas (solo usuarios autenticados)
    Route::get('ventas/{id}/factura-pdf', [VentaController::class, 'descargarFacturaPDF']);
    Route::get('ventas/{id}/factura-xml', [VentaController::class, 'descargarFacturaXML']);

    // 🧾 Cuentas contables
    Route::apiResource('cuentas', CuentaController::class);

    // 📚 Asientos contables
    Route::apiResource('asientos', AsientoContableController::class);

    // 💼 Partidas contables
    Route::apiResource('partidas', PartidaContableController::class);

    // 📊 Reportes
    Route::prefix('reportes')->group(function () {
        Route::post('libro-diario', [ReporteController::class, 'libroDiario']);
        Route::post('mayor-cuentas', [ReporteController::class, 'mayorCuentas']);
        Route::post('balance-general', [ReporteController::class, 'balanceGeneral']);
        Route::post('estado-resultados', [ReporteController::class, 'estadoResultados']);
        Route::post('ventas-periodo', [ReporteController::class, 'ventasPorPeriodo']);
        Route::post('inventario', [ReporteController::class, 'inventario']);

        // Descargas de reportes
        Route::get('libro-diario/pdf', [ReporteController::class, 'descargarLibroDiarioPDF']);
        Route::get('libro-diario/excel', [ReporteController::class, 'descargarLibroDiarioExcel']);
    });
});

// 🔹 Rutas públicas para facturas (acceso con token opcional)
// Útil para abrir factura en ventana nueva del frontend sin login completo
Route::prefix('public')->group(function () {
    Route::get('ventas/{id}/factura-pdf', [VentaController::class, 'descargarFacturaPDF']);
    Route::get('ventas/{id}/factura-xml', [VentaController::class, 'descargarFacturaXML']);
});
