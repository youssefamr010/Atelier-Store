<!-- Announcement Top Bar (Synced with Admin Live Settings) -->
@php
    $navCollections = \App\Models\Collection::where('status', 'active')->orderBy('sort_order')->take(6)->get();
    $currentSlug = request()->route('slug') ?? '';
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $cartCount = \App\Http\Controllers\CartController::cartCount();
@endphp

@if(Auth::check() && Auth::user()->isAdmin())
    <!-- Admin Quick-Jump Bar — visible on ALL screen sizes -->
    <div class="flex bg-black text-amber-400 border-b border-amber-400/30 text-[10px] font-mono h-8 px-4 sm:px-8 items-center justify-between z-50 relative">
        <div class="flex items-center gap-2 truncate">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse shrink-0"></span>
            <span class="text-white/90 text-[11px] font-medium truncate">Admin: {{ Auth::user()->name }}</span>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center bg-amber-400 text-black px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider hover:bg-white transition-all shadow-sm">
                Admin Suite →
            </a>
            <a href="{{ route('admin.products.create') }}" class="text-white/80 hover:text-amber-400 text-[10px] uppercase font-mono px-1 py-0.5 transition-colors">
                + Product
            </a>
            <a href="{{ route('admin.content.index') }}" class="text-white/80 hover:text-amber-400 text-[10px] uppercase font-mono px-1 py-0.5 transition-colors">
                <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg> Edit Banner
            </a>
        </div>
    </div>
@endif

