<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$app->instance('request', \Illuminate\Http\Request::create('/'));

echo "=== PART 1 VERIFICATION: CATALOG AND PRODUCT DETAIL PAGE ===\n\n";

// 1. Check Product Detail Page
$product = \App\Models\Product::with(['variants.mediaAssets', 'mediaAssets', 'collections'])->find(9);
$html = view('product', [
    'product' => $product,
    'selectedVariantId' => 24,
    'settings' => []
])->render();

echo "Product Detail Page Rendered successfully (Length: " . strlen($html) . " bytes)\n";

// Check if Blue variant json has correct image
if (preg_match('/variants:\s*(\[.*?\]),/s', $html, $m)) {
    $variantsData = json_decode($m[1], true);
    foreach ($variantsData as $vd) {
        echo "  Detail Variant ID {$vd['id']} [{$vd['title']}]:\n";
        echo "    image: {$vd['image']}\n";
        echo "    gallery: " . implode(', ', $vd['gallery']) . "\n";
    }
}

// 2. Check Storefront Cards
$products = \App\Models\Product::active()->where('id', 9)->with(['variants.mediaAssets', 'mediaAssets', 'collections'])->get();
$cards = \App\Services\ProductCardService::toDisplayCards($products);
echo "\nStorefront Catalog Cards for Product #9:\n";
foreach ($cards as $c) {
    echo "  Card [{$c->display_title}]:\n";
    echo "    Primary: {$c->image}\n";
    echo "    Hover: {$c->hover_image}\n";
    echo "    Color Hex: {$c->color_hex}\n";
}

echo "\n✅ PART 1 VERIFICATION COMPLETE!\n";
