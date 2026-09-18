@extends('layouts.app')

@php
    $isArTrack = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $whatsappNumber = preg_replace('/[^0-9]/', '', $settings['social_whatsapp'] ?? '201000000000');
    $whatsappMsg = urlencode($settings['social_whatsapp_msg'] ?? ($isArTrack ? 'مرحباً، أود الاستفسار عن طلبي في Atelier' : 'Hello, I would like to inquire about my Atelier order'));
@endphp

@section('title', ($isArTrack ? 'تتبع مسار الشحنات والطلبات — ' : 'Live Shipment Tracking — ') . ($settings['storeName'] ?? 'ATELIER'))
@section('meta_description', $isArTrack ? 'تابع خط سير وتفاصيل شحناتك المباشرة لدى ATELIER بكل سهولة.' : 'Track your live orders and shipment dispatch in real time.')

@section('content')
<div class="bg-[#F8F7F3] min-h-screen py-8 sm:py-14">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @auth
        {{-- ══════════════════════════════════════════════════════
             1. AUTHENTICATED USER: IMMEDIATE ORDER DASHBOARD
        ══════════════════════════════════════════════════════ --}}
        <div>
            {{-- Luxury Welcome Header --}}
            <div class="bg-white rounded-3xl border border-black/10 p-6 sm:p-8 mb-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/10">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-black/50">
                                {{ $isArTrack ? 'خدمة المتابعة المباشرة' : 'Live Concierge Dispatch' }}
                            </span>
                        </div>
                        <h1 class="font-display text-2xl sm:text-3xl font-extrabold uppercase tracking-tight text-black">
                            {{ $isArTrack ? 'شحناتك وطلباتك الحالية' : 'Your Live Shipments' }}
                        </h1>
                        <p class="text-xs sm:text-sm text-black/60 mt-1 font-sans">
                            {{ $isArTrack ? 'يتم تحديث مسار ووضع طلباتك تلقائياً لحظة بلحظة.' : 'Your orders are updated in real-time — most recent first.' }}
                        </p>
                    </div>

                    <div class="flex flex-col items-start sm:items-end gap-1 bg-[#FAF9F5] p-3 rounded-2xl border border-black/5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-black/45">
                            {{ $isArTrack ? 'الحساب المسجل' : 'Account' }}
                        </span>
                        <span class="text-xs font-mono font-bold text-black truncate max-w-[200px]">{{ auth()->user()->email }}</span>
                    </div>
                </div>

                {{-- VIP WhatsApp Concierge Bar --}}
                <div class="mt-4 pt-1 flex items-center justify-between flex-wrap gap-3">
                    <span class="text-xs text-black/60 font-medium">
                        {{ $isArTrack ? 'هل ترغب بتعديل العنوان أو موعد التسليم؟' : 'Need to modify your delivery address or schedule?' }}
                    </span>
                    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMsg }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold hover:bg-emerald-100 transition-colors">
                        <svg class="w-3.5 h-3.5 fill-current text-emerald-600" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>{{ $isArTrack ? 'محادثة المساعد الشخصي VIP' : 'VIP Concierge WhatsApp' }}</span>
                    </a>
                </div>
            </div>

            {{-- When User has Orders --}}
            @if(isset($userOrders) && $userOrders->isNotEmpty())
            <div class="space-y-6 mb-12">
                @foreach($userOrders as $order)
                @php
                    $status = strtolower($order->status ?? 'pending');
                    $isDelivered = in_array($status, ['delivered', 'completed']);
                    $isShipped = in_array($status, ['shipped', 'out_for_delivery', 'delivered', 'completed']);
                    $isProcessing = in_array($status, ['processing', 'shipped', 'out_for_delivery', 'delivered', 'completed']);
                    $totalEgp = number_format(($order->total_amount_minor ?? $order->total_price_minor ?? 0) / 100);
                @endphp

                <div class="bg-white rounded-3xl border border-black/10 shadow-sm overflow-hidden transition-all hover:border-black/20" x-data="{ openItems: true }">
                    {{-- Card Header --}}
                    <div class="p-6 sm:p-7 border-b border-black/10 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="font-display font-extrabold text-lg sm:text-xl text-black">#{{ $order->order_number }}</h2>
                                <span class="px-3 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider
                                    {{ $isDelivered ? 'bg-emerald-100 text-emerald-800' : ($isShipped ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                            <p class="text-xs text-black/55 mt-1 font-sans">
                                {{ $order->created_at ? $order->created_at->format('M d, Y • h:i A') : '' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-900 border border-amber-200' }}">
                                {{ $order->payment_status === 'paid' ? ($isArTrack ? 'تم السداد' : 'Paid') : ($isArTrack ? 'دفع عند الاستلام' : 'COD Pending') }}
                            </span>
                            <div class="text-right">
                                <span class="font-display font-extrabold text-lg text-black">{{ $totalEgp }}</span>
                                <span class="text-[10px] font-normal text-black/60 ml-0.5">{{ $order->currency ?: 'EGP' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Dispatch Timeline --}}
                    <div class="p-6 sm:p-7 bg-[#FAF9F5] border-b border-black/10">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-black/60 mb-5">
                            {{ $isArTrack ? 'مراحل خط سير وتجهيز الشحنة' : 'Shipment Progress Timeline' }}
                        </p>
                        <div class="grid grid-cols-4 gap-2 text-center relative">
                            {{-- Step 1 --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs bg-black text-white shadow-sm z-10">
                                    ✓
                                </div>
                                <span class="font-bold text-xs text-black mt-2">{{ $isArTrack ? 'تم استلام الطلب' : 'Confirmed' }}</span>
                                <span class="text-[10px] text-black/50">{{ $order->created_at ? $order->created_at->format('d/m') : '' }}</span>
                            </div>

                            {{-- Step 2 --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs z-10 transition-all {{ $isProcessing ? 'bg-black text-white shadow-sm' : 'bg-black/10 text-black/40' }}">
                                    {{ $isProcessing ? '✓' : '2' }}
                                </div>
                                <span class="font-bold text-xs mt-2 {{ $isProcessing ? 'text-black' : 'text-black/40' }}">{{ $isArTrack ? 'تجهيز وتغليف' : 'Crafting' }}</span>
                                <span class="text-[10px] text-black/50">{{ $isProcessing ? ($isArTrack ? 'جاهز' : 'Ready') : '' }}</span>
                            </div>

                            {{-- Step 3 --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs z-10 transition-all {{ $isShipped ? 'bg-black text-white shadow-sm animate-bounce' : 'bg-black/10 text-black/40' }}">
                                    {{ $isShipped ? '🚚' : '3' }}
                                </div>
                                <span class="font-bold text-xs mt-2 {{ $isShipped ? 'text-black' : 'text-black/40' }}">{{ $isArTrack ? 'مع المندوب' : 'With Courier' }}</span>
                                <span class="text-[10px] text-black/50">{{ $order->tracking_number ? $order->tracking_number : '' }}</span>
                            </div>

                            {{-- Step 4 --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs z-10 transition-all {{ $isDelivered ? 'bg-emerald-600 text-white shadow-sm' : 'bg-black/10 text-black/40' }}">
                                    {{ $isDelivered ? '✓' : '4' }}
                                </div>
                                <span class="font-bold text-xs mt-2 {{ $isDelivered ? 'text-emerald-700 font-extrabold' : 'text-black/40' }}">{{ $isArTrack ? 'تم التسليم' : 'Delivered' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Collapsible Package Items --}}
                    <div>
                        <button type="button" @click="openItems = !openItems" class="w-full p-4 sm:p-5 flex items-center justify-between text-left hover:bg-black/2 transition-colors">
                            <span class="text-xs font-bold uppercase tracking-wider text-black/70 flex items-center gap-2">
                                <span>📦</span>
                                <span>{{ $isArTrack ? 'محتويات وقطع الشحنة' : 'Package Contents' }} ({{ $order->items->count() }})</span>
                            </span>
                            <span class="text-xs text-black/40" x-text="openItems ? '▲ {{ $isArTrack ? 'إخفاء' : 'Collapse' }}' : '▼ {{ $isArTrack ? 'عرض القطع' : 'Expand' }}'"></span>
                        </button>

                        <div x-show="openItems" class="p-5 sm:p-6 border-t border-black/5 space-y-3">
                            @foreach($order->items as $item)
                            @php
                                $itemImg = $item->product?->image_url ?: ($item->variant?->image_url ?: ($item->metadata_json['image_url'] ?? null));
                                $itemPrice = number_format(((int)($item->total_price_minor ?? $item->unit_price_minor ?? 0)) / 100);
                            @endphp
                            <div class="flex items-center justify-between gap-4 p-3 rounded-2xl bg-[#FAF9F5] border border-black/5">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-white border border-black/10 shrink-0 overflow-hidden flex items-center justify-center p-1">
                                        @if($itemImg)
                                        <img src="{{ $itemImg }}" alt="{{ $item->product_title }}" class="w-full h-full object-contain">
                                        @else
                                        <span class="text-black/20 text-base">◆</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-xs sm:text-sm text-black truncate">{{ $item->product_title ?: 'ATELIER Piece' }}</h4>
                                        <p class="text-[11px] text-black/60 mt-0.5">
                                            {{ $isArTrack ? 'الكمية:' : 'Qty:' }} {{ $item->quantity }}
                                            @if(!empty($item->metadata_json['variant_title']))
                                                • {{ $item->metadata_json['variant_title'] }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="font-bold text-xs sm:text-sm text-black shrink-0 font-mono">{{ $itemPrice }} EGP</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @else
            {{-- ── AUTHENTICATED USER HAS NO ORDERS YET ── --}}
            <div class="bg-white rounded-3xl border border-black/10 p-10 sm:p-14 text-center shadow-sm mb-12">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[#FAF9F5] border border-black/10 flex items-center justify-center text-2xl">
                    📦
                </div>
                <h3 class="font-display font-bold text-lg sm:text-xl uppercase tracking-tight text-black mb-2">
                    {{ $isArTrack ? 'ليس لديك أي شحنات أو طلبات جارية حالياً' : 'No Active Shipments Found' }}
                </h3>
                <p class="text-xs sm:text-sm text-black/60 max-w-md mx-auto mb-6 leading-relaxed">
                    {{ $isArTrack ? 'لم تقم بطلب أي منتجات بعد من هذا الحساب. استكشف مجموعتنا الفاخرة واختر ما يناسبك.' : 'You haven\'t placed any orders yet. Discover our latest bespoke arrivals below.' }}
                </p>
                <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="inline-flex items-center gap-2 rounded-xl bg-black text-white font-display text-xs font-bold uppercase tracking-[0.2em] px-6 py-3.5 hover:bg-neutral-800 transition-all shadow-sm">
                    {{ $isArTrack ? 'تصفح كل التشكيلات ←' : 'Browse Boutique →' }}
                </a>
            </div>
            @endif
        </div>

        @else
        {{-- ══════════════════════════════════════════════════════
             2. GUEST USER: CLEAN LOOKUP & LOGIN SHORTCUT
        ══════════════════════════════════════════════════════ --}}
        <div x-data="guestTracker('{{ $autoSearchQuery }}')" x-init="init()" class="mb-12">
            <div class="bg-white rounded-3xl border border-black/10 p-6 sm:p-10 shadow-sm">
                <div class="text-center pb-6 mb-6 border-b border-black/10">
                    <div class="flex items-center justify-center gap-2 mb-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-black/50">
                            {{ $isArTrack ? 'خدمة المتابعة والتتبع المباشر' : 'Live Concierge Dispatch' }}
                        </span>
                    </div>
                    <h1 class="font-display text-2xl sm:text-3xl font-extrabold uppercase tracking-tight text-black">
                        {{ $isArTrack ? 'تتبع شحنتك المباشرة' : 'Live Shipment Tracking' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-black/60 max-w-md mx-auto mt-2">
                        {{ $isArTrack ? 'أدخل رقم الهاتف المسجل أثناء الشراء أو رقم الطلب لعرض خط سير الشحنة فوراً.' : 'Enter your registered phone number or Order ID to instantly locate your delivery.' }}
                    </p>
                </div>

                {{-- Guest Search Form --}}
                <form @submit.prevent="trackOrders()" class="max-w-xl mx-auto space-y-4">
                    <div>
                        <label class="block font-bold text-xs uppercase tracking-wider text-black/80 mb-1.5">
                            {{ $isArTrack ? 'رقم الهاتف (WhatsApp) أو البريد الإلكتروني' : 'Phone Number or Email' }}
                        </label>
                        <div class="relative flex items-center">
                            <input
                                type="text"
                                x-model="query"
                                required
                                placeholder="{{ $isArTrack ? '010XXXXXXXX أو بريدك الإلكتروني' : '010XXXXXXXX or your email address' }}"
                                class="w-full rounded-2xl border border-black/15 bg-[#FAF9F5] p-4 text-xs sm:text-sm text-black placeholder:text-black/35 focus:bg-white focus:border-black focus:ring-2 focus:ring-black/10 focus:outline-none transition-all"
                            >
                            <button type="submit" :disabled="loading" class="absolute right-2 rounded-xl bg-black text-white px-5 py-2.5 text-xs font-display font-bold uppercase tracking-wider hover:bg-neutral-800 disabled:opacity-50 transition-all flex items-center justify-center cursor-pointer shadow-sm">
                                <span x-show="!loading">{{ $isArTrack ? 'تتبع' : 'Track' }}</span>
                                <span x-show="loading" class="flex items-center gap-1.5" style="display: none;">
                                    <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div x-show="errorMessage" x-cloak class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-medium text-rose-800">
                        <span x-text="errorMessage"></span>
                    </div>
                </form>

                {{-- Direct Login Prompt --}}
                <div class="mt-8 pt-6 border-t border-black/10 text-center flex flex-col sm:flex-row items-center justify-center gap-3">
                    <span class="text-xs text-black/60">{{ $isArTrack ? 'لديك حساب بالفعل وتريد رؤية طلباتك تلقائياً دون إدخال بيانات؟' : 'Already have an account and want auto-tracking?' }}</span>
                    <a href="{{ route('login') }}" class="font-bold text-xs text-black underline hover:text-black/70 transition-colors">
                        {{ $isArTrack ? 'تسجيل الدخول المباشر ←' : 'Sign In Now →' }}
                    </a>
                </div>
            </div>

            {{-- Guest Search Results --}}
            <div x-show="orders.length > 0" x-cloak class="mt-8 space-y-6">
                <template x-for="order in orders" :key="order.order_number">
                    <div class="bg-white rounded-3xl border border-black/10 p-6 sm:p-7 shadow-sm">
                        <div class="flex justify-between items-center pb-4 border-b border-black/10">
                            <div>
                                <h3 class="font-display font-extrabold text-lg text-black">#<span x-text="order.order_number"></span></h3>
                                <p class="text-xs text-black/55 mt-0.5" x-text="order.created_at"></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-black text-white" x-text="order.status"></span>
                        </div>
                        <div class="py-4 border-b border-black/10 flex justify-between items-center text-xs">
                            <span class="text-black/60">{{ $isArTrack ? 'إجمالي الطلب:' : 'Order Total:' }}</span>
                            <span class="font-bold text-sm text-black font-mono" x-text="order.total"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        @endauth

        {{-- ══════════════════════════════════════════════════════
             3. LUXURY RECOMMENDATIONS & TRENDING PIECES
             (Always displayed so the customer gets inspired!)
        ══════════════════════════════════════════════════════ --}}
        @if(isset($recommendedCards) && $recommendedCards->isNotEmpty())
        <div class="mt-12 pt-8 border-t border-black/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-black/45 block mb-1">
                        {{ $isArTrack ? 'مختارات فاخرة تليق بك' : 'Curated Recommendations' }}
                    </span>
                    <h2 class="font-display text-xl sm:text-2xl font-extrabold uppercase tracking-tight text-black">
                        {{ $isArTrack ? 'قطع مميزة قد تنال إعجابك' : 'Suggested Luxury Pieces' }}
                    </h2>
                </div>
                <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="text-xs font-bold text-black hover:text-black/60 transition-colors">
                    {{ $isArTrack ? 'عرض جميع القطع ←' : 'View All Pieces →' }}
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($recommendedCards as $card)
                <a href="{{ $card['product_url'] ?? '#' }}" class="group bg-white rounded-2xl border border-black/10 overflow-hidden shadow-sm hover:shadow-md hover:border-black/30 transition-all flex flex-col">
                    <div class="aspect-square bg-[#FAF9F5] p-3 overflow-hidden flex items-center justify-center relative">
                        @if(!empty($card['image']))
                        <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        @else
                        <span class="text-black/20 text-3xl">◆</span>
                        @endif
                        @if(!empty($card['badge_text']))
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-black text-white text-[9px] font-bold uppercase tracking-wider">
                            {{ $card['badge_text'] }}
                        </span>
                        @endif
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-display font-bold text-xs sm:text-sm text-black group-hover:underline truncate">{{ $card['title'] }}</h3>
                            @if(!empty($card['option_title']))
                            <p class="text-[10px] text-black/55 mt-0.5 truncate">{{ $card['option_title'] }}</p>
                            @endif
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-black/5 flex items-center justify-between">
                            <span class="font-bold text-xs sm:text-sm text-black">{{ $card['price_formatted'] }}</span>
                            <span class="text-[10px] font-bold uppercase text-black/50 group-hover:text-black transition-colors">{{ $isArTrack ? 'عرض' : 'View' }} →</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<script>
function guestTracker(initialQuery) {
    return {
        query: initialQuery || '',
        loading: false,
        errorMessage: '',
        orders: [],

        init() {
            if (this.query) {
                this.trackOrders();
            }
        },

        trackOrders() {
            if (!this.query.trim()) return;
            this.loading = true;
            this.errorMessage = '';

            fetch('{{ route("track.order.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ query: this.query.trim() }),
            })
            .then(r => r.json().then(data => ({ status: r.status, body: data })))
            .then(({ status, body }) => {
                this.loading = false;
                if (status >= 200 && status < 300 && body.success) {
                    this.orders = body.orders || [];
                } else {
                    this.errorMessage = body.message || '{{ $isArTrack ? "لم يتم العثور على شحنات مسجلة بهذا الرقم." : "No shipments found." }}';
                    this.orders = [];
                }
            })
            .catch(() => {
                this.loading = false;
                this.errorMessage = '{{ $isArTrack ? "حدث خطأ في الاتصال، يرجى المحاولة مرة أخرى." : "Network error, please try again." }}';
            });
        }
    };
}
</script>
@endsection
