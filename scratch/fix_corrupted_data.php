<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING CORRUPTED VARIANT DATA ===\n\n";

$variants = \App\Models\ProductVariant::all();
foreach ($variants as $v) {
    $attrs = $v->attributes_json ?? [];
    $attrs['image_url'] = $v->image_url;
    $v->attributes_json = $attrs;
    $v->save();
    echo "Synchronized Variant #{$v->id} [{$v->title}] attributes_json.image_url => '{$v->image_url}'\n";
}

echo "\nAll variant records synchronized successfully!\n";
