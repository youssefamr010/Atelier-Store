<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

@mkdir(storage_path('framework/views'), 0777, true);
@mkdir(storage_path('framework/sessions'), 0777, true);
config(['view.compiled' => storage_path('framework/views')]);

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

echo "=============================================\n";
echo "       SECTION 3 CHECKOUT VERIFICATION       \n";
echo "=============================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($condition, $name) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] $name\n";
        $passCount++;
    } else {
        echo "  [FAIL] $name\n";
        $failCount++;
    }
}

// 1. Setup User & Cart
$user = User::firstOrCreate(
    ['email' => 'checkout_test@atelier.test'],
    ['name' => 'Checkout Tester', 'password' => bcrypt('secret123')]
);

$product = Product::with('variants')->first();
$variant = $product->variants->first();

// Clean existing cart
Cart::where('user_id', $user->id)->delete();
$cart = Cart::create(['user_id' => $user->id, 'session_id' => 'test_session_' . uniqid()]);
CartItem::create([
    'cart_id' => $cart->id,
    'product_id' => $product->id,
    'product_variant_id' => $variant ? $variant->id : null,
    'quantity' => 2,
]);

assertTest($cart->items()->count() === 1, "Cart initialized with 1 item for test user");

// 2. Test CheckoutController show() logic
Auth::login($user);
$controller = app(\App\Http\Controllers\WebCheckoutController::class);
$response = $controller->show(Request::create('/checkout', 'GET'));

assertTest($response instanceof \Illuminate\View\View, "Checkout show returns View instance");
$viewData = $response->getData();
assertTest(isset($viewData['cartItems']) && count($viewData['cartItems']) === 1, "Checkout view receives cartItems correctly");
$itemsList = array_values($viewData['cartItems']);
assertTest(isset($itemsList[0]) && $itemsList[0]['qty'] === 2, "Cart item quantity is 2");
assertTest(isset($viewData['subtotal']) && $viewData['subtotal'] > 0, "Subtotal calculated correctly: " . ($viewData['subtotal'] ?? 0));

// 3. Test View Compilation
try {
    view()->share('errors', new \Illuminate\Support\ViewErrorBag());
    $renderedHtml = $response->render();
    assertTest(strpos($renderedHtml, 'CARTO') !== false || strpos($renderedHtml, 'basemaps.cartocdn.com') !== false, "CARTO Positron map tiles present in compiled view");
    assertTest(strpos($renderedHtml, 'atl-district-pill') !== false, "Unified district pill buttons present in view");
    assertTest(strpos($renderedHtml, 'Step 01') !== false && strpos($renderedHtml, 'Step 02') !== false, "Clear 01/02 step hierarchy present in view");
    assertTest(strpos($renderedHtml, 'ملخص الطلب') !== false, "Order summary section rendered");
} catch (\Exception $e) {
    assertTest(false, "View rendering failed: " . $e->getMessage());
}

// 4. Test Checkout Place Order (COD with new address & GPS coords)
$placeRequest = Request::create('/checkout', 'POST', [
    'full_name' => 'Checkout Tester',
    'phone' => '01012345678',
    'city' => 'القاهرة',
    'street_address' => '12 شارع النيل، الزمالك',
    'latitude' => 30.0626,
    'longitude' => 31.2224,
    'payment_method' => 'cod',
    'save_to_address_book' => '1',
    'address_label' => 'المنزل - زمالك',
]);

$placeResponse = $controller->place($placeRequest);
assertTest($placeResponse->isRedirect(), "Place order returns redirect response");
echo "  [INFO] Redirect URL: " . $placeResponse->getTargetUrl() . "\n";
if ($placeResponse->getSession() && $placeResponse->getSession()->has('errors')) {
    echo "  [INFO] Session errors: " . json_encode($placeResponse->getSession()->get('errors')->all()) . "\n";
}

$lastOrder = Order::where('customer_email', 'checkout_test@atelier.test')->latest('id')->first();
assertTest($lastOrder !== null, "Order successfully created in database for test user");
if ($lastOrder) {
    assertTest($lastOrder->payment_method === 'cod', "Order payment method is 'cod'");
    $meta = $lastOrder->metadata_json;
    $shippingAddr = $meta['shipping_address'] ?? [];
    assertTest(($shippingAddr['latitude'] ?? null) == 30.0626 && ($shippingAddr['longitude'] ?? null) == 31.2224, "Order GPS coordinates recorded correctly (30.0626, 31.2224)");
}

// 5. Test Saved Address was created
$savedAddress = Address::where('user_id', $user->id)->where('label', 'المنزل - زمالك')->first();
assertTest($savedAddress !== null, "Saved address added to user address book");
if ($savedAddress) {
    assertTest($savedAddress->latitude == 30.0626, "Saved address holds latitude");
}

// 6. Test Cart was cleared after order placement
$cartAfter = Cart::where('user_id', $user->id)->first();
$itemsCountAfter = $cartAfter ? $cartAfter->items()->count() : 0;
assertTest($itemsCountAfter === 0, "Cart items cleared after order placement");

echo "\n---------------------------------------------\n";
echo "SUMMARY: $passCount PASSED, $failCount FAILED\n";
echo "---------------------------------------------\n";
