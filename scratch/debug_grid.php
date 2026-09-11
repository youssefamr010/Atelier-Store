<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
@mkdir(storage_path('framework/views'), 0777, true);
config(['view.compiled' => storage_path('framework/views')]);

use App\Services\ProductCardService;
use App\Models\Product;

$products = Product::with(['variants.mediaAssets', 'mediaAssets', 'collections'])->get();
$allCards = ProductCardService::toDisplayCards($products, false);
$collections = \App\Models\Collection::where('status', 'active')->orderBy('sort_order')->get();
$categoryCounts = ProductCardService::getCategoryCardCounts($products, false);

view()->share('errors', new \Illuminate\Support\ViewErrorBag());
$html24 = view('collections', [
    'settings' => ['storefront_lang' => 'en', 'storeName' => 'ATELIER'],
    'displayCards' => $allCards,
    'categoryCounts' => $categoryCounts,
    'collections' => $collections,
    'cartItems' => [],
    'currentSlug' => 'all',
    'collectionTitle' => 'All Products',
])->render();

// Count card indicators (the "View →" button on each card)
$viewArrow = 'View →';
$count = substr_count($html24, $viewArrow);
echo "Cards with 'View →' button: $count\n";

// Also check 0-card render
$html0 = view('collections', [
    'settings' => ['storefront_lang' => 'en', 'storeName' => 'ATELIER'],
    'displayCards' => collect(),
    'categoryCounts' => [],
    'collections' => $collections,
    'cartItems' => [],
    'currentSlug' => 'all',
    'collectionTitle' => 'All Products',
])->render();

echo "0-card: contains 'grid-cols-2'? " . (str_contains($html0, 'grid-cols-2') ? 'YES' : 'NO') . "\n";
echo "0-card: contains 'No pieces'? " . (str_contains($html0, 'No pieces') ? 'YES' : 'NO') . "\n";
echo "0-card: contains 'empty'? " . (str_contains($html0, 'empty') ? 'YES' : 'NO') . "\n";
// Save snippet for inspection
echo "0-card snippet around empty: " . substr($html0, strpos($html0, 'found in') - 50, 200) . "\n";
