@extends('layouts.app')

@section('title', ((($settings['storefront_lang'] ?? 'en') === 'ar') ? 'إتمام الطلب بأمان' : 'Secure Checkout') . ' — ' . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', (($settings['storefront_lang'] ?? 'en') === 'ar') ? 'أتمم طلبك بأمان وفخامة مع ATELIER.' : 'Complete your ATELIER order securely.')

@section('content')

@php
    $ar = (($settings['storefront_lang'] ?? 'en') === 'ar');
    $cartItemsList = $cartItems ?? [];
    $rawSubtotal = (float)($subtotal ?? 0);
    $rawShipping = (float)($shippingCost ?? 0);
    $rawTotal = (float)($total ?? ($rawSubtotal + $rawShipping));
    $ptsDiscount = (float)($maxPointsDiscountEgp ?? 0);
@endphp

<div class="bg-[#F8F7F3] min-h-screen py-8 sm:py-12"
    x-data="{
        selectedAddressId: '{{ $defaultAddress?->id ?? ($savedAddresses->isNotEmpty() ? $savedAddresses->first()->id : 'new') }}',
        useNewAddress: {{ $savedAddresses->isEmpty() ? 'true' : 'false' }},
        payMethod: 'cod',
        isGift: false,
        deliveryTime: 'anytime',
        submitting: false,
        redeemPoints: false,
        ptsDiscount: {{ $ptsDiscount }},
        baseSubtotal: {{ $rawSubtotal }},
        shippingFee: {{ $rawShipping }},
        giftWrapCost: 0,
        get currentTotal() {
            let t = this.baseSubtotal + this.shippingFee + (this.isGift ? this.giftWrapCost : 0);
            if (this.redeemPoints) {
                t = Math.max(0, t - this.ptsDiscount);
            }
            return t;
        }
    }"
