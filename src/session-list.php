<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$redis = $app->make('redis')->connection();

// list all cache-HASH keys and print their TTL + token to find newest session
$keys = $redis->keys('*');
$real = array_map(fn ($k) => str_replace('sistem-informasi-sekretariat-database-', '', $k), $keys);

$rows = [];
foreach ($real as $rk) {
    if (! str_contains($rk, 'sistem-informasi-sekretariat-cache-')) {
        continue;
    }
    $val = (string) $redis->get($rk);
    if (str_contains($val, '_token')) {
        preg_match('/_token\\\\?";s:\d+:"([^"]+)"/', $val, $m);
        $ttl = $redis->ttl($rk);
        $rows[] = ['sid' => substr(str_replace('sistem-informasi-sekretariat-cache-', '', $rk), 0, 12), 'ttl' => $ttl, 'token' => $m[1] ?? '?', 'val' => $val];
    }
}
usort($rows, fn ($a, $b) => $b['ttl'] <=> $a['ttl']);
echo "total session rows: " . count($rows) . "\n";
foreach (array_slice($rows, 0, 5) as $r) {
    echo "sid={$r['sid']} ttl={$r['ttl']} token=" . substr($r['token'], 0, 15) . " len=" . strlen($r['val']) . "\n";
}