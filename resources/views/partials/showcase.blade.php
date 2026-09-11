{{-- Compact High-Density Luxury Showcase — 2/3/4/5 columns --}}
@php
    $isArabicStore = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $bestSellerId = (int) ($settings['featured_bestseller_product_id'] ?? 0);
@endphp
<section class="w-full bg-[#F5F5F0]">

    {{-- Section Header Strip --}}
    <div class="border-b border-black/10 py-4 px-4 sm:px-8 lg:px-14 bg-white/60">
        <div class="max-w-screen-2xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 bg-black inline-block"></span>
                <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black">
                    {{ $isArabicStore ? 'اختيارات وألوان متاحة' : 'Products & available colors' }}
                </span>
            </div>
            <a href="{{ route('collections.show', ['slug' => 'all']) }}"
               class="font-editorial font-bold text-[10px] uppercase tracking-[0.2em] text-black/60 hover:text-black transition-colors flex items-center gap-1">
                <span>{{ $isArabicStore ? 'كل المنتجات' : 'View all products' }}</span>
                <span>→</span>
            </a>
        </div>
    </div>

    {{-- 1. Main High-Density Product Grid --}}
    <div class="max-w-screen-2xl mx-auto px-3 sm:px-6 lg:px-10 py-6 lg:py-8">
        @if(isset($products) && $products->count() > 0)

        {{-- ── Skeleton placeholders (shown briefly before Alpine hydrates) ── --}}
        <div id="card-skeletons" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4" aria-hidden="true">
            @for ($s = 0; $s < 10; $s++)
            <div class="atelier-skeleton bg-white border border-black/10 p-2.5">
                <div class="skeleton-img bg-black/8 mb-2" style="aspect-ratio:4/3;"></div>
                <div class="skeleton-line bg-black/8 h-2 w-3/4 mb-1.5"></div>
                <div class="skeleton-line bg-black/8 h-2 w-1/2 mb-2"></div>
                <div class="skeleton-line bg-black/8 h-2 w-full"></div>
            </div>
            @endfor
        </div>

        {{-- ── Actual Product Grid ── --}}
        <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4 hidden">
            @foreach($products as $index => $product)
                @php
                    $img = $product->image_url
                        ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url))
                        : ($product->mediaAssets->first()?->url
                            ? (str_starts_with($product->mediaAssets->first()->url, 'http') ? $product->mediaAssets->first()->url : url($product->mediaAssets->first()->url))
                            : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&q=75');
                    $price = $product->retail_price_minor ? number_format($product->retail_price_minor / 100, 0) . ' ' . ($isArabicStore ? 'ج.م' : 'EGP') : '—';
                    $colorSwatches = $product->variants->map(function ($variant) {
                        $attributes = $variant->attributes_json ?? [];
                        $color = $attributes['color_hex'] ?? $attributes['hex'] ?? null;
                        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? strtoupper($color) : null;
                    })->filter()->unique()->take(5)->values();
                    $isNew        = !empty($product->is_new);
                    $isBestseller = !empty($product->is_bestseller);
                @endphp
                <a
                    href="{{ route('products.show', ['slug' => $product->slug]) }}"
                    data-prefetch-url="{{ route('products.show', ['slug' => $product->slug]) }}"
                    x-data="{ current: '{{ $img }}' }"
                    class="group block border border-black bg-white p-2.5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-0.5 transition-all duration-300 reveal-on-scroll"
                    style="transition-delay: {{ ($index % 5) * 60 }}ms;"
                >
                    {{-- Image Frame --}}
                    <div class="product-media-frame relative border border-black/8 mb-2" style="aspect-ratio:4/3;overflow:hidden;">
                        <img
                            :src="current"
                            alt="{{ $product->title }}"
                            class="w-full h-full object-contain object-center group-hover:scale-105 transition-transform duration-500 ease-out"
                            loading="{{ $index < 10 ? 'eager' : 'lazy' }}"
                            decoding="async"
                            sizes="(max-width:640px) 50vw, (max-width:1024px) 33vw, (max-width:1280px) 25vw, 20vw"
                        >

                        {{-- Left Badges: OFFER + Colors --}}
                        <div class="absolute top-1.5 left-1.5 flex flex-col gap-0.5">
                            @if($product->compare_at_price_minor && $product->compare_at_price_minor > $product->retail_price_minor)
                                @php $savePct = round((($product->compare_at_price_minor - $product->retail_price_minor) / $product->compare_at_price_minor) * 100); @endphp
                                <span class="bg-red-600 text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight shadow-sm">
                                    {{ $isArabicStore ? '-' . $savePct . '%' : $savePct . '% OFF' }}
                                </span>
                            @else
                                <span class="bg-black text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight">
                                    {{ $isArabicStore ? 'ديكور' : 'DECOR' }}
                                </span>
                            @endif
                            @if($product->variants->count() > 1)
                                <span class="bg-black/75 text-white text-[7px] font-mono font-bold uppercase px-1.5 py-0.5 leading-tight border border-white/20">
                                    {{ $product->variants->count() }} {{ $isArabicStore ? 'ألوان' : 'CLR' }}
                                </span>
                            @endif
                        </div>

                        {{-- Right Badges: NEW / BESTSELLER --}}
                        @if($isNew || $isBestseller)
                        <div class="absolute top-1.5 right-1.5 flex flex-col gap-0.5">
                            @if($isNew)
                                <span class="bg-emerald-600 text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight">
                                    {{ $isArabicStore ? 'جديد' : 'NEW' }}
                                </span>
                            @endif
                            @if($isBestseller)
                                <span class="bg-amber-500 text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight">
                                    {{ $isArabicStore ? 'الأكثر مبيعاً' : 'BEST' }}
                                </span>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="space-y-1">
                        {{-- Color dots + In-stock row --}}
                        <div class="flex items-center justify-between gap-1 min-h-[16px]">
                            @if($colorSwatches->isNotEmpty())
                                <span class="flex items-center gap-1" aria-label="{{ $isArabicStore ? 'الألوان المتاحة' : 'Available colors' }}">
                                    @foreach($product->variants as $variant)
                                        @php
                                            $variantColor = $variant->attributes_json['color_hex'] ?? $variant->attributes_json['hex'] ?? null;
                                            $variantImage = $variant->image_url ? (str_starts_with($variant->image_url, 'http') ? $variant->image_url : url($variant->image_url)) : null;
                                        @endphp
                                        @if(is_string($variantColor) && preg_match('/^#[0-9a-fA-F]{6}$/', $variantColor))
                                            <i
                                                @if($variantImage) @mouseenter='current = @json($variantImage)' @mouseleave="current = '{{ $img }}'" @endif
                                                class="shrink-0 border border-black/30 hover:border-black cursor-pointer transition-transform hover:scale-125 inline-block"
                                                style="background-color:{{ $variantColor }};width:8px;height:8px;border-radius:50%;display:inline-block;"
                                                title="{{ $variant->title }}"
                                            ></i>
                                        @endif
                                    @endforeach
                                    @if($product->variants->count() > $colorSwatches->count())
                                        <span class="text-[8px] font-mono text-black/50">+{{ $product->variants->count() - $colorSwatches->count() }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-[8px] font-editorial uppercase tracking-widest text-black/40">{{ $isArabicStore ? 'قطعة' : 'Piece' }}</span>
                            @endif
                            <span class="text-[8px] font-mono text-emerald-700 font-bold uppercase shrink-0">● {{ $isArabicStore ? 'متوفر' : 'Stock' }}</span>
                        </div>

                        {{-- Title --}}
                        <h3 class="font-editorial font-black text-xs sm:text-sm uppercase tracking-tight text-black group-hover:underline line-clamp-1 leading-snug">
                            {{ $product->title }}
                        </h3>

                        {{-- Description --}}
                        <p class="font-sans text-[10px] text-black/60 line-clamp-2 leading-snug">
                            {{ $product->description }}
                        </p>

                        {{-- Price + View Product --}}
                        <div class="pt-2 flex items-center justify-between border-t border-black/10 mt-2 gap-1">
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1 flex-wrap">
                                    <span class="font-editorial font-black text-xs sm:text-sm text-black block leading-none">
                                        {{ $price }}
                                    </span>
                                    @if($product->compare_at_price_minor && $product->compare_at_price_minor > $product->retail_price_minor)
                                        <span class="text-[9px] text-black/35 line-through font-mono">
                                            {{ number_format($product->compare_at_price_minor / 100, 0) }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[8px] text-green-700 font-bold uppercase">{{ $isArabicStore ? 'شحن سريع' : 'Ships Fast' }}</span>
                            </div>
                            <span class="font-editorial font-bold text-[9px] uppercase tracking-wider bg-black text-white px-2 py-1.5 group-hover:bg-neutral-800 transition-colors shrink-0 whitespace-nowrap" style="min-height:32px;display:flex;align-items:center;">
                                {{ $isArabicStore ? 'عرض ←' : 'View →' }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Reveal grid + hide skeletons after paint --}}
        <script>
            (function () {
                var skeletons = document.getElementById('card-skeletons');
                var grid = document.getElementById('product-grid');
                if (skeletons && grid) {
                    if (document.readyState === 'complete' || document.readyState === 'interactive') {
                        skeletons.remove(); grid.classList.remove('hidden');
                    } else {
                        document.addEventListener('DOMContentLoaded', function () {
                            skeletons.remove(); grid.classList.remove('hidden');
                        });
                    }
                }
            })();
        </script>

        {{-- Organized Category Exploration Section --}}
        @if(isset($collections) && $collections->count() > 0)
        <div class="mt-12 pt-10 border-t-2 border-black">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <span class="font-editorial font-bold text-[9px] uppercase tracking-[0.25em] text-black/50 block mb-1">
                        {{ $isArabicStore ? 'تسوق حسب الفئة' : 'Shop by category' }}
                    </span>
                    <h2 class="font-display text-2xl sm:text-3xl uppercase text-black">
                        {{ $isArabicStore ? 'اختر ما يناسبك' : 'Choose what suits you' }}
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($collections->take(3) as $cSection)
                <div class="bg-white border-2 border-black p-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-black/10 mb-3">
                            <h3 class="font-editorial font-black text-sm uppercase text-black">{{ $cSection->title }}</h3>
                            <span class="text-[9px] font-mono font-bold bg-black text-white px-2 py-0.5">
                                {{ $cSection->products->count() }} {{ $isArabicStore ? 'منتجات' : 'Items' }}
                            </span>
                        </div>
                        <p class="font-sans text-[10px] text-black/60 mb-5 line-clamp-2">
                            {{ $cSection->description ?? 'Handcrafted minimalist EDC and bespoke leather pieces designed for daily endurance.' }}
                        </p>
                    </div>

                    <a href="{{ route('collections.show', ['slug' => $cSection->slug]) }}" class="w-full text-center py-2 bg-black text-white font-editorial font-bold text-[10px] uppercase tracking-wider hover:bg-neutral-800 transition-colors block">
                        {{ $isArabicStore ? 'استكشف ' . $cSection->title . ' ←' : 'Explore ' . $cSection->title . ' →' }}
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Load More / Full Catalog Banner --}}
        <div class="text-center mt-10 pt-5">
            <a href="{{ route('collections.show', ['slug' => 'all']) }}"
               class="inline-flex items-center gap-3 font-editorial font-black text-xs uppercase tracking-[0.25em] border-2 border-black bg-white px-6 py-3 text-black hover:bg-black hover:text-white transition-all duration-300 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1">
                <span>{{ $isArabicStore ? 'تصفح كل ' . $products->count() . ' المنتجات' : 'Browse all ' . $products->count() . ' products' }}</span>
                <span>→</span>
            </a>
        </div>

        @else
        <div class="py-20 text-center bg-white border-2 border-dashed border-black/20 p-8">
            <p class="font-editorial font-bold text-xs uppercase tracking-widest text-black/40">No products yet in archive</p>
        </div>
        @endif
    </div>

</section>
