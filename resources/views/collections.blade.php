@extends('layouts.app')

@php
    $isArCol = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $isAllOverview = ($currentSlug ?? 'all') === 'all';
@endphp

@section('title', ($isArCol ? ($collectionTitle ?? 'التصنيفات') . ' — ' : ($collectionTitle ?? 'Collections') . ' — ') . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', $collectionDescription ?? ($isArCol ? 'تصفح تشكيلاتنا الحصرية من القطع والديكورات الفاخرة.' : 'Discover our curated selection of bespoke interior decorations and handcrafted lifestyle accessories.'))

@section('content')
<div 
    class="bg-[#F5F5F0] min-h-screen py-6 sm:py-8 lg:py-12"
    x-data="{
        viewMode: '{{ $isAllOverview ? 'collections' : 'products' }}', // 'collections' (Big Cards) or 'products' (Grid)
        searchQuery: '',
        gridCols: 'standard',
        filterMatch(title) {
            if (!this.searchQuery) return true;
            return title.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
        }
    }"
>
    <div class="max-w-screen-2xl mx-auto px-3 sm:px-6 lg:px-10">

        {{-- ── 1. Clean Editorial Hero (No cluttered breadcrumbs, No wallet text) ── --}}
        <div class="border-b-2 border-black pb-5 sm:pb-6 mb-6 sm:mb-8 reveal-on-scroll">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="space-y-1.5 max-w-3xl">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-black inline-block"></span>
                        <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black/70">
                            {{ $isArCol ? ($isAllOverview ? 'المجموعات والتصنيفات' : 'تصنيف خاص') : ($isAllOverview ? 'CURATED COLLECTIONS' : 'COLLECTION') }}
                        </span>
                        @if(!$isAllOverview)
                            <span class="text-[9px] font-mono font-bold bg-black text-white px-2 py-0.5 rounded-full">
                                {{ $products->count() }} {{ $isArCol ? 'قطعة' : 'pieces' }}
                            </span>
                        @else
                            <span class="text-[9px] font-mono font-bold bg-black text-white px-2 py-0.5 rounded-full">
                                {{ $collections->count() }} {{ $isArCol ? 'مجموعات' : 'collections' }}
                            </span>
                        @endif
                    </div>
                    <h1 class="font-editorial font-black text-2xl sm:text-4xl lg:text-5xl uppercase tracking-tight text-black leading-tight">
                        {{ $isArCol ? ($isAllOverview ? 'تشكيلات أتيليه الفاخرة' : ($collectionTitle ?? 'المنتجات')) : ($collectionTitle ?? 'Collections') }}
                    </h1>
                    <p class="font-sans text-xs sm:text-sm text-black/70 max-w-2xl leading-relaxed">
                        {{ $collectionDescription }}
                    </p>
                </div>

                {{-- Quick Action / Back link --}}
                @if(!$isAllOverview)
                    <div class="shrink-0">
                        <a 
                            href="{{ route('collections.show', ['slug' => 'all']) }}" 
                            class="inline-flex items-center gap-2 border-2 border-black bg-white px-4 py-2 text-xs font-editorial font-bold uppercase tracking-wider text-black hover:bg-black hover:text-white transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"
                        >
                            <span>{{ $isArCol ? '← عرض جميع المجموعات' : '← All Collections' }}</span>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Tab Switcher on Overview: Big Collection Cards vs Product Grid --}}
            @if($isAllOverview)
                <div class="flex items-center gap-2 mt-6 pt-4 border-t border-black/10">
                    <button 
                        type="button" 
                        @click="viewMode = 'collections'" 
                        :class="viewMode === 'collections' ? 'bg-black text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-gray-100'"
                        class="px-4 py-2 border-2 border-black text-xs font-editorial font-bold uppercase tracking-wider transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="7" height="7" stroke-width="2"/><rect x="14" y="3" width="7" height="7" stroke-width="2"/><rect x="14" y="14" width="7" height="7" stroke-width="2"/><rect x="3" y="14" width="7" height="7" stroke-width="2"/></svg>
                        <span>{{ $isArCol ? 'بطاقات المجموعات (Big Cards)' : 'Browse Collections' }}</span>
                    </button>

                    <button 
                        type="button" 
                        @click="viewMode = 'products'" 
                        :class="viewMode === 'products' ? 'bg-black text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-gray-100'"
                        class="px-4 py-2 border-2 border-black text-xs font-editorial font-bold uppercase tracking-wider transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <span>{{ $isArCol ? 'جميع المنتجات' : 'All Products Grid' }}</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- ── 2. BIG COLLECTION CARDS (Requested by User for Mobile & Desktop) ── --}}
        @if($isAllOverview)
            <div x-show="viewMode === 'collections'" class="space-y-6 sm:space-y-8 mb-12">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @forelse($collections as $col)
                        @php
                            $colImg = $col->image_url 
                                ? (str_starts_with($col->image_url, 'http') ? $col->image_url : url($col->image_url))
                                : 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=1000&q=80';
                        @endphp
                        <a 
                            href="{{ route('collections.show', ['slug' => $col->slug]) }}"
                            class="group block relative overflow-hidden border-2 border-black bg-black rounded-none shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-1 transition-all duration-300"
                        >
                            {{-- Image Container with Aspect Ratio (Mobile friendly large frame) --}}
                            <div class="relative w-full aspect-[16/10] sm:aspect-[4/3] overflow-hidden bg-neutral-900">
                                <img 
                                    src="{{ $colImg }}" 
                                    alt="{{ $col->title }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                                    loading="lazy"
                                >
                                {{-- Ambient Dark Gradient --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-85 group-hover:opacity-90 transition-opacity"></div>
                                
                                {{-- Piece Count Badge --}}
                                <div class="absolute top-3 right-3 sm:top-4 sm:right-4 z-10">
                                    <span class="bg-white text-black text-[10px] font-mono font-black uppercase px-2.5 py-1 border border-black shadow-xs">
                                        {{ $col->products_count ?? 0 }} {{ $isArCol ? 'قطعة' : 'Pieces' }}
                                    </span>
                                </div>

                                {{-- Card Content --}}
                                <div class="absolute inset-x-0 bottom-0 p-4 sm:p-6 z-10 text-white flex flex-col justify-end space-y-2">
                                    <span class="text-[9px] font-editorial font-bold uppercase tracking-[0.2em] text-amber-300">
                                        {{ $isArCol ? 'تشكيلة مختارة' : 'Collection' }}
                                    </span>
                                    <h2 class="font-editorial font-black text-xl sm:text-2xl uppercase tracking-tight text-white leading-tight">
                                        {{ $col->title }}
                                    </h2>
                                    @if(!empty($col->description))
                                        <p class="font-sans text-xs text-white/80 line-clamp-2 leading-relaxed">
                                            {{ $col->description }}
                                        </p>
                                    @endif
                                    <div class="pt-2 flex items-center gap-1.5 text-xs font-editorial font-bold uppercase tracking-wider text-white group-hover:text-amber-300 transition-colors">
                                        <span>{{ $isArCol ? 'تصفح المجموعة ←' : 'Explore Collection →' }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-16 text-center border-2 border-dashed border-black/30 p-8 bg-white">
                            <h3 class="font-editorial font-bold text-lg uppercase tracking-wider text-black">
                                {{ $isArCol ? 'لا توجد مجموعات بعد' : 'No collections available' }}
                            </h3>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- ── 3. Sticky Filter & Category Rail (When in Products Mode or Single Collection) ── --}}
        <div 
            x-show="viewMode === 'products' || !{{ $isAllOverview ? 'true' : 'false' }}"
            class="sticky top-14 z-30 bg-[#F5F5F0]/95 backdrop-blur-md py-3 -mx-3 px-3 sm:-mx-6 sm:px-6 lg:-mx-10 lg:px-10 border-b border-black/10 mb-6 sm:mb-8 transition-all"
        >
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

        {{-- ── 4. Actual Product Grid ── --}}
        <div 
            x-show="viewMode === 'products' || !{{ $isAllOverview ? 'true' : 'false' }}"
            id="col-product-grid" 
            class="grid gap-3 lg:gap-4"
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
                        {{ $isArCol ? 'لا توجد منتجات في هذه المجموعة حالياً' : 'No pieces found in this collection' }}
                    </h3>
                    <p class="text-xs text-black/60 max-w-md mx-auto mt-1 mb-6">
                        {{ $isArCol ? 'يمكنك إضافة منتجات جديدة بسهولة من لوحة التحكم.' : 'You can easily add new products to this collection from the admin panel.' }}
                    </p>
                    <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="btn-luxury inline-block px-8 py-3 text-xs tracking-wider">
                        {{ $isArCol ? 'تصفح جميع التشكيلات ←' : 'Browse All Collections →' }}
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
