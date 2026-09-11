<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Suite — Atelier Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f5f5f0; }
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid #000;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-4 sm:p-8" x-data="adminApp()">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8 border-b-2 border-black pb-6">
            <div>
                <span class="text-xs uppercase font-bold tracking-widest text-gray-500">ADMINISTRATIVE CONSOLE</span>
                <h1 class="text-3xl font-black uppercase tracking-tight text-black">ATELIER Admin Suite</h1>
                <p class="text-xs text-gray-500 mt-0.5">Products · Images · Banners · Collections · Users & Security</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('home') }}" class="text-xs font-bold uppercase tracking-wider text-gray-700 hover:text-black border border-black px-4 py-2 bg-white">
                    ← Storefront
                </a>
                <a href="{{ route('account') }}" class="text-xs font-bold uppercase tracking-wider text-black border border-black px-4 py-2 bg-white hover:bg-gray-100">
                    Client Area
                </a>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-bold uppercase tracking-wider text-white bg-black hover:bg-red-600 px-4 py-2 transition-colors border border-black">
                        <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Toast Notification -->
        <div 
            x-show="toast.show" 
            x-transition 
            :class="toast.isError ? 'bg-red-900 border-red-500' : 'bg-black border-green-500'" 
            class="fixed bottom-6 right-6 z-50 text-white border-l-4 p-4 shadow-2xl max-w-md flex items-center gap-3"
            style="display: none;"
        >
            <span x-text="toast.isError ? '<svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>' : '✓'" class="text-lg font-bold"></span>
            <div>
                <p x-text="toast.message" class="text-xs font-bold tracking-wide"></p>
                <p x-show="toast.subtext" x-text="toast.subtext" class="text-[10px] text-gray-300"></p>
            </div>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="bg-black text-white p-4 mb-6 text-xs font-bold uppercase tracking-wider">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-600 text-white p-4 mb-6 text-xs font-bold uppercase tracking-wider">
                <svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> {{ session('error') }}
            </div>
        @endif

        <!-- Navigation Tabs -->
        <div class="flex border-b-2 border-black mb-8 gap-1 overflow-x-auto pb-0">
            <button 
                @click="tab = 'manage'" 
                :class="tab === 'manage' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
                class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider border-t-2 border-l-2 border-r-2 border-black transition-colors whitespace-nowrap"
            >
                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg> Products ({{ $products->count() }})
            </button>
            <button 
                @click="tab = 'collections'" 
                :class="tab === 'collections' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
                class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider border-t-2 border-l-2 border-r-2 border-black transition-colors whitespace-nowrap"
            >
                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg> Collections ({{ $collections->count() }})
            </button>
            <button 
                @click="tab = 'products'" 
                :class="tab === 'products' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
                class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider border-t-2 border-l-2 border-r-2 border-black transition-colors whitespace-nowrap"
            >
                <svg class="w-8 h-8 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg> Images
            </button>
            <button 
                @click="tab = 'banners'" 
                :class="tab === 'banners' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
                class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider border-t-2 border-l-2 border-r-2 border-black transition-colors whitespace-nowrap"
            >
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z"/></svg> Banners
            </button>
            <button 
                @click="tab = 'users'" 
                :class="tab === 'users' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
                class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider border-t-2 border-l-2 border-r-2 border-black transition-colors whitespace-nowrap"
            >
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg> Users ({{ $users->count() }})
            </button>
        </div>

        <!-- TAB: COLLECTIONS MANAGEMENT -->
        <div x-cloak x-show="tab === 'collections'" class="space-y-8">

            <!-- Create New Collection -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="border-b-2 border-black pb-4 mb-6">
                    <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">NEW COLLECTION</span>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-black">+ Create New Collection</h2>
                    <p class="text-xs text-gray-600 mt-1">Collections are the categories shown in your store nav (e.g. MagSafe Wallets, Slim Bifolds).</p>
                </div>
                <form action="{{ route('admin.collections.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Collection Name *</label>
                            <input type="text" name="title" required placeholder="e.g. Limited Edition" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Display Order</label>
                            <input type="number" name="sort_order" min="0" placeholder="e.g. 80" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                            <span class="text-[10px] text-gray-500">Lower = appears first in nav</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border-2 border-black p-2.5 text-sm bg-white focus:outline-none">
                                <option value="active"><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active (Visible)</option>
                                <option value="draft"><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Draft (Hidden)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Short description for this collection..." class="w-full border-2 border-black p-2.5 text-sm focus:outline-none"></textarea>
                    </div>
                    <button type="submit" class="bg-black text-white px-8 py-3 text-xs font-bold uppercase tracking-widest hover:bg-gray-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        Create Collection →
                    </button>
                </form>
            </div>

            <!-- All Collections List -->
            <div class="space-y-3">
                <div class="flex items-center gap-3 border-b-2 border-black pb-3">
                    <h2 class="text-xl font-black uppercase tracking-tight text-black">All Collections</h2>
                    <span class="text-[10px] bg-black text-white px-2 py-0.5 font-bold">{{ $collections->count() }} TOTAL</span>
                </div>

                @foreach($collections->sortBy('sort_order') as $col)
                    <div class="bg-white border-2 border-black p-5 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]" x-data="{ colEditOpen: false }">
                        <div class="flex items-center gap-4">
                            <!-- Collection Image -->
                            @if($col->image_url)
                                <img src="{{ str_starts_with($col->image_url, 'http') ? $col->image_url : url($col->image_url) }}" alt="{{ $col->title }}" class="w-14 h-14 object-cover border border-black flex-shrink-0">
                            @else
                                <div class="w-14 h-14 bg-gray-100 border border-black flex items-center justify-center flex-shrink-0">
                                    <span class="text-xl"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg></span>
                                </div>
                            @endif

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-[10px] text-gray-400">{{ $col->slug }}</span>
                                    <span class="text-[10px] px-2 py-0.5 font-bold uppercase border {{ $col->status === 'active' ? 'bg-green-100 border-green-600 text-green-800' : 'bg-gray-100 border-gray-400 text-gray-700' }}">
                                        {{ $col->status }}
                                    </span>
                                    <span class="text-[10px] bg-gray-100 border border-gray-300 px-2 py-0.5">Order: {{ $col->sort_order }}</span>
                                </div>
                                <h3 class="font-black text-base uppercase tracking-tight mt-0.5">{{ $col->title }}</h3>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $col->products()->count() }} product(s)
                                    @if($col->description) · {{ Str::limit($col->description, 60) }} @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                                <a href="{{ route('collections.show', ['slug' => $col->slug]) }}" target="_blank" class="border border-black px-3 py-1.5 text-[10px] font-bold uppercase hover:bg-gray-100">
                                    ↗ View
                                </a>
                                <button @click="colEditOpen = !colEditOpen" class="border-2 border-black bg-black text-white px-3 py-1.5 text-[10px] font-bold uppercase hover:bg-gray-800">
                                    <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Edit
                                </button>
                                <form action="{{ route('admin.collections.delete', $col->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete collection &quot;{{ addslashes($col->title) }}&quot;? Products will be unlinked but not deleted.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="border border-red-600 text-red-600 px-3 py-1.5 text-[10px] font-bold uppercase hover:bg-red-50">
                                        <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg> Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <div x-show="colEditOpen" x-cloak class="mt-5 pt-5 border-t-2 border-black">
                            <form action="{{ route('admin.collections.update', $col->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Collection Name</label>
                                        <input type="text" name="title" value="{{ $col->title }}" required class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Display Order</label>
                                        <input type="number" name="sort_order" value="{{ $col->sort_order }}" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Status</label>
                                        <select name="status" class="w-full border-2 border-black p-2.5 text-sm bg-white focus:outline-none">
                                            <option value="active" {{ $col->status === 'active' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active</option>
                                            <option value="draft" {{ $col->status === 'draft' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Draft</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Description</label>
                                    <textarea name="description" rows="2" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">{{ $col->description }}</textarea>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="submit" class="bg-black text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-gray-800">
                                        Save →
                                    </button>
                                    <button type="button" @click="colEditOpen = false" class="border border-black px-4 py-2.5 text-xs font-bold uppercase">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 0: PRODUCT MANAGEMENT -->
        <div x-cloak x-show="tab === 'manage'" class="space-y-8">

            <!-- Create New Product -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="border-b-2 border-black pb-4 mb-6">
                    <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">NEW PRODUCT</span>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-black">+ Create New Product</h2>
                    <p class="text-xs text-gray-600 mt-1">Fill in the fields below then click Create. You can add images after creation from the Image Manager tab.</p>
                </div>

                <form action="{{ route('admin.products.create') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Product Name / Title *</label>
                            <input type="text" name="title" required placeholder="e.g. ATELIER Slim MagSafe Wallet" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">SKU Code *</label>
                            <input type="text" name="sku" required placeholder="e.g. ATL-WLT-001" class="w-full border-2 border-black p-2.5 text-sm font-mono focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Retail Price (EGP) *</label>
                            <input type="number" name="retail_price_minor" step="0.01" min="0" required placeholder="e.g. 780" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                            <span class="text-[10px] text-gray-500">Enter in EGP (e.g. 780 = 780 EGP)</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Cost Price (EGP)</label>
                            <input type="number" name="cost_price_minor" step="0.01" min="0" placeholder="e.g. 350" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Stock / Inventory</label>
                            <input type="number" name="inventory" min="0" placeholder="e.g. 50" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border-2 border-black p-2.5 text-sm bg-white focus:outline-none">
                                <option value="active"><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active (Visible to customers)</option>
                                <option value="draft"><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Draft (Hidden from store)</option>
                                <option value="archived"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg> Archived</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Product Description</label>
                        <textarea name="description" rows="4" placeholder="Describe this product in detail..." class="w-full border-2 border-black p-2.5 text-sm focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-2">Assign to Collections</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($collections as $col)
                                <label class="flex items-center gap-1.5 cursor-pointer border border-black px-3 py-1.5 text-xs font-bold">
                                    <input type="checkbox" name="collection_ids[]" value="{{ $col->id }}" class="accent-black">
                                    {{ $col->title }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="bg-black text-white px-8 py-3.5 text-xs font-bold uppercase tracking-widest hover:bg-gray-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        Create Product →
                    </button>
                </form>
            </div>

            <!-- Existing Products Edit List -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 border-b-2 border-black pb-3 mb-4">
                    <h2 class="text-xl font-black uppercase tracking-tight text-black">All Products</h2>
                    <span class="text-[10px] bg-black text-white px-2 py-0.5 font-bold">{{ $products->count() }} TOTAL</span>
                </div>

                @foreach($products as $product)
                    @php
                        $productImg = $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url)) : null;
                        $productCollectionIds = $product->collections->pluck('id')->toArray();
                    @endphp
                    <div class="bg-white border-2 border-black p-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]" x-data="{ editOpen: false }">
                        <!-- Product Row Header -->
                        <div class="flex items-start gap-4">
                            <!-- Thumbnail -->
                            @if($productImg)
                                <img src="{{ $productImg }}" alt="{{ $product->title }}" class="w-16 h-16 object-cover border border-black flex-shrink-0">
                            @else
                                <div class="w-16 h-16 bg-gray-200 border border-black flex items-center justify-center flex-shrink-0">
                                    <span class="text-2xl"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></span>
                                </div>
                            @endif

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-[10px] text-gray-500 uppercase">{{ $product->sku }}</span>
                                    <span class="text-[10px] px-2 py-0.5 font-bold uppercase border {{ $product->status === 'active' ? 'bg-green-100 border-green-600 text-green-800' : 'bg-gray-100 border-gray-400 text-gray-700' }}">
                                        {{ $product->status }}
                                    </span>
                                </div>
                                <h3 class="font-black text-base uppercase tracking-tight mt-0.5 truncate">{{ $product->title }}</h3>
                                <div class="flex items-center gap-4 mt-1 text-xs text-gray-600">
                                    <span><strong>{{ number_format($product->retail_price_minor / 100, 0) }} EGP</strong></span>
                                    <span>Stock: <strong>{{ $product->inventory }}</strong></span>
                                    <span>Images: <strong>{{ $product->mediaAssets->count() }}</strong></span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ route('products.show', ['slug' => $product->slug]) }}" target="_blank" class="border border-black px-3 py-1.5 text-[10px] font-bold uppercase hover:bg-gray-100" title="View on storefront">
                                    ↗ View
                                </a>
                                <button @click="editOpen = !editOpen" class="border-2 border-black bg-black text-white px-3 py-1.5 text-[10px] font-bold uppercase hover:bg-gray-800">
                                    <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Edit
                                </button>
                                <form action="{{ route('admin.products.toggle-status', $product->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="border border-black px-3 py-1.5 text-[10px] font-bold uppercase {{ $product->status === 'active' ? 'hover:bg-yellow-50' : 'hover:bg-green-50' }}" title="Toggle visibility">
                                        {{ $product->status === 'active' ? '⏸ Hide' : '▶ Show' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.products.delete', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete {{ addslashes($product->title) }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="border border-red-600 text-red-600 px-3 py-1.5 text-[10px] font-bold uppercase hover:bg-red-50">
                                        <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg> Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Inline Edit Form (Collapsible) -->
                        <div x-show="editOpen" x-cloak class="mt-6 pt-5 border-t-2 border-black">
                            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Product Title</label>
                                        <input type="text" name="title" value="{{ $product->title }}" required class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Retail Price (EGP)</label>
                                        <input type="number" name="retail_price_minor" step="0.01" value="{{ $product->retail_price_minor / 100 }}" required class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Cost Price (EGP)</label>
                                        <input type="number" name="cost_price_minor" step="0.01" value="{{ $product->cost_price_minor / 100 }}" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Stock / Inventory</label>
                                        <input type="number" name="inventory" value="{{ $product->inventory }}" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Status</label>
                                    <select name="status" class="border-2 border-black p-2.5 text-sm bg-white focus:outline-none">
                                        <option value="active" {{ $product->status === 'active' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active</option>
                                        <option value="draft" {{ $product->status === 'draft' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Draft</option>
                                        <option value="archived" {{ $product->status === 'archived' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg> Archived</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Description</label>
                                    <textarea name="description" rows="3" class="w-full border-2 border-black p-2.5 text-sm focus:outline-none">{{ $product->description }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-2">Collections</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($collections as $col)
                                            <label class="flex items-center gap-1.5 cursor-pointer border px-3 py-1.5 text-xs font-bold {{ in_array($col->id, $productCollectionIds) ? 'border-black bg-black text-white' : 'border-black bg-white text-black' }}">
                                                <input type="checkbox" name="collection_ids[]" value="{{ $col->id }}" {{ in_array($col->id, $productCollectionIds) ? 'checked' : '' }} class="accent-white">
                                                {{ $col->title }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="submit" class="bg-black text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-gray-800">
                                        Save Changes →
                                    </button>
                                    <button type="button" @click="editOpen = false" class="border border-black px-4 py-2.5 text-xs font-bold uppercase">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 1: PRODUCT IMAGES -->
        <div x-show="tab === 'products'" class="space-y-8">
            <div class="bg-white border-2 border-black p-4 text-xs text-gray-700 flex items-center gap-3">
                <span class="text-xl"><svg class="w-4 h-4 inline-block text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg></span>
                <span><strong>Smart Image Management:</strong> Click or tap directly on any product thumbnail or gallery image below to instantly replace it with a new file. Images are uploaded asynchronously with live preview updates. Only JPG, PNG, and WebP files up to 5MB are accepted.</span>
            </div>

            @foreach($products as $product)
                <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                    <!-- Product Header -->
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center border-b-2 border-black pb-4 mb-6 gap-4">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">SKU: {{ $product->sku }} | ID: #{{ $product->id }}</span>
                            <h2 class="text-2xl font-black uppercase tracking-tight text-black">{{ $product->title }}</h2>
                            <span class="text-xs text-gray-600">Price: {{ number_format($product->retail_price_minor / 100, 2) }} {{ $product->currency }} | Stock: {{ $product->inventory }}</span>
                        </div>
                        <div>
                            <button 
                                @click="triggerNewGalleryUpload({{ $product->id }})"
                                class="bg-black text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-800"
                            >
                                + Add Gallery Image
                            </button>
                            <input 
                                type="file" 
                                id="new-gallery-file-{{ $product->id }}" 
                                class="hidden" 
                                accept="image/jpeg,image/png,image/webp"
                                @change="uploadNewGalleryImage($event, {{ $product->id }})"
                            >
                        </div>
                    </div>

                    <!-- Images Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <!-- MAIN COVER IMAGE -->
                        <div class="border-2 border-black p-4 bg-gray-50 flex flex-col justify-between relative group">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider bg-black text-white px-2 py-0.5">MAIN COVER</span>
                                    <span class="text-[10px] text-gray-500 font-mono">Primary Image</span>
                                </div>
                                <div class="text-xs font-bold text-gray-900 mb-3 truncate" title="Main Image — {{ $product->title }}">
                                    Main Image — {{ $product->title }}
                                </div>
                            </div>

                            <!-- Clickable Image Thumbnail -->
                            <div 
                                class="relative aspect-square border-2 border-black bg-white overflow-hidden cursor-pointer hover:opacity-90 transition-all"
                                @click="triggerMainImageUpload({{ $product->id }})"
                                title="Click to replace main image for {{ $product->title }}"
                            >
                                <img 
                                    id="main-img-preview-{{ $product->id }}"
                                    src="{{ $product->image_url ? (str_starts_with($product->image_url, 'http') ? $product->image_url : url($product->image_url)) : 'https://via.placeholder.com/400x400?text=No+Image' }}" 
                                    alt="Image for: {{ $product->title }}"
                                    class="w-full h-full object-cover object-center"
                                >
                                
                                <!-- Hover Overlay -->
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-3 text-center">
                                    <span class="text-lg mb-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg></span>
                                    <span class="text-xs font-bold uppercase tracking-wider">Click to Replace</span>
                                    <span class="text-[9px] text-gray-300 mt-1">Image for: {{ $product->title }}</span>
                                </div>

                                <!-- Loading Spinner Overlay -->
                                <div 
                                    id="main-img-loader-{{ $product->id }}" 
                                    class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center text-white p-2 text-center"
                                    style="display: none;"
                                >
                                    <div class="spinner mb-2"></div>
                                    <span class="text-[10px] font-bold uppercase">Uploading...</span>
                                </div>

                                <!-- Success Indicator Overlay -->
                                <div 
                                    id="main-img-success-{{ $product->id }}" 
                                    class="absolute inset-0 bg-green-600/90 flex flex-col items-center justify-center text-white p-2 text-center"
                                    style="display: none;"
                                >
                                    <span class="text-2xl font-bold">✓</span>
                                    <span class="text-xs font-bold uppercase tracking-wider">Updated!</span>
                                </div>
                            </div>

                            <input 
                                type="file" 
                                id="main-file-input-{{ $product->id }}" 
                                class="hidden" 
                                accept="image/jpeg,image/png,image/webp"
                                @change="uploadMainImage($event, {{ $product->id }}, '{{ addslashes($product->title) }}')"
                            >

                            <div class="mt-3 text-[10px] text-gray-600 text-center font-bold uppercase tracking-wider">
                                Image for: {{ $product->title }}
                            </div>
                        </div>

                        <!-- GALLERY IMAGES -->
                        @foreach($product->mediaAssets as $index => $asset)
                            @php 
                                $assetUrl = str_starts_with($asset->url, 'http') ? $asset->url : url($asset->url);
                                $slotNum = $index + 1;
                            @endphp
                            <div class="border-2 border-black p-4 bg-white flex flex-col justify-between relative group">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-bold uppercase tracking-wider bg-gray-200 text-gray-800 px-2 py-0.5">
                                            GALLERY SLOT #{{ $slotNum }}
                                        </span>
                                        <form action="{{ route('admin.quick-edit.delete-media', $asset->id) }}" method="POST" onsubmit="return confirm('Remove this gallery image permanently?');">
                                            @csrf
                                            <button type="submit" class="text-[10px] text-red-600 hover:text-red-900 font-bold uppercase">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                    <div class="text-xs font-bold text-gray-900 mb-3 truncate" title="Gallery Image {{ $slotNum }} — {{ $product->title }}">
                                        Gallery Image {{ $slotNum }} — {{ $product->title }}
                                    </div>
                                </div>

                                <!-- Clickable Gallery Thumbnail -->
                                <div 
                                    class="relative aspect-square border border-black bg-gray-100 overflow-hidden cursor-pointer hover:opacity-90 transition-all"
                                    @click="triggerGalleryAssetUpload({{ $asset->id }})"
                                    title="Click to replace Gallery Image {{ $slotNum }} for {{ $product->title }}"
                                >
                                    <img 
                                        id="asset-img-preview-{{ $asset->id }}"
                                        src="{{ $assetUrl }}" 
                                        alt="Image for: {{ $product->title }}"
                                        class="w-full h-full object-cover object-center"
                                    >

                                    <!-- Hover Overlay -->
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-3 text-center">
                                        <span class="text-lg mb-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg></span>
                                        <span class="text-xs font-bold uppercase tracking-wider">Click to Replace</span>
                                        <span class="text-[9px] text-gray-300 mt-1">Image for: {{ $product->title }}</span>
                                    </div>

                                    <!-- Loading Spinner Overlay -->
                                    <div 
                                        id="asset-img-loader-{{ $asset->id }}" 
                                        class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center text-white p-2 text-center"
                                        style="display: none;"
                                    >
                                        <div class="spinner mb-2"></div>
                                        <span class="text-[10px] font-bold uppercase">Uploading...</span>
                                    </div>

                                    <!-- Success Indicator Overlay -->
                                    <div 
                                        id="asset-img-success-{{ $asset->id }}" 
                                        class="absolute inset-0 bg-green-600/90 flex flex-col items-center justify-center text-white p-2 text-center"
                                        style="display: none;"
                                    >
                                        <span class="text-2xl font-bold">✓</span>
                                        <span class="text-xs font-bold uppercase tracking-wider">Updated!</span>
                                    </div>
                                </div>

                                <input 
                                    type="file" 
                                    id="asset-file-input-{{ $asset->id }}" 
                                    class="hidden" 
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="uploadGalleryAsset($event, {{ $asset->id }}, '{{ addslashes($product->title) }}')"
                                >

                                <div class="mt-3 text-[10px] text-gray-600 text-center font-bold uppercase tracking-wider">
                                    Image for: {{ $product->title }}
                                </div>
                            </div>
                        @endforeach

                    </div>

                    <!-- Variant Color Photo Linker -->
                    @if($product->variants->count() > 0)
                        <div class="mt-8 pt-6 border-t-2 border-black/10">
                            <h3 class="text-sm font-black uppercase tracking-wider mb-4 text-black">Link Color Swatches to Images</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($product->variants as $variant)
                                    @php 
                                        $cName = $variant->attributes_json['color'] ?? ($variant->title ?? 'Variant');
                                        $cHex = $variant->attributes_json['color_hex'] ?? '#000000';
                                        $currentImg = $variant->attributes_json['image_url'] ?? '';
                                    @endphp
                                    <form action="{{ route('admin.quick-edit.update-variant', $variant->id) }}" method="POST" class="flex items-center gap-3 bg-gray-50 p-3 border border-black">
                                        @csrf
                                        <div class="flex items-center gap-2 w-32 shrink-0">
                                            <span class="w-4 h-4 border border-black inline-block" style="background-color: {{ $cHex }};"></span>
                                            <span class="font-bold text-xs truncate">{{ $cName }}</span>
                                        </div>
                                        
                                        <select name="image_url" class="border border-black p-1.5 w-full text-xs bg-white">
                                            <option value="">(Default Product Cover)</option>
                                            @foreach($product->mediaAssets as $asset)
                                                @php $assetUrl = str_starts_with($asset->url, 'http') ? $asset->url : url($asset->url); @endphp
                                                <option value="{{ $assetUrl }}" {{ $currentImg === $assetUrl ? 'selected' : '' }}>
                                                    Gallery Image: {{ $asset->filename }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="bg-black text-white px-3 py-1.5 text-[10px] font-bold uppercase shrink-0 hover:bg-gray-800">
                                            Save
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

        <!-- TAB 2: PROMO BANNERS & COLLECTIONS -->
        <div x-cloak x-show="tab === 'banners'" class="space-y-8">
            <!-- Homepage Promotional Banner -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="border-b-2 border-black pb-4 mb-6">
                    <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">HOMEPAGE HERO & PROMO</span>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-black">Promotional Banner Image</h2>
                    <p class="text-xs text-gray-600 mt-1">Click the banner thumbnail below to replace the live homepage banner image.</p>
                </div>

                @php
                    $bannerImg = $settings['homepage_banner_image'] ?? '';
                    $bannerImgUrl = $bannerImg ? (str_starts_with($bannerImg, 'http') ? $bannerImg : url($bannerImg)) : 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=1800&q=85';
                    $currentHeight = $settings['homepage_banner_height'] ?? 'medium';
                    $currentPos = $settings['homepage_banner_position'] ?? 'center center';
                    $currentFit = $settings['homepage_banner_fit'] ?? 'cover';
                    $currentZoom = $settings['homepage_banner_zoom'] ?? '100';
                    $currentOverlay = $settings['homepage_banner_overlay'] ?? 'medium';
                @endphp

                <div 
                    class="border-2 border-black p-6 bg-gray-50 max-w-4xl relative" 
                    x-data="{
                        height: '{{ $currentHeight }}',
                        position: '{{ $currentPos }}',
                        fit: '{{ $currentFit }}',
                        zoom: '{{ $currentZoom }}',
                        overlay: '{{ $currentOverlay }}',
                        get heightStyle() {
                            if (this.height === 'compact') return '300px';
                            if (this.height === 'medium') return '420px';
                            if (this.height === 'large') return '560px';
                            if (this.height === 'full') return '75vh';
                            return this.height.includes('px') || this.height.includes('vh') ? this.height : '420px';
                        }
                    }"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-xs font-bold text-black uppercase">Live Interactive Preview</span>
                            <span class="text-[10px] text-gray-500 block">Click image to upload new file, or adjust dimensions & focal point below</span>
                        </div>
                        <span class="text-[10px] bg-black text-white px-2 py-0.5 font-bold uppercase">1-Click Replace</span>
                    </div>

                    <!-- Live Preview Box -->
                    <div 
                        class="relative w-full border-2 border-black bg-black overflow-hidden cursor-pointer transition-all duration-300"
                        :style="'height: ' + heightStyle"
                        @click="triggerPromoBannerUpload()"
                        title="Click to replace Homepage Promotional Banner"
                    >
                        <img 
                            id="banner-img-preview"
                            src="{{ $bannerImgUrl }}" 
                            alt="Image for: Homepage Promotional Banner"
                            class="w-full h-full grayscale contrast-125 transition-all duration-300"
                            :style="'object-position: ' + position + '; object-fit: ' + fit + '; transform: scale(' + (zoom/100) + ');'"
                        >

                        <!-- Dark Overlay Preview -->
                        <div 
                            class="absolute inset-0 transition-colors pointer-events-none"
                            :class="{
                                'bg-transparent': overlay === 'none',
                                'bg-black/25': overlay === 'light',
                                'bg-gradient-to-t from-black via-black/60 to-black/40': overlay === 'medium',
                                'bg-gradient-to-t from-black via-black/80 to-black/60': overlay === 'dark'
                            }"
                        ></div>

                        <!-- Sample Overlay Text Preview -->
                        <div class="absolute inset-0 p-6 flex flex-col justify-end text-white pointer-events-none">
                            <span class="text-[9px] font-bold uppercase tracking-widest border border-white/40 px-2 py-0.5 bg-black/60 w-fit mb-2">
                                LIVE BANNER PREVIEW
                            </span>
                            <h3 class="text-xl sm:text-2xl font-black uppercase text-white drop-shadow" x-text="$refs.titleInput ? $refs.titleInput.value : '{{ addslashes($settings['homepage_banner_title'] ?? 'EXCLUSIVE ARCHIVE RELEASE') }}'"></h3>
                            <p class="text-xs text-white/80 max-w-lg mt-1" x-text="$refs.subInput ? $refs.subInput.value : '{{ addslashes($settings['homepage_banner_subtitle'] ?? 'Handcrafted full-grain Italian leather...') }}'"></p>
                        </div>

                        <div class="absolute inset-0 bg-black/60 opacity-0 hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-4 text-center">
                            <span class="text-2xl mb-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg></span>
                            <span class="text-sm font-bold uppercase tracking-wider">Click to Replace Image</span>
                            <span class="text-xs text-gray-300 mt-1">Image for: Homepage Promotional Banner</span>
                        </div>

                        <!-- Loading Spinner -->
                        <div id="banner-img-loader" class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center text-white p-2 text-center" style="display: none;">
                            <div class="spinner mb-2"></div>
                            <span class="text-xs font-bold uppercase">Uploading New Banner...</span>
                        </div>

                        <!-- Success -->
                        <div id="banner-img-success" class="absolute inset-0 bg-green-600/90 flex flex-col items-center justify-center text-white p-2 text-center" style="display: none;">
                            <span class="text-3xl font-bold">✓</span>
                            <span class="text-sm font-bold uppercase tracking-wider">Banner Updated!</span>
                        </div>
                    </div>

                    <input 
                        type="file" 
                        id="promo-banner-file-input" 
                        class="hidden" 
                        accept="image/jpeg,image/png,image/webp"
                        @change="uploadPromoBanner($event)"
                    >

                    <!-- Advanced Dimension, Crop & Focal Point Controls -->
                    <form action="{{ route('admin.settings.update-banner-text') }}" method="POST" class="mt-6 pt-6 border-t-2 border-black space-y-6">
                        @csrf
                        <input type="hidden" name="homepage_banner_position" :value="position">
                        <input type="hidden" name="homepage_banner_height" :value="height">
                        <input type="hidden" name="homepage_banner_fit" :value="fit">
                        <input type="hidden" name="homepage_banner_zoom" :value="zoom">
                        <input type="hidden" name="homepage_banner_overlay" :value="overlay">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-4 border border-black">
                            
                            <!-- 1. Height / Dimensions -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-black mb-2">
                                    <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 3 0 015.196-3zm1.536-.887a2.165 2.165 0 001.083-1.838c.005-.352.054-.695.14-1.025m-1.223 2.863l2.077-1.199"/></svg> 1. Banner Height (Dimensions):
                                </label>
                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <button 
                                        type="button" 
                                        @click="height = 'compact'" 
                                        :class="height === 'compact' ? 'bg-black text-white font-bold' : 'bg-gray-100 text-black hover:bg-gray-200'"
                                        class="p-2 text-xs border border-black uppercase text-center"
                                    >
                                        Compact (300px)
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="height = 'medium'" 
                                        :class="height === 'medium' ? 'bg-black text-white font-bold' : 'bg-gray-100 text-black hover:bg-gray-200'"
                                        class="p-2 text-xs border border-black uppercase text-center"
                                    >
                                        Medium (420px)
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="height = 'large'" 
                                        :class="height === 'large' ? 'bg-black text-white font-bold' : 'bg-gray-100 text-black hover:bg-gray-200'"
                                        class="p-2 text-xs border border-black uppercase text-center"
                                    >
                                        Large (560px)
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="height = 'full'" 
                                        :class="height === 'full' ? 'bg-black text-white font-bold' : 'bg-gray-100 text-black hover:bg-gray-200'"
                                        class="p-2 text-xs border border-black uppercase text-center"
                                    >
                                        Full Screen (75vh)
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-[10px] text-gray-500 font-bold uppercase">Or Custom Height:</span>
                                    <input type="text" x-model="height" placeholder="e.g. 480px or 60vh" class="border border-black p-1.5 text-xs bg-gray-50 w-32 font-mono">
                                </div>
                            </div>

                            <!-- 2. Which part to show (Focal Point 9-Grid) -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-black mb-2">
                                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> 2. Focal Point (Which Part Appears):
                                </label>
                                <p class="text-[10px] text-gray-500 mb-2">Select where the image focus should anchor:</p>
                                <div class="grid grid-cols-3 gap-1.5 w-48 bg-gray-200 p-2 border border-black">
                                    <!-- Top Row -->
                                    <button type="button" @click="position = 'top left'" :class="position === 'top left' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Top Left">↖ Top L</button>
                                    <button type="button" @click="position = 'top center'" :class="position === 'top center' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Top Center">↑ Top</button>
                                    <button type="button" @click="position = 'top right'" :class="position === 'top right' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Top Right">↗ Top R</button>
                                    <!-- Middle Row -->
                                    <button type="button" @click="position = 'center left'" :class="position === 'center left' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Center Left">← Left</button>
                                    <button type="button" @click="position = 'center center'" :class="position === 'center center' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Center Center">● Center</button>
                                    <button type="button" @click="position = 'center right'" :class="position === 'center right' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Center Right">Right →</button>
                                    <!-- Bottom Row -->
                                    <button type="button" @click="position = 'bottom left'" :class="position === 'bottom left' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Bottom Left">↙ Btm L</button>
                                    <button type="button" @click="position = 'bottom center'" :class="position === 'bottom center' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Bottom Center">↓ Bottom</button>
                                    <button type="button" @click="position = 'bottom right'" :class="position === 'bottom right' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'" class="p-2 text-[10px] font-bold border border-black text-center" title="Bottom Right">↘ Btm R</button>
                                </div>
                                <span class="text-[10px] font-mono text-gray-600 block mt-1.5">Current Anchor: <strong x-text="position"></strong></span>
                            </div>

                        </div>

                        <!-- 3. Fit Mode, Zoom & Overlay Settings -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-white p-4 border border-black">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-black mb-1">Image Fit (Crop Mode):</label>
                                <select x-model="fit" class="w-full border border-black p-2 text-xs bg-gray-50 focus:outline-none">
                                    <option value="cover">Cover (Fill & Crop nicely)</option>
                                    <option value="contain">Contain (Show 100% Uncropped)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-black mb-1">
                                    Zoom Level: <span x-text="zoom + '%'"></span>
                                </label>
                                <input type="range" x-model="zoom" min="80" max="180" step="5" class="w-full accent-black mt-1">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-black mb-1">Dark Overlay Tint:</label>
                                <select x-model="overlay" class="w-full border border-black p-2 text-xs bg-gray-50 focus:outline-none">
                                    <option value="medium">Medium Gradient (Recommended)</option>
                                    <option value="dark">Dark Gradient (Maximum Text Contrast)</option>
                                    <option value="light">Light Tint (Show More Photo)</option>
                                    <option value="none">No Overlay</option>
                                </select>
                            </div>
                        </div>

                        <!-- 4. Banner Text Controls -->
                        <div class="space-y-3 pt-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Banner Headline (Title):</label>
                                <input type="text" x-ref="titleInput" name="homepage_banner_title" value="{{ $settings['homepage_banner_title'] ?? '' }}" placeholder="e.g. EXCLUSIVE ARCHIVE RELEASE — LIMITED DISPATCH" class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Banner Subtitle / Description:</label>
                                <textarea x-ref="subInput" name="homepage_banner_subtitle" rows="2" placeholder="e.g. Handcrafted full-grain Italian leather..." class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none">{{ $settings['homepage_banner_subtitle'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Banner Action Link:</label>
                                <input type="text" name="homepage_banner_link" value="{{ $settings['homepage_banner_link'] ?? '/collections/all' }}" placeholder="/collections/all" class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none">
                            </div>
                        </div>

                        <button type="submit" class="bg-black text-white px-8 py-3.5 text-xs font-bold uppercase tracking-widest hover:bg-gray-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            Save All Banner Dimensions & Settings →
                        </button>
                    </form>
                </div>
            </div>

            <!-- Collections Images -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="border-b-2 border-black pb-4 mb-6">
                    <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">CATEGORY ARCHIVES</span>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-black">Collection Cover Images</h2>
                    <p class="text-xs text-gray-600 mt-1">Click any collection image thumbnail below to replace it with a new cover image.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach($collections as $col)
                        @php $colUrl = $col->image_url ? (str_starts_with($col->image_url, 'http') ? $col->image_url : url($col->image_url)) : 'https://via.placeholder.com/600x400?text=' . urlencode($col->title); @endphp
                        <div class="border-2 border-black p-4 bg-gray-50 relative group flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] bg-black text-white px-2 py-0.5 font-bold uppercase block w-fit mb-2">
                                    {{ $col->slug }}
                                </span>
                                <h3 class="text-sm font-bold text-black mb-2">Image for: {{ $col->title }}</h3>
                            </div>

                            <div 
                                class="relative aspect-video border border-black bg-white overflow-hidden cursor-pointer"
                                @click="triggerCollectionUpload({{ $col->id }})"
                                title="Click to replace cover image for {{ $col->title }}"
                            >
                                <img 
                                    id="col-img-preview-{{ $col->id }}"
                                    src="{{ $colUrl }}" 
                                    alt="Image for: {{ $col->title }}"
                                    class="w-full h-full object-cover"
                                >

                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-2 text-center">
                                    <span class="text-sm font-bold uppercase">Click to Replace</span>
                                    <span class="text-[9px] text-gray-300">Image for: {{ $col->title }}</span>
                                </div>

                                <div id="col-img-loader-{{ $col->id }}" class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center text-white" style="display: none;">
                                    <div class="spinner mb-2"></div>
                                    <span class="text-[10px] font-bold uppercase">Uploading...</span>
                                </div>

                                <div id="col-img-success-{{ $col->id }}" class="absolute inset-0 bg-green-600/90 flex flex-col items-center justify-center text-white" style="display: none;">
                                    <span class="text-xl font-bold">✓</span>
                                    <span class="text-[10px] font-bold uppercase">Updated!</span>
                                </div>
                            </div>

                            <input 
                                type="file" 
                                id="col-file-input-{{ $col->id }}" 
                                class="hidden" 
                                accept="image/jpeg,image/png,image/webp"
                                @change="uploadCollectionImage($event, {{ $col->id }}, '{{ addslashes($col->title) }}')"
                            >

                            <div class="mt-3 text-[10px] text-gray-600 text-center font-bold uppercase tracking-wider">
                                Image for: {{ $col->title }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Client Access Portal Background Wallpaper -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="border-b-2 border-black pb-4 mb-6 flex flex-col sm:flex-row justify-between sm:items-end gap-3">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">PORTAL AESTHETICS</span>
                        <h2 class="text-2xl font-black uppercase tracking-tight text-black">Client Portal Background Wallpaper</h2>
                        <p class="text-xs text-gray-600 mt-1">خلفية شاشة تسجيل الدخول والوصول للمتجر (/account). اضغط على الإطار أدناه لاختيار صورة خلفية أو تعديل درجة التعتيم.</p>
                    </div>
                    @if(!empty($settings['account_portal_background_url']))
                        <form action="{{ route('admin.settings.reset-portal-bg') }}" method="POST">
                            @csrf
                            <button type="submit" class="border border-red-500 text-red-700 bg-red-50 hover:bg-red-100 text-[10px] font-bold uppercase px-3 py-1.5 transition-colors">
                                Reset to Minimalist Cream ↺
                            </button>
                        </form>
                    @endif
                </div>

                @php
                    $portalBg = $settings['account_portal_background_url'] ?? '';
                    $portalBgUrl = $portalBg ? (str_starts_with($portalBg, 'http') ? $portalBg : url($portalBg)) : '';
                    $currentPortalOverlay = $settings['account_portal_overlay'] ?? 'medium';
                @endphp

                <div class="border-2 border-black p-6 bg-gray-50 max-w-4xl space-y-5">
                    <div 
                        class="relative w-full aspect-[21/9] border-2 border-black bg-neutral-900 overflow-hidden cursor-pointer group flex items-center justify-center"
                        onclick="document.getElementById('portal-bg-file-input').click()"
                        title="Click to upload Client Portal Wallpaper"
                    >
                        @if(!empty($portalBgUrl))
                            <img id="portal-bg-preview" src="{{ $portalBgUrl }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-center p-6 text-white/70 space-y-2">
                                <span class="text-3xl block"><svg class="w-8 h-8 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg></span>
                                <span class="font-bold text-xs uppercase tracking-widest text-white block">No Custom Background Set</span>
                                <span class="text-[10px] text-gray-400 block">Currently displaying default luxury cream (#F5F5F0). Click anywhere here to upload an editorial wallpaper.</span>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white p-4 text-center">
                            <span class="text-sm font-bold uppercase">Click to Upload Wallpaper</span>
                            <span class="text-[10px] text-gray-300">Supports JPG, PNG, WebP (Up to 5MB)</span>
                        </div>

                        <div id="portal-bg-loader" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center text-white" style="display: none;">
                            <div class="spinner mb-2"></div>
                            <span class="text-[10px] font-bold uppercase">Uploading Wallpaper...</span>
                        </div>
                    </div>

                    <input 
                        type="file" 
                        id="portal-bg-file-input" 
                        class="hidden" 
                        accept="image/jpeg,image/png,image/webp"
                        onchange="uploadPortalBg(event)"
                    >

                    <!-- Overlay Selector Form -->
                    <form action="{{ route('admin.settings.portal-overlay') }}" method="POST" class="flex flex-wrap items-center justify-between gap-4 border-t border-black/10 pt-4">
                        @csrf
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-bold uppercase tracking-wider text-black">Overlay Tint (درجة التعتيم):</label>
                            <select name="account_portal_overlay" class="border border-black p-2 text-xs bg-white focus:outline-none font-mono">
                                <option value="none" {{ $currentPortalOverlay === 'none' ? 'selected' : '' }}>None (100% Bright)</option>
                                <option value="subtle" {{ $currentPortalOverlay === 'subtle' ? 'selected' : '' }}>Subtle Tint (35% Dark)</option>
                                <option value="medium" {{ $currentPortalOverlay === 'medium' ? 'selected' : '' }}>Medium Gradient (Recommended)</option>
                                <option value="dark" {{ $currentPortalOverlay === 'dark' ? 'selected' : '' }}>Dramatic Dark (80% Dark)</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-black text-white px-5 py-2 text-xs font-bold uppercase tracking-wider hover:bg-neutral-800">
                            Save Overlay Tint ✓
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 3: USER SECURITY, PASSWORD RESET & ADDRESS BOOKS -->
        <div x-cloak x-show="tab === 'users'" class="space-y-8">

            <!-- <svg class="w-3.5 h-3.5 inline-block text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> Google OAuth Designated Admins Configuration Box -->
            <div class="bg-black text-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-white/20 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><svg class="w-3.5 h-3.5 inline-block text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-white">Google OAuth Admin Accounts / مسؤولو المتجر عبر Google</h2>
                            <p class="text-xs text-white/70">أي بريد إلكتروني يُسجل هنا سيتم منحه صلاحيات المسؤول (Admin) تلقائياً بمجرد تسجيل دخوله عبر حساب Google وتحويله فوراً للوحة الأدمن.</p>
                        </div>
                    </div>
                    <span class="bg-green-500/20 border border-green-400 text-green-300 text-[10px] font-mono font-bold uppercase px-2.5 py-1">Auto-Role Promotion Active <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg></span>
                </div>

                <form action="{{ route('admin.settings.google-admins') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-white/90 mb-1.5">
                            قائمة إيميلات الأدمن المعتمدة (افصل بين الإيميلات بفاصلة أو سطر جديد):
                        </label>
                        <textarea 
                            name="admin_google_emails" 
                            rows="2" 
                            placeholder="mnosec206@gmail.com, another-admin@gmail.com" 
                            class="w-full border-2 border-white/40 p-3 text-xs font-mono bg-white/10 text-white placeholder-white/40 focus:bg-black focus:border-white focus:outline-none"
                        >{{ $settings['admin_google_emails'] ?? '' }}</textarea>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-3 pt-1">
                        <span class="text-[11px] text-white/60"><svg class="w-4 h-4 inline-block text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg> يمكنك إضافة أكثر من حساب لمديري المتجر أو شركائك.</span>
                        <button type="submit" class="bg-white text-black hover:bg-gray-200 px-6 py-2.5 text-xs font-bold uppercase tracking-wider transition-all shadow-[3px_3px_0px_0px_rgba(255,255,255,0.4)]">
                            حفظ إيميلات الأدمن المعتمدة ✓
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="border-b-2 border-black pb-4 mb-6">
                    <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500">AUTHENTICATION & SECURITY AUDIT</span>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-black">User Accounts & Permissions</h2>
                    <p class="text-xs text-gray-600 mt-1">
                        إدارة صلاحيات المستخدمين والمسؤولين وإرسال روابط إعادة تعيين كلمات المرور.
                    </p>
                </div>

                <div class="space-y-6">
                    @foreach($users as $u)
                        <div class="border-2 border-black p-6 bg-white">
                            <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-gray-200 pb-4 mb-4 gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-black text-black">{{ $u->name }}</h3>
                                        @if($u->isAdmin())
                                            <span class="bg-black text-white text-[9px] font-bold px-2 py-0.5 uppercase tracking-wider"><svg class="w-3.5 h-3.5 inline-block text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> Admin</span>
                                        @endif
                                        @if($u->isBanned())
                                            <span class="bg-red-600 text-white text-[9px] font-bold px-2 py-0.5 uppercase tracking-wider">Banned</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-600">{{ $u->email }} | Registered: {{ $u->created_at->format('M d, Y') }}</span>
                                </div>

                                <!-- Admin Actions: Toggle Role & Password Reset -->
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('admin.users.toggle-admin', $u->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="border-2 border-black {{ $u->isAdmin() ? 'bg-amber-100 text-amber-950 hover:bg-amber-200' : 'bg-white text-black hover:bg-black hover:text-white' }} px-3 py-2 text-xs font-bold uppercase tracking-wider transition-colors">
                                            {{ $u->isAdmin() ? '<svg class="w-3.5 h-3.5 inline-block text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> مسؤول (إلغاء)' : '+ ترقية لأدمن' }}
                                        </button>
                                    </form>
                                    <button 
                                        type="button" 
                                        @click="sendAdminPasswordReset({{ $u->id }}, '{{ $u->email }}')"
                                        :disabled="resettingUserId === {{ $u->id }}"
                                        class="bg-black text-white px-3 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 disabled:opacity-50 transition-all flex items-center gap-1.5"
                                    >
                                        <span x-show="resettingUserId !== {{ $u->id }}"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg> Reset Pass</span>
                                        <span x-show="resettingUserId === {{ $u->id }}">Sending...</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Activity & Security Log -->
                            <div class="bg-gray-50 border border-gray-200 p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono">
                                <div>
                                    <span class="text-gray-500 font-bold block mb-1">PASSWORD LAST CHANGED:</span>
                                    <span class="font-semibold text-gray-800" id="pwd-changed-{{ $u->id }}">
                                        {{ $u->password_changed_at ? $u->password_changed_at->format('Y-m-d H:i:s') : 'Not changed since registration' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 font-bold block mb-1">PASSWORD RESET REQUEST ACTIVITY:</span>
                                    <span class="font-semibold text-gray-800" id="pwd-reset-act-{{ $u->id }}">
                                        @if($u->password_reset_requested_at)
                                            Requested by {{ $u->password_reset_requested_by ?? 'User' }} on {{ $u->password_reset_requested_at->format('Y-m-d H:i:s') }}
                                        @else
                                            No recent reset requests
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- User Saved Delivery Addresses (Read-only for Admin Support) -->
                            <div class="pt-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-2 flex items-center gap-2">
                                    <span>Saved Delivery Addresses ({{ $u->addresses->count() }}):</span>
                                    <span class="text-[10px] text-gray-500 font-normal italic">(Read-only for customer support)</span>
                                </h4>

                                @if($u->addresses->isNotEmpty())
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach($u->addresses as $addr)
                                            <div class="border border-gray-300 p-3 bg-white text-xs">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-bold text-[10px] uppercase bg-gray-100 px-1.5 py-0.5 border">
                                                        {{ $addr->label }}
                                                    </span>
                                                    @if($addr->is_default)
                                                        <span class="text-[9px] font-bold text-black border border-black px-1">DEFAULT</span>
                                                    @endif
                                                </div>
                                                <p class="font-semibold text-black">{{ $addr->full_name }}</p>
                                                <p class="text-gray-600">{{ $addr->street_address }}, {{ $addr->city }}</p>
                                                <p class="text-gray-500 text-[11px]">Phone: {{ $addr->phone }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-gray-400 italic">No saved delivery addresses in user's address book.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- JavaScript Controller for Admin Actions -->
    <script>
        function adminApp() {
            return {
                tab: 'manage',
                resettingUserId: null,
                toast: {
                    show: false,
                    message: '',
                    subtext: '',
                    isError: false,
                },
                notify(message, subtext = '', isError = false) {
                    this.toast.message = message;
                    this.toast.subtext = subtext;
                    this.toast.isError = isError;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 4000);
                },

                // Validation helper
                validateImageFile(file) {
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        this.notify('Invalid File Type', 'Only JPG, JPEG, PNG, and WebP images are allowed.', true);
                        return false;
                    }
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        this.notify('File Size Exceeded', 'The selected image exceeds the maximum 5MB size limit.', true);
                        return false;
                    }
                    return true;
                },

                // Main Cover Image replacement
                triggerMainImageUpload(productId) {
                    document.getElementById(`main-file-input-${productId}`).click();
                },
                uploadMainImage(event, productId, productTitle) {
                    const file = event.target.files[0];
                    if (!file || !this.validateImageFile(file)) return;

                    const loader = document.getElementById(`main-img-loader-${productId}`);
                    const success = document.getElementById(`main-img-success-${productId}`);
                    const preview = document.getElementById(`main-img-preview-${productId}`);
                    
                    loader.style.display = 'flex';

                    const formData = new FormData();
                    formData.append('image', file);

                    fetch(`/admin/products/${productId}/replace-image`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        loader.style.display = 'none';
                        if (data.success) {
                            preview.src = data.image_url + '?t=' + new Date().getTime();
                            success.style.display = 'flex';
                            setTimeout(() => { success.style.display = 'none'; }, 2000);
                            this.notify('Image Updated Successfully', `Main cover image updated for: ${productTitle}`);
                        } else {
                            this.notify('Upload Failed', data.message || 'Could not update image.', true);
                        }
                    })
                    .catch(err => {
                        loader.style.display = 'none';
                        this.notify('Network Error', 'Failed to upload image.', true);
                    });
                },

                // Gallery Asset replacement
                triggerGalleryAssetUpload(assetId) {
                    document.getElementById(`asset-file-input-${assetId}`).click();
                },
                uploadGalleryAsset(event, assetId, productTitle) {
                    const file = event.target.files[0];
                    if (!file || !this.validateImageFile(file)) return;

                    const loader = document.getElementById(`asset-img-loader-${assetId}`);
                    const success = document.getElementById(`asset-img-success-${assetId}`);
                    const preview = document.getElementById(`asset-img-preview-${assetId}`);

                    loader.style.display = 'flex';

                    const formData = new FormData();
                    formData.append('image', file);

                    fetch(`/admin/media/${assetId}/replace`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        loader.style.display = 'none';
                        if (data.success) {
                            preview.src = data.image_url + '?t=' + new Date().getTime();
                            success.style.display = 'flex';
                            setTimeout(() => { success.style.display = 'none'; }, 2000);
                            this.notify('Gallery Image Updated', `Updated gallery image for: ${productTitle}`);
                        } else {
                            this.notify('Upload Failed', data.message || 'Could not update gallery image.', true);
                        }
                    })
                    .catch(err => {
                        loader.style.display = 'none';
                        this.notify('Network Error', 'Failed to upload gallery image.', true);
                    });
                },

                // New Gallery Image Upload
                triggerNewGalleryUpload(productId) {
                    document.getElementById(`new-gallery-file-${productId}`).click();
                },
                uploadNewGalleryImage(event, productId) {
                    const file = event.target.files[0];
                    if (!file || !this.validateImageFile(file)) return;

                    this.notify('Uploading Image...', 'Attaching new image to product gallery.');

                    const formData = new FormData();
                    formData.append('image', file);

                    fetch(`/admin/products/${productId}/upload-gallery`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.notify('Gallery Image Attached', 'Reloading gallery view...');
                            setTimeout(() => { window.location.reload(); }, 1000);
                        } else {
                            this.notify('Upload Failed', data.message || 'Could not attach image.', true);
                        }
                    })
                    .catch(err => {
                        this.notify('Network Error', 'Failed to attach image.', true);
                    });
                },

                // Promo Banner replacement
                triggerPromoBannerUpload() {
                    document.getElementById('promo-banner-file-input').click();
                },
                uploadPromoBanner(event) {
                    const file = event.target.files[0];
                    if (!file || !this.validateImageFile(file)) return;

                    const loader = document.getElementById('banner-img-loader');
                    const success = document.getElementById('banner-img-success');
                    const preview = document.getElementById('banner-img-preview');

                    loader.style.display = 'flex';

                    const formData = new FormData();
                    formData.append('image', file);

                    fetch('/admin/settings/replace-banner', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        loader.style.display = 'none';
                        if (data.success) {
                            preview.src = data.image_url + '?t=' + new Date().getTime();
                            success.style.display = 'flex';
                            setTimeout(() => { success.style.display = 'none'; }, 2000);
                            this.notify('Banner Updated', 'Homepage promotional banner updated successfully.');
                        } else {
                            this.notify('Upload Failed', data.message || 'Could not update banner.', true);
                        }
                    })
                    .catch(err => {
                        loader.style.display = 'none';
                        this.notify('Network Error', 'Failed to upload banner.', true);
                    });
                },

                // Collection Image replacement
                triggerCollectionUpload(colId) {
                    document.getElementById(`col-file-input-${colId}`).click();
                },
                uploadCollectionImage(event, colId, colTitle) {
                    const file = event.target.files[0];
                    if (!file || !this.validateImageFile(file)) return;

                    const loader = document.getElementById(`col-img-loader-${colId}`);
                    const success = document.getElementById(`col-img-success-${colId}`);
                    const preview = document.getElementById(`col-img-preview-${colId}`);

                    loader.style.display = 'flex';

                    const formData = new FormData();
                    formData.append('image', file);

                    fetch(`/admin/collections/${colId}/replace-image`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        loader.style.display = 'none';
                        if (data.success) {
                            preview.src = data.image_url + '?t=' + new Date().getTime();
                            success.style.display = 'flex';
                            setTimeout(() => { success.style.display = 'none'; }, 2000);
                            this.notify('Collection Image Updated', `Updated cover for: ${colTitle}`);
                        } else {
                            this.notify('Upload Failed', data.message || 'Could not update image.', true);
                        }
                    })
                    .catch(err => {
                        loader.style.display = 'none';
                        this.notify('Network Error', 'Failed to upload image.', true);
                    });
                },

                // Admin Triggered Password Reset
                sendAdminPasswordReset(userId, userEmail) {
                    this.resettingUserId = userId;
                    fetch(`/admin/users/${userId}/send-password-reset`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.resettingUserId = null;
                        if (data.success) {
                            this.notify('Password Reset Email Dispatched', data.message);
                            const actElem = document.getElementById(`pwd-reset-act-${userId}`);
                            if (actElem) {
                                actElem.innerText = `Requested by Admin on ${data.timestamp}`;
                            }
                        } else {
                            this.notify('Reset Request Failed', data.message || 'Could not dispatch reset email.', true);
                        }
                    })
                    .catch(err => {
                        this.resettingUserId = null;
                        this.notify('Network Error', 'Could not send reset request.', true);
                    });
                }
            };
        }

        function uploadPortalBg(event) {
            const file = event.target.files[0];
            if (!file) return;
            const loader = document.getElementById('portal-bg-loader');
            if (loader) loader.style.display = 'flex';
            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', document.querySelector('meta[name=csrf-token]').getAttribute('content'));

            fetch('{{ route("admin.settings.replace-portal-bg") }}', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (loader) loader.style.display = 'none';
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Upload failed');
                }
            })
            .catch(err => {
                if (loader) loader.style.display = 'none';
                alert('Network error while uploading wallpaper.');
            });
        }
    </script>
</body>
</html>
