<?php
require '/var/www/html/vendor/autoload.php';
define('LARAVEL_START', microtime(true));
$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$source = file_get_contents('/var/www/html/resources/views/roles/index.blade.php');
$compiler = $app->make('blade.compiler');
$compiled = $compiler->compileString($source);

// check balance
echo "count endif: " . substr_count($compiled, 'endif') . "\n";
echo "count endif;: " . substr_count($compiled, '<?php endif; ?>') . "\n";
echo "count if(: " . substr_count($compiled, 'if(') . "\n";
echo "count forelse close: " . substr_count($compiled, 'endforeach; endif;') . "\n";
// Save compiled to inspect
file_put_contents('/var/www/html/compiled-view.txt', $compiled);
echo "saved\n";