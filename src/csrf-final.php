<?php

// Compare: session token in redis vs token returned in form
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$redis = $app->make('redis')->connection();
$keys = $redis->keys('*');

// The double-prefix phenomenon: phpredis keys() returns keys with its own prefix applied.
// Real key = keys()[i] minus the client's prefix ("sistem-informasi-sekretariat-database-")
$real = array_map(fn ($k) => str_replace('sistem-informasi-sekretariat-database-', '', $k), $keys);

$latest = null;
foreach ($real as $rk) {
    if (! str_contains($rk, 'cache-')) {
        continue;
    }
    $val = $redis->get($rk);
    if ($val && str_contains((string) $val, '_token')) {
        // extract session id part
        $sid = str_replace('sistem-informasi-sekretariat-cache-', '', $rk);
        echo "SID: " . substr($sid, 0, 20) . "\n";
        preg_match('/_token\\\\?"[;,]?\s*[\"s:]*(\d+:"[A-Za-z0-9+\/=_-]+)"/', $val, $m);
        if (preg_match('/_token":"([A-Za-z0-9+\/=_-]{20,})"/', $val, $mm)) {
            echo "  token: " . substr($mm[1], 0, 20) . "...\n";
        }
    }
}