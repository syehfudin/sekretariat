<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

// SID from real jar is acm1HJzFv7lLdnAx... session authenticated (login_web present)!
// token: 8WVRfWcBVpOU1MfBCvcDkxl7G2xwWf2uz054f4Ym

// The csrf2 curl got from GET create page was mCPCPCi80b7F... which is DIFFERENT
// because the GET create response ROTATED the session (Set-Cookie new sid)!
// So the token in form belongs to NEW sid, but jar after GET has new session cookie too.
// POST then uses newest jar. Then 419?? 

// Wait - curl -c updates jar. The last GET's Set-Cookie rotated sid => jar has newest sid
// whose session contains csrf2. POST with that cookie should match csrf2.

// BUT the POST response sets ANOTHER Set-Cookie. If the ROTATION happens on every
// request (session id regeneration), then after GET the jar gets sid_N. POST with
// sid_N works... 

// Test: does GET /anggota/create with auth return Set-Cookie session rotation?
// We'll replicate with kernel: handle GET create with authenticated jar and check
// response Set-Cookie presence.

$enc = $app->make('encrypter');
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

$sid = 'acm1HJzFv7lLdnAx0M4cHh6C5VQ2VnYzR3ZTBmNzU5NzUw'; // placeholder wrong; we need full sid
// Reconstruct: full sid from decrypt earlier
$cookie = 'eyJpdiI6ImM0TlRLRm1PaG42MUlpdjFnaVNDY2c9PSIsInZhbHVlIjoiMCtIWEtWendyN0xPRlRJV0FBZGJUdlhoM28vQmJrQnRLdGZlSUFjSUVmenE2VklvWVlKdWx1MkVoMVMyZjRNVkJ5V1dkOEJQbHNlQ24rRVoxZTgra3VPNnNWRXQxVVRJQndnMVBlSVhhdi9VYVJXdkF4d0thckw0NFVFNnA5MWMiLCJtYWMiOiIwOTUzOTQ5NGM1OGM1MGUyNjYyZTQ5NWEzOGZhMjE0YWNkZjk0ZDFkMDE0MTYxM2IwN2QyNWQ5OGJhMzQwZDZlIiwidGFnIjoiIn0=';
$dec = $enc->decrypt(urldecode($cookie), false);
$parts = explode('|', $dec);
$fullSid = $parts[1];
echo 'full sid: ' . substr($fullSid, 0, 20) . '...' . "\n";

// Now the question: after GET /anggota/create with this cookie, does response rotate?
$req = Illuminate\Http\Request::create('/anggota/create', 'GET');
$req->headers->set('Cookie', 'sistem-informasi-sekretariat-session=' . urlencode($cookie));
$resp = $kernelHttp->handle($req);
echo 'GET status: ' . $resp->getStatusCode() . "\n";
foreach ($resp->headers->getCookies() as $c) {
    if (str_contains($c->getName(), 'session')) {
        $newVal = $c->getValue();
        try {
            $dec2 = $enc->decrypt(urldecode($newVal), false);
            $parts2 = explode('|', $dec2);
            echo 'response sid: ' . substr($parts2[1], 0, 20) . "...\n";
            echo 'rotated: ' . var_export($parts2[1] !== $fullSid, true) . "\n";
        } catch (Throwable $e) {
            echo 'dec err: ' . $e->getMessage() . "\n";
        }
    }
}