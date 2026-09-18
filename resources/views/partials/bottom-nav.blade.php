@php
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $cartCount = \App\Http\Controllers\CartController::cartCount();
    $isHome = request()->routeIs('home');
    $isCatalog = request()->is('collections*') || request()->routeIs('collections*');
    $isAccount = request()->routeIs('account*') || request()->routeIs('login') || request()->routeIs('register');
    $isWishlist = request()->routeIs('wishlist*');
    $isCart = request()->routeIs('cart*') || request()->routeIs('checkout*');
@endphp

<!-- Luxury Mobile Floating Bottom Navigation Bar -->
<nav class="md:hidden fixed bottom-3 left-3 right-3 z-40 bg-white/92 backdrop-blur-xl border border-black/10 rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] px-2 py-2 transition-all duration-300 ring-1 ring-black/5" aria-label="Mobile Navigation">
    <div class="flex items-center justify-around">
        
        <!-- 1. Home -->
        <a href="{{ route('home') }}" 
           class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 {{ $isHome ? 'text-black font-bold scale-105' : 'text-black/50 hover:text-black' }}"
           title="{{ $isAr ? 'الرئيسية' : 'Home' }}">
            <svg class="w-5 h-5 {{ $isHome ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[9.5px] mt-0.5 tracking-tight {{ $isHome ? 'font-black' : 'font-medium' }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</span>
            @if($isHome)
                <span class="w-1 h-1 rounded-full bg-black mt-0.5"></span>
            @endif
        </a>

        <!-- 2. Categories / Drawer Menu -->
        <button type="button" 
                onclick="window.dispatchEvent(new CustomEvent('open-sidebar-menu'))"
                class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 text-black/50 hover:text-black cursor-pointer"
                title="{{ $isAr ? 'الأقسام' : 'Menu' }}">
            <svg class="w-5 h-5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="text-[9.5px] mt-0.5 tracking-tight font-medium">{{ $isAr ? 'الأقسام' : 'Explore' }}</span>
        </button>

        <!-- 3. Smart Search -->
        <button type="button" 
                onclick="window.dispatchEvent(new CustomEvent('open-smart-search'))"
                class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 text-black/50 hover:text-black cursor-pointer"
                title="{{ $isAr ? 'بحث' : 'Search' }}">
            <svg class="w-5 h-5 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
            <span class="text-[9.5px] mt-0.5 tracking-tight font-medium">{{ $isAr ? 'بحث' : 'Search' }}</span>
        </button>

        <!-- 4. Account -->
        <a href="{{ route('account') }}" 
           class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 {{ $isAccount ? 'text-black font-bold scale-105' : 'text-black/50 hover:text-black' }}"
           title="{{ $isAr ? 'حسابي' : 'Account' }}">
            <svg class="w-5 h-5 {{ $isAccount ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-[9.5px] mt-0.5 tracking-tight {{ $isAccount ? 'font-black' : 'font-medium' }}">{{ $isAr ? 'حسابي' : 'Account' }}</span>
            @if($isAccount)
                <span class="w-1 h-1 rounded-full bg-black mt-0.5"></span>
            @endif
        </a>

        <!-- 5. Luxury Bag Button with Badge -->
        <a href="{{ route('cart.index') }}" 
           class="relative flex flex-col items-center justify-center py-1 px-3 rounded-xl transition-all duration-200 {{ $isCart ? 'text-black font-bold scale-105' : 'text-black/50 hover:text-black' }}"
           title="{{ $isAr ? 'حقيبة التسوق' : 'Bag' }}">
            <div class="relative">
                <svg class="w-5 h-5 {{ $isCart ? 'stroke-[2.4]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                @if($cartCount > 0)
                    <span class="absolute -top-1.5 -right-2 bg-black text-white text-[9px] font-mono font-bold w-4 h-4 rounded-full flex items-center justify-center ring-2 ring-white">
                        {{ $cartCount }}
                    </span>
                @endif
            </div>
            <span class="text-[9.5px] mt-0.5 tracking-tight {{ $isCart ? 'font-black' : 'font-medium' }}">{{ $isAr ? 'السلة' : 'Bag' }}</span>
            @if($isCart)
                <span class="w-1 h-1 rounded-full bg-black mt-0.5"></span>
            @endif
        </a>

    </div>
</nav>
