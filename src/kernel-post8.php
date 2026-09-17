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

$request = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Lestari',
    'pekerjaan' => 'Perawat',
    '_token' => $bestToken,
]);
$request->cookies = new Symfony\Component\HttpFoundation\InputBag([
    'sistem-informasi-sekretariat-session' => $cookieVal,
]);

// Intercept session token just before CSRF middleware: use a wrapped closure by
// pushing our own middleware at the front of the web group? Simplest: read session
// after handle via the session store. Instead: log session token DURING request.
try {
    $response = $kernelHttp->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . "\n";
    // after handle, check what session token got loaded for this sid
    $val = (string) $redis->get('sistem-informasi-sekretariat-cache-' . $bestSid);
    preg_match('/_token":"([A-Za-z0-9+\/=_-]{20,})"/', $val, $m);
    echo 'session-token-now: ' . substr($m[1] ?? 'none', 0, 15) . "\n";
    echo 'provided-token: ' . substr((string) $bestToken, 0, 15) . "\n";
    // Did the session id ROTATE? Check if a new sid key appeared
    $newVal = urlencode($enc->encrypt($bestSid));
    $sessCookie = $response->headers->get('Set-Cookie');
    echo 'set-cookie-present: ' . ($sessCookie ? 'yes' : 'no') . "\n";
} catch (Throwable $e) {
    echo 'EXC: ' . $e->getMessage() . "\n";
}