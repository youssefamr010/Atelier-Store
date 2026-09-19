<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Atelier Administrative Console</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=5">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=5">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=5">
    
    <!-- Sharp Admin Typography: Space Grotesk + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN for Full Mobile & Desktop Responsive Support -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        black: '#000000',
                        white: '#FFFFFF',
                        'admin-bg': '#F2F1ED',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                        display: ['Space Grotesk', 'Inter', 'system-ui', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Chart.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('head-styles')
    @stack('head-scripts')

    <style>
        [x-cloak] { display: none !important; }
        html, body {
            max-width: 100vw;
            overflow-x: hidden;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #F2F1ED;
            margin: 0;
            padding: 0;
            letter-spacing: -0.01em;
            -webkit-text-size-adjust: 100%;
        }

        /* Responsive Mobile Table and Container Safeguards */
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ── SHARP HEADINGS using Space Grotesk ─────────────────────── */
        h1, h2, h3, h4, h5, h6,
        .font-black, .font-bold,
        [class*="tracking-tight"], [class*="tracking-wide"] {
            font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif;
        }
        /* Mono labels stay monospace */
        .font-mono, [class*="font-mono"] {
            font-family: ui-monospace, 'SFMono-Regular', 'Menlo', monospace;
        }

        /* ── ANIMATION: admin panel entrance ─────────────────────── */
        @keyframes admin-fade-up {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes admin-card-in {
            from { opacity: 0; transform: translateY(8px) scale(0.99); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .widget-animated {
            animation: admin-card-in 0.45s cubic-bezier(0.16,1,0.3,1) both;
        }
        .widget-animated:nth-child(1) { animation-delay: 0.05s; }
        .widget-animated:nth-child(2) { animation-delay: 0.10s; }
        .widget-animated:nth-child(3) { animation-delay: 0.15s; }
        .widget-animated:nth-child(4) { animation-delay: 0.20s; }
        .widget-animated:nth-child(5) { animation-delay: 0.25s; }
        .widget-animated:nth-child(6) { animation-delay: 0.30s; }
        
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
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.05);
            border-radius: 14px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.25); border-radius: 6px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.45); }
    </style>
