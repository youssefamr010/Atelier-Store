<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * GET /admin/search?q=term
     * Returns grouped JSON results for the admin global search bar.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['products' => [], 'orders' => [], 'customers' => []]);
        }

        $like = '%' . $q . '%';

        // Products — top 5
        $products = Product::where(function ($query) use ($like) {
            $query->where('title', 'like', $like)
                  ->orWhere('sku', 'like', $like);
        })
        ->select('id', 'title', 'sku', 'retail_price_minor', 'status', 'image_url', 'slug')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn($p) => [
            'id'     => $p->id,
            'label'  => $p->title,
            'sub'    => $p->sku . ' · ' . number_format($p->retail_price_minor / 100, 0) . ' EGP',
            'badge'  => $p->status,
            'url'    => route('admin.products.edit', $p->id),
            'img'    => $p->image_url ? (str_starts_with($p->image_url, 'http') ? $p->image_url : url($p->image_url)) : null,
        ]);

        // Orders — top 5
        $orders = Order::where(function ($query) use ($like, $q) {
            $query->where('order_number', 'like', $like)
                  ->orWhere('customer_email', 'like', $like)
                  ->orWhere('notes', 'like', '%' . $q . '%');
        })
        ->select('id', 'order_number', 'customer_email', 'total_amount_minor', 'shipping_status', 'created_at', 'metadata_json')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn($o) => [
            'id'    => $o->id,
            'label' => '#' . $o->order_number,
            'sub'   => ($o->customer_name ?? $o->customer_email) . ' · ' . number_format($o->total_amount_minor / 100, 0) . ' EGP',
            'badge' => $o->shipping_status,
            'url'   => route('admin.orders.show', $o->id),
        ]);

        // Customers — top 5
        $customers = User::where('is_admin', false)
        ->where(function ($query) use ($like) {
            $query->where('name', 'like', $like)
                  ->orWhere('email', 'like', $like);
        })
        ->select('id', 'name', 'email', 'created_at')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn($c) => [
            'id'    => $c->id,
            'label' => $c->name,
            'sub'   => $c->email,
            'badge' => 'customer',
            'url'   => route('admin.customers.show', $c->id),
        ]);

        // Admin Navigation & Integrations Pages
        $navLinks = [
            [
                'label'    => 'Telegram Bot & AI Assistant',
                'sub'      => 'Configure Telegram bot token, recipients, live sales assistant & alerts',
                'keywords' => ['telegram', 'tel', 'bot', 'alerts', 'notifications', 'تيليجرام', 'بوت', 'تليجرام', 'اشعارات'],
                'url'      => route('admin.telegram.index'),
                'icon'     => '✈️',
                'badge'    => 'Integration',
            ],
            [
                'label'    => 'WhatsApp Alerts & Gateway',
                'sub'      => 'Automated WhatsApp customer order confirmations & status updates',
                'keywords' => ['whatsapp', 'what', 'wa', 'chat', 'واتساب', 'واتس', 'رسائل', 'gateway', 'ultramsg'],
                'url'      => route('admin.whatsapp.index'),
                'icon'     => '💬',
                'badge'    => 'Integration',
            ],
            [
                'label'    => 'Products Management',
                'sub'      => 'View catalog, manage stock, prices, video & transparent images',
                'keywords' => ['product', 'products', 'item', 'catalog', 'منتجات', 'منتج', 'بضاعة'],
                'url'      => route('admin.products.index'),
                'icon'     => '🛍️',
                'badge'    => 'Catalog',
            ],
            [
                'label'    => 'Orders & Shipments',
                'sub'      => 'Manage customer orders, change status, and print receipts',
                'keywords' => ['order', 'orders', 'shipping', 'طلبات', 'اوردر', 'طلب', 'شحنات'],
                'url'      => route('admin.orders.index'),
                'icon'     => '🚚',
                'badge'    => 'Operations',
            ],
            [
                'label'    => 'Shipping & Delivery Rules',
                'sub'      => 'Configure Egyptian governorate delivery rates and express rules',
                'keywords' => ['shipping', 'delivery', 'tax', 'rates', 'شحن', 'محافظات', 'توصيل', 'ضرائب'],
                'url'      => route('admin.shipping.index'),
                'icon'     => '📍',
                'badge'    => 'Settings',
            ],
            [
                'label'    => 'Coupons & Discounts',
                'sub'      => 'Create promo codes, discounts, and flash sales',
                'keywords' => ['coupon', 'coupons', 'discount', 'promo', 'كوبونات', 'خصم', 'كوبون'],
                'url'      => route('admin.coupons.index'),
                'icon'     => '🎟️',
                'badge'    => 'Catalog',
            ],
            [
                'label'    => 'Abandoned Carts Recovery',
                'sub'      => 'Recover lost customers and incomplete checkouts',
                'keywords' => ['cart', 'abandoned', 'recovery', 'سلات', 'متروكة', 'سلة'],
                'url'      => route('admin.abandoned-carts.index'),
                'icon'     => '🛒',
                'badge'    => 'Marketing',
            ],
            [
                'label'    => 'Content & Promo Banners',
                'sub'      => 'Customize homepage marquee, banners, and store texts',
                'keywords' => ['content', 'banner', 'settings', 'store', 'محتوى', 'بانر', 'اعدادات'],
                'url'      => route('admin.content.index'),
                'icon'     => '⚙️',
                'badge'    => 'Settings',
            ],
            [
                'label'    => 'Analytics & Reports',
                'sub'      => 'Live traffic breakdown, revenue charts, and visitor insights',
                'keywords' => ['analytics', 'stats', 'traffic', 'charts', 'احصائيات', 'تقارير', 'ارباح'],
                'url'      => route('admin.analytics.index'),
                'icon'     => '📈',
                'badge'    => 'Overview',
            ],
        ];

        $qLower = mb_strtolower($q);
        $matchedNavigation = collect($navLinks)->filter(function ($item) use ($qLower) {
            if (str_contains(mb_strtolower($item['label']), $qLower)) return true;
            if (str_contains(mb_strtolower($item['sub']), $qLower)) return true;
            foreach ($item['keywords'] as $kw) {
                if (str_contains(mb_strtolower($kw), $qLower) || str_contains($qLower, mb_strtolower($kw))) {
                    return true;
                }
            }
            return false;
        })->values()->all();

        return response()->json([
            'navigation' => $matchedNavigation,
            'products'   => $products,
            'orders'     => $orders,
            'customers'  => $customers,
        ]);
    }
}
