<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Atelier Administrative Console</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: 2 }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('head-styles')
    @stack('head-scripts')
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
        
        /* Sleek Admin Theme & Glassmorphism */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 24px -4px rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .glass-panel:hover {
            box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        /* Floating Widget Animation */
        @keyframes float-widget {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }
        .widget-animated {
            animation: float-widget 6s ease-in-out infinite;
        }

        /* Pixel Art Character Animation */
        @keyframes walk-across {
            0% { transform: translateX(-10vw) scaleX(1); }
            49% { transform: translateX(110vw) scaleX(1); }
            50% { transform: translateX(110vw) scaleX(-1); }
            99% { transform: translateX(-10vw) scaleX(-1); }
            100% { transform: translateX(-10vw) scaleX(1); }
        }
        .pixel-pet {
            position: fixed;
            bottom: 10px;
            left: 0;
            width: 64px;
            height: 64px;
            /* Umbreon - sleek black/white/yellow pixel art */
            background: url('https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/versions/generation-v/black-white/animated/197.gif') no-repeat bottom center;
            background-size: contain;
            animation: walk-across 35s linear infinite;
            z-index: 9999;
            pointer-events: none;
            opacity: 0.9;
            filter: drop-shadow(0 4px 4px rgba(0,0,0,0.2));
        }

        [x-cloak] { display: none !important; }
        .ql-toolbar.ql-snow { border: 1px solid #e5e7eb !important; border-radius: 12px 12px 0 0; background: #fafafa; }
        .ql-container.ql-snow { border: 1px solid #e5e7eb !important; border-top: 0 !important; border-radius: 0 0 12px 12px; background: rgba(255,255,255,0.8); font-size: 13px; min-height: 140px; }
        
        /* Custom Scrollbar for sleekness */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.4); }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#f8f9fa] to-[#e9ecef] text-black antialiased flex flex-col relative overflow-x-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Background subtle animated gradients for visual comfort -->
    <div class="fixed inset-0 z-[-1] pointer-events-none opacity-40">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-gradient-to-r from-gray-200 to-gray-300 blur-[100px] animate-[pulse_8s_ease-in-out_infinite]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-gradient-to-r from-gray-100 to-gray-300 blur-[120px] animate-[pulse_10s_ease-in-out_infinite_reverse]"></div>
    </div>

    <!-- Top Navigation Bar -->
    <header 
        x-data="{
            searchQuery: '',
            searchResults: { products: [], orders: [], customers: [] },
            searchOpen: false,
            searchLoading: false,
            searchTimeout: null,
            init() {
                window.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        this.$refs.globalSearchInput.focus();
                        this.searchOpen = true;
                    }
                    if (e.key === 'Escape') {
                        this.searchOpen = false;
                    }
                });
            },
            onSearchInput() {
                clearTimeout(this.searchTimeout);
                if (this.searchQuery.trim().length < 2) {
                    this.searchResults = { products: [], orders: [], customers: [] };
                    this.searchOpen = false;
                    return;
                }
                this.searchLoading = true;
                this.searchOpen = true;
                this.searchTimeout = setTimeout(() => {
                    fetch(`/admin/search?q=${encodeURIComponent(this.searchQuery.trim())}`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.searchResults = data;
                        this.searchLoading = false;
                    })
                    .catch(() => { this.searchLoading = false; });
                }, 280);
            }
        }"
        class="glass-panel sticky top-0 z-40 px-4 sm:px-8 py-3 flex items-center justify-between gap-4 border-b-0 m-2 rounded-2xl"
    >
        <!-- Left: Logo & Role -->
        <div class="flex items-center gap-3 shrink-0">
            <!-- Mobile Menu Toggle -->
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="flex items-center gap-2.5">
                <span class="bg-black text-white px-2 py-0.5 text-[10px] font-mono font-bold tracking-widest uppercase">
                    {{ strtoupper(auth()->user()->admin_role ?? 'ADMIN') }}
                </span>
                <a href="{{ route('admin.dashboard') }}" class="font-black text-lg tracking-tight uppercase hover:opacity-80 transition-opacity hidden sm:inline">
                    {{ \App\Models\Setting::get('store_name', 'ATELIER') }} Studio
                </a>
            </div>
        </div>

        <!-- Center: Global Quick Search (Ctrl+K) -->
        <div class="flex-1 max-w-lg relative" @click.away="searchOpen = false">
            <div class="relative flex items-center">
                <span class="absolute left-3 text-gray-400 text-xs"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg></span>
                <input 
                    x-ref="globalSearchInput"
                    type="text" 
                    x-model="searchQuery" 
                    @input="onSearchInput()"
                    @focus="if(searchQuery.length >= 2) searchOpen = true"
                    placeholder="Search products, orders, customers... (Ctrl+K)" 
                    class="w-full border border-gray-200 rounded-xl pl-9 pr-16 py-2 text-xs bg-white/50 focus:bg-white focus:ring-2 focus:ring-black/5 focus:outline-none placeholder-gray-400 font-medium transition-all"
                >
                <kbd class="absolute right-2.5 hidden sm:inline-block bg-gray-100 text-gray-500 text-[10px] px-1.5 py-0.5 font-mono rounded-md font-semibold">Ctrl+K</kbd>
            </div>

            <!-- Search Dropdown Results -->
            <div 
                x-show="searchOpen && (searchResults.products.length > 0 || searchResults.orders.length > 0 || searchResults.customers.length > 0 || searchLoading)" 
                x-cloak 
                class="absolute left-0 right-0 top-full mt-2 bg-white/90 backdrop-blur-xl border border-gray-100 rounded-xl shadow-xl max-h-96 overflow-y-auto z-50 divide-y divide-gray-100"
            >
                <div x-show="searchLoading" class="p-4 text-center text-xs text-gray-400 font-mono">
                    Searching catalog & records...
                </div>

                <!-- Products Group -->
                <template x-if="searchResults.products && searchResults.products.length > 0">
                    <div class="p-2">
                        <span class="block text-[9px] font-mono font-bold uppercase tracking-widest text-gray-400 px-2 py-1"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg> Products</span>
                        <template x-for="p in searchResults.products" :key="'p-' + p.id">
                            <a :href="p.url" class="flex items-center justify-between p-2 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-2.5 truncate">
                                    <template x-if="p.img">
                                        <img :src="p.img" alt="" class="w-8 h-8 object-cover border border-black shrink-0">
                                    </template>
                                    <div class="truncate">
                                        <span class="block text-xs font-bold text-black truncate" x-text="p.label"></span>
                                        <span class="block text-[10px] text-gray-500 font-mono truncate" x-text="p.sub"></span>
                                    </div>
                                </div>
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 border shrink-0 ml-2" :class="p.badge === 'active' ? 'border-green-600 text-green-700 bg-green-50' : 'border-gray-400 text-gray-600'" x-text="p.badge"></span>
                            </a>
                        </template>
                    </div>
                </template>

                <!-- Orders Group -->
                <template x-if="searchResults.orders && searchResults.orders.length > 0">
                    <div class="p-2">
                        <span class="block text-[9px] font-mono font-bold uppercase tracking-widest text-gray-400 px-2 py-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg> Orders</span>
                        <template x-for="o in searchResults.orders" :key="'o-' + o.id">
                            <a :href="o.url" class="flex items-center justify-between p-2 hover:bg-gray-50 transition-colors">
                                <div>
                                    <span class="block text-xs font-bold text-black" x-text="o.label"></span>
                                    <span class="block text-[10px] text-gray-500 font-mono" x-text="o.sub"></span>
                                </div>
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 border border-black bg-gray-100 shrink-0 ml-2" x-text="o.badge"></span>
                            </a>
                        </template>
                    </div>
                </template>

                <!-- Customers Group -->
                <template x-if="searchResults.customers && searchResults.customers.length > 0">
                    <div class="p-2">
                        <span class="block text-[9px] font-mono font-bold uppercase tracking-widest text-gray-400 px-2 py-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg> Customers</span>
                        <template x-for="c in searchResults.customers" :key="'c-' + c.id">
                            <a :href="c.url" class="flex items-center justify-between p-2 hover:bg-gray-50 transition-colors">
                                <div>
                                    <span class="block text-xs font-bold text-black" x-text="c.label"></span>
                                    <span class="block text-[10px] text-gray-500 font-mono" x-text="c.sub"></span>
                                </div>
                                <span class="text-[9px] font-mono text-gray-400">Profile →</span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Right: Indicators & Logout -->
        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
            <!-- Maintenance Mode status indicator -->
            @php $isMaint = in_array((string)\App\Models\Setting::get('maintenance_mode', '0'), ['1', 'true', 'on'], true); @endphp
            @if($isMaint)
                <span class="bg-amber-400 text-black border border-black text-[9px] font-bold uppercase px-2 py-1 hidden md:flex items-center gap-1 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <span class="animate-ping w-1.5 h-1.5 bg-black rounded-full"></span>
                    <span>Maintenance Active</span>
                </span>
            @endif

            <!-- Unread notifications icon -->
            @php $globalUnread = \App\Models\AdminNotification::where('is_read', false)->count(); @endphp
            <a href="{{ route('admin.notifications.index') }}" class="relative p-2 text-gray-700 hover:text-black hover:bg-gray-100 transition-colors border border-transparent hover:border-black" title="System Notifications">
                <span class="text-base"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg></span>
                @if($globalUnread > 0)
                    <span class="absolute top-1 right-1 bg-red-600 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center font-mono">
                        {{ $globalUnread }}
                    </span>
                @endif
            </a>

            <!-- View Storefront -->
            <a href="{{ route('home') }}" target="_blank" class="hidden md:inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-gray-600 hover:text-black bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition-all">
                <span>Storefront</span>
                <span>↗</span>
            </a>

            <!-- Admin Profile & Logout -->
            <div class="flex items-center gap-2 pl-2 sm:pl-4 border-l border-gray-200">
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-black/90 text-white hover:bg-black rounded-lg px-4 py-1.5 text-xs font-bold uppercase tracking-wider shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5" title="Sign out securely">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- App Body: Sidebar + Main Content -->
    <div class="flex-1 flex max-w-[1600px] w-full mx-auto relative z-10">
        
        <!-- Sidebar Navigation -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:sticky top-[80px] left-2 h-[calc(100vh-100px)] w-64 glass-panel z-30 flex flex-col justify-between transition-transform duration-300 ease-out overflow-y-auto"
        >
            <div class="p-4 space-y-6">
                <!-- Group 1: Core Commerce -->
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 px-2">Store Overview</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.dashboard') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg></span>
                            <span>Dashboard</span>
                        </a>
                        <a 
                            href="{{ route('admin.analytics.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.analytics.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg></span>
                            <span>Analytics & Traffic</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 2: Catalog & Stock -->
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 px-2">Catalog & Stock</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.products.index') }}" 
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.products.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-sm"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg></span>
                                <span>Products</span>
                            </div>
                            <span class="text-[10px] font-mono opacity-70">{{ \App\Models\Product::count() }}</span>
                        </a>
                        <a 
                            href="{{ route('admin.inventory.index') }}" 
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.inventory.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-sm"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></span>
                                <span>Inventory & Stock</span>
                            </div>
                        </a>
                        <a 
                            href="{{ route('admin.collections.index') }}" 
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.collections.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-sm"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg></span>
                                <span>Collections</span>
                            </div>
                            <span class="text-[10px] font-mono opacity-70">{{ \App\Models\Collection::count() }}</span>
                        </a>
                        <a 
                            href="{{ route('admin.coupons.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.coupons.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/></svg></span>
                            <span>Coupons & Offers</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 3: Sales & Customers -->
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 px-2">Sales & Operations</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.orders.index') }}" 
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.orders.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></span>
                                <span>Orders</span>
                            </div>
                            @php $pendingCnt = \App\Models\Order::where('shipping_status', 'pending')->count(); @endphp
                            @if($pendingCnt > 0)
                                <span class="bg-amber-400 text-black text-[9px] px-1.5 py-0.2 font-bold font-mono">{{ $pendingCnt }} new</span>
                            @endif
                        </a>
                        <a 
                            href="{{ route('admin.abandoned-carts.index') }}" 
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.abandoned-carts.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg></span>
                                <span>Abandoned Carts</span>
                            </div>
                            @php $abandonedCnt = \App\Models\AbandonedCart::whereNull('followed_up_at')->count(); @endphp
                            @if($abandonedCnt > 0)
                                <span class="bg-red-500 text-white text-[9px] px-1.5 py-0.2 font-bold font-mono">{{ $abandonedCnt }}</span>
                            @endif
                        </a>
                        <a 
                            href="{{ route('admin.customers.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.customers.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></span>
                            <span>Customers</span>
                        </a>
                        <a 
                            href="{{ route('admin.reviews.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.reviews.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm">⭐</span>
                            <span>Product Reviews</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 4: Storefront & Configuration -->
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 px-2">Store Configuration</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.telegram.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.telegram.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg></span>
                            <span>Telegram Alerts</span>
                        </a>
                        <a 
                            href="{{ route('admin.whatsapp.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.whatsapp.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg></span>
                            <span>WhatsApp Client</span>
                        </a>
                        <a 
                            href="{{ route('admin.surveys.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.surveys.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg></span>
                            <span>Surveys & Polls</span>
                        </a>
                        <a 
                            href="{{ route('admin.content.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.content.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-4 h-4 inline-block text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg></span>
                            <span>Content, Social & WhatsApp</span>
                        </a>
                        <a 
                            href="{{ route('admin.shipping.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.shipping.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></span>
                            <span>Shipping & Tax</span>
                        </a>
                        @if(auth()->user()?->isSuperAdmin())
                            <a 
                                href="{{ route('admin.team.index') }}" 
                                class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.team.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                            >
                                <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg></span>
                                <span>Team & Roles</span>
                            </a>
                        @endif
                        <a 
                            href="{{ route('admin.notifications.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.notifications.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg></span>
                            <span>Notifications</span>
                        </a>
                        <a 
                            href="{{ route('admin.audit-log.index') }}" 
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider border {{ request()->routeIs('admin.audit-log.*') ? 'bg-black text-white border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'text-gray-800 border-transparent hover:border-black hover:bg-gray-50' }} transition-all"
                        >
                            <span class="text-sm"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg></span>
                            <span>Audit Log</span>
                        </a>
                    </nav>
                </div>

                <!-- Quick Editor fallback -->
                <div class="pt-2 border-t border-gray-200">
                    <a 
                        href="{{ route('admin.quick-edit') }}" 
                        class="flex items-center gap-2 px-2 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400 hover:text-black transition-colors"
                    >
                        <span><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg></span>
                        <span>Single-Page Quick Editor</span>
                    </a>
                </div>
            </div>

            <!-- Footer note & Live Store Link -->
            <div class="p-4 bg-gray-50 border-t border-black space-y-2">
                <a 
                    href="{{ route('home') }}" 
                    target="_blank" 
                    class="w-full flex items-center justify-center gap-2 bg-black text-white hover:bg-gray-800 py-2.5 px-3 text-xs font-bold uppercase tracking-wider shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-transform active:translate-y-0.5"
                >
                    <span><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg></span>
                    <span>View Live Storefront ↗</span>
                </a>
                <div class="text-[10px] font-mono text-gray-500 text-center">
                    <span>ROLE: {{ strtoupper(auth()->user()->admin_role ?? 'ADMIN') }}</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 sm:p-8 lg:p-10 max-w-7xl w-full mx-auto">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="bg-black text-white p-4 mb-6 text-xs font-bold uppercase tracking-wider border-l-4 border-green-500 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-green-400 font-bold">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-600 text-white p-4 mb-6 text-xs font-bold uppercase tracking-wider border-l-4 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span><svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg></span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 text-red-900 border-2 border-red-600 p-4 mb-6 text-xs font-bold shadow-[4px_4px_0px_0px_rgba(220,38,38,0.3)]">
                    <p class="mb-1 uppercase tracking-wider">Please fix the following issues:</p>
                    <ul class="list-disc list-inside space-y-0.5 font-normal">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Background overlay for mobile sidebar -->
    <div 
        x-show="sidebarOpen" 
        @click="sidebarOpen = false"
        x-cloak
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-20 lg:hidden transition-opacity"
    ></div>

    <!-- The constantly moving pixel character -->
    <div class="pixel-pet"></div>

    @stack('scripts')
</body>
</html>
