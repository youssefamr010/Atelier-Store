<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingZone;
use App\Services\Payment\PaymobPaymentProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebCheckoutController extends Controller
{
    /**
     * Show checkout page with user saved addresses, dynamic cart, and live shipping zones.
     */
    public function show(Request $request): View
    {
        $settings = Setting::allAsMap();
        $user = Auth::user();
        $savedAddresses = $user ? $user->addresses()->latest()->get() : collect();
        $defaultAddress = $user ? $user->defaultAddress() : null;
        $shippingZones = ShippingZone::where('is_active', true)->orderBy('sort_order')->get();

        // 1. Check direct product checkout vs account/session cart
        $cartItems = [];
        $subtotal = 0;

        if ($request->filled('product_id')) {
            // Load with 'product' on variants so effective_price_minor inherits correctly
            $directProduct = Product::with(['mediaAssets', 'variants.product'])->find($request->input('product_id'));
            if ($directProduct) {
                $vId = $request->input('variant_id');
                $qty = max(1, min(20, (int) $request->input('qty', 1)));
                // Default price from product itself
                $priceMinor = (int) ($directProduct->retail_price_minor ?? 0);
                $vLabel = null;
                $variantImage = null;
                $colorHex = null;
                if ($vId) {
                    $variant = $directProduct->variants->firstWhere('id', $vId);
                    if ($variant) {
                        $vLabel = $variant->title ?: $variant->attribute_value;
                        // effective_price_minor now correctly inherits from product
                        $effectivePrice = $variant->effective_price_minor;
                        $priceMinor = $effectivePrice > 0 ? $effectivePrice : (int) ($directProduct->retail_price_minor ?? 0);
                        $variantImage = $variant->image_url ?: ($variant->attributes_json['image_url'] ?? null);
                        $colorHex = $variant->attributes_json['color_hex'] ?? null;
                    }
                }
                $cover = $directProduct->mediaAssets->first();
                $coverImage = $variantImage ?: ($cover?->url ?: $directProduct->image_url);

                $unitPrice = (int) round($priceMinor / 100);
                $cartItems['direct'] = [
                    'product_id' => $directProduct->id,
                    'variant_id' => $vId,
                    'name'       => $directProduct->title,
                    'variant'    => $vLabel,
                    'color_hex'  => $colorHex,
                    'price'      => $unitPrice,
                    'image'      => $coverImage,
                    'qty'        => $qty,
                ];
                $subtotal = $unitPrice * $qty;
            }
        } else {
            $activeCart = app(CartController::class)->getCartItems();
            if (!empty($activeCart)) {
                $cartItems = $activeCart;
                foreach ($cartItems as $item) {
                    $subtotal += ($item['price'] ?? 0) * ($item['qty'] ?? 1);
                }
            }
            // NO silent fallback product — empty cart stays empty, user sees empty checkout
        }

        $defaultRate = (float) Setting::get('default_shipping_rate', 75);
        $shippingCost = $subtotal >= (float) Setting::get('free_shipping_threshold', 999999) ? 0 : $defaultRate;
        $total = $subtotal + $shippingCost;

        // Loyalty Points Calculation
        $userPoints = $user ? $user->loyaltyPointsBalance() : 0;
        $loyaltyEnabled = Setting::get('loyalty_enabled', '1') === '1';
        $ptsUnit = (int) Setting::get('loyalty_redeem_pts_unit', 100);
        $ptsDiscountEgp = (float) Setting::get('loyalty_redeem_discount_egp', 50);
        $maxPointsDiscountEgp = ($loyaltyEnabled && $ptsUnit > 0 && $userPoints >= $ptsUnit)
            ? min(floor($userPoints / $ptsUnit) * $ptsDiscountEgp, $subtotal)
            : 0;

        return view('checkout', compact(
            'settings',
            'savedAddresses',
            'defaultAddress',
            'shippingZones',
            'cartItems',
            'subtotal',
            'shippingCost',
            'total',
            'userPoints',
            'loyaltyEnabled',
            'ptsUnit',
            'ptsDiscountEgp',
            'maxPointsDiscountEgp'
        ));
    }

    /**
     * Place order from Web Checkout with dynamic shipping & tax calculation.
     */
    public function place(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Sanitize incoming IDs to prevent 'must be an integer' validation errors
        if ($request->has('variant_id')) {
            $rawVar = $request->input('variant_id');
            if (empty($rawVar) || !is_numeric($rawVar) || (int)$rawVar <= 0 || $rawVar === 'null' || $rawVar === 'undefined') {
                $request->merge(['variant_id' => null]);
            } else {
                $request->merge(['variant_id' => (int)$rawVar]);
            }
        }
        if ($request->has('product_id')) {
            $rawProd = $request->input('product_id');
            if (empty($rawProd) || !is_numeric($rawProd) || (int)$rawProd <= 0) {
                $request->merge(['product_id' => null]);
            } else {
                $request->merge(['product_id' => (int)$rawProd]);
            }
        }

        $validated = $request->validate([
            'saved_address_id'        => 'nullable|integer|exists:addresses,id',
            'full_name'               => 'nullable|required_without:saved_address_id|string|max:150',
            'phone'                   => 'nullable|required_without:saved_address_id|string|max:30',
            'city'                    => 'nullable|required_without:saved_address_id|string|max:100',
            'street_address'          => 'nullable|required_without:saved_address_id|string|max:255',
            'state'                   => 'nullable|string|max:100',
            'postal_code'             => 'nullable|string|max:30',
            'latitude'                => 'nullable|numeric|between:-90,90',
            'longitude'               => 'nullable|numeric|between:-180,180',
            'address_label'           => 'nullable|string|max:50',
            'save_to_address_book'    => 'nullable|boolean',
            'gift_wrap'               => 'nullable|boolean',
            'gift_message'            => 'nullable|string|max:500',
            'preferred_delivery_time' => 'nullable|string|max:50',
            'delivery_instructions'   => 'nullable|string|max:500',
            'payment_method'          => 'required|in:cod,paymob,stripe',
            'product_id'              => 'nullable|integer|exists:products,id',
            'variant_id'              => 'nullable|integer',
            'qty'                     => 'nullable|integer|min:1|max:20',
        ]);

        if ($validated['payment_method'] === 'paymob' && (blank(config('payment.paymob.secret_key')) || blank(config('payment.paymob.public_key')) || empty(array_filter(config('payment.paymob.integration_ids', []))))) {
            return back()->withInput()->withErrors(['payment_method' => 'Online card payment is not configured yet. Please choose Cash on Delivery or ask the store owner to finish Paymob setup.']);
        }

        // Resolve delivery details
        if (!empty($validated['saved_address_id']) && $user) {
            $address = Address::where('id', $validated['saved_address_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $fullName = $address->full_name;
            $phone = $address->phone;
            $city = $address->city;
            $streetAddress = $address->street_address;
            $state = $address->state;
            $postalCode = $address->postal_code;
            $latitude = $address->latitude;
            $longitude = $address->longitude;
        } else {
            $fullName = !empty($validated['full_name']) ? trim((string)$validated['full_name']) : ($user?->name ?: 'Valued Client');
            $phone = !empty($validated['phone']) ? trim((string)$validated['phone']) : '';
            $city = !empty($validated['city']) ? trim((string)$validated['city']) : 'Cairo';
            $streetAddress = !empty($validated['street_address']) ? trim((string)$validated['street_address']) : 'Direct Delivery';
            $state = $validated['state'] ?? null;
            $postalCode = $validated['postal_code'] ?? null;
            $latitude = $request->filled('latitude') ? (float)$request->input('latitude') : null;
            $longitude = $request->filled('longitude') ? (float)$request->input('longitude') : null;

            // Save address if requested
            if ($user && !empty($request->input('save_to_address_book'))) {
                $user->addresses()->create([
                    'label'          => $request->input('address_label') ?: 'Home',
                    'full_name'      => $fullName,
                    'phone'          => $phone,
                    'street_address' => $streetAddress,
                    'city'           => $city,
                    'state'          => $state,
                    'postal_code'    => $postalCode,
                    'country_code'   => 'EG',
                    'latitude'       => $latitude,
                    'longitude'      => $longitude,
                    'is_default'     => $user->addresses()->count() === 0,
                ]);
            }
        }

        $email = $user ? $user->email : (preg_replace('/[^0-9]/', '', $phone) ?: Str::random(8)) . '@client.atelier.com';

        // Find or create customer record with safe non-null fallback
        $trimmedName = trim((string)$fullName);
        $nameParts = explode(' ', $trimmedName, 2);
        $firstName = $nameParts[0] !== '' ? $nameParts[0] : 'Client';
        $lastName  = isset($nameParts[1]) && trim($nameParts[1]) !== '' ? trim($nameParts[1]) : '.';

        $customer = Customer::firstOrCreate(
            ['email' => mb_strtolower(trim($email))],
            [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $phone,
            ]
        );

        $order = DB::transaction(function () use ($validated, $user, $customer, $email, $fullName, $phone, $city, $streetAddress, $state, $postalCode, $latitude, $longitude, $request, $firstName, $lastName) {
            $activeCart = app(CartController::class)->getCartItems();
            $itemsToProcess = [];
            $subtotalMinor = 0;

            if (!empty($validated['product_id'])) {
                // Direct product checkout
                $product = Product::with(['variants.product', 'mediaAssets'])->find($validated['product_id']);
                if ($product) {
                    $variant = !empty($validated['variant_id']) ? $product->variants->firstWhere('id', $validated['variant_id']) : null;
                    abort_if(!empty($validated['variant_id']) && ! $variant, 422, 'Selected product option is unavailable.');
                    $attrs = $variant?->attributes_json ?? [];
                    $cover = $product->mediaAssets->first();
                    $unitMinor = $variant ? $variant->effective_price_minor : (int)($product->retail_price_minor ?? 0);

                    // PRICE GUARD: block checkout if product has no price set
                    abort_if($unitMinor <= 0, 422, "لا يمكن إتمام الطلب: سعر المنتج ({$product->title}) غير محدد. يرجى التواصل مع المتجر.");

                    $directQty = max(1, min(20, (int) ($validated['qty'] ?? 1)));
                    $lineTotalMinor = $unitMinor * $directQty;

                    $itemsToProcess[] = [
                        'product'       => $product,
                        'variant_id'    => $variant?->id,
                        'variant_title' => $variant?->title ?: $variant?->attribute_value,
                        'color_hex'     => $attrs['color_hex'] ?? null,
                        'image'         => $variant?->image_url ?: ($attrs['image_url'] ?? ($cover?->url ?: $product->image_url)),
                        'title'         => $product->title,
                        'sku'           => $variant?->sku ?: ($product->sku ?: ('SKU-' . $product->id)),
                        'unit_minor'    => $unitMinor,
                        'qty'           => $directQty,
                        'total_minor'   => $lineTotalMinor,
                    ];
                    $subtotalMinor += $lineTotalMinor;
                }
            } elseif (!empty($activeCart)) {
                // Multi-item shopping bag checkout — Strictly database-verified pricing
                foreach ($activeCart as $cartItem) {
                    $p = Product::with(['variants.product'])->find($cartItem['product_id']);
                    if ($p && $p->status === 'active') {
                        $qty = max(1, min(20, (int) ($cartItem['qty'] ?? 1)));
                        $unitMinor = (int) ($p->retail_price_minor ?? 0);
                        $var = null;
                        if (!empty($cartItem['variant_id'])) {
                            $var = $p->variants->firstWhere('id', $cartItem['variant_id']);
                            if ($var) $unitMinor = $var->effective_price_minor;
                        }

                        // PRICE GUARD: skip items with no price — don't allow free products
                        if ($unitMinor <= 0) {
                            continue; // skip this item silently (will be caught by subtotal guard below)
                        }

                        $lineTotalMinor = $unitMinor * $qty;
                        $itemsToProcess[] = [
                            'product'       => $p,
                            'variant_id'    => $cartItem['variant_id'] ?? null,
                            'variant_title' => $var?->title ?: $var?->attribute_value,
                            'color_hex'     => $var?->attributes_json['color_hex'] ?? ($cartItem['color_hex'] ?? null),
                            'image'         => $var?->image_url ?: ($var?->attributes_json['image_url'] ?? ($cartItem['image'] ?? $p->image_url)),
                            'title'         => $p->title,
                            'sku'           => $var?->sku ?: ($p->sku ?: ('SKU-' . $p->id)),
                            'unit_minor'    => $unitMinor,
                            'qty'           => $qty,
                            'total_minor'   => $lineTotalMinor,
                        ];
                        $subtotalMinor += $lineTotalMinor;
                    }
                }
            }

            if (empty($itemsToProcess)) {
                // SECURITY: never allow an order with no items
                abort(422, 'سلة التسوق فارغة. يرجى اختيار منتج قبل إتمام الطلب.');
            }

            // PRICE GUARD: block order if all items somehow have 0 subtotal
            if ($subtotalMinor <= 0) {
                abort(422, 'لا يمكن إتمام الطلب: بعض المنتجات ليس لها سعر محدد. يرجى تحديث سعر المنتج أولاً من لوحة التحكم.');
            }

            // 2. Resolve dynamic shipping zone and fee
            $zone = ShippingZone::findForGovernorate($city) ?? ShippingZone::findForGovernorate($state);
            $defaultShippingMinor = (int) round(((float) Setting::get('default_shipping_rate', 75)) * 100);
            $shippingMinor = $zone ? (int)$zone->rate_minor : $defaultShippingMinor;

            // 3. Free Shipping Threshold Check
            $freeThreshold = Setting::get('free_shipping_threshold');
            if (!empty($freeThreshold) && (float)$freeThreshold > 0) {
                $thresholdMinor = (int) round(((float)$freeThreshold) * 100);
                if ($subtotalMinor >= $thresholdMinor) {
                    $shippingMinor = 0; // Waived
                }
            }

            // 4. Tax calculation (if enabled)
            $taxMinor = 0;
            if (Setting::get('tax_enabled') === '1') {
                $taxRate = (float) Setting::get('tax_percentage', 0);
                if ($taxRate > 0) {
                    $taxMinor = (int) round($subtotalMinor * ($taxRate / 100));
                }
            }

            // 5. COD Surcharge
            $codSurchargeMinor = ($validated['payment_method'] === 'cod')
                ? (int) config('commerce.cod.surcharge_minor', 2000)
                : 0;

            // 6. Points Redemption & Coupon Discount
            $pointsDiscountMinor = 0;
            $pointsRedeemed = 0;
            $redeemRequested = $request->boolean('redeem_points') || $request->boolean('redeem_loyalty_points');
            if ($user && $redeemRequested && Setting::get('loyalty_enabled', '1') === '1') {
                $userBalance = $user->loyaltyPointsBalance();
                $ptsUnit = (int) Setting::get('loyalty_redeem_pts_unit', 100);
                $ptsDiscountEgp = (float) Setting::get('loyalty_redeem_discount_egp', 50);
                if ($ptsUnit > 0 && $userBalance >= $ptsUnit) {
                    $maxUnits = floor($userBalance / $ptsUnit);
                    $pointsRedeemed = (int) ($maxUnits * $ptsUnit);
                    $pointsDiscountMinor = (int) round(($maxUnits * $ptsDiscountEgp) * 100);
                    $pointsDiscountMinor = min($pointsDiscountMinor, $subtotalMinor);
                }
            }

            $couponDiscountMinor = 0;
            $appliedCouponCode = null;
            if ($request->filled('coupon_code')) {
                $couponInput = strtoupper(trim((string)$request->input('coupon_code')));
                if ($couponInput === strtoupper((string)Setting::get('newsletter_discount_code', 'WELCOME10'))) {
                    $couponDiscountMinor = (int) round($subtotalMinor * 0.10);
                    $appliedCouponCode = $couponInput;
                } else {
                    $coupon = \App\Models\Coupon::where('code', $couponInput)->where('is_active', true)->first();
                    if ($coupon) {
                        $couponDiscountMinor = $coupon->calculateDiscountMinor($subtotalMinor);
                        $appliedCouponCode = $coupon->code;
                    }
                }
            }

            $discountMinor = min($subtotalMinor, $pointsDiscountMinor + $couponDiscountMinor);
            $totalMinor = max(0, $subtotalMinor - $discountMinor) + $shippingMinor + $taxMinor + $codSurchargeMinor;

            $order = Order::create([
                'order_number'        => 'AT-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'customer_id'         => $customer->id,
                'customer_email'      => $email,
                'customer_name'       => $fullName,
                'customer_phone'      => $phone,
                'currency'            => 'EGP',
                'subtotal_minor'      => $subtotalMinor,
                'shipping_minor'      => $shippingMinor,
                'tax_minor'           => $taxMinor,
                'discount_minor'      => $discountMinor,
                'cod_surcharge_minor' => $codSurchargeMinor,
                'total_amount_minor'  => $totalMinor,
                'payment_status'      => $validated['payment_method'] === 'cod' ? 'cod_pending' : 'pending',
                'payment_method'      => $validated['payment_method'],
                'fulfillment_status'  => 'unfulfilled',
                'shipping_status'     => 'pending',
                'notes'               => "Delivery to: {$streetAddress}, {$city}. Phone: {$phone}",
                'metadata_json'       => [
                    'shipping_zone'           => $zone ? $zone->name : 'Standard Default',
                    'delivery_estimate'       => 'Usually delivers from 3 to 5 business days (Maximum 4 days from order date)',
                    'delivery_instructions'   => $validated['delivery_instructions'] ?? null,
                    'preferred_delivery_time' => $validated['preferred_delivery_time'] ?? null,
                    'gift_wrap'               => !empty($validated['gift_wrap']),
                    'gift_message'            => $validated['gift_message'] ?? null,
                    'points_redeemed'         => $pointsRedeemed,
                    'coupon_code'             => $appliedCouponCode,
                    'customer_name'           => $fullName,
                    'customer_phone'          => $phone,
                    'shipping_address'        => [
                        'name'      => $fullName,
                        'phone'     => $phone,
                        'street'    => $streetAddress,
                        'city'      => $city,
                        'state'     => $state,
                        'postal'    => $postalCode,
                        'country'   => 'EG',
                        'latitude'  => $latitude,
                        'longitude' => $longitude,
                    ],
                ],
            ]);

            if ($pointsRedeemed > 0 && $user) {
                try {
                    \App\Models\LoyaltyPointLedger::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'points' => -$pointsRedeemed,
                        'type' => 'redeem',
                        'notes' => "Redeemed {$pointsRedeemed} pts for discount on Order #{$order->order_number}",
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Loyalty point ledger entry skipped: ' . $e->getMessage());
                }
            }

            foreach ($itemsToProcess as $it) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $it['product']->id,
                    'product_variant_id' => $it['variant_id'],
                    'sku'                => $it['sku'],
                    'product_title'      => $it['title'],
                    'quantity'           => $it['qty'],
                    'unit_price_minor'   => $it['unit_minor'],
                    'total_price_minor'  => $it['total_minor'],
                    'metadata_json'      => array_filter([
                        'supplier_product_url' => $it['product']->attributes_json['supplier_product_url'] ?? null,
                        'variant_title'        => $it['variant_title'] ?? null,
                        'color_hex'            => $it['color_hex'] ?? null,
                        'image_url'            => $it['image'] ?? null,
                    ]),
                ]);

                if (!empty($it['variant_id'])) {
                    $variantModel = \App\Models\ProductVariant::find($it['variant_id']);
                    if ($variantModel && $variantModel->inventory > 0) {
                        $variantModel->decrement('inventory', min($variantModel->inventory, $it['qty']));
                    }
                }
                if ($it['product']->inventory > 0) {
                    $it['product']->decrement('inventory', min($it['product']->inventory, $it['qty']));
                }
            }

            // Clear shopping bag (session for guest, database for auth user)
            if ($user) {
                CartController::clearUserCart($user);
            } else {
                session()->forget('cart');
            }

            // Sync CustomerAddress safely
            CustomerAddress::updateOrCreate(
                ['customer_id' => $customer->id, 'type' => 'shipping'],
                [
                    'first_name'     => $firstName,
                    'last_name'      => $lastName,
                    'address_line_1' => $streetAddress ?: ($city ?: 'Standard Delivery'),
                    'city'           => $city ?: 'Cairo',
                    'state'          => $state,
                    'postal_code'    => $postalCode,
                    'country_code'   => 'EG',
                    'phone'          => $phone,
                ]
            );

            Payment::create([
                'order_id'        => $order->id,
                'provider'        => $validated['payment_method'],
                'amount_minor'    => $totalMinor,
                'currency'        => 'EGP',
                'status'          => $validated['payment_method'] === 'cod' ? 'pending' : 'pending',
                'idempotency_key' => Str::uuid()->toString(),
                'metadata_json'   => [
                    'payment_method' => $validated['payment_method'],
                ],
            ]);

            return $order;
        });

        // Dispatch Telegram Instant Notification (Safe & Non-blocking)
        try {
            app(\App\Services\TelegramNotificationService::class)->sendOrderNotification($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Telegram notification error: ' . $e->getMessage());
        }

        // Dispatch WhatsApp Client Instant Confirmation (Safe & Non-blocking)
        try {
            app(\App\Services\WhatsAppNotificationService::class)->sendOrderConfirmation($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp notification error: ' . $e->getMessage());
        }

        if ($validated['payment_method'] === 'paymob') {
            try {
                $intent = app(PaymobPaymentProvider::class)->createIntent((int) $order->total_amount_minor, 'EGP', [
                    'order_id' => $order->id, 'order_number' => $order->order_number,
                    'first_name' => $customer->first_name, 'last_name' => $customer->last_name,
                    'phone' => $phone, 'email' => $email, 'street' => $streetAddress, 'city' => $city, 'country' => 'EG',
                ]);
                $order->payment()->update(['provider_transaction_id' => $intent['provider_intent_id'], 'metadata_json' => ['checkout_url' => $intent['checkout_url']]]);
                return redirect()->away($intent['checkout_url']);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Could not start Paymob checkout', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                return redirect()->route('account')->with('error', "Order #{$order->order_number} is saved, but online payment could not start. Please try again or contact support.");
            }
        }

        return redirect()->route('account')->with('success', "Order #{$order->order_number} confirmed! Our concierge will contact you via WhatsApp ({$phone}) for dispatch.");
    }
}
