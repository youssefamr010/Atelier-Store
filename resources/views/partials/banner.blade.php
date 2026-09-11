{{-- Luxury Trust & Guarantee Strip (Monochrome Bespoke Design) --}}
@php
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

<section class="bg-black text-white py-10 border-t-2 border-b-2 border-black" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-14">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- 1. Free Fast Delivery --}}
            <div class="p-5 bg-white/[0.04] border border-white/15 hover:border-white/40 transition-all flex items-start gap-3.5 group">
                <div class="w-10 h-10 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-editorial font-bold text-xs uppercase tracking-wider text-white group-hover:underline transition-colors">
                        {{ $isAr ? 'شحن سريع ومجاني' : 'Express Delivery' }}
                    </h4>
                    <p class="font-sans text-[10px] text-white/60 mt-1 leading-normal">
                        {{ $isAr ? 'توصيل مجاني للطلبات المؤهلة' : 'Free delivery on qualifying orders' }}
                    </p>
                </div>
            </div>

            {{-- 2. Delivery in 3-5 Days --}}
            <div class="p-5 bg-white/[0.04] border border-white/15 hover:border-white/40 transition-all flex items-start gap-3.5 group">
                <div class="w-10 h-10 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-editorial font-bold text-xs uppercase tracking-wider text-white group-hover:underline transition-colors">
                        {{ $isAr ? 'وصول خلال 3-5 أيام' : 'Ships in 3–5 Days' }}
                    </h4>
                    <p class="font-sans text-[10px] text-white/60 mt-1 leading-normal">
                        {{ $isAr ? 'تغطية شاملة لجميع محافظات مصر' : 'Available across all governorates' }}
                    </p>
                </div>
            </div>

            {{-- 3. Genuine Leather & Guarantee --}}
            <div class="p-5 bg-white/[0.04] border border-white/15 hover:border-white/40 transition-all flex items-start gap-3.5 group">
                <div class="w-10 h-10 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-editorial font-bold text-xs uppercase tracking-wider text-white group-hover:underline transition-colors">
                        {{ $isAr ? 'ضمان جودة سنتين' : '2-Year Guarantee' }}
                    </h4>
                    <p class="font-sans text-[10px] text-white/60 mt-1 leading-normal">
                        {{ $isAr ? 'صيانة واستبدال فوري عند الحاجة' : 'Full repair or replacement pledge' }}
                    </p>
                </div>
            </div>

            {{-- 4. Cash on Delivery --}}
            <div class="p-5 bg-white/[0.04] border border-white/15 hover:border-white/40 transition-all flex items-start gap-3.5 group">
                <div class="w-10 h-10 border border-white/20 bg-white/10 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-editorial font-bold text-xs uppercase tracking-wider text-white group-hover:underline transition-colors">
                        {{ $isAr ? 'الدفع عند المعاينة' : 'Cash on Delivery' }}
                    </h4>
                    <p class="font-sans text-[10px] text-white/60 mt-1 leading-normal">
                        {{ $isAr ? 'عاين وافحص منتجك قبل السداد' : 'Pay after complete physical inspection' }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

