@extends('layouts.admin')

@section('title', 'Products Management')

@section('content')
<div class="space-y-6" x-data="productsManager()">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b-2 border-black pb-4">
        <div>
            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500">CATALOG MANAGEMENT</span>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">All Products</h1>
            <p class="text-xs text-gray-500 mt-0.5">{{ $products->total() }} total pieces registered in store inventory</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- CSV Export -->
            <a href="{{ route('admin.products.export') }}" class="border-2 border-black bg-white px-3.5 py-2 text-xs font-bold uppercase hover:bg-gray-100 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></span>
                <span>Export CSV</span>
            </a>

            <!-- CSV Import Modal Trigger -->
            <button @click="importModal = true" class="border-2 border-black bg-white px-3.5 py-2 text-xs font-bold uppercase hover:bg-gray-100 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg></span>
                <span>Import CSV</span>
            </button>

            <!-- Add Product -->
            <a href="{{ route('admin.products.create') }}" class="bg-black text-white px-4 py-2 text-xs font-bold uppercase tracking-widest hover:bg-gray-800 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-transform active:translate-y-0.5 flex items-center gap-1.5">
                <span>+</span>
                <span>Add Product</span>
            </a>
        </div>
    </div>

    <!-- Bulk Actions Floating Bar (Conditional when items selected) -->
    <div 
        x-show="selected.length > 0" 
        x-cloak 
        class="sticky top-16 z-30 bg-black text-white p-3 border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,0.5)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 animate-fadeIn"
    >
        <div class="flex items-center gap-3">
            <span class="bg-white text-black px-2 py-0.5 text-xs font-mono font-bold">
                <span x-text="selected.length"></span> Selected
            </span>
            <span class="text-xs font-bold uppercase tracking-wider text-gray-300">Bulk Actions:</span>
        </div>

        <form action="{{ route('admin.products.bulk') }}" method="POST" class="flex items-center gap-2 flex-wrap w-full sm:w-auto" onsubmit="return confirmBulkAction(this)">
            @csrf
            <!-- Hidden inputs for selected IDs -->
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="product_ids[]" :value="id">
            </template>

            <select name="action" x-model="bulkActionType" class="border border-white bg-black text-white p-1.5 text-xs font-bold uppercase focus:outline-none cursor-pointer">
                <option value="activate"><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Set Active</option>
                <option value="draft"><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Set Draft</option>
                <option value="price_percent"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg> Adjust Price by % (+/-)</option>
                <option value="price_fixed"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg> Adjust Price by EGP (+/-)</option>
                <option value="delete"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg> Delete Selected</option>
            </select>

            <input 
                x-show="bulkActionType === 'price_percent' || bulkActionType === 'price_fixed'"
                type="number" 
                step="0.01" 
                name="adjustment_value" 
                placeholder="e.g. 10 or -50" 
                class="w-28 border border-white p-1.5 text-xs bg-white text-black font-mono font-bold focus:outline-none"
            >

            <button type="submit" class="bg-white text-black hover:bg-gray-200 px-4 py-1.5 text-xs font-bold uppercase tracking-wider shadow-sm transition-colors">
                Apply Action
            </button>
            <button type="button" @click="selected = []" class="text-xs text-gray-400 hover:text-white underline ml-2">
                Clear
            </button>
        </form>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            
            <!-- Search -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-600 mb-1">Search Products & Variants</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by product name, color name, or SKU across catalog..." 
                    class="w-full border-2 border-black p-2 text-xs focus:outline-none"
                >
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border-2 border-black p-2 text-xs bg-white focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Draft</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg> Archived</option>
                </select>
            </div>

            <!-- Collection Filter -->
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-600 mb-1">Collection</label>
                <div class="flex items-center gap-2">
                    <select name="collection_id" class="w-full border-2 border-black p-2 text-xs bg-white focus:outline-none">
                        <option value="">All Collections</option>
                        @foreach($collections as $col)
                            <option value="{{ $col->id }}" {{ request('collection_id') == $col->id ? 'selected' : '' }}>{{ $col->title }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-black text-white px-4 py-2 text-xs font-bold uppercase hover:bg-gray-800">
                        Filter
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b-2 border-black bg-gray-50 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                            <th class="p-3 w-8 text-center">
                                <input type="checkbox" @change="toggleSelectAll($event)" class="w-4 h-4 accent-black cursor-pointer">
                            </th>
                            <th class="p-3 w-16">Image</th>
                            <th class="p-3">Product Name & SKU</th>
                            <th class="p-3">Collections</th>
                            <th class="p-3">Price</th>
                            <th class="p-3">Stock</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($products as $product)
                            @php
                                $img = $product->image_url ?: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=300';
                                $imgUrl = str_starts_with($img, 'http') ? $img : url($img);
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors" :class="selected.includes('{{ $product->id }}') ? 'bg-amber-50/60' : ''">
                                <!-- Checkbox -->
                                <td class="p-3 text-center">
                                    <input type="checkbox" value="{{ $product->id }}" x-model="selected" class="w-4 h-4 accent-black cursor-pointer">
                                </td>

                                <!-- Thumbnail -->
                                <td class="p-3">
                                    <div class="w-12 h-12 border border-black bg-gray-100 overflow-hidden relative group">
                                        <img src="{{ $imgUrl }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                    </div>
                                </td>

                                <!-- Title & SKU -->
                                <td class="p-3">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="font-black text-sm uppercase text-black hover:underline block truncate max-w-xs">
                                        {{ $product->title }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] font-mono text-gray-500 font-bold">{{ $product->sku }}</span>
                                        @if($product->variants->count() > 0)
                                            <span class="text-[9px] bg-gray-100 border border-gray-300 px-1 py-0.2 font-mono">
                                                {{ $product->variants->count() }} variant(s)
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Collections -->
                                <td class="p-3">
                                    @if($product->collections->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($product->collections as $col)
                                                <span class="text-[9px] font-bold uppercase bg-gray-100 border border-gray-300 px-1.5 py-0.5">
                                                    {{ $col->title }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-400 italic">Unassigned</span>
                                    @endif
                                </td>

                                <!-- Price (Inline Quick-Edit) -->
                                <td 
                                    class="p-3 font-mono transition-colors duration-500"
                                    x-data="{
                                        editing: false,
                                        priceEgp: '{{ $product->retail_price_minor ? (int)round($product->retail_price_minor / 100) : 0 }}',
                                        displayPrice: '{{ number_format($product->retail_price_minor / 100, 0) }} EGP',
                                        saved: false,
                                        save() {
                                            fetch('{{ route('admin.products.inline-edit', $product->id) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({ field: 'retail_price_minor', value: this.priceEgp })
                                            })
                                            .then(r => r.json())
                                            .then(data => {
                                                if(data.success) {
                                                    this.displayPrice = data.new_value_formatted;
                                                    this.saved = true;
                                                    setTimeout(() => { this.saved = false; }, 1200);
                                                }
                                                this.editing = false;
                                            })
                                            .catch(() => { this.editing = false; });
                                        }
                                    }"
                                    :class="saved ? 'bg-green-100' : ''"
                                >
                                    <div x-show="!editing" @click="editing = true; $nextTick(() => $refs.priceInput.focus())" class="cursor-pointer hover:underline flex items-center gap-1 group" title="Click to quick-edit price">
                                        <span class="font-black text-black" x-text="displayPrice"></span>
                                        <span class="opacity-0 group-hover:opacity-100 text-[9px] text-gray-400"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg></span>
                                    </div>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1">
                                        <input 
                                            x-ref="priceInput" 
                                            type="number" 
                                            x-model="priceEgp" 
                                            @keydown.enter="save()" 
                                            @keydown.escape="editing = false" 
                                            @blur="save()" 
                                            class="w-20 border-2 border-black p-1 text-xs font-mono font-bold bg-white focus:outline-none"
                                        >
                                        <span class="text-[10px] text-gray-400">EGP</span>
                                    </div>
                                    @if($product->compare_at_price_minor)
                                        <div class="text-[10px] text-gray-400 line-through">
                                            {{ number_format($product->compare_at_price_minor / 100, 0) }} EGP
                                        </div>
                                    @endif
                                </td>

                                <!-- Stock (Inline Quick-Edit) -->
                                <td 
                                    class="p-3 font-mono transition-colors duration-500"
                                    x-data="{
                                        editing: false,
                                        stock: {{ $product->inventory }},
                                        saved: false,
                                        save() {
                                            fetch('{{ route('admin.products.inline-edit', $product->id) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({ field: 'inventory', value: this.stock })
                                            })
                                            .then(r => r.json())
                                            .then(data => {
                                                if(data.success) {
                                                    this.stock = parseInt(data.new_value);
                                                    this.saved = true;
                                                    setTimeout(() => { this.saved = false; }, 1200);
                                                }
                                                this.editing = false;
                                            })
                                            .catch(() => { this.editing = false; });
                                        }
                                    }"
                                    :class="saved ? 'bg-green-100' : ''"
                                >
                                    <div x-show="!editing" @click="editing = true; $nextTick(() => $refs.stockInput.focus())" class="cursor-pointer hover:underline flex items-center gap-1 group" title="Click to quick-edit stock">
                                        <template x-if="stock <= 0">
                                            <span class="text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 text-[10px] font-bold">OUT OF STOCK</span>
                                        </template>
                                        <template x-if="stock > 0 && stock <= {{ $product->low_stock_threshold ?: 5 }}">
                                            <span class="text-amber-700 bg-amber-50 border border-amber-300 px-1.5 py-0.5 text-[10px] font-bold"><svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> <span x-text="stock"></span> left</span>
                                        </template>
                                        <template x-if="stock > {{ $product->low_stock_threshold ?: 5 }}">
                                            <span class="text-green-800 font-bold"><span x-text="stock"></span> in stock</span>
                                        </template>
                                        <span class="opacity-0 group-hover:opacity-100 text-[9px] text-gray-400"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg></span>
                                    </div>
                                    <div x-show="editing" x-cloak>
                                        <input 
                                            x-ref="stockInput" 
                                            type="number" 
                                            x-model="stock" 
                                            @keydown.enter="save()" 
                                            @keydown.escape="editing = false" 
                                            @blur="save()" 
                                            class="w-16 border-2 border-black p-1 text-xs font-mono font-bold bg-white focus:outline-none"
                                        >
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="p-3">
                                    <form action="{{ route('admin.products.toggle-status', $product->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-bold uppercase px-2 py-0.5 border cursor-pointer {{ $product->status === 'active' ? 'bg-green-100 border-green-600 text-green-800 hover:bg-green-200' : 'bg-gray-100 border-gray-400 text-gray-700 hover:bg-gray-200' }}" title="Click to toggle status">
                                            {{ $product->status }} ⟳
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="p-3 text-right space-x-1">
                                    <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="border border-black px-2 py-1 text-[10px] font-bold uppercase hover:bg-gray-100" title="View live page">
                                        ↗ View
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="bg-black text-white px-2.5 py-1 text-[10px] font-bold uppercase hover:bg-gray-800">
                                        <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Edit
                                    </a>
                                    <form action="{{ route('admin.products.delete', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete {{ addslashes($product->title) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="border border-red-600 text-red-600 px-2 py-1 text-[10px] font-bold uppercase hover:bg-red-50">
                                            <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t-2 border-black bg-gray-50 flex items-center justify-between">
                {{ $products->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-500">
                <span class="text-4xl block mb-2"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg></span>
                <h3 class="font-bold text-sm uppercase tracking-wider">No Products Found</h3>
                <p class="text-xs text-gray-400 mt-1">Try adjusting your search criteria or add a new piece to the catalog.</p>
                <a href="{{ route('admin.products.create') }}" class="inline-block mt-4 bg-black text-white px-4 py-2 text-xs font-bold uppercase tracking-wider">
                    + Add Product Now
                </a>
            </div>
        @endif
    </div>

    <!-- CSV Import Modal -->
    <div 
        x-show="importModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
    >
        <div class="bg-white border-2 border-black max-w-lg w-full p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] space-y-4" @click.outside="importModal = false">
            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <h3 class="text-lg font-black uppercase tracking-tight text-black"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg> Bulk Import Products (CSV)</h3>
                <button @click="importModal = false" class="text-black font-bold text-lg">✕</button>
            </div>

            <p class="text-xs text-gray-600 leading-relaxed">
                Upload a CSV spreadsheet with headers: <code class="bg-gray-100 px-1 py-0.5 font-mono text-[11px] font-bold">SKU, Name, Retail_Price_EGP, Inventory, Status</code>. Existing products matching by SKU will be updated; new SKUs will be created.
            </p>

            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Select CSV File *</label>
                    <input type="file" name="csv_file" required accept=".csv,text/csv" class="w-full border-2 border-black p-2.5 text-xs bg-white focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t">
                    <button type="button" @click="importModal = false" class="border border-black px-4 py-2 text-xs font-bold uppercase">
                        Cancel
                    </button>
                    <button type="submit" class="bg-black text-white px-6 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                        Upload & Process →
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function productsManager() {
    return {
        selected: [],
        bulkActionType: 'activate',
        importModal: false,
        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selected = @json($products->pluck('id')->map(fn($id) => (string)$id));
            } else {
                this.selected = [];
            }
        }
    };
}

function confirmBulkAction(form) {
    const action = form.elements['action'].value;
    if (action === 'delete') {
        return confirm('Are you sure you want to PERMANENTLY DELETE all selected products?');
    }
    return true;
}
</script>
@endpush
@endsection
