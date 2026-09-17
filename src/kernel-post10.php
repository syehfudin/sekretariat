<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// Register exception listener BEFORE handle
$app->terminating(function () {
});

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

// Hook into exceptions rendering
$exceptions = $app->make(Illuminate\Contracts\Debug\ExceptionHandler::class);
try {
    $response = $kernelHttp->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . "\n";

    // The session was persisted; find which sid the response rotated to:
    $cookieHeader = $response->headers->getCookies();
    foreach ($cookieHeader as $c) {
        if (str_contains($c->getName(), 'session')) {
            $newVal = $c->getValue();
            try {
                $dec = $enc->decrypt(urldecode($newVal), false);
                $arr = is_array($dec) ? $dec : [];
                $newSid = $arr['value'] ?? null;
                echo 'rotated sid: ' . substr((string) $newSid, 0, 12) . "\n";
                echo 'original sid: ' . substr((string) $bestSid, 0, 12) . "\n";
                echo 'rotated: ' . var_export($newSid !== $bestSid, true) . "\n";
            } catch (Throwable $ee) {
                echo 'cookie decrypt err: ' . $ee->getMessage() . "\n";
            }
        }
    }
} catch (Throwable $e) {
    echo 'EXC: ' . get_class($e) . ' ' . $e->getMessage() . "\n";
}