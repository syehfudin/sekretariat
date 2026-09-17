<?php

require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$redis = $app->make('redis');
$allKeys = $redis->keys('*');
echo "all keys count: " . count($allKeys) . "\n";
foreach (array_slice($allKeys, 0, 8) as $k) {
    $type = $redis->type($k);
    $ttl = $redis->ttl($k);
    echo substr($k, 0, 45) . " type=$type ttl=$ttl\n";
}