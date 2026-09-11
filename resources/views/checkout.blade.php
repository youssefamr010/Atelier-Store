@extends('layouts.app')

@section('title', 'Checkout — ' . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', 'Complete your ATELIER luxury order. Secure checkout with saved addresses and GPS delivery pin.')

@push('styles')
<style>
    /* ── CHECKOUT PAGE — Atelier Premium Redesign ────────────────────────────── */

    /* Step headers — Cinzel serif brand identity */
    .atl-step-hdr {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 2px solid #000;
        background: #000;
    }
    .atl-step-num {
        font-family: 'Cinzel', Georgia, serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: #fff;
        opacity: .55;
        white-space: nowrap;
    }
    .atl-step-title {
        font-family: 'Cinzel', Georgia, serif;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: #fff;
    }
    .atl-step-badge {
        font-family: 'Cinzel', Georgia, serif;
        font-weight: 900;
        font-size: 11px;
        background: #fff;
        color: #000;
        padding: 2px 8px;
        letter-spacing: .1em;
        border: 1.5px solid #fff;
        margin-left: .75rem;
    }

    /* Primary CTA button — consistent across page */
    .atl-btn-primary {
        display: block;
        width: 100%;
        background: #000;
        color: #fff;
        font-family: 'Cinzel', Georgia, serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .22em;
        text-transform: uppercase;
        padding: 1rem 1.5rem;
        border: 2px solid #000;
        box-shadow: 4px 4px 0 0 rgba(0,0,0,1);
        cursor: pointer;
        transition: box-shadow .15s, transform .15s;
        text-align: center;
    }
    .atl-btn-primary:hover {
        background: #111;
        box-shadow: 2px 2px 0 0 rgba(0,0,0,1);
        transform: translate(1px, 1px);
    }
    .atl-btn-primary:active {
        box-shadow: none;
        transform: translate(2px, 2px);
    }

    /* Error banner — black/white only */
    .atl-error-banner {
        background: #fff;
        border: 2px solid #000;
        box-shadow: 4px 4px 0 0 rgba(0,0,0,1);
        padding: 1rem 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: flex-start;
        gap: .75rem;
    }
</style>
@endpush

@section('content')

{{-- ── ORDER SUMMARY RIBBON ─────────────────────────────────────────────── --}}
@if(isset($cartItems) && count($cartItems))
<div class="bg-black text-white text-xs font-editorial font-bold uppercase tracking-[0.18em] py-2.5 px-6 flex items-center justify-between">
    <span>{{ count($cartItems) }} {{ count($cartItems) === 1 ? 'قطعة' : 'قطع' }} في طلبك</span>
    <span>{{ $settings['storeName'] ?? 'ATELIER' }} — Secure Checkout</span>
</div>
@endif

<div class="bg-[#F5F5F0] min-h-screen"
    x-data="{
        selectedAddressId: '{{ $defaultAddress?->id ?? ($savedAddresses->isNotEmpty() ? $savedAddresses->first()->id : 'new') }}',
        useNewAddress: {{ $savedAddresses->isEmpty() ? 'true' : 'false' }},
        payMethod: 'cod',
    }"
