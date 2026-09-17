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
$bestVal = null;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, '_token') && $redis->ttl($rk) > $bestTtl) {
        $bestTtl = $redis->ttl($rk);
        $bestSid = str_replace('sistem-informasi-sekretariat-cache-', '', $rk);
        $bestVal = $val;
    }
}
echo 'sid: ' . substr((string) $bestSid, 0, 12) . " ttl=$bestTtl\n";
echo "RAW PAYLOAD:\n$bestVal\n";