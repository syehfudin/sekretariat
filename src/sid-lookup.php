<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$redis = $app->make('redis')->connection();
$sid = 'df242769f978e408f5e8d19919e1250270f32598';
$key = 'sistem-informasi-sekretariat-cache-' . $sid;
var_dump((bool) $redis->exists($key));
$val = $redis->get($key);
echo 'val(first 250): ' . substr((string) $val, 0, 250) . "\n";