>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">

        {{-- ── PAGE HEADER ─────────────────────────────────── --}}
        <div class="mb-10">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <p class="font-editorial text-[10px] font-bold uppercase tracking-[0.28em] text-black/40 mb-1.5">
                        إتمام الطلب
                    </p>
                    <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl uppercase tracking-tight text-black leading-none">
                        Checkout
                    </h1>
                </div>
                <div class="flex items-center gap-3 pt-1 shrink-0">
                    <div class="w-5 h-px bg-black/30"></div>
                    @auth
                        <span class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/50">
                            {{ auth()->user()->name }}
                        </span>
                    @else
                        <a href="{{ route('account') }}" class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black hover:underline">
                            تسجيل الدخول للعناوين المحفوظة ←
                        </a>
                    @endauth
                </div>
            </div>
            <div class="h-px bg-black"></div>
        </div>

        {{-- ── ERROR BANNER ─────────────────────────────────── --}}
        @if($errors->any())
        <div class="atl-error-banner">
            <span class="text-black shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </span>
            <ul class="text-black text-xs font-sans space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── TWO-COLUMN LAYOUT ─────────────────────────────── --}}
        <form action="{{ route('checkout.place') }}" method="POST" id="checkoutForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-start">

            {{-- ════════════════════════════════════════════════════
                 LEFT COLUMN — Delivery & Payment (3 cols)
            ════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-3 space-y-8">

                {{-- ── STEP 1: DELIVERY DESTINATION ──────────── --}}
                <div class="bg-white border-2 border-black shadow-[6px_6px_0_0_rgba(0,0,0,1)]">

                {{-- Step header — Cinzel serif, Atelier brand voice --}}
                    <div class="atl-step-hdr">
                        <div class="flex items-center">
                            <span class="atl-step-badge">01</span>
                            <span class="atl-step-title">عنوان وتفاصيل التوصيل</span>
                        </div>
                        <span class="atl-step-num">Delivery</span>
                    </div>

                    <div class="p-6 space-y-6">

                        {{-- Saved addresses grid --}}
                        @auth
                        @if($savedAddresses->isNotEmpty())
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-editorial font-bold uppercase tracking-[0.18em] text-black/60">
                                    العناوين المحفوظة في حسابك
                                </p>
                                <span class="text-[9px] font-mono text-black/40">اختر عنواناً أو أضف جديداً</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($savedAddresses as $addr)
                                <label
                                    class="relative flex flex-col border-2 cursor-pointer transition-all duration-150 p-4 group"
                                    :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress
                                        ? 'border-black bg-black text-white shadow-[4px_4px_0_0_rgba(0,0,0,1)] -translate-y-0.5'
                                        : 'border-black/20 bg-white hover:border-black shadow-[2px_2px_0_0_rgba(0,0,0,0.05)]'"
                                    @click="selectedAddressId = '{{ $addr->id }}'; useNewAddress = false;"
                                >
                                    <input type="radio" name="saved_address_id" value="{{ $addr->id }}"
                                        :checked="selectedAddressId == '{{ $addr->id }}' && !useNewAddress"
                                        class="sr-only">

                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-editorial font-black text-[9px] uppercase tracking-widest px-2 py-0.5 border"
                                            :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'bg-white text-black border-white' : 'bg-black text-white border-black'">
                                            {{ $addr->label ?: 'عنوان' }}
                                        </span>
                                        @if($addr->is_default)
                                        <span class="text-[8px] font-editorial font-bold uppercase tracking-widest border px-1.5 py-0.5"
                                            :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'border-white text-white' : 'border-black text-black'">
                                            افتراضي
                                        </span>
                                        @endif
                                    </div>

                                    <p class="font-editorial font-bold text-sm mb-0.5">{{ $addr->full_name }}</p>
                                    <p class="text-xs font-sans opacity-75 leading-relaxed">{{ $addr->street_address }}</p>
                                    <p class="text-xs font-sans opacity-75">{{ $addr->city }}, Egypt</p>
                                    <p class="text-[10px] font-mono mt-2 opacity-70">{{ $addr->phone }}</p>

                                    @if($addr->latitude && $addr->longitude)
                                    <div class="mt-2.5 inline-flex items-center gap-1.5 text-[9px] font-mono px-2 py-1 border"
                                        :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress ? 'border-white/30 bg-white/10 text-white' : 'border-black/20 bg-[#F5F5F0] text-black'">
                                        <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg></span>
                                        <span class="font-bold">{{ number_format($addr->latitude,4) }}, {{ number_format($addr->longitude,4) }}</span>
                                    </div>
                                    @endif

                                    {{-- Selected checkmark --}}
                                    <div class="absolute top-3 right-3 w-4 h-4 border-2 flex items-center justify-center transition-all"
                                        :class="selectedAddressId == '{{ $addr->id }}' && !useNewAddress
                                            ? 'border-white bg-white'
                                            : 'border-black/30 bg-transparent'">
                                        <svg x-show="selectedAddressId == '{{ $addr->id }}' && !useNewAddress"
                                            class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="square" stroke-linejoin="miter" d="M2 6l3 3 5-5"/>
                                        </svg>
                                    </div>
                                </label>
                                @endforeach

                                {{-- New address option --}}
                                <label
                                    class="relative flex flex-col items-center justify-center border-2 cursor-pointer transition-all duration-150 p-5 min-h-[100px] text-center group"
                                    :class="useNewAddress ? 'border-black bg-black text-white shadow-[4px_4px_0_0_rgba(0,0,0,1)] -translate-y-0.5' : 'border-dashed border-black/30 bg-white hover:border-black shadow-[2px_2px_0_0_rgba(0,0,0,0.05)]'"
                                    @click="useNewAddress = true; selectedAddressId = 'new'; $nextTick(() => window.dispatchEvent(new CustomEvent('map-refresh-checkout-map')));"
                                >
                                    <span class="text-xl mb-1 font-bold" :class="useNewAddress ? 'text-white' : 'text-black/50 group-hover:text-black'">＋</span>
                                    <span class="font-editorial font-bold text-[10.5px] uppercase tracking-[0.15em] block">
                                        عنوان توصيل جديد
                                    </span>
                                    <span class="text-[10px] font-sans mt-1 opacity-70">تحديد موقع يدوي / على الخريطة</span>
                                </label>
                            </div>
                        </div>
                        @endif
                        @endauth

                        {{-- ── NEW ADDRESS FORM ──────────── --}}
                        <div x-show="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-5">

                            @auth
                            @if($savedAddresses->isNotEmpty())
                            <div class="h-px bg-black/10"></div>
                            @endif
                            @endauth

                            {{-- ── SMART MAP ─── --}}
                            <div>
                                @include('partials.location-map', [
                                    'mapId'         => 'checkout-map',
                                    'latInputId'    => 'checkout-lat',
                                    'lngInputId'    => 'checkout-lng',
                                    'cityInputId'   => 'checkout-city',
                                    'streetInputId' => 'checkout-street',
                                    'disabled'      => false,
                                ])
                            </div>

                            {{-- Customer details --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <div>
                                    <label class="block font-editorial font-bold text-[10px] uppercase tracking-[0.18em] text-black mb-1.5">
                                        الاسم الكامل <span class="text-red-600">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        placeholder="مثال: محمد أحمد"
                                        value="{{ auth()->user()?->name }}"
                                        class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40 font-sans"
                                    >
                                </div>
                                <div>
                                    <label class="block font-editorial font-bold text-[10px] uppercase tracking-[0.18em] text-black mb-1.5">
                                        رقم الهاتف (WhatsApp) <span class="text-red-600">*</span>
                                    </label>
                                    @php
                                        $prefillPhone = auth()->user()?->defaultAddress()?->phone
                                            ?? auth()->user()?->phone
                                            ?? '';
                                    @endphp
                                    <input
                                        type="tel"
                                        name="phone"
                                        :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        placeholder="010XXXXXXXX"
                                        value="{{ $prefillPhone }}"
                                        class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40 font-mono"
                                        dir="ltr"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block font-editorial font-bold text-[10px] uppercase tracking-[0.18em] text-black mb-1.5">
                                    المحافظة / المدينة <span class="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="city"
                                    id="checkout-city"
                                    :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    placeholder="القاهرة / الجيزة / الإسكندرية"
                                    class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40 font-sans"
                                >
                            </div>

                            <div>
                                <label class="block font-editorial font-bold text-[10px] uppercase tracking-[0.18em] text-black mb-1.5">
                                    العنوان بالتفصيل <span class="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="street_address"
                                    id="checkout-street"
                                    :required="useNewAddress || {{ $savedAddresses->isEmpty() ? 'true' : 'false' }}"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    placeholder="رقم المبنى، اسم الشارع، الطابق، رقم الشقة"
                                    class="w-full border-2 border-black p-3 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none focus:shadow-[3px_3px_0_0_rgba(0,0,0,1)] transition-shadow disabled:opacity-40 font-sans"
                                >
                            </div>

                            @auth
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2 border-t border-black/10">
                                <label class="flex items-center gap-2.5 cursor-pointer"
                                    :class="(!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}) ? 'opacity-30 pointer-events-none' : ''">
                                    <input type="checkbox" name="save_to_address_book" value="1" id="chk_save_book"
                                        checked
                                        :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                        class="w-4 h-4 accent-black shrink-0">
                                    <span class="font-editorial text-[10px] uppercase tracking-wider text-black">
                                        حفظ العنوان في دفتر العناوين
                                    </span>
                                </label>
                                <input type="text" name="address_label"
                                    placeholder="اسم العنوان (مثال: البيت، العمل)"
                                    :disabled="!useNewAddress && {{ $savedAddresses->isNotEmpty() ? 'true' : 'false' }}"
                                    class="border border-black p-2 text-[11px] bg-white focus:outline-none w-44 disabled:opacity-30 font-sans">
                            </div>
                            @endauth
                        </div>

                    </div>
                </div>

                {{-- ── STEP 2: PAYMENT ────────────────────────── --}}
                <div class="bg-white border-2 border-black shadow-[6px_6px_0_0_rgba(0,0,0,1)]">

                    <div class="atl-step-hdr">
                        <div class="flex items-center">
                            <span class="atl-step-badge">02</span>
                            <span class="atl-step-title">طريقة الدفع</span>
                        </div>
                        <span class="atl-step-num">Payment</span>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">

                            {{-- COD Option --}}
                            <label class="relative flex items-start gap-4 border-2 p-5 cursor-pointer transition-all duration-150"
                                :class="payMethod === 'cod' ? 'border-black bg-black text-white shadow-[4px_4px_0_0_rgba(0,0,0,1)] -translate-y-0.5' : 'border-black/20 hover:border-black bg-white shadow-[2px_2px_0_0_rgba(0,0,0,0.05)]'"
                                @click="payMethod = 'cod'">
                                <input type="radio" name="payment_method" value="cod"
                                    x-model="payMethod" class="sr-only">
                                <span class="text-2xl mt-0.5" :class="payMethod === 'cod' ? 'text-white' : 'text-black/60'">
                                    <svg class="w-6 h-6 inline-block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                                </span>
                                <div>
                                    <span class="font-editorial font-bold text-xs uppercase tracking-[0.15em] block mb-1">
                                        الدفع عند الاستلام
                                    </span>
                                    <span class="text-[10px] font-sans opacity-70 leading-normal block">فحص ومعاينة القطعة قبل الدفع للمندوب</span>
                                </div>
                                <div class="absolute top-3.5 right-3.5 w-4 h-4 border-2 flex items-center justify-center transition-all"
                                    :class="payMethod === 'cod' ? 'border-white bg-white' : 'border-black/30'">
                                    <svg x-show="payMethod === 'cod'" class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="square" d="M2 6l3 3 5-5"/>
                                    </svg>
                                </div>
                            </label>

                            {{-- Paymob Option --}}
                            <label class="relative flex items-start gap-4 border-2 p-5 cursor-pointer transition-all duration-150"
                                :class="payMethod === 'paymob' ? 'border-black bg-black text-white shadow-[4px_4px_0_0_rgba(0,0,0,1)] -translate-y-0.5' : 'border-black/20 hover:border-black bg-white shadow-[2px_2px_0_0_rgba(0,0,0,0.05)]'"
                                @click="payMethod = 'paymob'">
                                <input type="radio" name="payment_method" value="paymob"
                                    x-model="payMethod" class="sr-only">
                                <span class="text-2xl mt-0.5" :class="payMethod === 'paymob' ? 'text-white' : 'text-black/60'">
                                    <svg class="w-6 h-6 inline-block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                                </span>
                                <div>
                                    <span class="font-editorial font-bold text-xs uppercase tracking-[0.15em] block mb-1">
                                        Paymob / فيزا / ValU
                                    </span>
                                    <span class="text-[10px] font-sans opacity-70 leading-normal block">دفع إلكتروني فوري ومشفر 100%</span>
                                </div>
                                <div class="absolute top-3.5 right-3.5 w-4 h-4 border-2 flex items-center justify-center transition-all"
                                    :class="payMethod === 'paymob' ? 'border-white bg-white' : 'border-black/30'">
                                    <svg x-show="payMethod === 'paymob'" class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="square" d="M2 6l3 3 5-5"/>
                                    </svg>
                                </div>
                            </label>

                        </div>
                    </div>
                </div>

                {{-- Survey Widget --}}
                <div>
                    @include('partials.survey-widget', ['targetPage' => 'checkout'])
                </div>

            </div>

            {{-- ════════════════════════════════════════════════════
                 RIGHT COLUMN — Order Summary (2 cols)
            ════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-2">
                <div class="bg-white border-2 border-black shadow-[6px_6px_0_0_rgba(0,0,0,1)] sticky top-6">

                    <div class="flex items-center justify-between px-5 py-4 border-b-2 border-black bg-black text-white">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-white inline-block"></span>
                            <span class="font-editorial font-bold text-[11px] uppercase tracking-[0.2em] text-white">
                                ملخص الطلب
                            </span>
                        </div>
                        <span class="text-[9px] font-mono text-white/70 uppercase tracking-widest">
                            {{ isset($cartItems) ? count($cartItems) : 0 }} {{ (isset($cartItems) && count($cartItems) === 1) ? 'ITEM' : 'ITEMS' }}
                        </span>
                    </div>

                    <div class="p-5">

                        {{-- Cart Items --}}
                        @if(isset($cartItems) && count($cartItems))
                        <div class="space-y-4 mb-5 max-h-[340px] overflow-y-auto pr-1">
                            @foreach($cartItems as $item)
                            <div class="flex items-start gap-3 pb-3 border-b border-black/10 last:border-0 last:pb-0">
                                @if(isset($item['image']))
                                <div class="w-14 h-14 border-2 border-black/15 overflow-hidden shrink-0 bg-[#F5F5F0] p-1 flex items-center justify-center">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                        class="w-full h-full object-contain" decoding="async">
                                </div>
                                @else
                                <div class="w-14 h-14 border-2 border-black/15 bg-[#F5F5F0] flex items-center justify-center shrink-0">
                                    <span class="text-black/30 text-xl font-serif">◆</span>
                                </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-editorial font-bold text-xs uppercase tracking-tight text-black leading-snug">
                                        {{ $item['name'] ?? 'ATELIER Piece' }}
                                    </p>
                                    @if(!empty($item['variant']))
                                    <div class="text-[10px] font-editorial uppercase tracking-wider text-black/70 mt-1 flex items-center gap-1.5">
                                        @if(!empty($item['color_hex']))
                                            <span class="w-2.5 h-2.5 rounded-full border border-black/30 inline-block shrink-0" style="background-color: {{ $item['color_hex'] }};"></span>
                                        @endif
                                        <span class="truncate">{{ $item['variant'] }}</span>
                                    </div>
                                    @endif
                                    <p class="text-[10px] font-mono text-black/50 mt-0.5">الكمية: {{ $item['qty'] ?? 1 }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-editorial font-bold text-xs text-black">
                                        {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}
                                        <span class="text-[9px] font-normal text-black/60">EGP</span>
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        {{-- Elegant placeholder when cart is session-based --}}
                        <div class="flex items-center gap-3 py-3 mb-4 border-b border-black/10">
                            <div class="w-10 h-10 bg-[#F5F5F0] border-2 border-black/15 flex items-center justify-center shrink-0">
                                <span class="text-black/30 text-sm">◆</span>
                            </div>
                            <div>
                                <p class="font-editorial font-bold text-xs uppercase tracking-wider text-black">قطع ATELIER الفاخرة</p>
                                <p class="text-[10px] font-sans text-black/40 mt-0.5">المنتجات المختارة</p>
                            </div>
                        </div>
                        @endif

                        {{-- Totals --}}
                        <div class="border-t-2 border-black/10 pt-4 space-y-2.5">
                            @if(isset($subtotal))
                            <div class="flex justify-between text-xs font-sans text-black/70">
                                <span>المجموع الفرعي</span>
                                <span class="font-mono font-bold">{{ number_format($subtotal) }} EGP</span>
                            </div>
                            @endif
                            @if(isset($shippingCost))
                            <div class="flex justify-between text-xs font-sans text-black/70">
                                <span>الشحن</span>
                                <span class="font-mono font-bold">{{ $shippingCost == 0 ? 'مجاني' : number_format($shippingCost) . ' EGP' }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center border-t-2 border-black pt-3 mt-1.5">
                                <span class="font-editorial font-black text-sm uppercase tracking-wider text-black">الإجمالي الكلي</span>
                                <span class="font-display text-2xl text-black">
                                    {{ number_format(($total ?? $subtotal ?? 0)) }}
                                    <span class="font-editorial font-bold text-xs">EGP</span>
                                </span>
                            </div>
                        </div>

                        {{-- Submit CTA — uses unified .atl-btn-primary style --}}
                        <button type="submit" class="atl-btn-primary mt-6">
                            تأكيد وإرسال الطلب ←
                        </button>

                        {{-- Back link --}}
                        <div class="mt-4 text-center">
                            <a href="{{ route('home') }}"
                                class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/50 hover:text-black transition-colors">
                                ← العودة لمواصلة التسوق
                            </a>
                        </div>

                        {{-- Trust signals --}}
                        <div class="mt-5 pt-4 border-t border-black/10 flex items-center justify-center gap-5">
                            <div class="flex items-center gap-1.5 text-[9px] font-editorial font-bold uppercase tracking-wider text-black/50">
                                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg></span> <span>آمن ومشفر</span>
                            </div>
                            <div class="w-px h-3 bg-black/15"></div>
                            <div class="flex items-center gap-1.5 text-[9px] font-editorial font-bold uppercase tracking-wider text-black/50">
                                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></span> <span>تغليف فاخر</span>
                            </div>
                            <div class="w-px h-3 bg-black/15"></div>
                            <div class="flex items-center gap-1.5 text-[9px] font-editorial font-bold uppercase tracking-wider text-black/50">
                                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></span> <span>شحن سريع</span>
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

@push('scripts')
{{-- Any page-level checkout scripts if needed --}}
@endpush
