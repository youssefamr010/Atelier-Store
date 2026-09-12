{{-- Luxury Trust & Guarantee Strip (Ultra-Compact 1-Row Design) --}}
@php
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

<section class="bg-black text-white py-3 sm:py-4 border-t-2 border-b-2 border-black" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">

            {{-- 1. Free Fast Delivery --}}
            <div class="p-2.5 bg-white/[0.04] border border-white/15 flex items-center gap-2.5">
                <div class="w-7 h-7 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h4 class="font-editorial font-bold text-[10px] sm:text-[11px] uppercase tracking-wider text-white truncate">
                        {{ $isAr ? 'شحن سريع ومجاني' : 'Express Delivery' }}
                    </h4>
                    <p class="font-sans text-[8.5px] sm:text-[9px] text-white/50 truncate">
                        {{ $isAr ? 'لجميع المحافظات' : 'Door-to-door express' }}
                    </p>
                </div>
            </div>

            {{-- 2. Delivery in 3-5 Days --}}
            <div class="p-2.5 bg-white/[0.04] border border-white/15 flex items-center gap-2.5">
                <div class="w-7 h-7 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h4 class="font-editorial font-bold text-[10px] sm:text-[11px] uppercase tracking-wider text-white truncate">
                        {{ $isAr ? 'وصول خلال 3-5 أيام' : 'Ships in 3–5 Days' }}
                    </h4>
                    <p class="font-sans text-[8.5px] sm:text-[9px] text-white/50 truncate">
                        {{ $isAr ? 'تتبع فوري ومباشر' : 'Live tracking updates' }}
                    </p>
                </div>
            </div>

            {{-- 3. Genuine Leather & Guarantee --}}
            <div class="p-2.5 bg-white/[0.04] border border-white/15 flex items-center gap-2.5">
                <div class="w-7 h-7 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h4 class="font-editorial font-bold text-[10px] sm:text-[11px] uppercase tracking-wider text-white truncate">
                        {{ $isAr ? 'ضمان جودة سنتين' : '2-Year Guarantee' }}
                    </h4>
                    <p class="font-sans text-[8.5px] sm:text-[9px] text-white/50 truncate">
                        {{ $isAr ? 'جلد طبيعي 100%' : '100% Full-grain craft' }}
                    </p>
                </div>
            </div>

            {{-- 4. Cash on Delivery --}}
            <div class="p-2.5 bg-white/[0.04] border border-white/15 flex items-center gap-2.5">
                <div class="w-7 h-7 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h4 class="font-editorial font-bold text-[10px] sm:text-[11px] uppercase tracking-wider text-white truncate">
                        {{ $isAr ? 'الدفع عند المعاينة' : 'Cash on Delivery' }}
                    </h4>
                    <p class="font-sans text-[8.5px] sm:text-[9px] text-white/50 truncate">
                        {{ $isAr ? 'عاين قبل الاستلام' : 'Physical inspection' }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
