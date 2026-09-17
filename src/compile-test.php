<?php
// Direct compile of roles view source to see exact PHP output
$source = file_get_contents('/var/www/html/resources/views/roles/index.blade.php');
$compiler = new Illuminate\View\Compilers\BladeCompiler(
    new Illuminate\View\Filesystem,
    '/tmp/blade-test'
);
$compiled = $compiler->compileString($source);
echo $compiled;