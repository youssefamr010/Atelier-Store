<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Http\Controllers\CartController;
use App\Services\ProductCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "====================================================\n";
echo "  SECTION 2 VERIFICATION TEST SUITE — ATELIER STORE\n";
echo "====================================================\n\n";

$passCount = 0;
$failCount = 0;

function testAssert($condition, $message) {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] " . $message . "\n";
        $passCount++;
    } else {
        echo " [FAIL] " . $message . "\n";
        $failCount++;
    }
}

// ── TEST 1: No Duplication & 1 Card Per Variant ─────────────────────────────────
echo "--- 1. Testing Display Card Transformation & Zero Duplication ---\n";
$allProducts = Product::active()->with(['variants' => fn ($q) => $q->where('status', 'active')->with('mediaAssets'), 'mediaAssets', 'collections'])->get();
$displayCards = ProductCardService::toDisplayCards($allProducts);

testAssert($displayCards->count() === 24, "Total display cards across catalog is exactly 24 (all variants transformed). Found: " . $displayCards->count());

// Check specific product (e.g. Silicone Case)
$siliconeProduct = $allProducts->firstWhere('slug', 'atelier-silicone-protection-case');
$siliconeCards = $displayCards->where('product_id', $siliconeProduct->id);

testAssert($siliconeCards->count() === 2, "Silicone case with 2 colors produces EXACTLY 2 cards (No duplicate 3rd card for base product). Found: " . $siliconeCards->count());
$cardTitles = $siliconeCards->pluck('variant_title')->all();
testAssert(in_array('Rose Pink', $cardTitles) && in_array('Midnight Blue', $cardTitles), "Silicone cards contain 'Rose Pink' and 'Midnight Blue'");

foreach ($displayCards as $c) {
    if ($c->variant_id !== null) {
        testAssert(str_contains($c->url, '?variant=' . $c->variant_id), "Card '{$c->display_title}' URL contains '?variant={$c->variant_id}'");
        testAssert(!empty($c->image), "Card '{$c->display_title}' has valid image URL: {$c->image}");
    }
}

// ── TEST 2: Tab Counters Consistency ──────────────────────────────────────────
echo "\n--- 2. Testing Category Tab Counters (1:1 with Grid) ---\n";
$counts = ProductCardService::getCategoryCardCounts();

testAssert(isset($counts['all']) && $counts['all'] === 24, "Main 'ALL PIECES' counter equals 24. Found: " . ($counts['all'] ?? 'NULL'));

$casesCol = Collection::where('slug', 'cases-protection')->first();
if ($casesCol) {
    testAssert($counts['cases-protection'] === 2, "Category 'Precision Phone Cases' counter equals 2 (matching 2 color variants). Found: " . ($counts['cases-protection'] ?? 'NULL'));
}

// ── TEST 3: Cart & Variant Flow (Add to Cart with variant_id) ─────────────────
echo "\n--- 3. Testing Variant Selection & Cart Flow Integrity ---\n";

// Find Midnight Blue variant
$midnightBlueVariant = ProductVariant::where('title', 'Midnight Blue')->first();
testAssert($midnightBlueVariant !== null, "Found 'Midnight Blue' variant in DB (ID: " . ($midnightBlueVariant?->id ?? 'NONE') . ")");

if ($midnightBlueVariant) {
    // 3a. Guest Session Cart Flow
    session(['cart' => []]);
    $cartCtrl = new CartController();
    
    $guestReq = Request::create('/cart/add', 'POST', [
        'product_id' => $midnightBlueVariant->product_id,
        'variant_id' => $midnightBlueVariant->id,
        'qty'        => 2,
    ]);
    
    $cartCtrl->add($guestReq);
    $guestCartItems = $cartCtrl->getCartItems();
    
    $firstItem = reset($guestCartItems);
    testAssert(!empty($firstItem), "Item successfully added to guest cart.");
    testAssert(($firstItem['variant_id'] ?? null) == $midnightBlueVariant->id, "Guest cart item variant_id matches selected variant (#{$midnightBlueVariant->id})");
    testAssert(($firstItem['variant'] ?? null) === 'Midnight Blue', "Guest cart item variant label is 'Midnight Blue'. Found: " . ($firstItem['variant'] ?? 'NONE'));
    testAssert(($firstItem['color_hex'] ?? null) === '#191970', "Guest cart item color_hex is '#191970'. Found: " . ($firstItem['color_hex'] ?? 'NONE'));
    testAssert(($firstItem['qty'] ?? 0) === 2, "Guest cart item quantity is 2");

    // 3b. Authenticated Database Cart Flow
    $testUser = User::firstOrCreate(
        ['email' => 'section2_tester@atelier.local'],
        ['name' => 'Section 2 Tester', 'password' => bcrypt('secret12345')]
    );
    
    Auth::login($testUser);
    CartItem::whereHas('cart', fn($q) => $q->where('user_id', $testUser->id))->delete();

    $authReq = Request::create('/cart/add', 'POST', [
        'product_id' => $midnightBlueVariant->product_id,
        'variant_id' => $midnightBlueVariant->id,
        'qty'        => 3,
    ]);
    
    $cartCtrl->add($authReq);
    
    $userCart = Cart::where('user_id', $testUser->id)->first();
    $dbCartItem = $userCart ? $userCart->items()->first() : null;
    
    testAssert($dbCartItem !== null, "Cart item saved in database for user");
    testAssert($dbCartItem?->product_variant_id == $midnightBlueVariant->id, "Database cart item has exact product_variant_id (#{$midnightBlueVariant->id})");
    testAssert($dbCartItem?->quantity === 3, "Database cart item quantity is 3");

    $authCartItems = $cartCtrl->getCartItems();
    $authFirstItem = reset($authCartItems);
    testAssert(($authFirstItem['variant'] ?? null) === 'Midnight Blue', "Authenticated cart snapshot displays 'Midnight Blue' option name");
    testAssert(($authFirstItem['price'] ?? null) === (int)($midnightBlueVariant->effective_price_minor / 100), "Authenticated cart item price matches variant price: " . ($authFirstItem['price'] ?? 0));

    // Cleanup
    $userCart->items()->delete();
    Auth::logout();
    session(['cart' => []]);
}

echo "\n====================================================\n";
echo "TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "====================================================\n";

if ($failCount === 0) {
    echo "\n>>> ALL SECTION 2 TESTS PASSED PERFECTLY WITH ZERO ERRORS! <<<\n";
    exit(0);
} else {
    echo "\n>>> ERRORS FOUND IN SECTION 2! <<<\n";
    exit(1);
}
