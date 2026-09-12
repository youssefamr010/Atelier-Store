{{-- Category Rail — Minimal Horizontal Filter Bar & Quick Category Chips --}}
@php
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $displayCollections = \App\Models\Collection::where('status', 'active')
        ->withCount('products')
        ->orderBy('sort_order')
        ->take(10)
        ->get();
    $railCategoryCounts = $categoryCounts ?? [];
@endphp

@if($displayCollections->isNotEmpty())
<section class="bg-white border-b border-black/10 overflow-x-auto scrollbar-none py-2.5 px-3 sm:px-6 shadow-2xs">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center gap-2" style="min-width: max-content;">
            
            <a href="{{ route('collections.show', ['slug' => 'all']) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-black/20 hover:border-black text-black hover:bg-black hover:text-white transition-all text-[10px] font-editorial font-bold uppercase tracking-wider shrink-0 {{ request()->is('collections/all') || request()->routeIs('home') ? 'bg-black text-white border-black shadow-xs' : 'bg-[#F9F8F5]' }}">
                <span>{{ $isAr ? 'كافة المعروضات' : 'All Pieces' }}</span>
                <span class="font-mono text-[9px] opacity-75">({{ $railCategoryCounts['all'] ?? \App\Models\Product::active()->count() }})</span>
            </a>

            @foreach($displayCollections as $cat)
            @php
                $railCount = $railCategoryCounts[$cat->slug] ?? ($railCategoryCounts[$cat->id] ?? $cat->products_count);
                $isCatActive = request()->is('collections/' . $cat->slug);
            @endphp
            <a href="{{ route('collections.show', ['slug' => $cat->slug]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-black/15 hover:border-black text-black hover:bg-black hover:text-white transition-all text-[10px] font-editorial font-bold uppercase tracking-wider shrink-0 {{ $isCatActive ? 'bg-black text-white border-black shadow-xs' : 'bg-[#F9F8F5]' }}">
                <span class="truncate max-w-[150px]">
                    {{ $cat->title }}
                </span>
                <span class="font-mono text-[9px] opacity-75">({{ $railCount }})</span>
            </a>
            @endforeach

        </div>
    </div>
</section>
@endif
