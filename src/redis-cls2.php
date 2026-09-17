<?php
require '/var/www/html/vendor/autoload.php';
var_dump(class_exists(Illuminate\Redis\RedisManager::class));
var_dump(class_exists('Redis'));