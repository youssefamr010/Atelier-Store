{{-- Top Promotional Banner (Auto-switching between Hero & Offers) --}}
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
    $offersBannerImg = url('/imges/decor/offers-banner.jpg');

    $bannerHeightSetting = $settings['homepage_banner_height'] ?? '48vh';
    $heightMap = [
        'compact' => 'h-[280px] sm:h-[340px] lg:h-[380px]',
        'medium'  => 'h-[360px] sm:h-[440px] lg:h-[500px]',
        'large'   => 'h-[460px] sm:h-[560px] lg:h-[640px]',
        'full'    => 'h-[70vh] sm:h-[80vh] lg:h-[88vh]',
    ];
    $heightClass = $heightMap[$bannerHeightSetting] ?? (str_ends_with($bannerHeightSetting, 'vh') || str_ends_with($bannerHeightSetting, 'px') ? '' : 'h-[36vh] sm:h-[44vh] lg:h-[48vh]');
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
        'light'  => 'bg-black/25',
        'medium' => 'bg-gradient-to-t from-black via-black/60 to-black/40',
        'dark'   => 'bg-gradient-to-t from-black via-black/80 to-black/60',
    ];
    $overlayClass = $overlayClasses[$bannerOverlay] ?? $overlayClasses['medium'];
@endphp

@if(!empty($bannerImgUrl) || !empty($bannerVideoUrl))
<section class="relative w-full overflow-hidden border-b border-black bg-black text-white reveal-on-scroll {{ $heightClass }}" style="{{ $customHeightStyle }}"
    x-data="{ slide: 0 }"
    x-init="setInterval(() => slide = slide === 0 ? 1 : 0, 5000)"
>
    {{-- SLIDE 0: Original Hero --}}
    <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out" :class="slide === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'">
        @if(!empty($bannerLink))
            <a href="{{ str_starts_with($bannerLink, 'http') ? $bannerLink : url($bannerLink) }}" class="block group absolute inset-0">
        @endif

            @if(!empty($bannerVideoUrl))
                <video autoplay muted loop playsinline preload="metadata" poster="{{ $bannerImgUrl }}"
                    class="absolute inset-0 w-full h-full object-cover grayscale contrast-125"
                    style="object-position: {{ $bannerPosition }}; transform: scale({{ $bannerZoom }});">
                    <source src="{{ $bannerVideoUrl }}">
                </video>
            @else
                <img src="{{ $bannerImgUrl }}" alt="{{ $bannerTitle ?: 'Promotional Banner' }}"
                    class="absolute inset-0 w-full h-full grayscale contrast-125 hero-slow-zoom"
                    style="object-position: {{ $bannerPosition }}; object-fit: {{ $bannerFit }}; transform-origin:center center;">
            @endif

            <div class="absolute inset-0 {{ $overlayClass }}"></div>

            <div class="relative z-10 w-full h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 flex flex-col justify-end pb-8 sm:pb-12 space-y-2 sm:space-y-3">
                <div class="inline-flex items-center gap-2 border border-white/40 px-2.5 py-0.5 bg-black/60 w-fit">
                    <span class="w-1.5 h-1.5 bg-white"></span>
                    <span class="font-editorial font-bold text-[9px] sm:text-[10px] uppercase tracking-[0.2em] text-white">
                        {{ $isArabicStore ? 'ديكورات منزلية مختارة' : 'Curated Home Decoration' }}
                    </span>
                </div>

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

        @if(!empty($bannerLink))
            </a>
        @endif
    </div>

    {{-- SLIDE 1: Special Offers --}}
    <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out" :class="slide === 1 ? 'opacity-100 z-10' : 'opacity-0 z-0'">
        <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="block group absolute inset-0">

            <img src="{{ $offersBannerImg }}" alt="Special Offers — Up to 40% Off"
                class="absolute inset-0 w-full h-full object-cover">

            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-black/30"></div>

            <div class="relative z-10 w-full h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 flex flex-col justify-end pb-8 sm:pb-12 space-y-2 sm:space-y-3">
                <div class="inline-flex items-center gap-2 border border-red-400/60 px-3 py-1 bg-red-600/80 w-fit">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                    <span class="font-editorial font-bold text-[9px] sm:text-[10px] uppercase tracking-[0.2em] text-white">
                        {{ $isArabicStore ? 'عروض حصرية لفترة محدودة' : 'Limited Time Offers' }}
                    </span>
                </div>

                <h2 class="font-editorial font-black text-2xl sm:text-4xl lg:text-5xl uppercase tracking-normal text-white leading-tight max-w-3xl drop-shadow-md">
                    {{ $isArabicStore ? 'خصومات تصل إلى 40%' : 'Special Offers — Up to 40% Off' }}
                </h2>

                <p class="font-sans text-sm sm:text-base text-white/80 max-w-xl">
                    {{ $isArabicStore ? 'ديكورات منزلية أنيقة بأسعار استثنائية.' : 'Elegant home decorations at exceptional prices.' }}
                </p>

                <div class="pt-2">
                    <span class="inline-flex items-center gap-2 font-editorial font-bold text-xs uppercase tracking-[0.15em] bg-white text-black px-5 py-2.5 group-hover:bg-neutral-100 transition-colors shadow-lg">
                        <span>{{ $isArabicStore ? 'تسوق العروض ←' : 'Shop Offers →' }}</span>
                    </span>
                </div>
            </div>

        </a>
    </div>

</section>
@endif
