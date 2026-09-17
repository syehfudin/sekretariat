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

// Manually run StartSession FIRST so session exists, then CSRF middleware
$sessionMiddleware = new Illuminate\Session\Middleware\StartSession(
    $app->make('session'),
    $app->make(Illuminate\Cookie\CookieServiceProvider::class ? 'cookie' : 'cookie')
);
$request = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Lestari',
    'pekerjaan' => 'Perawat',
    '_token' => $bestToken,
]);
$request->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $cookieVal);

$cookieJar = new Symfony\Component\HttpFoundation\Response('', 200);
try {
    $respFromStart = $sessionMiddleware->handle($request, function ($req) use (&$started) {
        // session now started
        $store = $req->session();
        echo 'after StartSession sid: ' . substr($store->getId(), 0, 16) . "\n";
        echo 'after StartSession token: ' . substr($store->token(), 0, 12) . "\n";
        echo 'sid == bestSid: ' . var_export($store->getId() === $bestSid, true) . "\n";

        // Now run CSRF middleware
        $enc = $app->make('encrypter');
        $csrf = new Illuminate\Foundation\Http\Middleware\PreventRequestForgery($app, $enc);
        try {
            $res = $csrf->handle($req, function () {
                return 'PASSED';
            });
            echo 'csrf passed: ';
            var_dump($res === 'PASSED');
        } catch (Throwable $e) {
            echo 'csrf threw: ' . $e->getMessage() . "\n";
            echo 'session token at throw: ' . substr($req->session()->token(), 0, 12) . "\n";
            echo 'provided token: ' . substr((string) $req->input('_token'), 0, 12) . "\n";
        }
        return new Symfony\Component\HttpFoundation\Response('done');
    });
} catch (Throwable $e) {
    echo 'outer exc: ' . $e->getMessage() . "\n";
}