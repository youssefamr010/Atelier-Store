{{-- Flagship Luxury E-Commerce Showcase (Amazon / Farfetch Level Depth & Ergonomics) --}}
@php
    $isArabicStore = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $bestSellerId = (int) ($settings['featured_bestseller_product_id'] ?? 0);
    $activeCollections = $collections ?? \App\Models\Collection::where('status', 'active')->withCount('products')->get();
@endphp

<section class="w-full bg-[#F8F7F3] py-6 sm:py-10" x-data="flagshipShowcase()">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── 1. CATEGORY STORY CIRCLES / QUICK NAVIGATION (Amazon & Instagram Style) ── --}}
        @if($activeCollections->isNotEmpty())
        <div class="mb-8 overflow-hidden">
            <div class="flex items-center gap-3 sm:gap-4 overflow-x-auto scrollbar-none pb-2 pt-1" style="-webkit-overflow-scrolling: touch;">
                
                {{-- All Pieces Story Pill --}}
                <button
                    type="button"
                    @click="activeTab = 'all'"
                    class="group flex flex-col items-center gap-1.5 shrink-0 focus:outline-none cursor-pointer"
                >
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-0.5 transition-all duration-300"
                         :class="activeTab === 'all' ? 'ring-2 ring-black bg-black' : 'ring-1 ring-black/15 bg-white group-hover:ring-black/40'">
                        <div class="w-full h-full rounded-full bg-[#FAF9F5] flex items-center justify-center text-xl sm:text-2xl shadow-inner">
                            ✨
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-center transition-colors max-w-[80px] truncate"
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
                    class="group flex flex-col items-center gap-1.5 shrink-0 focus:outline-none cursor-pointer"
                >
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-0.5 transition-all duration-300"
                         :class="activeTab === 'col-{{ $cItem->id }}' ? 'ring-2 ring-black bg-black' : 'ring-1 ring-black/15 bg-white group-hover:ring-black/40'">
                        <div class="w-full h-full rounded-full bg-[#FAF9F5] overflow-hidden flex items-center justify-center p-1">
                            @if(!empty($cImg))
                            <img src="{{ $cImg }}" alt="{{ $cItem->title }}" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-300" loading="lazy">
                            @else
                            <span class="text-xs font-bold text-black/40">◆</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-center transition-colors max-w-[85px] truncate"
                          :class="activeTab === 'col-{{ $cItem->id }}' ? 'text-black font-extrabold' : 'text-black/60 group-hover:text-black'">
                        {{ $cItem->title }}
                    </span>
                </button>
                @endforeach

            </div>
        </div>
        @endif

        {{-- ── 2. VIP SPOTLIGHT & FLASH DROP (High Conversion Scarcity Banner) ── --}}
        @if(isset($featuredProduct) && $featuredProduct)
        @php
            $fpImg = $featuredProduct->image_url ?: ($featuredProduct->mediaAssets->first()?->url ?: '');
            $fpPrice = number_format(($featuredProduct->retail_price_minor ?: 78000) / 100);
            $fpOldPrice = $featuredProduct->compare_at_price_minor ? number_format($featuredProduct->compare_at_price_minor / 100) : null;
        @endphp
        <div class="bg-white rounded-3xl border border-black/10 p-6 sm:p-8 mb-10 shadow-sm overflow-hidden relative">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center">
                
                {{-- Left / Media --}}
                <div class="lg:col-span-5 bg-[#FAF9F5] rounded-2xl p-4 sm:p-6 flex items-center justify-center relative overflow-hidden border border-black/5">
                    <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-black text-white text-[10px] font-bold uppercase tracking-wider shadow-sm z-10">
                        ⚡ {{ $isArabicStore ? 'إصدار محدود حصري' : 'Limited VIP Drop' }}
                    </span>
                    @if($fpImg)
                    <img src="{{ $fpImg }}" alt="{{ $featuredProduct->title }}" class="max-h-56 sm:max-h-64 object-contain hover:scale-105 transition-transform duration-500" loading="eager">
                    @else
                    <span class="text-4xl text-black/20">◆</span>
                    @endif
                </div>

                {{-- Right / Info & Countdown Timer --}}
                <div class="lg:col-span-7 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold text-amber-700 mb-1">
                            <span>🔥</span>
                            <span>{{ $isArabicStore ? 'الأكثر طلباً هذا الأسبوع • شحن فوري مجاني' : 'Top Choice This Week • Express VIP Dispatch' }}</span>
                        </div>
                        <h2 class="font-display font-extrabold text-2xl sm:text-3xl uppercase tracking-tight text-black leading-snug">
                            {{ $featuredProduct->title }}
                        </h2>
                        <p class="text-xs sm:text-sm text-black/65 font-sans mt-1.5 line-clamp-2 leading-relaxed">
                            {{ $featuredProduct->seo_description ?: Str::limit(strip_tags($featuredProduct->description ?? ''), 140) }}
                        </p>
                    </div>

                    {{-- Live Countdown Box --}}
                    <div class="bg-[#FAF9F5] rounded-2xl p-3.5 sm:p-4 border border-black/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-black/70">
                                {{ $isArabicStore ? 'ينتهي العرض الخاص خلال:' : 'Special Dispatch Offer Ends:' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 font-mono font-black text-xs sm:text-sm text-black">
                            <div class="bg-black text-white px-2.5 py-1 rounded-lg shadow-sm" x-text="hours">04</div>
                            <span>:</span>
                            <div class="bg-black text-white px-2.5 py-1 rounded-lg shadow-sm" x-text="minutes">32</div>
                            <span>:</span>
                            <div class="bg-black text-white px-2.5 py-1 rounded-lg shadow-sm" x-text="seconds">18</div>
                        </div>
                    </div>

                    {{-- Action Row --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                        <div class="flex items-baseline gap-2">
                            <span class="font-display text-2xl sm:text-3xl font-black text-black">{{ $fpPrice }}</span>
                            <span class="text-xs font-bold text-black/60">{{ $isArabicStore ? 'ج.م' : 'EGP' }}</span>
                            @if($fpOldPrice)
                            <span class="text-sm text-black/40 line-through font-mono">{{ $fpOldPrice }}</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('products.show', ['slug' => $featuredProduct->slug]) }}"
                               class="rounded-xl bg-black text-white font-display text-xs font-bold uppercase tracking-[0.18em] px-6 py-3.5 hover:bg-neutral-800 transition-all shadow-md active:scale-95 flex items-center justify-center gap-2">
                                <span>{{ $isArabicStore ? 'استكشف واطلب الآن ←' : 'Order Now →' }}</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif

        {{-- ── 3. SMART MULTI-TAB PRODUCT SHOWCASE (Instant Client-Side Filtering) ── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-black/10 pb-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-black/45 block mb-1">
                    {{ $isArabicStore ? 'المقتنيات الحصرية' : 'Masterpiece Catalog' }}
                </span>
                <h2 class="font-display text-2xl sm:text-3xl font-extrabold uppercase tracking-tight text-black">
                    {{ $isArabicStore ? 'تشكيلات ATELIER المميزة' : 'Curated Masterpieces' }}
                </h2>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-none">
                <button type="button" @click="activeTab = 'all'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer"
                    :class="activeTab === 'all' ? 'bg-black text-white shadow-sm' : 'bg-white border border-black/15 text-black/70 hover:border-black'">
                    {{ $isArabicStore ? 'الكل' : 'All' }}
                </button>
                <button type="button" @click="activeTab = 'bestsellers'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer"
                    :class="activeTab === 'bestsellers' ? 'bg-black text-white shadow-sm' : 'bg-white border border-black/15 text-black/70 hover:border-black'">
                    🔥 {{ $isArabicStore ? 'الأكثر طلباً' : 'Bestsellers' }}
                </button>
                <button type="button" @click="activeTab = 'new'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer"
                    :class="activeTab === 'new' ? 'bg-black text-white shadow-sm' : 'bg-white border border-black/15 text-black/70 hover:border-black'">
                    ✨ {{ $isArabicStore ? 'وصل حديثاً' : 'New In' }}
                </button>
            </div>
        </div>

        {{-- ── 4. RESPONSIVE PRODUCT GRID ── --}}
        @if(isset($products) && $products->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 mb-12">
            @foreach($products as $index => $product)
                @php
                    $img = $product->image_url ?: ($product->mediaAssets->first()?->url ?: '');
                    $priceFormatted = $product->retail_price_minor ? number_format($product->retail_price_minor / 100, 0) . ' ' . ($isArabicStore ? 'ج.م' : 'EGP') : '—';
                    $isNew = !empty($product->is_new);
                    $isBestseller = !empty($product->is_bestseller) || ($product->id === $bestSellerId);
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
                    class="group bg-white rounded-3xl border border-black/10 overflow-hidden shadow-sm hover:shadow-xl hover:border-black/25 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative"
                >
                    {{-- Card Media Frame --}}
                    <div class="relative bg-[#FAF9F5] p-3 sm:p-4 aspect-square overflow-hidden flex items-center justify-center">
                        {{-- Badges --}}
                        <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10">
                            @if($product->compare_at_price_minor && $product->compare_at_price_minor > $product->retail_price_minor)
                                @php $savePct = round((($product->compare_at_price_minor - $product->retail_price_minor) / $product->compare_at_price_minor) * 100); @endphp
                                <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-bold uppercase tracking-wider shadow-sm">
                                    -{{ $savePct }}%
                                </span>
                            @elseif($isNew)
                                <span class="px-2 py-0.5 rounded-full bg-black text-white text-[9px] font-bold uppercase tracking-wider shadow-sm">
                                    {{ $isArabicStore ? 'جديد' : 'NEW' }}
                                </span>
                            @elseif($isBestseller)
                                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-black text-[9px] font-extrabold uppercase tracking-wider shadow-sm">
                                    {{ $isArabicStore ? 'الأكثر طلباً' : 'BESTSELLER' }}
                                </span>
                            @endif
                        </div>

                        {{-- Wishlist Button --}}
                        <button
                            type="button"
                            @click.stop.prevent="$store.wishlist.toggle({{ $product->id }})"
                            class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm border border-black/10 flex items-center justify-center text-black/50 hover:text-black hover:bg-white transition-all shadow-sm z-10"
                            :class="$store.wishlist.has({{ $product->id }}) ? 'text-black bg-white ring-1 ring-black' : ''"
                            title="{{ $isArabicStore ? 'إضافة للمفضلة' : 'Wishlist' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>

                        {{-- Product Image --}}
                        <a href="{{ route('products.show', ['slug' => $product->slug]) }}" class="w-full h-full flex items-center justify-center">
                            @if(!empty($img))
                            <img :src="currentImg" alt="{{ $product->title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" loading="{{ $index < 4 ? 'eager' : 'lazy' }}">
                            @else
                            <span class="text-black/20 text-3xl">◆</span>
                            @endif
                        </a>
                    </div>

                    {{-- Card Details --}}
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-2.5">
                        <div>
                            {{-- Color Swatches --}}
                            @if($product->variants->isNotEmpty())
                            <div class="flex items-center gap-1.5 mb-1.5">
                                @foreach($product->variants->take(4) as $variant)
                                    @php
                                        $vColor = $variant->attributes_json['color_hex'] ?? $variant->attributes_json['hex'] ?? null;
                                        $vImg = $variant->image_url ? (str_starts_with($variant->image_url, 'http') ? $variant->image_url : url($variant->image_url)) : null;
                                    @endphp
                                    @if($vColor)
                                    <span
                                        @if($vImg) @mouseenter="currentImg = '{{ $vImg }}'" @mouseleave="currentImg = '{{ $img }}'" @click="currentImg = '{{ $vImg }}'" @endif
                                        class="w-3.5 h-3.5 rounded-full border border-black/20 hover:scale-125 transition-transform cursor-pointer shadow-2xs"
                                        style="background-color: {{ $vColor }};"
                                        title="{{ $variant->title }}">
                                    </span>
                                    @endif
                                @endforeach
                            </div>
                            @endif

                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}">
                                <h3 class="font-display font-bold text-xs sm:text-sm text-black group-hover:underline line-clamp-2 leading-tight">
                                    {{ $product->title }}
                                </h3>
                            </a>
                        </div>

                        {{-- Price & Quick CTA --}}
                        <div class="pt-2 border-t border-black/5 flex items-center justify-between gap-2">
                            <div class="flex items-baseline gap-1">
                                <span class="font-display font-extrabold text-xs sm:text-sm text-black">{{ $priceFormatted }}</span>
                            </div>

                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}"
                               class="rounded-xl bg-black text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 hover:bg-neutral-800 active:scale-95 transition-all shrink-0">
                                {{ $isArabicStore ? 'عرض' : 'View' }} →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- ── 5. INTERACTIVE "WHY ATELIER" LUXURY PILLARS ── --}}
        <div class="bg-white rounded-3xl border border-black/10 p-6 sm:p-10 mb-10 shadow-sm">
            <div class="text-center max-w-xl mx-auto mb-8">
                <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-black/45 block mb-1">
                    {{ $isArabicStore ? 'معايير الجودة الملكية' : 'The Atelier Standard' }}
                </span>
                <h3 class="font-display text-xl sm:text-2xl font-extrabold uppercase tracking-tight text-black">
                    {{ $isArabicStore ? 'لماذا يختار عملاؤنا ATELIER؟' : 'Why Clients Choose Atelier' }}
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Pillar 1 --}}
                <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-10 h-10 rounded-xl bg-white border border-black/10 flex items-center justify-center text-xl shrink-0">
                        🇮🇹
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'جلد إيطالي طبيعي 100%' : 'Full-Grain Italian Leather' }}</h4>
                        <p class="text-[11px] text-black/60 mt-1 leading-relaxed">{{ $isArabicStore ? 'مدبوغ يدوياً بأرقى الزيوت الطبيعية ليدوم طويلاً.' : 'Handcrafted full-grain hide that patinas beautifully over time.' }}</p>
                    </div>
                </div>

                {{-- Pillar 2 --}}
                <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-10 h-10 rounded-xl bg-white border border-black/10 flex items-center justify-center text-xl shrink-0">
                        🛡️
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'حماية RFID وتيتانيوم' : 'Titanium & RFID Shield' }}</h4>
                        <p class="text-[11px] text-black/60 mt-1 leading-relaxed">{{ $isArabicStore ? 'حماية بطاقاتك من السرقة الإلكترونية والأوزان الثقيلة.' : 'Aerospace titanium architecture and instant RFID signal block.' }}</p>
                    </div>
                </div>

                {{-- Pillar 3 --}}
                <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-10 h-10 rounded-xl bg-white border border-black/10 flex items-center justify-center text-xl shrink-0">
                        🚚
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'معاينة وفحص قبل الدفع' : 'Inspect Before Paying' }}</h4>
                        <p class="text-[11px] text-black/60 mt-1 leading-relaxed">{{ $isArabicStore ? 'افحص قطعتك الفاخرة وتأكد من جودتها مع المندوب.' : 'Complete confidence with direct inspection upon arrival.' }}</p>
                    </div>
                </div>

                {{-- Pillar 4 --}}
                <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-[#FAF9F5] border border-black/5">
                    <div class="w-10 h-10 rounded-xl bg-white border border-black/10 flex items-center justify-center text-xl shrink-0">
                        💎
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-black">{{ $isArabicStore ? 'ضمان استبدال وسنتين' : '2-Year Bespoke Warranty' }}</h4>
                        <p class="text-[11px] text-black/60 mt-1 leading-relaxed">{{ $isArabicStore ? 'ضمان رسمي كامل على كافة المنتجات والإكسسوارات.' : 'Full coverage and dedicated VIP concierge support.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 6. VIP REVIEWS & SOCIAL PROOF REEL ── --}}
        <div class="bg-white rounded-3xl border border-black/10 p-6 sm:p-10 mb-10 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-black/10 mb-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-black/45 block mb-1">
                        {{ $isArabicStore ? 'تقييمات عملاء VIP' : 'Client Satisfaction' }}
                    </span>
                    <h3 class="font-display text-xl sm:text-2xl font-extrabold uppercase tracking-tight text-black flex items-center gap-2">
                        <span>{{ $isArabicStore ? 'آراء نخبة عملائنا' : 'Verified Client Reviews' }}</span>
                        <span class="text-amber-500 text-sm">★★★★★</span>
                    </h3>
                </div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    <span>✓ 4.9/5</span>
                    <span>•</span>
                    <span>{{ $isArabicStore ? '+2,400 عميل راضٍ' : '+2,400 Verified Clients' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                {{-- Review 1 --}}
                <div class="p-5 rounded-2xl bg-[#FAF9F5] border border-black/5 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs mb-2">★★★★★</div>
                        <p class="text-xs text-black/80 leading-relaxed">
                            "{{ $isArabicStore ? 'الخامة والتقفيل والتغليف يفوق الوصف. المحفظة فخمة جداً والمغناطيس قوي وثابت.' : 'Exceptional craftsmanship and packaging. The leather feel and magnetic snap are pure luxury.' }}"
                        </p>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-black/5">
                        <span class="font-bold text-xs text-black">{{ $isArabicStore ? 'م. طارق كمال' : 'Tarek K.' }}</span>
                        <span class="text-[10px] font-bold text-emerald-700">{{ $isArabicStore ? 'مشتري موثق ✓' : 'Verified Buyer ✓' }}</span>
                    </div>
                </div>

                {{-- Review 2 --}}
                <div class="p-5 rounded-2xl bg-[#FAF9F5] border border-black/5 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs mb-2">★★★★★</div>
                        <p class="text-xs text-black/80 leading-relaxed">
                            "{{ $isArabicStore ? 'طلبت طقم هدايا مع كارت إهداء خاص ووصل في أقل من 48 ساعة بتغليف ولا غلطة.' : 'Ordered the executive gift set with custom card — arrived in under 48 hours flawlessly packaged.' }}"
                        </p>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-black/5">
                        <span class="font-bold text-xs text-black">{{ $isArabicStore ? 'أحمد الشناوي' : 'Ahmed E.' }}</span>
                        <span class="text-[10px] font-bold text-emerald-700">{{ $isArabicStore ? 'مشتري موثق ✓' : 'Verified Buyer ✓' }}</span>
                    </div>
                </div>

                {{-- Review 3 --}}
                <div class="p-5 rounded-2xl bg-[#FAF9F5] border border-black/5 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center gap-1 text-amber-500 text-xs mb-2">★★★★★</div>
                        <p class="text-xs text-black/80 leading-relaxed">
                            "{{ $isArabicStore ? 'التيتانيوم خفيف جداً ومريح في الجيب وحماية الكروت بتعطي راحة بال تامة.' : 'Super lightweight titanium, fits comfortably and RFID protection gives complete peace of mind.' }}"
                        </p>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-black/5">
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
        hours: '03',
        minutes: '48',
        seconds: '22',

        init() {
            // Live countdown timer ticker
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

        matchesTab(tagsString) {
            if (this.activeTab === 'all') return true;
            if (this.activeTab === 'bestsellers') return tagsString.includes('tag-bestsellers');
            if (this.activeTab === 'new') return tagsString.includes('tag-new');
            return tagsString.includes(this.activeTab);
        }
    };
}
</script>
