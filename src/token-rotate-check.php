<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// FINAL INSIGHT: session id rotates on EVERY request because of session.rotate?
// Or: the session id CHANGES because Laravel 13 regenerates on every request when
// the request has no "previous" session match? There is a known behavior:
// session()->regenerate() in LoginController. But on every GET?

// Check config: session.rotate_ids?
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

// Find sessions created in the last 2 minutes and check the pattern: each curl flow
// creates 3 sessions (get login, post login, get create). POST create would then
// create a 4th NEW session whose token != csrf2 (belonging to 3rd session)...
// BUT kernel full-flow passed! Because kernel flow used the SAME jar variable.

// Wait - kernel full-flow2 PASSED all the way. curl flow fails.
// Difference: kernel flow used resp Set-Cookie directly. curl uses jar with -c.
// curl jar: after login, jar has ONE session cookie. GET create responds with TWO
// Set-Cookie headers for session (rotation twice!). Curl OVERWRITES jar with last.
// Then which one did the token belong to? The page token generated BEFORE response
// cookies were set - it's sid1's token. If rotation happened mid-request, the new
// sid2 session in redis has DIFFERENT token. POST with sid2 cookie + sid1 token => 419!!

// WHY does the sid rotate mid-request? => session middleware regenerate happens when
// the OLD session id was NOT FOUND in redis? No...

// THE ANSWER: our nginx fastcgi config lacks HTTPS var. The app is http. session cookie
// secure=null => false. Fine. BUT the ROTATION: Laravel rotates the session id on
// EVERY request when using "CacheBasedSessionHandler"? NO.

// Let's verify rotation empirically: which sid did the page's form token come from?
// Get token + capture BOTH session cookies from GET create response:
$enc = $app->make('encrypter');
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

// Use the LAST authed session from redis
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
    if (str_contains($val, 'login_web_') && str_contains($val, 'anggota/create')) {
        preg_match('/sistem-informasi-sekretariat-cache-([A-Za-z0-9_-]+)/', $rk, $mm);
        $authSid = $mm[1];
        break;
    }
}
if ($authSid) {
    $prefix = Illuminate\Cookie\CookieValuePrefix::create('sistem-informasi-sekretariat-session', $enc->getKey());
    $encSid = urlencode($enc->encrypt($prefix . $authSid, false));
    $req = Illuminate\Http\Request::create('/anggota/create', 'GET');
    $req->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . $encSid);
    $resp = $kernelHttp->handle($req);
    echo 'GET status: ' . $resp->getStatusCode() . "\n";
    preg_match('/name="_token" value="([^"]*)"/', $resp->getContent(), $m);
    $pageToken = $m[1] ?? '';
    echo 'page token: ' . substr($pageToken, 0, 15) . "\n";
    // check redis session token for authSid
    $val = (string) $redis->get('sistem-informasi-sekretariat-cache-' . $authSid);
    preg_match('/_token":"([A-Za-z0-9+\/=_-]{20,})"/', $val, $m);
    echo 'redis token: ' . substr($m[1] ?? 'none', 0, 15) . "\n";
    echo 'token match: ' . var_export(($m[1] ?? '') === $pageToken, true) . "\n";
    // response set-cookie sids
    foreach ($resp->headers->getCookies() as $c) {
        if (str_contains($c->getName(), 'session')) {
            try {
                $d = $enc->decrypt(urldecode($c->getValue()), false);
                $parts = explode('|', $d);
                echo 'cookie sid: ' . substr($parts[1], 0, 15) . ' rotated=' . var_export($parts[1] !== $authSid, true) . "\n";
            } catch (Throwable $e) {
                echo 'dec err: ' . $e->getMessage() . "\n";
            }
        }
    }
}