<?php

// Session stored in cache via RedisStore with its own prefix.
// RedisStore::prefix = config cache prefix = '' probably. Then keys are {redis options prefix}{cache:...}? 
// But scan shows only "...-cache-xxx" keys = those are sessions?? No, those come from cache:clear etc.
// The session keys in RedisStore would be: prefix("database-") + store key
// RedisStore uses $this->setPrefix(config cache prefix). Session via cache uses "cache" store config?
// Actually CacheBasedSessionHandler uses cache store configured in config/cache.php
// and key = session-id directly. RedisStore::prefix comes from config('database.redis.cache.prefix') = ''
// BUT the store applies "phpredis" prefix "sistem-informasi-sekretariat-database-" + maybe "laravel_cache"?? 
// Actual keys observed: sistem-informasi-sekretariat-database-sistem-informasi-sekretariat-cache-HASH
// => prefix from database.redis.options.prefix + cache prefix "sistem-informasi-sekretariat-cache-"??
// Hmm that "sistem-informasi-sekretariat-cache" comes from config('database.redis.cache.prefix')?

require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cacheStore = $app->make('cache')->store('redis');
$ref = new ReflectionClass($cacheStore);
foreach ($ref->getProperties() as $p) {
    if ($p->getName() === 'store') {
        $p->setAccessible(true);
        $store = $p->getValue($cacheStore);
        $r = new ReflectionClass($store);
        $pp = $r->getProperty('prefix');
        $pp->setAccessible(true);
        echo "cache store prefix: '" . $pp->getValue($store) . "'\n";
        echo "store class: " . get_class($store) . "\n";
    }
}

// Test: put a session key manually
$cacheStore->put('sess_test_123', 'HELLO', 60);
$found = $app->make('redis')->connection()->keys('*sess_test_123*');
echo "test key written: " . count($found) . "\n";
foreach ($found as $k) {
    echo "  $k\n";
}
$cacheStore->forget('sess_test_123');