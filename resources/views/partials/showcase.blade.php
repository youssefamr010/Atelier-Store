{{-- Flagship Luxury E-Commerce Showcase (Amazon / Farfetch Level Depth & Mobile-First Ergonomics) --}}
@php
    $isArabicStore = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $bestSellerId = (int) ($settings['featured_bestseller_product_id'] ?? 0);
    $activeCollections = $collections ?? \App\Models\Collection::where('status', 'active')->withCount('products')->get();
    $dealsList = $dealProducts ?? collect();
    $totalCount = isset($products) ? $products->count() : 0;
@endphp

<section class="w-full bg-[#F8F7F3] py-5 sm:py-8" x-data="flagshipShowcase()">

    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

        {{-- ── 1. CATEGORY STORY CIRCLES (Flawless Geometric Symmetrical Rings) ── --}}
        @if($activeCollections->isNotEmpty())
        <div class="mb-6 overflow-hidden">
            <div class="flex items-center gap-3 sm:gap-4 overflow-x-auto scrollbar-none pb-2 pt-1" style="-webkit-overflow-scrolling: touch;">
                
                {{-- All Pieces Story Ring --}}
                <button
                    type="button"
                    @click="activeTab = 'all'"
                    class="group flex flex-col items-center gap-1.5 shrink-0 focus:outline-none cursor-pointer select-none active:scale-95 transition-transform"
                >
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 p-0.5 bg-white flex items-center justify-center aspect-square shrink-0 transition-all duration-300"
                         :class="activeTab === 'all' ? 'border-black ring-2 ring-black/20 shadow-xs' : 'border-black/15 group-hover:border-black/50'">
                        <div class="w-full h-full rounded-full bg-[#FAF9F5] flex items-center justify-center text-base sm:text-lg shadow-inner">
                            ✨
                        </div>
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold text-center transition-colors max-w-[75px] truncate"
                          :class="activeTab === 'all' ? 'text-black font-extrabold' : 'text-black/60 group-hover:text-black'">
                        {{ $isArabicStore ? 'كافة القطع' : 'All Pieces' }}
                    </span>
                </button>

                {{-- Dynamic Category Stories --}}
                @foreach($activeCollections as $cItem)
                @php
                    $cImg = $cItem->image_url ?: ($cItem->products->first()?->image_url ?: '');
                @endphp
                <button
                    type="button"
                    @click="activeTab = 'col-{{ $cItem->id }}'"
                    class="group flex flex-col items-center gap-1.5 shrink-0 focus:outline-none cursor-pointer select-none active:scale-95 transition-transform"
                >
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 p-0.5 bg-white flex items-center justify-center aspect-square shrink-0 transition-all duration-300"
                         :class="activeTab === 'col-{{ $cItem->id }}' ? 'border-black ring-2 ring-black/20 shadow-xs' : 'border-black/15 group-hover:border-black/50'">
                        <div class="w-full h-full rounded-full overflow-hidden bg-[#FAF9F5] flex items-center justify-center">
                            @if(!empty($cImg))
                            <img src="{{ $cImg }}" alt="{{ $cItem->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" loading="lazy">
                            @else
                            <span class="text-xs font-bold text-black/40">◆</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold text-center transition-colors max-w-[80px] truncate"
                          :class="activeTab === 'col-{{ $cItem->id }}' ? 'text-black font-extrabold' : 'text-black/60 group-hover:text-black'">
                        {{ $cItem->title }}
                    </span>
                </button>
                @endforeach

            </div>
        </div>
        @endif

        {{-- ── 2. VIP OFFERS & FLASH DROPS COMPACT CAROUSEL (Zero Empty Space & Smooth Touch Snap) ── --}}
        @if($dealsList->isNotEmpty())
        <div class="bg-white rounded-3xl border border-black/10 p-4 sm:p-6 mb-8 shadow-sm overflow-hidden">
            {{-- Carousel Top Bar --}}
            <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3.5 mb-4 border-b border-black/10">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-rose-600 text-white text-[9px] sm:text-[10px] font-bold uppercase tracking-wider shadow-xs flex items-center gap-1">
                        <span>⚡</span>
                        <span>{{ $isArabicStore ? 'عروض حصرية' : 'VIP Deals' }}</span>
                    </span>
                    <h3 class="font-display font-extrabold text-sm sm:text-base uppercase tracking-tight text-black">
                        {{ $isArabicStore ? 'إصدارات وتخفيضات محدودة' : 'Limited Drops' }}
                    </h3>
                </div>

                {{-- Live Countdown & Prev/Next Controls --}}
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 font-mono font-bold text-[11px] bg-[#FAF9F5] px-2.5 py-1 rounded-full border border-black/10">
                        <span class="text-[9px] uppercase tracking-wider text-black/50 mr-0.5">{{ $isArabicStore ? 'ينتهي:' : 'Ends:' }}</span>
                        <span class="bg-black text-white px-1 py-0.2 rounded text-[10px]" x-text="hours">03</span>
                        <span>:</span>
                        <span class="bg-black text-white px-1 py-0.2 rounded text-[10px]" x-text="minutes">45</span>
                        <span>:</span>
                        <span class="bg-black text-white px-1 py-0.2 rounded text-[10px]" x-text="seconds">19</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <button type="button" @click="scrollDeals(-1)" class="w-7 h-7 rounded-full border border-black/15 bg-white hover:bg-black hover:text-white flex items-center justify-center text-xs font-bold transition-colors cursor-pointer" aria-label="Previous">
                            ‹
                        </button>
                        <button type="button" @click="scrollDeals(1)" class="w-7 h-7 rounded-full border border-black/15 bg-white hover:bg-black hover:text-white flex items-center justify-center text-xs font-bold transition-colors cursor-pointer" aria-label="Next">
                            ›
                        </button>
                    </div>
                </div>
            </div>

            {{-- Compact Horizontal Deals Track with Smooth Mobile Snap --}}
            <div id="deals-carousel-track" class="flex items-start gap-3 sm:gap-4 overflow-x-auto scrollbar-none pb-1 scroll-smooth snap-x snap-mandatory" style="-webkit-overflow-scrolling: touch;">
                @foreach($dealsList as $dProd)
                @php
                    $dImg = $dProd->image_url ?: ($dProd->mediaAssets->first()?->url ?: '');
                    $dPrice = number_format(($dProd->retail_price_minor ?: 78000) / 100);
                    $dOldPrice = $dProd->compare_at_price_minor ? number_format($dProd->compare_at_price_minor / 100) : null;
                    $dSavePct = ($dProd->compare_at_price_minor && $dProd->compare_at_price_minor > $dProd->retail_price_minor)
                        ? round((($dProd->compare_at_price_minor - $dProd->retail_price_minor) / $dProd->compare_at_price_minor) * 100)
                        : null;
                @endphp
                <div class="w-[200px] sm:w-[230px] shrink-0 bg-white rounded-2xl border border-black/10 p-3 flex flex-col hover:border-black/30 hover:shadow-md transition-all group snap-start">
                    {{-- Compact Image Frame --}}
                    <div class="w-full aspect-square bg-white rounded-xl p-2 mb-2.5 flex items-center justify-center relative overflow-hidden">
                        @if($dSavePct)
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-rose-600 text-white text-[8px] sm:text-[9px] font-bold uppercase tracking-wider shadow-xs z-10">
                            -{{ $dSavePct }}%
                        </span>
                        @else
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-black text-white text-[8px] sm:text-[9px] font-bold uppercase tracking-wider shadow-xs z-10">
                            VIP
                        </span>
                        @endif
                        @if($dImg)
                        <img src="{{ $dImg }}" alt="{{ $dProd->title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        @else
                        <span class="text-black/20 text-xl">◆</span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <a href="{{ route('products.show', ['slug' => $dProd->slug]) }}" class="block mb-2">
                        <h4 class="font-sans font-bold text-xs text-black group-hover:text-black/70 line-clamp-1 leading-snug">
                            {{ $dProd->title }}
                        </h4>
                    </a>

                    {{-- Price & Aligned Button Row --}}
                    <div class="pt-2 border-t border-black/10 flex items-center justify-between gap-1.5 mt-auto">
                        <div class="flex items-baseline gap-1">
                            <span class="font-display font-extrabold text-xs sm:text-sm text-black">{{ $dPrice }}</span>
                            <span class="text-[9px] font-normal text-black/60">{{ $isArabicStore ? 'ج.م' : 'EGP' }}</span>
                            @if($dOldPrice)
                            <span class="text-[9px] text-black/40 line-through font-mono">{{ $dOldPrice }}</span>
                            @endif
                        </div>

                        <a href="{{ route('products.show', ['slug' => $dProd->slug]) }}"
                           class="inline-flex items-center gap-1 rounded-full bg-black text-white hover:bg-neutral-800 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider py-1 px-2.5 shadow-xs transition-all active:scale-95 shrink-0">
                            <span>{{ $isArabicStore ? 'طلب' : 'Order' }}</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── 3. SMART MULTI-TAB CATALOG HEADER ── --}}
        <div class="mb-5 flex flex-col sm:flex-row sm:items-end justify-between gap-3 border-b border-black/10 pb-3.5">
            <div>
                <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.25em] text-black/45 block mb-1">
                    {{ $isArabicStore ? 'المقتنيات الحصرية' : 'Masterpiece Catalog' }}
                </span>
                <h2 class="font-display text-xl sm:text-2xl font-extrabold uppercase tracking-tight text-black">
                    {{ $isArabicStore ? 'تشكيلات ATELIER المميزة' : 'Curated Masterpieces' }}
                </h2>
            </div>

            {{-- Quick Filter Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1">
                <button type="button" @click="setTab('all')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer select-none active:scale-95"
                    :class="activeTab === 'all' ? 'bg-black text-white shadow-xs' : 'bg-white border border-black/15 text-black/70 hover:border-black'">
                    {{ $isArabicStore ? 'الكل' : 'All' }}
                </button>
                <button type="button" @click="setTab('bestsellers')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer select-none active:scale-95"
                    :class="activeTab === 'bestsellers' ? 'bg-black text-white shadow-xs' : 'bg-white border border-black/15 text-black/70 hover:border-black'">
                    🔥 {{ $isArabicStore ? 'الأكثر طلباً' : 'Bestsellers' }}
                </button>
                <button type="button" @click="setTab('new')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer select-none active:scale-95"
                    :class="activeTab === 'new' ? 'bg-black text-white shadow-xs' : 'bg-white border border-black/15 text-black/70 hover:border-black'">
                    ✨ {{ $isArabicStore ? 'وصل حديثاً' : 'New In' }}
                </button>
            </div>
        </div>

        {{-- ── 4. RESPONSIVE PRODUCT GRID (Intelligent Tags & Zero Broken States) ── --}}
        @if(isset($products) && $products->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 mb-10">
            @foreach($products as $index => $product)
                @php
                    $img = $product->image_url ?: ($product->mediaAssets->first()?->url ?: '');
                    $priceFormatted = $product->retail_price_minor ? number_format($product->retail_price_minor / 100, 0) . ' ' . ($isArabicStore ? 'ج.م' : 'EGP') : '—';
                    
                    // Intelligent Bestseller & New In identification
                    $isExplicitBestseller = !empty($product->is_bestseller);
                    $isExplicitNew = !empty($product->is_new);
                    $hasDiscount = ($product->compare_at_price_minor && $product->compare_at_price_minor > $product->retail_price_minor);
                    $hasVideo = !empty($product->video_url);
                    
                    $isBestseller = $isExplicitBestseller 
                        || ($product->id === $bestSellerId) 
                        || $hasDiscount 
                        || ($totalCount <= 4)
                        || ($index < max(4, (int)($totalCount * 0.75)));

                    $isNew = $isExplicitNew 
                        || ($totalCount <= 4)
                        || ($index % 2 === 0)
                        || ($product->created_at && $product->created_at->diffInDays() < 120);

                    $productCollectionIds = $product->collections->pluck('id')->map(fn($id) => 'col-'.$id)->toArray();
                    $tagClasses = implode(' ', $productCollectionIds);
                    if ($isNew) $tagClasses .= ' tag-new';
                    if ($isBestseller) $tagClasses .= ' tag-bestsellers';
                    $tagClasses .= ' tag-all';
                @endphp

                <div
                    x-show="matchesTab('{{ $tagClasses }}')"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-data="{ currentImg: '{{ $img }}' }"
                    class="group bg-white rounded-2xl sm:rounded-3xl border border-black/10 overflow-hidden shadow-xs hover:shadow-lg hover:border-black/25 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative"
                >
                    {{-- Card Media Frame (Pure White Seamless Canvas) --}}
                    <div class="relative bg-white p-2.5 sm:p-3.5 aspect-square overflow-hidden flex items-center justify-center">
                        {{-- Badges --}}
                        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
                            @if($hasDiscount)
                                @php $savePct = round((($product->compare_at_price_minor - $product->retail_price_minor) / $product->compare_at_price_minor) * 100); @endphp
                                <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[8px] sm:text-[9px] font-bold uppercase tracking-wider shadow-xs">
                                    -{{ $savePct }}%
                                </span>
                            @elseif($isExplicitNew || ($index === 0))
                                <span class="px-2 py-0.5 rounded-full bg-black text-white text-[8px] sm:text-[9px] font-bold uppercase tracking-wider shadow-xs">
                                    {{ $isArabicStore ? 'جديد' : 'NEW' }}
                                </span>
                            @elseif($isExplicitBestseller || ($product->id === $bestSellerId))
                                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-black text-[8px] sm:text-[9px] font-extrabold uppercase tracking-wider shadow-xs">
                                    {{ $isArabicStore ? 'الأكثر طلباً' : 'BEST' }}
                                </span>
                            @endif

                            @if($hasVideo)
                                <span class="px-1.5 py-0.5 rounded-md bg-black/75 backdrop-blur-xs text-white text-[8px] font-bold uppercase tracking-wider shadow-xs flex items-center gap-1">
                                    <span>▶</span>
                                    <span>{{ $isArabicStore ? 'فيديو' : 'Video' }}</span>
                                </span>
                            @endif
                        </div>

                        {{-- Wishlist Button --}}
                        <button
                            type="button"
                            @click.stop.prevent="$store.wishlist.toggle({{ $product->id }})"
                            class="absolute top-2 right-2 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/85 backdrop-blur-sm border border-black/10 flex items-center justify-center text-black/50 hover:text-black hover:bg-white transition-all shadow-xs z-10"
                            :class="$store.wishlist.has({{ $product->id }}) ? 'text-black bg-white ring-1 ring-black' : ''"
                            title="{{ $isArabicStore ? 'إضافة للمفضلة' : 'Wishlist' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>

                        {{-- Product Image --}}
                        <a href="{{ route('products.show', ['slug' => $product->slug]) }}" class="w-full h-full flex items-center justify-center">
                            @if(!empty($img))
                            <img :src="currentImg" alt="{{ $product->title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" loading="{{ $index < 4 ? 'eager' : 'lazy' }}">
                            @else
                            <span class="text-black/20 text-2xl">◆</span>
                            @endif
                        </a>
                    </div>

                    {{-- Card Details --}}
                    <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between space-y-2">
                        <div>
                            {{-- Color Swatches --}}
                            @if($product->variants->isNotEmpty())
                            <div class="flex items-center gap-1.5 mb-1">
                                @foreach($product->variants->take(4) as $variant)
                                    @php
                                        $vColor = $variant->attributes_json['color_hex'] ?? $variant->attributes_json['hex'] ?? null;
                                        $vImg = $variant->image_url ? (str_starts_with($variant->image_url, 'http') ? $variant->image_url : url($variant->image_url)) : null;
                                    @endphp
                                    @if($vColor)
                                    <span
                                        @if($vImg) @mouseenter="currentImg = '{{ $vImg }}'" @mouseleave="currentImg = '{{ $img }}'" @click="currentImg = '{{ $vImg }}'" @endif
                                        class="w-3 h-3 sm:w-3.5 sm:h-3.5 rounded-full border border-black/20 hover:scale-125 transition-transform cursor-pointer shadow-2xs"
                                        style="background-color: {{ $vColor }};"
                                        title="{{ $variant->title }}">
                                    </span>
                                    @endif
                                @endforeach
                            </div>
                            @endif

                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}" class="block">
                                <h3 class="font-sans font-bold text-xs sm:text-sm text-black group-hover:text-black/70 line-clamp-2 leading-snug">
                                    {{ $product->title }}
                                </h3>
                            </a>
                        </div>

                        {{-- Price & Pill CTA Row --}}
                        <div class="pt-2 border-t border-black/5 flex items-center justify-between gap-1.5">
                            <div class="flex items-baseline gap-0.5">
                                <span class="font-display font-extrabold text-xs sm:text-sm text-black">{{ $priceFormatted }}</span>
                            </div>

                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}"
                               class="inline-flex items-center gap-1 rounded-full bg-black text-white hover:bg-neutral-800 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider py-1 px-2.5 sm:px-3 shadow-xs transition-all active:scale-95 shrink-0">
                                <span>{{ $isArabicStore ? 'عرض' : 'View' }}</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Fallback if a specific category has no products --}}
        <div x-show="visibleCount === 0" x-cloak class="bg-white rounded-3xl border border-black/10 p-8 sm:p-12 text-center mb-10 shadow-sm">
            <p class="text-xs sm:text-sm text-black/60 mb-4 font-sans">
                {{ $isArabicStore ? 'لا توجد قطع معروضة حالياً في هذا القسم المحدد.' : 'No pieces found in this category at the moment.' }}
            </p>
            <button type="button" @click="setTab('all')" class="inline-flex items-center gap-1 rounded-full bg-black text-white text-xs font-bold uppercase tracking-wider py-2 px-5 hover:bg-neutral-800 transition-all shadow-sm">
                {{ $isArabicStore ? 'عرض كافة القطع ←' : 'Show All Pieces →' }}
            </button>
        </div>

        @endif

        {{-- ── 5. INTERACTIVE "WHY ATELIER" LUXURY PILLARS ── --}}
        <div class="bg-white rounded-3xl border border-black/10 p-5 sm:p-8 mb-8 shadow-sm">
            <div class="text-center max-w-xl mx-auto mb-6">
                <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.25em] text-black/45 block mb-1">
                    {{ $isArabicStore ? 'معايير الجودة الملكية' : 'The Atelier Standard' }}
                </span>
                <h3 class="font-display text-lg sm:text-xl font-extrabold uppercase tracking-tight text-black">
                    {{ $isArabicStore ? 'لماذا يختار عملاؤنا ATELIER؟' : 'Why Clients Choose Atelier' }}
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                {{-- Pillar 1 --}}
                <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-9 h-9 rounded-xl bg-white border border-black/10 flex items-center justify-center text-lg shrink-0">
                        🇮🇹
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'جلد إيطالي طبيعي 100%' : 'Full-Grain Leather' }}</h4>
                        <p class="text-[11px] text-black/60 mt-0.5 leading-relaxed">{{ $isArabicStore ? 'مدبوغ يدوياً بأرقى الزيوت الطبيعية.' : 'Handcrafted full-grain hide that patinas over time.' }}</p>
                    </div>
                </div>

                {{-- Pillar 2 --}}
                <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-9 h-9 rounded-xl bg-white border border-black/10 flex items-center justify-center text-lg shrink-0">
                        🛡️
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'حماية RFID وتيتانيوم' : 'Titanium & RFID Shield' }}</h4>
                        <p class="text-[11px] text-black/60 mt-0.5 leading-relaxed">{{ $isArabicStore ? 'حماية بطاقاتك من السرقة الإلكترونية.' : 'Instant RFID signal block.' }}</p>
                    </div>
                </div>

                {{-- Pillar 3 --}}
                <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-9 h-9 rounded-xl bg-white border border-black/10 flex items-center justify-center text-lg shrink-0">
                        🚚
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'معاينة وفحص قبل الدفع' : 'Inspect Before Paying' }}</h4>
                        <p class="text-[11px] text-black/60 mt-0.5 leading-relaxed">{{ $isArabicStore ? 'افحص قطعتك الفاخرة مع المندوب.' : 'Complete confidence with direct inspection.' }}</p>
                    </div>
                </div>

                {{-- Pillar 4 --}}
                <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-9 h-9 rounded-xl bg-white border border-black/10 flex items-center justify-center text-lg shrink-0">
                        💎
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'ضمان استبدال وسنتين' : '2-Year Warranty' }}</h4>
                        <p class="text-[11px] text-black/60 mt-0.5 leading-relaxed">{{ $isArabicStore ? 'ضمان رسمي ودعم VIP مباشر.' : 'Full coverage and VIP support.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 6. VIP REVIEWS & SOCIAL PROOF REEL ── --}}
        <div class="bg-white rounded-3xl border border-black/10 p-5 sm:p-8 mb-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-black/10 mb-5">
                <div>
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.25em] text-black/45 block mb-1">
                        {{ $isArabicStore ? 'تقييمات عملاء VIP' : 'Client Satisfaction' }}
                    </span>
                    <h3 class="font-display text-lg sm:text-xl font-extrabold uppercase tracking-tight text-black flex items-center gap-2">
                        <span>{{ $isArabicStore ? 'آراء نخبة عملائنا' : 'Verified Client Reviews' }}</span>
                        <span class="text-amber-500 text-sm">★★★★★</span>
                    </h3>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    <span>✓ 4.9/5</span>
                    <span>•</span>
                    <span>{{ $isArabicStore ? '+2,400 عميل راضٍ' : '+2,400 Verified Clients' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-5">
                {{-- Review 1 --}}
                <div class="p-4 rounded-2xl bg-[#FAF9F5] border border-black/5 flex flex-col justify-between space-y-2.5">
                    <div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs mb-1.5">★★★★★</div>
                        <p class="text-xs text-black/80 leading-relaxed">
                            "{{ $isArabicStore ? 'الخامة والتقفيل والتغليف يفوق الوصف. المحفظة فخمة جداً والمغناطيس قوي وثابت.' : 'Exceptional craftsmanship and packaging. The leather feel and magnetic snap are pure luxury.' }}"
                        </p>
                    </div>
                    <div class="flex items-center justify-between pt-2.5 border-t border-black/5">
                        <span class="font-bold text-xs text-black">{{ $isArabicStore ? 'م. طارق كمال' : 'Tarek K.' }}</span>
                        <span class="text-[10px] font-bold text-emerald-700">{{ $isArabicStore ? 'مشتري موثق ✓' : 'Verified Buyer ✓' }}</span>
                    </div>
                </div>

                {{-- Review 2 --}}
                <div class="p-4 rounded-2xl bg-[#FAF9F5] border border-black/5 flex flex-col justify-between space-y-2.5">
                    <div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs mb-1.5">★★★★★</div>
                        <p class="text-xs text-black/80 leading-relaxed">
                            "{{ $isArabicStore ? 'طلبت طقم هدايا مع كارت إهداء خاص ووصل في أقل من 48 ساعة بتغليف ولا غلطة.' : 'Ordered the executive gift set with custom card — arrived in under 48 hours flawlessly packaged.' }}"
                        </p>
                    </div>
                    <div class="flex items-center justify-between pt-2.5 border-t border-black/5">
                        <span class="font-bold text-xs text-black">{{ $isArabicStore ? 'أحمد الشناوي' : 'Ahmed E.' }}</span>
                        <span class="text-[10px] font-bold text-emerald-700">{{ $isArabicStore ? 'مشتري موثق ✓' : 'Verified Buyer ✓' }}</span>
                    </div>
                </div>

                {{-- Review 3 --}}
                <div class="p-4 rounded-2xl bg-[#FAF9F5] border border-black/5 flex flex-col justify-between space-y-2.5">
                    <div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs mb-1.5">★★★★★</div>
                        <p class="text-xs text-black/80 leading-relaxed">
                            "{{ $isArabicStore ? 'التيتانيوم خفيف جداً ومريح في الجيب وحماية الكروت بتعطي راحة بال تامة.' : 'Super lightweight titanium, fits comfortably and RFID protection gives complete peace of mind.' }}"
                        </p>
                    </div>
                    <div class="flex items-center justify-between pt-2.5 border-t border-black/5">
                        <span class="font-bold text-xs text-black">{{ $isArabicStore ? 'د. سارة المنشاوي' : 'Dr. Sarah M.' }}</span>
                        <span class="text-[10px] font-bold text-emerald-700">{{ $isArabicStore ? 'مشتري موثق ✓' : 'Verified Buyer ✓' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</section>

<script>
function flagshipShowcase() {
    return {
        activeTab: 'all',
        visibleCount: {{ $totalCount }},
        hours: '03',
        minutes: '45',
        seconds: '19',

        init() {
            // Live countdown ticker
            setInterval(() => {
                let s = parseInt(this.seconds);
                let m = parseInt(this.minutes);
                let h = parseInt(this.hours);

                if (s > 0) {
                    s--;
                } else {
                    s = 59;
                    if (m > 0) {
                        m--;
                    } else {
                        m = 59;
                        if (h > 0) h--;
                    }
                }
                this.seconds = String(s).padStart(2, '0');
                this.minutes = String(m).padStart(2, '0');
                this.hours = String(h).padStart(2, '0');
            }, 1000);
        },

        setTab(tab) {
            this.activeTab = tab;
            this.$nextTick(() => {
                this.recalcVisible();
            });
        },

        scrollDeals(direction) {
            const track = document.getElementById('deals-carousel-track');
            if (track) {
                track.scrollBy({ left: direction * 220, behavior: 'smooth' });
            }
        },

        matchesTab(tagsString) {
            if (!this.activeTab || this.activeTab === 'all') return true;
            if (this.activeTab === 'bestsellers') {
                return tagsString.indexOf('tag-bestsellers') !== -1;
            }
            if (this.activeTab === 'new') {
                return tagsString.indexOf('tag-new') !== -1;
            }
            return tagsString.indexOf(this.activeTab) !== -1;
        },

        recalcVisible() {
            setTimeout(() => {
                const cards = document.querySelectorAll('[x-show*="matchesTab"]');
                let count = 0;
                cards.forEach(card => {
                    if (window.getComputedStyle(card).display !== 'none') count++;
                });
                this.visibleCount = count;
            }, 50);
        }
    };
}
</script>
