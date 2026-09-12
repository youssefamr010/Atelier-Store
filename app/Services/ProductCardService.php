<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Collection as ProductCollection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class ProductCardService
{
    /**
     * Map common color names to HEX codes for luxury swatch circles.
     */
    protected static array $colorHexMap = [
        'stone grey' => '#878681',
        'obsidian black' => '#1A1A1A',
        'cognac tan' => '#A0522D',
        'midnight navy' => '#191970',
        'stealth matte black' => '#222222',
        'brushed titanium' => '#8E9296',
        'gunmetal grey' => '#4A4E51',
        'saddle brown' => '#8B4513',
        'espresso black' => '#1B140E',
        'burgundy wine' => '#800020',
        'matte carbon weave' => '#2B2B2B',
        'forged carbon grain' => '#383838',
        'desert tan' => '#D2B48C',
        'carbon obsidian' => '#111111',
        'matte obsidian' => '#1A1A1A',
        'titanium silver' => '#C0C0C0',
        'vintage brown' => '#7A4926',
        'classic black' => '#111111',
        'navy blue' => '#000080',
        'executive brown gift set' => '#5C3A21',
        'obsidian black gift set' => '#151515',
        'rose pink' => '#E8A3B7',
        'midnight blue' => '#191970',
    ];

    /**
     * Transform a collection of products into individual display cards (1 per variant or 1 per base product).
     * Strictly avoids duplicate cards: if a product has active variants, ONLY the variants are rendered.
     */
    public static function toDisplayCards($products, bool $isArabic = false): Collection
    {
        $cards = collect();

        if ($products instanceof \Illuminate\Database\Eloquent\Collection) {
            $products->loadMissing([
                'variants' => fn ($q) => $q->whereIn('status', ['published', 'active'])->with('mediaAssets'),
                'mediaAssets',
            ]);
        }

        foreach ($products as $product) {
            $activeVariants = $product->variants ? $product->variants->whereIn('status', ['published', 'active'])->values() : collect();

            $baseImg = !empty($product->image_url)
                ? self::formatAssetUrl($product->image_url)
                : ($product->mediaAssets && $product->mediaAssets->first()
                    ? self::formatAssetUrl($product->mediaAssets->first()->url ?: ('/storage/media/'.$product->mediaAssets->first()->filename))
                    : '');

            $displayMode = $product->catalog_display_mode ?: 'separate_cards';

            // Build rich swatches array for all active variants
            $swatches = $activeVariants->map(function ($v) use ($product, $isArabic, $baseImg) {
                $varImg = null;
                $vMedia = $v->relationLoaded('mediaAssets') ? $v->mediaAssets : collect();

                if (!empty($v->image_url)) {
                    $varImg = ProductCardService::formatAssetUrl($v->image_url);
                } elseif (!empty($v->attributes_json['image_url'])) {
                    $varImg = ProductCardService::formatAssetUrl($v->attributes_json['image_url']);
                } elseif ($vMedia->isNotEmpty()) {
                    $firstAsset = $vMedia->first();
                    $varImg = ProductCardService::formatAssetUrl($firstAsset->url ?: ('/storage/media/'.$firstAsset->filename));
                }

                if (empty($varImg)) {
                    $varImg = $baseImg;
                }

                $hoverImg = $varImg;
                if ($vMedia->count() > 1) {
                    $secondAsset = $vMedia->skip(1)->first();
                    $hoverImg = ProductCardService::formatAssetUrl($secondAsset->url ?: ('/storage/media/'.$secondAsset->filename));
                }

                $titleLower = strtolower(trim((string) $v->title));
                $colorHex = $v->attributes_json['color_hex'] 
                    ?? $v->attributes_json['hex'] 
                    ?? self::$colorHexMap[$titleLower] 
                    ?? '#1A1A1A';

                $effectivePriceMinor = $v->effective_price_minor ?: (int) $product->retail_price_minor;
                $formattedPrice = $effectivePriceMinor ? number_format($effectivePriceMinor / 100, 0) . ' ' . ($isArabic ? 'ج.م' : 'EGP') : '—';

                return [
                    'variant_id'      => $v->id,
                    'title'           => $v->title,
                    'color_hex'       => $colorHex,
                    'image'           => $varImg,
                    'hover_image'     => $hoverImg,
                    'price_formatted' => $formattedPrice,
                    'price_minor'     => $effectivePriceMinor,
                    'in_stock'        => (int) $v->inventory > 0,
                    'inventory'       => (int) $v->inventory,
                    'url'             => route('products.show', ['slug' => $product->slug, 'variant' => $v->id]),
                ];
            })->values()->all();

            if ($displayMode === 'single_card' || $activeVariants->isEmpty()) {
                // SINGLE CARD MODE: Exactly 1 card per product with interactive live swatches
                $firstVar = $activeVariants->first();
                $firstSwatch = !empty($swatches) ? $swatches[0] : null;

                $initialImg = $firstSwatch ? $firstSwatch['image'] : $baseImg;
                $initialHover = $firstSwatch ? $firstSwatch['hover_image'] : $baseImg;
                $initialPriceMinor = $firstSwatch ? $firstSwatch['price_minor'] : (int) $product->retail_price_minor;
                $initialPriceFormatted = $firstSwatch ? $firstSwatch['price_formatted'] : ($product->retail_price_minor ? number_format($product->retail_price_minor / 100, 0) . ' ' . ($isArabic ? 'ج.م' : 'EGP') : '—');
                $initialInStock = $firstSwatch ? $firstSwatch['in_stock'] : ((int) $product->inventory > 0);
                $initialVariantTitle = $firstSwatch ? $firstSwatch['title'] : null;
                $initialVariantId = $firstSwatch ? $firstSwatch['variant_id'] : null;
                $initialUrl = $firstSwatch ? $firstSwatch['url'] : route('products.show', ['slug' => $product->slug]);

                $cards->push((object) [
                    'card_id'                => 'p' . $product->id,
                    'mode'                   => 'single_card',
                    'product_id'             => $product->id,
                    'variant_id'             => $initialVariantId,
                    'product'                => $product,
                    'variant'                => $firstVar,
                    'title'                  => $product->title,
                    'variant_title'          => $initialVariantTitle,
                    'display_title'          => $product->title . ($initialVariantTitle ? ' — ' . $initialVariantTitle : ''),
                    'slug'                   => $product->slug,
                    'url'                    => $initialUrl,
                    'image'                  => $initialImg,
                    'hover_image'            => $initialHover,
                    'price_minor'            => $initialPriceMinor,
                    'price_formatted'        => $initialPriceFormatted,
                    'in_stock'               => $initialInStock,
                    'inventory'              => $firstSwatch ? $firstSwatch['inventory'] : (int) $product->inventory,
                    'color_hex'              => $firstSwatch ? $firstSwatch['color_hex'] : null,
                    'swatches'               => $swatches,
                    'sibling_variants_count' => count($swatches),
                    'collection_ids'         => $product->collections ? $product->collections->pluck('id')->all() : [],
                ]);
            } else {
                // SEPARATE CARDS MODE: 1 card per variant with sibling color preview swatches
                foreach ($swatches as $swatch) {
                    $cards->push((object) [
                        'card_id'                => 'p' . $product->id . '-v' . $swatch['variant_id'],
                        'mode'                   => 'separate_cards',
                        'product_id'             => $product->id,
                        'variant_id'             => $swatch['variant_id'],
                        'product'                => $product,
                        'variant'                => $activeVariants->firstWhere('id', $swatch['variant_id']),
                        'title'                  => $product->title,
                        'variant_title'          => $swatch['title'],
                        'display_title'          => $product->title . ' — ' . $swatch['title'],
                        'slug'                   => $product->slug,
                        'url'                    => $swatch['url'],
                        'image'                  => $swatch['image'],
                        'hover_image'            => $swatch['hover_image'],
                        'price_minor'            => $swatch['price_minor'],
                        'price_formatted'        => $swatch['price_formatted'],
                        'in_stock'               => $swatch['in_stock'],
                        'inventory'              => $swatch['inventory'],
                        'color_hex'              => $swatch['color_hex'],
                        'swatches'               => $swatches,
                        'sibling_variants_count' => count($swatches),
                        'collection_ids'         => $product->collections ? $product->collections->pluck('id')->all() : [],
                    ]);
                }
            }
        }

        return $cards;
    }

    /**
     * Compute exact display card counts for each collection category and "All Pieces".
     */
    public static function getCategoryCardCounts(): array
    {
        $allProducts = Product::active()
            ->with(['variants' => fn ($q) => $q->where('status', 'active')->with('mediaAssets'), 'collections', 'mediaAssets'])
            ->get();

        $allCards = self::toDisplayCards($allProducts);
        $totalAll = $allCards->count();

        $counts = [
            'all' => $totalAll,
        ];

        $collections = ProductCollection::where('status', 'active')->get();
        foreach ($collections as $col) {
            $colCardsCount = $allCards->filter(function ($card) use ($col) {
                return in_array($col->id, $card->collection_ids, true);
            })->count();

            $counts[$col->slug] = $colCardsCount;
            $counts[$col->id]   = $colCardsCount;
        }

        return $counts;
    }

    /**
     * Safely format and URL-encode asset paths for browser consumption.
     */
    public static function formatAssetUrl(?string $url): string
    {
        if (empty($url)) {
            return 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800&q=80';
        }

        // If local loopback URL was saved in database, convert to request host
        if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/(.*)$#i', $url, $matches)) {
            return url($matches[3]);
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'data:')) {
            return $url;
        }

        $cleanPath = ltrim($url, '/');
        $segments = explode('/', $cleanPath);
        $encodedSegments = array_map('rawurlencode', $segments);

        return url(implode('/', $encodedSegments));
    }
}
