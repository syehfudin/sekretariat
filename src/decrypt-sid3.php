<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$enc = $app->make('encrypter');

$sid = trim(file_get_contents('/var/www/html/sidval.txt'));
$dec = $enc->decrypt(urldecode($sid), false);
$arr = is_array($dec) ? $dec : ['raw' => $dec];
$value = is_array($dec) ? $dec['value'] : $dec;
echo "RAW: $value\n";

// value = hash|token format for CSRF cookie. But this is SESSION cookie - its 'value'
// should be plain session id. The hash|token format is for XSRF-TOKEN!
// Did capture-sid.sh grab the XSRF cookie instead of session cookie? grep sekretariat-session...
// Let's check what jar had:
echo "---\n";
$enc2 = $app->make('encrypter');

// Decrypt the session cookie by decrypting without serialization and extracting value field
$parts = explode('|', $value);
echo "parts: " . count($parts) . " -> part2 = " . ($parts[1] ?? 'none') . "\n";
echo "part1: " . ($parts[0] ?? 'none') . "\n";