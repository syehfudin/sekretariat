<?php

// type=0 = non-existent via phpredis client! But keys() lists them. Classic phpredis
// DOUBLE-PREFIX BUG: keys() returns prefix applied twice; real key is only ONE prefix.
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$redis = $app->make('redis')->connection();

// Strip the double prefix: real key = "sistem-informasi-sekretariat-cache-HASH"
$probe = 'sistem-informasi-sekretariat-cache-6XTgJXEC6LlY4OkJMxhJ2PT1YL9K3SE1oOFcMHax';
echo "single-prefix exists: ";
var_dump($redis->exists($probe));
$type = $redis->type($probe);
echo "type: $type\n";
if ($type == 1) {
    $p = $redis->get($probe);
    echo "val(first 250): " . substr((string) $p, 0, 250) . "\n";
    // decode session
    $arr = json_decode($p, true);
    if (is_array($arr) && isset($arr['_token'])) {
        echo "FOUND TOKEN: " . $arr['_token'] . "\n";
    }
    echo "all fields: " . implode(',', array_keys($arr ?? [])) . "\n";
}