<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

try {
    $compiled = app('blade.compiler')->compileString(file_get_contents('resources/views/partials/showcase.blade.php'));
    file_put_contents('scratch/compiled_showcase.php', $compiled);
    echo "Compiled successfully! Length: " . strlen($compiled) . "\n";
    
    // Now syntax check the compiled php
    $output = [];
    $returnVar = 0;
    exec('php -l scratch/compiled_showcase.php', $output, $returnVar);
    echo implode("\n", $output) . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
