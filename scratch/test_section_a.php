<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ProductController;

echo "========================================================\n";
echo "TEST 1: DUPLICATE / CLONE VARIANT LOGIC\n";
echo "========================================================\n";

$admin = User::first();
if ($admin) {
    Auth::login($admin);
}

$product = Product::with(['variants.mediaAssets'])->has('variants', '>=', 1)->first();
$original = $product->variants->first();

echo "Original Variant: #{$original->id} ({$original->title}) | SKU: {$original->sku} | Stock: {$original->inventory}\n";

// Add a test media asset to original to test copying
$testAsset = MediaAsset::create([
    'type' => 'image',
    'url' => '/storage/media/test-clone.jpg',
    'filename' => 'test-clone.jpg',
    'mime_type' => 'image/jpeg',
    'size_bytes' => 1024,
]);
$original->mediaAssets()->attach($testAsset->id, ['group' => 'gallery', 'sort_order' => 1]);

$controller = app(ProductController::class);
$initialCount = $product->variants()->count();

// Call duplicateVariant
$response = $controller->duplicateVariant($original->id);

$product->refresh();
$cloned = $product->variants()->latest('id')->first();

echo "Cloned Variant: #{$cloned->id} ({$cloned->title}) | SKU: {$cloned->sku} | Stock: {$cloned->inventory}\n";

assert($product->variants()->count() === $initialCount + 1, "Variant count should increase by 1");
assert(str_contains($cloned->title, '(Copy)'), "Cloned variant title should contain '(Copy)'");
assert($cloned->sku !== $original->sku, "Cloned SKU must be unique");
assert($cloned->mediaAssets()->count() === 1, "Cloned variant must inherit original gallery media asset");
assert($cloned->mediaAssets()->first()->id === $testAsset->id, "Cloned media asset ID must match");
echo "✓ Variant duplication verified successfully!\n";

echo "\n========================================================\n";
echo "TEST 2: INLINE QUICK-EDIT ENDPOINT LOGIC\n";
echo "========================================================\n";

// Test price_override inline edit
$req1 = Request::create("/admin/variants/{$cloned->id}/inline-edit", 'POST', [
    'field' => 'price_override',
    'value' => '899.50',
]);
$res1 = $controller->inlineEditVariant($req1, $cloned->id);
$data1 = json_decode($res1->getContent(), true);

assert($data1['success'] === true, "Inline price update must succeed");
assert($cloned->fresh()->price_override_minor === 89950, "Price override minor must be 89950");
echo "✓ Inline price update verified: " . $data1['formatted'] . "\n";

// Test inventory inline edit
$req2 = Request::create("/admin/variants/{$cloned->id}/inline-edit", 'POST', [
    'field' => 'inventory',
    'value' => '42',
]);
$res2 = $controller->inlineEditVariant($req2, $cloned->id);
$data2 = json_decode($res2->getContent(), true);

assert($data2['success'] === true, "Inline stock update must succeed");
assert($cloned->fresh()->inventory === 42, "Stock must be 42");
echo "✓ Inline stock update verified: " . $data2['formatted'] . "\n";

echo "\n========================================================\n";
echo "TEST 3: BLADE VIEW & SECTION A UI VERIFICATION\n";
echo "========================================================\n";

$rendered = view('admin.products.edit', [
    'product' => $product->load('variants.mediaAssets', 'mediaAssets', 'collections'),
    'collections' => collect(),
    'errors' => new \Illuminate\Support\ViewErrorBag(),
])->render();

assert(str_contains($rendered, 'quickSaveVariantField'), "quickSaveVariantField JS function must be referenced in inputs");
assert(str_contains($rendered, 'dup-var-'), "Duplicate form dup-var- must be present on variant cards");
assert(str_contains($rendered, 'dup-warning-'), "Duplicate warning container must be present");
assert(str_contains($rendered, 'color-suggest-badge-'), "Color suggest badge must be present");
assert(str_contains($rendered, 'COLOR_PALETTE'), "COLOR_PALETTE dictionary must be in JS scripts");
assert(str_contains($rendered, 'getColorDistance'), "getColorDistance algorithm must be in JS scripts");
echo "✓ All Section A UI elements and client-side features are present in Blade template!\n";

// Cleanup
$cloned->mediaAssets()->detach();
$cloned->delete();
$original->mediaAssets()->detach($testAsset->id);
$testAsset->delete();

echo "\n========================================================\n";
echo "ALL SECTION A TESTS PASSED! ZERO ERRORS.\n";
echo "========================================================\n";
