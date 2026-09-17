<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$enc = $app->make('encrypter');
$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();

// Decrypt the EXACT session cookie from the last curl jar to find its sid & token
$cookie = 'eyJpdiI6ImM0TlRLRm1PaG42MUlpdjFnaVNDY2c9PSIsInZhbHVlIjoiMCtIWEtWendyN0xPRlRJV0FBZGJUdlhoM28vQmJrQnRLdGZlSUFjSUVmenE2VklvWVlKdWx1MkVoMVMyZjRNVkJ5V1dkOEJQbHNlQ24rRVoxZTgra3VPNnNWRXQxVVRJQndnMVBlSVhhdi9VYVJXdkF4d0thckw0NFVFNnA5MWMiLCJtYWMiOiIwOTUzOTQ5NGM1OGM1MGUyNjYyZTQ5NWEzOGZhMjE0YWNkZjk0ZDFkMDE0MTYxM2IwN2QyNWQ5OGJhMzQwZDZlIiwidGFnIjoiIn0=';
$dec = $enc->decrypt(urldecode($cookie), false);
$parts = explode('|', $dec);
$sid = $parts[1];
echo "sid: " . substr($sid, 0, 16) . "\n";
$val = (string) $redis->get('sistem-informasi-sekretariat-cache-' . $sid);
echo 'payload: ' . substr($val, 0, 300) . "\n";
preg_match('/_token":"([A-Za-z0-9+\/=_-]{20,})"/', $val, $m);
echo 'session token: ' . ($m[1] ?? 'none') . "\n";