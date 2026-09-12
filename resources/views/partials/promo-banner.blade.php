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
                    class="w-full h-auto max-h-[85vh] object-cover sm:object-contain transition-transform duration-700 ease-out group-hover:scale-[1.01]"
                    loading="eager"
                    fetchpriority="high"
                >
            @endif

            {{-- Floating Content / CTA Pill --}}
            @if($hasText)
                {{-- Rich Text Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent flex flex-col justify-end p-4 sm:p-8 lg:p-12 space-y-2">
                    @if(!empty($bannerSubtitle))
                        <div class="inline-flex items-center gap-2 border border-white/40 px-2.5 py-0.5 bg-black/70 w-fit backdrop-blur-sm">
                            <span class="w-1.5 h-1.5 bg-white"></span>
                            <span class="font-editorial font-bold text-[9px] sm:text-[10px] uppercase tracking-[0.2em] text-white">
                                {{ $bannerSubtitle }}
                            </span>
                        </div>
                    @endif

                    @if(!empty($bannerTitle))
                        <h2 class="font-editorial font-black text-xl sm:text-3xl lg:text-5xl uppercase tracking-normal text-white leading-tight max-w-3xl drop-shadow-md">
                            {{ $bannerTitle }}
                        </h2>
                    @endif

                    @if(!empty($bannerLink))
                        <div class="pt-1 sm:pt-2">
                            <span class="inline-flex items-center gap-2 font-editorial font-bold text-[11px] sm:text-xs uppercase tracking-[0.15em] text-white group-hover:underline underline-offset-4 drop-shadow">
                                <span>{{ $isArabicStore ? 'تسوق التشكيلة الآن ←' : 'Explore Collection Now →' }}</span>
                            </span>
                        </div>
                    @endif
                </div>
            @elseif(!empty($bannerLink))
                {{-- Minimalist Floating Luxury Badge (Zero image obstruction) --}}
                <div class="absolute bottom-3 left-3 sm:bottom-5 sm:left-5 z-10">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-black/85 text-white font-editorial font-bold text-[10px] sm:text-xs uppercase tracking-[0.15em] backdrop-blur-sm border border-white/20 shadow-lg group-hover:bg-black group-hover:scale-105 transition-all duration-200">
                        <span>{{ $isArabicStore ? 'تسوق التشكيلة الآن ←' : 'Explore Collection Now →' }}</span>
                    </span>
                </div>
            @endif
        </div>

    @if(!empty($bannerLink))
        </a>
    @endif
</section>
@endif

