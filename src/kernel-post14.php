<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// Register a terminating debug: capture exception thrown inside handle by wrapping
// the exception handler render callback. Simplest: temporarily replace exception
// handler reporting to log the exception class for THIS request.
$app->singleton('app.debug.testlog', function () {
    return new Illuminate\Support\MessageBag;
});

// Use the framework's exception handler to render: catch ANY Throwable from handle
// and print full class
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

$request = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Lestari',
    'pekerjaan' => 'Perawat',
    '_token' => $bestToken,
]);
$request->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $cookieVal);
$request->server->set('SERVER_NAME', '127.0.0.1');

// Also set request attributes Laravel expects
try {
    ob_start();
    $response = $kernelHttp->handle($request);
    $out = ob_get_clean();
    echo 'STATUS: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 419) {
        // diff tokens with logging middleware approach:
        // Instead let's directly run the middleware manually:
        $middleware = new Illuminate\Foundation\Http\Middleware\PreventRequestForgery($app, $enc);
        $started = $app->make('session.store');
        echo 'session store id: ' . $started->getId() . "\n";
        echo 'session store token: ' . substr($started->token(), 0, 12) . "\n";
        $next = function ($req) {
            return 'OK';
        };
        try {
            $result = $middleware->handle($request, $next);
            echo 'middleware result: ' . (is_string($result) ? $result : get_class($result)) . "\n";
        } catch (Throwable $e) {
            echo 'middleware threw: ' . get_class($e) . ' ' . $e->getMessage() . "\n";
        }
    }
} catch (Throwable $e) {
    echo 'EXC: ' . get_class($e) . ' ' . $e->getMessage() . "\n";
}