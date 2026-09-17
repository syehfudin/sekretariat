<?php

// Compare tokens: what's in session vs what form displays
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Session driver redis - check a session key from redis
$redis = $app->make('redis');
$prefix = config('database.redis.options.prefix', '');
echo "redis prefix: '$prefix'\n";
$keys = $redis->keys('*');
$sessionKeys = array_filter($keys, fn ($k) => strpos($k, 'laravel_cache') === false);
echo "total keys: " . count($keys) . "\n";
foreach (array_slice(array_values($sessionKeys), 0, 3) as $k) {
    echo "key: " . substr($k, -40) . "\n";
}

// Check encrypted CSRF - maybe session encrypt => csrf in session
$sessionConfig = config('session.encrypt');
echo "session.encrypt: " . var_export($sessionConfig, true) . "\n";
echo "APP_KEY in env: " . substr((string)config('app.key'), 0, 15) . "\n";