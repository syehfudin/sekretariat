<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// DECISIVE: The session token changes on every POST attempt:
// "session-token-now" differed from earlier because POST regenerated the session.
// Notice in kernel-post8: session-token-now == provided-token BUT still 419!
// That means tokensMatch SUCCEEDED but something AFTER threw TokenMismatch? No—
// the payload we re-read is post-rotate.

// => The middleware checks hash_equals OK, BUT $request->session() may be a NEW
//    empty session (regenerated) whose token is different from redis token we sent.
//    The "session-token-now" we read after could be the ROTATED token which equals
//    provided by coincidence? Impossible for 2 different runs.

// Actually the smoking gun: after 419 the session token CHANGED between our runs:
// run1: OTuCqW..., then session now has ANOTHER token. This means EVERY POST request
// ROTATES the session and REGENERATES the CSRF token. Laravel does that when
// session()->regenerate() or when the session is created FRESH (invalid session).

// If the sid cookie we send FAILS decryption by EncryptCookies, Laravel treats
// request as cookie-less => new session each time => new random token => mismatch!
// Test: decrypt the cookie value EXACTLY as Laravel does (with CookieValuePrefix):

$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$keys = $redis->keys('*');
$real = array_map(function ($k) {
    return str_replace('sistem-informasi-sekretariat-database-', '', $k);
}, $keys);
$bestTtl = -1;
$bestSid = null;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (preg_match('/_token":"([A-Za-z0-9+\/=_-]{20,})"/', $val, $m) && $redis->ttl($rk) > $bestTtl) {
        $bestTtl = $redis->ttl($rk);
        $bestSid = str_replace('sistem-informasi-sekretariat-cache-', '', $rk);
    }
}

$enc = $app->make('encrypter');
$raw = $enc->encrypt($bestSid, false); // no serialization prefix
echo 'sid: ' . substr($bestSid, 0, 12) . "\n";
echo 'raw encrypt: ' . substr($raw, 0, 20) . "\n";

// Now compare with how EncryptCookies+CookieValuePrefix works:
$prefixed = Illuminate\Cookie\CookieValuePrefix::create('sistem-informasi-sekretariat-session', $enc->getKey()) . $raw;
echo 'prefixed first 20: ' . substr($prefixed, 0, 20) . "\n";

// decrypt back
try {
    $dec = $enc->decrypt($raw, false);
    echo 'decrypt raw ok: ' . $dec . "\n";
} catch (Throwable $e) {
    echo 'decrypt raw err: ' . $e->getMessage() . "\n";
}