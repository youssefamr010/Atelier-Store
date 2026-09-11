@extends('layouts.admin')

@section('title', 'Executive Dashboard')

@section('content')
<div class="space-y-8">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b-2 border-black pb-4">
        <div>
            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500">EXECUTIVE OVERVIEW</span>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">Store Dashboard</h1>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.products.create') }}" class="bg-black text-white px-3.5 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-transform active:translate-y-0.5">
                + Add Product
            </a>
            <a href="{{ route('admin.content.index') }}" class="border-2 border-black bg-white px-3.5 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                <span>Social & WhatsApp</span>
            </a>
            <a href="{{ route('admin.content.index') }}" class="border-2 border-black bg-white px-3.5 py-2 text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Site Settings</span>
            </a>
        </div>
    </div>

    <!-- Live presence: small, useful and privacy-conscious. Refreshes with the dashboard. -->
    <div class="rounded-2xl border border-emerald-300 bg-emerald-50/70 p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <div><p class="text-[10px] font-bold uppercase tracking-[.16em] text-emerald-800">Live right now</p><h2 class="text-lg font-black text-black">{{ $onlineVisitors }} visitor{{ $onlineVisitors === 1 ? '' : 's' }} browsing the store</h2></div>
            </div>
            <span class="text-[10px] text-emerald-800/75">Active in the last 90 seconds · refresh this page for the latest list</span>
        </div>
        <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="rounded-xl bg-white/80 border border-emerald-200 p-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-black/50">Admins online ({{ $onlineAdmins->count() }})</p>
                <p class="mt-1 text-xs font-semibold text-black">{{ $onlineAdmins->pluck('display_name')->filter()->implode(' · ') ?: 'No admin is active' }}</p>
            </div>
            <div class="rounded-xl bg-white/80 border border-emerald-200 p-3 lg:col-span-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-black/50 mb-2">Live Visitors Feed (Active in last 90s)</p>
                @php 
                    $allStorefront = \App\Models\UserPresence::where('last_seen_at', '>=', now()->subSeconds(90))
                        ->where('area', 'storefront')->latest('last_seen_at')->get();
                @endphp
                <div class="max-h-32 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                    @forelse($allStorefront as $presence)
                        <div class="flex items-center justify-between bg-white border border-emerald-100 p-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-bold text-black">{{ $presence->display_name ?: 'Guest ' . substr($presence->session_key, 0, 4) }}</span>
                            </div>
                            <span class="font-mono text-[9px] text-gray-500 bg-gray-50 px-1.5 py-0.5 border">
                                {{ $presence->page_path }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No visitors currently active.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Revenue Today -->
        <div class="glass-panel widget-animated p-6">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-widest">Revenue Today</span>
                <span class="text-lg"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg></span>
            </div>
            <div class="text-2xl sm:text-3xl font-black tracking-tight text-black font-mono">
                {{ number_format($revenueToday / 100, 0) }} <span class="text-xs font-sans font-bold">EGP</span>
            </div>
            <div class="text-[11px] text-gray-500 mt-2 font-medium">
                This Month: <strong class="text-black font-mono">{{ number_format($revenueMonth / 100, 0) }} EGP</strong>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="glass-panel widget-animated p-6">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-widest">Orders</span>
                <span class="text-lg"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></span>
            </div>
            <div class="text-2xl sm:text-3xl font-black tracking-tight text-black font-mono">
                {{ $totalOrders }}
            </div>
            <div class="text-[11px] text-gray-500 mt-2 font-medium">
                <span class="inline-block w-2 h-2 rounded-full {{ $pendingOrders > 0 ? 'bg-amber-500 animate-pulse' : 'bg-green-500' }} mr-1"></span>
                <strong>{{ $pendingOrders }}</strong> pending action
            </div>
        </div>

        <!-- Active Inventory -->
        <div class="glass-panel widget-animated p-6">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-widest">Products</span>
                <span class="text-lg"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg></span>
            </div>
            <div class="text-2xl sm:text-3xl font-black tracking-tight text-black font-mono">
                {{ $totalProducts }}
            </div>
            <div class="text-[11px] text-gray-500 mt-2 font-medium">
                @if($lowStockCount > 0)
                    <span class="text-red-600 font-bold"><svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> {{ $lowStockCount }} low stock alerts</span>
                @else
                    <span class="text-green-600 font-bold">✓ Inventory healthy</span>
                @endif
            </div>
        </div>

        <!-- Visitors & Traffic -->
        <div class="glass-panel widget-animated p-6">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-widest">Today's Traffic</span>
                <span class="text-lg"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg></span>
            </div>
            <div class="text-2xl sm:text-3xl font-black tracking-tight text-black font-mono">
                {{ $visitorsToday }} <span class="text-xs font-sans font-bold text-gray-500">uniques</span>
            </div>
            <div class="text-[11px] text-gray-500 mt-2 font-medium">
                <strong>{{ $viewsToday }}</strong> total page views today
            </div>
        </div>

    </div>

    <!-- ─── Interactive Sales Charts (Chart.js) ────────────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- 30-Day Revenue Line Chart (2 Cols) -->
        <div class="lg:col-span-2 bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="flex items-center justify-between border-b pb-2">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">FINANCIAL TRAJECTORY</span>
                    <h2 class="text-base font-black uppercase tracking-tight text-black">Revenue Trend (Last 30 Days)</h2>
                </div>
                <span class="text-xs font-mono font-bold text-gray-600">Daily EGP</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Best-Selling Products Bar Chart (1 Col) -->
        <div class="glass-panel widget-animated p-6">
            <div class="border-b pb-2">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">DEMAND VELOCITY</span>
                <h2 class="text-base font-black uppercase tracking-tight text-black">Top Selling Pieces</h2>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="bestsellersChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Low Stock Alert Box (Conditional) -->
    @if($lowStockProducts->count() > 0)
        <div class="bg-amber-50 border-2 border-amber-600 p-5 shadow-[4px_4px_0px_0px_rgba(217,119,6,0.5)]">
            <div class="flex items-center justify-between mb-3 border-b border-amber-300 pb-2">
                <div class="flex items-center gap-2">
                    <span class="text-lg"><svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg></span>
                    <h3 class="font-black text-sm uppercase tracking-wider text-amber-900">Low Stock Alert ({{ $lowStockProducts->count() }} items ≤ 5 units)</h3>
                </div>
                <a href="{{ route('admin.inventory.index') }}" class="text-xs font-bold uppercase underline text-amber-900 hover:text-black">Inventory Manager →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lowStockProducts->take(6) as $lowP)
                    <div class="flex items-center justify-between bg-white border border-amber-400 p-2.5 text-xs">
                        <div class="truncate mr-2">
                            <span class="font-bold text-black block truncate">{{ $lowP->title }}</span>
                            <span class="text-[10px] font-mono text-gray-500">SKU: {{ $lowP->sku }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-block font-mono font-bold px-2 py-0.5 {{ $lowP->inventory === 0 ? 'bg-red-600 text-white' : 'bg-amber-100 text-amber-900 border border-amber-400' }}">
                                {{ $lowP->inventory }} left
                            </span>
                            <a href="{{ route('admin.products.edit', $lowP->id) }}" class="block text-[10px] text-black underline font-bold mt-1">Edit</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Orders Section -->
    <div class="glass-panel widget-animated p-6">
        <div class="flex items-center justify-between border-b-2 border-black pb-4 mb-4">
            <div>
                <h2 class="text-xl font-black uppercase tracking-tight text-black">Recent Orders</h2>
                <p class="text-xs text-gray-500 mt-0.5">Latest transactions and dispatch updates</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="border border-black px-3 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors">
                View All Orders →
            </a>
        </div>

        @if($recentOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b-2 border-black bg-gray-50 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                            <th class="p-3">Order #</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Items</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 font-medium">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-3 font-mono font-bold text-black">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-black">{{ $order->customer_name ?: 'Guest Connoisseur' }}</div>
                                    <div class="text-[10px] text-gray-500 font-mono">{{ $order->customer_email }}</div>
                                </td>
                                <td class="p-3 text-gray-600">
                                    {{ $order->created_at->format('M d, Y') }}
                                    <span class="block text-[10px] text-gray-400 font-mono">{{ $order->created_at->format('H:i') }}</span>
                                </td>
                                <td class="p-3">
                                    <span class="font-bold">{{ $order->items->sum('quantity') }}</span> item(s)
                                </td>
                                <td class="p-3 font-mono font-black text-sm text-black">
                                    {{ number_format($order->total_price_minor / 100, 0) }} EGP
                                </td>
                                <td class="p-3">
                                    @php
                                        $badgeClass = match($order->status) {
                                            'delivered'  => 'bg-green-100 text-green-800 border-green-600',
                                            'shipped'    => 'bg-blue-100 text-blue-800 border-blue-600',
                                            'processing' => 'bg-purple-100 text-purple-800 border-purple-600',
                                            'cancelled'  => 'bg-red-100 text-red-800 border-red-600',
                                            default      => 'bg-amber-100 text-amber-800 border-amber-600',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <details class="relative inline-block text-left">
                                        <summary class="list-none cursor-pointer border border-black px-2 py-1 text-xs font-bold hover:bg-black hover:text-white transition-colors select-none">
                                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                        </summary>
                                        <div class="absolute right-0 mt-1 w-44 bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] z-50 py-1 text-left">
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="block px-3 py-2 text-xs font-bold text-black hover:bg-gray-100">
                                                <svg class="w-3.5 h-3.5 inline-block mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                View Details
                                            </a>
                                            <a href="{{ route('admin.orders.show', $order->id) }}#status" class="block px-3 py-2 text-xs font-bold text-black hover:bg-gray-100">
                                                <svg class="w-3.5 h-3.5 inline-block mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                                                Update Status
                                            </a>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center text-gray-500 border border-dashed border-gray-300">
                <span class="text-3xl block mb-2"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></span>
                <p class="font-bold text-sm uppercase tracking-wider">No orders recorded yet</p>
                <p class="text-xs text-gray-400 mt-1">Live customer orders will appear here automatically.</p>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue 30-Day Line Chart
    const ctxRevenue = document.getElementById('revenueChart');
    if (ctxRevenue) {
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Revenue (EGP)',
                    data: {!! json_encode($chartRevenue) !!},
                    borderColor: '#000000',
                    backgroundColor: 'rgba(0, 0, 0, 0.05)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.2,
                    pointBackgroundColor: '#000000',
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        grid: { color: '#e5e5e5' },
                        ticks: {
                            font: { size: 10, family: 'monospace' },
                            callback: function(val) { return val.toLocaleString() + ' EGP'; }
                        }
                    }
                }
            }
        });
    }

    // 2. Best Sellers Horizontal Bar Chart
    const ctxBestsellers = document.getElementById('bestsellersChart');
    if (ctxBestsellers) {
        new Chart(ctxBestsellers, {
            type: 'bar',
            data: {
                labels: {!! json_encode($bestsellerLabels) !!},
                datasets: [{
                    label: 'Units Sold',
                    data: {!! json_encode($bestsellerUnits) !!},
                    backgroundColor: '#111827',
                    borderRadius: 2,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: '#e5e5e5' },
                        ticks: { font: { size: 10, family: 'monospace' }, precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) {
                                const label = this.getLabelForValue(value);
                                return label.length > 18 ? label.substr(0, 18) + '...' : label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
