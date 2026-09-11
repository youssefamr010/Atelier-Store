{{-- Master Luxury Monochrome Footer --}}
@php
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

<footer class="bg-[#0b0b0e] text-white border-t-2 border-black/40 overflow-hidden relative" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    
    {{-- Subtle Top Ambient Glow --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-1 bg-gradient-to-r from-transparent via-amber-400/50 to-transparent"></div>

    {{-- 1. VIP Newsletter & Circle Strip --}}
    <div class="border-b border-white/10 py-12 px-4 sm:px-6 lg:px-12 bg-white/[0.02]">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="space-y-1.5 max-w-xl">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-300 font-mono text-[9px] uppercase tracking-widest">
                    ✦ {{ $isAr ? 'عضوية النخبة والخصومات الحصرية' : 'VIP Circle & Private Drops' }}
                </span>
                <h3 class="font-editorial font-black uppercase text-xl sm:text-2xl text-white tracking-normal">
                    {{ $isAr ? 'انضم إلى مجتمع ' . ($settings['storeName'] ?? 'ATELIER') : 'Join The ' . ($settings['storeName'] ?? 'ATELIER') . ' Circle' }}
                </h3>
                <p class="font-sans text-xs text-gray-400 leading-relaxed">
                    {{ $isAr 
                        ? 'احصل على إشعار مبكر بالإصدارات المحدودة، القطع الأرشيفية الخاصة، وخصم 10% على طلبك الأول.' 
                        : 'Receive early notifications on bespoke archive drops, secret collections, and 10% off your inaugural order.' }}
                </p>
            </div>
            
            <form action="#" method="POST" class="flex w-full lg:w-auto max-w-md gap-2" onsubmit="event.preventDefault(); alert('{{ $isAr ? 'تم اشتراكك بنجاح في مجتمع ATELIER!' : 'Subscribed successfully to ATELIER Circle.' }}');">
                <input 
                    type="email" 
                    placeholder="{{ $isAr ? 'أدخل بريدك الإلكتروني هنا...' : 'Enter your email address...' }}"
                    required
                    class="bg-white/5 border border-white/20 text-white placeholder-gray-500 text-xs px-4 py-3.5 flex-1 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white/10 transition-all font-sans min-h-[46px]"
                >
                <button type="submit" class="bg-gradient-to-r from-amber-400 to-amber-300 hover:from-amber-300 hover:to-white text-black font-editorial font-black uppercase text-xs tracking-widest px-6 py-3.5 rounded-xl transition-all shadow-md active:scale-95 shrink-0 min-h-[46px] cursor-pointer">
                    {{ $isAr ? 'اشتراك ←' : 'Subscribe →' }}
                </button>
            </form>
        </div>
    </div>

    {{-- 2. Four-Column Rich Navigation Grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12">
        
        {{-- Brand Column --}}
        <div class="space-y-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center font-editorial font-black text-amber-300 text-lg group-hover:scale-105 transition-transform shrink-0">
                    A
                </div>
                <div>
                    <span class="font-editorial font-black uppercase text-2xl tracking-normal text-white block leading-none">
                        {{ $settings['store_name'] ?? ($settings['storeName'] ?? 'ATELIER') }}
                    </span>
                    <span class="font-mono text-[9px] text-gray-400 tracking-widest uppercase">
                        STUDIO EGYPT · 2026
                    </span>
                </div>
            </a>
            <p class="font-sans text-xs text-gray-400 leading-relaxed">
                {{ $settings['footer_brand_tagline'] ?? ($isAr 
                    ? 'إكسسوارات فاخرة مصممة بدقة متناهية من الجلد الطبيعي والتيتانيوم المتين لأسلوب حياة راقٍ.' 
                    : 'Precision-engineered everyday accessories, luxury leather architecture, and titanium EDC crafted for modern motion.') }}
            </p>
            @php
                $waNum = $settings['social_whatsapp'] ?? '201000000000';
                $waMsg = $settings['social_whatsapp_msg'] ?? ($isAr ? 'مرحباً، أود الاستفسار عن منتجات Atelier' : 'Hello! I have an inquiry about Atelier products.');
                $igLink = $settings['social_instagram'] ?? 'https://www.instagram.com/atelier_store404?stkn=MTYybHFvbXNjazhnOA%3D%3D&utm_source=qr';
                $fbLink = $settings['social_facebook'] ?? 'https://www.facebook.com/share/1CWwWyeEQU/?mibextid=wwXIfr';
            @endphp
            <div class="flex items-center flex-wrap gap-2.5 pt-2">
                {{-- Instagram --}}
                <a href="{{ $igLink }}" target="_blank" rel="noopener noreferrer"
                   class="w-9 h-9 rounded-xl bg-white/5 hover:bg-gradient-to-tr hover:from-amber-500 hover:via-pink-500 hover:to-purple-600 border border-white/10 hover:border-transparent flex items-center justify-center text-gray-300 hover:text-white transition-all shadow-sm hover:scale-110 group" title="Instagram">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>

                {{-- Facebook --}}
                <a href="{{ $fbLink }}" target="_blank" rel="noopener noreferrer"
                   class="w-9 h-9 rounded-xl bg-white/5 hover:bg-[#1877F2] border border-white/10 hover:border-transparent flex items-center justify-center text-gray-300 hover:text-white transition-all shadow-sm hover:scale-110 group" title="Facebook">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>

                {{-- WhatsApp VIP Concierge --}}
                <a href="https://wa.me/{{ $waNum }}?text={{ urlencode($waMsg) }}" target="_blank" rel="noopener noreferrer"
                   class="w-9 h-9 rounded-xl bg-emerald-500/10 hover:bg-emerald-500 border border-emerald-500/20 hover:border-transparent flex items-center justify-center text-emerald-400 hover:text-white transition-all shadow-sm hover:scale-110 group" title="WhatsApp VIP Concierge">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Collections Column --}}
        <div class="space-y-3">
            <h4 class="font-editorial font-bold uppercase text-xs tracking-[0.2em] text-white border-b border-white/15 pb-2 flex items-center justify-between">
                <span>{{ $isAr ? 'أقسام التشكيلات' : 'Archive Collections' }}</span>
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            </h4>
            <ul class="space-y-2 text-xs font-sans text-gray-400">
                <li>
                    <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="hover:text-amber-300 transition-colors py-1 inline-flex items-center gap-1.5 font-bold text-white">
                        <span>✦</span>
                        <span>{{ $isAr ? 'كل التشكيلات المتاحة' : 'All Archive Pieces' }}</span>
                    </a>
                </li>
                @php $footerCols = \App\Models\Collection::where('status', 'active')->orderBy('sort_order')->take(5)->get(); @endphp
                @foreach($footerCols as $fCol)
                    <li>
                        <a href="{{ route('collections.show', ['slug' => $fCol->slug]) }}" class="hover:text-amber-300 transition-colors py-1 inline-block">
                            {{ $fCol->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Concierge & Care --}}
        <div class="space-y-3">
            <h4 class="font-editorial font-bold uppercase text-xs tracking-[0.2em] text-white border-b border-white/15 pb-2 flex items-center justify-between">
                <span>{{ $isAr ? 'خدمة العملاء والضمان' : 'Client Concierge' }}</span>
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
            </h4>
            <ul class="space-y-2 text-xs font-sans text-gray-400">
                <li>
                    <a href="{{ route('track.order') }}" class="hover:text-cyan-300 transition-colors py-1 inline-flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 stroke-[2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>{{ $isAr ? 'تتبع مسار الشحنة بالهاتف' : 'Track Active Shipment' }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('account') }}" class="hover:text-cyan-300 transition-colors py-1 inline-flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 stroke-[2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ $isAr ? 'حساب العميل والطلبات السابقة' : 'Client Profile & Orders' }}</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Trust & Payment Security --}}
        <div class="space-y-4">
            <h4 class="font-editorial font-bold uppercase text-xs tracking-[0.2em] text-white border-b border-white/15 pb-2 flex items-center justify-between">
                <span>{{ $isAr ? 'الدفع الآمن والضمان' : 'Verified Transactions' }}</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            </h4>
            <div class="space-y-2 text-xs font-sans text-gray-400 leading-relaxed">
                <p class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400 stroke-[2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $isAr ? 'الدفع عند الاستلام متاح بجميع المحافظات' : 'Cash on Delivery available nationwide in Egypt.' }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-1.5 pt-2 text-[10px] font-mono">
                <span class="px-2.5 py-1 rounded bg-amber-400/10 border border-amber-400/30 text-amber-300">COD</span>
            </div>
        </div>

    </div>

    {{-- 3. Bottom Copyright & Terms Bar --}}
    <div class="border-t border-white/10 py-6 px-4 sm:px-6 lg:px-12 text-[10px] font-sans text-gray-500 bg-black/40">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div>
                © {{ date('Y') }} {{ $settings['storeName'] ?? 'ATELIER' }}. {{ $isAr ? 'جميع الحقوق محفوظة. صُنع بعناية في القاهرة، مصر.' : 'All rights reserved. Handcrafted in Cairo, Egypt.' }}
            </div>
            <div class="flex items-center gap-4">
                {{-- Dynamic Pages from Admin can be injected here later --}}
            </div>
        </div>
    </div>

</footer>
