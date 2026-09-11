@extends('layouts.admin')

@section('title', 'Collections & Categories')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b-2 border-black pb-4">
        <div>
            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500">CURATION & TAXONOMY</span>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">Store Collections</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage the categories and themed archives displayed across your store navigation</p>
        </div>
    </div>

    <!-- Create New Collection Card -->
    <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
        <div class="border-b-2 border-black pb-3 mb-4">
            <h2 class="text-lg font-black uppercase tracking-tight text-black">+ Create New Collection</h2>
        </div>
        <form action="{{ route('admin.collections.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Collection Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Titanium Key Organizers" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Display Sort Order</label>
                    <input type="number" name="sort_order" value="10" min="0" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                    <span class="text-[9px] text-gray-500">Lower numbers appear first</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Visibility Status *</label>
                    <select name="status" class="w-full border-2 border-black p-2.5 text-xs bg-white focus:outline-none">
                        <option value="active"><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active (Visible)</option>
                        <option value="draft"><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Draft (Hidden)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Description / Tagline</label>
                    <textarea name="description" rows="2" placeholder="Brief curation summary..." class="w-full border-2 border-black p-2.5 text-xs focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Cover Image</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full border-2 border-black p-2 text-xs bg-white focus:outline-none">
                    <span class="text-[9px] text-gray-400">JPG, PNG, WebP (Max 5MB)</span>
                </div>
            </div>

            <button type="submit" class="bg-black text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                Create Collection →
            </button>
        </form>
    </div>

    <!-- Collections List -->
    <div class="space-y-4">
        <h2 class="text-xl font-black uppercase tracking-tight text-black border-b-2 border-black pb-2">
            Existing Collections ({{ $collections->count() }})
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($collections as $col)
                @php
                    $img = $col->image_url ?: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400';
                    $imgUrl = str_starts_with($img, 'http') ? $img : url($img);
                @endphp
                <div class="bg-white border-2 border-black p-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col justify-between" x-data="{ editOpen: false }">
                    
                    <!-- Main Card Preview -->
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 border border-black bg-gray-100 overflow-hidden relative shrink-0">
                            <img src="{{ $imgUrl }}" alt="Cover Image — {{ $col->title }}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-[10px] font-mono text-gray-500 font-bold">/{{ $col->slug }}</span>
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 border {{ $col->status === 'active' ? 'bg-green-100 text-green-800 border-green-600' : 'bg-gray-100 text-gray-700 border-gray-400' }}">
                                    {{ $col->status }}
                                </span>
                                <span class="text-[9px] font-mono bg-gray-100 px-1 py-0.2">Order: {{ $col->sort_order }}</span>
                            </div>
                            <h3 class="font-black text-base uppercase tracking-tight text-black truncate">{{ $col->title }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $col->products_count }} product(s) linked</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-gray-200 pt-3 mt-4">
                        <a href="{{ route('collections.show', $col->slug) }}" target="_blank" class="text-[10px] font-bold uppercase text-gray-600 hover:text-black">
                            ↗ Storefront Page
                        </a>
                        <div class="space-x-2">
                            <button @click="editOpen = !editOpen" class="border border-black bg-black text-white px-3 py-1 text-[10px] font-bold uppercase hover:bg-gray-800">
                                <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Edit
                            </button>
                            <form action="{{ route('admin.collections.delete', $col->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete collection &quot;{{ addslashes($col->title) }}&quot;? Products will not be deleted.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="border border-red-600 text-red-600 px-2.5 py-1 text-[10px] font-bold uppercase hover:bg-red-50">
                                    <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Slide-down Edit Form -->
                    <div x-show="editOpen" x-cloak class="mt-4 pt-4 border-t-2 border-black space-y-4">
                        <form action="{{ route('admin.collections.update', $col->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[9px] font-bold uppercase text-gray-700 mb-0.5">Title</label>
                                <input type="text" name="title" value="{{ $col->title }}" required class="w-full border border-black p-2 text-xs">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-700 mb-0.5">Sort Order</label>
                                    <input type="number" name="sort_order" value="{{ $col->sort_order }}" class="w-full border border-black p-2 text-xs font-mono">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-700 mb-0.5">Status</label>
                                    <select name="status" class="w-full border border-black p-2 text-xs bg-white">
                                        <option value="active" {{ $col->status === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="draft" {{ $col->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold uppercase text-gray-700 mb-0.5">Description</label>
                                <textarea name="description" rows="2" class="w-full border border-black p-2 text-xs">{{ $col->description }}</textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="bg-black text-white px-4 py-1.5 text-xs font-bold uppercase hover:bg-gray-800">
                                    Save
                                </button>
                                <button type="button" @click="editOpen = false" class="border border-black px-3 py-1.5 text-xs font-bold uppercase">
                                    Cancel
                                </button>
                            </div>
                        </form>

                        <!-- Cover image replace -->
                        <form action="{{ route('admin.collections.replace-image', $col->id) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-gray-200">
                            @csrf
                            <label class="block text-[9px] font-bold uppercase text-gray-700 mb-1">Replace Cover Image for: {{ $col->title }}</label>
                            <div class="flex items-center gap-2">
                                <input type="file" name="image" required accept="image/jpeg,image/png,image/webp" class="border border-black p-1 text-[10px] w-full bg-white">
                                <button type="submit" class="bg-black text-white px-3 py-1.5 text-[10px] font-bold uppercase shrink-0 hover:bg-gray-800">
                                    Upload
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
