<?php
header('Content-Type: application/json');

\ = [
    'status' => 'success',
    'message' => 'Diagnostic endpoint',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'extensions' => get_loaded_extensions(),
    'laravel' => class_exists('Illuminate\\Foundation\\Application') ? 'LOADED' : 'NOT_LOADED'
];

// Verificar middlewares
\ = [
    'TrustProxies' => 'App\\Http\\Middleware\\TrustProxies',
    'TrustHosts' => 'App\\Http\\Middleware\\TrustHosts',
    'Authenticate' => 'App\\Http\\Middleware\\Authenticate'
];

foreach (\ as \ => \) {
    \['middlewares'][\] = class_exists(\) ? 'EXISTS' : 'MISSING';
}

echo json_encode(\, JSON_PRETTY_PRINT);
