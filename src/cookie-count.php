<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$enc = $app->make('encrypter');

// Real flow works at kernel level. The double Set-Cookie on GET create suggests
// session rotated twice: two DIFFERENT sids set as cookies; curl keeps the LAST.
// Redis session persist: which sid does StartSession SAVE at end? The LAST store.
// If two cookies have different sids but redis has the second one => POST with
// jar (2nd sid) + token from page... The PAGE token: which session generated it?

// In flow: GET create => StartSession loads session sid1, CSRF middleware adds
// XSRF cookie + view renders token from sid1. Response Set-Cookie #1: sid_rotated?
// Where does the SECOND Set-Cookie come from? The AddQueuedCookiesToResponse...

// Let's find WHY sid rotates at all: SessionConfig! session.regenerate = false,
// but session ID REGENERATION happens when cookie mismatch? Actually Laravel 13
// has session "rotation" for privacy on every request? No.

// CHECK: our nginx conf passes fastcgi_param HTTPS empty. If session config secure=null
// fine. BUT: session same_site lax + domain null - fine.

// Let's test: does the ANGGAOT create response set TWO different session cookies?
$enc = $app->make('encrypter');
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$keys = $redis->keys('*');
$real = array_map(function ($k) {
    return str_replace('sistem-informasi-sekretariat-database-', '', $k);
}, $keys);
// find authed session (has login_web)
$authSid = null;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, 'login_web_') && str_contains($val, 'anggota/create')) {
        preg_match('/sistem-informasi-sekretariat-cache-([A-Za-z0-9_-]+)/', $rk, $mm);
        $authSid = $mm[1];
        break;
    }
}
echo 'auth sid: ' . substr((string) $authSid, 0, 16) . "\n";
$encVal = $enc->encrypt($authSid);
$req = Illuminate\Http\Request::create('/anggota/create', 'GET');
$req->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . urlencode($prefix = Illuminate\Cookie\CookieValuePrefix::create('sistem-informasi-sekretariat-session', $enc->getKey()) . $encVal));
$resp = $kernelHttp->handle($req);
echo 'status: ' . $resp->getStatusCode() . "\n";
$cookies = $resp->headers->getCookies();
echo 'total cookies: ' . count($cookies) . "\n";
foreach ($cookies as $c) {
    echo 'cookie: ' . $c->getName() . "\n";
}