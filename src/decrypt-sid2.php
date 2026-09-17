<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$enc = $app->make('encrypter');

$sid = trim(file_get_contents('/var/www/html/sidval.txt'));
try {
    $dec = $enc->decrypt(urldecode($sid), false);
    $arr = is_array($dec) ? $dec : ['raw' => $dec];
    $value = $arr['value'] ?? $dec;
    echo 'SID_DEC: ' . $value . "\n";

    $redis = $app->make('redis')->connection();
    $cacheKey = 'sistem-informasi-sekretariat-cache-' . $value;
    echo 'redis exists: ';
    var_dump((bool) $redis->exists($cacheKey));
    if ($redis->exists($cacheKey)) {
        $val = $redis->get($cacheKey);
        echo 'payload: ' . substr((string) $val, 0, 300) . "\n";
        if (preg_match('/_token\\\\?";s:\d+:"([A-Za-z0-9_-]+)/', $val, $m)) {
            echo 'session _token: ' . substr($mm = $m[1], 0, 15) . "\n";
        }
    }
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . "\n";
}