<?php

return [
    'paths' => ['*'], // ← CAMBIAR a wildcard para TODAS las rutas

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];