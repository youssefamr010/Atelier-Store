@php
    $storyEnabled = ($settings['homepage_story_enabled'] ?? '1') === '1';
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';

    $storySub = $settings['homepage_story_subtitle'] ?? ($isAr ? 'حرفية وفخامة بلا مساومة' : 'UNCOMPROMISING ARTISANAL HERITAGE');
    $storyTitle = $settings['homepage_story_title'] ?? ($isAr ? 'فلسفة التصميم والأناقة الخالدة' : 'The Atelier Standard');
    $storyBody = $settings['homepage_story_body'] ?? ($isAr 
        ? 'إكسسوارات فاخرة مصممة بأعلى معايير الحرفية والتفصيل الدقيق.' 
        : 'Devoted to minimalist geometry and timeless aesthetic permanence.');
@endphp

@if($storyEnabled)
<section class="w-full bg-[#111111] text-white py-3 sm:py-3.5 border-t border-b border-black/40" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-3 text-center md:text-left">
            
            <!-- Left: Headline & Tagline -->
            <div class="flex items-center gap-2.5 flex-wrap justify-center md:justify-start">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block shrink-0"></span>
                <span class="font-editorial font-bold text-xs uppercase tracking-wider text-white">
                    {{ $storyTitle }}:
                </span>
                <span class="font-sans text-[11px] text-white/60">
                    {{ $storyBody }}
                </span>
            </div>

            <!-- Right: Micro Badges + Link -->
            <div class="flex items-center gap-3 shrink-0 text-[9.5px] font-mono text-white/60">
                <span class="text-amber-300 font-bold">100% ARTISANAL</span>
                <span>·</span>
                <span>2 YRS WARRANTY</span>
                <a href="{{ route('collections.show', ['slug' => 'all']) }}" 
                   class="inline-flex items-center gap-1 bg-white text-black hover:bg-amber-300 px-2.5 py-1 text-[9px] font-editorial font-bold uppercase tracking-wider transition-colors">
                    <span>{{ $isAr ? 'استكشف' : 'Explore' }}</span>
                    <span>→</span>
                </a>
            </div>

        </div>
    </div>
</section>
@endif
