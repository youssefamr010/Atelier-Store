@extends('layouts.app')

@section('title', ($product->seo_title ?: $product->title) . ' — ' . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', $product->seo_description ?: Str::limit(strip_tags($product->description ?? ''), 160))
@section('canonical', route('products.show', ['slug' => $product->slug]))
@section('og_type', 'product')
@section('og_image', $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url)) : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=1000&q=85')

@push('structured_data')
@php
    $schemaImg = $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url)) : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=1000&q=85';
    $schemaPrice = number_format(($product->retail_price_minor ?: 78000) / 100, 2, '.', '');
@endphp
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": @json($product->title),
  "image": [
    @json($schemaImg)
  ],
  "description": @json($product->seo_description ?: Str::limit(strip_tags($product->description ?? ''), 300)),
  "sku": @json($product->sku),
  "brand": {
    "@type": "Brand",
    "name": @json($settings['storeName'] ?? 'ATELIER')
  },
  "offers": {
    "@type": "Offer",
    "url": @json(route('products.show', ['slug' => $product->slug])),
    "priceCurrency": "EGP",
    "price": "{{ $schemaPrice }}",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "{{ ($product->inventory > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
    "seller": {
      "@type": "Organization",
      "name": @json($settings['storeName'] ?? 'ATELIER')
    }
  }
}
</script>
@endpush

@section('content')
@php
    $isArProd = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $mainImg = $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url)) : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=1000&q=85';
    $basePriceEgp = $product->retail_price_minor ? number_format($product->retail_price_minor / 100, 0) . ' EGP' : '780 EGP';
    $variants = $product->variants ?? collect();
    $mediaAssets = $product->mediaAssets ?? collect();

    // Map variants data for Alpine.js
    $colorMap = [
        'stone grey' => '#878681',
        'obsidian black' => '#1A1A1A',
        'cognac tan' => '#A0522D',
        'midnight navy' => '#191970',
        'stealth matte black' => '#222222',
        'brushed titanium' => '#8E9296',
        'gunmetal grey' => '#4A4E51',
        'saddle brown' => '#8B4513',
        'espresso black' => '#1B140E',
        'burgundy wine' => '#800020',
        'matte carbon weave' => '#2B2B2B',
        'forged carbon grain' => '#383838',
        'desert tan' => '#D2B48C',
        'carbon obsidian' => '#111111',
        'matte obsidian' => '#1A1A1A',
        'titanium silver' => '#C0C0C0',
        'vintage brown' => '#7A4926',
        'classic black' => '#111111',
        'navy blue' => '#000080',
        'executive brown gift set' => '#5C3A21',
        'obsidian black gift set' => '#151515',
        'rose pink' => '#E8A3B7',
        'midnight blue' => '#191970',
    ];

    $variantsJson = $variants->map(function($v) use ($product, $mainImg, $colorMap) {
        $price = $v->effective_price_minor ? number_format($v->effective_price_minor / 100, 0) . ' EGP' : number_format($product->retail_price_minor / 100, 0) . ' EGP';
        $vImg = $v->image_url 
            ? (str_starts_with($v->image_url, 'http') ? $v->image_url : url($v->image_url))
            : (isset($v->attributes_json['image_url']) && !empty($v->attributes_json['image_url']) 
                ? (str_starts_with($v->attributes_json['image_url'], 'http') ? $v->attributes_json['image_url'] : url($v->attributes_json['image_url']))
                : $mainImg);
        $variantMedia = $v->mediaAssets->pluck('url')->map(function($url) {
            return str_starts_with($url, 'http') ? $url : url($url);
        })->values()->toArray();

        $titleLower = strtolower(trim($v->title));
        $colorHex = $v->attributes_json['color_hex'] ?? $v->attributes_json['hex'] ?? $colorMap[$titleLower] ?? '#161616';

        return [
            'id'             => (string)$v->id,
            'title'          => $v->title,
            'attribute_name' => $v->attribute_name ?: 'Color',
            'price'          => $price,
            'inventory'      => $v->inventory,
            'image'          => $vImg,
            'color_hex'      => $colorHex,
            'gallery'        => count($variantMedia) > 0 ? $variantMedia : [],
        ];
    })->values();

    $allThumbAssets = collect($mediaAssets)->filter(fn($a) => !empty($a->url));
    $uniqueAssetUrls = collect([$mainImg])->merge($allThumbAssets->map(fn($a) => str_starts_with($a->url, 'http') ? $a->url : url($a->url)))->unique()->values()->toArray();

