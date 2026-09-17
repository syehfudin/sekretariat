<?php

// Cache put+get works, but raw KEYS shows nothing => RedisStore uses Redis
// with a DIFFERENT serialization (serialize+encrypt?) and maybe IGBINARY... 
// The point: session data IS being stored (login worked before!). 
// So redis cache works. The 419 means session lost between GET and POST requests.

// KEY QUESTION: is the session cookie sent by curl identical between requests?
// Curl jar shows same session cookie. So maybe TWO session cookies with same name
// (one set by earlier pages without HttpOnly?) confuse the request.

// Actually! Look: cookie name is 40+ chars "sistem-informasi-sekretariat-session" — 
// but XSRF-TOKEN cookie + session cookie: Laravel CSRF compares form token against
// session['_token']. If session ID rotates between GET-create and POST (because
// response regenerated), token invalid.

// Test: capture session ID (session cookie value) after login vs after GET create
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$redis = $app->make('redis')->connection();
$all = $redis->keys('*');
echo "all raw keys now: " . count($all) . "\n";
foreach ($all as $k) {
    $type = $redis->type($k);
    if ($type == Redis::REDIS_STRING) {
        $p = $redis->get($k);
        $arr = json_decode($p, true);
        if (is_array($data = json_decode($p, true) ?: [])) {
            if (isset($arr['_token'])) {
                echo "SESSION KEY: " . substr($k, -30) . " token=" . substr($arr['_token'], 0, 10) . "\n";
            }
        }
    }
}