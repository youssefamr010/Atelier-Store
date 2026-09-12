{{-- Top Promotional Banner (Smart Responsive Dimensions & Ambient Fill) --}}
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
    $heightMap = [
        'auto'      => 'h-auto aspect-[16/9] sm:aspect-[21/9] lg:aspect-[3/1]',
        'natural'   => 'h-auto aspect-[16/9] sm:aspect-[21/9]',
        'panoramic' => 'h-auto aspect-[2/1] sm:aspect-[21/9] lg:aspect-[3/1]',
        'cinematic' => 'h-auto aspect-[16/9] sm:aspect-[16/9]',
        'compact'   => 'h-[260px] sm:h-[320px] lg:h-[380px]',
        'medium'    => 'h-[340px] sm:h-[420px] lg:h-[480px]',
        'large'     => 'h-[440px] sm:h-[540px] lg:h-[620px]',
        'full'      => 'h-[70vh] sm:h-[80vh] lg:h-[88vh]',
    ];
    $heightClass = $heightMap[$bannerHeightSetting] ?? (str_ends_with($bannerHeightSetting, 'vh') || str_ends_with($bannerHeightSetting, 'px') ? '' : 'h-auto aspect-[16/9] sm:aspect-[21/9]');
    $customHeightStyle = (!isset($heightMap[$bannerHeightSetting]) && (str_ends_with($bannerHeightSetting, 'vh') || str_ends_with($bannerHeightSetting, 'px') || str_ends_with($bannerHeightSetting, '%'))) 
        ? "height: {$bannerHeightSetting};" 
        : '';

    $bannerPosition = $settings['homepage_banner_position'] ?? 'center center';
    $bannerFit = $settings['homepage_banner_fit'] ?? 'cover';
    $bannerZoom = (float)($settings['homepage_banner_zoom'] ?? 100) / 100;
    if ($bannerZoom < 0.5) $bannerZoom = 1;
    $bannerOverlay = $settings['homepage_banner_overlay'] ?? 'medium';

    $overlayClasses = [
        'none'   => 'bg-transparent',
        'light'  => 'bg-black/20',
        'medium' => 'bg-gradient-to-t from-black/80 via-black/35 to-transparent',
        'dark'   => 'bg-gradient-to-t from-black via-black/60 to-black/30',
    ];
    $overlayClass = $overlayClasses[$bannerOverlay] ?? $overlayClasses['medium'];
@endphp

@if(!empty($bannerImgUrl) || !empty($bannerVideoUrl))
<section class="relative w-full overflow-hidden border-b border-black bg-neutral-950 text-white reveal-on-scroll {{ $heightClass }}" style="{{ $customHeightStyle }}">
    @if(!empty($bannerLink))
        <a href="{{ str_starts_with($bannerLink, 'http') ? $bannerLink : url($bannerLink) }}" class="block group absolute inset-0">
    @endif

        {{-- Smart Ambient Backdrop: Soft blurred glow of image to eliminate dark voids --}}
        @if(!empty($bannerImgUrl))
            <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
                <img src="{{ $bannerImgUrl }}" alt="" class="w-full h-full object-cover blur-2xl scale-125 opacity-35">
            </div>
        @endif

        @if(!empty($bannerVideoUrl))
            <video autoplay muted loop playsinline preload="metadata" poster="{{ $bannerImgUrl }}"
                class="absolute inset-0 w-full h-full object-cover"
                style="object-position: {{ $bannerPosition }}; transform: scale({{ $bannerZoom }});">
                <source src="{{ $bannerVideoUrl }}">
            </video>
        @else
            <img src="{{ $bannerImgUrl }}" alt="{{ $bannerTitle ?: 'Promotional Banner' }}"
                class="absolute inset-0 w-full h-full hero-slow-zoom"
                style="object-position: {{ $bannerPosition }}; object-fit: {{ $bannerFit }}; transform-origin:center center;">
        @endif

        @if($bannerOverlay !== 'none')
            <div class="absolute inset-0 {{ $overlayClass }}"></div>
        @endif

        @if(!empty($bannerTitle) || !empty($bannerSubtitle) || !empty($bannerLink))
            <div class="relative z-10 w-full h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 flex flex-col justify-end pb-8 sm:pb-12 space-y-2 sm:space-y-3">
                @if(!empty($bannerSubtitle))
                    <div class="inline-flex items-center gap-2 border border-white/40 px-2.5 py-0.5 bg-black/60 w-fit backdrop-blur-xs">
                        <span class="w-1.5 h-1.5 bg-white"></span>
                        <span class="font-editorial font-bold text-[9px] sm:text-[10px] uppercase tracking-[0.2em] text-white">
                            {{ $bannerSubtitle }}
                        </span>
                    </div>
                @endif

                @if(!empty($bannerTitle))
                    <h2 class="font-editorial font-black text-2xl sm:text-4xl lg:text-5xl uppercase tracking-normal text-white leading-tight max-w-3xl drop-shadow-md">
                        {{ $bannerTitle }}
                    </h2>
                @endif

                @if(!empty($bannerLink))
                    <div class="pt-2">
                        <span class="inline-flex items-center gap-2 font-editorial font-bold text-xs uppercase tracking-[0.15em] text-white group-hover:underline underline-offset-4">
                            <span>{{ $isArabicStore ? 'تسوق الآن ←' : 'Shop Now →' }}</span>
                        </span>
                    </div>
                @endif
            </div>
        @endif

    @if(!empty($bannerLink))
        </a>
    @endif
</section>
@endif
