<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$enc = $app->make('encrypter');
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$keys = $redis->keys('*');
$real = array_map(function ($k) {
    return str_replace('sistem-informasi-sekretariat-database-', '', $k);
}, $keys);

$found = false;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, 'login_web_') && str_contains($val, 'anggota/create')) {
        preg_match('/sistem-informasi-sekretariat-cache-([A-Za-z0-9_-]+)/', $rk, $mm);
        $authSid = $mm[1];
        $found = true;
        break;
    }
}
echo "found=$found\n";
if ($found) {
    $prefix = Illuminate\Cookie\CookieValuePrefix::create('sistem-informasi-sekretariat-session', $enc->getKey());
    $encSid = urlencode($enc->encrypt($prefix . $authSid, false));
    $req = Illuminate\Http\Request::create('/anggota/create', 'GET');
    $req->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $encSid);
    $resp = $kernelHttp->handle($req);
    echo 'GET status: ' . $resp->getStatusCode() . "\n";
    preg_match('/name="_token" value="([^"]*)"/', $resp->getContent(), $m);
    $pageToken = $m[1] ?? '';
    echo 'page token: ' . substr($pageToken, 0, 15) . "\n";
    $val = (string) $redis->get('sistem-informasi-sekretariat-cache-' . $authSid);
    preg_match('/_token":"([A-Za-z0-9+\/=_-]{20,})"/', $val, $mm);
    echo 'redis token: ' . substr($mm[1] ?? 'none', 0, 15) . "\n";
    echo 'match: ' . var_export(($mm[1] ?? '') === $pageToken, true) . "\n";
    foreach ($resp->headers->getCookies() as $c) {
        if (str_contains($c->getName(), 'session')) {
            $d = $enc->decrypt(urldecode($c->getValue()), false);
            $parts = explode('|', $d);
            echo 'cookie sid: ' . substr($parts[1], 0, 15) . ' rotated=' . var_export($parts[1] !== $authSid, true) . "\n";
        }
    }
}