{{-- Top Promotional Banner (Smart Adaptive Dimensions, Zero-Crop Auto-Fit & Luxury Ambient Fill) --}}
@php
    $isArabicStore = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $bannerImg = $settings['homepage_banner_image'] ?? '';
    $bannerVideo = $settings['homepage_banner_video'] ?? '';
    $bannerTitle = $settings['homepage_banner_title'] ?? '';
    $bannerSubtitle = $settings['homepage_banner_subtitle'] ?? '';
    $bannerLink = $settings['homepage_banner_link'] ?? '';
    
    if (empty($bannerImg) && (!empty($bannerTitle) || !empty($bannerSubtitle))) {
        $bannerImg = 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=1800&q=85';
    }
    
    $bannerImgUrl = $bannerImg ? (str_starts_with($bannerImg, 'http') ? $bannerImg : url($bannerImg)) : '';
    $bannerVideoUrl = $bannerVideo ? (str_starts_with($bannerVideo, 'http') ? $bannerVideo : url($bannerVideo)) : '';

    $bannerHeightSetting = $settings['homepage_banner_height'] ?? 'auto';
    $bannerPosition = $settings['homepage_banner_position'] ?? 'center center';
    $bannerFit = $settings['homepage_banner_fit'] ?? 'contain'; // Smart default to preserve entire image
    $bannerOverlay = $settings['homepage_banner_overlay'] ?? 'medium';

    $overlayClasses = [
        'none'   => 'bg-transparent',
        'light'  => 'bg-black/20',
        'medium' => 'bg-gradient-to-t from-black/85 via-black/30 to-black/10',
        'dark'   => 'bg-gradient-to-t from-black via-black/60 to-black/35',
    ];
    $overlayClass = $overlayClasses[$bannerOverlay] ?? $overlayClasses['medium'];
@endphp

@if(!empty($bannerImgUrl) || !empty($bannerVideoUrl))
<section class="relative w-full overflow-hidden border-b border-black bg-neutral-950 text-white reveal-on-scroll group select-none">
    @if(!empty($bannerLink))
        <a href="{{ str_starts_with($bannerLink, 'http') ? $bannerLink : url($bannerLink) }}" class="block relative w-full h-full">
    @endif

        {{-- 1. Ambient Background Layer (Blurred backdrop to gracefully fill any aspect ratio gaps without dark voids) --}}
        @if(!empty($bannerImgUrl))
            <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
                <img src="{{ $bannerImgUrl }}" alt="" class="w-full h-full object-cover blur-2xl scale-125 opacity-35 transition-opacity duration-700">
            </div>
        @endif

        {{-- 2. Main Media Container (Adapts smartly to image proportion on mobile & desktop) --}}
        <div class="relative w-full flex items-center justify-center min-h-[200px] sm:min-h-[300px] lg:min-h-[420px] max-h-[80vh]">
            @if(!empty($bannerVideoUrl))
                <video autoplay muted loop playsinline preload="metadata" poster="{{ $bannerImgUrl }}"
                    class="w-full h-full object-cover max-h-[80vh]"
                    style="object-position: {{ $bannerPosition }};">
                    <source src="{{ $bannerVideoUrl }}">
                </video>
            @else
                <img 
                    src="{{ $bannerImgUrl }}" 
                    alt="{{ $bannerTitle ?: 'Promotional Banner' }}"
                    class="w-full h-auto max-h-[80vh] object-contain sm:object-cover hero-slow-zoom transition-all duration-500"
                    style="object-position: {{ $bannerPosition }};"
                    loading="eager"
                    decoding="async"
                >
            @endif

            {{-- Dynamic Readability Overlay (if title or link is present) --}}
            @if($bannerOverlay !== 'none' && (!empty($bannerTitle) || !empty($bannerSubtitle) || !empty($bannerLink)))
                <div class="absolute inset-0 {{ $overlayClass }} pointer-events-none"></div>
            @endif

            {{-- Floating Content (Title, Subtitle, Shop CTA) --}}
            @if(!empty($bannerTitle) || !empty($bannerSubtitle) || !empty($bannerLink))
                <div class="absolute inset-x-0 bottom-0 z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 pb-6 sm:pb-10 flex flex-col justify-end space-y-2 sm:space-y-3">
                    @if(!empty($bannerSubtitle))
                        <div class="inline-flex items-center gap-2 border border-white/40 px-2.5 py-0.5 bg-black/60 w-fit backdrop-blur-sm rounded-sm">
                            <span class="w-1.5 h-1.5 bg-white"></span>
                            <span class="font-editorial font-bold text-[9px] sm:text-[10px] uppercase tracking-[0.2em] text-white">
                                {{ $bannerSubtitle }}
                            </span>
                        </div>
                    @endif

                    @if(!empty($bannerTitle))
                        <h2 class="font-editorial font-black text-xl sm:text-3xl lg:text-5xl uppercase tracking-normal text-white leading-tight max-w-3xl drop-shadow-lg">
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
            @endif
        </div>

    @if(!empty($bannerLink))
        </a>
    @endif
</section>
@endif
