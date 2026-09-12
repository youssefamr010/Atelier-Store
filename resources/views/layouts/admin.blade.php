<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Atelier Administrative Console</title>
    
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
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #F2F1ED; margin: 0; padding: 0; letter-spacing: -0.01em; }

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
        class="glass-panel sticky top-0 z-30 px-3 sm:px-6 py-2.5 sm:py-3 flex items-center justify-between gap-3 m-2 sm:m-3 border border-black/10 shadow-sm"
    >
        <!-- Left: Mobile Menu Trigger + Logo -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Mobile Menu Toggle Button (Prominent on Phones) -->
            <button 
                type="button"
                @click="sidebarOpen = !sidebarOpen" 
                class="lg:hidden p-2 rounded-lg border border-black/20 bg-white hover:bg-black hover:text-white text-black transition-colors min-h-[40px] min-w-[40px] flex items-center justify-center cursor-pointer shadow-xs active:scale-95"
                aria-label="Toggle navigation menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div class="flex items-center gap-2">
                <span class="bg-black text-white px-2 py-0.5 text-[9px] sm:text-[10px] font-mono font-bold tracking-widest uppercase rounded">
                    {{ strtoupper(auth()->user()->admin_role ?? 'ADMIN') }}
                </span>
                <a href="{{ route('admin.dashboard') }}" class="font-black text-sm sm:text-base tracking-tight uppercase hover:opacity-80 transition-opacity truncate max-w-[140px] sm:max-w-none">
                    {{ \App\Models\Setting::get('store_name', 'ATELIER') }} Studio
                </a>
            </div>
        </div>

        <!-- Center: Global Quick Search -->
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
                x-show="searchOpen && (searchResults.products.length > 0 || searchResults.orders.length > 0 || searchResults.customers.length > 0 || searchLoading)" 
                x-cloak 
                class="absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-96 overflow-y-auto z-50 divide-y divide-gray-100"
            >
                <div x-show="searchLoading" class="p-3 text-center text-xs text-gray-400 font-mono">
                    Searching catalog & records...
                </div>

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

        <!-- Right: Storefront Link & Logout -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- View Storefront -->
            <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider text-black border border-black/20 bg-white hover:bg-black hover:text-white px-2.5 py-1.5 rounded-lg transition-all shadow-xs min-h-[36px]">
                <span>Store</span>
                <span>↗</span>
            </a>

            <!-- Admin Logout -->
            <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-black text-white hover:bg-neutral-800 rounded-lg px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider shadow-xs transition-colors min-h-[36px] cursor-pointer">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <!-- App Body: Sidebar + Main Content -->
    <div class="flex-1 flex max-w-[1600px] w-full mx-auto relative z-10">
        
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
            class="fixed lg:sticky top-0 lg:top-[85px] left-0 h-full lg:h-[calc(100vh-105px)] w-72 lg:w-64 bg-white lg:glass-panel z-50 lg:z-20 flex flex-col justify-between transition-transform duration-300 ease-out overflow-y-auto shadow-2xl lg:shadow-sm border-r lg:border-r-0 lg:ml-3"
        >
            <div class="p-4 space-y-5">
                <!-- Mobile Drawer Header with Close Button -->
                <div class="flex lg:hidden items-center justify-between pb-3 border-b border-black/10">
                    <span class="font-black text-sm uppercase tracking-wider text-black">Admin Menu</span>
                    <button 
                        type="button" 
                        @click="sidebarOpen = false"
                        class="w-8 h-8 rounded-lg border border-black/20 flex items-center justify-center text-black hover:bg-black hover:text-white transition-colors"
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
                            href="{{ route('admin.reviews.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.reviews.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span>⭐</span>
                                <span>Reviews</span>
                            </div>
                        </a>
                    </nav>
                </div>

                <!-- Group 4: Settings & Integrations -->
                <div>
                    <span class="block text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400 mb-2 px-1">Settings</span>
                    <nav class="space-y-1">
                        <a 
                            href="{{ route('admin.content.index') }}" 
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.content.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>⚙️</span>
                            <span>Content & Banner</span>
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
                            class="flex items-center gap-3 px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border {{ request()->routeIs('admin.notifications.*') ? 'bg-black text-white border-black' : 'text-gray-800 border-transparent hover:bg-gray-100' }} transition-colors"
                        >
                            <span>🔔</span>
                            <span>Notifications</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-4 bg-gray-50 border-t border-black/10">
                <a 
                    href="{{ route('home') }}" 
                    target="_blank" 
                    class="w-full flex items-center justify-center gap-2 bg-black text-white hover:bg-neutral-800 py-2.5 px-3 rounded-lg text-xs font-bold uppercase tracking-wider shadow-xs transition-colors"
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

            @if($errors->any())
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
