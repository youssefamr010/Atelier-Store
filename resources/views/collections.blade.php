@extends('layouts.app')

@php
    $isArCol = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

@section('title', ($isArCol ? 'جميع المنتجات — ' : ($collectionTitle ?? 'All Products') . ' — ') . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', $isArCol
    ? 'تصفح تشكيلتنا الكاملة من المحافظ الجلدية الفاخرة وإكسسوارات الاستخدام اليومي.'
    : ($collectionDescription ?? 'Precision engineered RFID wallets, magnetic cardholders, and handcrafted full-grain leather EDC accessories.')
)

@section('content')
<div class="bg-[#F5F5F0] min-h-screen py-8 lg:py-12">
    <div class="max-w-screen-2xl mx-auto px-3 sm:px-6 lg:px-10">

        {{-- Collection Editorial Header --}}
        <div class="border-b-2 border-black pb-6 mb-8 reveal-on-scroll">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 bg-black"></span>
                <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black/70">
                    {{ $isArCol ? ($currentSlug === 'all' ? 'جميع المنتجات' : 'تصنيف المنتجات') : ($currentSlug === 'all' ? 'FULL ARCHIVE' : 'CATEGORY SPOTLIGHT') }}
                </span>
            </div>
            <h1 class="font-editorial font-black text-2xl sm:text-4xl uppercase tracking-tight text-black">
                {{ $isArCol ? ($currentSlug === 'all' ? 'جميع المنتجات الفاخرة' : ($collectionTitle ?? 'المنتجات')) : ($collectionTitle ?? 'All Products') }}
            </h1>
            <p class="font-sans text-xs text-black/75 mt-2 max-w-2xl leading-relaxed">
                {{ $isArCol ? 'محافظ ذكية بخاصية حجب RFID، حوامل بطاقات مغناطيسية وإكسسوارات جلد طبيعي فاخرة مصنوعة يدوياً.' : ($collectionDescription ?? 'Precision engineered RFID smart wallets, magnetic phone attachments, and handcrafted full-grain leather EDC accessories.') }}
            </p>
        </div>

        {{-- Breadcrumb Navigation --}}
        <nav class="flex items-center gap-1.5 text-[10px] font-editorial uppercase tracking-wider text-black/50 mb-6" aria-label="{{ $isArCol ? 'مسار التنقل' : 'Breadcrumb' }}">
            <a href="{{ route('home') }}" class="hover:text-black transition-colors">{{ $isArCol ? 'الرئيسية' : 'Home' }}</a>
            <span>/</span>
            <span class="text-black font-bold">{{ $isArCol ? ($collectionTitle ?? 'جميع المنتجات') : ($collectionTitle ?? 'All Products') }}</span>
        </nav>

        {{-- Filter & Category Rail with Live Counts --}}
        <div id="filter-rail" class="flex items-center gap-2 overflow-x-auto pb-3 mb-6 text-[10px] font-editorial font-bold uppercase tracking-wider scrollbar-none sticky top-14 z-20 bg-[#F5F5F0] py-2">
            <a
                href="{{ route('collections.show', ['slug' => 'all']) }}"
                class="px-3 py-2 border-2 border-black transition-all flex items-center gap-1.5 whitespace-nowrap {{ ($currentSlug ?? 'all') === 'all' ? 'bg-black text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-black hover:text-white' }}"
            >
                <span>{{ $isArCol ? 'الكل' : 'ALL' }}</span>
                <span class="text-[9px] font-mono opacity-80">({{ \App\Models\Product::active()->count() }})</span>
            </a>

            @foreach($collections as $col)
                <a
                    href="{{ route('collections.show', ['slug' => $col->slug]) }}"
                    class="px-3 py-2 border-2 border-black transition-all flex items-center gap-1.5 whitespace-nowrap {{ ($currentSlug ?? '') === $col->slug ? 'bg-black text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-black hover:text-white' }}"
                >
                    <span>{{ $col->title }}</span>
                    @if(isset($col->products_count) && $col->products_count > 0)
                        <span class="text-[9px] font-mono opacity-80">({{ $col->products_count }})</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- ── Skeleton placeholders ── --}}
        <div id="col-skeletons" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4" aria-hidden="true">
            @for ($s = 0; $s < 10; $s++)
            <div class="atelier-skeleton bg-white border border-black/10 p-2.5">
                <div class="skeleton-img bg-black/8 mb-2" style="aspect-ratio:4/3;"></div>
                <div class="skeleton-line bg-black/8 h-2 w-3/4 mb-1.5"></div>
                <div class="skeleton-line bg-black/8 h-2 w-1/2 mb-2"></div>
                <div class="skeleton-line bg-black/8 h-2 w-full"></div>
            </div>
            @endfor
        </div>

        {{-- Product Grid — identical card design to showcase.blade.php --}}
        <div id="col-product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4 hidden">
            @forelse($products as $index => $product)
                @php
                    $img = $product->image_url
                        ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url))
                        : ($product->mediaAssets->first()?->url
                            ? url($product->mediaAssets->first()->url)
                            : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&q=75');
                    $price = $product->retail_price_minor ? number_format($product->retail_price_minor / 100, 0) . ' ' . ($isArCol ? 'ج.م' : 'EGP') : '—';
                    $hoverAsset = $product->mediaAssets->skip(1)->first() ?? $product->mediaAssets->first();
                    $hoverImg   = $hoverAsset?->url ? url($hoverAsset->url) : $img;
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
                    {{-- Image Frame: clean — badge top-left only, NO heart button inside --}}
                    <div class="product-media-frame relative border border-black/10 mb-2.5 bg-[#FBFBFA] overflow-hidden" style="aspect-ratio:4/3;">
                        <img
                            :src="current"
                            alt="{{ $product->title }}"
                            class="w-full h-full object-contain object-center group-hover:scale-105 transition-transform duration-300 ease-out"
                            loading="{{ $index < 10 ? 'eager' : 'lazy' }}"
                            decoding="async"
                            sizes="(max-width:640px) 50vw, (max-width:1024px) 33vw, (max-width:1280px) 25vw, 20vw"
                        >

                        {{-- Badge: top-left only, black/white editorial, pointer-events-none --}}
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
                                    {{ $isArCol ? 'جديد' : 'NEW' }}
                                </span>
                            </div>
                        @elseif($isBestseller)
                            <div class="absolute top-2 left-2 pointer-events-none">
                                <span class="bg-black text-white text-[8px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight">
                                    {{ $isArCol ? 'الأكثر مبيعاً' : 'BEST' }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Card Details --}}
                    <div class="space-y-2">

                        {{-- Row 1: Color dots + Stock indicator --}}
                        <div class="flex items-center justify-between gap-1 min-h-[14px]">
                            @if($colorSwatches->isNotEmpty())
                                <div class="flex items-center gap-1" aria-label="{{ $isArCol ? 'الألوان المتاحة' : 'Available colors' }}">
                                    @foreach($product->variants as $variant)
                                        @php
                                            $variantColor = $variant->attributes_json['color_hex'] ?? null;
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
                                    {{ $isArCol ? 'متبقي ' . $product->inventory : $product->inventory . ' left' }}
                                </span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $product->inventory > 0 ? 'bg-emerald-500' : 'bg-black/15' }}"
                                    title="{{ $product->inventory > 0 ? 'In Stock' : 'Out of Stock' }}"></span>
                            @endif
                        </div>

                        {{-- Row 2: Title + Wishlist Heart inline (BELOW image, never overlapping) --}}
                        <div class="flex items-start justify-between gap-1.5 min-h-[2.4rem] sm:min-h-[2.6rem]">
                            <h3 class="font-editorial font-bold text-xs sm:text-sm text-black group-hover:underline line-clamp-2 leading-tight flex-1 min-w-0" title="{{ $product->title }}">
                                {{ $product->title }}
                            </h3>

                            <button
                                type="button"
                                @click.stop.prevent="$store.wishlist.toggle({{ $product->id }})"
                                class="atelier-wishlist-btn shrink-0 flex items-center justify-center w-6 h-6 -mt-0.5 text-black/40 hover:text-black touch-manipulation outline-none"
                                :class="$store.wishlist.has({{ $product->id }}) ? 'is-wishlisted' : ''"
                                title="{{ $isArCol ? 'حفظ في المفضلة' : 'Save to Wishlist' }}"
                                aria-label="{{ $isArCol ? 'حفظ في المفضلة' : 'Save to Wishlist' }}"
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
                                {{ $isArCol ? 'عرض' : 'View' }} <span class="font-mono">→</span>
                            </span>
                        </div>
                    </div>
                </a>

            @empty
                <div class="col-span-full py-16 text-center border-2 border-dashed border-black/30 p-8 bg-white">
                    <div class="w-12 h-12 border-2 border-black bg-[#F5F5F0] flex items-center justify-center mx-auto mb-3 text-black">
                        <svg class="w-6 h-6 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="font-editorial font-bold text-lg uppercase tracking-wider text-black">
                        {{ $isArCol ? 'لم يتم العثور على قطع في هذا التصنيف' : 'No pieces found in this specific collection' }}
                    </h3>
                    <p class="text-xs text-black/60 max-w-md mx-auto mt-1 mb-6">
                        {{ $isArCol ? 'تصفح تشكيلتنا الكاملة لاكتشاف جميع المحافظ وإكسسوارات الجلد الطبيعي.' : 'Browse all our handcrafted wallets, money clips, and EDC products.' }}
                    </p>
                    <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="btn-luxury inline-block px-8 py-3 text-xs tracking-wider">
                        {{ $isArCol ? 'تصفح جميع المنتجات (' . \App\Models\Product::active()->count() . ') ←' : 'Shop All Products (' . \App\Models\Product::active()->count() . ') →' }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Reveal grid + hide skeletons --}}
        <script>
            (function () {
                var skeletons = document.getElementById('col-skeletons');
                var grid = document.getElementById('col-product-grid');
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

    </div>
</div>
@endsection
