<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// FOKUS: GET create via kernel = 302 => auth FAILS at kernel. But curl GET create = 200.
// Why kernel 302? The cookie jar cookie we sent: prefix+encrypt(sid). Compare with
// REAL cookie from jar: decrypt both to check format.

$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$realCookie = trim(file_get_contents('/var/www/html/sidval2.txt'));
$decReal = $enc = $app->make('encrypter');
try {
    $d1 = $decReal->decrypt(urldecode($realCookie), false);
    echo 'real: ' . $d1 . "\n";
} catch (Throwable $e) {
    echo 'real err: ' . $e->getMessage() . "\n";
}

$keys = $redis->keys('*');
$real = array_map(function ($k) {
    return str_replace('sistem-informasi-sekretariat-database-', '', $k);
}, $keys);
$authSid = null;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, 'login_web_')) {
        preg_match('/sistem-informasi-sekretariat-cache-([A-Za-z0-9_-]+)/', $rk, $mm);
        $authSid = $mm[1];
        break;
    }
}
echo 'authSid: ' . substr((string) $authSid, 0, 16) . "\n";

// Now build cookie the EXACT way EncryptCookies::encrypt does:
$prefix = Illuminate\Cookie\CookieValuePrefix::create('sistem-informasi-sekretariat-session', $enc->getKey());
$encVal = $enc->encrypt($prefix . $authSid, false); // serialize=false for cookies
echo 'built cookie first30: ' . substr($encVal, 0, 30) . "\n";
echo 'real cookie first30: ' . substr($realCookie, 0, 30) . "\n";