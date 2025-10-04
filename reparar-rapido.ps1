Write-Host "=== REPARACIÓN RÁPIDA DEL BACKEND ===" -ForegroundColor Magenta

Write-Host "`n1. Creando controladores básicos..." -ForegroundColor Yellow

# Crear controlador básico de Proveedores
Write-Host "  Creando ProveedorController..." -ForegroundColor Gray
docker-compose exec app bash -c "cat > app/Http/Controllers/ProveedorController.php << 'ENDPROVEEDOR'
<?php
namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request \$request): JsonResponse
    {
        try {
            \$proveedores = Proveedor::all();
            return response()->json([
                'success' => true,
                'data' => \$proveedores,
                'message' => 'Proveedores obtenidos exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedores'
            ], 500);
        }
    }
    
    public function store(Request \$request): JsonResponse
    {
        try {
            \$proveedor = Proveedor::create(\$request->all());
            return response()->json([
                'success' => true,
                'data' => \$proveedor,
                'message' => 'Proveedor creado exitosamente'
            ], 201);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al crear proveedor'
            ], 500);
        }
    }
    
    public function show(int \$id): JsonResponse
    {
        try {
            \$proveedor = Proveedor::findOrFail(\$id);
            return response()->json([
                'success' => true,
                'data' => \$proveedor,
                'message' => 'Proveedor obtenido exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }
    }
    
    public function update(Request \$request, int \$id): JsonResponse
    {
        try {
            \$proveedor = Proveedor::findOrFail(\$id);
            \$proveedor->update(\$request->all());
            return response()->json([
                'success' => true,
                'data' => \$proveedor,
                'message' => 'Proveedor actualizado exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar proveedor'
            ], 500);
        }
    }
    
    public function destroy(int \$id): JsonResponse
    {
        try {
            \$proveedor = Proveedor::findOrFail(\$id);
            \$proveedor->delete();
            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar proveedor'
            ], 500);
        }
    }
}
ENDPROVEEDOR"

# Crear controlador básico de Ventas
Write-Host "  Creando VentaController..." -ForegroundColor Gray
docker-compose exec app bash -c "cat > app/Http/Controllers/VentaController.php << 'ENDVENTA'
<?php
namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Vehiculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request \$request): JsonResponse
    {
        try {
            \$ventas = Venta::with(['cliente', 'detalles.vehiculo'])->get();
            return response()->json([
                'success' => true,
                'data' => \$ventas,
                'message' => 'Ventas obtenidas exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas'
            ], 500);
        }
    }
    
    public function store(Request \$request): JsonResponse
    {
        try {
            return DB::transaction(function () use (\$request) {
                // Validar datos básicos
                if (!\$request->cliente_id || !\$request->detalles) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos incompletos'
                    ], 400);
                }

                // Crear venta
                \$venta = Venta::create([
                    'cliente_id' => \$request->cliente_id,
                    'fecha_venta' => now(),
                    'subtotal' => 0,
                    'iva' => 0,
                    'total' => 0,
                    'estado_dian' => 'PENDIENTE',
                    'created_by' => auth()->id(),
                ]);
                
                \$subtotal = 0;
                
                // Procesar detalles
                foreach (\$request->detalles as \$detalle) {
                    \$vehiculo = Vehiculo::find(\$detalle['vehiculo_id']);
                    
                    if (!\$vehiculo) {
                        throw new \Exception('Vehículo no encontrado');
                    }
                    
                    if (\$vehiculo->stock < \$detalle['cantidad']) {
                        throw new \Exception('Stock insuficiente para el vehículo: ' . \$vehiculo->marca . ' ' . \$vehiculo->modelo);
                    }
                    
                    \$precio = \$vehiculo->precio_venta;
                    \$subtotalDetalle = \$precio * \$detalle['cantidad'];
                    
                    VentaDetalle::create([
                        'venta_id' => \$venta->id,
                        'vehiculo_id' => \$detalle['vehiculo_id'],
                        'cantidad' => \$detalle['cantidad'],
                        'precio_unitario' => \$precio,
                        'subtotal' => \$subtotalDetalle,
                    ]);
                    
                    // Actualizar stock
                    \$vehiculo->decrement('stock', \$detalle['cantidad']);
                    
                    \$subtotal += \$subtotalDetalle;
                }
                
                // Actualizar totales
                \$iva = \$subtotal * 0.19;
                \$total = \$subtotal + \$iva;
                
                \$venta->update([
                    'subtotal' => \$subtotal,
                    'iva' => \$iva, 
                    'total' => \$total
                ]);
                
                return response()->json([
                    'success' => true,
                    'data' => \$venta->load(['cliente', 'detalles.vehiculo']),
                    'message' => 'Venta creada exitosamente'
                ], 201);
            });
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creando venta: ' . \$e->getMessage()
            ], 500);
        }
    }
    
    public function show(int \$id): JsonResponse
    {
        try {
            \$venta = Venta::with(['cliente', 'detalles.vehiculo'])->findOrFail(\$id);
            return response()->json([
                'success' => true,
                'data' => \$venta,
                'message' => 'Venta obtenida exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada'
            ], 404);
        }
    }

    public function reenviarDian(int \$id): JsonResponse
    {
        try {
            \$venta = Venta::findOrFail(\$id);
            \$venta->update(['estado_dian' => 'ENVIADA']);
            
            return response()->json([
                'success' => true,
                'data' => \$venta,
                'message' => 'Factura marcada para reenvío a DIAN'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar factura'
            ], 500);
        }
    }
}
ENDVENTA"

