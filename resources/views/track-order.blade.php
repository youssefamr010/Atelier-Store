@extends('layouts.app')

@php
    $isArTrack = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $whatsappNumber = preg_replace('/[^0-9]/', '', $settings['social_whatsapp'] ?? '201000000000');
    $whatsappMsg = urlencode($settings['social_whatsapp_msg'] ?? ($isArTrack ? 'مرحباً، أود الاستفسار عن طلبي في Atelier' : 'Hello, I would like to inquire about my Atelier order'));
@endphp

@section('title', ($isArTrack ? 'تتبع مسار الشحنات — ' : 'Track Shipments — ') . ($settings['storeName'] ?? 'ATELIER'))

@section('content')
<div class="bg-[#F5F5F0] min-h-screen py-12 lg:py-20">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">

        @auth
        {{-- ══════════════════════════════════════════════════════
             AUTHENTICATED FLOW: Show orders automatically
        ══════════════════════════════════════════════════════ --}}
        <div x-data="authenticatedTracker()" x-init="init()">

            {{-- Page Header --}}
            <div class="border-2 border-black bg-white p-6 sm:p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black pb-5 mb-5">
                    <div>
                        <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black/60 block mb-1">
                            {{ $isArTrack ? 'خدمة التتبع المباشر' : 'CONCIERGE DISPATCH' }}
                        </span>
                        <h1 class="font-editorial font-black text-2xl sm:text-3xl uppercase tracking-normal text-black">
                            {{ $isArTrack ? 'شحناتك المباشرة' : 'Your Live Shipments' }}
                        </h1>
                        <p class="text-xs text-black/60 mt-1 font-sans">
                            {{ $isArTrack ? 'جميع طلباتك مرتبة بالأحدث أولاً' : 'All your orders — most recent first' }}
                        </p>
                    </div>
                    <div class="flex flex-col items-start sm:items-end gap-1.5">
                        <span class="text-[10px] font-editorial font-bold uppercase tracking-wider text-black/50">
                            {{ $isArTrack ? 'مسجل كـ' : 'Signed in as' }}
                        </span>
                        <span class="text-xs font-mono font-bold text-black">{{ auth()->user()->email }}</span>
                    </div>
                </div>

                {{-- WhatsApp CTA --}}
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMsg }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-xs font-editorial font-bold uppercase tracking-wider text-black hover:opacity-75 transition-opacity">
                    <svg class="w-4 h-4 fill-current text-emerald-600" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>{{ $isArTrack ? 'تحدث مع خدمة عملاء VIP عبر واتساب' : 'Chat with VIP Concierge on WhatsApp' }}</span>
                </a>
            </div>

            {{-- Loading State --}}
            <template x-if="loading">
                <div class="space-y-4">
                    @for($i = 0; $i < 3; $i++)
                    <div class="border-2 border-black/10 bg-white p-6 animate-pulse">
                        <div class="flex justify-between mb-4">
                            <div class="h-5 w-32 bg-black/10 rounded"></div>
                            <div class="h-5 w-20 bg-black/10 rounded"></div>
                        </div>
                        <div class="h-3 w-48 bg-black/5 rounded mb-2"></div>
                        <div class="h-3 w-36 bg-black/5 rounded"></div>
                    </div>
                    @endfor
                </div>
            </template>

            {{-- No Orders State --}}
            <template x-if="!loading && orders.length === 0">
                <div class="text-center py-16 bg-white border-2 border-dashed border-black/20">
                    <svg class="w-12 h-12 mx-auto mb-4 text-black/25" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m8-4v8"/>
                    </svg>
                    <p class="font-editorial text-sm uppercase tracking-wider text-black/60 mb-4">
                        {{ $isArTrack ? 'لا توجد طلبات بعد' : 'No orders placed yet.' }}
                    </p>
                    <a href="{{ route('collections.show', ['slug' => 'all']) }}" class="btn-luxury inline-block px-6 py-3 text-xs tracking-wider">
                        {{ $isArTrack ? 'تصفح المتجر ←' : 'Explore Boutique →' }}
                    </a>
                </div>
            </template>

            {{-- Orders List --}}
            <template x-if="!loading && orders.length > 0">
                <div class="space-y-5">
                    <template x-for="order in orders" :key="order.order_number">
                        <div class="border-2 border-black bg-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">

                            {{-- Order Header --}}
                            <div class="p-5 sm:p-6 border-b border-black/10 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                                <div>
                                    <h2 class="font-editorial font-black text-lg uppercase text-black">#<span x-text="order.order_number"></span></h2>
                                    <p class="text-[11px] text-black/55 mt-0.5 font-sans" x-text="order.created_at"></p>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2.5 py-0.5 text-[9px] font-editorial font-bold uppercase tracking-wider border"
                                          :class="statusClass(order.status)" x-text="order.status"></span>
                                    <span class="px-2.5 py-0.5 text-[9px] font-editorial font-bold uppercase tracking-wider border"
                                          :class="order.payment_status === 'paid' ? 'bg-green-50 text-green-800 border-green-600' : 'bg-amber-50 text-amber-800 border-amber-600'"
                                          x-text="order.payment_status"></span>
                                    <span class="font-editorial font-black text-base text-black font-mono" x-text="order.total"></span>
                                </div>
                            </div>

                            {{-- Dispatch Timeline --}}
                            <div class="p-5 sm:p-6 bg-[#FAFAFA] border-b border-black/10">
                                <p class="font-editorial font-bold text-[10px] uppercase tracking-wider text-black/55 mb-4">
                                    {{ $isArTrack ? 'مراحل الشحنة' : 'Dispatch Timeline' }}
                                </p>
                                <div class="flex items-start gap-0">
                                    <template x-for="(step, idx) in buildTimeline(order.status)" :key="idx">
                                        <div class="flex-1 flex flex-col items-center">
                                            <div class="relative w-full flex items-center">
                                                <div class="flex-1" :class="idx === 0 ? 'invisible' : ''">
                                                    <div class="h-0.5 w-full" :class="step.active ? 'bg-black' : 'bg-black/15'"></div>
                                                </div>
                                                <div class="w-4 h-4 rounded-full border-2 shrink-0 transition-all duration-300 z-10"
                                                     :class="step.active ? 'bg-black border-black ring-4 ring-black/10' : 'bg-white border-black/25'">
                                                </div>
                                                <div class="flex-1" :class="idx === 3 ? 'invisible' : ''">
                                                    <div class="h-0.5 w-full" :class="step.nextActive ? 'bg-black' : 'bg-black/15'"></div>
                                                </div>
                                            </div>
                                            <p class="text-[9px] font-bold text-center mt-1.5 leading-tight px-0.5"
                                               :class="step.active ? 'text-black' : 'text-black/35'" x-text="step.label"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Order Items (collapsed by default, expandable) --}}
                            <div x-data="{ expanded: false }">
                                <button type="button" @click="expanded = !expanded"
                                        class="w-full p-4 text-left flex items-center justify-between border-b border-black/10 hover:bg-black/2 transition-colors">
                                    <span class="font-editorial font-bold text-[10px] uppercase tracking-wider text-black/60">
                                        {{ $isArTrack ? 'محتويات الشحنة' : 'Package Contents' }}
                                        (<span x-text="order.items.length"></span>)
                                    </span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="p-4 sm:p-5 space-y-3">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="flex items-center justify-between gap-3 text-xs border border-black/10 p-3 bg-[#FAFAFA]">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <img x-show="item.image_url" :src="item.image_url" alt="" class="w-12 h-12 object-contain bg-white border border-black/10 shrink-0" loading="lazy">
                                                <div class="min-w-0">
                                                    <p class="font-bold text-black truncate" x-text="item.quantity + 'x ' + item.title"></p>
                                                    <p x-show="item.variant" class="text-[10px] text-black/55 mt-0.5" x-text="item.variant"></p>
                                                </div>
                                            </div>
                                            <span class="font-bold text-black shrink-0 font-mono" x-text="item.price"></span>
                                        </div>
                                    </template>
                                    <div class="flex items-center justify-between pt-2 border-t border-black/10">
                                        <span class="font-editorial font-bold text-xs uppercase tracking-wider text-black/60">{{ $isArTrack ? 'الإجمالي' : 'Order Total' }}</span>
                                        <span class="font-editorial font-black text-lg text-black font-mono" x-text="order.total"></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
            </template>

        </div>

        @else
        {{-- ══════════════════════════════════════════════════════
             GUEST FLOW: Search by phone / email
        ══════════════════════════════════════════════════════ --}}
        <div x-data="orderTracker()">

            <div class="border-2 border-black bg-white p-8 sm:p-12 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
                <div class="border-b border-black pb-6 mb-8 text-center">
                    <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black/60 block mb-1">
                        {{ $isArTrack ? 'خدمة التتبع والشحن السريع' : 'CONCIERGE DISPATCH' }}
                    </span>
                    <h1 class="font-editorial font-black text-2xl sm:text-3xl uppercase tracking-normal text-black">
                        {{ $isArTrack ? 'تتبع شحناتك المباشر' : 'Live Shipment Tracking' }}
                    </h1>
                    <p class="font-sans text-xs text-black/70 mt-2">
                        {{ $isArTrack ? 'أدخل رقم هاتفك المسجل أو بريدك الإلكتروني لعرض ومتابعة جميع شحناتك في خطوة واحدة.' : 'Enter your registered phone number or email to view and track all your shipments.' }}
                    </p>
                </div>

                <form @submit.prevent="trackOrders()" class="space-y-4">
                    <div>
                        <label class="block font-editorial font-bold text-[10px] uppercase tracking-wider text-black mb-1.5">
                            {{ $isArTrack ? 'رقم الهاتف المسجل أو البريد الإلكتروني' : 'Registered Phone Number or Email' }}
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="query"
                                required
                                placeholder="{{ $isArTrack ? '010XXXXXXXX أو بريدك الإلكتروني' : '010XXXXXXXX or your email address' }}"
                                class="w-full border-2 border-black p-3.5 text-xs bg-[#F5F5F0] focus:bg-white focus:outline-none font-sans font-medium transition-colors"
                            >
                            <button type="submit" :disabled="loading" class="absolute right-2 top-2 bottom-2 bg-black text-white px-4 text-xs font-editorial font-bold uppercase tracking-wider hover:bg-neutral-800 disabled:opacity-50 transition-colors flex items-center justify-center">
                                <span x-show="!loading">{{ $isArTrack ? 'تتبع' : 'Track' }}</span>
                                <span x-show="loading" x-cloak>...</span>
                            </button>
                        </div>
                        <p class="text-[10px] font-sans text-black/50 mt-1.5">
                            {{ $isArTrack ? 'لا يشترط تذكر رقم الطلب — يكفي كتابة رقم هاتفك المسجل أثناء الشراء.' : 'No Order ID required — just enter the phone number you used during checkout.' }}
                        </p>
                    </div>

                    <div x-show="errorMessage" x-cloak class="bg-red-50 border border-red-300 text-red-800 p-3.5 text-xs font-medium">
                        <span x-text="errorMessage"></span>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-black/10 text-center">
                    <p class="text-[11px] text-black/60 mb-2">
                        {{ $isArTrack ? 'هل تحتاج لمساعدة فورية؟' : 'Need instant assistance or address modification?' }}
                    </p>
                    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMsg }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 text-xs font-editorial font-bold uppercase tracking-wider text-black hover:opacity-75 transition-opacity">
                        <svg class="w-4 h-4 fill-current text-emerald-600" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>{{ $isArTrack ? 'تحدث مع خدمة عملاء VIP عبر واتساب' : 'Chat with VIP Concierge on WhatsApp' }}</span>
                    </a>
                </div>
            </div>

            {{-- Guest: Multiple Orders Selector --}}
            <div x-show="orders.length > 1" x-cloak class="mt-8 space-y-3">
                <h3 class="font-editorial font-bold text-xs uppercase tracking-wider text-black/70">
                    {{ $isArTrack ? 'الطلبات المسجلة (' : 'Found Shipments (' }}<span x-text="orders.length"></span>{{ $isArTrack ? ')' : ')' }}
                </h3>
                <div class="space-y-2.5">
                    <template x-for="(oItem, oIdx) in orders" :key="oItem.order_number">
                        <div
                            @click="selectOrder(oItem)"
                            class="p-4 border-2 border-black bg-white cursor-pointer transition-all hover:-translate-y-0.5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between"
                            :class="selectedOrder?.order_number === oItem.order_number ? 'ring-2 ring-black bg-[#FBFBFA]' : ''"
                        >
                            <div>
                                <span class="font-editorial font-black text-sm uppercase text-black">#<span x-text="oItem.order_number"></span></span>
                                <span class="text-[10px] font-sans text-black/50 block" x-text="oItem.created_at"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 text-[9px] font-editorial font-bold uppercase tracking-wider border" :class="statusClass(oItem.status)">
                                    <span x-text="oItem.status"></span>
                                </span>
                                <span class="font-editorial font-bold text-xs text-black" x-text="oItem.total"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Guest: Active Order Detailed Card --}}
            <div x-show="selectedOrder" x-cloak class="mt-8 border-2 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6 sm:p-8 border-b border-black">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                        <div>
                            <span class="font-editorial font-bold text-[10px] uppercase tracking-[0.2em] text-black/50 block mb-1">{{ $isArTrack ? 'بيانات الشحنة' : 'Shipment Details' }}</span>
                            <h2 class="font-editorial font-black text-xl uppercase tracking-tight text-black">#<span x-text="selectedOrder?.order_number"></span></h2>
                            <p class="text-xs text-black/60 mt-1" x-text="selectedOrder?.created_at"></p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="px-3 py-1 text-[10px] font-editorial font-bold uppercase tracking-wider border" :class="statusClass(selectedOrder?.status)">
                                <span x-text="selectedOrder?.status"></span>
                            </span>
                            <span class="px-3 py-1 text-[10px] font-editorial font-bold uppercase tracking-wider border"
                                  :class="selectedOrder?.payment_status === 'paid' ? 'bg-green-50 text-green-800 border-green-600' : 'bg-amber-50 text-amber-800 border-amber-600'">
                                <span x-text="selectedOrder?.payment_status"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8 border-b border-black/10 bg-[#FAFAFA]">
                    <h3 class="font-editorial font-bold text-xs uppercase tracking-wider text-black/60 mb-5">{{ $isArTrack ? 'مراحل خط سير الشحنة' : 'Dispatch Timeline' }}</h3>
                    <div class="space-y-4">
                        <template x-for="(step, idx) in timeline" :key="idx">
                            <div class="flex items-start gap-3.5">
                                <div class="flex flex-col items-center">
                                    <div class="w-3.5 h-3.5 rounded-full border-2 transition-colors"
                                         :class="step.active ? 'bg-black border-black ring-4 ring-black/10' : 'bg-white border-black/30'"></div>
                                    <div x-show="idx < timeline.length - 1" class="w-0.5 h-7 bg-black/20 mt-1"></div>
                                </div>
                                <div class="pb-1">
                                    <p class="text-xs font-bold text-black" :class="{ 'text-black/40': !step.active }" x-text="step.label"></p>
                                    <p class="text-[10px] text-black/60 mt-0.5" x-text="step.description"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="p-6 sm:p-8 border-b border-black/10">
                    <h3 class="font-editorial font-bold text-xs uppercase tracking-wider text-black/60 mb-4">{{ $isArTrack ? 'محتويات الشحنة' : 'Package Contents' }}</h3>
                    <div class="space-y-3">
                        <template x-for="item in selectedOrder?.items" :key="item.id">
                            <div class="flex items-center justify-between gap-3 text-xs text-black/80 border border-black/10 p-3.5 bg-[#FAFAFA]">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img x-show="item.image_url" :src="item.image_url" alt="" class="w-12 h-12 object-contain bg-white border border-black/10 shrink-0" loading="lazy">
                                    <div class="min-w-0">
                                        <p class="font-bold text-black truncate" x-text="item.quantity + 'x ' + item.title"></p>
                                        <p x-show="item.variant" class="text-[10px] text-black/55 mt-0.5" x-text="item.variant"></p>
                                    </div>
                                </div>
                                <span class="font-bold text-black shrink-0 font-mono" x-text="item.price"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="p-6 sm:p-8 flex items-center justify-between bg-white">
                    <span class="font-editorial font-bold text-xs uppercase tracking-wider text-black/60">{{ $isArTrack ? 'إجمالي الطلب' : 'Total Amount' }}</span>
                    <span class="font-editorial font-black text-xl text-black font-mono" x-text="selectedOrder?.total"></span>
                </div>
            </div>

        </div>
        @endauth

    </div>
</div>

<script>
// ─── Authenticated User Tracker ───────────────────────────────────────────
function authenticatedTracker() {
    return {
        loading: true,
        orders: [],

        init() {
            @auth
            // Fetch this user's own orders server-side via the existing search endpoint
            // using their verified email — guests cannot reach this branch
            fetch('{{ route("track.order.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ query: '{{ auth()->user()->email }}' }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.orders = data.orders || [];
                } else {
                    this.orders = [];
                }
            })
            .catch(() => { this.orders = []; })
            .finally(() => { this.loading = false; });
            @endauth
        },

        buildTimeline(status) {
            const steps = [
                { key: 'pending',    label: '{{ $isArTrack ? "تأكيد الطلب" : "Confirmed" }}',     nextActive: false },
                { key: 'processing', label: '{{ $isArTrack ? "قيد التجهيز" : "Preparing" }}',     nextActive: false },
                { key: 'shipped',    label: '{{ $isArTrack ? "في الطريق" : "Shipped" }}',          nextActive: false },
                { key: 'delivered',  label: '{{ $isArTrack ? "تم التسليم" : "Delivered" }}',       nextActive: false },
            ];
            const order = ['pending', 'processing', 'shipped', 'delivered'];
            const idx = order.indexOf(status);
            return steps.map((s, i) => ({
                ...s,
                active: i <= (idx >= 0 ? idx : 0),
                nextActive: (i + 1) <= (idx >= 0 ? idx : 0),
            }));
        },

        statusClass(status) {
            const c = {
                delivered:  'bg-green-50 text-green-800 border-green-600',
                shipped:    'bg-blue-50 text-blue-800 border-blue-600',
                processing: 'bg-purple-50 text-purple-800 border-purple-600',
                cancelled:  'bg-red-50 text-red-800 border-red-600',
            };
            return c[status] || 'bg-amber-50 text-amber-800 border-amber-600';
        },
    };
}

