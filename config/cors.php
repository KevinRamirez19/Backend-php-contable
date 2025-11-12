<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'login', 'register', 'sanctum/csrf-cookie'],

    // Permitir todos los métodos (GET, POST, PUT, DELETE, etc.)
    'allowed_methods' => ['*'],

    // Permitir peticiones desde tu frontend
    'allowed_origins' => ['http://localhost:3000'],

    // Deja los patrones vacíos
    'allowed_origins_patterns' => [],

    // Permitir todos los headers
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Si usas cookies o autenticación con tokens
    'supports_credentials' => true,

];
