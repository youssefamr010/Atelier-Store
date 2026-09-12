@php
    $storyEnabled = ($settings['homepage_story_enabled'] ?? '1') === '1';
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';

    $storySub = $settings['homepage_story_subtitle'] ?? ($isAr ? 'حرفية وفخامة بلا مساومة' : 'UNCOMPROMISING ARTISANAL HERITAGE');
    $storyTitle = $settings['homepage_story_title'] ?? ($isAr ? 'عن Atelier — فلسفة التصميم والأناقة الخالدة' : 'The Atelier Standard: Mastercrafted Elegance');
    $storyBody = $settings['homepage_story_body'] ?? ($isAr 
        ? 'انطلقت Atelier برؤية لإعادة تعريف الديكورات والإكسسوارات الفاخرة، حيث تلتقي أجود الخامات العالمية بأعلى مستويات الحرفية والتفصيل الدقيق. كل قطعة في تشكيلاتنا صُممت بعناية فائقة لتمنح مساحتك حضوراً فريداً وأناقة تدوم طويلاً.' 
        : 'Born from a devotion to minimalist geometry and timeless aesthetic permanence, Atelier crafts bespoke interior pieces and lifestyle decor using hand-selected materials and meticulous artisan standards. Every piece is an artifact of precision, tailored for those who appreciate quiet luxury.');
    
    $storyImg = $settings['homepage_story_image'] ?? 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1000&q=85';
@endphp

@if($storyEnabled)
<section class="w-full bg-[#121212] text-white py-16 lg:py-24 border-t-2 border-b-2 border-black relative overflow-hidden" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <!-- Subtle Background Monogram Silhouette -->
    <div class="absolute -right-16 -bottom-16 text-white/[0.03] font-display text-[22rem] select-none pointer-events-none leading-none">
        A
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-14 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
            
            <!-- Left: Text Editorial -->
            <div class="lg:col-span-7 space-y-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-amber-500">
                        <span class="w-2 h-2 bg-amber-500 inline-block"></span>
                        <span class="font-editorial font-bold text-xs uppercase tracking-[0.25em] text-amber-400">
                            {{ $storySub }}
                        </span>
                    </div>
                    
                    <h2 class="font-display text-fluid-title text-white leading-tight uppercase" style="font-size: clamp(1.6rem, 4.5vw, 3rem);">
                        {{ $storyTitle }}
                    </h2>
                </div>

                <div class="space-y-4 font-sans text-xs sm:text-sm text-white/80 leading-relaxed max-w-xl">
                    <p class="whitespace-pre-line">{{ $storyBody }}</p>
                </div>

                <!-- Heritage Credentials -->
                <div class="grid grid-cols-3 gap-4 pt-4 border-t border-white/10">
                    <div>
                        <span class="font-mono font-black text-xl sm:text-2xl text-white block">100%</span>
                        <span class="text-[9px] font-editorial uppercase tracking-wider text-white/50 block mt-0.5">{{ $isAr ? 'حرفية وتفاصيل متقنة' : 'Artisanal Craft' }}</span>
                    </div>
                    <div>
                        <span class="font-mono font-black text-xl sm:text-2xl text-white block">2 YRS</span>
                        <span class="text-[9px] font-editorial uppercase tracking-wider text-white/50 block mt-0.5">{{ $isAr ? 'ضمان شامل ومباشر' : 'Bespoke Warranty' }}</span>
                    </div>
                    <div>
                        <span class="font-mono font-black text-xl sm:text-2xl text-white block">24H</span>
                        <span class="text-[9px] font-editorial uppercase tracking-wider text-white/50 block mt-0.5">{{ $isAr ? 'تجهيز سريع للشحن' : 'Express Dispatch' }}</span>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('collections.show', ['slug' => 'all']) }}" 
                       class="inline-flex items-center gap-2 bg-white text-black hover:bg-neutral-200 px-7 py-3.5 text-xs font-editorial font-bold uppercase tracking-wider transition-all shadow-[4px_4px_0px_0px_rgba(255,255,255,0.2)]">
                        <span>{{ $isAr ? 'استكشف التشكيلة الكاملة' : 'Explore The Collection' }}</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <!-- Right: Heritage Craft Imagery -->
            <div class="lg:col-span-5">
                <div class="relative">
                    <div class="border-2 border-white/20 p-2 bg-white/5 shadow-[8px_8px_0px_0px_rgba(255,255,255,0.1)]">
                        <img 
                            src="{{ $storyImg }}" 
                            alt="Atelier Heritage Craftsmanship" 
                            class="w-full aspect-4/3 sm:aspect-square object-cover"
                            loading="lazy"
                        >
                    </div>
                    
                    <!-- Floating Stamp Badge -->
                    <div class="absolute -bottom-4 -left-4 bg-black border-2 border-amber-500/60 text-white p-3 shadow-lg hidden sm:flex items-center gap-3">
                        <x-icon name="shield" class="w-6 h-6 text-amber-400 shrink-0" />
                        <div>
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-wider text-white block leading-none">{{ $isAr ? 'معايير الجودة الممتازة' : 'ATELIER CERTIFIED' }}</span>
                            <span class="text-[8px] font-mono text-white/60 block mt-0.5">{{ $isAr ? 'خامات مختارة بعناية' : 'Hand-Finished in Egypt' }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endif
