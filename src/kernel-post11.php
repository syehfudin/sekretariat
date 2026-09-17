<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$keys = $redis->keys('*');
$real = array_map(function ($k) {
    return str_replace('sistem-informasi-sekretariat-database-', '', $k);
}, $keys);
$bestTtl = -1;
$bestSid = null;
$bestToken = null;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (preg_match('/_token":"([A-Za-z0-9+\/=_-]{20,})"/', $val, $m) && $redis->ttl($rk) > $bestTtl) {
        $bestTtl = $redis->ttl($rk);
        $bestSid = str_replace('sistem-informasi-sekretariat-cache-', '', $rk);
        $bestToken = $m[1];
    }
}

$enc = $app->make('encrypter');
$cookieVal = urlencode($enc->encrypt($bestSid));

// THE INSIGHT TEST: send Cookie header directly (raw string) instead of InputBag,
// exactly like curl does. Maybe EncryptCookies middleware reads from $request->cookies
// BEFORE we set it... InputBag set after Request::create should be fine. But test with
// header approach:
$request = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Lestari',
    'pekerjaan' => 'Perawat',
    '_token' => $bestToken,
]);
$request->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $cookieVal);

try {
    $response = $kernelHttp->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 419) {
        // Check: did OUR session get a login-session? The previous.url?
        $val = (string) $redis->get('sistem-informasi-sekretariat-cache-' . $bestSid);
        echo 'session-before-post now contains: ' . substr($val, 0, 150) . "\n";
    }
} catch (Throwable $e) {
    echo 'EXC: ' . $e->getMessage() . "\n";
}