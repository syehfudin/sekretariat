<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

// Get cookie from a REAL browser-like flow: take our captured sidval (from real curl),
// which decrypts successfully. Extract EXACT cookie value string that real flow had.
// Our earlier capture: SID from jar decrypts to hash|tEENa5o... with part2 being SID.
// But Laravel EncryptCookies decrypt expects array with 'value'. part2 IS valid sid.

// The 419 in kernel test: cookie decrypt path OK (sid resolved, session loaded,
// token matched) yet 419. => The only remaining check: hash_equals passed but
// Origin check threw earlier? No: hasValidOrigin throws OriginMismatchException
// (500-class), not TokenMismatch. With Sec-Fetch-Site absent → falls to tokensMatch.

// WAIT. Look again at kernel-post8 output: 'session-token-now' was OTuCq... equal
// provided. But we extracted from redis AFTER handle. The session rotated (Set-Cookie
// present) so redis now holds ROTATED token. Equal to provided => the ROTATED token
// equals the OLD provided token?? That happens when CSRF middleware calls
// session()->regenerateToken() then adds cookie... on SUCCESSFUL pass.
// So the middleware PASSED and then route/middleware stack threw TokenMismatch later?!
// Who else throws TokenMismatch? Only this middleware.

// => Maybe it's not 419-from-csrf! The 419 template may render for OTHER exception.
// Laravel converts TokenMismatch to 419 via exception handler. What else maps to 419?
// Check Exceptions.php config in this app + framework mapping:
$handler = $app->make(Illuminate\Contracts\Debug\ExceptionHandler::class);
$ref = new ReflectionClass($handler);
echo 'handler class: ' . get_class($handler) . "\n";
$prop = $ref->getProperty('app');
$prop->setAccessible(true);
$inner = $prop->getValue($handler);
$ref2 = new ReflectionClass($inner);
foreach ($ref2->getProperties() as $p2) {
    if (str_contains($p2->getName(), 'exception') || str_contains($p2->getName(), 'map')) {
        echo 'prop: ' . $p2->getName() . "\n";
    }
}