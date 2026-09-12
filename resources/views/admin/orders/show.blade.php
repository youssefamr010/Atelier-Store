@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="max-w-5xl space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b-2 border-black pb-4">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black mb-1 block">
                ← Back to All Orders
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black uppercase tracking-tight text-black font-mono">Order #{{ $order->order_number }}</h1>
                @php
                    $badgeClass = match($order->status) {
                        'delivered'  => 'bg-green-100 text-green-800 border-green-600',
                        'shipped'    => 'bg-blue-100 text-blue-800 border-blue-600',
                        'processing' => 'bg-purple-100 text-purple-800 border-purple-600',
                        'cancelled'  => 'bg-red-100 text-red-800 border-red-600',
                        default      => 'bg-amber-100 text-amber-800 border-amber-600',
                    };
                @endphp
                <span class="text-xs font-bold uppercase px-2.5 py-0.5 border {{ $badgeClass }}">
                    {{ $order->status }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Placed on {{ $order->created_at->format('l, F d, Y \a\t H:i') }}</p>
        </div>
    </div>

    <!-- Status Update Action Card -->
    <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
        <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2 mb-4">
            Order Fulfillment Status
        </h2>
        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Fulfillment Status</label>
                <select name="status" class="w-full border-2 border-black p-2.5 text-xs bg-white font-bold focus:outline-none">
                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pending (Awaiting Dispatch)</option>
                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Processing (In Packing)</option>
                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg> Shipped (Out for Delivery)</option>
                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Delivered (Completed)</option>
                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}><svg class="w-3.5 h-3.5 inline-block text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Payment Status</label>
                <select name="payment_status" class="w-full border-2 border-black p-2.5 text-xs bg-white font-bold focus:outline-none">
                    <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending (COD / Unpaid)</option>
                    <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid (Collected / Confirmed)</option>
                    <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-black text-white py-2.5 px-4 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    Update Order Status →
                </button>
            </div>
        </form>
    </div>

    <!-- Order Items & Customer Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Line Items (2 Cols) -->
        <div class="lg:col-span-2 bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">
                Ordered Items ({{ $order->items->count() }})
            </h2>

            <div class="divide-y divide-gray-200">
                @foreach($order->items as $item)
                    @php
                        $p = $item->product;
                        $img = $p ? $p->image_url : null;
                        $imgUrl = $img ? (str_starts_with($img, 'http') ? $img : url($img)) : 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=200';
                    @endphp
                    <div class="py-4 flex items-center gap-4">
                        <div class="w-16 h-16 border border-black bg-gray-100 overflow-hidden shrink-0">
                            <img src="{{ $imgUrl }}" alt="{{ $item->product_title }}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-sm uppercase tracking-tight text-black truncate">
                                {{ $item->product_title }}
                            </h3>
                            <div class="text-[10px] text-gray-500 font-mono mt-0.5">
                                SKU: {{ $item->sku }}
                                @if($item->variant_title) · Color/Variant: <strong>{{ $item->variant_title }}</strong> @endif
                            </div>
                            <div class="text-xs text-gray-600 mt-1">
                                Quantity: <strong class="text-black font-mono">{{ $item->quantity }}</strong> × {{ number_format($item->unit_price_minor / 100, 0) }} EGP
                            </div>
                        </div>
                        <div class="text-right font-mono font-black text-sm text-black">
                            {{ number_format(($item->unit_price_minor * $item->quantity) / 100, 0) }} EGP
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Totals Summary Box -->
            <div class="border-t-2 border-black pt-4 space-y-2 text-xs font-mono">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>{{ number_format($order->subtotal_minor / 100, 0) }} EGP</span>
                </div>
                @if($order->discount_minor > 0)
                    <div class="flex justify-between text-green-700 font-bold">
                        <span>Discount Promo</span>
                        <span>-{{ number_format($order->discount_minor / 100, 0) }} EGP</span>
                    </div>
                @endif
                <div class="flex justify-between text-gray-600">
                    <span>Shipping</span>
                    <span>{{ number_format($order->shipping_minor / 100, 0) }} EGP</span>
                </div>
                @if($order->cod_surcharge_minor > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>Cash on Delivery Surcharge</span>
                        <span>+{{ number_format($order->cod_surcharge_minor / 100, 0) }} EGP</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-black text-black border-t border-black pt-2">
                    <span class="font-sans uppercase">Grand Total</span>
                    <span>{{ number_format($order->total_price_minor / 100, 0) }} EGP</span>
                </div>
            </div>
        </div>

        <!-- Customer & Shipping Card (1 Col) -->
        <div class="space-y-6">
            
            <!-- Customer Details -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-3">
                <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">
                    Customer Info
                </h2>
                <div>
                    <span class="block text-sm font-black uppercase text-black">{{ $order->customer_name ?: 'Guest Connoisseur' }}</span>
                    <span class="block text-xs text-gray-600 font-mono mt-0.5">{{ $order->customer_email }}</span>
                    @if($order->customer_phone)
                        <span class="block text-xs font-bold text-black font-mono mt-1"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg> {{ $order->customer_phone }}</span>
                        
                        @php
                            $waService = app(\App\Services\WhatsAppNotificationService::class);
                            $waDirectUrl = $waService->generateWhatsAppDirectUrl(
                                $order->customer_phone, 
                                "مرحباً {$order->customer_name} ⭐\nبخصوص طلبك رقم #{$order->order_number} من ATELIER Studio Egypt..."
                            );
                        @endphp
                        <div class="pt-2">
                            <a 
                                href="{{ $waDirectUrl }}" 
                                target="_blank" 
                                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs uppercase px-3 py-1.5 border border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-colors"
                            >
                                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg></span>
                                <span>مراسلة واتساب فورية</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-3">
                <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">
                    Delivery Address
                </h2>
                @php 
                    $addr = $order->shipping_address_json ?? []; 
                    $metaShipping = $order->metadata_json['shipping_address'] ?? [];
                    $lat = $metaShipping['latitude'] ?? ($addr['latitude'] ?? null);
                    $lng = $metaShipping['longitude'] ?? ($addr['longitude'] ?? null);
                @endphp
                <div class="text-xs space-y-1 text-gray-800">
                    <p class="font-bold text-black">{{ $addr['full_name'] ?? ($metaShipping['name'] ?? ($order->customer_name ?? '')) }}</p>
                    <p>{{ $addr['street_address'] ?? ($metaShipping['street'] ?? ($order->shipping_address ?? 'Not specified')) }}</p>
                    @if(isset($addr['apartment'])) <p>Apt/Suite: {{ $addr['apartment'] }}</p> @endif
                    <p>{{ $addr['city'] ?? ($metaShipping['city'] ?? '') }} {{ isset($addr['state']) ? '· ' . $addr['state'] : (isset($metaShipping['state']) ? '· ' . $metaShipping['state'] : '') }}</p>
                    @if(isset($addr['phone']) || isset($metaShipping['phone'])) 
                        <p class="font-mono text-gray-600">Phone: {{ $addr['phone'] ?? $metaShipping['phone'] }}</p> 
                    @endif

                    @if($lat && $lng)
                        <div class="pt-3">
                            <a 
                                href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" 
                                target="_blank" 
                                class="inline-flex items-center gap-1.5 text-xs font-mono font-bold bg-black text-white px-3 py-1.5 hover:bg-gray-800 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"
                            >
                                <span><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> فتح موقع العميل على الخريطة ({{ number_format($lat, 4) }}, {{ number_format($lng, 4) }}) ↗</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-3">
                <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-500 border-b pb-2">
                    Payment Architecture
                </h2>
                <div class="text-xs space-y-1">
                    <p><span class="text-gray-500">Method:</span> <strong class="uppercase font-mono">{{ $order->payment_method ?? 'COD' }}</strong></p>
                    <p><span class="text-gray-500">Status:</span> <strong class="capitalize">{{ $order->payment_status ?? 'pending' }}</strong></p>
                    <p><span class="text-gray-500">Currency:</span> <strong class="font-mono">{{ $order->currency ?? 'EGP' }}</strong></p>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
