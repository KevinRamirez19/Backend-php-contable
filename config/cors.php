<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'health',
        'public/*'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://accounting-system-vert.vercel.app',
        'https://accounting-system-8z3nffqu2-cr7kevin132-3789s-projects.vercel.app',
        'https://accounting-system-git-main-cr7kevin132-3789s-projects.vercel.app',
    ],

    'allowed_origins_patterns' => [
        'https://.*\.vercel\.app',
        'http://localhost:3000',
        'http://127\.0\.0\.1:3000',
        'http://localhost:3001'
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];