<?php

// Sessions ALSO stored via cache (CacheBasedSessionHandler) but no session-* keys in redis?
// Because session cookie encryption? No. Let's create session directly and check redis.
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$manager = $app->make('session');
$driver = $manager->driver();
$ref = new ReflectionClass($driver);
$handlerProp = null;
foreach ($ref->getProperties() as $p) {
    if ($p->getName() === 'handler') {
        $p->setAccessible(true);
        $handlerProp = $p->getValue($driver);
    }
}
echo "handler: " . get_class($handlerProp) . "\n";
$r = new ReflectionClass($handlerProp);
foreach ($r->getProperties() as $p) {
    $p->setAccessible(true);
    $v = $p->getValue($handlerProp);
    if (is_object($v)) {
        echo "  -> " . get_class($v) . "\n";
        $r2 = new ReflectionClass($v);
        foreach ($r2->getProperties() as $p2) {
            $p2->setAccessible(true);
            $v2 = $p2->getValue($v);
            if (is_object($v2)) {
                echo "    => " . get_class($v2) . "\n";
            }
        }
    }
}