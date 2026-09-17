<?php

// Redis keys prefixed but phpredis "keys()" returns full names; type=0 means NONE (nonexistent!)
// Keys() with prefix returns double-prefixed keys. Use connection directly.
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$redis = $app->make('redis');
$conn = $redis->connection(); // default
$allKeys = $conn->keys('*');
echo "keys: " . count($allKeys) . "\n";
$withData = 0;
foreach ($allKeys as $k) {
    $type = $conn->type($k);
    if ($type !== -2 && $type !== 0) {
        $withData++;
        if ($withData <= 5) {
            $ttl = $conn->ttl($k);
            echo "LIVE: " . substr($k, -25) . " type=$type ttl=$ttl\n";
            if ($type === 1) {
                $payload = $conn->get($k);
                $data = json_decode($payload, true);
                if (is_array($data)) {
                    echo "  fields: " . implode(',', array_slice(array_keys($data), 0, 8)) . "\n";
                    if (isset($data['_token'])) {
                        echo "  _token: " . substr($data['_token'], 0, 15) . "\n";
                    }
                }
            }
        }
    }
}
echo "keys with data: $withData / " . count($allKeys) . "\n";