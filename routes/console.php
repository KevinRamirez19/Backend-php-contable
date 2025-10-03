<?php

use App\Console\Commands\SyncDianStatusCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

// Comando personalizado para sincronizar estado DIAN
Artisan::command('dian:sync', function () {
    $this->info('Sincronizando estados DIAN...');
    Artisan::call(SyncDianStatusCommand::class, ['--all' => true]);
    $this->info(Artisan::output());
})->purpose('Sincronizar estados DIAN de todas las ventas pendientes');

// Comando de inspiración
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Comando para limpiar cache completo
Artisan::command('system:clean', function () {
    $this->info('Limpiando cache del sistema...');
    
    Artisan::call('cache:clear');
    $this->info('✓ Cache limpiado');
    
    Artisan::call('config:clear');
    $this->info('✓ Configuración limpiada');
    
    Artisan::call('route:clear');
    $this->info('✓ Rutas limpiadas');
    
    Artisan::call('view:clear');
    $this->info('✓ Vistas limpiadas');
    
    $this->info('Sistema limpiado exitosamente!');
})->purpose('Limpiar toda la cache del sistema');

// Comando para ver estado del sistema
Artisan::command('system:status', function () {
    $this->info('=== Estado del Sistema ===');
    $this->info('App: ' . config('app.name'));
    $this->info('Env: ' . config('app.env'));
    $this->info('URL: ' . config('app.url'));
    $this->info('DB: ' . config('database.default'));
    $this->info('Cache: ' . config('cache.default'));
    $this->newLine();
    
    $this->info('Comandos DIAN disponibles:');
    $this->line('  php artisan dian:sync-status --all');
    $this->line('  php artisan dian:sync-status --venta-id=1');
    $this->line('  php artisan dian:sync');
})->purpose('Mostrar estado actual del sistema');