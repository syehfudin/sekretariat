<?php

// THE TEST: POST directly to a route WITHOUT CSRF (none in web... but /up health)
// Better: temporarily add exception for /anggota POST via app bootstrap middleware
// config. But root cause first: compare session token from redis BEFORE POST and
// token in form. The 419 message body should tell "CSRF token mismatch".
// Laravel 13 ValidateCsrfToken: tokensMatch via hash_equals(session token, provided).
// Session token: from THIS sid. Provided: _token field. Both from same page.

// Possible: PHP redis client + Laravel session SERIALIZATION json — session payload
// we read is PHP-serialized wrapper s:151:...; fine.

// CRUCIAL: Maybe the POST request never carries the session cookie because of
// cookie size > 4096 header limit? Cookie is 342 chars - fine.

// Let's simulate the exact POST inside Laravel kernel with known cookies:
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Get the latest session token from redis
$redisManager = $app->make('redis');
$redis = $redisManager->connection();
$keys = $redis->keys('*');
$real = array_map(fn ($k) => str_replace('sistem-informasi-sekretariat-database-', '', $k), $keys);
$best = null;
$bestTtl = -1;
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, '_token') && $redis->ttl($rk) > $bestTtl) {
        $bestTtl = $redis->ttl($rk);
        $bestSid = str_replace('sistem-informasi-sekretariat-cache-', '', $rk);
        preg_match('/_token\\\\";s:\d+:"([^"]+)"/', $val, $m);
        $bestToken = $m[1] ?? null;
    }
}
echo "best sid: " . substr((string) $bestSid, 0, 15) . " ttl=$bestTtl\n";
echo "token: " . substr((string) $bestToken, 0, 15) . "\n";

// Now craft request with this session cookie (encrypted) and this token
$enc = $app->make('encrypter');
$cookieVal = urlencode($enc->encrypt($bestSid, false));
// Laravel cookie encryption wraps with serialize... encrypt($value, false) => raw
$cookieVal = urlencode($enc->encrypt($bestSid));

$request = Illuminate\Http\Request::create('/anggota', 'POST', [
    'nama_lengkap' => 'Dewi Lestari',
    'pekerjaan' => 'Perawat',
    '_token' => $bestToken,
]);
$request->cookies = new Symfony\Component\HttpFoundation\ParameterBag([
    'sistem-informasi-sekretariat-session' => $cookieVal,
]);
$request->server->set('REQUEST_METHOD', 'POST');

try {
    $response = $kernel->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 419) {
        echo 'BODY: ' . substr(strip_tags($response->getContent()), 0, 200) . "\n";
    }
} catch (Throwable $e) {
    echo 'EXC: ' . $e->getMessage() . "\n";
}
$kernel->terminate($request, $response ?? null);