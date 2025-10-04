Write-Host "=== VERIFICACIÓN DE COMPONENTES INTERNOS ===" -ForegroundColor Cyan

# Verificar controladores
Write-Host "`nCONTROLADORES:" -ForegroundColor Yellow
docker-compose exec app php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\$controllers = [
    'AuthController',
    'ClienteController', 
    'VehiculoController',
    'ProveedorController',
    'CompraController',
    'VentaController',
    'AsientoContableController',
    'ReporteController'
];

foreach (\$controllers as \$controller) {
    \$class = 'App\\Http\\Controllers\\' . \$controller;
    if (class_exists(\$class)) {
        echo '  ✅ ' . \$controller . PHP_EOL;
        
        // Verificar métodos principales
        \$methods = get_class_methods(\$class);
        \$mainMethods = array_filter(\$methods, function(\$method) {
            return !str_starts_with(\$method, '_');
        });
        echo '     Métodos: ' . implode(', ', \$mainMethods) . PHP_EOL;
    } else {
        echo '  ❌ ' . \$controller . PHP_EOL;
    }
}
"

# Verificar servicios
Write-Host "`nSERVICIOS:" -ForegroundColor Yellow
docker-compose exec app php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\$services = [
    'AuthService',
    'VentaService',
    'CompraService', 
    'ContabilidadService',
    'DianService'
];

foreach (\$services as \$service) {
    \$class = 'App\\Services\\' . \$service;
    if (class_exists(\$class)) {
        echo '  ✅ ' . \$service . PHP_EOL;
    } else {
        echo '  ❌ ' . \$service . PHP_EOL;
    }
}
"

# Verificar modelos
Write-Host "`nMODELOS:" -ForegroundColor Yellow
docker-compose exec app php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\$models = [
    'User',
    'Rol',
    'Cliente',
    'Proveedor', 
    'Vehiculo',
    'Compra',
    'CompraDetalle',
    'Venta',
    'VentaDetalle',
    'Cuenta',
    'AsientoContable',
    'PartidaContable'
];

foreach (\$models as \$model) {
    \$class = 'App\\Models\\' . \$model;
    if (class_exists(\$class)) {
        echo '  ✅ ' . \$model . PHP_EOL;
    } else {
        echo '  ❌ ' . \$model . PHP_EOL;
    }
}
"

Write-Host "`n=== VERIFICACIÓN DE COMPONENTES COMPLETADA ===" -ForegroundColor Cyan