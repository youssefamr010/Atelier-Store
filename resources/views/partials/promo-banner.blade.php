{{-- Top Promotional Banner (Smart Adaptive Dimensions, Zero-Crop Natural Fit & Luxury Dynamic Rendering) --}}
@php
    $isArabicStore = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $bannerImg = $settings['homepage_banner_image'] ?? '';
    $bannerVideo = $settings['homepage_banner_video'] ?? '';
    $bannerTitle = $settings['homepage_banner_title'] ?? '';
    $bannerSubtitle = $settings['homepage_banner_subtitle'] ?? '';
    $bannerLink = $settings['homepage_banner_link'] ?? '';
    
    $bannerImgUrl = $bannerImg ? (str_starts_with($bannerImg, 'http') ? $bannerImg : url($bannerImg)) : '';
    $bannerVideoUrl = $bannerVideo ? (str_starts_with($bannerVideo, 'http') ? $bannerVideo : url($bannerVideo)) : '';
    $hasText = !empty($bannerTitle) || !empty($bannerSubtitle);
@endphp

@if(!empty($bannerImgUrl) || !empty($bannerVideoUrl))
<section class="relative w-full overflow-hidden border-b border-black bg-[#F5F5F0] reveal-on-scroll group select-none">
    @if(!empty($bannerLink))
        <a href="{{ str_starts_with($bannerLink, 'http') ? $bannerLink : url($bannerLink) }}" class="block relative w-full h-full cursor-pointer">
    @endif

        {{-- Main Media Container: Fluid Natural Height --}}
        <div class="relative w-full flex items-center justify-center">
            @if(!empty($bannerVideoUrl))
                <video autoplay muted loop playsinline preload="metadata" poster="{{ $bannerImgUrl }}"
                    class="w-full h-auto max-h-[85vh] object-cover">
                    <source src="{{ $bannerVideoUrl }}">
                </video>
            @else
                <img 
                    src="{{ $bannerImgUrl }}" 
                    alt="{{ $bannerTitle ?: 'Promotional Banner' }}"
                    class="w-full h-auto max-h-[85vh] object-contain sm:object-cover transition-transform duration-700 ease-out group-hover:scale-[1.005]"
                    loading="eager"
                    fetchpriority="high"
                >
            @endif

            {{-- Floating Content Overlay only if Title/Subtitle are set --}}
            @if($hasText)
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent flex flex-col justify-end p-4 sm:p-8 lg:p-12 space-y-2">
                    @if(!empty($bannerSubtitle))
                        <div class="inline-flex items-center gap-2 border border-white/40 px-2 py-0.5 bg-black/70 w-fit backdrop-blur-sm">
                            <span class="w-1.5 h-1.5 bg-white"></span>
                            <span class="font-editorial font-bold text-[9px] uppercase tracking-[0.2em] text-white">
                                {{ $bannerSubtitle }}
                            </span>
                        </div>
                    @endif

                    @if(!empty($bannerTitle))
                        <h2 class="font-editorial font-black text-xl sm:text-3xl lg:text-5xl uppercase tracking-normal text-white leading-tight max-w-3xl drop-shadow-md">
                            {{ $bannerTitle }}
                        </h2>
                    @endif
                </div>
            @endif
        </div>

        {{-- Minimalist Corner Sub-Strip: smaller, positioned cleanly below image with zero artwork overlap --}}
        @if(!empty($bannerLink))
            <div class="w-full bg-[#161616] border-t border-black/20 px-4 sm:px-6 py-1.5 flex items-center justify-between text-white group-hover:bg-black transition-colors duration-200">
                <span class="inline-flex items-center gap-2 text-white/50 text-[9px] font-mono tracking-widest uppercase">
                    <span class="w-1.5 h-1.5 bg-emerald-400 inline-block"></span>
                    <span>{{ $settings['storeName'] ?? 'ATELIER' }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-[9px] sm:text-[10px] font-editorial font-bold uppercase tracking-[0.2em] text-white group-hover:text-amber-300 transition-colors">
                    <span>{{ $isArabicStore ? 'تصفح التشكيلة الآن' : 'Explore Collection' }}</span>
                    <span class="font-mono text-xs">→</span>
                </span>
            </div>
        @endif

    @if(!empty($bannerLink))
        </a>
    @endif
</section>
@endif

