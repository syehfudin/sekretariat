<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);

$rm = $app->make('redis');
$redis = $rm->connection();
var_dump(get_class($rm));
var_dump(get_class($redis));
exit;