<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

// Check: does the sid from our REAL curl jar get found?
// Our capture-sid run earlier gave SID that decrypts to "hash|tEENa5o..."
// The SESSION KEY in redis uses tEENa5o... => so Laravel's actual SID in redis
// equals part2. The cookie decrypt via EncryptCookies produces the full
// ["value" => ???] array. Let's see what EncryptCookies ACTUALLY does:

// Real cookie from earlier: /var/www/html/sidval.txt
$sidCookie = trim(file_get_contents('/var/www/html/sidval.txt'));
$enc = $app->make('encrypter');
try {
    $dec = $enc->decrypt(urldecode($sidCookie)); // WITH serialize
    echo 'decrypted type: ' . gettype($dec) . "\n";
    var_dump(is_string($dec) ? substr($dec, 0, 60) : $dec);
} catch (Throwable $e) {
    echo 'with-serial err: ' . $e->getMessage() . "\n";
    // try with false
    $dec2 = $enc->decrypt(urldecode($sidCookie), false);
    echo 'no-serial: ';
    var_dump($dec2);
}