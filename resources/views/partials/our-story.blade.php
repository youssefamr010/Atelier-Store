@php
    $storyEnabled = ($settings['homepage_story_enabled'] ?? '1') === '1';
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';

    $storySub = $settings['homepage_story_subtitle'] ?? ($isAr ? 'حرفية وفخامة بلا مساومة' : 'UNCOMPROMISING ARTISANAL HERITAGE');
    $storyTitle = $settings['homepage_story_title'] ?? ($isAr ? 'فلسفة التصميم والأناقة الخالدة' : 'The Atelier Standard: Mastercrafted Elegance');
    $storyBody = $settings['homepage_story_body'] ?? ($isAr 
        ? 'انطلقت Atelier برؤية لإعادة تعريف الإكسسوارات الفاخرة والديكور، حيث تلتقي أجود الخامات بأعلى مستويات الحرفية والتفصيل الدقيق.' 
        : 'Devoted to minimalist geometry and timeless permanency, Atelier crafts bespoke pieces using hand-selected materials and artisan standards.');
    
    $storyImg = $settings['homepage_story_image'] ?? 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=600&q=80';
@endphp

@if($storyEnabled)
<section class="w-full bg-[#121212] text-white py-6 sm:py-8 border-t-2 border-b-2 border-black relative overflow-hidden" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- Left: Text Editorial (Compact) -->
            <div class="lg:col-span-8 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-amber-400 inline-block"></span>
                    <span class="font-editorial font-bold text-[9px] uppercase tracking-[0.25em] text-amber-400">
                        {{ $storySub }}
                    </span>
                </div>
                
                <h3 class="font-editorial font-black text-lg sm:text-xl lg:text-2xl text-white uppercase leading-tight">
                    {{ $storyTitle }}
                </h3>

                <p class="font-sans text-xs text-white/70 leading-relaxed max-w-2xl line-clamp-3">
                    {{ $storyBody }}
                </p>

                <!-- Compact Badges & CTA -->
                <div class="flex flex-wrap items-center gap-4 pt-1">
                    <div class="flex items-center gap-3 text-[10px] font-mono text-white/60">
                        <span class="text-white font-bold">100% ARTISANAL</span>
                        <span>·</span>
                        <span class="text-white font-bold">2 YRS WARRANTY</span>
                        <span>·</span>
                        <span class="text-white font-bold">EXPRESS 24H</span>
                    </div>
                    <a href="{{ route('collections.show', ['slug' => 'all']) }}" 
                       class="inline-flex items-center gap-1.5 bg-white text-black hover:bg-neutral-200 px-3.5 py-1.5 text-[10px] font-editorial font-bold uppercase tracking-wider transition-all shadow-[2px_2px_0px_0px_rgba(255,255,255,0.3)]">
                        <span>{{ $isAr ? 'استكشف التشكيلة' : 'Explore Archive' }}</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <!-- Right: Compact Craft Imagery -->
            <div class="lg:col-span-4 hidden lg:block">
                <div class="border-2 border-white/20 p-1.5 bg-white/5 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.1)] max-w-[260px] mx-auto">
                    <img 
                        src="{{ $storyImg }}" 
                        alt="Atelier Craftsmanship" 
                        class="w-full h-28 object-cover"
                        loading="lazy"
                    >
                </div>
            </div>

        </div>
    </div>
</section>
@endif