// ─── Guest Order Tracker (unchanged logic) ───────────────────────────────
function orderTracker() {
    const params = new URLSearchParams(window.location.search);
    return {
        query: params.get('order') || params.get('phone') || '',
        loading: false,
        errorMessage: '',
        orders: [],
        selectedOrder: null,
        timeline: [],

        init() {
            if (this.query) this.trackOrders();
        },

        async trackOrders() {
            if (!this.query.trim()) return;
            this.loading = true;
            this.errorMessage = '';
            this.orders = [];
            this.selectedOrder = null;

            try {
                const res = await fetch('{{ route("track.order.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ query: this.query.trim() }),
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    this.errorMessage = data.message || 'No orders found for this information.';
                    return;
                }

                this.orders = data.orders || [data.order];
                if (this.orders.length > 0) this.selectOrder(this.orders[0]);
            } catch (e) {
                this.errorMessage = 'Network connection error. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        selectOrder(order) {
            this.selectedOrder = order;
            this.timeline = this.buildTimeline(order.status);
        },

        buildTimeline(status) {
            const steps = [
                { key: 'pending',    label: '{{ $isArTrack ? "تأكيد الطلب" : "Order Confirmed" }}',    description: '{{ $isArTrack ? "استلمنا طلبك وجاري فحصه." : "We received your order and are inspecting the items." }}' },
                { key: 'processing', label: '{{ $isArTrack ? "قيد التجهيز" : "In Preparation" }}',     description: '{{ $isArTrack ? "جاري التغليف والتجهيز للشحن." : "Carefully packaged and prepared for courier dispatch." }}' },
                { key: 'shipped',    label: '{{ $isArTrack ? "خرج للتوصيل" : "Out for Delivery" }}',   description: '{{ $isArTrack ? "المندوب في طريقه إليك." : "Courier is en route to your shipping destination." }}' },
                { key: 'delivered',  label: '{{ $isArTrack ? "تم التسليم" : "Delivered" }}',            description: '{{ $isArTrack ? "تم تسليم الشحنة بنجاح." : "Shipment successfully handed to client." }}' },
            ];
            const statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
            const currentIdx = statusOrder.indexOf(status);
            return steps.map((step, idx) => ({ ...step, active: idx <= (currentIdx >= 0 ? currentIdx : 0) }));
        },

        statusClass(status) {
            const c = {
                delivered:  'bg-green-50 text-green-800 border-green-600',
                shipped:    'bg-blue-50 text-blue-800 border-blue-600',
                processing: 'bg-purple-50 text-purple-800 border-purple-600',
                cancelled:  'bg-red-50 text-red-800 border-red-600',
            };
            return c[status] || 'bg-amber-50 text-amber-800 border-amber-600';
        },
    };
}
</script>
@endsection