@endphp

<script>
function productDetailComponent() {
    const variants = @json($variantsJson);
    const defaultGallery = @json($uniqueAssetUrls);
    const fallbackMainImg = @json($mainImg);
    const initialPrice = @json(count($variantsJson) > 0 ? $variantsJson[0]['price'] : $basePriceEgp);
    const initialStock = {{ count($variantsJson) > 0 ? (int)$variantsJson[0]['inventory'] : (int)$product->inventory }};
    const initialVarId = @json(count($variantsJson) > 0 ? (string)$variantsJson[0]['id'] : '');
    const initialOptionTitle = @json(count($variantsJson) > 0 ? (string)$variantsJson[0]['title'] : '');
    const currentProdId = {{ (int)$product->id }};
    const currentProdTitle = @json($product->title);
    const currentProdUrl = @json(route('products.show', ['slug' => $product->slug]));

    return {
        activeImage: fallbackMainImg,
        selectedImage: fallbackMainImg,
        activePrice: initialPrice,
        activeStock: initialStock,
        selectedVariantId: initialVarId,
        selectedOptionTitle: initialOptionTitle,
        qty: 1,
        variantsMap: variants,
        defaultGallery: defaultGallery,
        currentGallery: defaultGallery,
        init() {
            const params = new URLSearchParams(window.location.search);
            const varId = params.get('variant');
            const colorParam = params.get('color');
            if (varId && this.variantsMap.length > 0) {
                const found = this.variantsMap.find(v => String(v.id) === String(varId));
                if (found) this.selectVariant(found);
            } else if (colorParam && this.variantsMap.length > 0) {
                const found = this.variantsMap.find(v => v.title && v.title.toLowerCase().includes(colorParam.toLowerCase()));
                if (found) this.selectVariant(found);
            }

            // Record to recently viewed v2
            try {
                if (localStorage.getItem('atelier_recently_viewed')) {
                    localStorage.removeItem('atelier_recently_viewed');
                }
                const stored = JSON.parse(localStorage.getItem('atelier_recently_viewed_v2') || '[]');
                const currentItem = {
                    id: currentProdId,
                    title: currentProdTitle,
                    price: initialPrice,
                    image: fallbackMainImg,
                    url: currentProdUrl
                };
                const filtered = stored.filter(i => i && i.id !== currentItem.id);
                filtered.unshift(currentItem);
                localStorage.setItem('atelier_recently_viewed_v2', JSON.stringify(filtered.slice(0, 8)));
            } catch(e) {}
        },
        selectVariant(v) {
            if (!v) return;
            this.selectedVariantId = String(v.id);
            this.selectedOptionTitle = v.title || '';
            this.activePrice = v.price;
            this.activeStock = v.inventory;
            
            if (v.gallery && v.gallery.length > 0) {
                this.currentGallery = v.gallery;
                this.activeImage = v.gallery[0];
                this.selectedImage = v.gallery[0];
            } else {
                this.currentGallery = this.defaultGallery;
                if (v.image) {
                    this.activeImage = v.image;
                    this.selectedImage = v.image;
                } else {
                    this.activeImage = (this.defaultGallery && this.defaultGallery[0]) || fallbackMainImg;
                    this.selectedImage = (this.defaultGallery && this.defaultGallery[0]) || fallbackMainImg;
                }
            }
        },
        selectVariantById(id) {
            const v = this.variantsMap.find(item => String(item.id) === String(id));
            if (v) this.selectVariant(v);
        },
        previewVariant(v) { 
            if (!v) return;
            if (v.gallery && v.gallery.length > 0) {
                this.activeImage = v.gallery[0];
            } else if (v.image) {
                this.activeImage = v.image;
            }
        },
        previewVariantById(id) {
            const v = this.variantsMap.find(item => String(item.id) === String(id));
            if (v) this.previewVariant(v);
        },
        restoreSelectedImage() { 
            this.activeImage = this.selectedImage || fallbackMainImg; 
        },
        shareProduct() {
            if (navigator.share) {
                navigator.share({ title: currentProdTitle, url: window.location.href }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link copied to clipboard!');
            }
        }
    };
}
</script>

<div 
    x-data="productDetailComponent()"
    class="bg-[#F5F5F0] min-h-screen py-5 sm:py-8 lg:py-20"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16">
            
            <!-- Product Gallery Column -->
            <div class="lg:col-span-7 space-y-4">
                <div class="relative w-full overflow-hidden border-2 border-black bg-white p-3 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
                    <div class="product-media-frame relative">
                        <img 
                            src="{{ $mainImg }}"
                            :src="activeImage" 
                            alt="{{ $product->title }}" 
                            class="w-full h-full object-contain object-center transition-all duration-500 ease-out"
                            fetchpriority="high" decoding="async"
                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1627123424574-724758594e93?w=1000&q=85';"
                        >
                    </div>
                </div>

                <!-- Thumbnails Rail -->
                <div x-show="currentGallery.length > 0" style="display: none;" class="flex items-center gap-3 overflow-x-auto pb-2">
                    <template x-for="thumb in currentGallery" :key="thumb">
                        <button 
                            type="button" 
                            @click="activeImage = thumb; selectedImage = thumb"
                            @mouseenter="activeImage = thumb" @mouseleave="restoreSelectedImage()"
                            class="w-16 h-16 border p-0.5 bg-white shrink-0 cursor-pointer transition-all duration-200"
                            :class="activeImage === thumb ? 'border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'border-black/30 opacity-70 hover:opacity-100'"
                        >
                            <img :src="thumb" alt="" class="w-full h-full object-contain bg-white" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1627123424574-724758594e93?w=400&q=80';">
                        </button>
                    </template>
                </div>

                @if(count($variantsJson) > 0)
                    <div class="mt-4 rounded-xl border border-black/10 bg-white/70 p-3 sm:p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-[.16em] text-black/55">{{ ($settings['storefront_lang'] ?? 'en') === 'ar' ? 'اختر اللون' : 'Choose color' }}</span>
                            <span class="text-xs text-black/65" x-text="selectedOptionTitle"></span>
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            @foreach($variantsJson as $vItem)
                                <button type="button" @click="selectVariantById('{{ $vItem['id'] }}')" @mouseenter="previewVariantById('{{ $vItem['id'] }}')" @mouseleave="restoreSelectedImage()" class="group flex flex-col items-center gap-1.5" :aria-label="'{{ addslashes($vItem['title']) }}'">
                                    <span class="w-9 h-9 rounded-full border-2 transition-all shadow-sm" style="background-color: {{ $vItem['color_hex'] }}" :class="selectedVariantId === '{{ $vItem['id'] }}' ? 'ring-2 ring-black ring-offset-2 border-black scale-110' : 'border-black/20 hover:scale-105'"></span>
                                    <span class="max-w-[68px] truncate text-[9px] font-semibold text-black/60 group-hover:text-black">{{ $vItem['title'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Product Specs & Action Column -->
            <div class="lg:col-span-5 space-y-6">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <template x-if="activeStock > 0">
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black">
                                IN STOCK · 24H EXPRESS DISPATCH
                            </span>
                        </template>
                        <template x-if="activeStock <= 0">
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-red-600">
                                CURRENTLY OUT OF STOCK
                            </span>
                        </template>
                        
                        <div class="flex items-center gap-3">
                            <!-- Wishlist Toggle -->
                            <button 
                                type="button" 
                                @click="$store.wishlist.toggle({{ $product->id }})"
                                class="flex items-center gap-1 text-xs font-editorial font-bold uppercase tracking-wider transition-colors cursor-pointer"
                                :class="$store.wishlist.has({{ $product->id }}) ? 'text-red-600' : 'text-black hover:opacity-70'"
                                title="Add to Wishlist"
                            >
                                <x-icon name="heart" class="w-4 h-4" />
                                <span x-text="$store.wishlist.has({{ $product->id }}) ? 'Saved' : 'Wishlist'"></span>
                            </button>

                            <button @click="shareProduct()" type="button" class="text-xs font-editorial font-bold uppercase tracking-wider text-black hover:underline cursor-pointer">
                                ↗ Share
                            </button>
                        </div>
                    </div>

                    <!-- Low Stock Urgency Indicator -->
                    @php
                        $urgencyEnabled = ($settings['urgency_indicator_enabled'] ?? '1') === '1';
                        $threshold = (int)($settings['urgency_stock_threshold'] ?? 5);
                    @endphp
                    @if($urgencyEnabled)
                    <template x-if="activeStock > 0 && activeStock <= {{ $threshold }}">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-300 text-amber-900 rounded text-xs font-bold animate-pulse">
                            <x-icon name="flame" class="w-4 h-4 text-amber-600 shrink-0" />
                            <span>
                                {{ $isArProd ? 'عاجل: متبقي ' : 'Only ' }}
                                <span x-text="activeStock" class="font-mono font-black"></span>
                                {{ $isArProd ? ' قطع فقط في المخزون — اطلب الآن!' : ' left in stock — order soon!' }}
                            </span>
                        </div>
                    </template>
                    @endif

                    <h1 class="font-sans font-extrabold text-[clamp(1.75rem,4vw,3.25rem)] tracking-[-0.04em] text-black leading-[1.06]"
                        style="word-break: break-word; overflow-wrap: break-word;">
                        {{ $product->title }}
                    </h1>

                    <!-- Star Rating summary under title -->
                    @php
                        $avgRating = $product->averageRating();
                        $reviewsCount = $product->reviewsCount();
                    @endphp
                    <div class="flex items-center gap-2 pt-1">
                        <div class="flex items-center text-amber-500">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($avgRating) ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 text-gray-200' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <a href="#reviews-section" class="text-xs font-mono font-bold text-gray-600 hover:text-black hover:underline">
                            {{ $avgRating > 0 ? number_format($avgRating, 1) : '5.0' }} ({{ $reviewsCount }} {{ $isArProd ? 'تقييم موثق' : 'reviews' }})
                        </a>
                    </div>

                    <div class="pt-2">
                        @php
                            $isArProd = ($settings['storefront_lang'] ?? 'en') === 'ar';
                        @endphp
                        <span class="font-editorial font-black text-3xl text-black" x-text="activePrice"></span>
                        @if($product->compare_at_price_minor)
                            <span class="text-sm text-gray-400 line-through font-editorial ml-2">
                                {{ number_format($product->compare_at_price_minor / 100, 0) }} {{ $isArProd ? 'ج.م' : 'EGP' }}
                            </span>
                        @endif
                        <span class="text-[10px] text-black/60 font-editorial font-bold uppercase tracking-widest block mt-0.5">
                            {{ $isArProd ? 'الضريبة والتوصيل يُحسبان عند إتمام الطلب' : 'Taxes and delivery are calculated at checkout' }}
                        </span>
                    </div>
                </div>

                <!-- Rich Description -->
                <div class="border-t border-b border-black/10 py-4 font-sans text-xs sm:text-sm text-black/80 leading-relaxed prose prose-sm max-w-none">
                    {!! $product->description !!}
                </div>

                <!-- Color & Option Selection -->
                @if(count($variantsJson) > 0)
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="font-editorial font-bold text-xs uppercase tracking-wider text-black">
                                {{ $isArProd ? 'اللون / الخيار المختار:' : 'Select Color / Finish:' }}
                                <span x-text="selectedOptionTitle" class="font-sans font-bold text-black ml-1"></span>
                            </span>
                            <span class="text-[10px] font-mono text-black/50">
                                {{ count($variantsJson) }} {{ $isArProd ? 'خيارات' : 'Options' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            @foreach($variantsJson as $vItem)
                                <button 
                                    type="button" 
                                    @click="selectVariantById('{{ $vItem['id'] }}')"
                                    @mouseenter="previewVariantById('{{ $vItem['id'] }}')"
                                    @mouseleave="restoreSelectedImage()"
                                    class="flex items-center gap-2.5 border-2 px-3.5 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider transition-all min-h-[44px] cursor-pointer"
                                    :class="selectedVariantId === '{{ $vItem['id'] }}' ? 'border-black bg-black text-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] -translate-y-0.5' : 'border-black/20 bg-white text-black hover:border-black'"
                                >
                                    <span class="w-3.5 h-3.5 rounded-full border border-black/30 shrink-0 inline-block shadow-xs" style="background-color: {{ $vItem['color_hex'] }};"></span>
                                    <span>{{ $vItem['title'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Technical Specifications (Dimensions & Materials) -->
                @if($product->material || $product->dimensions || $product->weight)
                    <div class="border-2 border-black p-4 bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] space-y-2 text-xs">
                        <h3 class="font-editorial font-bold uppercase text-[11px] tracking-wider text-black border-b border-black/10 pb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>{{ $isArProd ? 'المواصفات الفنية والخامات' : 'Technical Specifications' }}</span>
                        </h3>
                        <dl class="space-y-1.5 font-sans">
                            @if($product->material)
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500 font-bold uppercase text-[10px]">{{ $isArProd ? 'الخامة' : 'Material' }}</dt>
                                    <dd class="text-black font-semibold text-right">{{ $product->material }}</dd>
                                </div>
                            @endif
                            @if($product->dimensions)
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500 font-bold uppercase text-[10px]">{{ $isArProd ? 'الأبعاد' : 'Dimensions' }}</dt>
                                    <dd class="text-black font-mono font-semibold text-right">{{ $product->dimensions }}</dd>
                                </div>
                            @endif
                            @if($product->weight)
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500 font-bold uppercase text-[10px]">{{ $isArProd ? 'الوزن' : 'Weight' }}</dt>
                                    <dd class="text-black font-mono font-semibold text-right">{{ $product->weight }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                <!-- Quantity & Purchase Actions -->
                <form method="POST" action="{{ route('cart.add') }}" class="space-y-4 pt-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" :value="selectedVariantId">

                    <!-- Quantity Control -->
                    <div class="flex items-center justify-between border-2 border-black bg-white p-3 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <span class="font-editorial font-bold text-xs uppercase tracking-wider text-black">{{ $isArProd ? 'الكمية المطلوبة' : 'Quantity' }}</span>
                        <div class="flex items-center border-2 border-black bg-[#F5F5F0]">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="w-9 h-9 flex items-center justify-center font-bold text-base hover:bg-black hover:text-white transition-colors">−</button>
                            <input type="number" name="qty" x-model="qty" min="1" max="20" class="w-12 h-9 text-center font-mono font-bold text-xs border-x-2 border-black bg-white focus:outline-none" readonly>
                            <button type="button" @click="qty = Math.min(20, qty + 1)" class="w-9 h-9 flex items-center justify-center font-bold text-base hover:bg-black hover:text-white transition-colors">+</button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2.5">
                        <button type="submit" class="btn-luxury w-full py-4 text-center text-xs tracking-[0.2em] flex items-center justify-center gap-2 cursor-pointer">
                            <span>{{ $isArProd ? 'إضافة إلى السلة' : 'Add to Cart' }}</span>
                            <span class="text-sm font-bold">+</span>
                        </button>
                        
                        <a :href="'{{ route('checkout') }}?product_id={{ $product->id }}' + (selectedVariantId ? '&variant_id=' + selectedVariantId : '')" 
                           class="btn-luxury-outline w-full py-3.5 text-center text-xs tracking-[0.2em] block">
                            {{ $isArProd ? 'شراء فوري مباشر ←' : 'Buy Now →' }}
                        </a>
                    </div>
                </form>

                <div class="text-center pt-1">
                    <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/50 hover:text-black transition-colors">
                        {{ $isArProd ? '← العودة لتصفح جميع المنتجات' : '← Continue Shopping' }}
                    </a>
                </div>

                <!-- Amazon Product Link — Admin Only -->
                @auth
                @if(auth()->user()->is_admin && !empty($product->attributes_json['supplier_product_url']))
                    <div class="border-2 border-black bg-[#FFFBEB] p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                    <span class="font-editorial font-bold text-xs uppercase tracking-wider text-black">
                                        Admin: Amazon.eg Source Link
                                    </span>
                                </div>
                                <p class="text-[11px] text-black/70 mt-0.5">
                                    This link is only visible to admin users
                                </p>
                            </div>
                            <a href="{{ $product->attributes_json['supplier_product_url'] }}" target="_blank" rel="noopener noreferrer" 
                               class="bg-black text-white text-[10px] font-editorial font-bold uppercase tracking-wider px-3 py-2 shrink-0 hover:bg-neutral-800 transition-colors flex items-center gap-1 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                <span>Amazon.eg</span>
                                <span>↗</span>
                            </a>
                        </div>
                    </div>
                @endif
                @endauth

                <!-- Product Specifications -->
                @if($product->material || $product->dimensions || $product->weight)
                    <div class="border-2 border-black bg-white p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <span class="font-editorial font-bold text-xs uppercase tracking-wider text-black block border-b border-black/10 pb-2 mb-3">
                            {{ $isArProd ? 'المواصفات الفنية' : 'Technical Specifications' }}
                        </span>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            @if($product->material)
                                <div>
                                    <span class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/50 block">Material</span>
                                    <span class="font-sans font-medium text-black">{{ $product->material }}</span>
                                </div>
                            @endif
                            @if($product->dimensions)
                                <div>
                                    <span class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/50 block">Dimensions</span>
                                    <span class="font-sans font-medium text-black">{{ $product->dimensions }}</span>
                                </div>
                            @endif
                            @if($product->weight)
                                <div>
                                    <span class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/50 block">Weight</span>
                                    <span class="font-sans font-medium text-black">{{ $product->weight }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Trust Badges & Guarantee Strip -->
                @include('partials.trust-badges')

            </div>

        </div>

    </div>
</div>

{{-- Verified Customer Reviews Section --}}
@include('partials.product-reviews')

{{-- Related Products Section --}}
@if(isset($relatedProducts) && $relatedProducts->count() > 0)
<section class="bg-[#F5F5F0] border-t border-black py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">

        <div class="flex items-baseline justify-between mb-8">
            <h2 class="font-display text-fluid-title uppercase text-black" style="font-size: clamp(1.4rem, 4vw, 2.5rem);">
                {{ $isArProd ? 'قد يعجبك أيضاً' : 'You May Also Like' }}
            </h2>
            <a href="{{ route('collections.show', ['slug' => 'all']) }}"
               class="font-editorial font-bold text-[10px] uppercase tracking-[0.2em] text-black hover:opacity-60 transition-opacity flex items-center gap-1">
                {{ $isArProd ? 'عرض الكل ←' : 'View All →' }}
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($relatedProducts->take(4) as $related)
            @php
                $relImg = $related->image_url
                    ? (str_starts_with($related->image_url, 'http') ? $related->image_url : url($related->image_url))
                    : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&q=80';
                $relPrice = $related->retail_price_minor
                    ? number_format($related->retail_price_minor / 100, 0) . ' EGP'
                    : '780 EGP';
                $relatedAlt = $related->mediaAssets->first()?->url ?? $relImg;
                $relatedAlt = str_starts_with($relatedAlt, 'http') ? $relatedAlt : url($relatedAlt);
            @endphp
            <a href="{{ route('products.show', ['slug' => $related->slug]) }}"
               x-data='{ current: @json($relImg) }'
               class="product-card group block bg-white border border-black overflow-hidden">
                <div class="product-media-frame relative w-full">
                    <img
                        src="{{ $relImg }}"
                        :src="current"
                        alt="{{ $related->title }}"
                        class="w-full h-full object-contain object-center group-hover:scale-105 transition-transform duration-500 ease-out"
                        style="position: absolute; inset: 0; width: 100%; height: 100%;"
                        loading="lazy"
                        onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&q=80';"
                    >
                </div>
                <div class="p-3 border-t border-black/10">
                    <p class="font-editorial font-bold text-[10px] uppercase tracking-wider text-black truncate mb-1">{{ $related->title }}</p>
                    <p class="font-editorial font-bold text-sm text-black">{{ $relPrice }}</p>
                </div>
            </a>
            @endforeach
        </div>

    </div>
</section>
@endif

@endsection
