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
        <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4 hidden stagger-children">
            @foreach($products as $index => $product)
                @php
                    $img = $product->image_url ?: ($product->mediaAssets->first()?->url ?: '');
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
                    class="group block border-2 border-black bg-white p-3 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-1 transition-all duration-200 ease-out reveal-on-scroll relative"
                    style="transition-delay: {{ ($index % 5) * 50 }}ms;"
                >
                    {{-- Image Frame: clean — badge top-left only, NO heart button inside the image at all --}}
                    <div class="product-media-frame relative border border-black/10 mb-2.5 bg-[#FBFBFA] overflow-hidden" style="aspect-ratio:4/3;">
                        @if(!empty($img))
                        <img
                            src="{{ $img }}"
                            :src="current"
                            alt="{{ $product->title }}"
                            class="w-full h-full object-contain object-center group-hover:scale-105 transition-transform duration-300 ease-out"
                            loading="{{ $index < 6 ? 'eager' : 'lazy' }}"
                            decoding="async"
                        >
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-[#F5F5F0]">
                            <span class="font-editorial font-bold text-xs uppercase tracking-widest text-black/40">ATELIER</span>
                        </div>
                        @endif

                        {{-- Badge: top-left corner only, black/white editorial style, pointer-events-none so it never blocks clicks --}}
                        @if($product->compare_at_price_minor && $product->compare_at_price_minor > $product->retail_price_minor)
                            @php $savePct = round((($product->compare_at_price_minor - $product->retail_price_minor) / $product->compare_at_price_minor) * 100); @endphp
                            <div class="absolute top-2 left-2 pointer-events-none">
                                <span class="bg-black text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight">
                                    -{{ $savePct }}%
                                </span>
                            </div>
                        @elseif($isNew)
                            <div class="absolute top-2 left-2 pointer-events-none">
                                <span class="bg-black text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight">
                                    {{ $isArabicStore ? 'جديد' : 'NEW' }}
                                </span>
                            </div>
                        @elseif($isBestseller)
                            <div class="absolute top-2 left-2 pointer-events-none">
                                <span class="bg-black text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight">
                                    {{ $isArabicStore ? 'الأكثر مبيعاً' : 'BEST' }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Card Details --}}
                    <div class="space-y-2 mt-0">

                        {{-- Row 1: Color dots + Stock indicator --}}
                        <div class="flex items-center justify-between gap-1 min-h-[14px]">
                            @if($colorSwatches->isNotEmpty())
                                <div class="flex items-center gap-1" aria-label="{{ $isArabicStore ? 'الألوان المتاحة' : 'Available colors' }}">
                                    @foreach($product->variants as $variant)
                                        @php
                                            $variantColor = $variant->attributes_json['color_hex'] ?? $variant->attributes_json['hex'] ?? null;
                                            $variantImage = $variant->image_url ? (str_starts_with($variant->image_url, 'http') ? $variant->image_url : url($variant->image_url)) : null;
                                        @endphp
                                        @if(is_string($variantColor) && preg_match('/^#[0-9a-fA-F]{6}$/', $variantColor))
                                            <i
                                                @if($variantImage) @mouseenter='current = @json($variantImage)' @mouseleave="current = '{{ $img }}'" @endif
                                                class="shrink-0 border border-black/20 hover:border-black cursor-pointer transition-transform hover:scale-110 inline-block rounded-full"
                                                style="background-color:{{ $variantColor }};width:7px;height:7px;"
                                                title="{{ $variant->title }}"
                                            ></i>
                                        @endif
                                    @endforeach
                                    @if($product->variants->count() > $colorSwatches->count())
                                        <span class="text-[8px] font-mono text-black/40">+{{ $product->variants->count() - $colorSwatches->count() }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-[8px] text-black/25">—</span>
                            @endif

                            @php
                                $urgencyThreshold = (int)($settings['urgency_stock_threshold'] ?? 5);
                                $isLowStock = $product->inventory > 0 && $product->inventory <= $urgencyThreshold;
                            @endphp
                            @if($isLowStock)
                                <span class="text-[8px] font-mono text-black/60 shrink-0">
                                    {{ $isArabicStore ? 'متبقي ' . $product->inventory : $product->inventory . ' left' }}
                                </span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $product->inventory > 0 ? 'bg-emerald-500' : 'bg-black/15' }}"
                                    title="{{ $product->inventory > 0 ? 'In Stock' : 'Out of Stock' }}"></span>
                            @endif
                        </div>

                        {{-- Row 2: Title + Wishlist Heart (inline — heart is BELOW the image, next to the title) --}}
                        <div class="flex items-start justify-between gap-1.5 min-h-[2.4rem] sm:min-h-[2.6rem]">
                            <h3 class="font-editorial font-bold text-xs sm:text-sm text-black group-hover:underline line-clamp-2 leading-tight flex-1 min-w-0" title="{{ $product->title }}">
                                {{ $product->title }}
                            </h3>

                            {{-- Elegant inline wishlist heart — black when saved, not red --}}
                            <button
                                type="button"
                                @click.stop.prevent="$store.wishlist.toggle({{ $product->id }})"
                                class="atelier-wishlist-btn shrink-0 flex items-center justify-center w-6 h-6 -mt-0.5 text-black/40 hover:text-black touch-manipulation outline-none"
                                :class="$store.wishlist.has({{ $product->id }}) ? 'is-wishlisted' : ''"
                                title="{{ $isArabicStore ? 'حفظ في المفضلة' : 'Save to Wishlist' }}"
                                aria-label="{{ $isArabicStore ? 'حفظ في المفضلة' : 'Save to Wishlist' }}"
                            >
                                <svg class="atelier-heart-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path class="atelier-heart-path" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Row 3: Price + View affordance --}}
                        <div class="flex items-center justify-between border-t border-black/8 pt-1.5">
                            <div class="flex items-baseline gap-1.5 flex-wrap min-w-0">
                                <span class="font-editorial font-black text-xs sm:text-sm text-black leading-none">
                                    {{ $price }}
                                </span>
                                @if($product->compare_at_price_minor && $product->compare_at_price_minor > $product->retail_price_minor)
                                    <span class="text-[9px] text-black/35 line-through font-mono">
                                        {{ number_format($product->compare_at_price_minor / 100, 0) }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/40 group-hover:text-black transition-colors duration-200 flex items-center gap-0.5 shrink-0">
                                {{ $isArabicStore ? 'عرض' : 'View' }} <span class="font-mono">→</span>
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
