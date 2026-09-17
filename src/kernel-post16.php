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

$sessionMiddleware = new Illuminate\Session\Middleware\StartSession($app->make('session'));
$request = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Lestari',
    'pekerjaan' => 'Perawat',
    '_token' => $bestToken,
]);
$request->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $cookieVal);

try {
    $sessionMiddleware->handle($request, function ($req) use ($app, $bestSid, $bestToken) {
        $store = $req->session();
        echo 'sid: ' . substr($store->getId(), 0, 16) . "\n";
        echo 'sid==best: ' . var_export($store->getId() === $bestSid, true) . "\n";
        echo 'store token: ' . substr($store->token(), 0, 12) . "\n";
        echo 'provided: ' . substr((string) $bestToken, 0, 12) . "\n";
        $enc = $app->make('encrypter');
        $csrf = new Illuminate\Foundation\Http\Middleware\PreventRequestForgery($app, $enc);
        try {
            $res = $csrf->handle($req, function () {
                return 'PASSED';
            });
            echo 'csrf: ' . (is_string($res) ? $res : 'object') . "\n";
        } catch (Throwable $e) {
            echo 'csrf threw: ' . $e->getMessage() . "\n";
        }
        return new Symfony\Component\HttpFoundation\Response('done');
    });
} catch (Throwable $e) {
    echo 'outer: ' . $e->getMessage() . "\n";
}