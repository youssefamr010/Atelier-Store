<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Account-based & Session Shopping Cart Controller.
 * Authenticated users: synced across all devices via the database (carts & cart_items).
 * Guests: managed via session until login/registration, then seamlessly merged.
 * Cart key: 'productId_variantId'
 */
class CartController extends Controller
{
    // ── GET /cart ─────────────────────────────────────────────────────────────
    public function index(): View
    {
        $settings = Setting::allAsMap();
        $cartItems = $this->getCartItems();
        $subtotal  = 0;

        foreach ($cartItems as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['qty'] ?? 1);
        }

        return view('cart', compact('cartItems', 'subtotal', 'settings'));
    }

    // ── POST /cart/add ────────────────────────────────────────────────────────
    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer',
            'qty'        => 'nullable|integer|min:1|max:20',
        ]);

        $productId = (int) $request->input('product_id');
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $qty       = max(1, min(20, (int) $request->input('qty', 1)));

        $product = Product::with(['mediaAssets', 'variants'])->findOrFail($productId);
        $variant = $variantId ? $product->variants->firstWhere('id', $variantId) : null;
        abort_if($variantId && ! $variant, 422, 'The selected product option is unavailable.');

        if (Auth::check()) {
            $cart = Cart::forUser(Auth::id());
            $cartItem = $cart->items()
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($cartItem) {
                $cartItem->quantity = min(20, $cartItem->quantity + $qty);
                $cartItem->save();
            } else {
                $cart->items()->create([
                    'product_id'         => $productId,
                    'product_variant_id' => $variantId,
                    'quantity'           => $qty,
                ]);
            }
        } else {
            $cartKey = $productId . '_' . ($variantId ?? '0');
            $cart = session('cart', []);

            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['qty'] = min(20, ($cart[$cartKey]['qty'] ?? 1) + $qty);
            } else {
                $cart[$cartKey] = $this->makeCartItem($product, $variant, $qty);
            }

            session(['cart' => $cart]);
        }

        return back()->with('success', "«{$product->title}» added to your cart.");
    }

    // ── POST /cart/update ─────────────────────────────────────────────────────
    public function update(Request $request): RedirectResponse
    {
        $key    = (string) $request->input('key', '');
        $action = $request->input('action'); // 'increase' | 'decrease' | 'set'

        if (empty($key)) {
            return back();
        }

        [$productId, $variantId] = $this->parseCartKey($key);

        if (Auth::check()) {
            $cart = Cart::forUser(Auth::id());
            $cartItem = $cart->items()
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($cartItem) {
                if ($action === 'increase') {
                    $cartItem->quantity = min(20, $cartItem->quantity + 1);
                    $cartItem->save();
                } elseif ($action === 'decrease') {
                    $newQty = $cartItem->quantity - 1;
                    if ($newQty <= 0) {
                        $cartItem->delete();
                    } else {
                        $cartItem->quantity = $newQty;
                        $cartItem->save();
                    }
                } elseif ($action === 'set') {
                    $qty = max(1, min(20, (int) $request->input('qty', 1)));
                    $cartItem->quantity = $qty;
                    $cartItem->save();
                }
            }
        } else {
            $cart = session('cart', []);
            if (!isset($cart[$key])) {
                return back();
            }

            if ($action === 'increase') {
                $cart[$key]['qty'] = min(20, ($cart[$key]['qty'] ?? 1) + 1);
            } elseif ($action === 'decrease') {
                $newQty = ($cart[$key]['qty'] ?? 1) - 1;
                if ($newQty <= 0) {
                    unset($cart[$key]);
                } else {
                    $cart[$key]['qty'] = $newQty;
                }
            } elseif ($action === 'set') {
                $qty = max(1, min(20, (int) $request->input('qty', 1)));
                $cart[$key]['qty'] = $qty;
            }

            session(['cart' => $cart]);
        }

        return back();
    }

    // ── POST /cart/remove ─────────────────────────────────────────────────────
    public function remove(Request $request): RedirectResponse
    {
        $key = (string) $request->input('key', '');
        if (empty($key)) {
            return back();
        }

        [$productId, $variantId] = $this->parseCartKey($key);

        if (Auth::check()) {
            $cart = Cart::forUser(Auth::id());
            $cart->items()
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->delete();
        } else {
            $cart = session('cart', []);
            unset($cart[$key]);
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Item removed from your cart.');
    }

    // ── POST /cart/clear ──────────────────────────────────────────────────────
    public function clear(): RedirectResponse
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cart->items()->delete();
            }
        }

        session()->forget('cart');
        return back()->with('success', 'Your cart has been cleared.');
    }

    // ── Get all active cart items (DB for Auth, Session for Guest) ─────────────
    public function getCartItems(): array
    {
        $cartItems = [];

        if (Auth::check()) {
            $cart = Cart::forUser(Auth::id());
            $items = $cart->items()->with(['product.mediaAssets', 'variant'])->get();

            foreach ($items as $item) {
                $product = $item->product;
                if (!$product || $product->status !== 'active') {
                    $item->delete();
                    continue;
                }
                $variant = $item->variant;
                $key = $item->product_id . '_' . ($item->product_variant_id ?? '0');
                $cartItems[$key] = $this->makeCartItem($product, $variant, (int) $item->quantity);
            }
        } else {
            $cart = session('cart', []);
            foreach ($cart as $key => $item) {
                $product = Product::with(['mediaAssets', 'variants'])->find($item['product_id'] ?? 0);
                if (!$product || $product->status !== 'active') {
                    unset($cart[$key]);
                    continue;
                }
                $variant = !empty($item['variant_id']) ? $product->variants->firstWhere('id', $item['variant_id']) : null;
                $cartItems[$key] = $this->makeCartItem($product, $variant, (int) ($item['qty'] ?? 1));
            }
            session(['cart' => $cartItems]);
        }

        return $cartItems;
    }

    // ── Helper: get cart count (for header badge) ─────────────────────────────
    public static function cartCount(): int
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            return $cart ? (int) $cart->items()->sum('quantity') : 0;
        }

        $cart = session('cart', []);
        return array_sum(array_column($cart, 'qty'));
    }

    // ── Helper: get cart subtotal ─────────────────────────────────────────────
    public static function cartSubtotal(): int
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if (!$cart) {
                return 0;
            }
            $items = $cart->items()->with(['product', 'variant'])->get();
            $total = 0;
            foreach ($items as $item) {
                $priceMinor = $item->variant?->effective_price_minor ?: (int) ($item->product?->retail_price_minor ?? 0);
                $total += (int) round($priceMinor / 100) * (int) $item->quantity;
            }
            return $total;
        }

        $cart = session('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['price'] ?? 0) * ($item['qty'] ?? 1);
        }
        return $total;
    }

    /**
     * Merge guest session cart into authenticated user database cart upon login/registration.
     * Combines quantities for duplicate items up to max 20, avoiding duplicate rows.
     */
    public static function mergeSessionCartIntoUserCart(User $user): void
    {
        $sessionCart = session('cart', []);
        if (empty($sessionCart)) {
            return;
        }

        $dbCart = Cart::forUser($user->id);

        foreach ($sessionCart as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
            $qty       = max(1, min(20, (int) ($item['qty'] ?? 1)));

            if ($productId <= 0) {
                continue;
            }

            if (!Product::where('id', $productId)->where('status', 'active')->exists()) {
                continue;
            }

            $existingItem = $dbCart->items()
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($existingItem) {
                $existingItem->quantity = min(20, $existingItem->quantity + $qty);
                $existingItem->save();
            } else {
                $dbCart->items()->create([
                    'product_id'         => $productId,
                    'product_variant_id' => $variantId,
                    'quantity'           => $qty,
                ]);
            }
        }

        // Clear guest session cart once successfully merged
        session()->forget('cart');
    }

    /** Clear user's database cart when order is placed */
    public static function clearUserCart(User $user): void
    {
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            $cart->items()->delete();
        }
        session()->forget('cart');
    }

    /** Build a single reliable display snapshot from the current product option. */
    public function makeCartItem(Product $product, ?ProductVariant $variant, int $qty): array
    {
        $attrs = $variant?->attributes_json ?? [];
        $variantImage = $variant?->image_url ?: ($attrs['image_url'] ?? null);
        $cover = $product->mediaAssets->first();
        $image = $this->publicImage($variantImage)
            ?: $this->publicImage($cover?->url)
            ?: $this->publicImage($product->image_url);
        $priceMinor = $variant?->effective_price_minor ?: (int) $product->retail_price_minor;

        return [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name'       => $product->title,
            'variant'    => $variant?->title ?: $variant?->attribute_value,
            'color_hex'  => $attrs['color_hex'] ?? $attrs['hex'] ?? null,
            'price'      => (int) round($priceMinor / 100),
            'image'      => $image,
            'qty'        => max(1, min(20, $qty)),
        ];
    }

    private function parseCartKey(string $key): array
    {
        $parts = explode('_', $key);
        $productId = (int) ($parts[0] ?? 0);
        $variantId = (isset($parts[1]) && $parts[1] !== '' && $parts[1] !== '0') ? (int) $parts[1] : null;
        return [$productId, $variantId];
    }

    private function publicImage(?string $path): ?string
    {
        if (! $path) return null;
        return str_starts_with($path, 'http') ? $path : url($path);
    }
}
