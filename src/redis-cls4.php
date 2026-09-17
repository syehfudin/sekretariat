<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$rm = $app->make('redis');
echo 'class: ' . get_class($rm) . "\n";