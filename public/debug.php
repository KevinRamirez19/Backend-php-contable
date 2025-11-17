<?php
echo "1. PHP working\n";
require __DIR__.'/../vendor/autoload.php';
echo "2. Autoload OK\n";
$app = require_once __DIR__.'/../bootstrap/app.php';
echo "3. App bootstrapped\n";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
echo "4. Kernel created\n";
echo "✅ ALL SYSTEMS GO\n";