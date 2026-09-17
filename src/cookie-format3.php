<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernelHttp->bootstrap();

$enc = $app->make('encrypter');
$cookie = trim(file_get_contents('/var/www/html/sidval2.txt'));
$dec = $enc->decrypt(urldecode($cookie), false);
echo 'decrypted: ';
var_dump($dec);
$parts = explode('|', $dec);
echo 'part1 (prefix hash): ' . substr($parts[0], 0, 12) . "\n";
echo 'part2 (value): ' . $parts[1] . "\n";

$rmClass = Illuminate\Redis\RedisManager::class;
$rm = new $rmClass($app, config('database.redis.client'), config('database.redis'));
$redis = $rm->connection();
$key = 'sistem-informasi-sekretariat-cache-' . $parts[1];
echo 'redis key exists: ';
var_dump((bool) $redis->exists($key));
echo 'payload: ' . substr((string) $redis->get($key), 0, 200) . "\n";