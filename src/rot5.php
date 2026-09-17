<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// KEY INSIGHT: GET /anggota/create via kernel returns 302 => auth FAILS in kernel
// env, but WORKS via curl/nginx (200). The difference: REAL request has many headers.
// The auth failure => 302 redirect to login. THEN token empty => no token.
// BUT CURL GET create = 200 WITH the form. So curl request works, kernel request fails.

// Difference between kernel request and real request: the real request goes through
// nginx which sets SCRIPT_FILENAME etc. Kernel Request::create('/anggota/create')
// - fine normally.

// => Auth via 'web' guard uses session()->get('login_web_...'). Session middleware
//    must have STARTED the session from the cookie. In kernel, cookie header set =
//    prefix+encrypt(sid, false). Maybe EncryptCookies::decrypt expects
//    $enc->encrypt($prefix.$sid, true) (serialized=true default for encrypt()).
// EncryptCookies uses static::serialized() = false by default... but the framework's
// EncryptCookies in Laravel 13 might use serialize=true now. Check decrypt error
// with true:
$enc = $app->make('encrypter');
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$keys = $redis->keys('*');
$real = array_map(function ($k) {
    return str_replace('sistem-informasi-sekretariat-database-', '', $k);
}, $keys);
$authSid = null;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, 'login_web_') && str_contains($val, 'anggota')) {
        preg_match('/sistem-informasi-sekretariat-cache-([A-Za-z0-9_-]+)/', $rk, $mm);
        $authSid = $mm[1];
        break;
    }
}
$prefix = Illuminate\Cookie\CookieValuePrefix::create('sistem-informasi-sekretariat-session', $enc->getKey());
$encTrue = urlencode($enc->encrypt($prefix . $authSid)); // serialize=true
$encFalse = urlencode($enc->encrypt($prefix . $authSid, false));

$req = Illuminate\Http\Request::create('/anggota/create', 'GET');
$req->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $encTrue);
$resp = $kernelHttp->handle($req);
echo 'with-serialize-true status: ' . $resp->getStatusCode() . "\n";

$req2 = Illuminate\Http\Request::create('/anggota/create', 'GET');
$req2->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $encFalse);
$resp2 = $kernelHttp->handle($req2);
echo 'with-serialize-false status: ' . $resp2->getStatusCode() . "\n";