</head>
<body class="min-h-screen bg-[#F4F4F0] text-black antialiased flex flex-col relative overflow-x-hidden" x-data="{ sidebarOpen: false }">

    <!-- Top Navigation Bar -->
    <header 
        x-data="{
            searchQuery: '',
            searchResults: { products: [], orders: [], customers: [] },
            searchOpen: false,
            searchLoading: false,
            mobileSearchOpen: false,
            searchTimeout: null,
            init() {
                window.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        this.$refs.globalSearchInput?.focus();
                        this.searchOpen = true;
                    }
                    if (e.key === 'Escape') {
                        this.searchOpen = false;
                        this.mobileSearchOpen = false;
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
        class="glass-panel sticky top-0 z-30 px-3 sm:px-6 py-2.5 sm:py-3 flex items-center justify-between gap-2 sm:gap-4 m-2 sm:m-3 border border-black/10 shadow-sm transition-all"
    >
        <!-- Left: Mobile Menu Trigger + Logo -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Mobile Menu Toggle Button -->
            <button 
                type="button"
                @click="sidebarOpen = !sidebarOpen" 
                class="lg:hidden p-2 rounded-xl border border-black/20 bg-white hover:bg-black hover:text-white text-black transition-all min-h-[42px] min-w-[42px] flex items-center justify-center cursor-pointer shadow-xs active:scale-95"
                aria-label="Toggle navigation menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div class="flex items-center gap-2.5">
                <img 
                    src="{{ asset('apple-touch-icon.png') }}?v=5" 
                    alt="ATELIER" 
                    class="w-9 h-9 rounded-full object-cover border border-black/15 shadow-sm shrink-0"
                    onerror="this.style.display='none'"
                >
                <div class="flex items-center gap-1.5">
                    <span class="bg-black text-white px-2 py-0.5 text-[9px] sm:text-[10px] font-mono font-bold tracking-widest uppercase rounded">
                        {{ strtoupper(auth()->user()->admin_role ?? 'ADMIN') }}
                    </span>
                    <a href="{{ route('admin.dashboard') }}" class="font-black text-sm sm:text-base tracking-tight uppercase hover:opacity-80 transition-opacity truncate max-w-[100px] sm:max-w-none">
                        {{ \App\Models\Setting::get('store_name', 'ATELIER') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Center: Global Quick Search (Desktop) -->
        <div class="hidden md:flex flex-1 max-w-md mx-2 relative" @click.away="searchOpen = false">
            <div class="relative flex items-center w-full">
                <span class="absolute left-3 text-gray-400 text-xs">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </span>
                <input 
                    x-ref="globalSearchInput"
                    type="text" 
                    x-model="searchQuery" 
                    @input="onSearchInput()"
                    @focus="if(searchQuery.length >= 2) searchOpen = true"
                    placeholder="Search products, orders, customers... (Ctrl+K)" 
                    class="w-full border border-gray-300 rounded-xl pl-9 pr-16 py-1.5 text-xs bg-white focus:bg-white focus:ring-2 focus:ring-black/10 focus:outline-none placeholder-gray-400 font-medium transition-all"
                >
                <kbd class="absolute right-2.5 hidden sm:inline-block bg-gray-100 text-gray-500 text-[10px] px-1.5 py-0.5 font-mono rounded font-semibold border">Ctrl+K</kbd>
            </div>

            <!-- Search Dropdown Results -->
            <div 
                x-show="searchOpen && ((searchResults.navigation && searchResults.navigation.length > 0) || searchResults.products.length > 0 || searchResults.orders.length > 0 || searchResults.customers.length > 0 || searchLoading)" 
                x-cloak 
                class="absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-96 overflow-y-auto z-50 divide-y divide-gray-100"
            >
                <div x-show="searchLoading" class="p-3 text-center text-xs text-gray-400 font-mono">
                    Searching catalog, integrations & records...
                </div>

                <!-- Navigation & Integrations Group -->
                <template x-if="searchResults.navigation && searchResults.navigation.length > 0">
                    <div class="p-2 bg-neutral-50/80">
                        <span class="block text-[9px] font-mono font-bold uppercase tracking-widest text-amber-600 px-2 py-1">Quick Navigation & Bots</span>
                        <template x-for="n in searchResults.navigation" :key="'nav-' + n.label">
                            <a :href="n.url" class="flex items-center justify-between p-2 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-black/10 shadow-xs mb-1">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="text-base shrink-0" x-text="n.icon"></span>
                                    <div class="truncate">
                                        <span class="block text-xs font-black text-black truncate" x-text="n.label"></span>
                                        <span class="block text-[10px] text-gray-500 font-medium truncate" x-text="n.sub"></span>
                                    </div>
                                </div>
                                <span class="text-[9px] font-mono font-bold uppercase px-1.5 py-0.5 border border-black/20 bg-black text-white shrink-0 ml-2 rounded" x-text="n.badge"></span>
                            </a>
                        </template>
                    </div>
                </template>

                <!-- Products Group -->
                <template x-if="searchResults.products && searchResults.products.length > 0">
                    <div class="p-2">
                        <span class="block text-[9px] font-mono font-bold uppercase tracking-widest text-gray-400 px-2 py-1">Products</span>
                        <template x-for="p in searchResults.products" :key="'p-' + p.id">
                            <a :href="p.url" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                <div class="flex items-center gap-2.5 truncate">
                                    <template x-if="p.img">
                                        <img :src="p.img" alt="" class="w-8 h-8 object-cover border border-black shrink-0 rounded">
                                    </template>
                                    <div class="truncate">
                                        <span class="block text-xs font-bold text-black truncate" x-text="p.label"></span>
                                        <span class="block text-[10px] text-gray-500 font-mono truncate" x-text="p.sub"></span>
                                    </div>
                                </div>
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 border shrink-0 ml-2 rounded" :class="p.badge === 'active' ? 'border-green-600 text-green-700 bg-green-50' : 'border-gray-400 text-gray-600'" x-text="p.badge"></span>
                            </a>
                        </template>
                    </div>
                </template>

                <!-- Orders Group -->
                <template x-if="searchResults.orders && searchResults.orders.length > 0">
                    <div class="p-2">
                        <span class="block text-[9px] font-mono font-bold uppercase tracking-widest text-gray-400 px-2 py-1">Orders</span>
                        <template x-for="o in searchResults.orders" :key="'o-' + o.id">
                            <a :href="o.url" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                <div>
                                    <span class="block text-xs font-bold text-black" x-text="o.label"></span>
                                    <span class="block text-[10px] text-gray-500 font-mono" x-text="o.sub"></span>
                                </div>
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 border border-black bg-gray-100 shrink-0 ml-2 rounded" x-text="o.badge"></span>
                            </a>
                        </template>
                    </div>
                </template>

                <!-- Customers Group -->
                <template x-if="searchResults.customers && searchResults.customers.length > 0">
                    <div class="p-2">
                        <span class="block text-[9px] font-mono font-bold uppercase tracking-widest text-gray-400 px-2 py-1">Customers</span>
                        <template x-for="c in searchResults.customers" :key="'c-' + c.id">
                            <a :href="c.url" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
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

        <!-- Right: Mobile Search Trigger + Notifications + Store + Logout -->
        <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
            <!-- Mobile Search Icon Button -->
            <button 
                type="button" 
                @click="mobileSearchOpen = !mobileSearchOpen"
                class="md:hidden p-2 rounded-xl border border-black/15 bg-white text-gray-700 hover:text-black min-h-[40px] min-w-[40px] flex items-center justify-center transition-colors"
                aria-label="Search"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </button>

            <!-- Notifications Shortcut Button with Unread Badge -->
            @php 
                $unreadNotifCount = \App\Models\AdminNotification::where('is_read', false)->count();
            @endphp
            <a 
                href="{{ route('admin.notifications.index') }}" 
                class="relative p-2 rounded-xl border border-black/15 bg-white hover:bg-black hover:text-white text-gray-800 transition-colors min-h-[40px] min-w-[40px] flex items-center justify-center shadow-xs"
                title="Notifications"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                @if($unreadNotifCount > 0)
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] font-black font-mono w-4 h-4 rounded-full flex items-center justify-center animate-pulse">
                        {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                    </span>
                @endif
            </a>

            <!-- View Storefront -->
            <a href="{{ route('home') }}" target="_blank" class="hidden xs:inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider text-black border border-black/20 bg-white hover:bg-black hover:text-white px-2.5 py-1.5 rounded-xl transition-all shadow-xs min-h-[40px]">
                <span>Store</span>
                <span>↗</span>
            </a>

            <!-- Admin Logout -->
            <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-black text-white hover:bg-neutral-800 rounded-xl px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider shadow-xs transition-colors min-h-[40px] cursor-pointer">
                    Logout
                </button>
            </form>
        </div>

        <!-- Mobile Search Fullscreen Overlay / Bar -->
        <div 
            x-show="mobileSearchOpen" 
            x-cloak 
            class="md:hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl p-3 shadow-2xl z-50 mx-2"
        >
            <div class="relative flex items-center">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input="onSearchInput()"
                    placeholder="Search products, orders, customers..." 
                    class="w-full border border-gray-300 rounded-lg pl-3 pr-8 py-2 text-xs bg-white focus:ring-2 focus:ring-black/10 focus:outline-none"
                    autofocus
                >
                <button @click="mobileSearchOpen = false" class="absolute right-2.5 text-gray-400 text-xs font-bold">✕</button>
            </div>
            
            <div x-show="searchOpen && ((searchResults.navigation && searchResults.navigation.length > 0) || searchResults.products.length > 0 || searchResults.orders.length > 0 || searchResults.customers.length > 0)" class="mt-2 max-h-64 overflow-y-auto divide-y divide-gray-100">
                <template x-for="n in (searchResults.navigation || [])" :key="'mn-' + n.label">
                    <a :href="n.url" class="flex items-center justify-between p-2 hover:bg-amber-50 text-xs bg-gray-50/80">
                        <div class="flex items-center gap-2 truncate">
                            <span x-text="n.icon"></span>
                            <span class="font-black text-black truncate" x-text="n.label"></span>
                        </div>
                        <span class="text-[9px] font-mono font-bold uppercase px-1 py-0.5 bg-black text-white rounded shrink-0 ml-1" x-text="n.badge"></span>
                    </a>
                </template>
                <template x-for="p in searchResults.products" :key="'mp-' + p.id">
                    <a :href="p.url" class="flex items-center justify-between p-2 hover:bg-gray-50 text-xs">
                        <span class="font-bold truncate" x-text="p.label"></span>
                        <span class="text-[10px] text-gray-500 font-mono" x-text="p.sub"></span>
                    </a>
                </template>
                <template x-for="o in searchResults.orders" :key="'mo-' + o.id">
                    <a :href="o.url" class="flex items-center justify-between p-2 hover:bg-gray-50 text-xs">
                        <span class="font-bold" x-text="o.label"></span>
                        <span class="text-[10px] text-gray-500 font-mono" x-text="o.sub"></span>
                    </a>
                </template>
            </div>
        </div>
    </header>

    <!-- App Body: Sidebar + Main Content -->
    <div 
        class="flex-1 flex max-w-[1600px] w-full mx-auto relative z-10"
        x-init="$watch('sidebarOpen', val => document.body.style.overflow = val ? 'hidden' : '')"
    >
        
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            x-cloak
            class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 lg:hidden"
        ></div>

        <!-- Sidebar Navigation Drawer (Desktop Sticky + Mobile Off-Canvas Drawer) -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:sticky top-0 lg:top-[85px] left-0 h-full lg:h-[calc(100vh-105px)] w-72 lg:w-64 bg-white lg:glass-panel z-50 lg:z-20 flex flex-col justify-between transition-transform duration-300 ease-out overflow-y-auto overscroll-y-contain shadow-2xl lg:shadow-sm border-r lg:border-r-0 lg:ml-3"
            style="-webkit-overflow-scrolling: touch;"
        >
            <div class="p-4 space-y-5 pb-24 lg:pb-4">
                <!-- Mobile Drawer Header with Close Button -->
                <div class="flex lg:hidden items-center justify-between pb-3 border-b border-black/10">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-black text-sm uppercase tracking-wider text-black">Admin Panel</span>
                    </div>
                    <button 
                        type="button" 
                        @click="sidebarOpen = false"
                        class="w-9 h-9 rounded-xl border border-black/20 flex items-center justify-center text-black hover:bg-black hover:text-white transition-colors"
                        aria-label="Close menu"
                    >
                        ✕
                    </button>
                </div>

                <!-- Group 1: Core Commerce -->
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400 mb-2 px-1">Overview</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.dashboard') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>📊</span>
                            <span>Dashboard</span>
                        </a>
                        <a 
                            href="{{ route('admin.analytics.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.analytics.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>📈</span>
                            <span>Analytics</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 2: Catalog & Stock -->
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400 mb-2 px-1">Catalog</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.products.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.products.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>🛍️</span>
                                <span>Products</span>
                            </div>
                            <span class="text-[10px] font-mono opacity-70">{{ \App\Models\Product::count() }}</span>
                        </a>
                        <a 
                            href="{{ route('admin.inventory.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.inventory.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>📦</span>
                                <span>Inventory</span>
                            </div>
                        </a>
                        <a 
                            href="{{ route('admin.collections.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.collections.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>🏷️</span>
                                <span>Collections</span>
                            </div>
                            <span class="text-[10px] font-mono opacity-70">{{ \App\Models\Collection::count() }}</span>
                        </a>
                        <a 
                            href="{{ route('admin.coupons.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.coupons.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>🎟️</span>
                            <span>Coupons</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 3: Sales & Operations -->
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400 mb-2 px-1">Operations</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.orders.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.orders.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>🚚</span>
                                <span>Orders</span>
                            </div>
                            <span class="text-[10px] font-mono opacity-70">{{ \App\Models\Order::count() }}</span>
                        </a>
                        <a 
                            href="{{ route('admin.customers.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.customers.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>👥</span>
                                <span>Customers</span>
                            </div>
                        </a>
                        <a 
                            href="{{ route('admin.abandoned-carts.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.abandoned-carts.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>🛒</span>
                                <span>Abandoned Carts</span>
                            </div>
                        </a>
                        <a 
                            href="{{ route('admin.reviews.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.reviews.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>⭐</span>
                                <span>Reviews</span>
                            </div>
                        </a>
                        <a 
                            href="{{ route('admin.surveys.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.surveys.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>📝</span>
                                <span>Surveys & Polls</span>
                            </div>
                        </a>
                    </nav>
                </div>

                <!-- Group 4: Automations & Bots (Highlighted) -->
                @php 
                    $hasTgBot = !empty(\App\Models\Setting::get('telegram_bot_token', config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'))));
                    $isWaEnabled = \App\Models\Setting::get('whatsapp_enabled', '0') === '1';
                @endphp
                <div class="p-2.5 rounded-xl bg-gradient-to-br from-neutral-900 to-black text-white border border-neutral-700 shadow-md">
                    <span class="block text-[10px] font-mono font-bold uppercase tracking-widest text-amber-400 mb-2 px-1 flex items-center justify-between">
                        <span>🤖 Bots & Integrations</span>
                        <span class="text-[8px] px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300 font-mono">LIVE</span>
                    </span>
                    <nav class="space-y-1">
                        <!-- Telegram Bot -->
                        <a 
                            href="{{ route('admin.telegram.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-2.5 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.telegram.*') ? 'bg-blue-600 text-white border-blue-400' : 'text-neutral-200 border-transparent hover:bg-white/10' }} transition-colors"
                        >
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm">✈️</span>
                                <span class="text-[11px]">Telegram Bot</span>
                            </div>
                            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded {{ $hasTgBot ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-500/40' : 'bg-neutral-800 text-neutral-400' }}">
                                {{ $hasTgBot ? 'Active' : 'Setup' }}
                            </span>
                        </a>

                        <!-- WhatsApp Gateway -->
                        <a 
                            href="{{ route('admin.whatsapp.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-2.5 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.whatsapp.*') ? 'bg-emerald-600 text-white border-emerald-400' : 'text-neutral-200 border-transparent hover:bg-white/10' }} transition-colors"
                        >
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm">💬</span>
                                <span class="text-[11px]">WhatsApp Alerts</span>
                            </div>
                            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded {{ $isWaEnabled ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-500/40' : 'bg-neutral-800 text-neutral-400' }}">
                                {{ $isWaEnabled ? 'Active' : 'Setup' }}
                            </span>
                        </a>
                    </nav>
                </div>

                <!-- Group 5: Settings & Configuration -->
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400 mb-2 px-1">Settings</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.content.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.content.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>⚙️</span>
                            <span>Content & Banners</span>
                        </a>
                        <a 
                            href="{{ route('admin.shipping.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.shipping.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>📍</span>
                            <span>Shipping & Govs</span>
                        </a>
                        <a 
                            href="{{ route('admin.notifications.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.notifications.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>🔔</span>
                                <span>Notifications</span>
                            </div>
                            @if($unreadNotifCount > 0)
                                <span class="bg-red-600 text-white text-[9px] font-mono font-black px-1.5 py-0.5 rounded-full">
                                    {{ $unreadNotifCount }}
                                </span>
                            @endif
                        </a>
                        <a 
                            href="{{ route('admin.audit-log.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.audit-log.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>📋</span>
                            <span>Audit Log</span>
                        </a>
                        @if((auth()->user()->admin_role ?? '') === 'super_admin')
                            <a 
                                href="{{ route('admin.team.index') }}" 
                                @click="sidebarOpen = false"
                                class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.team.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                            >
                                <span>👥</span>
                                <span>Team & Access</span>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-4 bg-gray-50 border-t border-black/10">
                <a 
                    href="{{ route('home') }}" 
                    target="_blank" 
                    class="w-full flex items-center justify-center gap-2 bg-black text-white hover:bg-neutral-800 py-2.5 px-3 rounded-xl text-xs font-bold uppercase tracking-wider shadow-xs transition-colors"
                >
                    <span>View Storefront ↗</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area (Clean mobile responsive wrapping & horizontal safety) -->
        <main class="flex-1 p-3 sm:p-6 lg:p-8 max-w-full min-w-0 overflow-x-hidden">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="bg-black text-white p-3.5 mb-5 text-xs font-bold uppercase tracking-wider border-l-4 border-green-500 rounded-lg shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-green-400 font-bold">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-600 text-white p-3.5 mb-5 text-xs font-bold uppercase tracking-wider border-l-4 border-black rounded-lg shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="bg-red-50 text-red-900 border border-red-500 p-3.5 mb-5 text-xs font-bold rounded-lg">
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

    @stack('scripts')
</body>
</html>
