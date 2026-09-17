<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);

$rmClass = Illuminate\Redis\RedisManager::class;
/** @var Illuminate\Redis\RedisManager $rm */
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
    if (str_contains($val, '_token') && $redis->ttl($rk) > $bestTtl) {
        $bestTtl = $redis->ttl($rk);
        $bestSid = str_replace('sistem-informasi-sekretariat-cache-', '', $rk);
        preg_match('/_token\\\\?";s:\d+:"([^"]+)"/', $val, $m);
        $bestToken = $m[1] ?? null;
    }
}
echo 'sid: ' . substr((string) $bestSid, 0, 15) . " ttl=$bestTtl\n";
echo 'token: ' . substr((string) $bestToken, 0, 15) . "\n";

$enc = $app->make('encrypter');
$cookieVal = urlencode($enc->encrypt($bestSid));

$request = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Lestari',
    'pekerjaan' => 'Perawat',
    '_token' => $bestToken,
]);
$request->cookies = new Symfony\Component\HttpFoundation\ParameterBag([
    'sistem-informasi-sekretariat-session' => $cookieVal,
]);

try {
    $response = $kernelHttp->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 419) {
        echo 'BODY: ' . substr(strip_tags($response->getContent()), 0, 150) . "\n";
    }
} catch (Throwable $e) {
    echo 'EXC: ' . $e->getMessage() . "\n";
}