# Crear controlador básico de Compras
Write-Host "  Creando CompraController..." -ForegroundColor Gray
docker-compose exec app bash -c "cat > app/Http/Controllers/CompraController.php << 'ENDCOMPRA'
<?php
namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index(Request \$request): JsonResponse
    {
        try {
            \$compras = Compra::with(['proveedor'])->get();
            return response()->json([
                'success' => true,
                'data' => \$compras,
                'message' => 'Compras obtenidas exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener compras'
            ], 500);
        }
    }
    
    public function show(int \$id): JsonResponse
    {
        try {
            \$compra = Compra::with(['proveedor'])->findOrFail(\$id);
            return response()->json([
                'success' => true,
                'data' => \$compra,
                'message' => 'Compra obtenida exitosamente'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada'
            ], 404);
        }
    }

    public function marcarComoPagada(int \$id): JsonResponse
    {
        try {
            \$compra = Compra::findOrFail(\$id);
            \$compra->update(['estado' => 'PAGADA']);
            
            return response()->json([
                'success' => true,
                'data' => \$compra,
                'message' => 'Compra marcada como pagada'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar compra como pagada'
            ], 500);
        }
    }

    public function marcarComoAnulada(int \$id): JsonResponse
    {
        try {
            \$compra = Compra::findOrFail(\$id);
            \$compra->update(['estado' => 'ANULADA']);
            
            return response()->json([
                'success' => true,
                'data' => \$compra,
                'message' => 'Compra marcada como anulada'
            ]);
        } catch (\Exception \$e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar compra como anulada'
            ], 500);
        }
    }
}
ENDCOMPRA"

Write-Host "`n2. Limpiando cache..." -ForegroundColor Yellow
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app composer dump-autoload

Write-Host "`n3. Verificando controladores creados..." -ForegroundColor Yellow
docker-compose exec app php -r "
\$controllers = ['ProveedorController', 'VentaController', 'CompraController'];
foreach (\$controllers as \$controller) {
    \$class = 'App\\Http\\Controllers\\' . \$controller;
    if (class_exists(\$class)) {
        echo '  ✅ ' . \$controller . ' creado correctamente' . PHP_EOL;
    } else {
        echo '  ❌ ' . \$controller . ' no se pudo crear' . PHP_EOL;
    }
}
"

Write-Host "`n🎉 REPARACIÓN COMPLETADA" -ForegroundColor Magenta
Write-Host "Ejecuta .\verificar-backend.ps1 para probar nuevamente" -ForegroundColor Cyan