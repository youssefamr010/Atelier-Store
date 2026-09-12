@extends('layouts.app')

@php
    $isArCol = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

@section('title', ($isArCol ? 'جميع المنتجات — ' : ($collectionTitle ?? 'All Products') . ' — ') . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', $isArCol
    ? 'تصفح تشكيلتنا الكاملة من المنتجات والديكورات الفاخرة وإكسسوارات الاستخدام اليومي.'
    : ($collectionDescription ?? 'Precision engineered RFID wallets, magnetic cardholders, and handcrafted luxury EDC accessories.')
)

@section('content')
<div 
    class="bg-[#F5F5F0] min-h-screen py-6 sm:py-8 lg:py-12"
    x-data="{
        searchQuery: '',
        sortBy: 'featured',
        gridCols: 'standard', // 'compact', 'standard', 'spacious'
        totalItems: {{ $products->count() }},
        filterMatch(title) {
            if (!this.searchQuery) return true;
            return title.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
        }
    }"
>
    <div class="max-w-screen-2xl mx-auto px-3 sm:px-6 lg:px-10">

        {{-- ── 1. Collection Editorial Hero ── --}}
        <div class="border-b-2 border-black pb-6 sm:pb-8 mb-6 sm:mb-8 reveal-on-scroll">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="space-y-2 max-w-3xl">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-black inline-block"></span>
                        <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black/70">
                            {{ $isArCol ? ($currentSlug === 'all' ? 'الأرشيف الكامل' : 'تصنيف خاص') : ($currentSlug === 'all' ? 'FULL ARCHIVE' : 'COLLECTION SPOTLIGHT') }}
                        </span>
                        <span class="text-[9px] font-mono font-bold bg-black text-white px-2 py-0.5 rounded-full">
                            {{ $products->count() }} {{ $isArCol ? 'قطعة' : 'pieces' }}
                        </span>
                    </div>
                    <h1 class="font-editorial font-black text-2xl sm:text-4xl lg:text-5xl uppercase tracking-tight text-black leading-tight">
                        {{ $isArCol ? ($currentSlug === 'all' ? 'جميع القطع المختارة' : ($collectionTitle ?? 'المنتجات')) : ($collectionTitle ?? 'All Products') }}
                    </h1>
                    <p class="font-sans text-xs sm:text-sm text-black/75 max-w-2xl leading-relaxed">
                        {{ $isArCol 
                            ? ($collectionDescription ?? 'تصفح تشكيلتنا الحصرية من القطع المصنوعة بأعلى معايير الجودة والاهتمام بأدق التفاصيل.') 
                            : ($collectionDescription ?? 'Curated premium accessories and bespoke home decoration engineered for timeless aesthetics and enduring utility.') }}
                    </p>
                </div>

                {{-- Quick Stats / Breadcrumb --}}
                <div class="text-right flex items-center md:flex-col md:items-end justify-between gap-1 text-[10px] font-editorial uppercase tracking-wider text-black/50">
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('home') }}" class="hover:text-black transition-colors">{{ $isArCol ? 'الرئيسية' : 'Home' }}</a>
                        <span>/</span>
                        <span class="text-black font-bold">{{ $isArCol ? ($collectionTitle ?? 'المجموعة') : ($collectionTitle ?? 'Collection') }}</span>
                    </div>
                    <span class="font-mono text-[10px] text-black/40">
                        {{ $isArCol ? 'توصيل فوري متاح' : 'Express Delivery Available' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ── 2. Sticky Filter & Category Rail with Live Counts ── --}}
        <div class="sticky top-14 z-30 bg-[#F5F5F0]/95 backdrop-blur-md py-3 -mx-3 px-3 sm:-mx-6 sm:px-6 lg:-mx-10 lg:px-10 border-b border-black/10 mb-6 sm:mb-8 transition-all">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                
                {{-- Categories Pill Bar --}}
                <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 scrollbar-none flex-1 min-w-0">
                    <a
                        href="{{ route('collections.show', ['slug' => 'all']) }}"
                        class="px-3.5 py-2 border-2 border-black transition-all flex items-center gap-1.5 shrink-0 text-[10px] font-editorial font-bold uppercase tracking-wider touch-manipulation {{ ($currentSlug ?? 'all') === 'all' ? 'bg-black text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-black hover:text-white' }}"
                    >
                        <span>{{ $isArCol ? 'الكل' : 'ALL' }}</span>
                        <span class="text-[9px] font-mono opacity-80">({{ \App\Models\Product::active()->count() }})</span>
                    </a>

                    @foreach($collections as $col)
                        <a
                            href="{{ route('collections.show', ['slug' => $col->slug]) }}"
                            class="px-3.5 py-2 border-2 border-black transition-all flex items-center gap-1.5 shrink-0 text-[10px] font-editorial font-bold uppercase tracking-wider touch-manipulation {{ ($currentSlug ?? '') === $col->slug ? 'bg-black text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-black hover:text-white' }}"
                        >
                            <span>{{ $col->title }}</span>
                            @if(isset($col->products_count) && $col->products_count > 0)
                                <span class="text-[9px] font-mono opacity-80">({{ $col->products_count }})</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{-- Live Search & Controls --}}
                <div class="flex items-center gap-2 shrink-0">
                    {{-- In-Collection Live Search --}}
                    <div class="relative flex-1 sm:w-56">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="{{ $isArCol ? 'بحث في المجموعة...' : 'Filter collection...' }}" 
                            class="w-full border-2 border-black bg-white px-3 py-1.5 text-xs font-sans placeholder-black/40 focus:outline-none shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"
                        >
                        <template x-if="searchQuery">
                            <button @click="searchQuery = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-black/50 hover:text-black font-bold text-xs">×</button>
                        </template>
                    </div>

                    {{-- Grid Switcher (Desktop) --}}
                    <div class="hidden sm:flex items-center border-2 border-black bg-white shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <button 
                            type="button" 
                            @click="gridCols = 'compact'" 
                            :class="gridCols === 'compact' ? 'bg-black text-white' : 'text-black hover:bg-gray-100'"
                            class="p-1.5 transition-colors"
                            title="Compact Grid"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        </button>
                        <button 
                            type="button" 
                            @click="gridCols = 'standard'" 
                            :class="gridCols === 'standard' ? 'bg-black text-white' : 'text-black hover:bg-gray-100'"
                            class="p-1.5 transition-colors"
                            title="Standard Grid"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── 3. Skeleton Placeholders ── --}}
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

        {{-- ── 4. Actual Modern Product Grid ── --}}
        <div 
            id="col-product-grid" 
            class="grid gap-3 lg:gap-4 hidden"
            :class="{
                'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5': gridCols === 'standard',
                'grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6': gridCols === 'compact'
            }"
        >
            @forelse($products as $index => $product)
                @php
                    $img = $product->image_url
                        ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url))
                        : ($product->mediaAssets->first()?->url
                            ? (str_starts_with($product->mediaAssets->first()->url, 'http') ? $product->mediaAssets->first()->url : url($product->mediaAssets->first()->url))
                            : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&q=75');
                    $price = $product->retail_price_minor ? number_format($product->retail_price_minor / 100, 0) . ' ' . ($isArCol ? 'ج.م' : 'EGP') : '—';
                    $colorSwatches = $product->variants->map(function ($variant) {
                        $attributes = $variant->attributes_json ?? [];
                        $color = $attributes['color_hex'] ?? $attributes['hex'] ?? null;
                        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? strtoupper($color) : null;
                    })->filter()->unique()->take(5)->values();
                    $isNew        = !empty($product->is_new);
                    $isBestseller = !empty($product->is_bestseller);
                @endphp

                <div 
                    x-show="filterMatch('{{ addslashes($product->title) }}')"
                    x-data="{ current: '{{ $img }}' }"
                    class="h-full"
                >
                    <a
                        href="{{ route('products.show', ['slug' => $product->slug]) }}"
                        data-prefetch-url="{{ route('products.show', ['slug' => $product->slug]) }}"
                        class="group atelier-card relative flex flex-col justify-between h-full bg-white border border-black/15 hover:border-black p-2.5 sm:p-3 transition-all duration-300 hover:shadow-[0_8px_24px_rgba(0,0,0,0.12)] hover:-translate-y-1 block touch-manipulation"
                        id="col-card-{{ $product->id }}"
                        aria-label="{{ $product->title }}"
                    >
                        {{-- Visual Container: Image + Badges ONLY --}}
                        <div class="relative w-full overflow-hidden bg-[#FAFAF7] border border-black/8 mb-2.5 aspect-square">
                            <img
                                :src="current"
                                alt="{{ $product->title }}"
                                class="w-full h-full object-contain object-center transition-transform duration-500 ease-out group-hover:scale-105"
                                loading="{{ $index < 8 ? 'eager' : 'lazy' }}"
                                decoding="async"
                                sizes="(max-width:640px) 50vw, (max-width:1024px) 33vw, 20vw"
                            >

                            {{-- Badges: top-left --}}
                            @if($product->compare_at_price_minor && $product->compare_at_price_minor > $product->retail_price_minor)
                                @php $savePct = round((($product->compare_at_price_minor - $product->retail_price_minor) / $product->compare_at_price_minor) * 100); @endphp
                                <div class="absolute top-2 left-2 pointer-events-none">
                                    <span class="bg-black text-white text-[8px] sm:text-[9px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight shadow-xs">
                                        -{{ $savePct }}%
                                    </span>
                                </div>
                            @elseif($isNew)
                                <div class="absolute top-2 left-2 pointer-events-none">
                                    <span class="bg-black text-white text-[8px] sm:text-[9px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight shadow-xs">
                                        {{ $isArCol ? 'جديد' : 'NEW' }}
                                    </span>
                                </div>
                            @elseif($isBestseller)
                                <div class="absolute top-2 left-2 pointer-events-none">
                                    <span class="bg-black text-white text-[8px] sm:text-[9px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5 leading-tight shadow-xs">
                                        {{ $isArCol ? 'الأكثر طلباً' : 'BESTSELLER' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Bottom Information Container --}}
                        <div class="space-y-1.5 flex-1 flex flex-col justify-between">
                            
                            {{-- Row 1: Swatches + Stock --}}
                            <div class="flex items-center justify-between min-h-[14px]">
                                @if($colorSwatches->isNotEmpty())
                                    <div class="flex items-center gap-1" aria-label="{{ $isArCol ? 'الألوان المتاحة' : 'Available colors' }}">
                                        @foreach($product->variants as $variant)
                                            @php
                                                $variantColor = $variant->attributes_json['color_hex'] ?? null;
                                                $variantImage = $variant->image_url ? (str_starts_with($variant->image_url, 'http') ? $variant->image_url : url($variant->image_url)) : null;
                                            @endphp
                                            @if(is_string($variantColor) && preg_match('/^#[0-9a-fA-F]{6}$/', $variantColor))
                                                <i
                                                    @if($variantImage) @mouseenter="current = '{{ $variantImage }}'" @mouseleave="current = '{{ $img }}'" @endif
                                                    class="shrink-0 border border-black/20 hover:border-black cursor-pointer transition-transform hover:scale-125 inline-block rounded-full"
                                                    style="background-color:{{ $variantColor }};width:8px;height:8px;"
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

                            {{-- Row 2: Title + Wishlist Heart (inline, 2-lines readable) --}}
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
                </div>

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
                        {{ $isArCol ? 'تصفح تشكيلتنا الكاملة لاكتشاف جميع القطع المتوفرة.' : 'Browse our full archive to explore all handcrafted luxury pieces.' }}
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
