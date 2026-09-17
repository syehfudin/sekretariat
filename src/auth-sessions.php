<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// Check the sessions in redis right after curl flow: 
// how many sessions have "login" as previous route (unauthenticated sessions)?
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$keys = $redis->keys('*');
$real = array_map(function ($k) {
    return str_replace('sistem-informasi-sekretariat-database-', '', $k);
}, $keys);

$authed = 0;
$guest = 0;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, 'login_TTL')) {
        continue;
    }
    if (str_contains($val, 'login_web_')) {
        $authed++;
    } elseif (str_contains($val, '_token')) {
        $guest++;
    }
}
echo "authed sessions: $authed\nguest sessions: $guest\n";

// Show an authed session's keys
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, 'login_web_')) {
        echo 'authed session payload: ' . substr($val, 0, 400) . "\n";
        break;
    }
}