{{-- Dynamic Animated Ticker Bar --}}
<div class="hidden md:block bg-black text-white border-b border-white/10 overflow-hidden" style="height: 30px;">
    <style>
        @keyframes atl-ticker {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .atl-ticker-track {
            display: flex;
            width: max-content;
            animation: atl-ticker 52s linear infinite;
            will-change: transform;
        }
        .atl-ticker-track:hover { animation-play-state: paused; }
        .atl-ticker-item {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0 2rem;
            font-family: 'Cinzel', Georgia, serif;
            font-weight: 700;
            font-size: 8.5px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            line-height: 30px;
            color: rgba(255,255,255,0.78);
        }
        .atl-ticker-sep {
            display: inline-block;
            width: 4px;
            height: 4px;
            background: rgba(255,255,255,0.35);
            margin: 0 1.5rem;
            transform: rotate(45deg);
            vertical-align: middle;
            flex-shrink: 0;
        }
    </style>
    <div class="atl-ticker-track" id="atl-ticker">
        @php
        $tickerItems = [
            'SPECIAL SERVICE & PREMIUM PRODUCTS CONDUCTED ACROSS EGYPT',
            'FULL-GRAIN ITALIAN TUSCAN LEATHER CRAFTSMANSHIP',
            'DELIVERY FROM 3 TO 5 BUSINESS DAYS — DOOR-TO-DOOR EXPRESS',
            'CASH ON DELIVERY · PAYMOB ENCRYPTED GATEWAY · VISA · VALU',
            '100% AUTHENTIC LUXURY ACCESSORIES · ORIGIN: CAIRO, EGYPT',
            'SECURE PACKAGING — EVERY ORDER HAND-WRAPPED IN ATELIER BOX',
            'TRACK YOUR ORDER IN REAL-TIME — DIRECT SMS & WHATSAPP UPDATES',
        ];
        @endphp
        @foreach($tickerItems as $t)
        <span class="atl-ticker-item">{{ $t }}<span class="atl-ticker-sep"></span></span>
        @endforeach
        @foreach($tickerItems as $t)
        <span class="atl-ticker-item">{{ $t }}<span class="atl-ticker-sep"></span></span>
        @endforeach
    </div>
</div>

<!-- Main Sticky Navigation Header (Signature Atelier Brutalist Luxury Design) -->
<header 
    x-data="headerSearchComponent()"
    class="site-header sticky top-0 z-40 bg-[#F5F5F0]/95 backdrop-blur-xl border-b-2 border-black transition-all duration-300"
>
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-3">
            
            <!-- 1. LEFT: Menu Trigger Button + Atelier Brand Wordmark -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Signature Menu Toggle Button (Same Brutalist Luxury Theme) -->
                <button 
                    type="button" 
                    @click="mobileMenuOpen = true"
                    class="h-10 px-3 border-2 border-black bg-white hover:bg-black hover:text-white text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] active:scale-95 transition-all flex items-center justify-center gap-2 font-editorial font-bold text-[11px] uppercase tracking-wider cursor-pointer group"
                    aria-label="{{ $isAr ? 'القائمة الجانبية' : 'Menu' }}"
                    title="{{ $isAr ? 'فتح القائمة' : 'Toggle Menu' }}"
                >
                    <svg class="w-4 h-4 stroke-[2.2] group-hover:rotate-90 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="hidden sm:inline">{{ $isAr ? 'القائمة' : 'MENU' }}</span>
                </button>

                <!-- Brand Logo + Wordmark -->
                <a href="{{ route('home') }}" class="group flex items-center gap-2 focus:outline-none pl-1" title="{{ $settings['store_name'] ?? 'ATELIER' }}">
                    <img 
                        src="{{ asset('apple-touch-icon.png') }}?v=5" 
                        alt="ATELIER Logo" 
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover transition-transform group-hover:scale-110 shadow-md border border-black/10"
                        onerror="this.style.display='none'"
                    >
                    <div class="flex flex-col items-start">
                        <span class="font-editorial font-black text-xl sm:text-2xl lg:text-3xl text-black uppercase leading-none tracking-tight transition-transform group-hover:scale-[1.02]">
                            {{ $settings['store_name'] ?? ($settings['storeName'] ?? 'ATELIER') }}
                        </span>
                        <span class="font-editorial font-bold text-[7px] sm:text-[8px] tracking-[0.32em] text-black/60 uppercase mt-0.5 whitespace-nowrap">
                            STUDIO EGYPT · 2026
                        </span>
                    </div>
                </a>
            </div>

            <!-- 2. CENTER: Unified Integrated Search Bar (Desktop & Tablet) -->
            <div class="hidden md:flex flex-1 max-w-sm lg:max-w-md mx-2 relative">
                <form action="{{ route('collections.show', ['slug' => 'all']) }}" method="GET" class="w-full relative" @submit="submitSearch">
                    <div class="h-10 flex items-center bg-white border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] w-full overflow-hidden focus-within:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all">
                        <div class="pl-3 pr-1 text-black/50 flex items-center shrink-0">
                            <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="q" 
                            x-model="searchQuery" 
                            @input.debounce.250ms="performLiveSearch()"
                            @focus="searchFocused = true"
                            @click.away="searchFocused = false"
                            placeholder="{{ $isAr ? 'ابحث عن قطعة أو منتج...' : 'Search pieces & products...' }}" 
                            class="w-full h-full py-1 px-2 text-xs font-sans text-black placeholder:text-black/40 focus:outline-none bg-transparent"
                            autocomplete="off"
                        >
                        <button type="submit" class="h-full px-3.5 bg-black hover:bg-neutral-800 text-white font-editorial font-bold text-[10px] uppercase tracking-wider shrink-0 transition-colors border-l-2 border-black">
                            {{ $isAr ? 'بحث' : 'SEARCH' }}
                        </button>
                    </div>

                    <!-- Live Dropdown Results -->
                    <div 
                        x-show="searchFocused && liveResults.length > 0" 
                        x-transition 
                        x-cloak
                        class="absolute left-0 right-0 top-full mt-1.5 bg-white border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] z-50 max-h-80 overflow-y-auto divide-y divide-black/10"
                    >
                        <template x-for="item in liveResults" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-[#F5F5F0] transition-colors">
                                <img :src="item.image" :alt="item.title" class="w-10 h-10 object-cover border border-black/15 shrink-0 bg-[#F5F5F0]">
                                <div class="flex-1 min-w-0">
                                    <p class="font-editorial font-bold text-xs uppercase tracking-normal text-black truncate" x-text="item.title"></p>
                                    <p class="font-sans font-semibold text-[11px] text-black/70 mt-0.5" x-text="item.price"></p>
                                </div>
                                <span class="text-xs font-editorial text-black/40">View →</span>
                            </a>
                        </template>
                        <div class="p-2 bg-[#FAFAFA] text-center border-t border-black/10">
                            <a :href="'/collections/all?q=' + encodeURIComponent(searchQuery)" class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black hover:underline">
                                {{ $isAr ? 'عرض كافة النتائج ←' : 'View all matching results →' }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 3. RIGHT: Perfectly Symmetrical Buttons (Exact Same Height, Border, Font, Spacing) -->
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                
                <!-- Desktop: All Products / Catalog Button -->
                <a 
                    href="{{ route('collections.show', ['slug' => 'all']) }}" 
                    class="hidden xl:flex h-10 px-3.5 border-2 border-black {{ request()->is('collections/all') ? 'bg-black text-white' : 'bg-white hover:bg-black hover:text-white text-black' }} shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] active:scale-95 transition-all items-center justify-center gap-1.5 font-editorial font-bold text-[11px] uppercase tracking-wider"
                    title="{{ $isAr ? 'كل المنتجات' : 'All Products' }}"
                >
                    <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>{{ $isAr ? 'المنتجات' : 'CATALOG' }}</span>
                </a>

                <!-- Desktop: Track Order Button -->
                <a 
                    href="{{ route('track.order') }}" 
                    class="hidden lg:flex h-10 px-3.5 border-2 border-black {{ request()->routeIs('track.order') ? 'bg-black text-white' : 'bg-white hover:bg-black hover:text-white text-black' }} shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] active:scale-95 transition-all items-center justify-center gap-1.5 font-editorial font-bold text-[11px] uppercase tracking-wider"
                    title="{{ $isAr ? 'تتبع الطلب' : 'Track Order' }}"
                >
                    <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <span>{{ $isAr ? 'تتبع الطلب' : 'TRACK' }}</span>
                </a>

                <!-- Account Button (Desktop & Tablet) -->
                <a 
                    href="{{ route('account') }}" 
                    class="hidden sm:flex h-10 px-3.5 border-2 border-black {{ request()->routeIs('account*') ? 'bg-black text-white' : 'bg-white hover:bg-black hover:text-white text-black' }} shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] active:scale-95 transition-all items-center justify-center gap-1.5 font-editorial font-bold text-[11px] uppercase tracking-wider"
                    title="{{ $isAr ? 'حسابي' : 'Client Account' }}"
                >
                    <svg class="w-3.5 h-3.5 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ $isAr ? 'حسابي' : 'ACCOUNT' }}</span>
                </a>

                <!-- Mobile: Quick Search Icon Trigger -->
                <button 
                    type="button" 
                    onclick="window.dispatchEvent(new CustomEvent('open-smart-search'))"
                    class="md:hidden h-10 w-10 border-2 border-black bg-white hover:bg-black hover:text-white text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] active:scale-95 transition-all flex items-center justify-center cursor-pointer shrink-0"
                    aria-label="{{ $isAr ? 'بحث' : 'Search' }}"
                >
                    <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                </button>

                <!-- Luxury Shopping Bag Button (Exact Matching Height & Signature Aesthetic) -->
                <a 
                    href="{{ route('cart.index') }}" 
                    class="h-10 px-3.5 sm:px-4 border-2 border-black bg-black text-white hover:bg-neutral-800 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] active:scale-95 transition-all flex items-center justify-center gap-2 font-editorial font-bold text-[11px] uppercase tracking-wider cursor-pointer shrink-0"
                    title="{{ $isAr ? 'السلة وإتمام الطلب' : 'Shopping Bag & Checkout' }}"
                >
                    <svg class="w-3.5 h-3.5 text-white stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="tracking-widest hidden xs:inline sm:inline">{{ $isAr ? 'السلة' : 'BAG' }}</span>
                    <span class="bg-white text-black text-[10px] font-mono font-black px-1.5 py-0.5 border border-black leading-none">
                        {{ $cartCount }}
                    </span>
                </a>

            </div>

        </div>
    </div>

    <!-- Luxury Off-Canvas Sidebar Drawer (Fluid Luxury E-Commerce Master Drawer) -->
    <div 
        x-show="mobileMenuOpen"
        x-cloak
        class="fixed inset-0 z-[9999] flex"
        role="dialog" 
        aria-modal="true"
    >
        <!-- Dark Blur Backdrop -->
        <div 
            x-show="mobileMenuOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileMenuOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-md"
        ></div>

        <!-- Sliding Menu Panel -->
        <div 
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-350 transform"
            x-transition:enter-start="{{ $isAr ? 'translate-x-full' : '-translate-x-full' }}"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-250 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="{{ $isAr ? 'translate-x-full' : '-translate-x-full' }}"
            class="relative w-[90%] max-w-sm sm:max-w-md bg-[#FAFAF7] text-black shadow-2xl flex flex-col z-10 overflow-hidden border-{{ $isAr ? 'l' : 'r' }} border-black/10"
            dir="{{ $isAr ? 'rtl' : 'ltr' }}"
            style="height: 100dvh; max-height: 100dvh;"
        >
            <!-- Drawer Top Bar (Sleek Glassmorphic Header) -->
            <div class="shrink-0 px-5 py-4 border-b border-black/10 flex items-center justify-between bg-white/90 backdrop-blur-md">
                <a href="{{ route('home') }}" class="flex items-center gap-3 min-w-0 group" @click="mobileMenuOpen = false">
                    <img 
                        src="{{ asset('apple-touch-icon.png') }}?v=5" 
                        alt="ATELIER" 
                        class="w-10 h-10 rounded-full object-cover shrink-0 shadow-md ring-1 ring-black/10 transition-transform group-hover:scale-105"
                        onerror="this.outerHTML='<div class=&quot;w-10 h-10 rounded-xl bg-black text-white flex items-center justify-center font-black text-base shrink-0 shadow-md&quot;>A</div>'"
                    >
                    <div class="min-w-0">
                        <h3 class="font-display font-bold text-sm tracking-wider uppercase text-black leading-tight truncate">
                            {{ $settings['store_name'] ?? ($settings['storeName'] ?? 'ATELIER') }}
                        </h3>
                        <p class="font-mono text-[8.5px] tracking-[0.22em] text-black/50 uppercase mt-0.5">
                            {{ $isAr ? 'استوديو المنتجات الفاخرة' : 'LUXURY BESPOKE STUDIO' }}
                        </p>
                    </div>
                </a>
                <button 
                    type="button" 
                    @click="mobileMenuOpen = false"
                    class="w-9 h-9 rounded-full border border-black/10 bg-[#F5F5F0] hover:bg-black hover:text-white flex items-center justify-center text-black/80 transition-all shrink-0 active:scale-90 cursor-pointer shadow-sm"
                    aria-label="{{ $isAr ? 'إغلاق القائمة' : 'Close menu' }}"
                >
                    <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- User VIP Privilege Card / Guest Welcome --}}
            <div class="shrink-0 px-5 py-3.5 bg-white border-b border-black/8">
                @auth
                    @php
                        $uPoints = Auth::user()->loyaltyPointsBalance();
                    @endphp
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gradient-to-r from-neutral-900 to-neutral-800 text-white shadow-md">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-white/15 text-white flex items-center justify-center font-bold text-xs shrink-0 ring-1 ring-white/20">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold truncate leading-snug">{{ Auth::user()->name }}</p>
                                <p class="text-[9px] text-amber-300 font-mono flex items-center gap-1 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>
                                    <span>{{ $isAr ? 'عضو النخبة' : 'VIP Member' }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[9px] text-white/60 block">{{ $isAr ? 'نقاط المكافآت' : 'Points' }}</span>
                            <span class="text-xs font-mono font-black text-amber-300">{{ number_format($uPoints) }} <span class="text-[9px] font-normal text-white/70">PTS</span></span>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-[#F4F3EE] border border-black/8">
                        <div>
                            <p class="text-xs font-bold text-black">{{ $isAr ? 'مرحباً بك في ATELIER' : 'Welcome to ATELIER' }}</p>
                            <p class="text-[9.5px] text-black/60">{{ $isAr ? 'سجل للحصول على نقاط مكافآت فورية' : 'Sign in to unlock privilege rewards' }}</p>
                        </div>
                        <a href="{{ route('login') }}" 
                           @click="mobileMenuOpen = false"
                           class="px-3 py-1.5 rounded-lg bg-black text-white text-[10px] font-bold uppercase tracking-wider hover:bg-neutral-800 transition-all shrink-0 shadow-sm">
                            {{ $isAr ? 'دخول' : 'Sign In' }}
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Drawer Body (Fluid Smooth Scrolling) -->
            <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-5 py-5 space-y-6">
                
                <!-- Quick Search Input inside Drawer -->
                <form action="{{ route('collections.show', ['slug' => 'all']) }}" method="GET" class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="{{ $isAr ? 'ابحث عن قطعة أو منتج...' : 'Search pieces & products...' }}"
                        class="w-full bg-white border border-black/15 rounded-xl py-2.5 {{ $isAr ? 'pr-9 pl-3' : 'pl-9 pr-3' }} text-xs text-black placeholder:text-black/40 focus:outline-none focus:border-black focus:ring-1 focus:ring-black/20 shadow-sm font-sans transition-all"
                    >
                    <button type="submit" class="absolute {{ $isAr ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-black/50 hover:text-black transition-colors">
                        <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                    </button>
                </form>

                <!-- Primary Category Navigation Grid -->
                <div>
                    <div class="flex items-center justify-between mb-3 px-1">
                        <p class="text-[10px] font-mono font-bold uppercase tracking-[0.18em] text-black/50 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-black inline-block"></span>
                            <span>{{ $isAr ? 'الأقسام والتشكيلات' : 'COLLECTIONS & CATALOG' }}</span>
                        </p>
                        <a href="{{ url('/collections') }}" @click="mobileMenuOpen = false" class="text-[10px] font-bold text-black/60 hover:text-black hover:underline">
                            {{ $isAr ? 'عرض الكل' : 'View all' }}
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <!-- Home Card -->
                        @php $isHomeActive = request()->routeIs('home'); @endphp
                        <a href="{{ route('home') }}" 
                           @click="mobileMenuOpen = false"
                           class="p-3.5 rounded-xl border {{ $isHomeActive ? 'bg-black text-white border-black shadow-md' : 'bg-white text-black border-black/8 hover:border-black/20 hover:shadow-sm' }} transition-all flex flex-col justify-between group active:scale-95">
                            <div class="w-7 h-7 rounded-lg {{ $isHomeActive ? 'bg-white/20 text-white' : 'bg-[#F5F5F0] text-black' }} flex items-center justify-center mb-3">
                                <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider {{ $isHomeActive ? 'text-white' : 'text-black' }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</span>
                                <span class="block text-[9px] {{ $isHomeActive ? 'text-white/70' : 'text-black/50' }} mt-0.5">{{ $isAr ? 'واجهة المتجر' : 'Storefront' }}</span>
                            </div>
                        </a>

                        <!-- All Products Card -->
                        @php $isAllActive = request()->is('collections/all'); @endphp
                        <a href="{{ route('collections.show', ['slug' => 'all']) }}" 
                           @click="mobileMenuOpen = false"
                           class="p-3.5 rounded-xl border {{ $isAllActive ? 'bg-black text-white border-black shadow-md' : 'bg-white text-black border-black/8 hover:border-black/20 hover:shadow-sm' }} transition-all flex flex-col justify-between group active:scale-95">
                            <div class="w-7 h-7 rounded-lg {{ $isAllActive ? 'bg-white/20 text-white' : 'bg-[#F5F5F0] text-black' }} flex items-center justify-center mb-3">
                                <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider {{ $isAllActive ? 'text-white' : 'text-black' }}">{{ $isAr ? 'كل المنتجات' : 'All Catalog' }}</span>
                                <span class="block text-[9px] {{ $isAllActive ? 'text-white/70' : 'text-black/50' }} mt-0.5">{{ $isAr ? 'جميع القطع' : 'Complete archive' }}</span>
                            </div>
                        </a>

                        <!-- Dynamic Curated Collections Cards -->
                        @foreach($navCollections as $mCol)
                            @php $isMActive = request()->is('collections/' . $mCol->slug); @endphp
                            <a href="{{ route('collections.show', ['slug' => $mCol->slug]) }}" 
                               @click="mobileMenuOpen = false"
                               class="p-3.5 rounded-xl border {{ $isMActive ? 'bg-black text-white border-black shadow-md' : 'bg-white text-black border-black/8 hover:border-black/20 hover:shadow-sm' }} transition-all flex flex-col justify-between group active:scale-95">
                                <div class="w-7 h-7 rounded-lg {{ $isMActive ? 'bg-white/20 text-white' : 'bg-[#F5F5F0] text-black' }} flex items-center justify-center mb-3">
                                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider truncate {{ $isMActive ? 'text-white' : 'text-black' }}">{{ $mCol->title }}</span>
                                    <span class="block text-[9px] {{ $isMActive ? 'text-white/70' : 'text-black/50' }} mt-0.5">{{ $isAr ? 'تشكيلة مختارة' : 'Curated line' }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Fast Actions & Account Services -->
                <div>
                    <p class="text-[10px] font-mono font-bold uppercase tracking-[0.18em] text-black/50 mb-3 px-1 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-black inline-block"></span>
                        <span>{{ $isAr ? 'خدمات وسرعة الوصول' : 'QUICK SERVICES' }}</span>
                    </p>
                    <div class="space-y-2">
                        <!-- Track Order -->
                        <a href="{{ route('track.order') }}" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center justify-between p-3 bg-white rounded-xl border border-black/8 hover:border-black/20 hover:shadow-sm transition-all group active:scale-98">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#F5F5F0] flex items-center justify-center text-black shrink-0">
                                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-black">{{ $isAr ? 'تتبع الشحنة والطلب' : 'Track Your Shipment' }}</p>
                                    <p class="text-[9.5px] text-black/50 font-sans">{{ $isAr ? 'معرفة حالة الشحنة المباشرة' : 'Real-time order tracker' }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-black/40 group-hover:text-black {{ $isAr ? 'rotate-180' : '' }} transition-colors">→</span>
                        </a>

                        <!-- Client Account -->
                        <a href="{{ route('account') }}" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center justify-between p-3 bg-white rounded-xl border border-black/8 hover:border-black/20 hover:shadow-sm transition-all group active:scale-98">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#F5F5F0] flex items-center justify-center text-black shrink-0">
                                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-black">{{ $isAr ? 'الحساب والطلبات' : 'Client Profile' }}</p>
                                    <p class="text-[9.5px] text-black/50 font-sans">{{ $isAr ? 'الطلبات والعناوين المحفوظة' : 'Orders & saved details' }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-black/40 group-hover:text-black {{ $isAr ? 'rotate-180' : '' }} transition-colors">→</span>
                        </a>

                        <!-- My Wishlist / Favorites -->
                        <a href="{{ route('account') }}#wishlist" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center justify-between p-3 bg-white rounded-xl border border-black/8 hover:border-black/20 hover:shadow-sm transition-all group active:scale-98">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 fill-current stroke-current" viewBox="0 0 24 24">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-black">{{ $isAr ? 'المفضلات' : 'My Wishlist' }}</p>
                                    <p class="text-[9.5px] text-black/50 font-sans">{{ $isAr ? 'القطع المحفوظة في قائمتك' : 'Saved favorite pieces' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-mono font-bold bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full" x-show="$store.wishlist && $store.wishlist.items && $store.wishlist.items.length > 0" x-text="$store.wishlist.items.length"></span>
                                <span class="text-xs font-bold text-black/40 group-hover:text-black {{ $isAr ? 'rotate-180' : '' }} transition-colors">→</span>
                            </div>
                        </a>

                        <!-- VIP WhatsApp Concierge -->
                        @php
                            $drawerWaNum = preg_replace('/[^0-9]/', '', $settings['social_whatsapp'] ?? '201000000000');
                            $drawerWaMsg = $settings['social_whatsapp_msg'] ?? ($isAr ? 'مرحباً، أود الاستفسار عن منتجات Atelier' : 'Hello, I have an inquiry about Atelier products');
                        @endphp
                        <a href="https://wa.me/{{ $drawerWaNum }}?text={{ urlencode($drawerWaMsg) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center justify-between p-3 bg-white rounded-xl border border-black/8 hover:border-black/20 hover:shadow-sm transition-all group active:scale-98">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.112.551 4.095 1.517 5.823l-1.61 5.885 6.036-1.583c1.667.909 3.578 1.427 5.609 1.427 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-black">{{ $isAr ? 'خدمة العملاء VIP واتساب' : 'VIP Concierge WhatsApp' }}</p>
                                    <p class="text-[9.5px] text-black/50 font-sans">{{ $isAr ? 'مساعدة واستفسار مباشر' : 'Instant live concierge' }}</p>
                                </div>
                            </div>
                            <span class="text-[9px] font-mono font-bold uppercase bg-emerald-600 text-white px-2 py-0.5 rounded-full flex items-center gap-1 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                <span>Online</span>
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Brand Guarantee -->
                <div class="p-3.5 rounded-xl bg-white border border-black/8 text-center shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-black mb-0.5 flex items-center justify-center gap-1">
                        <span class="text-amber-500">✦</span>
                        <span>{{ $isAr ? 'جلد طبيعي 100% وضمان ممتد' : '100% Full-Grain Leather Guaranteed' }}</span>
                        <span class="text-amber-500">✦</span>
                    </p>
                    <p class="text-[9px] font-sans text-black/60">
                        {{ $isAr ? 'صناعة يدوية دقيقة بمعايير التميز الإيطالي' : 'Bespoke precision crafting & lifetime commitment' }}
                    </p>
                </div>
            </div>

            <!-- Drawer Bottom Checkout Footer -->
            <div class="p-4 border-t border-black/10 bg-white shrink-0 shadow-lg">
                <a href="{{ route('cart.index') }}" 
                   @click="mobileMenuOpen = false"
                   class="w-full py-3.5 rounded-xl bg-black text-white text-xs font-bold tracking-wider uppercase flex items-center justify-center gap-2.5 shadow-md hover:bg-neutral-800 active:scale-95 transition-all cursor-pointer">
                    <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>{{ $isAr ? 'سلة المشتريات وإتمام الطلب' : 'View Bag & Checkout' }}</span>
                    <span class="bg-white text-black px-2 py-0.5 text-[10px] font-mono font-black leading-none rounded-md">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </div>
</header>

<script>
function headerSearchComponent() {
    return {
        mobileMenuOpen: false,
        searchQuery: '',
        searchFocused: false,
        liveResults: [],
        init() {
            window.addEventListener('open-sidebar-menu', () => { this.mobileMenuOpen = true; });
            window.addEventListener('close-sidebar-menu', () => { this.mobileMenuOpen = false; });
        },
        async performLiveSearch() {
            if (this.searchQuery.trim().length < 2) {
                this.liveResults = [];
                return;
            }
            try {
                const res = await fetch(`/collections/all?q=${encodeURIComponent(this.searchQuery.trim())}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.liveResults = data && data.products ? data.products.slice(0, 6) : [];
            } catch (e) {
                this.liveResults = [];
            }
        },
        submitSearch(e) {
            if (!this.searchQuery.trim()) {
                e.preventDefault();
            }
        }
    }
}
</script>
