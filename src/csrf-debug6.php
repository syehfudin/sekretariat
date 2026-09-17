<?php

// Session keys missing from redis?! Only cache keys exist. Sessions in Redis use
// a different prefix: {REDIS_PREFIX}{session cookie connection}. Check config:
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "redis options prefix: " . config('database.redis.options.prefix') . "\n";
echo "redis cache prefix: " . config('database.redis.cache.prefix') . "\n";
echo "session connection: " . var_export(config('session.connection'), true) . "\n";

// Laravel session in redis uses cache store? No - its own handler with prefix.
// Check what prefix session handler uses:
$sessionManager = $app->make('session');
$driver = $sessionManager->driver();
$ref = new ReflectionClass($driver);
foreach ($ref->getProperties() as $p) {
    $p->setAccessible(true);
    $v = $p->getValue($driver);
    if (is_object($v)) {
        echo "session handler prop: " . get_class($v) . "\n";
        $r2 = new ReflectionClass($v);
        foreach ($r2->getProperties() as $p2) {
            $p2->setAccessible(true);
            $v2 = $p2->getValue($v);
            if (is_string($v2) && str_contains($v2, 'prefix')) {
                echo "  {$p2->getName()}: $v2\n";
            }
        }
    }
}