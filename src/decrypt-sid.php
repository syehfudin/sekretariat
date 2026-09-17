<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$enc = $app->make('encrypter');

$sid = file_get_contents('/var/www/html/sidval.txt');
$sid = trim($sid);
try {
    $dec = $enc->decrypt(urldecode($sid));
    echo "DEC_SID: $dec\n";

    // find this session in redis
    $redis = $app->make('redis')->connection();
    $cacheKey = 'sistem-informasi-sekretariat-cache-' . $dec;
    echo 'redis exists: ';
    var_dump($redis->exists($cacheKey));
    if ($redis->exists($cacheKey)) {
        $val = $redis->get($cacheKey);
        echo 'payload(first 300): ' . substr((string) $val, 0, 300) . "\n";
    }
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . "\n";
}