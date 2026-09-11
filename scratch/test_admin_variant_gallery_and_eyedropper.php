<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$admin = User::first();
if ($admin) {
    Auth::login($admin);
}

echo "========================================================\n";
echo "TEST 1: VARIANT GALLERY ISOLATION & UPLOADS\n";
echo "========================================================\n";

// Pick a product with at least 2 variants
$product = Product::with(['variants.mediaAssets'])->has('variants', '>=', 2)->first();

if (!$product) {
    echo "FAILED: No product with >= 2 variants found.\n";
    exit(1);
}

$variants = $product->variants;
$var1 = $variants[0];
$var2 = $variants[1];

echo "Product: #{$product->id} - {$product->title}\n";
echo "Variant 1: #{$var1->id} - {$var1->title}\n";
echo "Variant 2: #{$var2->id} - {$var2->title}\n";

// Create a test media asset and attach to Variant 1
$testAsset1 = MediaAsset::create([
    'type' => 'image',
    'url' => '/storage/media/test-variant-1.jpg',
    'filename' => 'test-variant-1.jpg',
    'mime_type' => 'image/jpeg',
    'size_bytes' => 1024,
]);

$var1->mediaAssets()->attach($testAsset1->id, ['group' => 'gallery', 'sort_order' => 1]);

// Reload fresh
$var1->refresh();
$var2->refresh();

echo "Variant 1 Media count: " . $var1->mediaAssets()->count() . "\n";
echo "Variant 2 Media count: " . $var2->mediaAssets()->count() . "\n";

assert($var1->mediaAssets()->count() >= 1, "Variant 1 should have >= 1 media asset");
assert($var2->mediaAssets()->count() === 0, "Variant 2 should have 0 media assets (ISOLATION CONFIRMED)");
echo "✓ Variant gallery isolation confirmed: Media attached to Variant 1 does NOT appear in Variant 2!\n";

// Test rendering edit blade
$rendered = view('admin.products.edit', [
    'product' => $product->load('variants.mediaAssets', 'mediaAssets', 'collections'),
    'collections' => collect(),
    'errors' => new \Illuminate\Support\ViewErrorBag(),
])->render();

assert(str_contains($rendered, 'del-var-media-' . $var1->id . '-' . $testAsset1->id), "Delete form for Variant 1 asset must be in view");
assert(!str_contains($rendered, 'del-var-media-' . $var2->id . '-' . $testAsset1->id), "Delete form for Variant 2 must NOT have Variant 1 asset");
echo "✓ Blade view renders correct per-variant gallery and delete forms!\n";

echo "\n========================================================\n";
echo "TEST 2: EYEDROPPER UI ELEMENTS\n";
echo "========================================================\n";

assert(str_contains($rendered, 'pickColorFromImage'), "Eyedropper function pickColorFromImage must exist in Blade view");
assert(str_contains($rendered, 'var-color-picker-' . $var1->id), "Variant color picker input must exist in Blade view");
assert(str_contains($rendered, 'var-color-hex-' . $var1->id), "Variant hex input must exist in Blade view");
assert(str_contains($rendered, 'canvas-color-picker-modal'), "Canvas fallback modal must exist in Blade view");
assert(str_contains($rendered, 'window.EyeDropper'), "Native EyeDropper API logic must exist in scripts");
echo "✓ All Eyedropper UI elements and fallback scripts are present!\n";

// Clean up test asset
$var1->mediaAssets()->detach($testAsset1->id);
$testAsset1->delete();

echo "\n========================================================\n";
echo "ALL TESTS PASSED SUCCESSFULLY! ZERO ERRORS.\n";
echo "========================================================\n";
