@extends('layouts.app')

@section('title', ((($settings['storefront_lang'] ?? 'en') === 'ar') ? 'إتمام الطلب' : 'Checkout') . ' — ' . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', (($settings['storefront_lang'] ?? 'en') === 'ar') ? 'أتمم طلبك بأمان مع ATELIER.' : 'Complete your ATELIER order securely.')

@section('content')

@php
    // Calculate locally from $settings (passed by controller) — $isArabicStore is only in the layout scope
    $ar = (($settings['storefront_lang'] ?? 'en') === 'ar');
@endphp

<div class="bg-[#F5F5F0] min-h-screen"
    x-data="{
        selectedAddressId: '{{ $defaultAddress?->id ?? ($savedAddresses->isNotEmpty() ? $savedAddresses->first()->id : 'new') }}',
        useNewAddress: {{ $savedAddresses->isEmpty() ? 'true' : 'false' }},
        payMethod: 'cod',
    }"
>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 lg:py-14">

        {{-- ── PAGE HEADER ────────────────────────────── --}}
        <div class="mb-8 border-b-2 border-black pb-5">
            <p class="text-[10px] font-bold uppercase tracking-[.28em] text-black/40 mb-1">
                {{ $ar ? 'إتمام الطلب' : 'Secure Checkout' }}
            </p>
            <h1 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-black leading-none">
                {{ $ar ? 'Checkout' : 'Checkout' }}
            </h1>
        </div>

        {{-- ── ERROR BANNER ───────────────────────────── --}}
        @if($errors->any())
        <div class="border-2 border-black bg-white p-4 mb-6 flex items-start gap-3 shadow-[3px_3px_0_0_rgba(0,0,0,1)]">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <ul class="text-xs font-sans space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- ── TWO-COLUMN LAYOUT ──────────────────────── --}}
        <form action="{{ route('checkout.place') }}" method="POST" id="checkoutForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

            {{-- ════ LEFT: Delivery + Payment (3 cols) ════ --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- ── STEP 1: DELIVERY ─────────────────── --}}
                <div class="bg-white border-2 border-black shadow-[4px_4px_0_0_rgba(0,0,0,1)]">

                    {{-- Header --}}
                    <div class="flex items-center gap-3 px-5 py-3.5 border-b-2 border-black bg-black">
                        <span class="font-display text-xs bg-white text-black px-2 py-0.5">01</span>
                        <span class="font-display text-[11px] uppercase tracking-[.2em] text-white">
                            {{ $ar ? 'عنوان التوصيل' : 'Delivery Details' }}
                        </span>
                    </div>

                    <div class="p-5 space-y-5">

                        {{-- Saved addresses --}}
                        @auth
                        @if($savedAddresses->isNotEmpty())
                        <div class="space-y-3">
                            <p class="text-[10px] font-bold uppercase tracking-[.18em] text-black/50">
                                {{ $ar ? 'العناوين المحفوظة' : 'Saved Addresses' }}
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($savedAddresses as $addr)
                                <label
                                    class="relative flex flex-col border-2 cursor-pointer transition-all p-4"
                                    :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress
                                        ? 'border-black bg-black text-white shadow-[3px_3px_0_0_rgba(0,0,0,1)] -translate-y-0.5'
                                        : 'border-black/20 bg-white hover:border-black'"
                                    @click="selectedAddressId = '{{ $addr->id }}'; useNewAddress = false;"
                                >
                                    <input type="radio" name="saved_address_id" value="{{ $addr->id }}"
                                        :checked="selectedAddressId == '{{ $addr->id }}' && !useNewAddress" class="sr-only">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-[9px] uppercase tracking-widest px-2 py-0.5 border"
                                            :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'bg-white text-black border-white' : 'bg-black text-white border-black'">
                                            {{ $addr->label ?: ($ar ? 'عنوان' : 'Address') }}
                                        </span>
                                        @if($addr->is_default)
                                        <span class="text-[8px] font-bold uppercase border px-1.5 py-0.5"
                                            :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'border-white text-white' : 'border-black text-black'">
                                            {{ $ar ? 'افتراضي' : 'Default' }}
                                        </span>
                                        @endif
                                    </div>
                                    <p class="font-bold text-sm mb-0.5">{{ $addr->full_name }}</p>
                                    <p class="text-xs opacity-70 leading-relaxed">{{ $addr->street_address }}</p>
                                    <p class="text-xs opacity-70">{{ $addr->city }}, Egypt</p>
                                    <p class="text-[10px] font-mono mt-1.5 opacity-60">{{ $addr->phone }}</p>
                                    {{-- Checkmark --}}
                                    <div class="absolute top-3 right-3 w-4 h-4 border-2 flex items-center justify-center"
                                        :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'border-white bg-white' : 'border-black/30'">
                                        <svg x-show="selectedAddressId == '{{ $addr->id }}' && !useNewAddress"
                                            class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="square" d="M2 6l3 3 5-5"/>
                                        </svg>
                                    </div>
                                </label>
                                @endforeach

                                {{-- New address option --}}
                                <label
                                    class="relative flex flex-col items-center justify-center border-2 cursor-pointer transition-all p-5 min-h-[100px] text-center"
                                    :class="useNewAddress ? 'border-black bg-black text-white shadow-[3px_3px_0_0_rgba(0,0,0,1)] -translate-y-0.5' : 'border-dashed border-black/30 bg-white hover:border-black'"
                                    @click="useNewAddress = true; selectedAddressId = 'new'; $nextTick(() => window.dispatchEvent(new CustomEvent('map-refresh-checkout-map')));"
                                >
                                    <span class="text-xl font-bold mb-1" :class="useNewAddress ? 'text-white' : 'text-black/40'">＋</span>
                                    <span class="font-bold text-[10.5px] uppercase tracking-[.15em]">
                                        {{ $ar ? 'عنوان جديد' : 'New Address' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        @endif
                        @endauth

                        {{-- New address form --}}
                        <div x-show="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-4">

                            @auth
                            @if($savedAddresses->isNotEmpty())
                            <div class="h-px bg-black/10"></div>
                            @endif
                            @endauth

                            {{-- Map --}}
                            @include('partials.location-map', [
                                'mapId'         => 'checkout-map',
                                'latInputId'    => 'checkout-lat',
                                'lngInputId'    => 'checkout-lng',
                                'cityInputId'   => 'checkout-city',
                                'streetInputId' => 'checkout-street',
                                'disabled'      => false,
                            ])

                            {{-- Name + Phone --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <div>
                                    <label class="block font-bold text-[10px] uppercase tracking-[.18em] text-black mb-1.5">
                                        {{ $ar ? 'الاسم الكامل' : 'Full Name' }} <span class="text-black/40">*</span>
                                    </label>
                                    <input type="text" name="full_name"
                                        :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        placeholder="{{ $ar ? 'مثال: محمد أحمد' : 'e.g. John Smith' }}"
                                        value="{{ auth()->user()?->name }}"
                                        class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40">
                                </div>
                                <div>
                                    <label class="block font-bold text-[10px] uppercase tracking-[.18em] text-black mb-1.5">
                                        {{ $ar ? 'رقم الهاتف (WhatsApp)' : 'Phone (WhatsApp)' }} <span class="text-black/40">*</span>
                                    </label>
                                    @php
                                        $prefillPhone = auth()->user()?->defaultAddress()?->phone ?? auth()->user()?->phone ?? '';
                                    @endphp
                                    <input type="tel" name="phone"
                                        :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        placeholder="010XXXXXXXX"
                                        value="{{ $prefillPhone }}"
                                        class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40 font-mono"
                                        dir="ltr">
                                </div>
                            </div>

                            {{-- City --}}
                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-[.18em] text-black mb-1.5">
                                    {{ $ar ? 'المحافظة / المدينة' : 'City / Governorate' }} <span class="text-black/40">*</span>
                                </label>
                                <input type="text" name="city" id="checkout-city"
                                    :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    placeholder="{{ $ar ? 'القاهرة / الجيزة / الإسكندرية' : 'Cairo / Giza / Alexandria' }}"
                                    class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40">
                            </div>

                            {{-- Street --}}
                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-[.18em] text-black mb-1.5">
                                    {{ $ar ? 'العنوان بالتفصيل' : 'Street Address' }} <span class="text-black/40">*</span>
                                </label>
                                <input type="text" name="street_address" id="checkout-street"
                                    :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    placeholder="{{ $ar ? 'رقم المبنى، الشارع، الطابق، الشقة' : 'Building no., Street, Floor, Apt' }}"
                                    class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40">
                            </div>

                            {{-- Save to book --}}
                            @auth
                            <div class="flex flex-wrap items-center gap-4 pt-1 border-t border-black/10">
                                <label class="flex items-center gap-2.5 cursor-pointer"
                                    :class="(!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}) ? 'opacity-30 pointer-events-none' : ''">
                                    <input type="checkbox" name="save_to_address_book" value="1" checked
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        class="w-4 h-4 accent-black shrink-0">
                                    <span class="text-[10px] uppercase tracking-wider font-bold">
                                        {{ $ar ? 'حفظ في دفتر العناوين' : 'Save to address book' }}
                                    </span>
                                </label>
                                <input type="text" name="address_label"
                                    placeholder="{{ $ar ? 'اسم (البيت، العمل...)' : 'Label (Home, Work...)' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    class="border border-black p-2 text-[11px] bg-white focus:outline-none w-40 disabled:opacity-30">
                            </div>
                            @endauth
                        </div>

                    </div>
                </div>

                {{-- ── STEP 2: PAYMENT ───────────────────── --}}
                <div class="bg-white border-2 border-black shadow-[4px_4px_0_0_rgba(0,0,0,1)]">

                    <div class="flex items-center gap-3 px-5 py-3.5 border-b-2 border-black bg-black">
                        <span class="font-display text-xs bg-white text-black px-2 py-0.5">02</span>
                        <span class="font-display text-[11px] uppercase tracking-[.2em] text-white">
                            {{ $ar ? 'طريقة الدفع' : 'Payment Method' }}
                        </span>
                    </div>

                    <div class="p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            {{-- COD --}}
                            <label class="relative flex items-start gap-4 border-2 p-4 cursor-pointer transition-all"
                                :class="payMethod === 'cod' ? 'border-black bg-black text-white shadow-[3px_3px_0_0_rgba(0,0,0,1)] -translate-y-0.5' : 'border-black/20 hover:border-black bg-white'"
                                @click="payMethod = 'cod'">
                                <input type="radio" name="payment_method" value="cod" x-model="payMethod" class="sr-only">
                                <svg class="w-5 h-5 mt-0.5 shrink-0" :class="payMethod==='cod'?'text-white':'text-black/50'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                                <div>
                                    <span class="font-bold text-xs uppercase tracking-[.15em] block mb-0.5">
                                        {{ $ar ? 'الدفع عند الاستلام' : 'Cash on Delivery' }}
                                    </span>
                                    <span class="text-[10px] opacity-70 block">
                                        {{ $ar ? 'فحص القطعة قبل الدفع' : 'Inspect before you pay' }}
                                    </span>
                                </div>
                                <div class="absolute top-3.5 right-3.5 w-4 h-4 border-2 flex items-center justify-center"
                                    :class="payMethod==='cod'?'border-white bg-white':'border-black/30'">
                                    <svg x-show="payMethod==='cod'" class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="square" d="M2 6l3 3 5-5"/></svg>
                                </div>
                            </label>

                            {{-- Paymob --}}
                            <label class="relative flex items-start gap-4 border-2 p-4 cursor-pointer transition-all"
                                :class="payMethod === 'paymob' ? 'border-black bg-black text-white shadow-[3px_3px_0_0_rgba(0,0,0,1)] -translate-y-0.5' : 'border-black/20 hover:border-black bg-white'"
                                @click="payMethod = 'paymob'">
                                <input type="radio" name="payment_method" value="paymob" x-model="payMethod" class="sr-only">
                                <svg class="w-5 h-5 mt-0.5 shrink-0" :class="payMethod==='paymob'?'text-white':'text-black/50'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                                <div>
                                    <span class="font-bold text-xs uppercase tracking-[.15em] block mb-0.5">
                                        {{ $ar ? 'بطاقة / Paymob / ValU' : 'Card / Paymob / ValU' }}
                                    </span>
                                    <span class="text-[10px] opacity-70 block">
                                        {{ $ar ? 'دفع إلكتروني مشفر' : 'Secure online payment' }}
                                    </span>
                                </div>
                                <div class="absolute top-3.5 right-3.5 w-4 h-4 border-2 flex items-center justify-center"
                                    :class="payMethod==='paymob'?'border-white bg-white':'border-black/30'">
                                    <svg x-show="payMethod==='paymob'" class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="square" d="M2 6l3 3 5-5"/></svg>
                                </div>
                            </label>

                        </div>
                    </div>
                </div>

                {{-- Survey widget --}}
                <div>@include('partials.survey-widget', ['targetPage' => 'checkout'])</div>

            </div>

            {{-- ════ RIGHT: Order Summary (2 cols) ════ --}}
            <div class="lg:col-span-2">
                <div class="bg-white border-2 border-black shadow-[4px_4px_0_0_rgba(0,0,0,1)] sticky top-6">

                    <div class="flex items-center justify-between px-5 py-3.5 border-b-2 border-black bg-black text-white">
                        <span class="font-display text-[11px] uppercase tracking-[.2em]">
                            {{ $ar ? 'ملخص الطلب' : 'Order Summary' }}
                        </span>
                        <span class="text-[9px] font-mono text-white/60 uppercase">
                            {{ isset($cartItems) ? count($cartItems) : 0 }} {{ $ar ? 'قطع' : 'ITEMS' }}
                        </span>
                    </div>

                    <div class="p-5">

                        {{-- Cart items --}}
                        @if(isset($cartItems) && count($cartItems))
                        <div class="space-y-3.5 mb-5 max-h-72 overflow-y-auto pr-1">
                            @foreach($cartItems as $item)
                            <div class="flex items-start gap-3 pb-3 border-b border-black/10 last:border-0 last:pb-0">
                                @if(isset($item['image']))
                                <div class="w-12 h-12 border border-black/15 overflow-hidden shrink-0 bg-[#F5F5F0] flex items-center justify-center">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain" decoding="async">
                                </div>
                                @else
                                <div class="w-12 h-12 border border-black/15 bg-[#F5F5F0] flex items-center justify-center shrink-0">
                                    <span class="text-black/20 text-base">◆</span>
                                </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-xs uppercase tracking-tight leading-snug truncate">{{ $item['name'] ?? 'ATELIER Piece' }}</p>
                                    @if(!empty($item['variant']))
                                    <div class="text-[10px] uppercase tracking-wider text-black/60 mt-0.5 flex items-center gap-1.5">
                                        @if(!empty($item['color_hex']))
                                        <span class="w-2.5 h-2.5 rounded-full border border-black/20 inline-block shrink-0" style="background:{{ $item['color_hex'] }}"></span>
                                        @endif
                                        <span class="truncate">{{ $item['variant'] }}</span>
                                    </div>
                                    @endif
                                    <p class="text-[10px] text-black/40 mt-0.5">{{ $ar ? 'الكمية' : 'Qty' }}: {{ $item['qty'] ?? 1 }}</p>
                                </div>
                                <p class="font-bold text-xs shrink-0">
                                    {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}
                                    <span class="text-[9px] font-normal text-black/50">EGP</span>
                                </p>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Totals --}}
                        <div class="border-t-2 border-black/10 pt-4 space-y-2">
                            @if(isset($subtotal))
                            <div class="flex justify-between text-xs text-black/60">
                                <span>{{ $ar ? 'المجموع الفرعي' : 'Subtotal' }}</span>
                                <span class="font-mono font-bold">{{ number_format($subtotal) }} EGP</span>
                            </div>
                            @endif
                            @if(isset($shippingCost))
                            <div class="flex justify-between text-xs text-black/60">
                                <span>{{ $ar ? 'الشحن' : 'Shipping' }}</span>
                                <span class="font-mono font-bold">{{ $shippingCost == 0 ? ($ar ? 'مجاني' : 'Free') : number_format($shippingCost) . ' EGP' }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center border-t-2 border-black pt-3 mt-1">
                                <span class="font-bold text-sm uppercase tracking-wider">{{ $ar ? 'الإجمالي' : 'Total' }}</span>
                                <span class="font-display text-2xl">
                                    {{ number_format(($total ?? $subtotal ?? 0)) }}
                                    <span class="font-bold text-xs">EGP</span>
                                </span>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                            class="w-full mt-5 bg-black text-white font-display text-[11px] uppercase tracking-[.22em] py-4 border-2 border-black shadow-[4px_4px_0_0_rgba(0,0,0,1)] hover:shadow-[2px_2px_0_0_rgba(0,0,0,1)] hover:translate-x-0.5 hover:translate-y-0.5 transition-all cursor-pointer focus:outline-none"
                        >
                            {{ $ar ? 'تأكيد وإرسال الطلب ←' : 'Place Order →' }}
                        </button>

                        {{-- Back --}}
                        <div class="mt-3.5 text-center">
                            <a href="{{ route('home') }}" class="text-[10px] font-bold uppercase tracking-wider text-black/40 hover:text-black transition-colors">
                                {{ $ar ? '← مواصلة التسوق' : '← Continue Shopping' }}
                            </a>
                        </div>

                        {{-- Trust badges --}}
                        <div class="mt-5 pt-4 border-t border-black/10 flex items-center justify-center gap-5">
                            <div class="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider text-black/40">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                {{ $ar ? 'آمن ومشفر' : 'Secure' }}
                            </div>
                            <div class="w-px h-3 bg-black/15"></div>
                            <div class="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider text-black/40">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                {{ $ar ? 'تغليف فاخر' : 'Luxury Packaging' }}
                            </div>
                            <div class="w-px h-3 bg-black/15"></div>
                            <div class="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider text-black/40">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                {{ $ar ? 'شحن سريع' : 'Fast Delivery' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        </form>

    </div>
</div>
@endsection