>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── CHECKOUT TOP BAR ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-5 border-b border-black/10">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-black/50 mb-1">
                    <a href="{{ route('cart.index') }}" class="hover:text-black transition-colors">{{ $ar ? 'السلة' : 'Cart' }}</a>
                    <span>/</span>
                    <span class="text-black">{{ $ar ? 'إتمام الطلب' : 'Checkout' }}</span>
                </div>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold uppercase tracking-tight text-black flex items-center gap-2.5">
                    <span>{{ $ar ? 'إتمام الطلب والشحن' : 'Secure Checkout' }}</span>
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    <span>{{ $ar ? 'تشفير آمن 256-Bit SSL' : '256-Bit SSL Encrypted' }}</span>
                </div>
            </div>
        </div>

        {{-- ── ERROR BANNER ── --}}
        @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50/90 p-4 sm:p-5 mb-8 flex items-start gap-3.5 shadow-sm">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <div class="flex-1">
                <h4 class="font-bold text-xs uppercase tracking-wider text-red-900 mb-1">
                    {{ $ar ? 'يرجى مراجعة البيانات التالية:' : 'Please correct the following errors:' }}
                </h4>
                <ul class="text-xs text-red-800 space-y-1 font-sans">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- ── TWO-COLUMN CHECKOUT FORM ── --}}
        <form action="{{ route('checkout.place') }}" method="POST" id="checkoutForm" @submit="submitting = true">
        @csrf

        @if(!empty($cartItems['direct']['product_id']) || request()->filled('product_id'))
            <input type="hidden" name="product_id" value="{{ $cartItems['direct']['product_id'] ?? request('product_id') }}">
            @if(!empty($cartItems['direct']['variant_id']) || request()->filled('variant_id'))
                <input type="hidden" name="variant_id" value="{{ $cartItems['direct']['variant_id'] ?? request('variant_id') }}">
            @endif
            <input type="hidden" name="qty" value="{{ $cartItems['direct']['qty'] ?? request('qty', 1) }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- ════ LEFT COLUMN: STEPS (7 cols) ════ --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- ── STEP 1: DELIVERY & ADDRESS ─────────────────── --}}
                <div class="bg-white rounded-2xl border border-black/10 p-5 sm:p-7 shadow-sm transition-all">

                    {{-- Step Header --}}
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-black/10">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-black text-white flex items-center justify-center font-bold text-xs shadow-sm">1</span>
                            <h2 class="font-display text-base sm:text-lg font-bold uppercase tracking-wide text-black">
                                {{ $ar ? 'عنوان التوصيل' : 'Delivery Address' }}
                            </h2>
                        </div>
                        <span class="text-[11px] text-black/50 font-medium">{{ $ar ? 'الخطوة 1 من 3' : 'Step 1 of 3' }}</span>
                    </div>

                    <div class="space-y-5">

                        {{-- Saved addresses (Logged in users) --}}
                        @auth
                        @if($savedAddresses->isNotEmpty())
                        <div class="space-y-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-black/70">
                                {{ $ar ? 'اختر من العناوين المحفوظة' : 'Select a Saved Address' }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($savedAddresses as $addr)
                                <label
                                    class="relative flex flex-col rounded-xl border-2 cursor-pointer transition-all p-4 select-none"
                                    :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress
                                        ? 'border-black bg-black text-white shadow-md ring-2 ring-black/10'
                                        : 'border-black/15 bg-white text-black hover:border-black/40'"
                                    @click="selectedAddressId = '{{ $addr->id }}'; useNewAddress = false;"
                                >
                                    <input type="radio" name="saved_address_id" value="{{ $addr->id }}"
                                        :checked="selectedAddressId == '{{ $addr->id }}' && !useNewAddress" class="sr-only">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-[10px] uppercase tracking-wider px-2.5 py-0.5 rounded-full"
                                            :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'bg-white/20 text-white' : 'bg-black/5 text-black'">
                                            {{ $addr->label ?: ($ar ? 'عنوان' : 'Address') }}
                                        </span>
                                        @if($addr->is_default)
                                        <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full"
                                            :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'bg-amber-400 text-black' : 'bg-amber-100 text-amber-900'">
                                            {{ $ar ? 'افتراضي' : 'Default' }}
                                        </span>
                                        @endif
                                    </div>
                                    <p class="font-bold text-sm mb-1">{{ $addr->full_name }}</p>
                                    <p class="text-xs opacity-80 leading-relaxed truncate">{{ $addr->street_address }}</p>
                                    <p class="text-xs opacity-80">{{ $addr->city }}, Egypt</p>
                                    <p class="text-[11px] font-mono mt-2 opacity-70" dir="ltr">{{ $addr->phone }}</p>
                                </label>
                                @endforeach

                                {{-- New address toggle pill --}}
                                <label
                                    class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed cursor-pointer transition-all p-5 min-h-[120px] text-center select-none"
                                    :class="useNewAddress ? 'border-black bg-black text-white shadow-md' : 'border-black/20 bg-[#FAF9F5] hover:border-black/50 text-black'"
                                    @click="useNewAddress = true; selectedAddressId = 'new'; $nextTick(() => window.dispatchEvent(new CustomEvent('map-refresh-checkout-map')));"
                                >
                                    <span class="text-2xl font-bold mb-1" :class="useNewAddress ? 'text-white' : 'text-black/50'">＋</span>
                                    <span class="font-bold text-xs uppercase tracking-wider">
                                        {{ $ar ? 'استخدام عنوان جديد' : 'Use New Address' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        @endif
                        @endauth

                        {{-- New Address Inputs Section --}}
                        <div x-show="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-4">

                            {{-- Interactive Map Locator --}}
                            <div class="rounded-xl overflow-hidden border border-black/10">
                                @include('partials.location-map', [
                                    'mapId'         => 'checkout-map',
                                    'latInputId'    => 'checkout-lat',
                                    'lngInputId'    => 'checkout-lng',
                                    'cityInputId'   => 'checkout-city',
                                    'streetInputId' => 'checkout-street',
                                    'disabled'      => false,
                                ])
                            </div>

                            {{-- Full Name & Phone --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-xs uppercase tracking-wider text-black/80 mb-1.5">
                                        {{ $ar ? 'الاسم الكامل' : 'Full Name' }} <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="full_name"
                                        :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        placeholder="{{ $ar ? 'مثال: أحمد محمود' : 'e.g. Alexander Vance' }}"
                                        value="{{ auth()->user()?->name }}"
                                        class="w-full rounded-xl border border-black/15 bg-[#FAF9F5] p-3.5 text-xs sm:text-sm text-black placeholder:text-black/35 focus:bg-white focus:border-black focus:ring-2 focus:ring-black/10 focus:outline-none transition-all disabled:opacity-40">
                                </div>
                                <div>
                                    <label class="block font-bold text-xs uppercase tracking-wider text-black/80 mb-1.5">
                                        {{ $ar ? 'رقم الهاتف (واتساب)' : 'Phone Number (WhatsApp)' }} <span class="text-rose-500">*</span>
                                    </label>
                                    @php
                                        $prefillPhone = auth()->user()?->defaultAddress()?->phone ?? auth()->user()?->phone ?? '';
                                    @endphp
                                    <input type="tel" name="phone"
                                        :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        placeholder="010XXXXXXXX"
                                        value="{{ $prefillPhone }}"
                                        class="w-full rounded-xl border border-black/15 bg-[#FAF9F5] p-3.5 text-xs sm:text-sm text-black placeholder:text-black/35 focus:bg-white focus:border-black focus:ring-2 focus:ring-black/10 focus:outline-none transition-all disabled:opacity-40 font-mono"
                                        dir="ltr">
                                </div>
                            </div>

                            {{-- City & Governorate --}}
                            <div>
                                <label class="block font-bold text-xs uppercase tracking-wider text-black/80 mb-1.5">
                                    {{ $ar ? 'المحافظة / المدينة' : 'City / Governorate' }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="city" id="checkout-city"
                                    :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    placeholder="{{ $ar ? 'القاهرة، الجيزة، الإسكندرية...' : 'Cairo, Giza, Alexandria...' }}"
                                    class="w-full rounded-xl border border-black/15 bg-[#FAF9F5] p-3.5 text-xs sm:text-sm text-black placeholder:text-black/35 focus:bg-white focus:border-black focus:ring-2 focus:ring-black/10 focus:outline-none transition-all disabled:opacity-40">
                            </div>

                            {{-- Detailed Street Address --}}
                            <div>
                                <label class="block font-bold text-xs uppercase tracking-wider text-black/80 mb-1.5">
                                    {{ $ar ? 'العنوان بالتفصيل (اسم الشارع، رقم العمارة، الطابق)' : 'Detailed Street Address (Building, Street, Floor, Apt)' }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="street_address" id="checkout-street"
                                    :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    placeholder="{{ $ar ? 'رقم المبنى، الشارع الرئيسي، علامة مميزة' : 'Building no., Street, Apartment, Landmark' }}"
                                    class="w-full rounded-xl border border-black/15 bg-[#FAF9F5] p-3.5 text-xs sm:text-sm text-black placeholder:text-black/35 focus:bg-white focus:border-black focus:ring-2 focus:ring-black/10 focus:outline-none transition-all disabled:opacity-40">
                            </div>

                            {{-- Save address for authenticated customer --}}
                            @auth
                            <div class="flex flex-wrap items-center gap-4 pt-3 border-t border-black/10">
                                <label class="flex items-center gap-2.5 cursor-pointer"
                                    :class="(!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}) ? 'opacity-30 pointer-events-none' : ''">
                                    <input type="checkbox" name="save_to_address_book" value="1" checked
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        class="w-4 h-4 rounded text-black accent-black shrink-0">
                                    <span class="text-xs font-bold uppercase tracking-wider text-black/80">
                                        {{ $ar ? 'حفظ هذا العنوان للطلبات القادمة' : 'Save to my address book' }}
                                    </span>
                                </label>
                                <input type="text" name="address_label"
                                    placeholder="{{ $ar ? 'تسمية العنوان (المنزل، العمل...)' : 'Label (Home, Office...)' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    class="rounded-lg border border-black/20 px-3 py-1.5 text-xs bg-white focus:outline-none focus:border-black w-48 disabled:opacity-30">
                            </div>
                            @endauth

                        </div>

                    </div>
                </div>

                {{-- ── STEP 2: BESPOKE OPTIONS & DELIVERY PREFERENCES ─── --}}
                <div class="bg-white rounded-2xl border border-black/10 p-5 sm:p-7 shadow-sm transition-all">

                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-black/10">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-black text-white flex items-center justify-center font-bold text-xs shadow-sm">2</span>
                            <h2 class="font-display text-base sm:text-lg font-bold uppercase tracking-wide text-black">
                                {{ $ar ? 'خيارات التوصيل والتغليف' : 'Delivery & Packaging Options' }}
                            </h2>
                        </div>
                        <span class="text-[11px] text-black/50 font-medium">{{ $ar ? 'الخطوة 2 من 3' : 'Step 2 of 3' }}</span>
                    </div>

                    <div class="space-y-6">

                        {{-- 🎁 Luxury Gift Wrapping & Card --}}
                        <div class="rounded-xl border border-black/10 bg-[#FAF9F5] p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-4">
                                <label class="flex items-start gap-3 cursor-pointer select-none">
                                    <input type="checkbox" name="gift_wrap" value="1" x-model="isGift" class="w-5 h-5 rounded text-black accent-black mt-0.5 shrink-0">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-base">🎁</span>
                                            <span class="font-bold text-xs sm:text-sm text-black">
                                                {{ $ar ? 'تغليف هدايا ملكي فاخر وبطاقة إهداء' : 'Complimentary Luxury Gift Wrapping & Card' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-black/60 mt-1 leading-relaxed">
                                            {{ $ar ? 'صندوق هدايا مغناطيسي فخم، شريط حريري أسود، وبطاقة إهداء خاصة مطبوعة بأناقة.' : 'Includes our signature bespoke magnetic box, silk ribbon, and an embossed personalized greeting card.' }}
                                        </p>
                                    </div>
                                </label>
                                <span class="px-2.5 py-1 rounded-full bg-black/5 text-black font-bold text-[10px] uppercase tracking-wider shrink-0">
                                    {{ $ar ? 'مجاني' : 'VIP Gift' }}
                                </span>
                            </div>

                            {{-- Gift Message Textarea (Shown when gift toggle is active) --}}
                            <div x-show="isGift" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 pt-4 border-t border-black/10">
                                <label class="block font-bold text-xs text-black/80 mb-1.5">
                                    {{ $ar ? 'رسالة الإهداء الخاصة بك (ستطبع على بطاقة الإهداء):' : 'Your Personal Gift Message (Printed on stationery card):' }}
                                </label>
                                <textarea name="gift_message" rows="2"
                                    placeholder="{{ $ar ? 'اكتب كلمتك الجميلة لمن تحب...' : 'Write your warm heartfelt message here...' }}"
                                    class="w-full rounded-xl border border-black/15 bg-white p-3 text-xs sm:text-sm text-black placeholder:text-black/35 focus:ring-2 focus:ring-black/10 focus:outline-none"></textarea>
                            </div>
                        </div>

                        {{-- ⏰ Preferred Delivery Window --}}
                        <div>
                            <label class="block font-bold text-xs uppercase tracking-wider text-black/80 mb-2.5">
                                {{ $ar ? 'وقت التوصيل المفضل' : 'Preferred Delivery Time Window' }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all"
                                    :class="deliveryTime === 'anytime' ? 'border-black bg-black text-white shadow-sm' : 'border-black/15 bg-white text-black hover:border-black/40'"
                                    @click="deliveryTime = 'anytime'">
                                    <input type="radio" name="preferred_delivery_time" value="anytime" x-model="deliveryTime" class="sr-only">
                                    <span class="text-sm">⚡</span>
                                    <div>
                                        <p class="font-bold text-xs">{{ $ar ? 'أي وقت (الأسرع)' : 'Anytime (Fastest)' }}</p>
                                        <p class="text-[10px] opacity-70">{{ $ar ? 'خلال ساعات العمل' : 'Standard dispatch' }}</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all"
                                    :class="deliveryTime === 'morning' ? 'border-black bg-black text-white shadow-sm' : 'border-black/15 bg-white text-black hover:border-black/40'"
                                    @click="deliveryTime = 'morning'">
                                    <input type="radio" name="preferred_delivery_time" value="morning" x-model="deliveryTime" class="sr-only">
                                    <span class="text-sm">🌅</span>
                                    <div>
                                        <p class="font-bold text-xs">{{ $ar ? 'صباحاً' : 'Morning' }}</p>
                                        <p class="text-[10px] opacity-70">10:00 AM – 3:00 PM</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all"
                                    :class="deliveryTime === 'evening' ? 'border-black bg-black text-white shadow-sm' : 'border-black/15 bg-white text-black hover:border-black/40'"
                                    @click="deliveryTime = 'evening'">
                                    <input type="radio" name="preferred_delivery_time" value="evening" x-model="deliveryTime" class="sr-only">
                                    <span class="text-sm">🌆</span>
                                    <div>
                                        <p class="font-bold text-xs">{{ $ar ? 'مساءً' : 'Evening' }}</p>
                                        <p class="text-[10px] opacity-70">4:00 PM – 10:00 PM</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- 📝 Delivery Notes / Courier Instructions --}}
                        <div>
                            <label class="block font-bold text-xs uppercase tracking-wider text-black/80 mb-1.5">
                                {{ $ar ? 'ملاحظات إضافية لمندوب الشحن (اختياري)' : 'Special Delivery Instructions (Optional)' }}
                            </label>
                            <input type="text" name="delivery_instructions"
                                placeholder="{{ $ar ? 'مثال: يرجى الاتصال قبل الوصول بـ 15 دقيقة أو تسليمها للأمن' : 'e.g. Call 15 min before arrival, leave with concierge' }}"
                                class="w-full rounded-xl border border-black/15 bg-[#FAF9F5] p-3.5 text-xs sm:text-sm text-black placeholder:text-black/35 focus:bg-white focus:border-black focus:ring-2 focus:ring-black/10 focus:outline-none transition-all">
                        </div>

                    </div>
                </div>

                {{-- ── STEP 3: PAYMENT METHOD ─────────────────────── --}}
                <div class="bg-white rounded-2xl border border-black/10 p-5 sm:p-7 shadow-sm transition-all">

                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-black/10">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-black text-white flex items-center justify-center font-bold text-xs shadow-sm">3</span>
                            <h2 class="font-display text-base sm:text-lg font-bold uppercase tracking-wide text-black">
                                {{ $ar ? 'طريقة الدفع' : 'Payment Method' }}
                            </h2>
                        </div>
                        <span class="text-[11px] text-black/50 font-medium">{{ $ar ? 'الخطوة 3 من 3' : 'Step 3 of 3' }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- COD --}}
                        <label class="relative flex items-start gap-3.5 rounded-xl border-2 p-4 sm:p-5 cursor-pointer transition-all select-none"
                            :class="payMethod === 'cod' ? 'border-black bg-black text-white shadow-md' : 'border-black/15 bg-white text-black hover:border-black/40'"
                            @click="payMethod = 'cod'">
                            <input type="radio" name="payment_method" value="cod" x-model="payMethod" class="sr-only">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                                :class="payMethod === 'cod' ? 'bg-white/15 text-white' : 'bg-black/5 text-black'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-sm mb-0.5">
                                    {{ $ar ? 'الدفع عند الاستلام' : 'Cash on Delivery (COD)' }}
                                </p>
                                <p class="text-xs opacity-75 leading-relaxed">
                                    {{ $ar ? 'عاين وافحص قطعك الفاخرة بنفسك قبل الدفع' : 'Inspect your bespoke piece before paying' }}
                                </p>
                            </div>
                        </label>

                        {{-- Paymob / Cards / ValU --}}
                        <label class="relative flex items-start gap-3.5 rounded-xl border-2 p-4 sm:p-5 cursor-pointer transition-all select-none"
                            :class="payMethod === 'paymob' ? 'border-black bg-black text-white shadow-md' : 'border-black/15 bg-white text-black hover:border-black/40'"
                            @click="payMethod = 'paymob'">
                            <input type="radio" name="payment_method" value="paymob" x-model="payMethod" class="sr-only">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                                :class="payMethod === 'paymob' ? 'bg-white/15 text-white' : 'bg-black/5 text-black'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-sm mb-0.5">
                                    {{ $ar ? 'بطاقات / Paymob / ValU' : 'Card / Paymob / ValU' }}
                                </p>
                                <p class="text-xs opacity-75 leading-relaxed">
                                    {{ $ar ? 'فيزا، ماستركارد، محافظ إلكترونية، وتقسيط' : 'Visa, Mastercard, Mobile Wallets & Installments' }}
                                </p>
                            </div>
                        </label>

                    </div>
                </div>

                {{-- Feedback / Survey Partial --}}
                <div>@include('partials.survey-widget', ['targetPage' => 'checkout'])</div>

            </div>

            {{-- ════ RIGHT COLUMN: LIVE ORDER SUMMARY (5 cols) ════ --}}
            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl border border-black/10 p-5 sm:p-7 shadow-sm sticky top-6">

                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-black/10">
                        <h3 class="font-display text-base sm:text-lg font-bold uppercase tracking-wide text-black">
                            {{ $ar ? 'ملخص الطلب' : 'Order Summary' }}
                        </h3>
                        <span class="px-2.5 py-1 rounded-full bg-black/5 text-black font-bold text-xs">
                            {{ count($cartItemsList) }} {{ $ar ? 'قطع' : 'Items' }}
                        </span>
                    </div>

                    {{-- Items Preview --}}
                    @if(count($cartItemsList) > 0)
                    <div class="space-y-3.5 mb-6 max-h-72 overflow-y-auto pr-1">
                        @foreach($cartItemsList as $item)
                        <div class="flex items-center gap-3.5 pb-3 border-b border-black/5 last:border-0 last:pb-0">
                            <div class="w-14 h-14 rounded-xl border border-black/10 bg-[#FAF9F5] shrink-0 overflow-hidden flex items-center justify-center p-1">
                                @if(!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain" decoding="async">
                                @else
                                <span class="text-black/20 text-lg">◆</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-xs sm:text-sm text-black truncate">{{ $item['name'] ?? 'ATELIER Piece' }}</h4>
                                @if(!empty($item['variant']))
                                <div class="text-[11px] text-black/60 mt-0.5 flex items-center gap-1.5">
                                    @if(!empty($item['color_hex']))
                                    <span class="w-2.5 h-2.5 rounded-full border border-black/20 shrink-0" style="background: {{ $item['color_hex'] }}"></span>
                                    @endif
                                    <span class="truncate">{{ $item['variant'] }}</span>
                                </div>
                                @endif
                                <p class="text-[11px] text-black/45 mt-0.5">{{ $ar ? 'الكمية' : 'Qty' }}: {{ $item['qty'] ?? 1 }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-bold text-xs sm:text-sm text-black">
                                    {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}
                                    <span class="text-[10px] font-normal text-black/60">{{ $ar ? 'ج.م' : 'EGP' }}</span>
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- VIP Loyalty Points Redemption Toggle --}}
                    @auth
                    @if(($loyaltyEnabled ?? true) && ($userPoints ?? 0) >= ($ptsUnit ?? 100) && ($maxPointsDiscountEgp ?? 0) > 0)
                    <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-3.5 mb-5 shadow-sm">
                        <label class="flex items-start justify-between gap-3 cursor-pointer select-none">
                            <div class="flex items-start gap-2.5">
                                <input type="checkbox" name="redeem_loyalty_points" value="1" x-model="redeemPoints" class="w-4 h-4 rounded text-black accent-black mt-0.5 shrink-0">
                                <div>
                                    <span class="font-bold text-xs text-amber-950 block">
                                        {{ $ar ? 'استبدال نقاط الـ VIP كخصم فوري' : 'Redeem VIP Privilege Points' }}
                                    </span>
                                    <span class="text-[11px] text-amber-800/80 block mt-0.5">
                                        {{ $ar ? 'رصيدك: ' . number_format($userPoints) . ' نقطة' : 'Available balance: ' . number_format($userPoints) . ' pts' }}
                                    </span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-xs text-emerald-800 shrink-0">
                                -{{ number_format($maxPointsDiscountEgp) }} {{ $ar ? 'ج.م' : 'EGP' }}
                            </span>
                        </label>
                    </div>
                    @endif
                    @endauth

                    {{-- Financial Totals Breakdown --}}
                    <div class="border-t border-black/10 pt-4 space-y-2.5 text-xs sm:text-sm text-black/70">
                        <div class="flex justify-between items-center">
                            <span>{{ $ar ? 'المجموع الفرعي' : 'Subtotal' }}</span>
                            <span class="font-bold text-black">{{ number_format($rawSubtotal) }} {{ $ar ? 'ج.م' : 'EGP' }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span>{{ $ar ? 'الشحن والتوصيل السريع' : 'Express Delivery' }}</span>
                            @if($rawShipping == 0)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase tracking-wider">
                                {{ $ar ? 'مجاني' : 'FREE' }}
                            </span>
                            @else
                            <span class="font-bold text-black">{{ number_format($rawShipping) }} {{ $ar ? 'ج.م' : 'EGP' }}</span>
                            @endif
                        </div>

                        <template x-if="isGift">
                            <div class="flex justify-between items-center text-black/70">
                                <span>{{ $ar ? 'تغليف الهدايا الملكي' : 'Luxury Gift Wrapping' }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-black/5 text-black font-bold text-[10px] uppercase">
                                    {{ $ar ? 'مجاني' : 'VIP Free' }}
                                </span>
                            </div>
                        </template>

                        <template x-if="redeemPoints && ptsDiscount > 0">
                            <div class="flex justify-between items-center text-emerald-700 font-bold">
                                <span>{{ $ar ? 'خصم نقاط الـ VIP' : 'VIP Points Discount' }}</span>
                                <span class="font-mono" x-text="'-' + Number(ptsDiscount).toLocaleString() + ' {{ $ar ? 'ج.م' : 'EGP' }}'"></span>
                            </div>
                        </template>

                        {{-- Grand Total --}}
                        <div class="flex justify-between items-center border-t border-black/10 pt-4 mt-2 text-black">
                            <span class="font-display font-extrabold text-base uppercase tracking-wider">{{ $ar ? 'الإجمالي النهائي' : 'Total Amount' }}</span>
                            <div class="text-right">
                                <span class="font-display text-2xl sm:text-3xl font-black" x-text="currentTotal.toLocaleString()">
                                    {{ number_format($rawTotal) }}
                                </span>
                                <span class="font-bold text-xs ml-1 text-black/70">{{ $ar ? 'ج.م' : 'EGP' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Submit CTA Button --}}
                    <button type="submit"
                        :disabled="submitting"
                        class="w-full mt-6 rounded-xl bg-black text-white font-display text-xs sm:text-sm font-bold uppercase tracking-[0.2em] py-4 px-6 hover:bg-neutral-800 active:scale-[0.99] transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!submitting">{{ $ar ? 'تأكيد وإرسال الطلب الآن ←' : 'Place Order Now →' }}</span>
                        <span x-show="submitting" class="flex items-center gap-2" style="display: none;">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ $ar ? 'جاري تجهيز طلبك...' : 'Processing your order...' }}</span>
                        </span>
                    </button>

                    {{-- Back Link --}}
                    <div class="mt-4 text-center">
                        <a href="{{ route('home') }}" class="text-xs font-semibold text-black/50 hover:text-black transition-colors">
                            {{ $ar ? '← العودة ومواصلة التسوق' : '← Continue Shopping' }}
                        </a>
                    </div>

                    {{-- Guarantee & Trust Icons --}}
                    <div class="mt-6 pt-5 border-t border-black/10 grid grid-cols-3 gap-2 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-base">🛡️</span>
                            <span class="text-[10px] font-bold text-black/60">{{ $ar ? 'دفع آمن 100%' : '100% Secure' }}</span>
                        </div>
                        <div class="flex flex-col items-center gap-1 border-x border-black/10">
                            <span class="text-base">✨</span>
                            <span class="text-[10px] font-bold text-black/60">{{ $ar ? 'قطع أصلية 100%' : 'Authentic' }}</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-base">🚀</span>
                            <span class="text-[10px] font-bold text-black/60">{{ $ar ? 'توصيل سريع' : 'Fast Express' }}</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        </form>

    </div>
</div>
@endsection
