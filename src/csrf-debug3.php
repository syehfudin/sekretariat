<?php

// XSRF cookie decrypts to "hash|token". The token part must match _token in form.
// From previous test: csrf2 form = yxmGuA0u9kEr2b59FenHaaOoF9LO120cCcXG5DUP (starts yxmGuA0u9k)
// XSRF cookie value part = yxmGuA0u9kEr2b59FenHaaOoF9LO120cCcXG5DUP — MATCH!
// So the XSRF-TOKEN cookie from the GET /anggota/create page contains the SAME token as the form.
// Yet POST with that token => 419.

// Hypothesis: session lost between GET and POST because cookie jar's session cookie
// was replaced but curl -b jar -c jar should keep... unless nginx POST response
// regenerates session ID. Check: after GET /anggota/create, session ID changed?
// Actually Laravel rotates session cookie only on regenerate. Let's simulate exact request
// with explicit Cookie header construction.

require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Direct request to route with token we know was valid for that session
$sessionModel = new Illuminate\Support\Fluent;

// Alternative: bypass HTTP - verify CSRF middleware accepts form token matching session
$redis = $app->make('redis');
$prefix = config('database.redis.options.prefix');
$keys = $redis->keys($prefix . '*');
$sessionKeys = array_values(array_filter($keys, fn ($k) => ! str_contains($k, 'laravel_cache')));
echo "session keys: " . count($sessionKeys) . "\n";

// Read the newest session payload
usort($sessionKeys, fn ($a, $b) => $redis->ttl($a) <=> $redis->ttl($b));
$payload = $redis->get(end($sessionKeys));
$data = json_decode($payload, true);
echo "session keys in payload: " . implode(',', array_keys($data ?? [])) . "\n";
if (isset($data['_token'])) {
    echo "session _token: " . substr($data['_token'], 0, 12) . "\n";
}