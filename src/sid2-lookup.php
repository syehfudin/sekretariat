<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$redis = $app->make('redis')->connection();

// Session for sid tEENa5ozaWyXjpBwyQEGZtL4oppggbJj6RlrIa9F (newest, ttl 7096)
// but our earlier lookup used the FULL decrypted part2 "df2427...|tEENa5o..." hmm
// actually part2 IS the session id: tEENa5ozaWyXjpBwyQEGZtL4oppggbJj6RlrIa9F
$key = 'sistem-informasi-sekretariat-cache-tEENa5ozaWyXjpBwyQEGZtL4oppggbJj6RlrIa9F';
echo 'exists: ';
var_dump((bool) $redis->exists($key));
$val = (string) $redis->get($key);
echo 'len: ' . strlen($val) . "\n";
echo 'payload: ' . substr($val, 0, 250) . "\n";
preg_match('/_token\\\\?";s:\d+:"([^"]+)"/', $val, $m);
echo 'token: ' . ($m[1] ?? 'NOT_FOUND') . "\n";