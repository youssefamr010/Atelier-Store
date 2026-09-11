<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch or create all 6 decoration collections
        $collections = [
            'lighting-ambiance' => Collection::firstOrCreate(['slug' => 'lighting-ambiance'], ['title' => 'Lighting & Ambiance', 'status' => 'active', 'sort_order' => 10]),
            'plants-greenery' => Collection::firstOrCreate(['slug' => 'plants-greenery'], ['title' => 'Plants & Vases', 'status' => 'active', 'sort_order' => 20]),
            'cushions-textiles' => Collection::firstOrCreate(['slug' => 'cushions-textiles'], ['title' => 'Cushions & Textiles', 'status' => 'active', 'sort_order' => 30]),
            'shelves-storage' => Collection::firstOrCreate(['slug' => 'shelves-storage'], ['title' => 'Shelves & Storage', 'status' => 'active', 'sort_order' => 40]),
            'wall-art-decor' => Collection::firstOrCreate(['slug' => 'wall-art-decor'], ['title' => 'Wall Art & Decor', 'status' => 'active', 'sort_order' => 50]),
            'figurines-accents' => Collection::firstOrCreate(['slug' => 'figurines-accents'], ['title' => 'Figurines & Accents', 'status' => 'active', 'sort_order' => 60]),
        ];

        $attachMedia = function (Product $product, array $imageUrls) {
            $mediaSync = [];
            foreach ($imageUrls as $i => $url) {
                $asset = MediaAsset::firstOrCreate(
                    ['url' => $url],
                    [
                        'type' => 'image',
                        'filename' => basename(parse_url($url, PHP_URL_PATH)) ?: "asset-{$product->id}-{$i}.jpg",
                        'mime_type' => 'image/jpeg',
                        'size_bytes' => 180000,
                    ]
                );
                $mediaSync[$asset->id] = ['sort_order' => $i, 'group' => 'gallery'];
            }
            $product->mediaAssets()->sync($mediaSync);
        };

        // 2. Remove all old products not in the new decoration catalog
        $validSkus = [
            'DEC-LGT-CDL-01', 'DEC-LGT-CRT-02', 'DEC-LGT-DIF-03', 'DEC-LGT-NEO-04', 'DEC-LGT-SOY-05',
            'DEC-PLN-SUC-06', 'DEC-PLN-EUC-07', 'DEC-PLN-DNT-08', 'DEC-PLN-GLS-09',
            'DEC-CUS-VLV-10', 'DEC-CUS-BOH-11', 'DEC-CUS-TAB-12', 'DEC-CUS-LIN-13',
            'DEC-SHF-FLT-14', 'DEC-SHF-TRY-15', 'DEC-SHF-LED-16', 'DEC-SHF-CAN-17',
            'DEC-WAL-MAC-18', 'DEC-WAL-STK-19', 'DEC-WAL-DRM-20', 'DEC-WAL-CAN-21',
            'DEC-FIG-NAU-22', 'DEC-FIG-MIR-23', 'DEC-FIG-BOK-24', 'DEC-FIG-OST-25'
        ];
        Product::whereNotIn('sku', $validSkus)->each(function ($oldProduct) {
            $oldProduct->variants()->delete();
            $oldProduct->collections()->detach();
            $oldProduct->delete();
        });

        // ── 1. DEC-LGT-CDL-01: SUJUN Taper Candle Holders Set of 3 (Gra ─────────────────────
        $p1 = Product::updateOrCreate(
            ['slug' => 'sujun-taper-candle-holders-set-of-3'],
            [
                'sku' => 'DEC-LGT-CDL-01',
                'title' => 'SUJUN Taper Candle Holders Set of 3 (Graduated Heights)',
                'description' => 'Exquisite set of 3 slender metal candlestick holders with protective non-slip velvet bases. Distinctive graduated heights create a dynamic, warm ambiance on dining tables, mantels, and holiday tablescapes.',
                'image_url' => '/imges/decor/dec-lgt-cdl-01.jpg',
                'cost_price_minor' => 28000,
                'retail_price_minor' => 44900,
                'compare_at_price_minor' => 59900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 1,
                'material' => 'Brushed Matte Iron & Velvet Base',
                'dimensions' => 'S: 24cm, M: 29cm, L: 34cm',
                'weight' => '460g (Set of 3)',
                'attributes_json' => [
                    'Material' => 'Brushed Matte Iron & Velvet Base',
                    'Dimensions' => 'S: 24cm, M: 29cm, L: 34cm',
                    'Weight' => '460g (Set of 3)',
                    'amazon_asin' => 'B07ZPPTBMB',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B07ZPPTBMB',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p1->collections()->sync([$collections['lighting-ambiance']->id]);
        $attachMedia($p1, ['/imges/decor/dec-lgt-cdl-01.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-lighting-02.jpg']);

        $p1->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-CDL-01-1'],
            [
                'title' => 'Brushed Gold',
                'attribute_name' => 'Color',
                'attribute_value' => 'Gold',
                'cost_price_minor' => 28000,
                'retail_price_minor' => 44900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-cdl-01.jpg',
                'attributes_json' => ['color_hex' => '#D4AF37', 'color' => 'Gold', 'image_url' => '/imges/decor/dec-lgt-cdl-01.jpg'],
                'status' => 'active',
            ]
        );

        $p1->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-CDL-01-2'],
            [
                'title' => 'Matte Black',
                'attribute_name' => 'Color',
                'attribute_value' => 'Matte Black',
                'cost_price_minor' => 28000,
                'retail_price_minor' => 44900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-cdl-01.jpg',
                'attributes_json' => ['color_hex' => '#1A1A1A', 'color' => 'Matte Black', 'image_url' => '/imges/decor/dec-lgt-cdl-01.jpg'],
                'status' => 'active',
            ]
        );

        $p1->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-CDL-01-3'],
            [
                'title' => 'Silver Chrome',
                'attribute_name' => 'Color',
                'attribute_value' => 'Silver',
                'cost_price_minor' => 28000,
                'retail_price_minor' => 44900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-cdl-01.jpg',
                'attributes_json' => ['color_hex' => '#C0C0C0', 'color' => 'Silver', 'image_url' => '/imges/decor/dec-lgt-cdl-01.jpg'],
                'status' => 'active',
            ]
        );

        // ── 2. DEC-LGT-CRT-02: 300 LED Cascading Fairy Waterfall Curtai ─────────────────────
        $p2 = Product::updateOrCreate(
            ['slug' => '300-led-fairy-waterfall-curtain-lights-3x3m'],
            [
                'sku' => 'DEC-LGT-CRT-02',
                'title' => '300 LED Cascading Fairy Waterfall Curtain Lights (3x3m)',
                'description' => 'High-density 300 LED indoor/outdoor fairy curtain lights featuring 8 dynamic lighting modes, timer function, and remote control. USB-powered with memory chip to remember your favorite ambient setting.',
                'image_url' => '/imges/decor/dec-lgt-crt-02.jpg',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'compare_at_price_minor' => 41900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 2,
                'material' => 'Waterproof Silver Copper Wire & Clear PVC',
                'dimensions' => '3m Width x 3m Drop (10 Strands)',
                'weight' => '210g',
                'attributes_json' => [
                    'Material' => 'Waterproof Silver Copper Wire & Clear PVC',
                    'Dimensions' => '3m Width x 3m Drop (10 Strands)',
                    'Weight' => '210g',
                    'amazon_asin' => 'B0DB1T3644',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0DB1T3644',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p2->collections()->sync([$collections['lighting-ambiance']->id]);
        $attachMedia($p2, ['/imges/decor/dec-lgt-crt-02.jpg', '/imges/decor/gallery-lighting-02.jpg', '/imges/decor/gallery-lighting-01.jpg']);

        $p2->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-CRT-02-1'],
            [
                'title' => 'Warm White',
                'attribute_name' => 'Color',
                'attribute_value' => 'Warm White',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-crt-02.jpg',
                'attributes_json' => ['color_hex' => '#FFE4B5', 'color' => 'Warm White', 'image_url' => '/imges/decor/dec-lgt-crt-02.jpg'],
                'status' => 'active',
            ]
        );

        $p2->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-CRT-02-2'],
            [
                'title' => 'Cool White',
                'attribute_name' => 'Color',
                'attribute_value' => 'Cool White',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-crt-02.jpg',
                'attributes_json' => ['color_hex' => '#F0F8FF', 'color' => 'Cool White', 'image_url' => '/imges/decor/dec-lgt-crt-02.jpg'],
                'status' => 'active',
            ]
        );

        $p2->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-CRT-02-3'],
            [
                'title' => 'Rainbow Multicolor',
                'attribute_name' => 'Color',
                'attribute_value' => 'Multicolor',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-crt-02.jpg',
                'attributes_json' => ['color_hex' => '#9370DB', 'color' => 'Multicolor', 'image_url' => '/imges/decor/dec-lgt-crt-02.jpg'],
                'status' => 'active',
            ]
        );

        // ── 3. DEC-LGT-DIF-03: Aromatherapy Botanical Essential Oil Ree ─────────────────────
        $p3 = Product::updateOrCreate(
            ['slug' => 'aromatherapy-botanical-essential-oil-reed-diffuser'],
            [
                'sku' => 'DEC-LGT-DIF-03',
                'title' => 'Aromatherapy Botanical Essential Oil Reed Diffuser (100ml)',
                'description' => 'Slow-release natural rattan reeds diffuse calming French lavender and white sage essential oils. Lasts for up to 45 continuous days, purifying room air and creating an inviting spa sanctuary at home.',
                'image_url' => '/imges/decor/dec-lgt-dif-03.jpg',
                'cost_price_minor' => 15000,
                'retail_price_minor' => 23900,
                'compare_at_price_minor' => 33900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 3,
                'material' => 'Amber Apothecary Glass & Natural Rattan Reeds',
                'dimensions' => '100ml Bottle (22cm Reeds Height)',
                'weight' => '320g',
                'attributes_json' => [
                    'Material' => 'Amber Apothecary Glass & Natural Rattan Reeds',
                    'Dimensions' => '100ml Bottle (22cm Reeds Height)',
                    'Weight' => '320g',
                    'amazon_asin' => 'B0FPGQ6XFF',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0FPGQ6XFF',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p3->collections()->sync([$collections['lighting-ambiance']->id]);
        $attachMedia($p3, ['/imges/decor/dec-lgt-dif-03.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-lighting-02.jpg']);

        $p3->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-DIF-03-1'],
            [
                'title' => 'French Lavender',
                'attribute_name' => 'Color',
                'attribute_value' => 'Lavender',
                'cost_price_minor' => 15000,
                'retail_price_minor' => 23900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-dif-03.jpg',
                'attributes_json' => ['color_hex' => '#BDB5D5', 'color' => 'Lavender', 'image_url' => '/imges/decor/dec-lgt-dif-03.jpg'],
                'status' => 'active',
            ]
        );

        $p3->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-DIF-03-2'],
            [
                'title' => 'Vanilla Sandalwood',
                'attribute_name' => 'Color',
                'attribute_value' => 'Vanilla',
                'cost_price_minor' => 15000,
                'retail_price_minor' => 23900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-dif-03.jpg',
                'attributes_json' => ['color_hex' => '#F3E5AB', 'color' => 'Vanilla', 'image_url' => '/imges/decor/dec-lgt-dif-03.jpg'],
                'status' => 'active',
            ]
        );

        // ── 4. DEC-LGT-NEO-04: Retro Theater Glow LED Neon Wall Sign (U ─────────────────────
        $p4 = Product::updateOrCreate(
            ['slug' => 'retro-theater-glow-led-neon-wall-sign'],
            [
                'sku' => 'DEC-LGT-NEO-04',
                'title' => 'Retro Theater Glow LED Neon Wall Sign (USB Powered)',
                'description' => 'Vibrant low-voltage flexible silicone LED neon strip mounted on ultra-clear acrylic backing. Includes pre-drilled hanging holes and inline dimmable dimmer switch. Creates an inviting cinema ambiance in home theaters, bedrooms, or cozy living rooms.',
                'image_url' => '/imges/decor/dec-lgt-neo-04.jpg',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'compare_at_price_minor' => 77900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 4,
                'material' => 'Flexible Silicone LED & High-Clarity Acrylic',
                'dimensions' => '40cm x 12cm x 1.5cm',
                'weight' => '380g',
                'attributes_json' => [
                    'Material' => 'Flexible Silicone LED & High-Clarity Acrylic',
                    'Dimensions' => '40cm x 12cm x 1.5cm',
                    'Weight' => '380g',
                    'amazon_asin' => 'B0D69XB1YL',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0D69XB1YL',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p4->collections()->sync([$collections['lighting-ambiance']->id]);
        $attachMedia($p4, ['/imges/decor/dec-lgt-neo-04.jpg', '/imges/decor/gallery-lighting-02.jpg', '/imges/decor/gallery-lighting-01.jpg']);

        $p4->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-NEO-04-1'],
            [
                'title' => 'Neon Warm Sunset',
                'attribute_name' => 'Color',
                'attribute_value' => 'Sunset Orange',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-neo-04.jpg',
                'attributes_json' => ['color_hex' => '#FF4500', 'color' => 'Sunset Orange', 'image_url' => '/imges/decor/dec-lgt-neo-04.jpg'],
                'status' => 'active',
            ]
        );

        $p4->variants()->updateOrCreate(
            ['sku' => 'DEC-LGT-NEO-04-2'],
            [
                'title' => 'Ice Cyan & Pink',
                'attribute_name' => 'Color',
                'attribute_value' => 'Ice Cyan',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-lgt-neo-04.jpg',
                'attributes_json' => ['color_hex' => '#00FFFF', 'color' => 'Ice Cyan', 'image_url' => '/imges/decor/dec-lgt-neo-04.jpg'],
                'status' => 'active',
            ]
        );

        // ── 5. DEC-LGT-SOY-05: Natural Soy Wax Aromatherapy Scented Can ─────────────────────
        $p5 = Product::updateOrCreate(
            ['slug' => 'natural-soy-wax-aromatherapy-scented-candles-gift-set'],
            [
                'sku' => 'DEC-LGT-SOY-05',
                'title' => 'Natural Soy Wax Aromatherapy Scented Candles Gift Set',
                'description' => '100% natural soy wax candles infused with organic botanical essential oils: Rose, Lavender, Vanilla & Jasmine. Poured in reusable decorative vintage travel tins with lead-free cotton wicks for a clean 30-hour smoke-free burn.',
                'image_url' => '/imges/decor/dec-lgt-soy-05.jpg',
                'cost_price_minor' => 23000,
                'retail_price_minor' => 35900,
                'compare_at_price_minor' => 50900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 5,
                'material' => '100% Natural Soy Wax & Essential Oils',
                'dimensions' => '6.5cm x 4cm per Tin (4-Pack)',
                'weight' => '520g',
                'attributes_json' => [
                    'Material' => '100% Natural Soy Wax & Essential Oils',
                    'Dimensions' => '6.5cm x 4cm per Tin (4-Pack)',
                    'Weight' => '520g',
                    'amazon_asin' => 'B08C2HHBCZ',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B08C2HHBCZ',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p5->collections()->sync([$collections['lighting-ambiance']->id]);
        $attachMedia($p5, ['/imges/decor/dec-lgt-soy-05.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-lighting-02.jpg']);

        // ── 6. DEC-PLN-SUC-06: Potted Faux Succulent Plants in White Ce ─────────────────────
        $p6 = Product::updateOrCreate(
            ['slug' => 'potted-faux-succulent-plants-in-white-ceramic-set-of-6'],
            [
                'sku' => 'DEC-PLN-SUC-06',
                'title' => 'Potted Faux Succulent Plants in White Ceramic (Set of 6)',
                'description' => 'Lifelike artificial succulents with tactile soft-touch leaves, realistic gravel substrate, and handcrafted geometric hexagonal ceramic pots. Zero watering, zero maintenance, vibrant year-round greenery for desks, bookshelves, and bathroom vanities.',
                'image_url' => '/imges/decor/dec-pln-suc-06.jpg',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'compare_at_price_minor' => 41900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 6,
                'material' => 'Ceramic Pot + Natural Stone Substrate + PE Foliage',
                'dimensions' => '7cm Diameter x 9cm Height each',
                'weight' => '680g (Set of 6)',
                'attributes_json' => [
                    'Material' => 'Ceramic Pot + Natural Stone Substrate + PE Foliage',
                    'Dimensions' => '7cm Diameter x 9cm Height each',
                    'Weight' => '680g (Set of 6)',
                    'amazon_asin' => 'B0H5782JB7',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0H5782JB7',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p6->collections()->sync([$collections['plants-greenery']->id]);
        $attachMedia($p6, ['/imges/decor/dec-pln-suc-06.jpg', '/imges/decor/gallery-plants-01.jpg', '/imges/decor/gallery-plants-02.jpg']);

        // ── 7. DEC-PLN-EUC-07: Frosted Silver Dollar Artificial Eucalyp ─────────────────────
        $p7 = Product::updateOrCreate(
            ['slug' => 'frosted-silver-dollar-artificial-eucalyptus-stems-15pcs'],
            [
                'sku' => 'DEC-PLN-EUC-07',
                'title' => 'Frosted Silver Dollar Artificial Eucalyptus Stems (15 Pcs)',
                'description' => 'Lush faux eucalyptus branches with botanical frosted powder finish for authentic natural look. Bendable iron-core stems easily trim or curve into flower vases, wedding centerpieces, and boho botanical arrangements.',
                'image_url' => '/imges/decor/dec-pln-euc-07.jpg',
                'cost_price_minor' => 14500,
                'retail_price_minor' => 22900,
                'compare_at_price_minor' => 32900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 7,
                'material' => 'Silk Fabric Leaves with Flexible Wire Core',
                'dimensions' => '42cm Height per Stem (15 Stems)',
                'weight' => '190g',
                'attributes_json' => [
                    'Material' => 'Silk Fabric Leaves with Flexible Wire Core',
                    'Dimensions' => '42cm Height per Stem (15 Stems)',
                    'Weight' => '190g',
                    'amazon_asin' => 'B0F32TPC73',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0F32TPC73',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p7->collections()->sync([$collections['plants-greenery']->id]);
        $attachMedia($p7, ['/imges/decor/dec-pln-euc-07.jpg', '/imges/decor/gallery-plants-02.jpg', '/imges/decor/gallery-plants-01.jpg']);

        $p7->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-EUC-07-1'],
            [
                'title' => 'Frosted Sage Green',
                'attribute_name' => 'Color',
                'attribute_value' => 'Sage Green',
                'cost_price_minor' => 14500,
                'retail_price_minor' => 22900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-euc-07.jpg',
                'attributes_json' => ['color_hex' => '#87A987', 'color' => 'Sage Green', 'image_url' => '/imges/decor/dec-pln-euc-07.jpg'],
                'status' => 'active',
            ]
        );

        $p7->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-EUC-07-2'],
            [
                'title' => 'Autumn Rust Terracotta',
                'attribute_name' => 'Color',
                'attribute_value' => 'Autumn Rust',
                'cost_price_minor' => 14500,
                'retail_price_minor' => 22900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-euc-07.jpg',
                'attributes_json' => ['color_hex' => '#CC4E2A', 'color' => 'Autumn Rust', 'image_url' => '/imges/decor/dec-pln-euc-07.jpg'],
                'status' => 'active',
            ]
        );

        // ── 8. DEC-PLN-DNT-08: Nordic Hollow Donut Ceramic Flower Vase  ─────────────────────
        $p8 = Product::updateOrCreate(
            ['slug' => 'nordic-hollow-donut-ceramic-flower-vase-set-pair'],
            [
                'sku' => 'DEC-PLN-DNT-08',
                'title' => 'Nordic Hollow Donut Ceramic Flower Vase Set (Pair)',
                'description' => 'Architectural circular hollow silhouette with unglazed textured matte finish. An iconic Scandinavian centerpiece designed for dried pampas grass, rabbit tail florals, or minimalist standalone shelf staging.',
                'image_url' => '/imges/decor/dec-pln-dnt-08.jpg',
                'cost_price_minor' => 27000,
                'retail_price_minor' => 41900,
                'compare_at_price_minor' => 59900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 8,
                'material' => '100% High-Fired Unglazed Terracotta Ceramic',
                'dimensions' => 'Large: 20x19cm, Medium: 16x15cm',
                'weight' => '850g (Set of 2)',
                'attributes_json' => [
                    'Material' => '100% High-Fired Unglazed Terracotta Ceramic',
                    'Dimensions' => 'Large: 20x19cm, Medium: 16x15cm',
                    'Weight' => '850g (Set of 2)',
                    'amazon_asin' => 'B0GG75QHWC',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0GG75QHWC',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p8->collections()->sync([$collections['plants-greenery']->id]);
        $attachMedia($p8, ['/imges/decor/dec-pln-dnt-08.jpg', '/imges/decor/gallery-plants-01.jpg', '/imges/decor/gallery-plants-02.jpg']);

        $p8->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-DNT-08-1'],
            [
                'title' => 'Off-White Matte',
                'attribute_name' => 'Color',
                'attribute_value' => 'Off-White',
                'cost_price_minor' => 27000,
                'retail_price_minor' => 41900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-dnt-08.jpg',
                'attributes_json' => ['color_hex' => '#F4F1EA', 'color' => 'Off-White', 'image_url' => '/imges/decor/dec-pln-dnt-08.jpg'],
                'status' => 'active',
            ]
        );

        $p8->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-DNT-08-2'],
            [
                'title' => 'Basalt Black Matte',
                'attribute_name' => 'Color',
                'attribute_value' => 'Matte Black',
                'cost_price_minor' => 27000,
                'retail_price_minor' => 41900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-dnt-08.jpg',
                'attributes_json' => ['color_hex' => '#222222', 'color' => 'Matte Black', 'image_url' => '/imges/decor/dec-pln-dnt-08.jpg'],
                'status' => 'active',
            ]
        );

        $p8->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-DNT-08-3'],
            [
                'title' => 'Warm Terracotta',
                'attribute_name' => 'Color',
                'attribute_value' => 'Terracotta',
                'cost_price_minor' => 27000,
                'retail_price_minor' => 41900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-dnt-08.jpg',
                'attributes_json' => ['color_hex' => '#C86D51', 'color' => 'Terracotta', 'image_url' => '/imges/decor/dec-pln-dnt-08.jpg'],
                'status' => 'active',
            ]
        );

        // ── 9. DEC-PLN-GLS-09: Modern Fluted Ribbed Glass Flower Vase ( ─────────────────────
        $p9 = Product::updateOrCreate(
            ['slug' => 'modern-fluted-ribbed-glass-flower-vase-22cm'],
            [
                'sku' => 'DEC-PLN-GLS-09',
                'title' => 'Modern Fluted Ribbed Glass Flower Vase (22cm)',
                'description' => 'Thick weighted crystal-clear borosilicate glass with vertical retro fluted texture. Catches and refracts sunlight beautifully while supporting single-stem lilies, tulips, or cascading eucalyptus greens.',
                'image_url' => '/imges/decor/dec-pln-gls-09.jpg',
                'cost_price_minor' => 16000,
                'retail_price_minor' => 25900,
                'compare_at_price_minor' => 34900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 9,
                'material' => 'Lead-Free High Density Borosilicate Glass',
                'dimensions' => '22cm Height x 10cm Base Diameter',
                'weight' => '620g',
                'attributes_json' => [
                    'Material' => 'Lead-Free High Density Borosilicate Glass',
                    'Dimensions' => '22cm Height x 10cm Base Diameter',
                    'Weight' => '620g',
                    'amazon_asin' => 'B0H52QR63H',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0H52QR63H',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p9->collections()->sync([$collections['plants-greenery']->id]);
        $attachMedia($p9, ['/imges/decor/dec-pln-gls-09.jpg', '/imges/decor/gallery-plants-02.jpg', '/imges/decor/gallery-plants-01.jpg']);

        $p9->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-GLS-09-1'],
            [
                'title' => 'Clear Crystal',
                'attribute_name' => 'Color',
                'attribute_value' => 'Clear',
                'cost_price_minor' => 16000,
                'retail_price_minor' => 25900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-gls-09.jpg',
                'attributes_json' => ['color_hex' => '#F0F8FF', 'color' => 'Clear', 'image_url' => '/imges/decor/dec-pln-gls-09.jpg'],
                'status' => 'active',
            ]
        );

        $p9->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-GLS-09-2'],
            [
                'title' => 'Smoky Charcoal',
                'attribute_name' => 'Color',
                'attribute_value' => 'Smoke Grey',
                'cost_price_minor' => 16000,
                'retail_price_minor' => 25900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-gls-09.jpg',
                'attributes_json' => ['color_hex' => '#4A4A4A', 'color' => 'Smoke Grey', 'image_url' => '/imges/decor/dec-pln-gls-09.jpg'],
                'status' => 'active',
            ]
        );

        $p9->variants()->updateOrCreate(
            ['sku' => 'DEC-PLN-GLS-09-3'],
            [
                'title' => 'Vintage Amber',
                'attribute_name' => 'Color',
                'attribute_value' => 'Amber Gold',
                'cost_price_minor' => 16000,
                'retail_price_minor' => 25900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-pln-gls-09.jpg',
                'attributes_json' => ['color_hex' => '#DAA520', 'color' => 'Amber Gold', 'image_url' => '/imges/decor/dec-pln-gls-09.jpg'],
                'status' => 'active',
            ]
        );

        // ── 10. DEC-CUS-VLV-10: Luxury Gold Stripe Plush Velvet Cushion  ─────────────────────
        $p10 = Product::updateOrCreate(
            ['slug' => 'luxury-gold-stripe-plush-velvet-cushion-cover-45x45cm'],
            [
                'sku' => 'DEC-CUS-VLV-10',
                'title' => 'Luxury Gold Stripe Plush Velvet Cushion Cover (45x45cm)',
                'description' => 'Ultra-soft heavyweight Dutch velvet fabric accented with a hand-stitched gold PU leather geometric inset. Equipped with invisible color-matched zipper closure. Transforms any sofa or accent chair into a luxury boutique hotel lounge.',
                'image_url' => '/imges/decor/dec-cus-vlv-10.jpg',
                'cost_price_minor' => 12000,
                'retail_price_minor' => 19900,
                'compare_at_price_minor' => 27900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 10,
                'material' => 'Premium Dutch Velvet & Gold Vegan Leather',
                'dimensions' => '45cm x 45cm',
                'weight' => '160g',
                'attributes_json' => [
                    'Material' => 'Premium Dutch Velvet & Gold Vegan Leather',
                    'Dimensions' => '45cm x 45cm',
                    'Weight' => '160g',
                    'amazon_asin' => 'B0H5X5M38F',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0H5X5M38F',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p10->collections()->sync([$collections['cushions-textiles']->id]);
        $attachMedia($p10, ['/imges/decor/dec-cus-vlv-10.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-plants-02.jpg']);

        $p10->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-VLV-10-1'],
            [
                'title' => 'Emerald Green',
                'attribute_name' => 'Color',
                'attribute_value' => 'Emerald Green',
                'cost_price_minor' => 12000,
                'retail_price_minor' => 19900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-vlv-10.jpg',
                'attributes_json' => ['color_hex' => '#097969', 'color' => 'Emerald Green', 'image_url' => '/imges/decor/dec-cus-vlv-10.jpg'],
                'status' => 'active',
            ]
        );

        $p10->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-VLV-10-2'],
            [
                'title' => 'Dusty Rose Pink',
                'attribute_name' => 'Color',
                'attribute_value' => 'Dusty Rose',
                'cost_price_minor' => 12000,
                'retail_price_minor' => 19900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-vlv-10.jpg',
                'attributes_json' => ['color_hex' => '#DCAE96', 'color' => 'Dusty Rose', 'image_url' => '/imges/decor/dec-cus-vlv-10.jpg'],
                'status' => 'active',
            ]
        );

        $p10->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-VLV-10-3'],
            [
                'title' => 'Midnight Navy',
                'attribute_name' => 'Color',
                'attribute_value' => 'Navy Blue',
                'cost_price_minor' => 12000,
                'retail_price_minor' => 19900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-vlv-10.jpg',
                'attributes_json' => ['color_hex' => '#000080', 'color' => 'Navy Blue', 'image_url' => '/imges/decor/dec-cus-vlv-10.jpg'],
                'status' => 'active',
            ]
        );

        $p10->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-VLV-10-4'],
            [
                'title' => 'Warm Mustard',
                'attribute_name' => 'Color',
                'attribute_value' => 'Mustard Gold',
                'cost_price_minor' => 12000,
                'retail_price_minor' => 19900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-vlv-10.jpg',
                'attributes_json' => ['color_hex' => '#E1AD01', 'color' => 'Mustard Gold', 'image_url' => '/imges/decor/dec-cus-vlv-10.jpg'],
                'status' => 'active',
            ]
        );

        // ── 11. DEC-CUS-BOH-11: Hand-Tufted Textured Boho Fringe Throw P ─────────────────────
        $p11 = Product::updateOrCreate(
            ['slug' => 'hand-tufted-textured-boho-fringe-throw-pillow-cover'],
            [
                'sku' => 'DEC-CUS-BOH-11',
                'title' => 'Hand-Tufted Textured Boho Fringe Throw Pillow Cover',
                'description' => 'Artisan woven thick cotton canvas with three-dimensional tufted geometric diamond embroidery and corner yarn tassels. Bohemian neutral aesthetic that blends effortlessly with modern, rustic, or Scandinavian interiors.',
                'image_url' => '/imges/decor/dec-cus-boh-11.jpg',
                'cost_price_minor' => 14000,
                'retail_price_minor' => 21900,
                'compare_at_price_minor' => 31900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 11,
                'material' => '100% Natural Breathable Cotton Canvas',
                'dimensions' => '45cm x 45cm',
                'weight' => '240g',
                'attributes_json' => [
                    'Material' => '100% Natural Breathable Cotton Canvas',
                    'Dimensions' => '45cm x 45cm',
                    'Weight' => '240g',
                    'amazon_asin' => 'B0G2SP1Q9L',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0G2SP1Q9L',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p11->collections()->sync([$collections['cushions-textiles']->id]);
        $attachMedia($p11, ['/imges/decor/dec-cus-boh-11.jpg', '/imges/decor/gallery-plants-02.jpg', '/imges/decor/gallery-lighting-01.jpg']);

        $p11->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-BOH-11-1'],
            [
                'title' => 'Cream Ivory & Charcoal',
                'attribute_name' => 'Color',
                'attribute_value' => 'Cream Ivory',
                'cost_price_minor' => 14000,
                'retail_price_minor' => 21900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-boh-11.jpg',
                'attributes_json' => ['color_hex' => '#FDFBF7', 'color' => 'Cream Ivory', 'image_url' => '/imges/decor/dec-cus-boh-11.jpg'],
                'status' => 'active',
            ]
        );

        $p11->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-BOH-11-2'],
            [
                'title' => 'Earthy Terracotta',
                'attribute_name' => 'Color',
                'attribute_value' => 'Terracotta',
                'cost_price_minor' => 14000,
                'retail_price_minor' => 21900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-boh-11.jpg',
                'attributes_json' => ['color_hex' => '#B85D43', 'color' => 'Terracotta', 'image_url' => '/imges/decor/dec-cus-boh-11.jpg'],
                'status' => 'active',
            ]
        );

        // ── 12. DEC-CUS-TAB-12: Bohemian Handwoven Macramé Cotton Table  ─────────────────────
        $p12 = Product::updateOrCreate(
            ['slug' => 'bohemian-handwoven-macrame-cotton-table-runner-200cm'],
            [
                'sku' => 'DEC-CUS-TAB-12',
                'title' => 'Bohemian Handwoven Macramé Cotton Table Runner (200cm)',
                'description' => 'Intricate hollow crochet chevron weaving handcrafted from unbleached natural cotton cords. Finished with delicate hand-knotted fringe edges. Ideal for dining tables, console tables, coffee tables, or piano tops.',
                'image_url' => '/imges/decor/dec-cus-tab-12.jpg',
                'cost_price_minor' => 17500,
                'retail_price_minor' => 27900,
                'compare_at_price_minor' => 38900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 12,
                'material' => '100% Eco-Friendly Natural Cotton Cord',
                'dimensions' => '200cm Length x 45cm Width',
                'weight' => '350g',
                'attributes_json' => [
                    'Material' => '100% Eco-Friendly Natural Cotton Cord',
                    'Dimensions' => '200cm Length x 45cm Width',
                    'Weight' => '350g',
                    'amazon_asin' => 'B0GHSF14DM',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0GHSF14DM',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p12->collections()->sync([$collections['cushions-textiles']->id]);
        $attachMedia($p12, ['/imges/decor/dec-cus-tab-12.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-plants-01.jpg']);

        $p12->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-TAB-12-1'],
            [
                'title' => 'Natural Ecru Cream',
                'attribute_name' => 'Color',
                'attribute_value' => 'Natural Ecru',
                'cost_price_minor' => 17500,
                'retail_price_minor' => 27900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-tab-12.jpg',
                'attributes_json' => ['color_hex' => '#F5F2EB', 'color' => 'Natural Ecru', 'image_url' => '/imges/decor/dec-cus-tab-12.jpg'],
                'status' => 'active',
            ]
        );

        $p12->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-TAB-12-2'],
            [
                'title' => 'Black & Cream Contrast',
                'attribute_name' => 'Color',
                'attribute_value' => 'Charcoal Black',
                'cost_price_minor' => 17500,
                'retail_price_minor' => 27900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-tab-12.jpg',
                'attributes_json' => ['color_hex' => '#303030', 'color' => 'Charcoal Black', 'image_url' => '/imges/decor/dec-cus-tab-12.jpg'],
                'status' => 'active',
            ]
        );

        // ── 13. DEC-CUS-LIN-13: Natural Slub Linen Semi-Sheer Draping Cu ─────────────────────
        $p13 = Product::updateOrCreate(
            ['slug' => 'natural-slub-linen-semi-sheer-draping-curtains-pair'],
            [
                'sku' => 'DEC-CUS-LIN-13',
                'title' => 'Natural Slub Linen Semi-Sheer Draping Curtains (Pair)',
                'description' => 'Rich slub linen weave texture softens harsh sunlight into gentle ambient daylight while preserving daytime privacy. Features rust-resistant matte silver grommets for smooth effortless gliding on any curtain rod.',
                'image_url' => '/imges/decor/dec-cus-lin-13.jpg',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'compare_at_price_minor' => 77900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 13,
                'material' => 'Flax Slub Linen Blend (Anti-Wrinkle)',
                'dimensions' => '140cm Width x 260cm Drop (2 Panels)',
                'weight' => '890g',
                'attributes_json' => [
                    'Material' => 'Flax Slub Linen Blend (Anti-Wrinkle)',
                    'Dimensions' => '140cm Width x 260cm Drop (2 Panels)',
                    'Weight' => '890g',
                    'amazon_asin' => 'B0CT5X377V',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0CT5X377V',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p13->collections()->sync([$collections['cushions-textiles']->id]);
        $attachMedia($p13, ['/imges/decor/dec-cus-lin-13.jpg', '/imges/decor/gallery-plants-02.jpg', '/imges/decor/gallery-lighting-02.jpg']);

        $p13->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-LIN-13-1'],
            [
                'title' => 'Warm Flax Beige',
                'attribute_name' => 'Color',
                'attribute_value' => 'Flax Beige',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-lin-13.jpg',
                'attributes_json' => ['color_hex' => '#D2B48C', 'color' => 'Flax Beige', 'image_url' => '/imges/decor/dec-cus-lin-13.jpg'],
                'status' => 'active',
            ]
        );

        $p13->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-LIN-13-2'],
            [
                'title' => 'Pure Cloud White',
                'attribute_name' => 'Color',
                'attribute_value' => 'Pure White',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-lin-13.jpg',
                'attributes_json' => ['color_hex' => '#FFFFFF', 'color' => 'Pure White', 'image_url' => '/imges/decor/dec-cus-lin-13.jpg'],
                'status' => 'active',
            ]
        );

        $p13->variants()->updateOrCreate(
            ['sku' => 'DEC-CUS-LIN-13-3'],
            [
                'title' => 'Cool Dove Grey',
                'attribute_name' => 'Color',
                'attribute_value' => 'Dove Grey',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-cus-lin-13.jpg',
                'attributes_json' => ['color_hex' => '#A9A9A9', 'color' => 'Dove Grey', 'image_url' => '/imges/decor/dec-cus-lin-13.jpg'],
                'status' => 'active',
            ]
        );

        // ── 14. DEC-SHF-FLT-14: Modern Minimalist Floating Wall Display  ─────────────────────
        $p14 = Product::updateOrCreate(
            ['slug' => 'modern-minimalist-floating-wall-display-shelves-set-of-3'],
            [
                'sku' => 'DEC-SHF-FLT-14',
                'title' => 'Modern Minimalist Floating Wall Display Shelves (Set of 3)',
                'description' => 'Engineered hardwood U-shaped ledge floating wall shelves in 3 graduated sizes with invisible mounting brackets. Creates stylish elevated display space for photo frames, mini succulents, candles, and decorative keepsakes.',
                'image_url' => '/imges/decor/dec-shf-flt-14.jpg',
                'cost_price_minor' => 25000,
                'retail_price_minor' => 39900,
                'compare_at_price_minor' => 55900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 14,
                'material' => 'High-Grade Engineered MDF with Matte Laminate',
                'dimensions' => 'S: 22cm, M: 32cm, L: 42cm (Depth: 10cm)',
                'weight' => '1.4kg (Complete Kit)',
                'attributes_json' => [
                    'Material' => 'High-Grade Engineered MDF with Matte Laminate',
                    'Dimensions' => 'S: 22cm, M: 32cm, L: 42cm (Depth: 10cm)',
                    'Weight' => '1.4kg (Complete Kit)',
                    'amazon_asin' => 'B0FQCQ71GV',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0FQCQ71GV',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p14->collections()->sync([$collections['shelves-storage']->id]);
        $attachMedia($p14, ['/imges/decor/dec-shf-flt-14.jpg', '/imges/decor/gallery-plants-01.jpg', '/imges/decor/gallery-lighting-01.jpg']);

        $p14->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-FLT-14-1'],
            [
                'title' => 'Nordic Clean White',
                'attribute_name' => 'Color',
                'attribute_value' => 'White',
                'cost_price_minor' => 25000,
                'retail_price_minor' => 39900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-flt-14.jpg',
                'attributes_json' => ['color_hex' => '#F8F9FA', 'color' => 'White', 'image_url' => '/imges/decor/dec-shf-flt-14.jpg'],
                'status' => 'active',
            ]
        );

        $p14->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-FLT-14-2'],
            [
                'title' => 'Industrial Matte Black',
                'attribute_name' => 'Color',
                'attribute_value' => 'Black',
                'cost_price_minor' => 25000,
                'retail_price_minor' => 39900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-flt-14.jpg',
                'attributes_json' => ['color_hex' => '#1C1C1C', 'color' => 'Black', 'image_url' => '/imges/decor/dec-shf-flt-14.jpg'],
                'status' => 'active',
            ]
        );

        $p14->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-FLT-14-3'],
            [
                'title' => 'Warm Natural Walnut',
                'attribute_name' => 'Color',
                'attribute_value' => 'Walnut',
                'cost_price_minor' => 25000,
                'retail_price_minor' => 39900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-flt-14.jpg',
                'attributes_json' => ['color_hex' => '#5C4033', 'color' => 'Walnut', 'image_url' => '/imges/decor/dec-shf-flt-14.jpg'],
                'status' => 'active',
            ]
        );

        // ── 15. DEC-SHF-TRY-15: Iridescent Pearl Leaf Ceramic Jewelry &  ─────────────────────
        $p15 = Product::updateOrCreate(
            ['slug' => 'iridescent-pearl-leaf-ceramic-jewelry-trinket-tray'],
            [
                'sku' => 'DEC-SHF-TRY-15',
                'title' => 'Iridescent Pearl Leaf Ceramic Jewelry & Trinket Tray',
                'description' => 'Cast in the graceful silhouette of a monstera leaf with an ethereal pearlescent luster glaze. Perfect bedside nightstand or vanity dish for rings, earrings, smartwatches, keys, and daily fine jewelry.',
                'image_url' => '/imges/decor/dec-shf-try-15.jpg',
                'cost_price_minor' => 9500,
                'retail_price_minor' => 15900,
                'compare_at_price_minor' => 21900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 15,
                'material' => 'Fine Glazed Ceramic with Pearlescent Finish',
                'dimensions' => '15cm x 9cm x 2cm',
                'weight' => '180g',
                'attributes_json' => [
                    'Material' => 'Fine Glazed Ceramic with Pearlescent Finish',
                    'Dimensions' => '15cm x 9cm x 2cm',
                    'Weight' => '180g',
                    'amazon_asin' => 'B0HFD1JQFN',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0HFD1JQFN',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p15->collections()->sync([$collections['shelves-storage']->id]);
        $attachMedia($p15, ['/imges/decor/dec-shf-try-15.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-plants-02.jpg']);

        $p15->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-TRY-15-1'],
            [
                'title' => 'Opal Iridescent Pearl',
                'attribute_name' => 'Color',
                'attribute_value' => 'Pearl White',
                'cost_price_minor' => 9500,
                'retail_price_minor' => 15900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-try-15.jpg',
                'attributes_json' => ['color_hex' => '#F0EAE1', 'color' => 'Pearl White', 'image_url' => '/imges/decor/dec-shf-try-15.jpg'],
                'status' => 'active',
            ]
        );

        $p15->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-TRY-15-2'],
            [
                'title' => 'Emerald Green Gold Rim',
                'attribute_name' => 'Color',
                'attribute_value' => 'Emerald Gold',
                'cost_price_minor' => 9500,
                'retail_price_minor' => 15900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-try-15.jpg',
                'attributes_json' => ['color_hex' => '#046307', 'color' => 'Emerald Gold', 'image_url' => '/imges/decor/dec-shf-try-15.jpg'],
                'status' => 'active',
            ]
        );

        // ── 16. DEC-SHF-LED-16: Picture Ledge Wooden Floating Gallery Ra ─────────────────────
        $p16 = Product::updateOrCreate(
            ['slug' => 'picture-ledge-wooden-floating-gallery-rail-set-of-2'],
            [
                'sku' => 'DEC-SHF-LED-16',
                'title' => 'Picture Ledge Wooden Floating Gallery Rail (Set of 2)',
                'description' => 'Slim profile wall-mounted display ledge with protective front lip to keep vinyl records, framed pictures, and art prints securely in place without sliding off.',
                'image_url' => '/imges/decor/dec-shf-led-16.jpg',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'compare_at_price_minor' => 41900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 16,
                'material' => 'Solid Pine Wood with Natural Grain Lacquer',
                'dimensions' => '40cm Length x 10cm Depth x 4cm Lip',
                'weight' => '720g (Set of 2)',
                'attributes_json' => [
                    'Material' => 'Solid Pine Wood with Natural Grain Lacquer',
                    'Dimensions' => '40cm Length x 10cm Depth x 4cm Lip',
                    'Weight' => '720g (Set of 2)',
                    'amazon_asin' => 'B0FBMDT7P6',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0FBMDT7P6',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p16->collections()->sync([$collections['shelves-storage']->id]);
        $attachMedia($p16, ['/imges/decor/dec-shf-led-16.jpg', '/imges/decor/gallery-plants-01.jpg', '/imges/decor/gallery-lighting-02.jpg']);

        $p16->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-LED-16-1'],
            [
                'title' => 'Natural Birch',
                'attribute_name' => 'Color',
                'attribute_value' => 'Natural Birch',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-led-16.jpg',
                'attributes_json' => ['color_hex' => '#D7C4B7', 'color' => 'Natural Birch', 'image_url' => '/imges/decor/dec-shf-led-16.jpg'],
                'status' => 'active',
            ]
        );

        $p16->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-LED-16-2'],
            [
                'title' => 'Matte White',
                'attribute_name' => 'Color',
                'attribute_value' => 'Matte White',
                'cost_price_minor' => 19000,
                'retail_price_minor' => 29900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-led-16.jpg',
                'attributes_json' => ['color_hex' => '#FAFAFA', 'color' => 'Matte White', 'image_url' => '/imges/decor/dec-shf-led-16.jpg'],
                'status' => 'active',
            ]
        );

        // ── 17. DEC-SHF-CAN-17: Decorative Porcelain Storage Jar with Ai ─────────────────────
        $p17 = Product::updateOrCreate(
            ['slug' => 'decorative-porcelain-storage-jar-with-airtight-bamboo-lid'],
            [
                'sku' => 'DEC-SHF-CAN-17',
                'title' => 'Decorative Porcelain Storage Jar with Airtight Bamboo Lid',
                'description' => 'High-gloss ceramic canister with tactile geometric relief pattern and silicone-sealed natural bamboo lid. Elegant multipurpose counter organizer for cotton swabs, tea lights, spices, or bedside sundries.',
                'image_url' => '/imges/decor/dec-shf-can-17.jpg',
                'cost_price_minor' => 13000,
                'retail_price_minor' => 20900,
                'compare_at_price_minor' => 28900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 17,
                'material' => 'High-Fired Porcelain Ceramic & Natural Bamboo',
                'dimensions' => '10cm Diameter x 12cm Height (500ml)',
                'weight' => '410g',
                'attributes_json' => [
                    'Material' => 'High-Fired Porcelain Ceramic & Natural Bamboo',
                    'Dimensions' => '10cm Diameter x 12cm Height (500ml)',
                    'Weight' => '410g',
                    'amazon_asin' => 'B0B54SLVSX',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0B54SLVSX',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p17->collections()->sync([$collections['shelves-storage']->id]);
        $attachMedia($p17, ['/imges/decor/dec-shf-can-17.jpg', '/imges/decor/gallery-lighting-02.jpg', '/imges/decor/gallery-plants-01.jpg']);

        $p17->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-CAN-17-1'],
            [
                'title' => 'White Marble Relief',
                'attribute_name' => 'Color',
                'attribute_value' => 'White Marble',
                'cost_price_minor' => 13000,
                'retail_price_minor' => 20900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-can-17.jpg',
                'attributes_json' => ['color_hex' => '#EFEFEF', 'color' => 'White Marble', 'image_url' => '/imges/decor/dec-shf-can-17.jpg'],
                'status' => 'active',
            ]
        );

        $p17->variants()->updateOrCreate(
            ['sku' => 'DEC-SHF-CAN-17-2'],
            [
                'title' => 'Matte Charcoal Grid',
                'attribute_name' => 'Color',
                'attribute_value' => 'Charcoal',
                'cost_price_minor' => 13000,
                'retail_price_minor' => 20900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-shf-can-17.jpg',
                'attributes_json' => ['color_hex' => '#2B2B2B', 'color' => 'Charcoal', 'image_url' => '/imges/decor/dec-shf-can-17.jpg'],
                'status' => 'active',
            ]
        );

        // ── 18. DEC-WAL-MAC-18: Artisan Hand-Knotted Macramé Wall Tapest ─────────────────────
        $p18 = Product::updateOrCreate(
            ['slug' => 'artisan-hand-knotted-macrame-wall-tapestry-70cm'],
            [
                'sku' => 'DEC-WAL-MAC-18',
                'title' => 'Artisan Hand-Knotted Macramé Wall Tapestry (70cm)',
                'description' => 'Suspended on a genuine sanded natural beechwood dowel, this handmade macramé wall hanging features geometric diamond knotwork and long flowing fringe. Adds warmth, texture, and bohemian soul to any blank wall.',
                'image_url' => '/imges/decor/dec-wal-mac-18.jpg',
                'cost_price_minor' => 16000,
                'retail_price_minor' => 25900,
                'compare_at_price_minor' => 35900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 18,
                'material' => '100% Unbleached Cotton Rope & Beech Wood',
                'dimensions' => '40cm Dowel Width x 70cm Total Drop',
                'weight' => '360g',
                'attributes_json' => [
                    'Material' => '100% Unbleached Cotton Rope & Beech Wood',
                    'Dimensions' => '40cm Dowel Width x 70cm Total Drop',
                    'Weight' => '360g',
                    'amazon_asin' => 'B0GXRFTGNN',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0GXRFTGNN',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p18->collections()->sync([$collections['wall-art-decor']->id]);
        $attachMedia($p18, ['/imges/decor/dec-wal-mac-18.jpg', '/imges/decor/gallery-plants-02.jpg', '/imges/decor/gallery-lighting-02.jpg']);

        $p18->variants()->updateOrCreate(
            ['sku' => 'DEC-WAL-MAC-18-1'],
            [
                'title' => 'Natural Ecru Ivory',
                'attribute_name' => 'Color',
                'attribute_value' => 'Ecru Ivory',
                'cost_price_minor' => 16000,
                'retail_price_minor' => 25900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-wal-mac-18.jpg',
                'attributes_json' => ['color_hex' => '#F8F4EC', 'color' => 'Ecru Ivory', 'image_url' => '/imges/decor/dec-wal-mac-18.jpg'],
                'status' => 'active',
            ]
        );

        $p18->variants()->updateOrCreate(
            ['sku' => 'DEC-WAL-MAC-18-2'],
            [
                'title' => 'Sage Green Accent',
                'attribute_name' => 'Color',
                'attribute_value' => 'Sage Green',
                'cost_price_minor' => 16000,
                'retail_price_minor' => 25900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-wal-mac-18.jpg',
                'attributes_json' => ['color_hex' => '#9CAF88', 'color' => 'Sage Green', 'image_url' => '/imges/decor/dec-wal-mac-18.jpg'],
                'status' => 'active',
            ]
        );

        // ── 19. DEC-WAL-STK-19: 3D Self-Adhesive Brick Textured Wall Pan ─────────────────────
        $p19 = Product::updateOrCreate(
            ['slug' => '3d-self-adhesive-brick-textured-wall-panels-pack-of-10'],
            [
                'sku' => 'DEC-WAL-STK-19',
                'title' => '3D Self-Adhesive Brick Textured Wall Panels (Pack of 10)',
                'description' => 'Textured 3D faux exposed brick panels made from high-density sound-dampening waterproof XPE foam. Peel-and-stick industrial adhesive allows instant wall makeover in TV backdrops, headboards, or accent feature walls.',
                'image_url' => '/imges/decor/dec-wal-stk-19.jpg',
                'cost_price_minor' => 22000,
                'retail_price_minor' => 34900,
                'compare_at_price_minor' => 47900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 19,
                'material' => 'Waterproof High-Density XPE Elastic Foam',
                'dimensions' => '70cm x 77cm per Sheet (5.4 sq.m Total)',
                'weight' => '1.2kg (Pack of 10)',
                'attributes_json' => [
                    'Material' => 'Waterproof High-Density XPE Elastic Foam',
                    'Dimensions' => '70cm x 77cm per Sheet (5.4 sq.m Total)',
                    'Weight' => '1.2kg (Pack of 10)',
                    'amazon_asin' => 'B09R5NXCZ8',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B09R5NXCZ8',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p19->collections()->sync([$collections['wall-art-decor']->id]);
        $attachMedia($p19, ['/imges/decor/dec-wal-stk-19.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-plants-01.jpg']);

        $p19->variants()->updateOrCreate(
            ['sku' => 'DEC-WAL-STK-19-1'],
            [
                'title' => 'Matte White Brick',
                'attribute_name' => 'Color',
                'attribute_value' => 'White Brick',
                'cost_price_minor' => 22000,
                'retail_price_minor' => 34900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-wal-stk-19.jpg',
                'attributes_json' => ['color_hex' => '#FDFDFD', 'color' => 'White Brick', 'image_url' => '/imges/decor/dec-wal-stk-19.jpg'],
                'status' => 'active',
            ]
        );

        $p19->variants()->updateOrCreate(
            ['sku' => 'DEC-WAL-STK-19-2'],
            [
                'title' => 'Loft Industrial Grey',
                'attribute_name' => 'Color',
                'attribute_value' => 'Grey Brick',
                'cost_price_minor' => 22000,
                'retail_price_minor' => 34900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-wal-stk-19.jpg',
                'attributes_json' => ['color_hex' => '#7A7A7A', 'color' => 'Grey Brick', 'image_url' => '/imges/decor/dec-wal-stk-19.jpg'],
                'status' => 'active',
            ]
        );

        // ── 20. DEC-WAL-DRM-20: Handmade Native Feather & Turquoise Bead ─────────────────────
        $p20 = Product::updateOrCreate(
            ['slug' => 'handmade-native-feather-turquoise-bead-dream-catcher'],
            [
                'sku' => 'DEC-WAL-DRM-20',
                'title' => 'Handmade Native Feather & Turquoise Bead Dream Catcher',
                'description' => 'Traditional woven web hoop adorned with polished turquoise wooden beads and natural fumigated soft feathers. Believed to catch negative dreams while letting peaceful visions drift down the feather tendrils.',
                'image_url' => '/imges/decor/dec-wal-drm-20.jpg',
                'cost_price_minor' => 10000,
                'retail_price_minor' => 15900,
                'compare_at_price_minor' => 22900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 20,
                'material' => 'Iron Ring + Suede Cord + Natural Feathers + Turquoise Beads',
                'dimensions' => '16cm Hoop Diameter x 60cm Total Length',
                'weight' => '95g',
                'attributes_json' => [
                    'Material' => 'Iron Ring + Suede Cord + Natural Feathers + Turquoise Beads',
                    'Dimensions' => '16cm Hoop Diameter x 60cm Total Length',
                    'Weight' => '95g',
                    'amazon_asin' => 'B0B9ZYZT7D',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0B9ZYZT7D',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p20->collections()->sync([$collections['wall-art-decor']->id]);
        $attachMedia($p20, ['/imges/decor/dec-wal-drm-20.jpg', '/imges/decor/gallery-plants-02.jpg', '/imges/decor/gallery-lighting-01.jpg']);

        $p20->variants()->updateOrCreate(
            ['sku' => 'DEC-WAL-DRM-20-1'],
            [
                'title' => 'Pure Angelic White',
                'attribute_name' => 'Color',
                'attribute_value' => 'White',
                'cost_price_minor' => 10000,
                'retail_price_minor' => 15900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-wal-drm-20.jpg',
                'attributes_json' => ['color_hex' => '#FFFFFF', 'color' => 'White', 'image_url' => '/imges/decor/dec-wal-drm-20.jpg'],
                'status' => 'active',
            ]
        );

        $p20->variants()->updateOrCreate(
            ['sku' => 'DEC-WAL-DRM-20-2'],
            [
                'title' => 'Mystic Turquoise Blue',
                'attribute_name' => 'Color',
                'attribute_value' => 'Turquoise',
                'cost_price_minor' => 10000,
                'retail_price_minor' => 15900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-wal-drm-20.jpg',
                'attributes_json' => ['color_hex' => '#40E0D0', 'color' => 'Turquoise', 'image_url' => '/imges/decor/dec-wal-drm-20.jpg'],
                'status' => 'active',
            ]
        );

        // ── 21. DEC-WAL-CAN-21: Modern Minimalist Botanical Canvas Art P ─────────────────────
        $p21 = Product::updateOrCreate(
            ['slug' => 'modern-minimalist-botanical-canvas-art-prints-set-of-3'],
            [
                'sku' => 'DEC-WAL-CAN-21',
                'title' => 'Modern Minimalist Botanical Canvas Art Prints (Set of 3)',
                'description' => 'High-definition giclée art prints on archival waterproof textured artist canvas. Features minimalist sage botanical eucalyptus and abstract line sketches. Delivered ready to insert into standard picture frames.',
                'image_url' => '/imges/decor/dec-wal-can-21.jpg',
                'cost_price_minor' => 26000,
                'retail_price_minor' => 40900,
                'compare_at_price_minor' => 57900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 21,
                'material' => '320g Archival Cotton Canvas (Waterproof Pigment)',
                'dimensions' => '30cm x 40cm each (Set of 3 Panels)',
                'weight' => '180g',
                'attributes_json' => [
                    'Material' => '320g Archival Cotton Canvas (Waterproof Pigment)',
                    'Dimensions' => '30cm x 40cm each (Set of 3 Panels)',
                    'Weight' => '180g',
                    'amazon_asin' => 'B09BD2Q5TS',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B09BD2Q5TS',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p21->collections()->sync([$collections['wall-art-decor']->id]);
        $attachMedia($p21, ['/imges/decor/dec-wal-can-21.jpg', '/imges/decor/gallery-lighting-02.jpg', '/imges/decor/gallery-plants-02.jpg']);

        // ── 22. DEC-FIG-NAU-22: Coastal Handcrafted Wooden Sailboat Tabl ─────────────────────
        $p22 = Product::updateOrCreate(
            ['slug' => 'coastal-handcrafted-wooden-sailboat-tabletop-centerpiece'],
            [
                'sku' => 'DEC-FIG-NAU-22',
                'title' => 'Coastal Handcrafted Wooden Sailboat Tabletop Centerpiece',
                'description' => 'Charming Mediterranean wooden sailboat model with stitched cotton canvas sails, nautical twine rigging, and distressed sea-salt weathered finish. Imparts relaxed coastal tranquility to shelves, mantels, and sideboards.',
                'image_url' => '/imges/decor/dec-fig-nau-22.jpg',
                'cost_price_minor' => 15000,
                'retail_price_minor' => 23900,
                'compare_at_price_minor' => 33900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 22,
                'material' => 'Solid Pinewood, Linen Sails & Jute Cordage',
                'dimensions' => '23cm Height x 16cm Length x 4cm Width',
                'weight' => '220g',
                'attributes_json' => [
                    'Material' => 'Solid Pinewood, Linen Sails & Jute Cordage',
                    'Dimensions' => '23cm Height x 16cm Length x 4cm Width',
                    'Weight' => '220g',
                    'amazon_asin' => 'B0DKYFJRHY',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0DKYFJRHY',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p22->collections()->sync([$collections['figurines-accents']->id]);
        $attachMedia($p22, ['/imges/decor/dec-fig-nau-22.jpg', '/imges/decor/gallery-lighting-01.jpg', '/imges/decor/gallery-plants-01.jpg']);

        $p22->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-NAU-22-1'],
            [
                'title' => 'Nautical Navy & White',
                'attribute_name' => 'Color',
                'attribute_value' => 'Navy/White',
                'cost_price_minor' => 15000,
                'retail_price_minor' => 23900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-nau-22.jpg',
                'attributes_json' => ['color_hex' => '#1B3F8B', 'color' => 'Navy/White', 'image_url' => '/imges/decor/dec-fig-nau-22.jpg'],
                'status' => 'active',
            ]
        );

        $p22->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-NAU-22-2'],
            [
                'title' => 'Coastal Sky Blue & White',
                'attribute_name' => 'Color',
                'attribute_value' => 'Sky Blue',
                'cost_price_minor' => 15000,
                'retail_price_minor' => 23900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-nau-22.jpg',
                'attributes_json' => ['color_hex' => '#87CEEB', 'color' => 'Sky Blue', 'image_url' => '/imges/decor/dec-fig-nau-22.jpg'],
                'status' => 'active',
            ]
        );

        // ── 23. DEC-FIG-MIR-23: Sunburst Geometric Metal Accent Round Wa ─────────────────────
        $p23 = Product::updateOrCreate(
            ['slug' => 'sunburst-geometric-metal-accent-round-wall-mirror-40cm'],
            [
                'sku' => 'DEC-FIG-MIR-23',
                'title' => 'Sunburst Geometric Metal Accent Round Wall Mirror (40cm)',
                'description' => 'Star-pattern radiating wrought iron wire frame cradling an ultra-reflective distortion-free silver mirror. Instantly visually expands smaller rooms, reflects ambient lighting, and acts as jewelry for your entryway wall.',
                'image_url' => '/imges/decor/dec-fig-mir-23.jpg',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'compare_at_price_minor' => 77900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 23,
                'material' => 'Forged Wrought Iron & HD Silver Mirror Glass',
                'dimensions' => '40cm Outer Diameter (20cm Center Mirror)',
                'weight' => '980g',
                'attributes_json' => [
                    'Material' => 'Forged Wrought Iron & HD Silver Mirror Glass',
                    'Dimensions' => '40cm Outer Diameter (20cm Center Mirror)',
                    'Weight' => '980g',
                    'amazon_asin' => 'B0GWRKBLG4',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0GWRKBLG4',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p23->collections()->sync([$collections['figurines-accents']->id]);
        $attachMedia($p23, ['/imges/decor/dec-fig-mir-23.jpg', '/imges/decor/gallery-plants-02.jpg', '/imges/decor/gallery-lighting-01.jpg']);

        $p23->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-MIR-23-1'],
            [
                'title' => 'Imperial Brushed Gold',
                'attribute_name' => 'Color',
                'attribute_value' => 'Gold',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-mir-23.jpg',
                'attributes_json' => ['color_hex' => '#CFB53B', 'color' => 'Gold', 'image_url' => '/imges/decor/dec-fig-mir-23.jpg'],
                'status' => 'active',
            ]
        );

        $p23->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-MIR-23-2'],
            [
                'title' => 'Sleek Matte Black',
                'attribute_name' => 'Color',
                'attribute_value' => 'Black',
                'cost_price_minor' => 34000,
                'retail_price_minor' => 53900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-mir-23.jpg',
                'attributes_json' => ['color_hex' => '#1A1A1A', 'color' => 'Black', 'image_url' => '/imges/decor/dec-fig-mir-23.jpg'],
                'status' => 'active',
            ]
        );

        // ── 24. DEC-FIG-BOK-24: Luxury Faux Designer Hardcover Book Stac ─────────────────────
        $p24 = Product::updateOrCreate(
            ['slug' => 'luxury-faux-designer-hardcover-book-stack-storage-box'],
            [
                'sku' => 'DEC-FIG-BOK-24',
                'title' => 'Luxury Faux Designer Hardcover Book Stack with Storage Box',
                'description' => 'Impeccably printed decorative faux coffee table books with embossed gold foil typography. Hollow concealed storage box design allows discreet storage of jewelry, remote controls, and keys while staging a designer aesthetic.',
                'image_url' => '/imges/decor/dec-fig-bok-24.jpg',
                'cost_price_minor' => 20000,
                'retail_price_minor' => 31900,
                'compare_at_price_minor' => 44900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 24,
                'material' => 'Heavyweight Laminated Cardboard with Magnetic Clasp',
                'dimensions' => '27cm x 17cm x 4.5cm per Book (Set of 3)',
                'weight' => '650g',
                'attributes_json' => [
                    'Material' => 'Heavyweight Laminated Cardboard with Magnetic Clasp',
                    'Dimensions' => '27cm x 17cm x 4.5cm per Book (Set of 3)',
                    'Weight' => '650g',
                    'amazon_asin' => 'B01MR4Y0CZ',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B01MR4Y0CZ',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p24->collections()->sync([$collections['figurines-accents']->id]);
        $attachMedia($p24, ['/imges/decor/dec-fig-bok-24.jpg', '/imges/decor/gallery-lighting-02.jpg', '/imges/decor/gallery-plants-02.jpg']);

        $p24->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-BOK-24-1'],
            [
                'title' => 'Monochrome Fashion White',
                'attribute_name' => 'Color',
                'attribute_value' => 'White/Black',
                'cost_price_minor' => 20000,
                'retail_price_minor' => 31900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-bok-24.jpg',
                'attributes_json' => ['color_hex' => '#EEEEEE', 'color' => 'White/Black', 'image_url' => '/imges/decor/dec-fig-bok-24.jpg'],
                'status' => 'active',
            ]
        );

        $p24->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-BOK-24-2'],
            [
                'title' => 'Architectural Beige & Gold',
                'attribute_name' => 'Color',
                'attribute_value' => 'Beige/Gold',
                'cost_price_minor' => 20000,
                'retail_price_minor' => 31900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-bok-24.jpg',
                'attributes_json' => ['color_hex' => '#E6D7B9', 'color' => 'Beige/Gold', 'image_url' => '/imges/decor/dec-fig-bok-24.jpg'],
                'status' => 'active',
            ]
        );

        // ── 25. DEC-FIG-OST-25: Handcrafted Luxury Ceramic Feather Cente ─────────────────────
        $p25 = Product::updateOrCreate(
            ['slug' => 'handcrafted-luxury-ceramic-feather-centerpiece-vase'],
            [
                'sku' => 'DEC-FIG-OST-25',
                'title' => 'Handcrafted Luxury Ceramic Feather Centerpiece Vase',
                'description' => 'Artisan statement sculptural ceramic vessel paired with authentic naturally molted ostrich plume feathers. A show-stopping luxury centerpiece that anchors formal dining tables, entryway consoles, and luxury suites.',
                'image_url' => '/imges/decor/dec-fig-ost-25.jpg',
                'cost_price_minor' => 76000,
                'retail_price_minor' => 119900,
                'compare_at_price_minor' => 167900,
                'currency' => 'EGP',
                'inventory' => 65,
                'status' => 'active',
                'sort_order' => 25,
                'material' => 'Hand-Thrown Glazed Ceramic Pottery & Natural Feathers',
                'dimensions' => '28cm Vase Height (55cm Total Display Height)',
                'weight' => '1.75kg',
                'attributes_json' => [
                    'Material' => 'Hand-Thrown Glazed Ceramic Pottery & Natural Feathers',
                    'Dimensions' => '28cm Vase Height (55cm Total Display Height)',
                    'Weight' => '1.75kg',
                    'amazon_asin' => 'B0GDP8KC1F',
                    'supplier_product_url' => 'https://www.amazon.eg/dp/B0GDP8KC1F',
                    'Amazon Store Offer' => 'Save with Atelier Exclusive Discount',
                ],
            ]
        );
        $p25->collections()->sync([$collections['figurines-accents']->id]);
        $attachMedia($p25, ['/imges/decor/dec-fig-ost-25.jpg', '/imges/decor/gallery-plants-01.jpg', '/imges/decor/gallery-lighting-02.jpg']);

        $p25->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-OST-25-1'],
            [
                'title' => 'Ivory Blanc & Champagne Plumes',
                'attribute_name' => 'Color',
                'attribute_value' => 'Ivory/Champagne',
                'cost_price_minor' => 76000,
                'retail_price_minor' => 119900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-ost-25.jpg',
                'attributes_json' => ['color_hex' => '#FFFDD0', 'color' => 'Ivory/Champagne', 'image_url' => '/imges/decor/dec-fig-ost-25.jpg'],
                'status' => 'active',
            ]
        );

        $p25->variants()->updateOrCreate(
            ['sku' => 'DEC-FIG-OST-25-2'],
            [
                'title' => 'Noir Matte & Jet Plumes',
                'attribute_name' => 'Color',
                'attribute_value' => 'Matte Noir',
                'cost_price_minor' => 76000,
                'retail_price_minor' => 119900,
                'inventory' => 20,
                'image_url' => '/imges/decor/dec-fig-ost-25.jpg',
                'attributes_json' => ['color_hex' => '#0D0D0D', 'color' => 'Matte Noir', 'image_url' => '/imges/decor/dec-fig-ost-25.jpg'],
                'status' => 'active',
            ]
        );

    }
}
