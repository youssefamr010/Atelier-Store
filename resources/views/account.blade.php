@extends('layouts.app')

@section('title', 'Client Account — ' . ($settings['storeName'] ?? 'ATELIER'))

@section('content')
@php $isArAccount = ($settings['storefront_lang'] ?? 'en') === 'ar'; @endphp
<div class="bg-[#F5F5F0] min-h-screen py-6 sm:py-10 lg:py-16" x-data="{ activeTab: 'orders', showAddAddress: false, editingAddressId: null }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        
        @if(session('success'))
            <div class="bg-black text-white p-4 text-center text-xs font-editorial uppercase tracking-wider mb-8">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 p-4 text-xs mb-8">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @auth
            <!-- Logged In Dashboard -->
            <div class="border border-black/10 bg-white/85 backdrop-blur-xl p-4 sm:p-8 shadow-[0_18px_48px_rgba(0,0,0,.10)]">
                <!-- Header -->
                <div class="border-b border-black/10 pb-6 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-4">
                        <!-- Left: Identity -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-black text-white flex items-center justify-center font-editorial font-black text-xl uppercase shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <span class="font-editorial font-bold text-[9px] uppercase tracking-[0.25em] text-black/45 block mb-0.5">
                                    {{ $isArAccount ? 'حساب العميل' : 'Client Account' }}
                                </span>
                                <h1 class="font-editorial font-black text-xl sm:text-2xl uppercase tracking-tight text-black leading-none">
                                    {{ auth()->user()->name }}
                                </h1>
                                <p class="text-[11px] text-black/50 mt-0.5 font-sans">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <!-- Right: Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ url('/admin/dashboard') }}" class="inline-flex items-center gap-1.5 text-[10px] font-editorial font-bold uppercase tracking-wider bg-black text-white px-3 py-2 hover:bg-neutral-800 transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                                    {{ $isArAccount ? 'لوحة الإدارة' : 'Admin Suite' }}
                                </a>
                            @endif
                            <form action="{{ route('client.logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-editorial font-bold uppercase tracking-wider border border-black/30 bg-white px-3 py-2 hover:bg-black hover:text-white transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    {{ $isArAccount ? 'تسجيل الخروج' : 'Sign Out' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-1.5 p-1.5 bg-black/[.04] border border-black/10 rounded-xl mb-6">
                    <button 
                        @click="activeTab = 'orders'" 
                        :class="activeTab === 'orders' ? 'bg-white shadow-sm text-black font-black' : 'text-black/55 hover:text-black'"
                        class="min-h-[46px] px-2 text-[10px] sm:text-xs font-editorial font-bold uppercase tracking-wide rounded-lg transition-colors flex items-center justify-center gap-1.5"
                    >
                        <x-icon name="cart" class="w-3.5 h-3.5" />
                        <span>{{ $isArAccount ? 'الطلبات' : 'Orders' }} ({{ $orders->count() }})</span>
                    </button>

                    <button 
                        @click="activeTab = 'wishlist'" 
                        :class="activeTab === 'wishlist' ? 'bg-white shadow-sm text-black font-black' : 'text-black/55 hover:text-black'"
                        class="min-h-[46px] px-2 text-[10px] sm:text-xs font-editorial font-bold uppercase tracking-wide rounded-lg transition-colors flex items-center justify-center gap-1.5"
                    >
                        <x-icon name="heart" class="w-3.5 h-3.5 text-red-600" />
                        <span>{{ $isArAccount ? 'المفضلة' : 'Wishlist' }} ({{ $wishlistItems->count() }})</span>
                    </button>

                    <button 
                        @click="activeTab = 'loyalty'" 
                        :class="activeTab === 'loyalty' ? 'bg-white shadow-sm text-black font-black' : 'text-black/55 hover:text-black'"
                        class="min-h-[46px] px-2 text-[10px] sm:text-xs font-editorial font-bold uppercase tracking-wide rounded-lg transition-colors flex items-center justify-center gap-1.5"
                    >
                        <x-icon name="gift" class="w-3.5 h-3.5 text-amber-600" />
                        <span>{{ $isArAccount ? 'نقاط الولاء' : 'Rewards' }} ({{ $loyaltyBalance }})</span>
                    </button>

                    <button 
                        @click="activeTab = 'addresses'" 
                        :class="activeTab === 'addresses' ? 'bg-white shadow-sm text-black font-black' : 'text-black/55 hover:text-black'"
                        class="min-h-[46px] px-2 text-[10px] sm:text-xs font-editorial font-bold uppercase tracking-wide rounded-lg transition-colors flex items-center justify-center gap-1.5"
                    >
                        <x-icon name="truck" class="w-3.5 h-3.5" />
                        <span>{{ $isArAccount ? 'العناوين' : 'Addresses' }} ({{ $addresses->count() }})</span>
                    </button>

                    <button 
                        @click="activeTab = 'details'" 
                        :class="activeTab === 'details' ? 'bg-white shadow-sm text-black font-black' : 'text-black/55 hover:text-black'"
                        class="min-h-[46px] px-2 text-[10px] sm:text-xs font-editorial font-bold uppercase tracking-wide rounded-lg transition-colors flex items-center justify-center gap-1.5 col-span-2 sm:col-span-1"
                    >
                        <x-icon name="account" class="w-3.5 h-3.5" />
                        <span>{{ $isArAccount ? 'الملف' : 'Profile' }}</span>
                    </button>
                </div>

                <!-- Tab 1: Order History -->
                <div x-show="activeTab === 'orders'" class="space-y-6">
                    @if($orders->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($orders as $order)
                                <div class="border-2 border-black/10 p-5 hover:border-black transition-colors bg-[#FAFAFA]">
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-4 border-b border-black/10 gap-2">
                                        <div>
                                            <span class="font-editorial font-bold text-sm text-black">Order #{{ $order->order_number }}</span>
                                            <span class="text-xs text-black/60 block">{{ $order->created_at->format('M d, Y — h:i A') }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 flex-wrap">
                                            @if($order->shipping_status)
                                                @php
                                                    $shippingBadge = match($order->shipping_status) {
                                                        'delivered'  => 'bg-green-100 text-green-800',
                                                        'shipped'    => 'bg-blue-100 text-blue-800',
                                                        'processing' => 'bg-purple-100 text-purple-800',
                                                        'cancelled'  => 'bg-red-100 text-red-800',
                                                        default      => 'bg-gray-100 text-gray-800',
                                                    };
                                                @endphp
                                                <span class="px-2.5 py-0.5 text-[10px] font-editorial font-bold uppercase tracking-wider {{ $shippingBadge }}">
                                                    {{ $order->shipping_status }}
                                                </span>
                                            @endif
                                            <span class="px-2.5 py-0.5 text-[10px] font-editorial font-bold uppercase tracking-wider {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ str_replace('_', ' ', $order->payment_status) }}
                                            </span>
                                            <span class="font-editorial font-bold text-sm text-black">
                                                {{ number_format($order->total_amount_minor / 100, 2) }} {{ $order->currency }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($order->items->isNotEmpty())
                                        <div class="pt-3 space-y-2">
                                            @foreach($order->items as $item)
                                                <div class="flex items-center justify-between gap-3 text-xs text-black/80 font-sans">
                                                    <span class="flex items-center gap-2 min-w-0">
                                                        @if(!empty($item->metadata_json['image_url']))<img src="{{ $item->metadata_json['image_url'] }}" alt="" class="w-9 h-9 object-contain bg-white border border-black/10 shrink-0" loading="lazy" decoding="async">@endif
                                                        <span class="min-w-0"><b class="block truncate">{{ $item->quantity }}x {{ $item->product_title }}</b>@if(!empty($item->metadata_json['variant_title']))<small class="mt-0.5 inline-flex items-center gap-1 text-black/55">@if(!empty($item->metadata_json['color_hex']))<i class="w-2 h-2 rounded-full border border-black/20" style="background: {{ $item->metadata_json['color_hex'] }}"></i>@endif{{ $item->metadata_json['variant_title'] }}</small>@endif</span>
                                                    </span>
                                                    <span>{{ number_format($item->total_price_minor / 100, 2) }} {{ $order->currency }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="pt-3 mt-3 border-t border-black/10 flex justify-end">
                                        <a href="{{ route('client.orders.show', $order) }}" class="text-xs font-editorial font-bold uppercase tracking-wider border border-black px-4 py-2 hover:bg-black hover:text-white transition-colors">
                                            View Order Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-neutral-50 border border-dashed border-black/20">
                            <p class="font-editorial text-sm uppercase tracking-wider text-black/60 mb-4">You have no recent orders in the archive.</p>
                            <a href="{{ route('home') }}" class="btn-luxury inline-block px-6 py-3 text-xs tracking-wider">
                                Explore Boutique →
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Tab: Wishlist (Saved Favorites) -->
                <div x-cloak x-show="activeTab === 'wishlist'" class="space-y-6">
                    <div class="flex justify-between items-center border-b border-black/10 pb-4">
                        <div>
                            <h2 class="font-editorial font-bold text-base uppercase">{{ $isArAccount ? 'قائمة المفضلة والقطع المحفوظة' : 'My Curated Wishlist' }}</h2>
                            <p class="text-xs text-black/60">{{ $isArAccount ? 'القطع التي قمت بحفظها للرجوع إليها أو شرائها لاحقاً.' : 'Pieces you have saved to your private curation for future acquisition.' }}</p>
                        </div>
                    </div>

                    @if($wishlistItems->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($wishlistItems as $wItem)
                                @php
                                    $wProd = $wItem->product;
                                    if(!$wProd) continue;
                                    $wImg = $wProd->image_url 
                                        ? (str_starts_with($wProd->image_url, 'http') ? $wProd->image_url : url($wProd->image_url))
                                        : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=400&q=80';
                                    $wPrice = $wProd->retail_price_minor ? number_format($wProd->retail_price_minor / 100, 0) . ' EGP' : '—';
                                @endphp
                                <div class="border-2 border-black bg-white p-3.5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col justify-between space-y-3">
                                    <div class="space-y-2">
                                        <div class="aspect-4/3 w-full bg-[#F5F5F0] border border-black/10 relative overflow-hidden">
                                            <img src="{{ $wImg }}" alt="{{ $wProd->title }}" class="w-full h-full object-contain">
                                            <button 
                                                type="button" 
                                                @click="$store.wishlist.toggle({{ $wProd->id }}); window.location.reload();"
                                                class="absolute top-2 right-2 bg-white/90 hover:bg-black hover:text-white p-1.5 border border-black transition-colors"
                                                title="Remove from Wishlist"
                                            >
                                                <x-icon name="trash" class="w-3.5 h-3.5" />
                                            </button>
                                        </div>

                                        <h3 class="font-editorial font-bold text-xs uppercase tracking-tight text-black line-clamp-1">{{ $wProd->title }}</h3>
                                        <div class="flex items-baseline justify-between">
                                            <span class="font-editorial font-black text-sm text-black">{{ $wPrice }}</span>
                                            <span class="text-[9px] font-mono {{ $wProd->inventory > 0 ? 'text-emerald-700 font-bold' : 'text-red-600' }}">
                                                {{ $wProd->inventory > 0 ? '● In Stock' : 'Out of Stock' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-black/10">
                                        <a href="{{ route('products.show', ['slug' => $wProd->slug]) }}" class="btn-luxury w-full py-2.5 text-center text-[10px] font-editorial font-bold uppercase tracking-wider block">
                                            {{ $isArAccount ? 'عرض القطعة ←' : 'View Piece →' }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-neutral-50 border border-dashed border-black/20 space-y-3">
                            <x-icon name="heart" class="w-8 h-8 text-black/30 mx-auto" />
                            <p class="font-editorial text-xs uppercase tracking-wider text-black/60">{{ $isArAccount ? 'قائمة المفضلة فارغة حالياً.' : 'Your wishlist is currently empty.' }}</p>
                            <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="btn-luxury inline-block px-6 py-2.5 text-xs tracking-wider">
                                {{ $isArAccount ? 'تصفح التشكيلات' : 'Explore Collections' }}
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Tab: Loyalty Points & Rewards -->
                <div x-cloak x-show="activeTab === 'loyalty'" class="space-y-6">
                    <div class="border-b border-black/10 pb-4">
                        <h2 class="font-editorial font-bold text-base uppercase">{{ $isArAccount ? 'برنامج مكافآت ونقاط الولاء' : 'Atelier Private Privilege & Rewards' }}</h2>
                        <p class="text-xs text-black/60">{{ $isArAccount ? 'اكسب نقاطاً مع كل طلب مكتمل واستبدلها بخصومات حصرية عند إتمام الطلب.' : 'Earn rewards with every delivered commission and redeem instant discounts at checkout.' }}</p>
                    </div>

                    @php
                        $redeemUnit = (int)($settings['loyalty_redeem_pts_unit'] ?? 100);
                        $discountEgp = (int)($settings['loyalty_redeem_discount_egp'] ?? 50);
                        $discountEquiv = floor($loyaltyBalance / max(1, $redeemUnit)) * $discountEgp;
                        $earnRate = (int)($settings['loyalty_earn_rate_egp'] ?? 10);
                    @endphp

                    <!-- Points Stats Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <span class="text-[10px] font-editorial font-bold uppercase tracking-widest text-gray-500 block">{{ $isArAccount ? 'رصيد النقاط المتاح' : 'Available Points Balance' }}</span>
                            <span class="text-3xl font-black font-mono text-amber-600 block mt-1">{{ number_format($loyaltyBalance) }} <small class="text-xs font-editorial">PTS</small></span>
                            <span class="text-[10px] text-gray-400 mt-1 block">جاهزة للاستبدال كخصم</span>
                        </div>

                        <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <span class="text-[10px] font-editorial font-bold uppercase tracking-widest text-gray-500 block">{{ $isArAccount ? 'قيمة الخصم المعادلة' : 'Redeemable Value' }}</span>
                            <span class="text-3xl font-black font-mono text-emerald-600 block mt-1">{{ number_format($discountEquiv) }} <small class="text-xs font-editorial">EGP</small></span>
                            <span class="text-[10px] text-gray-400 mt-1 block">كل {{ $redeemUnit }} نقطة = {{ $discountEgp }} ج.م خصم</span>
                        </div>

                        <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <span class="text-[10px] font-editorial font-bold uppercase tracking-widest text-gray-500 block">{{ $isArAccount ? 'معدل الكسب' : 'Earning Privilege' }}</span>
                            <span class="text-xl font-bold font-mono text-black block mt-2">1 pt / {{ $earnRate }} EGP</span>
                            <span class="text-[10px] text-gray-400 mt-1 block">تضاف تلقائياً عند استلام الطلب</span>
                        </div>
                    </div>

                    <!-- Points History Ledger -->
                    <div class="space-y-3">
                        <h3 class="font-editorial font-bold text-xs uppercase tracking-wider text-black">{{ $isArAccount ? 'سجل المعاملات والنقاط' : 'Privilege Activity Ledger' }}</h3>
                        
                        <div class="border-2 border-black bg-white overflow-x-auto shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <table class="w-full text-right text-xs">
                                <thead class="bg-black text-white text-[10px] uppercase font-editorial tracking-wider">
                                    <tr>
                                        <th class="p-3">التاريخ</th>
                                        <th class="p-3">نوع العملية</th>
                                        <th class="p-3">التفاصيل</th>
                                        <th class="p-3 text-left">النقاط</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 font-sans">
                                    @forelse($loyaltyLedgers as $ledger)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-3 font-mono text-gray-500 text-[11px]">{{ $ledger->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="p-3">
                                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase {{ $ledger->points > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ str_replace('_', ' ', $ledger->type) }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-gray-700 text-xs">{{ $ledger->notes ?: '—' }}</td>
                                            <td class="p-3 font-mono font-black text-sm text-left {{ $ledger->points > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                                {{ $ledger->points > 0 ? '+' . $ledger->points : $ledger->points }} pts
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-6 text-center text-gray-400 font-editorial uppercase tracking-wider text-xs">
                                                {{ $isArAccount ? 'لا توجد حركات نقاط مسجلة حتى الآن.' : 'No reward points transactions recorded yet.' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: My Addresses (Address Book) -->
                <div x-cloak x-show="activeTab === 'addresses'" class="space-y-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-editorial font-bold text-base uppercase">Saved Delivery Addresses</h2>
                            <p class="text-xs text-black/60">Manage your shipping destinations for fast one-click checkout.</p>
                        </div>
                        <button 
                            @click="showAddAddress = !showAddAddress; if(showAddAddress) { setTimeout(() => window.dispatchEvent(new CustomEvent('map-refresh-account-map')), 150); }" 
                            class="bg-black text-white px-4 py-2 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-neutral-800"
                        >
                            <span x-show="!showAddAddress">+ Add New Address</span>
                            <span x-show="showAddAddress">Cancel</span>
                        </button>
                    </div>

                    <!-- Add Address Form -->
                    <div x-show="showAddAddress" class="border-2 border-black p-6 bg-[#FAFAFA] transition-all">
                        <h3 class="font-editorial font-bold text-sm uppercase tracking-wider mb-4">Add New Delivery Address</h3>
                        <form action="{{ route('addresses.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- Map Picker -->
                            <div class="pb-2">
                                @include('partials.location-map', [
                                    'mapId'         => 'account-map',
                                    'latInputId'    => 'account-lat',
                                    'lngInputId'    => 'account-lng',
                                    'cityInputId'   => 'account-city',
                                    'streetInputId' => 'account-street',
                                ])
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-editorial font-bold text-[10px] uppercase tracking-wider text-black mb-1">Address Label</label>
                                    <input type="text" name="label" required placeholder="e.g. Home, Office, Villa" class="w-full border border-black p-3 text-xs bg-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block font-editorial font-bold text-[10px] uppercase tracking-wider text-black mb-1">Full Recipient Name</label>
                                    <input type="text" name="full_name" required value="{{ auth()->user()->name }}" placeholder="Recipient Name" class="w-full border border-black p-3 text-xs bg-white focus:outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-editorial font-bold text-[10px] uppercase tracking-wider text-black mb-1">Phone Number (WhatsApp)</label>
                                    <input type="tel" name="phone" required placeholder="010XXXXXXXX" class="w-full border border-black p-3 text-xs bg-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block font-editorial font-bold text-[10px] uppercase tracking-wider text-black mb-1">Governorate / City</label>
                                    <input type="text" name="city" id="account-city" required placeholder="Cairo / Giza / Alexandria" class="w-full border border-black p-3 text-xs bg-white focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block font-editorial font-bold text-[10px] uppercase tracking-wider text-black mb-1">Detailed Street Address</label>
                                <input type="text" name="street_address" id="account-street" required placeholder="Building name/no, Street, Floor, Apt" class="w-full border border-black p-3 text-xs bg-white focus:outline-none">
                            </div>
                            <div class="flex items-center gap-2 pt-2">
                                <input type="checkbox" name="is_default" value="1" id="add_is_default" class="w-4 h-4 accent-black">
                                <label for="add_is_default" class="font-editorial text-xs uppercase tracking-wider text-black cursor-pointer">
                                    Set as default delivery address
                                </label>
                            </div>
                            <button type="submit" class="btn-luxury px-6 py-3 text-xs tracking-wider">
                                Save Address →
                            </button>
                        </form>
                    </div>

                    <!-- Address List -->
                    @if($addresses->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($addresses as $address)
                                <div class="border-2 {{ $address->is_default ? 'border-black bg-white' : 'border-black/20 bg-[#FAFAFA]' }} p-5 relative">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-editorial font-bold text-xs uppercase tracking-wider bg-black text-white px-2 py-0.5">
                                                {{ $address->label }}
                                            </span>
                                            @if($address->is_default)
                                                <span class="border border-black text-black text-[9px] font-editorial font-bold uppercase tracking-widest px-1.5 py-0.5">
                                                    DEFAULT
                                                </span>
                                            @endif
                                        </div>

                                        @if($address->latitude && $address->longitude)
                                            <a 
                                                href="https://maps.google.com/?q={{ $address->latitude }},{{ $address->longitude }}" 
                                                target="_blank" 
                                                class="text-[9px] font-mono font-bold uppercase border border-black/30 bg-white hover:bg-black hover:text-white px-2 py-0.5 flex items-center gap-1 transition-colors"
                                                title="View pinned GPS location on Google Maps"
                                            >
                                                <span><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> GPS: {{ number_format($address->latitude, 3) }}, {{ number_format($address->longitude, 3) }} ↗</span>
                                            </a>
                                        @endif
                                    </div>

                                    <p class="font-editorial font-bold text-sm text-black mb-1">{{ $address->full_name }}</p>
                                    <p class="text-xs text-black/80 font-sans mb-1">{{ $address->street_address }}</p>
                                    <p class="text-xs text-black/80 font-sans mb-2">{{ $address->city }}, Egypt</p>
                                    <p class="text-xs text-black/60 font-sans mb-4">Phone: {{ $address->phone }}</p>

                                    <div class="flex items-center justify-between border-t border-black/10 pt-3 text-xs font-editorial font-bold uppercase tracking-wider">
                                        <div class="flex items-center gap-3">
                                            <button 
                                                type="button" 
                                                @click="editingAddressId = editingAddressId === {{ $address->id }} ? null : {{ $address->id }}"
                                                class="text-black hover:underline"
                                            >
                                                Edit
                                            </button>
                                            
                                            <form action="{{ route('addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this saved address?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>

                                        @if(!$address->is_default)
                                            <form action="{{ route('addresses.set-default', $address->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-black/60 hover:text-black hover:underline">
                                                    Make Default
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Inline Edit Form -->
                                    <div x-show="editingAddressId === {{ $address->id }}" class="mt-4 pt-4 border-t border-black/20">
                                        <h4 class="font-editorial font-bold text-xs uppercase mb-3">Edit Address ({{ $address->label }})</h4>
                                        <form action="{{ route('addresses.update', $address->id) }}" method="POST" class="space-y-3">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="block text-[9px] font-editorial uppercase tracking-wider text-black/70 mb-0.5">Label</label>
                                                <input type="text" name="label" value="{{ $address->label }}" required class="w-full border border-black p-2 text-xs bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-editorial uppercase tracking-wider text-black/70 mb-0.5">Full Name</label>
                                                <input type="text" name="full_name" value="{{ $address->full_name }}" required class="w-full border border-black p-2 text-xs bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-editorial uppercase tracking-wider text-black/70 mb-0.5">Phone Number</label>
                                                <input type="tel" name="phone" value="{{ $address->phone }}" required class="w-full border border-black p-2 text-xs bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-editorial uppercase tracking-wider text-black/70 mb-0.5">Governorate / City</label>
                                                <input type="text" name="city" value="{{ $address->city }}" required class="w-full border border-black p-2 text-xs bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-editorial uppercase tracking-wider text-black/70 mb-0.5">Street Address</label>
                                                <input type="text" name="street_address" value="{{ $address->street_address }}" required class="w-full border border-black p-2 text-xs bg-white">
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" name="is_default" value="1" id="edit_def_{{ $address->id }}" {{ $address->is_default ? 'checked' : '' }} class="w-3.5 h-3.5 accent-black">
                                                <label for="edit_def_{{ $address->id }}" class="text-[10px] font-editorial uppercase">Set as default</label>
                                            </div>
                                            <div class="flex gap-2 pt-1">
                                                <button type="submit" class="bg-black text-white px-4 py-2 text-[10px] font-editorial font-bold uppercase tracking-wider">Save Changes</button>
                                                <button type="button" @click="editingAddressId = null" class="border border-black px-4 py-2 text-[10px] font-editorial uppercase">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-neutral-50 border border-dashed border-black/20">
                            <p class="font-editorial text-sm uppercase tracking-wider text-black/60 mb-4">No saved addresses yet.</p>
                            <button @click="showAddAddress = true" class="btn-luxury inline-block px-6 py-3 text-xs tracking-wider">
                                Add Your First Delivery Address →
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Tab 3: Account Details & Security -->
                <div x-cloak x-show="activeTab === 'details'" class="space-y-6" x-data="{ editingProfile: false, changingPassword: false, showDeleteModal: false, profileForm: { name: '{{ auth()->user()->name }}', email: '{{ auth()->user()->email }}', phone: '{{ auth()->user()->phone ?? '' }}' }, passwordForm: { current_password: '', password: '', password_confirmation: '' }, profileMessage: '', passwordMessage: '', profileError: '', passwordError: '' }">
                    
                    {{-- Account Statistics --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="border border-black/10 p-4 bg-[#FAFAFA] text-center">
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-wider text-black/50 block mb-1">Member Since</span>
                            <span class="text-sm font-bold text-black">{{ auth()->user()->created_at->format('M Y') }}</span>
                        </div>
                        <div class="border border-black/10 p-4 bg-[#FAFAFA] text-center">
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-wider text-black/50 block mb-1">Total Orders</span>
                            <span class="text-sm font-bold text-black">{{ $orders->count() }}</span>
                        </div>
                        <div class="border border-black/10 p-4 bg-[#FAFAFA] text-center">
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-wider text-black/50 block mb-1">Total Spent</span>
                            <span class="text-sm font-bold text-black">{{ number_format($orders->sum('total_amount_minor') / 100, 0) }} EGP</span>
                        </div>
                        <div class="border border-black/10 p-4 bg-[#FAFAFA] text-center">
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-wider text-black/50 block mb-1">Addresses</span>
                            <span class="text-sm font-bold text-black">{{ $addresses->count() }}</span>
                        </div>
                    </div>

                    {{-- Client Profile (Editable) --}}
                    <div class="border border-black/10 p-6 bg-[#FAFAFA] space-y-4">
                        <div class="flex items-center justify-between border-b border-black/10 pb-3">
                            <h2 class="font-editorial font-bold text-base uppercase">Client Profile</h2>
                            <button @click="editingProfile = !editingProfile; profileMessage = ''; profileError = ''" class="text-xs font-editorial font-bold uppercase tracking-wider border border-black px-3 py-1.5 hover:bg-black hover:text-white transition-colors">
                                <span x-show="!editingProfile">Edit Profile</span>
                                <span x-show="editingProfile">Cancel</span>
                            </button>
                        </div>

                        {{-- View Mode --}}
                        <div x-show="!editingProfile" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-sans">
                            <div>
                                <span class="font-editorial font-bold uppercase text-[10px] text-black/60 block mb-1">{{ $isArAccount ? 'الاسم الكامل' : 'Full Name' }}</span>
                                <span class="text-sm font-semibold text-black" x-text="profileForm.name">{{ auth()->user()->name }}</span>
                            </div>
                            <div>
                                <span class="font-editorial font-bold uppercase text-[10px] text-black/60 block mb-1">{{ $isArAccount ? 'البريد الإلكتروني' : 'Email Address' }}</span>
                                <span class="text-sm font-semibold text-black" x-text="profileForm.email">{{ auth()->user()->email }}</span>
                            </div>
                            <div>
                                <span class="font-editorial font-bold uppercase text-[10px] text-black/60 block mb-1">{{ $isArAccount ? 'رقم الهاتف (واتساب)' : 'Phone (WhatsApp)' }}</span>
                                <span class="text-sm font-semibold text-black" x-text="profileForm.phone || '—'">{{ auth()->user()->phone ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Edit Mode --}}
                        <div x-show="editingProfile" x-cloak class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-editorial font-bold uppercase tracking-wider text-black/60 mb-1">{{ $isArAccount ? 'الاسم الكامل' : 'Full Name' }}</label>
                                    <input type="text" x-model="profileForm.name" class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-editorial font-bold uppercase tracking-wider text-black/60 mb-1">{{ $isArAccount ? 'البريد الإلكتروني' : 'Email Address' }}</label>
                                    <input type="email" x-model="profileForm.email" class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-editorial font-bold uppercase tracking-wider text-black/60 mb-1">{{ $isArAccount ? 'رقم الهاتف (واتساب) — اختياري' : 'Phone Number (WhatsApp) — Optional' }}</label>
                                <input type="tel" x-model="profileForm.phone" placeholder="010XXXXXXXX" class="w-full sm:w-64 border border-black p-2.5 text-xs bg-white focus:outline-none">
                            </div>
                            <div x-show="profileMessage" x-cloak class="text-xs text-green-700 font-medium bg-green-50 border border-green-300 p-2" x-text="profileMessage"></div>
                            <div x-show="profileError" x-cloak class="text-xs text-red-700 font-medium bg-red-50 border border-red-300 p-2" x-text="profileError"></div>
                            <div class="flex items-center gap-2 pt-1">
                                <button @click="
                                    profileError = ''; profileMessage = '';
                                    fetch('{{ route('account.profile.update') }}', {
                                        method: 'PUT',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                        body: JSON.stringify(profileForm)
                                    }).then(r => r.json()).then(data => {
                                        if (data.success) { profileMessage = data.message; editingProfile = false; }
                                        else { profileError = data.message || 'Failed to update profile.'; }
                                    }).catch(() => { profileError = 'Network error. Please try again.'; })
                                " class="bg-black text-white px-5 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-neutral-800 transition-colors">
                                    {{ $isArAccount ? 'حفظ التغييرات' : 'Save Changes' }}
                                </button>
                                <button @click="editingProfile = false" class="border border-black/30 bg-white px-4 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-black/5 transition-colors">
                                    {{ $isArAccount ? 'إلغاء' : 'Cancel' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Change Password --}}
                    <div class="border border-black/10 p-6 bg-[#FAFAFA] space-y-4">
                        <div class="flex items-center justify-between border-b border-black/10 pb-3">
                            <h2 class="font-editorial font-bold text-base uppercase">Account Security</h2>
                            <button @click="changingPassword = !changingPassword; passwordMessage = ''; passwordError = ''; passwordForm = { current_password: '', password: '', password_confirmation: '' }" class="text-xs font-editorial font-bold uppercase tracking-wider border border-black px-3 py-1.5 hover:bg-black hover:text-white transition-colors">
                                <span x-show="!changingPassword">Change Password</span>
                                <span x-show="changingPassword">Cancel</span>
                            </button>
                        </div>

                        <div x-show="!changingPassword" class="text-xs space-y-2">
                            <p class="text-black/80 font-sans">
                                <strong>Password last changed:</strong> 
                                {{ auth()->user()->password_changed_at ? auth()->user()->password_changed_at->format('M d, Y h:i A') : 'Initial Account Setup' }}
                            </p>
                            <p class="text-black/60 text-[11px] font-sans">
                                Click "Change Password" to update your account password directly, or request a secure reset link via email below.
                            </p>
                        </div>

                        {{-- Inline Password Change Form --}}
                        <div x-show="changingPassword" x-cloak class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-editorial font-bold uppercase tracking-wider text-black/60 mb-1">Current Password</label>
                                <input type="password" x-model="passwordForm.current_password" class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none" placeholder="Enter your current password">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-editorial font-bold uppercase tracking-wider text-black/60 mb-1">New Password</label>
                                    <input type="password" x-model="passwordForm.password" class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none" placeholder="Min 8 characters">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-editorial font-bold uppercase tracking-wider text-black/60 mb-1">Confirm New Password</label>
                                    <input type="password" x-model="passwordForm.password_confirmation" class="w-full border border-black p-2.5 text-xs bg-white focus:outline-none" placeholder="Repeat new password">
                                </div>
                            </div>
                            <div x-show="passwordMessage" x-cloak class="text-xs text-green-700 font-medium bg-green-50 border border-green-300 p-2" x-text="passwordMessage"></div>
                            <div x-show="passwordError" x-cloak class="text-xs text-red-700 font-medium bg-red-50 border border-red-300 p-2" x-text="passwordError"></div>
                            <button @click="
                                passwordError = ''; passwordMessage = '';
                                fetch('{{ route('account.password.update') }}', {
                                    method: 'PUT',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                    body: JSON.stringify(passwordForm)
                                }).then(r => r.json()).then(data => {
                                    if (data.success) { passwordMessage = data.message; changingPassword = false; passwordForm = { current_password: '', password: '', password_confirmation: '' }; }
                                    else { passwordError = data.message || 'Failed to change password.'; }
                                }).catch(() => { passwordError = 'Network error. Please try again.'; })
                            " class="bg-black text-white px-5 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-neutral-800 transition-colors">
                                Update Password
                            </button>
                        </div>

                        <div x-show="!changingPassword" class="pt-2">
                            <form action="{{ route('password.email') }}" method="POST" class="inline-block">
                                @csrf
                                <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                                <button type="submit" class="border border-black/30 bg-white px-4 py-2 text-[10px] font-editorial font-bold uppercase tracking-wider hover:bg-black/5 transition-colors text-black/70">
                                    Send Password Reset Email
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Delete Account --}}
                    <div class="border border-red-200 p-6 bg-red-50/50 space-y-3">
                        <h2 class="font-editorial font-bold text-base uppercase text-red-900">Danger Zone</h2>
                        <p class="text-xs text-red-800/70 font-sans">Permanently delete your account and all associated data. This action cannot be undone.</p>
                        <button @click="showDeleteModal = true" class="border-2 border-red-600 text-red-700 px-5 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-red-600 hover:text-white transition-colors">
                            Delete My Account
                        </button>

                        {{-- Delete Confirmation Modal --}}
                        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="showDeleteModal = false">
                            <div class="bg-white border-2 border-black p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] max-w-sm w-full mx-4">
                                <h3 class="font-editorial font-black text-lg uppercase text-black mb-2">Confirm Deletion</h3>
                                <p class="text-xs text-black/70 mb-6">Are you sure you want to permanently delete your account? All your orders, addresses, and personal data will be removed. This cannot be undone.</p>
                                <div class="flex gap-3">
                                    <form action="{{ route('account.destroy') }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full bg-red-600 text-white py-3 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-red-700 transition-colors">
                                            Yes, Delete Forever
                                        </button>
                                    </form>
                                    <button @click="showDeleteModal = false" class="flex-1 border-2 border-black py-3 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors">
                                        Keep Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

        @else
            @php
                $portalBgUrl = $settings['account_portal_background_url'] ?? '';
                $portalOverlay = $settings['account_portal_overlay'] ?? 'medium';
            @endphp

            <!-- Guest: Minimal Luxury Portal -->
            <div class="relative py-8 sm:py-16">
                @if(!empty($portalBgUrl))
                    <div class="fixed inset-0 bg-cover bg-center z-0 pointer-events-none" style="background-image: url('{{ $portalBgUrl }}');"></div>
                    <div class="fixed inset-0 z-0 pointer-events-none {{ 
                        $portalOverlay === 'none' ? 'bg-transparent' : (
                        $portalOverlay === 'subtle' ? 'bg-black/35' : (
                        $portalOverlay === 'dark' ? 'bg-black/80' : 
                        'bg-gradient-to-t from-black/90 via-black/60 to-black/40'
                    )) }}"></div>
                @endif

                <div class="relative z-10 max-w-sm mx-auto">
                    <div class="border-2 border-black {{ !empty($portalBgUrl) ? 'bg-white/95 backdrop-blur-md' : 'bg-white' }} px-10 py-12 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] text-center">

                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 border border-black/30 px-3 py-1 mb-8">
                            <span class="w-1 h-1 bg-black rounded-full"></span>
                            <span class="font-editorial text-[9px] uppercase tracking-[0.3em] text-black/60">Client Access</span>
                        </div>

                        <!-- Title -->
                        <h1 class="font-display text-3xl uppercase tracking-tight text-black font-normal mb-2">
                            {{ $settings['storeName'] ?? 'ATELIER' }}
                        </h1>
                        <p class="text-[11px] text-black/40 tracking-wide mb-8">Sign in to your account</p>

                        <!-- Google Button — Clean White -->
                        <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 border border-black/20 bg-white hover:bg-gray-50 transition-colors py-3.5 px-5 shadow-sm hover:shadow-md">
                            <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                            </svg>
                            <span class="text-[11px] font-medium text-gray-700 tracking-wide">Continue with Google</span>
                        </a>

                        <!-- Footer -->
                        <div class="mt-8 pt-6 border-t border-black/8 flex items-center justify-between">
                            <a href="{{ url('/pages/terms') }}" class="text-[10px] text-black/30 hover:text-black/60 transition-colors">Terms</a>
                            <a href="{{ url('/admin/login') }}" class="text-[10px] font-mono text-black/25 hover:text-black/50 transition-colors uppercase tracking-wider">Staff →</a>
                            <a href="{{ url('/pages/privacy') }}" class="text-[10px] text-black/30 hover:text-black/60 transition-colors">Privacy</a>
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</div>
@endsection
