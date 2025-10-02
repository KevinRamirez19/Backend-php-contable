<?php

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        return __DIR__ . '/../storage/' . ltrim($path, '/');
    }
}

return [
    'mode' => env('DIAN_MODE', 'homologacion'),
    
    'homologacion' => [
        'url' => env('DIAN_URL_HOMOLOGACION', 'https://vpfe-hab.dian.gov.co'),
        'username' => env('DIAN_USERNAME'),
        'password' => env('DIAN_PASSWORD'),
    ],
    
    'produccion' => [
        'url' => env('DIAN_URL_PRODUCCION', 'https://vpfe.dian.gov.co'),
        'username' => env('DIAN_USERNAME'),
        'password' => env('DIAN_PASSWORD'),
    ],
    
    'certificate' => [
        'path' => env('DIAN_CERTIFICATE_PATH', storage_path('app/certificates/certificate.p12')),
        'password' => env('DIAN_CERTIFICATE_PASSWORD'),
    ],
    
    'timeout' => 30,
    'retry_attempts' => 3,
];