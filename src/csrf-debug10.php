<?php

// All 31 keys = sessions?? type check maybe failed. Just print every key and raw prefix structure
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$redis = $app->make('redis')->connection();
$all = $redis->keys('*');
foreach (array_slice($all, 0, 6) as $k) {
    $type = $redis->type($k);
    echo "type=$type key=" . $k . "\n";
    if ($type == 1) {
        $p = $redis->get($k);
        echo "  val(first 200): " . substr((string) $p, 0, 200) . "\n";
    }
    if ($type == 5) {
        echo "  [hash fields]: ";
        var_dump($redis->hkeys($k));
    }
}