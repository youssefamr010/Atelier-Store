<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = App\Models\Product::with(['variants.mediaAssets', 'mediaAssets', 'collections'])->get();
echo "Total Products: " . $products->count() . "\n";
foreach ($products as $p) {
    echo "Product #{$p->id}: {$p->title} | Status: {$p->status} | Variants: " . $p->variants->count() . " | MediaAssets: " . $p->mediaAssets->count() . "\n";
    foreach ($p->variants as $v) {
        $variantMedia = $v->mediaAssets->count();
        echo "   -> Variant #{$v->id}: '{$v->title}' | SKU: {$v->sku} | Price: {$v->retail_price_minor} | image_url: " . ($v->image_url ?? 'NULL') . " | MediaAssets: {$variantMedia}\n";
    }
}
