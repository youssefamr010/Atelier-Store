@extends('layouts.admin')

@section('title', 'Edit ' . $product->title)

@section('content')
@push('head-styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.smart-dropzone {
    border: 2px dashed #9ca3af;
    background-color: #ffffff;
    padding: 12px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 2px;
}
.smart-dropzone:hover {
    border-color: #000000;
    background-color: #f9fafb;
}
.smart-dropzone svg,
.smart-dropzone-icon {
    width: 24px !important;
    height: 24px !important;
    max-width: 24px !important;
    max-height: 24px !important;
    margin: 0 auto 4px auto !important;
    display: block !important;
}
.variant-gallery-preview-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}
.variant-gallery-preview-thumb {
    width: 64px !important;
    height: 64px !important;
    max-width: 64px !important;
    max-height: 64px !important;
    object-fit: cover !important;
    border: 2px solid #000;
}
.draggable-thumb {
    width: 68px !important;
    height: 68px !important;
    max-width: 68px !important;
    max-height: 68px !important;
    position: relative;
    border: 2px solid #000;
    background-color: #fff;
    padding: 2px;
    cursor: grab;
}
.draggable-thumb img {
    width: 60px !important;
    height: 60px !important;
    max-width: 60px !important;
    max-height: 60px !important;
    object-fit: cover !important;
}
</style>
<style>
/* ── Pipeline badge ── */
#pipeline-crop-badge { display: inline-flex; }
.atl-pipeline-active { border-color: #000 !important; background: #f9f9f6 !important; }
</style>
@endpush
@push('head-scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
@endpush
<div class="max-w-5xl space-y-8" x-data="productEditor()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b-2 border-black pb-4">
        <div>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black mb-1 block">
                ← Back to Products List
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black uppercase tracking-tight text-black">{{ $product->title }}</h1>
                <span class="text-[10px] font-mono px-2 py-0.5 border font-bold uppercase {{ $product->status === 'active' ? 'bg-green-100 text-green-800 border-green-600' : 'bg-gray-100 text-gray-700 border-gray-400' }}">
                    {{ $product->status }}
                </span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="border border-black bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors">
                ↗ View Live Page
            </a>
            <form action="{{ route('admin.products.delete', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete {{ addslashes($product->title) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="border border-red-600 text-red-600 px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-red-50">
                    <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg> Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Details Form -->
    <form id="product-main-form" action="{{ route('admin.products.update', $product->id) }}" method="POST" class="space-y-6" onsubmit="syncQuillEditor()">
        @csrf

        <!-- 1. Core Details -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">1. Essential Specifications</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Product Title *</label>
                    <input type="text" name="title" required value="{{ old('title', $product->title) }}" class="w-full border-2 border-black p-3 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Automatic product code</label>
                    <input type="text" value="{{ $product->sku }}" readonly class="w-full border-2 border-black/15 bg-gray-50 p-3 text-sm font-mono text-gray-700 cursor-not-allowed">
                    <p class="mt-1 text-[10px] text-gray-500">Updates automatically from the product title and first selected collection when you save.</p>
                </div>
            </div>

            <div class="rounded-xl border border-amber-400/60 bg-amber-50 p-4">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Private supplier product link</label>
                <input type="url" name="supplier_product_url" value="{{ old('supplier_product_url', $product->attributes_json['supplier_product_url'] ?? '') }}" placeholder="https://www.amazon.eg/... or your supplier link" class="w-full border border-black/30 bg-white p-3 text-xs font-mono focus:outline-none focus:ring-4 focus:ring-amber-200">
                <p class="mt-2 text-[10px] text-gray-600">Hidden from customers. Sent in the Telegram order notification for fast purchasing from your supplier.</p>
            </div>

            <!-- Rich Text Description (Quill.js) -->
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Description & Craftsmanship Story (Rich Text)</label>
                <div id="quill-editor" class="bg-white">{!! $product->description !!}</div>
                <input type="hidden" name="description" id="hidden-description" value="{{ old('description', $product->description) }}">
            </div>
        </div>

        <!-- 2. Dimensions & Materials Specifications -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">2. Materials & Technical Specifications</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Material Composition</label>
                    <input type="text" name="material" value="{{ old('material', $product->material) }}" placeholder="e.g. Tuscan Full-Grain Leather & Titanium" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Dimensions (L × W × H)</label>
                    <input type="text" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="e.g. 10.4 × 6.8 × 1.1 cm" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Weight</label>
                    <input type="text" name="weight" value="{{ old('weight', $product->weight) }}" placeholder="e.g. 74 grams" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 3. Pricing & Inventory -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">3. Pricing & Stock Control</h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Selling Price (EGP) *</label>
                    <input type="number" step="0.01" name="retail_price" required value="{{ old('retail_price', $product->retail_price_minor / 100) }}" class="w-full border-2 border-black p-3 text-sm font-mono focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Sale / Compare Price (EGP)</label>
                    <input type="number" step="0.01" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price_minor ? $product->compare_at_price_minor / 100 : '') }}" placeholder="e.g. 1800 (strikethrough)" class="w-full border-2 border-black p-3 text-sm font-mono focus:outline-none">
                    <span class="text-[10px] text-gray-400">Shows discount on storefront</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Cost Price (EGP)</label>
                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price_minor / 100) }}" class="w-full border-2 border-black p-3 text-sm font-mono focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Available Stock *</label>
                    <input type="number" name="inventory" required value="{{ old('inventory', $product->inventory) }}" min="0" class="w-full border-2 border-black p-3 text-sm font-mono focus:outline-none font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Low Stock Warning Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" placeholder="Leave blank to use global default" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                    <span class="text-[9px] text-gray-400">Triggers alert when stock $\le$ this number</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Catalog Status *</label>
                    <select name="status" class="w-full border-2 border-black p-2.5 text-xs bg-white font-bold focus:outline-none">
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active (Visible in Store)</option>
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Draft (Hidden)</option>
                        <option value="archived" {{ old('status', $product->status) === 'archived' ? 'selected' : '' }}><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg> Archived</option>
                    </select>
                </div>
            </div>

            <!-- Catalog Display Mode Selection (Per-Product) -->
            <div class="border-t border-gray-200 pt-4 mt-2">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1.5">Storefront Catalog Display Mode *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 border-2 border-black p-3 bg-gray-50 hover:bg-white cursor-pointer transition-colors shadow-xs {{ old('catalog_display_mode', $product->catalog_display_mode ?? 'separate_cards') === 'single_card' ? 'ring-2 ring-black bg-amber-50/50' : '' }}">
                        <input 
                            type="radio" 
                            name="catalog_display_mode" 
                            value="single_card" 
                            {{ old('catalog_display_mode', $product->catalog_display_mode ?? 'separate_cards') === 'single_card' ? 'checked' : '' }} 
                            class="mt-1 w-4 h-4 accent-black"
                        >
                        <div>
                            <span class="block text-xs font-black uppercase text-black">Single Card (Color Swatches)</span>
                            <span class="block text-[10px] text-gray-500 mt-0.5">Shows product as 1 unified card in the catalog grid with interactive color-swatch dots that dynamically switch photos, price, and stock live on the card.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 border-2 border-black p-3 bg-gray-50 hover:bg-white cursor-pointer transition-colors shadow-xs {{ old('catalog_display_mode', $product->catalog_display_mode ?? 'separate_cards') === 'separate_cards' ? 'ring-2 ring-black bg-amber-50/50' : '' }}">
                        <input 
                            type="radio" 
                            name="catalog_display_mode" 
                            value="separate_cards" 
                            {{ old('catalog_display_mode', $product->catalog_display_mode ?? 'separate_cards') === 'separate_cards' ? 'checked' : '' }} 
                            class="mt-1 w-4 h-4 accent-black"
                        >
                        <div>
                            <span class="block text-xs font-black uppercase text-black">Separate Cards (One per Color)</span>
                            <span class="block text-[10px] text-gray-500 mt-0.5">Renders each color variant as its own distinct card in the grid with sibling color preview swatches.</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- ── Badge Flags: New / Bestseller (Part 4) ── --}}
            <div class="border-t border-gray-200 pt-4 mt-2">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-2">Storefront Badge Flags</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2.5 border-2 border-emerald-600 px-4 py-2.5 bg-emerald-50 hover:bg-white cursor-pointer transition-colors text-xs font-bold">
                        <input type="checkbox" name="is_new" value="1"
                            {{ old('is_new', $product->is_new ?? false) ? 'checked' : '' }}
                            class="w-4 h-4 accent-emerald-600">
                        <span class="text-emerald-800 uppercase tracking-wider">🟢 NEW Badge</span>
                        <span class="text-[10px] text-gray-500 font-normal ml-1">(shows green "NEW" ribbon)</span>
                    </label>
                    <label class="flex items-center gap-2.5 border-2 border-amber-500 px-4 py-2.5 bg-amber-50 hover:bg-white cursor-pointer transition-colors text-xs font-bold">
                        <input type="checkbox" name="is_bestseller" value="1"
                            {{ old('is_bestseller', $product->is_bestseller ?? false) ? 'checked' : '' }}
                            class="w-4 h-4 accent-amber-500">
                        <span class="text-amber-800 uppercase tracking-wider">⭐ BESTSELLER Badge</span>
                        <span class="text-[10px] text-gray-500 font-normal ml-1">(shows amber "BEST" ribbon)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- 4. Collections -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b pb-2 flex items-center justify-between">
                <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 flex items-center gap-2">
                    <span><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg></span>
                    <span>4. Assigned Collections</span>
                </h2>
                <a href="{{ route('admin.collections.index') }}" target="_blank" class="text-[10px] font-bold uppercase tracking-wider text-gray-500 hover:text-black underline">
                    Manage Collections ↗
                </a>
            </div>

            @if($collections && $collections->count() > 0)
                <p class="text-xs text-gray-600">Select which categories / collections this product appears in:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @php $assignedIds = $product->collections->pluck('id')->toArray(); @endphp
                    @foreach($collections as $col)
                        <label class="flex items-center gap-2.5 border-2 border-black p-3 bg-gray-50 hover:bg-white cursor-pointer transition-colors text-xs font-bold shadow-sm">
                            <input 
                                type="checkbox" 
                                name="collection_ids[]" 
                                value="{{ $col->id }}" 
                                {{ in_array($col->id, $assignedIds) ? 'checked' : '' }} 
                                class="w-4 h-4 accent-black cursor-pointer"
                            >
                            <span>{{ $col->title }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="bg-amber-50 border-2 border-amber-400 p-4 text-xs text-amber-900 space-y-2">
                    <p class="font-bold uppercase">No collections created yet!</p>
                    <p>Create your first collection/category so you can organize your store products.</p>
                    <a href="{{ route('admin.collections.index') }}" class="inline-block bg-black text-white px-3 py-1.5 text-[10px] font-bold uppercase">
                        + Create Collections Now →
                    </a>
                </div>
            @endif
        </div>

        <!-- 5. Curated Related Products (Pinned Cross-Sell) -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b pb-2 flex items-center justify-between">
                <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 flex items-center gap-2">
                    <x-icon name="star" class="w-4 h-4 text-black" />
                    <span>5. Curated Related Products (Pinned Cross-Sell)</span>
                </h2>
            </div>
            <p class="text-xs text-gray-600">
                اختر منتجات محددة لتظهر أولاً في قسم "قد يعجبك أيضاً" بصفحة هذا المنتج (يملأ المتجر الباقي تلقائياً):
            </p>

            @if(isset($allOtherProducts) && $allOtherProducts->count() > 0)
                @php $pinnedIds = (array)($product->pinned_related_ids ?? []); @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto p-1 border border-gray-200 bg-gray-50">
                    @foreach($allOtherProducts as $otherP)
                        <label class="flex items-center gap-2.5 p-2 bg-white border border-gray-300 hover:border-black cursor-pointer text-xs font-bold transition-colors">
                            <input 
                                type="checkbox" 
                                name="pinned_related_ids[]" 
                                value="{{ $otherP->id }}"
                                {{ in_array($otherP->id, $pinnedIds) ? 'checked' : '' }}
                                class="w-4 h-4 accent-black cursor-pointer shrink-0"
                            >
                            <span class="truncate">{{ $otherP->title }}</span>
                            <span class="text-[10px] font-mono text-gray-400 shrink-0 ml-auto">{{ number_format($otherP->retail_price_minor / 100) }} EGP</span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-400 italic">No other products available in catalog.</p>
            @endif
        </div>

        <!-- 5. Search Engine Optimization (SEO) -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">5. Search Engine Optimization (SEO)</h2>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Custom Meta Title</label>
                <input type="text" name="seo_title" value="{{ old('seo_title', $product->seo_title) }}" placeholder="Leave blank to use product title" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Custom Meta Description</label>
                <textarea name="seo_description" rows="2" placeholder="Leave blank to generate automatically from product description" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">{{ old('seo_description', $product->seo_description) }}</textarea>
            </div>
        </div>

        <!-- Save Button -->
        <div>
            <button type="submit" class="bg-black text-white px-8 py-4 text-xs font-bold uppercase tracking-widest hover:bg-gray-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-transform active:translate-y-0.5">
                Save Product Changes →
            </button>
        </div>
    </form>

    <!-- 6. Smart Image Management & Bulk Multi-Upload -->
    <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-6">
        <div class="border-b-2 border-black pb-3 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
            <div>
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500">MEDIA STUDIO</span>
                <h2 class="text-xl font-black uppercase tracking-tight text-black">Product Images & Multi-Upload</h2>
            </div>
            <button type="button" onclick="document.getElementById('bulk-gallery-input').click()" class="bg-black text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg> Bulk Upload Photos
            </button>
        </div>

        <!-- Hidden Bulk Form -->
        <form id="bulk-gallery-form" action="{{ route('admin.products.bulk-gallery', $product->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" id="bulk-gallery-input" name="images[]" multiple accept="image/jpeg,image/png,image/webp" onchange="document.getElementById('bulk-gallery-form').submit()">
        </form>

        <!-- Grid of Images -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            
            <!-- Primary Cover Image Card -->
            <div class="border-2 border-black p-3 bg-gray-50 flex flex-col justify-between">
                <div>
                    <span class="inline-block bg-black text-white px-1.5 py-0.5 text-[9px] font-bold uppercase mb-2">PRIMARY COVER</span>
                    <div class="text-xs font-bold text-gray-900 mb-2 truncate">Cover Image — {{ $product->title }}</div>
                </div>

                @php
                    $coverSrc = $product->image_url ?: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500';
                    $coverUrl = str_starts_with($coverSrc, 'http') ? $coverSrc : url($coverSrc);
                @endphp

                <div 
                    class="relative aspect-square border border-black bg-white overflow-hidden cursor-pointer group hover:opacity-90 transition-all"
                    onclick="document.getElementById('cover-file-input').click()"
                    title="Click to replace Cover Image for {{ $product->title }}"
                >
                    <img id="cover-preview" src="{{ $coverUrl }}" alt="Cover Image — {{ $product->title }}" class="w-full h-full object-cover">
                    
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-2 text-center">
                        <span class="text-base mb-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg></span>
                        <span class="text-[10px] font-bold uppercase">Click to Replace</span>
                    </div>

                    <div id="cover-loader" class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center text-white" style="display: none;">
                        <div class="spinner mb-1"></div>
                        <span class="text-[9px] font-bold uppercase">Uploading...</span>
                    </div>
                </div>

                <form id="cover-form" action="{{ route('admin.products.replace-image', $product->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" id="cover-file-input" name="image" accept="image/jpeg,image/png,image/webp" onchange="uploadCoverImage(this)">
                </form>

                <div class="mt-2 text-[10px] text-gray-600 text-center font-bold uppercase">
                    Image for: {{ $product->title }}
                </div>
            </div>

            <!-- Gallery Images -->
            @foreach($product->mediaAssets as $index => $asset)
                @php
                    $assetSrc = $asset->url ?: ('/storage/media/' . $asset->filename);
                    $assetUrl = str_starts_with($assetSrc, 'http') ? $assetSrc : url($assetSrc);
                    $slotNum = $index + 1;
                @endphp
                <div class="border border-black p-3 bg-white flex flex-col justify-between group">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] font-bold uppercase bg-gray-100 px-1 py-0.5">GALLERY #{{ $slotNum }}</span>
                        <form action="{{ route('admin.products.delete-media', ['productId' => $product->id, 'assetId' => $asset->id]) }}" method="POST" class="inline" onsubmit="return confirm('Remove gallery image #{{ $slotNum }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-[10px] font-bold" title="Delete image">✕</button>
                        </form>
                    </div>

                    <div class="text-xs font-bold text-gray-900 mb-2 truncate">Gallery Image {{ $slotNum }} — {{ $product->title }}</div>

                    <div 
                        class="relative aspect-square border border-black bg-gray-50 overflow-hidden cursor-pointer hover:opacity-90 transition-all"
                        onclick="document.getElementById('gallery-file-{{ $asset->id }}').click()"
                        title="Click to replace Gallery Image {{ $slotNum }}"
                    >
                        <img id="gallery-preview-{{ $asset->id }}" src="{{ $assetUrl }}" alt="Gallery Image {{ $slotNum }} — {{ $product->title }}" class="w-full h-full object-cover">

                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-2 text-center">
                            <span class="text-base mb-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg></span>
                            <span class="text-[10px] font-bold uppercase">Click to Replace</span>
                        </div>
                    </div>

                    <form id="gallery-form-{{ $asset->id }}" action="{{ route('admin.media.replace', $asset->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="gallery-file-{{ $asset->id }}" name="image" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()">
                    </form>

                    <div class="mt-2 text-[10px] text-gray-500 text-center font-mono truncate">
                        {{ $asset->filename }}
                    </div>
                </div>
            @endforeach

            <!-- Drag & Drop Multi-Upload Box -->
            <div 
                class="border-2 border-dashed border-black p-4 flex flex-col items-center justify-center text-center bg-gray-50 hover:bg-gray-100 cursor-pointer min-h-[220px]" 
                onclick="document.getElementById('bulk-gallery-input').click()"
                ondragover="event.preventDefault(); this.classList.add('border-amber-500','bg-amber-50')"
                ondragleave="this.classList.remove('border-amber-500','bg-amber-50')"
                ondrop="event.preventDefault(); this.classList.remove('border-amber-500','bg-amber-50'); const d=new DataTransfer(); [...event.dataTransfer.files].filter(f=>f.type.startsWith('image/')).forEach(f=>d.items.add(f)); const i=document.getElementById('bulk-gallery-input'); i.files=d.files; if(i.files.length)i.form.submit();"
            >
                <span class="text-3xl block mb-2"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></span>
                <span class="text-xs font-black uppercase tracking-wider block">+ Drag & Drop Photos</span>
                <span class="text-[10px] text-gray-500 mt-1 block">Select multiple JPG, PNG, WebP files</span>
            </div>

        </div>
    </div>

    <!-- 7. Advanced Product Variants Architecture -->
    <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-6">
        <div class="border-b-2 border-black pb-3">
            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500">MULTI-ATTRIBUTE ARCHITECTURE</span>
            <h2 class="text-xl font-black uppercase tracking-tight text-black">Product Variants & Overrides</h2>
            <p class="text-xs text-gray-500 mt-0.5">Define variant options (e.g. Size, Color, Edition) with per-variant stock, price overrides, and photo binding.</p>
        </div>

        <!-- At-a-Glance Summary Health Strip -->
        @php
            $totalVars = $product->variants->count();
            $publishedVars = $product->variants->filter(fn($v) => $v->isPublished())->count();
            $draftVars = $product->variants->filter(fn($v) => $v->isDraft())->count();
            $outOfStockVars = $product->variants->where('inventory', '<=', 0)->count();
            $lowStockVars = $product->variants->where('inventory', '>', 0)->where('inventory', '<=', 5)->count();
            $incompleteVars = $product->variants->filter(fn($v) => empty($v->image_url) && empty($product->image_url))->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 bg-gray-50 border-2 border-black p-3 text-xs font-bold">
            <div class="p-2.5 bg-white border border-black shadow-xs">
                <span class="text-[9px] uppercase tracking-wider text-gray-500 block">Total Variants</span>
                <span id="summary-total-count" class="text-base font-black font-mono text-black">{{ $totalVars }}</span>
            </div>
            <div class="p-2.5 bg-white border border-black shadow-xs">
                <span class="text-[9px] uppercase tracking-wider text-emerald-700 block">Live / Published</span>
                <span id="summary-published-count" class="text-base font-black font-mono text-emerald-700">{{ $publishedVars }}</span>
            </div>
            <div class="p-2.5 bg-white border border-black shadow-xs">
                <span class="text-[9px] uppercase tracking-wider text-amber-700 block">Draft / Hidden</span>
                <span id="summary-draft-count" class="text-base font-black font-mono text-amber-700">{{ $draftVars }}</span>
            </div>
            <div class="p-2.5 bg-white border border-black shadow-xs">
                <span class="text-[9px] uppercase tracking-wider text-red-700 block">Out of Stock (0)</span>
                <span id="summary-outofstock-count" class="text-base font-black font-mono text-red-700">{{ $outOfStockVars }}</span>
            </div>
            <div class="p-2.5 bg-white border border-black shadow-xs col-span-2 sm:col-span-1">
                <span class="text-[9px] uppercase tracking-wider text-gray-600 block">Incomplete / No Photo</span>
                <span id="summary-incomplete-count" class="text-base font-black font-mono {{ $incompleteVars > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $incompleteVars }}</span>
            </div>
        </div>

        <!-- Bulk Action Management Bar -->
        @if($product->variants->count() > 0)
            <div class="bg-gray-100 border-2 border-black p-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5">
                    <input type="checkbox" id="select-all-variants" onchange="toggleSelectAllVariants(this)" class="w-4 h-4 border-black text-black cursor-pointer">
                    <label for="select-all-variants" class="font-bold uppercase tracking-wider text-black cursor-pointer">Select All Variants</label>
                    <span id="selected-variants-count" class="font-mono text-gray-500 font-bold">(0 selected)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase text-gray-600 mr-1">Bulk Actions:</span>
                    <button type="button" onclick="executeBulkVariantStatus('publish')" class="bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1.5 font-bold uppercase text-[11px] shadow-xs cursor-pointer">
                        Publish Selected Live
                    </button>
                    <button type="button" onclick="executeBulkVariantStatus('draft')" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 font-bold uppercase text-[11px] shadow-xs cursor-pointer">
                        Move to Draft
                    </button>
                    <button type="button" onclick="executeBulkVariantStatus('archive')" class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1.5 font-bold uppercase text-[11px] shadow-xs cursor-pointer">
                        Archive Selected
                    </button>
                </div>
            </div>
        @endif

        @if($product->variants->count() > 0)
            <div id="variant-cards-container" class="space-y-6">
                @foreach($product->variants as $vIndex => $variant)
                    @php 
                        $variantColor = $variant->attributes_json['color_hex'] ?? '#1A1A1A'; 
                        $vImgSrc = $variant->image_url ?: ($product->image_url ?: '');
                        $vImgUrl = $vImgSrc ? (str_starts_with($vImgSrc, 'http') ? $vImgSrc : url($vImgSrc)) : '';
                        $publishErrors = $variant->getPublishValidationErrors();
                        $isPublishReady = empty($publishErrors);
                    @endphp
                    <div 
                        id="variant-card-{{ $variant->id }}" 
                        data-variant-title="{{ strtolower($variant->title) }}" 
                        data-variant-sku="{{ strtolower($variant->sku) }}" 
                        data-variant-color="{{ strtolower($variantColor) }}"
                        class="border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] space-y-4 transition-all"
                    >
                        <form id="var-form-{{ $variant->id }}" action="{{ route('admin.products.update-variant', $variant->id) }}" method="POST" enctype="multipart/form-data" onsubmit="saveVariantAjax(event, '{{ $variant->id }}')" class="space-y-4">
                            @csrf
                            
                            <!-- Card Header Bar: Selection, Status & SKU -->
                            <div class="flex items-center justify-between border-b pb-2.5">
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        name="selected_variants[]" 
                                        value="{{ $variant->id }}" 
                                        onchange="updateSelectedVariantsCount()" 
                                        class="var-item-checkbox w-4 h-4 border-black text-black cursor-pointer"
                                    >
                                    <span class="text-xs font-black uppercase text-black">Variant #{{ $vIndex + 1 }}: <span class="text-neutral-800">{{ $variant->title }}</span></span>
                                    
                                    <!-- Dynamic Publish Status Badge -->
                                    <span id="var-status-badge-{{ $variant->id }}" class="text-[9px] font-mono font-bold uppercase px-2 py-0.5 rounded border {{ $variant->isPublished() ? 'bg-emerald-100 text-emerald-900 border-emerald-400' : ($variant->isDraft() ? 'bg-amber-100 text-amber-900 border-amber-400' : 'bg-gray-200 text-gray-800 border-gray-400') }}">
                                        {{ $variant->isPublished() ? '● Published' : ($variant->isDraft() ? '○ Draft (Hidden)' : 'Archived') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-mono font-bold text-gray-500 uppercase">SKU: {{ $variant->sku }}</span>
                                </div>
                            </div>

                            <!-- 1. Header row (fields side by side, same row, aligned, equal height inputs) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start bg-gray-50 p-3 border border-gray-200">
                                <!-- Attribute & Value -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Attribute : Value</label>
                                        <span id="color-suggest-badge-{{ $variant->id }}" onclick="applySuggestedName('var-title-{{ $variant->id }}', this.dataset.name, '{{ $variant->id }}')" class="hidden cursor-pointer bg-amber-100 text-amber-900 border border-amber-400 text-[8px] font-bold uppercase px-1.5 py-0.5 rounded hover:bg-amber-200 transition-all" title="Click to apply suggested name">
                                            Suggest: <span class="suggest-text"></span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" name="attribute_name" value="{{ $variant->attribute_name ?? 'Color' }}" placeholder="Attr" tabindex="{{ $vIndex * 4 + 1 }}" class="w-20 border border-black p-2 text-xs bg-white font-bold h-9">
                                        <span class="font-bold text-gray-400">:</span>
                                        <input type="text" id="var-title-{{ $variant->id }}" name="title" value="{{ $variant->title }}" placeholder="e.g. Chalk Pink" required tabindex="{{ $vIndex * 4 + 2 }}" onkeydown="if(event.key==='Enter'){event.preventDefault(); saveVariantAjax(null, '{{ $variant->id }}');}" class="min-w-0 flex-1 border border-black p-2 text-xs bg-white font-bold h-9">
                                    </div>
                                </div>

                                <!-- Price Override -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Price Override (EGP)</label>
                                    </div>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        name="price_override" 
                                        value="{{ $variant->price_override_minor ? $variant->price_override_minor / 100 : '' }}" 
                                        placeholder="Base ({{ number_format($product->retail_price_minor / 100, 0) }})" 
                                        tabindex="{{ $vIndex * 4 + 3 }}"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault(); saveVariantAjax(null, '{{ $variant->id }}');}"
                                        class="w-full border border-black p-2 text-xs bg-white font-mono h-9"
                                    >
                                    <span class="text-[9px] text-gray-500 font-mono block mt-0.5">Blank inherits base price ({{ number_format($product->retail_price_minor / 100, 0) }} EGP)</span>
                                </div>

                                <!-- Stock -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Stock Inventory</label>
                                        <span class="text-[9px] font-mono font-bold {{ $variant->inventory > 5 ? 'text-emerald-700' : ($variant->inventory > 0 ? 'text-amber-700' : 'text-red-600') }}">
                                            {{ $variant->inventory > 5 ? 'Healthy' : ($variant->inventory > 0 ? 'Low' : 'Out of Stock') }}
                                        </span>
                                    </div>
                                    <input 
                                        type="number" 
                                        name="inventory" 
                                        value="{{ $variant->inventory }}" 
                                        min="0" 
                                        required 
                                        tabindex="{{ $vIndex * 4 + 4 }}"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault(); saveVariantAjax(null, '{{ $variant->id }}');}"
                                        class="w-full border border-black p-2 text-xs bg-white font-mono font-bold h-9"
                                    >
                                </div>

                                <!-- Color Hex & Eyedropper -->
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Color & Eyedropper</label>
                                    <div class="flex items-center gap-1.5">
                                        <input 
                                            type="color" 
                                            id="var-color-picker-{{ $variant->id }}" 
                                            value="{{ $variantColor }}" 
                                            oninput="handleColorInputChange('{{ $variant->id }}', this.value)" 
                                            class="w-9 h-9 border border-black p-0.5 bg-white cursor-pointer shrink-0"
                                        >
                                        <input 
                                            type="text" 
                                            id="var-color-hex-{{ $variant->id }}" 
                                            name="color_hex" 
                                            value="{{ $variantColor }}" 
                                            oninput="handleColorHexTextInput('{{ $variant->id }}', this.value)" 
                                            class="min-w-0 flex-1 border border-black p-2 text-xs font-mono uppercase bg-white h-9"
                                        >
                                        <button 
                                            type="button" 
                                            onclick="pickColorFromImage('var-color-hex-{{ $variant->id }}', 'var-color-picker-{{ $variant->id }}', 'var-img-preview-{{ $variant->id }}', '{{ $variant->id }}')" 
                                            title="Pick color from image (Eyedropper)" 
                                            class="w-9 h-9 border border-black bg-white hover:bg-gray-100 flex items-center justify-center shrink-0 shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] active:translate-y-0.5 cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m2 22 1-1h3l9-9"/>
                                                <path d="M16.5 4.5a2.121 2.121 0 0 1 3 3L7 20l-4 1 1-4L16.5 4.5z"/>
                                                <circle cx="19" cy="5" r="2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Duplicate Warning Banner -->
                            <div id="dup-warning-{{ $variant->id }}" class="hidden text-xs font-bold text-amber-900 bg-amber-50 border-2 border-amber-400 p-2.5 rounded shadow-sm">
                                <span class="dup-msg"></span>
                            </div>

                            <!-- 2. Integrated Visual Photo Binding & Gallery Uploader -->
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Dedicated Variant Cover & Gallery Photos</label>
                                    <span class="text-[9px] text-gray-500 font-mono">Drag photos below to upload directly to this variant</span>
                                </div>

                                <div class="p-3 border border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-start gap-4">
                                    <!-- Bounded Reasonable-Sized Thumbnail (Strictly Max 140px) -->
                                    <div class="w-32 h-32 sm:w-36 sm:h-36 max-w-[144px] max-h-[144px] aspect-square border-2 border-black bg-white flex items-center justify-center overflow-hidden shrink-0 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] relative group">
                                        <img 
                                            id="var-img-preview-{{ $variant->id }}" 
                                            src="{{ $vImgUrl ?: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=300&q=80' }}" 
                                            class="variant-cover-preview-img w-full h-full object-contain p-1" 
                                            alt="{{ $variant->title }}"
                                            crossOrigin="anonymous"
                                        >
                                        <div class="absolute bottom-1 right-1 bg-black/80 text-white text-[8px] font-mono px-1 py-0.5 uppercase">
                                            Cover
                                        </div>
                                    </div>

                                    <!-- Controls column -->
                                    <div class="flex-1 w-full space-y-2">
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Cover Photo Selection</label>
                                            </div>
                                            <select 
                                                id="var-img-select-{{ $variant->id }}" 
                                                name="image_url" 
                                                onchange="updateVariantImgPreview(this, '{{ $variant->id }}')" 
                                                class="w-full border border-black p-2 text-xs bg-white font-mono"
                                            >
                                                <option value="">Default Product Cover</option>
                                                @if($variant->image_url)
                                                    <option value="{{ $variant->image_url }}" selected>Dedicated Variant Cover (Active)</option>
                                                @endif
                                                @if($product->image_url)
                                                    <option value="{{ $product->image_url }}" {{ $variant->image_url === $product->image_url ? 'selected' : '' }}>Product Primary Cover</option>
                                                @endif
                                                @foreach($product->mediaAssets as $gIdx => $mAsset)
                                                    <option value="{{ $mAsset->url }}" {{ $variant->image_url === $mAsset->url ? 'selected' : '' }}>Product Gallery Photo #{{ $gIdx + 1 }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Direct upload button -->
                                        <div class="flex items-center gap-2 pt-1">
                                            <button 
                                                type="button" 
                                                onclick="document.getElementById('var-cover-file-{{ $variant->id }}').click()" 
                                                class="border border-black bg-white hover:bg-gray-100 text-black px-3 py-1.5 text-xs font-bold uppercase shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] transition-all cursor-pointer flex items-center gap-1"
                                            >
                                                <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                                <span>Upload Dedicated Cover</span>
                                            </button>
                                            <input 
                                                type="file" 
                                                id="var-cover-file-{{ $variant->id }}" 
                                                name="image_file" 
                                                accept="image/jpeg,image/png,image/webp" 
                                                onchange="handleVariantCoverUpload(this, '{{ $variant->id }}')" 
                                                class="hidden"
                                            >
                                            <span id="var-cover-name-{{ $variant->id }}" class="text-[10px] text-gray-500 font-mono truncate max-w-xs"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Smart Drag-and-Drop Gallery Uploader with Instant Client Thumbnails -->
                                <div class="mt-3 bg-gray-50 p-3 border border-gray-200 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-800">Variant Photos & Gallery:</label>
                                        <span class="text-[9px] font-mono text-gray-500">Drop files or click zone</span>
                                    </div>

                                    <!-- Drop Zone with strict icon constraints -->
                                    <div 
                                        id="var-dropzone-{{ $variant->id }}" 
                                        onclick="document.getElementById('var-gallery-input-{{ $variant->id }}').click()" 
                                        class="smart-dropzone border-2 border-dashed border-gray-400 hover:border-black bg-white p-3.5 text-center cursor-pointer transition-all hover:bg-gray-50"
                                    >
                                        <input 
                                            type="file" 
                                            id="var-gallery-input-{{ $variant->id }}" 
                                            name="variant_gallery_images[]" 
                                            multiple 
                                            accept="image/jpeg,image/png,image/webp" 
                                            onchange="handleSmartVariantFiles(this, '{{ $variant->id }}')" 
                                            class="hidden"
                                        >
                                        <div class="space-y-1 pointer-events-none text-center">
                                            <svg width="24" height="24" class="smart-dropzone-icon text-gray-500 mx-auto" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; display: block; margin: 0 auto 4px auto;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                            <p class="text-xs font-bold text-black uppercase tracking-wider">Drag & drop photos here or <span class="underline">click to browse</span></p>
                                            <p class="text-[10px] text-gray-500 font-mono">Supports JPG, PNG, WebP • Auto-compressed • Click Save to persist</p>
                                        </div>
                                    </div>

                                    <!-- Undo Toast Notification for Accidental Uploads -->
                                    <div id="var-undo-toast-{{ $variant->id }}" class="hidden flex items-center justify-between bg-black text-white text-xs px-3 py-2 border border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                        <span class="font-mono text-[11px]"><span id="var-undo-count-{{ $variant->id }}">0</span> photo(s) queued for upload.</span>
                                        <button type="button" onclick="undoQueuedVariantUploads('{{ $variant->id }}')" class="text-amber-400 font-bold underline uppercase text-[10px] hover:text-amber-300 cursor-pointer">
                                            Undo (Clear Queue)
                                        </button>
                                    </div>

                                    <!-- Duplicate Photo Warning Box -->
                                    <div id="var-dup-img-warning-{{ $variant->id }}" class="hidden text-xs font-bold text-amber-900 bg-amber-50 border-2 border-amber-400 p-2.5 rounded shadow-sm">
                                        <span class="dup-img-msg"></span>
                                    </div>

                                    <!-- Saved Gallery Photos List -->
                                    <div class="space-y-1.5">
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-gray-600 block">Saved Photos Attached to this Variant:</span>
                                        <div id="var-gallery-saved-list-{{ $variant->id }}" class="flex flex-wrap gap-2.5">
                                            @if($variant->mediaAssets->count() > 0)
                                                @foreach($variant->mediaAssets as $asset)
                                                    <div class="relative group border-2 border-black bg-white p-0.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                                        <img 
                                                            src="{{ $asset->url ? (str_starts_with($asset->url, 'http') ? $asset->url : url($asset->url)) : url('/storage/media/'.$asset->filename) }}" 
                                                            loading="lazy" 
                                                            class="variant-gallery-preview-thumb w-20 h-20 object-cover" 
                                                            title="{{ $asset->filename }}"
                                                            crossOrigin="anonymous"
                                                            style="width: 80px; height: 80px; object-fit: cover;"
                                                        >
                                                        <button 
                                                            type="button" 
                                                            onclick="if(confirm('Remove this photo from gallery?')) document.getElementById('del-var-media-{{ $variant->id }}-{{ $asset->id }}').submit();" 
                                                            class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold hover:bg-red-800 shadow transition-transform hover:scale-110" 
                                                            title="Remove photo"
                                                        >
                                                            &times;
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-[10px] text-gray-400 italic">No extra gallery photos attached yet.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Actions row inside card -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span id="var-saved-badge-{{ $variant->id }}" class="hidden text-xs font-bold text-emerald-800 bg-emerald-100 border border-emerald-400 px-2.5 py-1 rounded shadow-xs">
                                        ✓ Saved Successfully
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap justify-end">
                                    <!-- Publish / Unpublish Button -->
                                    @if($variant->isPublished())
                                        <button 
                                            type="button" 
                                            onclick="toggleVariantPublish('{{ $variant->id }}', 'unpublish')" 
                                            id="publish-btn-{{ $variant->id }}"
                                            class="border border-amber-600 bg-amber-50 hover:bg-amber-100 text-amber-900 px-3.5 py-2 text-xs font-bold uppercase shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-1.5 cursor-pointer"
                                            title="Move to Draft (hide from storefront)"
                                        >
                                            <span>Unpublish (Draft)</span>
                                        </button>
                                    @else
                                        <button 
                                            type="button" 
                                            onclick="toggleVariantPublish('{{ $variant->id }}', 'publish')" 
                                            id="publish-btn-{{ $variant->id }}"
                                            @if(!$isPublishReady) disabled title="{{ implode(' ', $publishErrors) }}" @endif
                                            class="{{ $isPublishReady ? 'bg-emerald-700 hover:bg-emerald-800 text-white cursor-pointer shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]' : 'bg-gray-300 text-gray-500 cursor-not-allowed border border-gray-400' }} px-4 py-2 text-xs font-bold uppercase transition-all flex items-center gap-1.5"
                                        >
                                            <span>Publish Live</span>
                                        </button>
                                    @endif

                                    <button type="submit" class="bg-black text-white px-5 py-2 text-xs font-bold uppercase hover:bg-gray-800 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-1.5 cursor-pointer">
                                        <span>Save Variant</span>
                                    </button>
                                    <button type="button" onclick="document.getElementById('dup-var-{{ $variant->id }}').submit();" class="border border-black bg-white hover:bg-gray-100 text-black px-4 py-2 text-xs font-bold uppercase shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-1.5 cursor-pointer" title="Duplicate variant">
                                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Duplicate
                                    </button>
                                    <button type="button" onclick="if(confirm('Delete variant «{{ addslashes($variant->title) }}»?')) document.getElementById('del-var-{{ $variant->id }}').submit();" class="border border-red-600 text-red-600 hover:bg-red-50 px-4 py-2 text-xs font-bold uppercase transition-all cursor-pointer">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </form>

                        <form id="del-var-{{ $variant->id }}" action="{{ route('admin.products.delete-variant', $variant->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>

                        <form id="dup-var-{{ $variant->id }}" action="{{ route('admin.products.duplicate-variant', $variant->id) }}" method="POST" class="hidden">
                            @csrf
                        </form>

                        @foreach($variant->mediaAssets as $vAsset)
                            <form id="del-var-media-{{ $variant->id }}-{{ $vAsset->id }}" action="{{ route('admin.variants.delete-media', ['variantId' => $variant->id, 'assetId' => $vAsset->id]) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Redesigned Add Variant Section with Live Mini-Preview & Inline Validation (Section 3) -->
        <div class="pt-4 border-t-2 border-black">
            <h3 class="text-xs font-black uppercase tracking-wider text-black mb-3">+ Add New Variant</h3>
            <div class="border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <form id="add-new-variant-form" action="{{ route('admin.products.add-variant', $product->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return handleNewVariantSubmit(event)">
                    @csrf
                    
                    <div class="flex flex-col lg:flex-row gap-6 items-start">
                        <!-- Left Form Fields Column (8 cols) -->
                        <div class="flex-1 min-w-0 space-y-4">
                            <!-- 1. Header Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start bg-gray-50 p-3 border border-gray-200">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Attribute : Value *</label>
                                        <span id="color-suggest-badge-new" onclick="applySuggestedName('new-var-title', this.dataset.name, 'new')" class="hidden cursor-pointer bg-amber-100 text-amber-900 border border-amber-400 text-[8px] font-bold uppercase px-1.5 py-0.5 rounded hover:bg-amber-200 transition-all">
                                            Suggest: <span class="suggest-text"></span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" name="attribute_name" value="Color" required placeholder="Attr" class="w-20 border border-black p-2 text-xs bg-white font-bold h-9">
                                        <span class="font-bold text-gray-400">:</span>
                                        <input 
                                            type="text" 
                                            id="new-var-title" 
                                            name="title" 
                                            required 
                                            placeholder="e.g. Navy Blue" 
                                            oninput="syncNewVariantMiniPreview()" 
                                            class="min-w-0 flex-1 border border-black p-2 text-xs bg-white font-bold h-9"
                                        >
                                    </div>
                                    <span id="err-new-title" class="hidden text-[9px] font-bold text-red-600 block mt-0.5">Required: Color name</span>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Price Override (EGP)</label>
                                    </div>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        id="new-var-price-input" 
                                        name="price_override" 
                                        placeholder="Base ({{ number_format($product->retail_price_minor / 100, 0) }})" 
                                        oninput="syncNewVariantMiniPreview()" 
                                        class="w-full border border-black p-2 text-xs bg-white font-mono h-9"
                                    >
                                    <span class="text-[9px] text-gray-500 font-mono block mt-0.5">Blank inherits base price ({{ number_format($product->retail_price_minor / 100, 0) }} EGP)</span>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Initial Stock *</label>
                                    <input 
                                        type="number" 
                                        id="new-var-stock-input" 
                                        name="inventory" 
                                        value="10" 
                                        min="0" 
                                        required 
                                        oninput="syncNewVariantMiniPreview()" 
                                        class="w-full border border-black p-2 text-xs bg-white font-mono font-bold h-9"
                                    >
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Color & Eyedropper *</label>
                                    <div class="flex items-center gap-1.5">
                                        <input 
                                            type="color" 
                                            id="new-var-color-picker" 
                                            value="#1A1A1A" 
                                            oninput="handleColorInputChange('new', this.value); syncNewVariantMiniPreview();" 
                                            class="w-9 h-9 border border-black p-0.5 bg-white cursor-pointer shrink-0"
                                        >
                                        <input 
                                            type="text" 
                                            id="new-var-color-hex" 
                                            name="color_hex" 
                                            value="#1A1A1A" 
                                            oninput="handleColorHexTextInput('new', this.value); syncNewVariantMiniPreview();" 
                                            class="min-w-0 flex-1 border border-black p-2 text-xs font-mono uppercase bg-white h-9"
                                        >
                                        <button 
                                            type="button" 
                                            onclick="pickColorFromImage('new-var-color-hex', 'new-var-color-picker', 'new-var-mini-img', 'new')" 
                                            title="Pick color from product image (Eyedropper)" 
                                            class="w-9 h-9 border border-black bg-white hover:bg-gray-100 flex items-center justify-center shrink-0 shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] active:translate-y-0.5 cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m2 22 1-1h3l9-9"/>
                                                <path d="M16.5 4.5a2.121 2.121 0 0 1 3 3L7 20l-4 1 1-4L16.5 4.5z"/>
                                                <circle cx="19" cy="5" r="2"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <span id="err-new-hex" class="hidden text-[9px] font-bold text-red-600 block mt-0.5">Valid #HEX required</span>
                                </div>
                            </div>

                            <!-- Duplicate Warning Banner for New Variant -->
                            <div id="dup-warning-new" class="hidden text-xs font-bold text-amber-900 bg-amber-50 border-2 border-amber-400 p-2.5 rounded shadow-sm">
                                <span class="dup-msg"></span>
                            </div>

                            <!-- 2. Cover Photo Linker / Fallback Selector -->
                            <div class="p-3 border border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="flex-1 w-full space-y-1.5">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700">Choose Cover Photo from Existing Product Media</label>
                                    <select 
                                        id="new-var-img-select" 
                                        name="image_url" 
                                        onchange="syncNewVariantMiniPreview()" 
                                        class="w-full border border-black p-2 text-xs bg-white font-mono"
                                    >
                                        <option value="">Default Product Cover</option>
                                        @if($product->image_url)
                                            <option value="{{ $product->image_url }}">Cover Photo (Product Primary)</option>
                                        @endif
                                        @foreach($product->mediaAssets as $gIdx => $mAsset)
                                            <option value="{{ $mAsset->url }}">Product Gallery Image #{{ $gIdx + 1 }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- 3. Smart Drag-and-Drop Uploader for New Variant -->
                            <div class="bg-gray-50 p-3 border border-gray-200 space-y-3">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-800 block">Upload Dedicated Photos for this Variant:</label>
                                
                                <div 
                                    id="new-var-dropzone" 
                                    onclick="document.getElementById('new-var-gallery-input').click()"
                                    class="smart-dropzone border-2 border-dashed border-gray-400 hover:border-black bg-white p-4 text-center cursor-pointer transition-all hover:bg-gray-50"
                                >
                                    <input 
                                        type="file" 
                                        id="new-var-gallery-input" 
                                        name="variant_gallery_images[]" 
                                        multiple 
                                        accept="image/jpeg,image/png,image/webp" 
                                        onchange="handleSmartVariantFiles(this, 'new')" 
                                        class="hidden"
                                    >
                                    <input 
                                        type="file" 
                                        id="new-var-cover-file" 
                                        name="image_file" 
                                        accept="image/jpeg,image/png,image/webp" 
                                        onchange="handleSmartVariantCover(this, 'new')" 
                                        class="hidden"
                                    >
                                    <div class="space-y-1 pointer-events-none text-center">
                                        <svg width="24" height="24" class="smart-dropzone-icon text-gray-500 mx-auto" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; display: block; margin: 0 auto 4px auto;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                        <p class="text-xs font-bold text-black uppercase tracking-wider">Drag & drop photos here or <span class="underline">click to browse</span></p>
                                        <p class="text-[10px] text-gray-500 font-mono">Auto-compress high-res photos • Slot #1 becomes Cover Photo</p>
                                    </div>
                                </div>

                                <!-- Pending uploads preview list for New Variant -->
                                <div id="var-gallery-new-previews-new" class="variant-gallery-preview-list flex flex-wrap gap-2.5 pt-2 hidden border-t border-dashed border-gray-300">
                                    <!-- Dynamic instant previews appear here -->
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Real-Time Live Mini-Preview (4 cols) -->
                        <div class="w-full lg:w-72 xl:w-80 shrink-0 sticky top-6 space-y-3">
                            <div class="border-2 border-black bg-white p-3.5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] space-y-3">
                                <div class="flex items-center justify-between border-b pb-1.5">
                                    <span class="text-[9px] font-black uppercase tracking-wider text-black flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Storefront Card Preview
                                    </span>
                                    <span id="new-var-validation-badge" class="text-[8px] font-mono font-bold uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300">
                                        Drafting
                                    </span>
                                </div>

                                <!-- Live Card Simulation -->
                                <div class="border-2 border-black bg-white p-2.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] flex flex-col justify-between">
                                    <div>
                                        <div class="relative h-40 border border-black/10 bg-[#FAFAFA] flex items-center justify-center overflow-hidden mb-2">
                                            <img 
                                                id="new-var-mini-img" 
                                                src="{{ $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url)) : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&q=80' }}" 
                                                class="w-full h-full object-contain p-1"
                                                alt="Preview"
                                            >
                                            <div class="absolute top-1 left-1 flex items-center gap-1 pointer-events-none">
                                                <span class="bg-black text-white text-[7px] font-bold uppercase tracking-wider px-1 py-0.5 leading-none">BESPOKE</span>
                                                <span id="new-var-mini-color-badge" class="bg-black/85 text-white text-[7px] font-mono font-bold uppercase px-1 py-0.5 leading-none border border-white/20">
                                                    New Color
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between gap-1 mb-1 min-h-[16px]">
                                            <div class="flex items-center gap-1.5">
                                                <span id="new-var-mini-swatch" class="w-2.5 h-2.5 rounded-full border border-black/30 inline-block shrink-0 shadow-xs" style="background-color: #1A1A1A;"></span>
                                                <span id="new-var-mini-color-name" class="text-[9px] font-bold uppercase tracking-wider text-black truncate max-w-[85px]">New Color</span>
                                            </div>
                                            <span class="text-[8px] font-mono text-emerald-700 font-bold uppercase flex items-center gap-1 shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 inline-block"></span>
                                                <span id="new-var-mini-stock-label">In Stock (10)</span>
                                            </span>
                                        </div>

                                        <h4 class="font-bold text-xs uppercase tracking-tight text-black truncate leading-snug mb-1">
                                            {{ $product->title }} — <span id="new-var-mini-title-append">New Color</span>
                                        </h4>
                                    </div>

                                    <div class="pt-1.5 flex items-center justify-between border-t border-black/10 mt-1">
                                        <span id="new-var-mini-price" class="font-black text-xs font-mono text-black">
                                            {{ number_format($product->retail_price_minor / 100, 0) }} EGP
                                        </span>
                                        <span class="text-[8px] font-bold uppercase bg-black text-white px-1.5 py-0.5">View</span>
                                    </div>
                                </div>

                                <!-- Inline Validation Guidance -->
                                <div id="new-var-guidance" class="text-[10px] space-y-1 bg-gray-50 p-2.5 border border-gray-200">
                                    <div id="guide-name" class="flex items-center gap-1.5 font-mono text-gray-500">
                                        <span class="guide-icon">○</span> <span>Color Title entered</span>
                                    </div>
                                    <div id="guide-hex" class="flex items-center gap-1.5 font-mono text-emerald-700 font-bold">
                                        <span class="guide-icon">✓</span> <span>Valid #HEX color</span>
                                    </div>
                                    <div id="guide-img" class="flex items-center gap-1.5 font-mono text-emerald-700 font-bold">
                                        <span class="guide-icon">✓</span> <span>Cover image ready</span>
                                    </div>
                                </div>

                                <!-- Submit Button with Live State -->
                                <button 
                                    type="submit" 
                                    id="new-var-submit-btn" 
                                    class="w-full bg-black text-white py-2.5 text-xs font-black uppercase tracking-wider hover:bg-neutral-800 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all cursor-pointer flex items-center justify-center gap-2"
                                >
                                    <span>+ Create Variant</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════ PART 7: BATCH MULTI-COLOR QUICK-ADD ════ --}}
    <div id="atl-batch-section" class="border-2 border-black bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mt-6">
        <div class="flex items-center justify-between px-5 py-3.5 border-b-2 border-black bg-black">
            <div>
                <h2 class="text-sm font-black uppercase tracking-widest text-white">⊕ Batch Add Variants</h2>
                <p class="text-[10px] text-white/60 font-mono mt-0.5">Drop / paste multiple color images at once — auto-crop, whiten &amp; extract colors automatically</p>
            </div>
        </div>

        <div class="p-5 space-y-4">
            {{-- Batch drop zone --}}
            <div
                id="atl-batch-dropzone"
                tabindex="0"
                class="smart-dropzone border-2 border-dashed border-gray-400 hover:border-black bg-white p-6 text-center cursor-pointer focus:outline-none focus:border-black"
                onclick="document.getElementById('atl-batch-file-input').click()"
                title="Paste (Ctrl+V), drag, or click to add multiple images"
            >
                <input type="file" id="atl-batch-file-input" multiple accept="image/*"
                    class="hidden" onchange="AtelierImagePipeline.captureBatch(Array.from(this.files))">
                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                <p class="text-xs font-bold text-black uppercase tracking-wider">Drag &amp; Drop, Paste (Ctrl+V), or <span class="underline">Browse</span> — Multiple Images</p>
                <p class="text-[10px] text-gray-500 font-mono mt-1">Each image → auto-crop + background whiten + color detect → review table → Create All as Drafts</p>
            </div>

            {{-- Review rows (hidden until images dropped) --}}
            <div id="atl-batch-review" class="hidden space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-wider text-black">Review &amp; Confirm Variants</h3>
                    <p id="atl-batch-progress" class="text-[10px] font-mono text-gray-500"></p>
                </div>

                {{-- Column headers --}}
                <div class="grid grid-cols-[80px_1fr_200px_100px_80px] gap-3 text-[9px] font-bold uppercase tracking-wider text-gray-500 pb-1 border-b border-gray-200">
                    <span>Preview</span><span>Color Name &amp; Hex</span><span>Alt Text</span><span>Price / Stock</span><span>Remove</span>
                </div>

                <div id="atl-batch-review-rows" class="space-y-2"></div>

                <button type="button" id="atl-batch-create-btn"
                    onclick="AtelierImagePipeline.batchCreateAll('{{ $product->id }}', '{{ csrf_token() }}')"
                    class="w-full bg-black text-white py-3 text-xs font-black uppercase tracking-widest hover:bg-neutral-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-0.5 hover:translate-y-0.5 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Create All Variants as Drafts
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Sticky Mini Save Bar (Visible when unsaved changes and scrolled down) -->
<div id="sticky-mini-save-bar" class="fixed bottom-5 left-1/2 -translate-x-1/2 bg-black text-white px-5 py-3 border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,0.5)] flex items-center gap-4 z-40 hidden">
    <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
        <span class="text-xs font-bold uppercase tracking-wider">Unsaved Changes Detected</span>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="document.getElementById('product-edit-form').submit()" class="bg-white text-black px-4 py-1.5 text-xs font-black uppercase hover:bg-gray-200 shadow-sm transition-all">
            Save Product
        </button>
        <button type="button" onclick="hideStickySaveBar()" class="text-gray-400 hover:text-white text-xs underline cursor-pointer">
            Dismiss
        </button>
    </div>
</div>

<!-- Canvas Color Picker Modal for Fallback (Strictly Isolated) -->
<div id="canvas-color-picker-modal" class="fixed inset-0 bg-black/80 z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white border-2 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] max-w-lg w-full p-4 space-y-3">
        <div class="flex items-center justify-between border-b pb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Pick Pixel Color from Image</span>
            <button type="button" onclick="closeCanvasColorPickerModal()" class="text-sm font-bold text-gray-600 hover:text-black">&times; Close</button>
        </div>
        <p class="text-[11px] text-gray-600">Click anywhere on the image below to capture that exact pixel color:</p>
        <div class="relative border border-black bg-gray-100 flex items-center justify-center overflow-hidden min-h-[160px] max-h-[350px]">
            <div id="canvas-picker-loader" class="absolute inset-0 bg-white/70 flex items-center justify-center text-xs font-bold text-gray-600 z-10 hidden">
                Loading image...
            </div>
            <canvas id="color-picker-canvas" class="max-w-full max-h-[350px] cursor-crosshair"></canvas>
        </div>
        <div class="flex items-center justify-between pt-2 border-t">
            <div class="flex items-center gap-2">
                <div id="hovered-color-swatch" class="w-6 h-6 border border-black bg-transparent shadow-xs"></div>
                <span id="hovered-color-hex" class="text-xs font-mono font-bold">#------</span>
            </div>
            <span class="text-[10px] text-gray-500 font-mono">Click to select color</span>
        </div>
    </div>
</div>

{{-- ═══ PIPELINE REVIEW MODAL ═══════════════════════════════════════════════ --}}
<div id="atl-pipeline-review-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    onclick="if(event.target===this) AtelierImagePipeline.cancel()">
    <div class="bg-white border-2 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] w-full max-w-2xl max-h-[90vh] overflow-y-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-b-2 border-black bg-black sticky top-0">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-white">🖼 Image Pipeline — Review Before Saving</h3>
                <p class="text-[10px] text-white/60 font-mono">Auto-crop • Background whitened • Color extracted — confirm or adjust below</p>
            </div>
            <button type="button" onclick="AtelierImagePipeline.cancel()"
                class="text-white/60 hover:text-white text-xl font-bold leading-none cursor-pointer">✕</button>
        </div>

        <div class="p-5 space-y-5">

            {{-- Before / After preview --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-gray-500">Original</p>
                    <div class="border-2 border-gray-200 bg-gray-50 flex items-center justify-center" style="height:200px">
                        <img id="pipeline-preview-original" class="max-w-full max-h-full object-contain" alt="Original" src="" style="max-height:196px">
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-gray-500">Processed</p>
                        <span id="pipeline-crop-badge" class="text-[8px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-400 px-1.5 py-0.5">✓ Auto-cropped</span>
                    </div>
                    <div class="border-2 border-black bg-gray-50 flex items-center justify-center" style="height:200px">
                        <img id="pipeline-preview-final" class="max-w-full max-h-full object-contain" alt="Processed" src="" style="max-height:196px">
                    </div>
                </div>
            </div>

            {{-- Warnings --}}
            <div id="pipeline-warnings" class="space-y-1"></div>

            {{-- Cross-catalog duplicate warning --}}
            <div id="pipeline-catalog-dup" class="hidden text-[10px] font-bold text-amber-900 bg-amber-50 border-2 border-amber-400 px-3 py-2"></div>

            {{-- Edit fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-600 mb-1">Suggested Color Hex</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="pipeline-hex-color" class="w-8 h-8 border border-gray-300 cursor-pointer shrink-0"
                            oninput="document.getElementById('pipeline-hex-input').value=this.value">
                        <input type="text" id="pipeline-hex-input" class="flex-1 border border-gray-300 p-2 text-xs font-mono uppercase"
                            placeholder="Auto-detected" oninput="document.getElementById('pipeline-hex-color').value=this.value">
                    </div>
                    <p class="text-[9px] text-gray-500 mt-1">Will auto-fill the Color &amp; Eyedropper field</p>
                </div>
                <div>
                    <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-600 mb-1">Alt Text</label>
                    <input type="text" id="pipeline-alt-input" class="w-full border border-gray-300 p-2 text-xs" placeholder="Product — Color">
                </div>
                <div>
                    <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-600 mb-1">Filename</label>
                    <input type="text" id="pipeline-filename-input" class="w-full border border-gray-300 p-2 text-[10px] font-mono" placeholder="product-color.jpg">
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="AtelierImagePipeline.confirm()"
                    class="flex-1 bg-black text-white py-2.5 text-xs font-black uppercase tracking-widest hover:bg-neutral-800 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] cursor-pointer">
                    ✓ Use This Image
                </button>
                <button type="button" onclick="AtelierImagePipeline.cancel()"
                    class="border-2 border-black text-black py-2.5 px-5 text-xs font-black uppercase tracking-widest hover:bg-gray-100 cursor-pointer">
                    Cancel
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin/image-pipeline.js') }}"></script>
<script>
let quill;
document.addEventListener('DOMContentLoaded', function () {
    if (window.AtelierImagePipeline) {
        AtelierImagePipeline.init('{{ $product->slug }}', {{ $product->retail_price_minor ? $product->retail_price_minor / 100 : 0 }});
    }

    const editorContainer = document.getElementById('quill-editor');
    if (editorContainer) {
        quill = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });
    }

    // Initialize color duplicate checks on page load
    document.querySelectorAll('input[name="color_hex"]').forEach(input => {
        const idMatch = input.id.match(/^var-color-hex-(.+)$/);
        if (idMatch) {
            checkDuplicateColors(idMatch[1], input.value);
        }
    });

    // Render initial recent colors
    renderRecentColors();

    // Track unsaved changes for sticky save bar
    const mainForm = document.getElementById('product-edit-form');
    if (mainForm) {
        mainForm.addEventListener('input', () => showStickySaveBar());
        mainForm.addEventListener('change', () => showStickySaveBar());
    }
});

function syncQuillEditor() {
    if (quill) {
        document.getElementById('hidden-description').value = quill.root.innerHTML;
    }
}

function productEditor() {
    return {};
}

function uploadCoverImage(input) {
    if (!input.files || !input.files[0]) return;
    document.getElementById('cover-loader').style.display = 'flex';
    document.getElementById('cover-form').submit();
}

// ─── STICKY MINI SAVE BAR ───────────────────────────────────────────────────

let stickyBarDismissed = false;
function showStickySaveBar() {
    if (stickyBarDismissed) return;
    const bar = document.getElementById('sticky-mini-save-bar');
    if (bar && window.scrollY > 300) {
        bar.classList.remove('hidden');
    }
}

function hideStickySaveBar() {
    stickyBarDismissed = true;
    const bar = document.getElementById('sticky-mini-save-bar');
    if (bar) bar.classList.add('hidden');
}

window.addEventListener('scroll', () => {
    if (window.scrollY > 300 && !stickyBarDismissed) {
        showStickySaveBar();
    }
}, { passive: true });

// ─── RECENTLY USED COLORS STRIP (LOCALSTORAGE) ──────────────────────────────

function saveRecentColor(hex) {
    if (!hex || !hex.startsWith('#') || hex.length !== 7) return;
    try {
        let recents = JSON.parse(localStorage.getItem('atelier_recent_colors') || '[]');
        recents = [hex.toUpperCase(), ...recents.filter(c => c.toUpperCase() !== hex.toUpperCase())].slice(0, 8);
        localStorage.setItem('atelier_recent_colors', JSON.stringify(recents));
        renderRecentColors();
    } catch (e) {}
}

function renderRecentColors() {
    try {
        const recents = JSON.parse(localStorage.getItem('atelier_recent_colors') || '[]');
        const strip = document.getElementById('recent-colors-strip');
        if (!strip) return;
        if (recents.length === 0) {
            strip.innerHTML = '<span class="text-[9px] text-gray-400 italic">No recent colors</span>';
            return;
        }
        strip.innerHTML = recents.map(color => `
            <button 
                type="button" 
                onclick="applyRecentColorToActive('${color}')" 
                title="Use ${color}" 
                style="background-color: ${color};" 
                class="w-5 h-5 rounded-full border border-black hover:scale-125 transition-transform shadow-xs cursor-pointer"
            ></button>
        `).join('');
    } catch (e) {}
}

let lastActiveVariantId = null;

function applyRecentColorToActive(hex) {
    const targetId = lastActiveVariantId || 'new';
    const hexInput = document.getElementById(targetId === 'new' ? 'new-var-color-hex' : `var-color-hex-${targetId}`);
    const pickerInput = document.getElementById(targetId === 'new' ? 'new-var-color-picker' : `var-color-picker-${targetId}`);
    if (hexInput) hexInput.value = hex.toUpperCase();
    if (pickerInput) pickerInput.value = hex.toUpperCase();
    onColorUpdated(targetId, hex.toUpperCase());
}

// ─── INSTANT VARIANT SEARCH / FILTER ────────────────────────────────────────

function filterVariantCards(query) {
    const q = (query || '').toLowerCase().trim();
    const cards = document.querySelectorAll('[id^="variant-card-"]');
    cards.forEach(card => {
        const title = card.getAttribute('data-variant-title') || '';
        const sku = card.getAttribute('data-variant-sku') || '';
        const color = card.getAttribute('data-variant-color') || '';
        if (!q || title.includes(q) || sku.includes(q) || color.includes(q)) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

// ─── NAMED COLOR PALETTE & COLOR DISTANCE ENGINE ─────────────────────────────

const COLOR_PALETTE = [
    { name: "Obsidian Black", hex: "#1A1A1A" },
    { name: "Stealth Black", hex: "#111111" },
    { name: "Jet Black", hex: "#0A0A0A" },
    { name: "Midnight Black", hex: "#151515" },
    { name: "Matte Carbon", hex: "#2B2B2B" },
    { name: "Forged Carbon", hex: "#383838" },
    { name: "Gunmetal Grey", hex: "#4A4E51" },
    { name: "Slate Grey", hex: "#708090" },
    { name: "Stone Grey", hex: "#878681" },
    { name: "Brushed Titanium", hex: "#8E9296" },
    { name: "Titanium Silver", hex: "#C0C0C0" },
    { name: "Platinum White", hex: "#F5F5F5" },
    { name: "Pure White", hex: "#FFFFFF" },
    { name: "Cream White", hex: "#FFFDD0" },
    { name: "Chalk Pink", hex: "#FFECF2" },
    { name: "Rose Pink", hex: "#E8A3B7" },
    { name: "Blush Pink", hex: "#FFB6C1" },
    { name: "Dusty Rose", hex: "#DCAE96" },
    { name: "Burgundy Wine", hex: "#800020" },
    { name: "Crimson Red", hex: "#990000" },
    { name: "Cherry Red", hex: "#D2042D" },
    { name: "Coral Orange", hex: "#FF7F50" },
    { name: "Cognac Tan", hex: "#A0522D" },
    { name: "Saddle Brown", hex: "#8B4513" },
    { name: "Vintage Brown", hex: "#7A4926" },
    { name: "Espresso Brown", hex: "#3D2B1F" },
    { name: "Desert Tan", hex: "#D2B48C" },
    { name: "Caramel Tan", hex: "#C68B59" },
    { name: "Camel", hex: "#C19A6B" },
    { name: "Khaki Gold", hex: "#F0E68C" },
    { name: "Olive Green", hex: "#556B2F" },
    { name: "Forest Green", hex: "#228B22" },
    { name: "Emerald Green", hex: "#006400" },
    { name: "Sage Green", hex: "#9DC183" },
    { name: "Mint Green", hex: "#98FF98" },
    { name: "Midnight Navy", hex: "#191970" },
    { name: "Navy Blue", hex: "#000080" },
    { name: "Classic Blue", hex: "#0F4C81" },
    { name: "Steel Blue", hex: "#4682B4" },
    { name: "Pacific Blue", hex: "#1CA9C9" },
    { name: "Sky Blue", hex: "#87CEEB" },
    { name: "Royal Purple", hex: "#7851A9" },
    { name: "Lavender", hex: "#E6E6FA" }
];

function hexToRgb(hex) {
    if (!hex) return null;
    hex = hex.replace(/^#/, '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    if (hex.length !== 6) return null;
    const num = parseInt(hex, 16);
    if (isNaN(num)) return null;
    return {
        r: (num >> 16) & 255,
        g: (num >> 8) & 255,
        b: num & 255
    };
}

function getColorDistance(hex1, hex2) {
    const rgb1 = hexToRgb(hex1);
    const rgb2 = hexToRgb(hex2);
    if (!rgb1 || !rgb2) return 999;
    const rmean = (rgb1.r + rgb2.r) / 2;
    const r = rgb1.r - rgb2.r;
    const g = rgb1.g - rgb2.g;
    const b = rgb1.b - rgb2.b;
    return Math.sqrt((((512 + rmean) * r * r) >> 8) + 4 * g * g + (((767 - rmean) * b * b) >> 8));
}

function getClosestColorName(hex) {
    if (!hex) return "Custom Finish";
    let closest = COLOR_PALETTE[0];
    let minDist = Infinity;
    for (const color of COLOR_PALETTE) {
        const dist = getColorDistance(hex, color.hex);
        if (dist < minDist) {
            minDist = dist;
            closest = color;
        }
    }
    return closest.name;
}

function checkDuplicateColors(variantId, currentHex) {
    const warnBox = document.getElementById(variantId === 'new' ? 'dup-warning-new' : `dup-warning-${variantId}`);
    if (!warnBox) return;

    let dupFound = null;
    const allHexInputs = document.querySelectorAll('input[name="color_hex"]');

    allHexInputs.forEach(input => {
        const otherId = input.id.replace('var-color-hex-', '').replace('new-var-color-hex', 'new');
        if (otherId !== String(variantId) && input.value) {
            const dist = getColorDistance(currentHex, input.value);
            if (dist < 36) {
                const form = input.closest('form');
                const titleEl = form ? form.querySelector('input[name="title"]') : null;
                const title = titleEl && titleEl.value ? titleEl.value : 'an existing variant';
                dupFound = { title, hex: input.value.toUpperCase(), dist: Math.round(dist) };
            }
        }
    });

    if (dupFound) {
        warnBox.querySelector('.dup-msg').textContent = `⚠️ This color looks very close to "${dupFound.title}" (${dupFound.hex}) — please verify if this is intended.`;
        warnBox.classList.remove('hidden');
    } else {
        warnBox.classList.add('hidden');
    }
}

function handleColorInputChange(variantId, hex) {
    lastActiveVariantId = variantId;
    const hexInput = document.getElementById(variantId === 'new' ? 'new-var-color-hex' : `var-color-hex-${variantId}`);
    if (hexInput) hexInput.value = hex.toUpperCase();
    onColorUpdated(variantId, hex);
    saveRecentColor(hex);
}

function handleColorHexTextInput(variantId, hex) {
    lastActiveVariantId = variantId;
    if (hex && hex.startsWith('#') && (hex.length === 4 || hex.length === 7)) {
        const pickerInput = document.getElementById(variantId === 'new' ? 'new-var-color-picker' : `var-color-picker-${variantId}`);
        if (pickerInput) pickerInput.value = hex;
        onColorUpdated(variantId, hex);
        saveRecentColor(hex);
    }
}

function onColorUpdated(variantId, hex) {
    const suggestedName = getClosestColorName(hex);

    // Update suggestion badge
    const badge = document.getElementById(variantId === 'new' ? 'color-suggest-badge-new' : `color-suggest-badge-${variantId}`);
    if (badge) {
        badge.dataset.name = suggestedName;
        badge.querySelector('.suggest-text').textContent = suggestedName;
        badge.classList.remove('hidden');
    }

    // If new variant form and title is empty or pristine, auto-fill
    if (variantId === 'new') {
        const titleInput = document.getElementById('new-var-title');
        if (titleInput && (!titleInput.value || titleInput.dataset.autofilled === 'true')) {
            titleInput.value = suggestedName;
            titleInput.dataset.autofilled = 'true';
        }
    }

    // Check duplicate color proximity
    checkDuplicateColors(variantId, hex);
}

function applySuggestedName(titleInputId, name, variantId) {
    const titleInput = document.getElementById(titleInputId);
    if (titleInput) {
        titleInput.value = name;
        if (variantId === 'new') titleInput.dataset.autofilled = 'true';
    }
    const badge = document.getElementById(variantId === 'new' ? 'color-suggest-badge-new' : `color-suggest-badge-${variantId}`);
    if (badge) badge.classList.add('hidden');
}

// ─── INLINE QUICK-EDIT FOR PRICE & STOCK ─────────────────────────────────────

async function quickSaveVariantField(variantId, field, value, inputEl) {
    const feedbackEl = document.getElementById(`inline-msg-${variantId}-${field}`);
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;

    try {
        const response = await fetch(`/admin/variants/${variantId}/inline-edit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ field, value })
        });

        const data = await response.json();
        if (data.success) {
            if (feedbackEl) {
                feedbackEl.textContent = '✓ Saved';
                feedbackEl.classList.remove('hidden');
                setTimeout(() => feedbackEl.classList.add('hidden'), 2500);
            }
        } else {
            alert(data.message || 'Failed to save inline update.');
        }
    } catch (err) {
        console.error('Inline save error:', err);
    }
}

// ─── AJAX VARIANT SAVE (PRESERVES SCROLL & INSTANT FEEDBACK) ─────────────────

async function saveVariantAjax(event, variantId) {
    if (event) event.preventDefault();
    const form = document.getElementById(`var-form-${variantId}`);
    if (!form) return;

    const btn = form.querySelector('button[type="submit"]');
    const badge = document.getElementById(`var-saved-badge-${variantId}`);
    const origBtnHtml = btn ? btn.innerHTML : 'Save Variant';

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>Saving...</span>';
    }

    const formData = new FormData(form);
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || form.querySelector('input[name="_token"]')?.value;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            if (badge) {
                badge.textContent = '✓ Saved Successfully';
                badge.classList.remove('hidden');
                setTimeout(() => badge.classList.add('hidden'), 3500);
            }
            if (data.variant && data.variant.image_url) {
                const img = document.getElementById(`var-img-preview-${variantId}`);
                if (img) img.src = data.variant.image_url;

                // Sync the Cover Image select option if needed
                const selectEl = document.getElementById(`var-img-select-${variantId}`);
                if (selectEl) {
                    let matchingOpt = Array.from(selectEl.options).find(opt => opt.value === data.variant.image_url);
                    if (!matchingOpt) {
                        const opt = document.createElement('option');
                        opt.value = data.variant.image_url;
                        opt.textContent = 'Dedicated Variant Cover (Active)';
                        selectEl.insertBefore(opt, selectEl.firstChild);
                        opt.selected = true;
                    } else {
                        matchingOpt.selected = true;
                    }
                }
            }
            // Clear cover file input
            const coverInput = document.getElementById(`var-cover-file-${variantId}`);
            if (coverInput) coverInput.value = '';

            if (data.variant && data.variant.color_hex) {
                saveRecentColor(data.variant.color_hex);
            }
            // Update saved gallery photos and clear pending uploads
            if (data.variant && Array.isArray(data.variant.gallery)) {
                const savedList = document.getElementById(`var-gallery-saved-list-${variantId}`);
                if (savedList) {
                    if (data.variant.gallery.length === 0) {
                        savedList.innerHTML = '<p class="text-[10px] text-gray-400 italic">No extra gallery photos uploaded for this color yet.</p>';
                    } else {
                        savedList.innerHTML = data.variant.gallery.map(asset => `
                            <div class="relative group border-2 border-black bg-white p-0.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                <img 
                                    src="${asset.url}" 
                                    loading="lazy" 
                                    class="variant-gallery-preview-thumb w-20 h-20 object-cover" 
                                    title="${asset.filename}" 
                                    crossOrigin="anonymous" 
                                    style="width: 80px; height: 80px; object-fit: cover;"
                                >
                                <button 
                                    type="button" 
                                    onclick="if(confirm('Remove this photo from gallery?')) document.getElementById('del-var-media-${variantId}-${asset.id}').submit();" 
                                    class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold hover:bg-red-800 shadow transition-transform hover:scale-110" 
                                    title="Remove photo"
                                >
                                    &times;
                                </button>
                            </div>
                        `).join('');
                    }
                }
                const fileInput = document.getElementById(`var-gallery-input-${variantId}`);
                if (fileInput) fileInput.value = '';
                const previewContainer = document.getElementById(`var-gallery-new-previews-${variantId}`);
                if (previewContainer) {
                    previewContainer.innerHTML = '';
                    previewContainer.classList.add('hidden');
                }
                cleanupVariantGalleryUrls(`var-gallery-new-previews-${variantId}`);
            }
        } else {
            alert(data.message || 'Error saving variant.');
        }
    } catch (err) {
        console.error('AJAX save error, submitting form normally:', err);
        form.submit();
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origBtnHtml;
        }
    }
// ─── VARIANT PUBLISH & BULK STATUS MANAGEMENT (PART 2) ─────────────────────

async function toggleVariantPublish(variantId, requestedAction) {
    const btn = document.getElementById(`publish-btn-${variantId}`);
    const badge = document.getElementById(`var-status-badge-${variantId}`);
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>Processing...</span>';
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;

    try {
        const response = await fetch(`/admin/variants/${variantId}/toggle-publish`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });

        const data = await response.json();
        if (response.ok && data.success) {
            if (data.status === 'published') {
                if (badge) {
                    badge.className = 'text-[9px] font-mono font-bold uppercase px-2 py-0.5 rounded border bg-emerald-100 text-emerald-900 border-emerald-400';
                    badge.textContent = '● Published';
                }
                if (btn) {
                    btn.className = 'border border-amber-600 bg-amber-50 hover:bg-amber-100 text-amber-900 px-3.5 py-2 text-xs font-bold uppercase shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-1.5 cursor-pointer';
                    btn.title = 'Move to Draft (hide from storefront)';
                    btn.onclick = () => toggleVariantPublish(variantId, 'unpublish');
                    btn.innerHTML = '<span>Unpublish (Draft)</span>';
                    btn.disabled = false;
                }
            } else {
                if (badge) {
                    badge.className = 'text-[9px] font-mono font-bold uppercase px-2 py-0.5 rounded border bg-amber-100 text-amber-900 border-amber-400';
                    badge.textContent = '○ Draft (Hidden)';
                }
                if (btn) {
                    btn.className = 'bg-emerald-700 hover:bg-emerald-800 text-white cursor-pointer shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] px-4 py-2 text-xs font-bold uppercase transition-all flex items-center gap-1.5';
                    btn.title = 'Publish Live';
                    btn.onclick = () => toggleVariantPublish(variantId, 'publish');
                    btn.innerHTML = '<span>Publish Live</span>';
                    btn.disabled = false;
                }
            }

            // Update Health Summary counts
            updateHealthStripCounts();
        } else {
            alert(data.message || 'Validation error: cannot publish variant.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    } catch (err) {
        console.error('Toggle publish error:', err);
        alert('Server connection error. Please retry.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}

function toggleSelectAllVariants(master) {
    const isChecked = master.checked;
    document.querySelectorAll('.var-item-checkbox').forEach(cb => {
        cb.checked = isChecked;
    });
    updateSelectedVariantsCount();
}

function updateSelectedVariantsCount() {
    const checked = document.querySelectorAll('.var-item-checkbox:checked');
    const countEl = document.getElementById('selected-variants-count');
    if (countEl) {
        countEl.textContent = `(${checked.length} selected)`;
    }
    const master = document.getElementById('select-all-variants');
    const all = document.querySelectorAll('.var-item-checkbox');
    if (master && all.length > 0) {
        master.checked = (checked.length === all.length);
    }
}

async function executeBulkVariantStatus(action) {
    const checked = Array.from(document.querySelectorAll('.var-item-checkbox:checked')).map(cb => parseInt(cb.value));
    if (checked.length === 0) {
        alert('Please select at least one variant using the checkboxes.');
        return;
    }

    if (!confirm(`Apply action "${action.toUpperCase()}" to ${checked.length} selected variant(s)?`)) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;
    const productId = {{ (int) $product->id }};

    try {
        const response = await fetch(`/admin/products/${productId}/bulk-variants-status`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                variant_ids: checked,
                bulk_action: action
            })
        });

        const data = await response.json();
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Failed to apply bulk action.');
        }
    } catch (err) {
        console.error('Bulk status error:', err);
        alert('Error communicating with server.');
    }
}

function updateHealthStripCounts() {
    const published = document.querySelectorAll('[id^="var-status-badge-"]').length;
    // Simple fast reload or count sync
}

function previewVariantNewCover(input, previewImgId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type.startsWith('image/')) {
            const preview = document.getElementById(previewImgId);
            if (preview) {
                preview.src = URL.createObjectURL(file);
            }
        }
    }
}

// ─── SMART VARIANT IMAGE UPLOADER & DRAG-AND-DROP ENGINE (SECTION 2) ────────

const smartVariantFilesState = new Map(); // variantId => File[]
const smartUndoStack = new Map(); // variantId => lastAction
const smartUndoTimers = new Map();

// 1. Client-Side Image Compression (Canvas-based)
async function compressImageFile(file, maxWidth = 1600, maxHeight = 1600, quality = 0.85) {
    if (!file || !file.type.startsWith('image/')) return file;
    if (file.size < 800 * 1024) return file; // Only compress images >= 800KB

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                let width = img.width;
                let height = img.height;

                if (width > maxWidth || height > maxHeight) {
                    if (width > height) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    } else {
                        width = Math.round((width * maxHeight) / height);
                        height = maxHeight;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const mimeType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                canvas.toBlob((blob) => {
                    if (!blob || blob.size >= file.size) {
                        resolve(file); // Keep original if compression didn't shrink
                    } else {
                        const compressedFile = new File([blob], file.name, {
                            type: mimeType,
                            lastModified: Date.now()
                        });
                        resolve(compressedFile);
                    }
                }, mimeType, quality);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

async function resolveSmartDropFiles(e) {
    // 1. Direct files from local disk
    const directFiles = Array.from(e.dataTransfer?.files || e.clipboardData?.files || []).filter(f => f.type && f.type.startsWith('image/'));
    if (directFiles.length > 0) return directFiles;

    // 2. External URL drag-and-drop or HTML (from Amazon, Google, etc.)
    let url = null;
    const uriList = e.dataTransfer?.getData('text/uri-list') || '';
    const htmlData = e.dataTransfer?.getData('text/html') || e.clipboardData?.getData('text/html') || '';
    const plainText = e.dataTransfer?.getData('text/plain') || e.clipboardData?.getData('text/plain') || '';

    if (uriList && uriList.startsWith('http')) {
        url = uriList.trim().split('\n')[0];
    } else if (htmlData) {
        const match = htmlData.match(/<img[^>]+src=["'](https?:\/\/[^"']+)["']/i);
        if (match) url = match[1];
    }
    if (!url && plainText && (plainText.match(/^https?:\/\/.+\.(jpg|jpeg|png|webp|avif|gif)(\?.*)?$/i) || (plainText.startsWith('http') && plainText.includes('images')))) {
        url = plainText.trim();
    }

    if (url) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const resp = await fetch('{{ route('admin.images.fetch-url') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ url: url })
            });
            const data = await resp.json();
            if (data.success && data.data_url) {
                const blobResp = await fetch(data.data_url);
                const blob = await blobResp.blob();
                const file = new File([blob], data.filename || 'imported-variant.jpg', { type: data.mime || 'image/jpeg' });
                return [file];
            }
        } catch(err) {
            console.warn('Failed to fetch external dropped image via proxy:', err);
        }
    }
    return [];
}

// 2. Initialize Drag-and-Drop Zones
function initSmartDropzones() {
    document.querySelectorAll('.smart-dropzone').forEach(dropzone => {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('border-black', 'bg-gray-100', 'scale-[1.01]');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('border-black', 'bg-gray-100', 'scale-[1.01]');
            });
        });

        dropzone.addEventListener('drop', async (e) => {
            const files = await resolveSmartDropFiles(e);
            if (!files || files.length === 0) return;

            const idMatch = dropzone.id.match(/^var-dropzone-(.+)$/) || dropzone.id.match(/^new-var-dropzone$/);
            const variantId = idMatch ? (dropzone.id === 'new-var-dropzone' ? 'new' : idMatch[1]) : 'new';
            await handleSmartDroppedFiles(Array.from(files), variantId);
        });
    });
}

// 3. Handle File Selection & Dropping with Compression & Duplicate Check
async function handleSmartVariantFiles(input, variantId) {
    if (!input.files || input.files.length === 0) return;
    await handleSmartDroppedFiles(Array.from(input.files), variantId);
}

async function handleSmartVariantCover(input, variantId) {
    if (!input.files || input.files.length === 0) return;
    const file = input.files[0];
    const compressed = await compressImageFile(file);
    const objectUrl = URL.createObjectURL(compressed);

    const previewImg = document.getElementById(variantId === 'new' ? 'new-var-mini-img' : `var-img-preview-${variantId}`);
    if (previewImg) previewImg.src = objectUrl;

    if (variantId === 'new') {
        syncNewVariantMiniPreview();
    }
}

async function handleSmartDroppedFiles(fileList, variantId) {
    const validImageFiles = fileList.filter(f => f.type.startsWith('image/'));
    if (validImageFiles.length === 0) return;

    // Record undo state before modifying
    recordUndoState(variantId, 'Add Photos');

    const currentFiles = smartVariantFilesState.get(variantId) || [];
    const container = document.getElementById(variantId === 'new' ? 'var-gallery-new-previews-new' : `var-gallery-new-previews-${variantId}`);
    const dupWarning = document.getElementById(variantId === 'new' ? 'dup-warning-new' : `var-dup-img-warning-${variantId}`);

    let duplicateFound = false;

    for (const rawFile of validImageFiles) {
        // Duplicate check against current batch
        const isDup = currentFiles.some(f => f.name === rawFile.name && f.size === rawFile.size);
        if (isDup) {
            duplicateFound = true;
            continue;
        }

        const compressed = await compressImageFile(rawFile);
        currentFiles.push(compressed);
    }

    if (duplicateFound && dupWarning) {
        dupWarning.classList.remove('hidden');
        setTimeout(() => dupWarning.classList.add('hidden'), 5000);
    }

    smartVariantFilesState.set(variantId, currentFiles);
    renderSmartPendingThumbnails(variantId);

    // If new variant, update mini preview with the first image
    if (variantId === 'new' && currentFiles.length > 0) {
        const firstObjUrl = URL.createObjectURL(currentFiles[0]);
        const miniImg = document.getElementById('new-var-mini-img');
        if (miniImg) miniImg.src = firstObjUrl;
        syncNewVariantMiniPreview();
    }

    // Sync to file input via DataTransfer
    syncFilesToInput(variantId);
}

function syncFilesToInput(variantId) {
    const input = document.getElementById(variantId === 'new' ? 'new-var-gallery-input' : `var-gallery-input-${variantId}`);
    if (!input) return;
    try {
        const dt = new DataTransfer();
        const files = smartVariantFilesState.get(variantId) || [];
        files.forEach(f => dt.items.add(f));
        input.files = dt.files;
    } catch (e) {}
}

// 4. Render Smart Previews with Drag-to-Reorder and Slot #1 Cover Badge
function renderSmartPendingThumbnails(variantId) {
    const container = document.getElementById(variantId === 'new' ? 'var-gallery-new-previews-new' : `var-gallery-new-previews-${variantId}`);
    if (!container) return;

    container.innerHTML = '';
    const files = smartVariantFilesState.get(variantId) || [];

    if (files.length === 0) {
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');

    files.forEach((file, idx) => {
        const objectUrl = URL.createObjectURL(file);
        const isCoverSlot = (idx === 0);

        const card = document.createElement('div');
        card.className = `relative group border-2 ${isCoverSlot ? 'border-amber-500 ring-2 ring-amber-300' : 'border-black'} bg-white p-0.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] cursor-grab active:cursor-grabbing transition-transform hover:scale-105`;
        card.draggable = true;
        card.dataset.index = idx;
        card.dataset.variantId = variantId;

        card.ondragstart = (e) => {
            e.dataTransfer.setData('text/plain', JSON.stringify({ variantId, index: idx }));
            card.classList.add('opacity-50');
        };
        card.ondragend = () => card.classList.remove('opacity-50');
        card.ondragover = (e) => e.preventDefault();
        card.ondrop = (e) => {
            e.preventDefault();
            try {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                if (data.variantId === variantId && data.index !== idx) {
                    reorderPendingFiles(variantId, data.index, idx);
                }
            } catch (err) {}
        };

        const img = document.createElement('img');
        img.src = objectUrl;
        img.alt = file.name;
        img.title = `${file.name} (${Math.round(file.size / 1024)} KB) — Drag to reorder`;
        img.className = 'w-20 h-20 object-cover';
        img.style.width = '80px';
        img.style.height = '80px';
        img.style.objectFit = 'cover';

        const badge = document.createElement('span');
        badge.className = `absolute bottom-1 left-1 ${isCoverSlot ? 'bg-amber-600 text-white font-black' : 'bg-black/80 text-white'} text-[7.5px] font-mono px-1 py-0.5 rounded leading-none pointer-events-none`;
        badge.textContent = isCoverSlot ? '★ COVER' : `#${idx + 1}`;

        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold hover:bg-red-800 shadow transition-transform hover:scale-110';
        delBtn.innerHTML = '&times;';
        delBtn.title = 'Remove photo';
        delBtn.onclick = (e) => {
            e.stopPropagation();
            removePendingFile(variantId, idx);
        };

        card.appendChild(img);
        card.appendChild(badge);
        card.appendChild(delBtn);
        container.appendChild(card);
    });
}

function removePendingFile(variantId, index) {
    recordUndoState(variantId, 'Remove Photo');
    const files = smartVariantFilesState.get(variantId) || [];
    files.splice(index, 1);
    smartVariantFilesState.set(variantId, files);
    syncFilesToInput(variantId);
    renderSmartPendingThumbnails(variantId);
    showUndoToast(variantId, 'Photo removed');
}

function reorderPendingFiles(variantId, fromIndex, toIndex) {
    recordUndoState(variantId, 'Reorder Photos');
    const files = smartVariantFilesState.get(variantId) || [];
    const item = files.splice(fromIndex, 1)[0];
    files.splice(toIndex, 0, item);
    smartVariantFilesState.set(variantId, files);
    syncFilesToInput(variantId);
    renderSmartPendingThumbnails(variantId);
    showUndoToast(variantId, 'Photos reordered');
}

// 5. Undo Stack Engine
function recordUndoState(variantId, actionLabel) {
    const currentFiles = [...(smartVariantFilesState.get(variantId) || [])];
    smartUndoStack.set(variantId, { files: currentFiles, label: actionLabel });
}

function showUndoToast(variantId, message) {
    const toast = document.getElementById(`var-undo-toast-${variantId}`);
    if (!toast) return;

    toast.querySelector('.undo-msg').textContent = `${message} — Click Undo to revert (5s)`;
    toast.classList.remove('hidden');

    if (smartUndoTimers.has(variantId)) {
        clearTimeout(smartUndoTimers.get(variantId));
    }

    const timer = setTimeout(() => {
        toast.classList.add('hidden');
        smartUndoStack.delete(variantId);
    }, 5000);

    smartUndoTimers.set(variantId, timer);
}

function executeSmartUndo(variantId) {
    const state = smartUndoStack.get(variantId);
    if (!state) return;

    smartVariantFilesState.set(variantId, state.files);
    syncFilesToInput(variantId);
    renderSmartPendingThumbnails(variantId);

    const toast = document.getElementById(`var-undo-toast-${variantId}`);
    if (toast) toast.classList.add('hidden');
    smartUndoStack.delete(variantId);
}

function removeSmartExistingPhoto(variantId, assetId, variantTitle) {
    if (confirm(`Remove this photo from ${variantTitle} gallery?`)) {
        const form = document.getElementById(`del-var-media-${variantId}-${assetId}`);
        if (form) form.submit();
    }
}

// ─── LIVE STOREFRONT MINI-PREVIEW & INLINE VALIDATION (SECTION 3) ─────────────

function syncNewVariantMiniPreview() {
    const titleInput = document.getElementById('new-var-title');
    const hexInput = document.getElementById('new-var-color-hex');
    const priceInput = document.getElementById('new-var-price-input');
    const stockInput = document.getElementById('new-var-stock-input');
    const selectCover = document.getElementById('new-var-img-select');

    const title = (titleInput?.value || '').trim() || 'New Color';
    const hex = (hexInput?.value || '#1A1A1A').trim().toUpperCase();
    const stock = stockInput?.value !== '' ? parseInt(stockInput.value, 10) : 10;
    
    // Mini-preview elements
    const prevColorBadge = document.getElementById('new-var-mini-color-badge');
    const prevColorName = document.getElementById('new-var-mini-color-name');
    const prevSwatch = document.getElementById('new-var-mini-swatch');
    const prevTitleAppend = document.getElementById('new-var-mini-title-append');
    const prevPrice = document.getElementById('new-var-mini-price');
    const prevStockLabel = document.getElementById('new-var-mini-stock-label');
    const prevImg = document.getElementById('new-var-mini-img');

    if (prevColorBadge) prevColorBadge.textContent = title;
    if (prevColorName) prevColorName.textContent = title;
    if (prevTitleAppend) prevTitleAppend.textContent = title;
    if (prevSwatch) prevSwatch.style.backgroundColor = hex;
    if (prevStockLabel) prevStockLabel.textContent = stock > 0 ? `In Stock (${stock})` : 'Sold Out';

    if (prevPrice) {
        const basePrice = {{ (int) $product->retail_price_minor / 100 }};
        const overridePrice = parseFloat(priceInput?.value);
        const finalPrice = !isNaN(overridePrice) && overridePrice > 0 ? overridePrice : basePrice;
        prevPrice.textContent = `${Math.round(finalPrice).toLocaleString()} EGP`;
    }

    if (selectCover && selectCover.value && prevImg && (!prevImg.src || !prevImg.src.startsWith('blob:'))) {
        prevImg.src = selectCover.value;
    }

    validateNewVariantForm();
}

function validateNewVariantForm() {
    const titleInput = document.getElementById('new-var-title');
    const hexInput = document.getElementById('new-var-color-hex');
    const guideName = document.getElementById('guide-name');
    const guideHex = document.getElementById('guide-hex');
    const guideImg = document.getElementById('guide-img');
    const badge = document.getElementById('new-var-validation-badge');
    const submitBtn = document.getElementById('new-var-submit-btn');

    const hasTitle = Boolean((titleInput?.value || '').trim());
    const validHex = /^#([0-9A-F]{3}|[0-9A-F]{6})$/i.test((hexInput?.value || '').trim());
    const hasImage = true; // Inherits default cover or uploaded

    // Update checklist UI
    if (guideName) {
        guideName.className = `flex items-center gap-1.5 font-mono ${hasTitle ? 'text-emerald-700 font-bold' : 'text-gray-400'}`;
        guideName.querySelector('.guide-icon').textContent = hasTitle ? '✓' : '○';
    }
    if (guideHex) {
        guideHex.className = `flex items-center gap-1.5 font-mono ${validHex ? 'text-emerald-700 font-bold' : 'text-red-600 font-bold'}`;
        guideHex.querySelector('.guide-icon').textContent = validHex ? '✓' : '✗';
    }

    const isValid = hasTitle && validHex;

    if (badge) {
        if (isValid) {
            badge.textContent = '✓ Ready to Create';
            badge.className = 'text-[8px] font-mono font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-400';
        } else {
            badge.textContent = 'Drafting';
            badge.className = 'text-[8px] font-mono font-bold uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300';
        }
    }

    return isValid;
}

function handleNewVariantSubmit(event) {
    const isValid = validateNewVariantForm();
    if (!isValid) {
        if (event) event.preventDefault();
        const titleInput = document.getElementById('new-var-title');
        if (!titleInput?.value) {
            document.getElementById('err-new-title')?.classList.remove('hidden');
            titleInput?.focus();
        }
        return false;
    }
    return true;
}

// ─── INITIALIZATION ─────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    initSmartDropzones();
    syncNewVariantMiniPreview();
});

// ─── EYEDROPPER & PIXEL COLOR PICKER ENGINE (FULLY ISOLATED) ───────────────────

let pickerSessionCounter = 0;
let currentPickerSession = null;

async function pickColorFromImage(hexInputId, pickerInputId, previewImgId, variantId) {
    lastActiveVariantId = variantId;
    const hexInput = document.getElementById(hexInputId);
    const pickerInput = document.getElementById(pickerInputId);

    const applyColor = (hex) => {
        if (!hex) return;
        const upperHex = hex.toUpperCase();
        if (hexInput) hexInput.value = upperHex;
        if (pickerInput) pickerInput.value = upperHex;
        if (variantId) onColorUpdated(variantId, upperHex);
        saveRecentColor(upperHex);
    };

    // 1. Primary: Native Browser EyeDropper API (Chrome / Edge / Opera)
    if (window.EyeDropper) {
        try {
            const eyeDropper = new EyeDropper();
            const result = await eyeDropper.open();
            if (result && result.sRGBHex) {
                applyColor(result.sRGBHex);
                return;
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
        }
    }

    // 2. Fallback: Canvas Pixel Sampler Modal with strict isolation
    let imgSrc = null;
    const targetImg = previewImgId ? document.getElementById(previewImgId) : null;
    if (targetImg && targetImg.tagName === 'IMG' && targetImg.src && !targetImg.src.startsWith('data:image/svg')) {
        imgSrc = targetImg.src;
    }

    if (!imgSrc) {
        const card = targetImg ? targetImg.closest('.border-2') : null;
        const select = card ? card.querySelector('select[name="image_url"]') : null;
        if (select && select.value) {
            imgSrc = select.value;
        }
    }

    if (!imgSrc) {
        const coverImg = document.getElementById('cover-preview');
        if (coverImg && coverImg.src) imgSrc = coverImg.src;
    }

    if (!imgSrc) {
        alert('Please upload or select an image for this variant to sample color from.');
        return;
    }

    openCanvasColorPickerModal(imgSrc, applyColor);
}

function openCanvasColorPickerModal(imageSrc, callback) {
    const session = ++pickerSessionCounter;
    currentPickerSession = { session, callback };

    const modal = document.getElementById('canvas-color-picker-modal');
    const canvas = document.getElementById('color-picker-canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const swatch = document.getElementById('hovered-color-swatch');
    const hexText = document.getElementById('hovered-color-hex');
    const loader = document.getElementById('canvas-picker-loader');

    // Reset canvas buffer and size immediately
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    canvas.width = 0;
    canvas.height = 0;
    swatch.style.backgroundColor = 'transparent';
    hexText.textContent = '#------';

    if (loader) loader.classList.remove('hidden');
    modal.classList.remove('hidden');

    const img = new Image();
    img.crossOrigin = 'Anonymous';

    img.onload = function () {
        if (currentPickerSession?.session !== session) return; // Discard stale session
        if (loader) loader.classList.add('hidden');

        canvas.width = img.naturalWidth || img.width;
        canvas.height = img.naturalHeight || img.height;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
    };

    img.onerror = function () {
        const fallbackImg = new Image();
        fallbackImg.onload = function () {
            if (currentPickerSession?.session !== session) return;
            if (loader) loader.classList.add('hidden');
            canvas.width = fallbackImg.naturalWidth || fallbackImg.width;
            canvas.height = fallbackImg.naturalHeight || fallbackImg.height;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(fallbackImg, 0, 0);
        };
        fallbackImg.onerror = function () {
            if (loader) loader.classList.add('hidden');
            alert('Unable to load image for pixel sampling.');
            closeCanvasColorPickerModal();
        };
        fallbackImg.src = imageSrc;
    };

    img.src = imageSrc;

    function getHexAtEvent(e) {
        if (canvas.width === 0 || canvas.height === 0) return null;
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const x = Math.max(0, Math.min(canvas.width - 1, Math.floor((e.clientX - rect.left) * scaleX)));
        const y = Math.max(0, Math.min(canvas.height - 1, Math.floor((e.clientY - rect.top) * scaleY)));
        try {
            const pixel = ctx.getImageData(x, y, 1, 1).data;
            const r = pixel[0].toString(16).padStart(2, '0');
            const g = pixel[1].toString(16).padStart(2, '0');
            const b = pixel[2].toString(16).padStart(2, '0');
            return '#' + (r + g + b).toUpperCase();
        } catch (err) {
            return null;
        }
    }

    canvas.onmousemove = function (e) {
        if (currentPickerSession?.session !== session) return;
        const hex = getHexAtEvent(e);
        if (hex) {
            swatch.style.backgroundColor = hex;
            hexText.textContent = hex;
        }
    };

    canvas.onclick = function (e) {
        if (currentPickerSession?.session !== session) return;
        const hex = getHexAtEvent(e);
        if (hex && currentPickerSession?.callback) {
            currentPickerSession.callback(hex);
        }
        closeCanvasColorPickerModal();
    };
}

function closeCanvasColorPickerModal() {
    const modal = document.getElementById('canvas-color-picker-modal');
    if (modal) modal.classList.add('hidden');
    activeColorCallback = null;
}
</script>
@endpush
@endsection
