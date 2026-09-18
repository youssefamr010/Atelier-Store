@extends('layouts.app')

@php
    $isArCart = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $freeShippingThreshold = 1000; // 1,000 EGP for VIP Free Shipping
    $amountLeft = max(0, $freeShippingThreshold - ($subtotal ?? 0));
    $progressPercent = min(100, (($subtotal ?? 0) / $freeShippingThreshold) * 100);
@endphp

@section('title', ($isArCart ? 'حقيبة التسوق الفاخرة — ' : 'Luxury Shopping Bag — ') . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', $isArCart ? 'راجع منتجاتك المختارة وأتمم طلبك بأمان وفخامة.' : 'Review your curated luxury selections and proceed securely.')

@section('content')
<div class="bg-[#F8F7F3] min-h-screen py-8 sm:py-14">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 pb-5 border-b border-black/10">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-black/45 mb-1">
                {{ $isArCart ? 'المقتنيات المختارة' : 'Curated Bag' }}
            </p>
            <h1 class="font-display text-3xl sm:text-4xl font-extrabold uppercase tracking-tight text-black leading-none">
                {{ $isArCart ? 'حقيبة التسوق' : 'Shopping Bag' }}
            </h1>
        </div>
        @if(!empty($cartItems))
        <span class="text-xs font-bold text-black/60">
            {{ count($cartItems) }} {{ $isArCart ? 'قطع مميزة' : 'Bespoke Pieces' }}
        </span>
        @endif
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 mb-6 text-xs sm:text-sm font-sans text-emerald-900 flex items-center gap-3 shadow-sm">
        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">✓</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 mb-6 text-xs sm:text-sm font-sans text-rose-900 flex items-center gap-3 shadow-sm">
        <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">✕</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(empty($cartItems))
    {{-- ── EMPTY CART EXPERIENCE ── --}}
    <div class="rounded-3xl border border-black/10 bg-white p-12 sm:p-20 text-center shadow-sm max-w-2xl mx-auto">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-[#FAF9F5] border border-black/10 flex items-center justify-center text-3xl">
            👜
        </div>
        <h2 class="font-display font-bold text-xl sm:text-2xl uppercase tracking-tight text-black mb-3">
            {{ $isArCart ? 'حقيبة التسوق الخاصة بك فارغة حالياً' : 'Your Shopping Bag is Empty' }}
        </h2>
        <p class="font-sans text-xs sm:text-sm text-black/60 max-w-md mx-auto mb-8 leading-relaxed">
            {{ $isArCart ? 'استكشف تشكيلاتنا الحصرية من الإكسسوارات الفاخرة والمصنوعات الجلدية الراقية لإضافتها إلى حقيبتك.' : 'Explore our master-crafted luxury accessories, leather goods, and bespoke essentials.' }}
        </p>
        <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-black text-white font-display text-xs sm:text-sm font-bold uppercase tracking-[0.2em] px-8 py-4 hover:bg-neutral-800 transition-all shadow-md active:scale-95">
            {{ $isArCart ? 'استكشف المجموعة الكاملة ←' : 'Explore Collections →' }}
        </a>
    </div>

    @else
    {{-- ── FREE SHIPPING PROGRESS BAR ── --}}
    <div class="rounded-2xl border border-black/10 bg-white p-4 sm:p-5 mb-8 shadow-sm">
        <div class="flex items-center justify-between text-xs sm:text-sm font-bold mb-2.5">
            <div class="flex items-center gap-2">
                <span>🚚</span>
                @if($amountLeft > 0)
                <span class="text-black">
                    {{ $isArCart ? 'أضف منتجات بقيمة ' : 'Add ' }}
                    <span class="font-mono text-amber-600 font-extrabold">{{ number_format($amountLeft) }} {{ $isArCart ? 'ج.م' : 'EGP' }}</span>
                    {{ $isArCart ? ' إضافية للحصول على شحن مجاني فوري!' : ' more for FREE Express Delivery!' }}
                </span>
                @else
                <span class="text-emerald-700">
                    {{ $isArCart ? '🎉 مبروك! لقد حصلت على توصيل مجاني فوري لهذا الطلب.' : '🎉 Congratulations! You unlocked FREE Express Delivery.' }}
                </span>
                @endif
            </div>
            <span class="font-mono text-xs text-black/50">{{ number_format($progressPercent) }}%</span>
        </div>
        <div class="w-full h-2 rounded-full bg-black/5 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 {{ $amountLeft > 0 ? 'bg-black' : 'bg-emerald-600' }}" style="width: {{ $progressPercent }}%"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- ════ LEFT: CART ITEMS LIST (7 cols) ════ --}}
        <div class="lg:col-span-7 space-y-4">
            @foreach($cartItems as $key => $item)
            <div class="bg-white rounded-2xl border border-black/10 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4 transition-all hover:border-black/25">
                {{-- Product Image --}}
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl border border-black/10 bg-[#FAF9F5] shrink-0 overflow-hidden flex items-center justify-center p-1.5">
                    @if(!empty($item['image']))
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain" loading="eager" decoding="async">
                    @else
                    <span class="text-black/20 text-2xl">◆</span>
                    @endif
                </div>

                {{-- Product Info --}}
                <div class="flex-1 min-w-0">
                    <h3 class="font-display font-bold text-sm sm:text-base uppercase tracking-wide text-black truncate leading-snug">
                        {{ $item['name'] }}
                    </h3>
                    @if(!empty($item['variant']))
                    <div class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-black/5 px-2.5 py-0.5 text-[11px] text-black/70">
                        @if(!empty($item['color_hex']))
                        <span class="w-2.5 h-2.5 rounded-full border border-black/20 shrink-0" style="background: {{ $item['color_hex'] }}"></span>
                        @endif
                        <span class="truncate">{{ $item['variant'] }}</span>
                    </div>
                    @endif
                    <p class="font-bold text-xs sm:text-sm text-black mt-1.5">
                        {{ number_format($item['price']) }}
                        <span class="text-[10px] font-normal text-black/60">{{ $isArCart ? 'ج.م' : 'EGP' }}</span>
                    </p>

                    {{-- Quantity Controls for Mobile & Desktop --}}
                    <div class="flex items-center gap-3 mt-3">
                        <div class="inline-flex items-center rounded-xl border border-black/20 bg-[#FAF9F5] p-0.5 shadow-sm">
                            <form method="POST" action="{{ route('cart.update') }}" class="m-0 p-0">
                                @csrf
                                <input type="hidden" name="key" value="{{ $key }}">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm text-black hover:bg-black hover:text-white transition-colors" aria-label="Decrease quantity">−</button>
                            </form>
                            <span class="font-mono font-bold text-xs sm:text-sm text-black w-8 text-center">{{ $item['qty'] }}</span>
                            <form method="POST" action="{{ route('cart.update') }}" class="m-0 p-0">
                                @csrf
                                <input type="hidden" name="key" value="{{ $key }}">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm text-black hover:bg-black hover:text-white transition-colors" aria-label="Increase quantity">+</button>
                            </form>
                        </div>

                        {{-- Item Subtotal (Mobile view) --}}
                        <span class="font-bold text-xs sm:text-sm text-black sm:hidden ml-auto">
                            {{ number_format($item['price'] * $item['qty']) }} {{ $isArCart ? 'ج.م' : 'EGP' }}
                        </span>
                    </div>
                </div>

                {{-- Price & Remove (Desktop) --}}
                <div class="hidden sm:flex flex-col items-end justify-between shrink-0 self-stretch">
                    <form method="POST" action="{{ route('cart.remove') }}">
                        @csrf
                        <input type="hidden" name="key" value="{{ $key }}">
                        <button type="submit" title="{{ $isArCart ? 'حذف من السلة' : 'Remove' }}" class="w-7 h-7 rounded-full text-black/35 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-all text-xs font-bold">
                            ✕
                        </button>
                    </form>
                    <div class="text-right">
                        <p class="font-display font-extrabold text-sm sm:text-base text-black">
                            {{ number_format($item['price'] * $item['qty']) }}
                            <span class="text-[10px] font-normal text-black/60">{{ $isArCart ? 'ج.م' : 'EGP' }}</span>
                        </p>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Cart Footer Options --}}
            <div class="flex justify-between items-center pt-3 px-1">
                <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="text-xs font-bold text-black/60 hover:text-black transition-colors flex items-center gap-1">
                    <span>{{ $isArCart ? '← مواصلة التسوق' : '← Continue Shopping' }}</span>
                </a>
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    <button type="submit" onclick="return confirm('{{ $isArCart ? 'هل أنت متأكد من رغبتك في تفريغ سلة التسوق؟' : 'Are you sure you want to clear your shopping bag?' }}')" class="text-xs font-bold text-rose-600/80 hover:text-rose-700 transition-colors">
                        {{ $isArCart ? 'تفريغ السلة' : 'Clear Bag' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- ════ RIGHT: ORDER SUMMARY (5 cols) ════ --}}
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl border border-black/10 p-5 sm:p-7 shadow-sm sticky top-6">
                
                <h3 class="font-display text-base sm:text-lg font-bold uppercase tracking-wide text-black pb-4 mb-5 border-b border-black/10">
                    {{ $isArCart ? 'ملخص الحساب' : 'Summary' }}
                </h3>

                {{-- Cost Details --}}
                <div class="space-y-3 text-xs sm:text-sm text-black/70">
                    <div class="flex justify-between items-center">
                        <span>{{ $isArCart ? 'المجموع الفرعي' : 'Subtotal' }}</span>
                        <span class="font-bold text-black">{{ number_format($subtotal) }} {{ $isArCart ? 'ج.م' : 'EGP' }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span>{{ $isArCart ? 'الشحن والتوصيل' : 'Estimated Shipping' }}</span>
                        <span class="text-xs text-black/50">{{ $isArCart ? 'يُحسب عند الدفع' : 'Calculated at checkout' }}</span>
                    </div>
                </div>

                {{-- Estimated Total --}}
                <div class="flex justify-between items-center border-t border-black/10 pt-4 mt-4 text-black">
                    <span class="font-display font-extrabold text-base uppercase tracking-wider">{{ $isArCart ? 'الإجمالي التقديري' : 'Estimated Total' }}</span>
                    <div class="text-right">
                        <span class="font-display text-2xl sm:text-3xl font-black">{{ number_format($subtotal) }}</span>
                        <span class="font-bold text-xs ml-1 text-black/70">{{ $isArCart ? 'ج.م' : 'EGP' }}</span>
                    </div>
                </div>

                {{-- Go to Checkout CTA --}}
                <a href="{{ route('checkout') }}" class="block w-full mt-6 rounded-xl bg-black text-white text-center font-display text-xs sm:text-sm font-bold uppercase tracking-[0.2em] py-4 px-6 hover:bg-neutral-800 transition-all shadow-md active:scale-[0.99]">
                    {{ $isArCart ? 'متابعة إتمام الطلب ←' : 'Proceed to Checkout →' }}
                </a>

                {{-- Trust Pillars --}}
                <div class="mt-6 pt-5 border-t border-black/10 grid grid-cols-3 gap-2 text-center">
                    <div class="flex flex-col items-center gap-1">
                        <span class="text-base">🔒</span>
                        <span class="text-[10px] font-bold text-black/60">{{ $isArCart ? 'دفع مشفر' : 'SSL Encrypted' }}</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 border-x border-black/10">
                        <span class="text-base">🎁</span>
                        <span class="text-[10px] font-bold text-black/60">{{ $isArCart ? 'تغليف ملكي' : 'Hand-Wrapped' }}</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <span class="text-base">⚡</span>
                        <span class="text-[10px] font-bold text-black/60">{{ $isArCart ? 'شحن فوري' : 'Fast Dispatch' }}</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
    @endif

</div>
</div>
@endsection
