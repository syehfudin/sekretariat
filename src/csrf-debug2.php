<?php

// Check VerifyCsrfToken behavior - maybe tokens rotated every request?
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Look at VerifyCsrfToken config - tokens config in config/cors, middleware exclusions
echo "csrf middleware excluded paths: ";
var_dump(config('csrf.except', null));

// Check if APP_URL mismatch triggers referer check: Laravel doesn't check referer.
// 419 = token mismatch. Token is per-session. Regenerate happens on login.
// BUT - session cookie name contains "sistem-informasi-sekretariat" - is it > valid?

// Test decrypt XSRF cookie
$xsrfCookie = 'eyJpdiI6InIxWnI1ekJtTm9PSmE3SzE2NnhkVFE9PSIsInZhbHVlIjoiYjhXeXBTUG1JNkc3STRId05zekpzT1MzTUNzV3ZycHA5aWJBdmF1SWhuQmhVOTdjT29xeFRoQnVuaGJkMHowS21jRUcxZHFtbU40ZkFoc3lBRzZjSVNhdHYveXRTSm9vc3h6M2ZWUy9saWM1T21pdC9nTjFPbUFsa1BLN1UxdUoiLCJtYWMiOiJhODAzZmE0MWYyZmQ1YzYzZjljOWMzMzNmMjFkZWEyZDQ5NmFhZTU1ZWZhODIxZjM5MDY0ZjgzY2FiOTU2YzQ2IiwidGFnIjoiIn0%3D';
$encrypter = $app->make('encrypter');
try {
    $decrypted = $encrypter->decrypt(urldecode($xsrfCookie));
    echo "xsrf decrypted: " . $decrypted = substr($decrypted = $decrypted ?? $decrypted = $decrypted ?? $decrypted ?? $decrypted ?? $decrypted ?? $decrypted ?? '', 0) . "\n";
} catch (Throwable $e) {
    echo "decrypt err: " . $e->getMessage() . "\n";
}
try {
    $val = $encrypter->decrypt(urldecode($xsrfCookie), false);
    var_dump(is_array($val) ? $val['value'] ?? $val : $val);
} catch (Throwable $e) {
    echo "decrypt err2: " . $e->getMessage() . "\n";
}