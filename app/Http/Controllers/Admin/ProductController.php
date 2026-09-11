<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Collection;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\MediaStorageService;
use App\Services\ProductSkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly MediaStorageService $mediaStorage) {}

    public function index(Request $request): View
    {
        $query = Product::with(['collections', 'mediaAssets', 'variants'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($vq) use ($search) {
                        $vq->where('title', 'like', "%{$search}%")
                           ->orWhere('sku', 'like', "%{$search}%")
                           ->orWhere('attributes_json->color', 'like', "%{$search}%")
                           ->orWhere('attributes_json->color_hex', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('collection_id')) {
            $query->whereHas('collections', function ($q) use ($request) {
                $q->where('collections.id', $request->input('collection_id'));
            });
        }

        $products = $query->paginate(15)->withQueryString();
        $collections = Collection::orderBy('sort_order')->get();

        return view('admin.products.index', compact('products', 'collections'));
    }

    public function create(): View
    {
        $collections = Collection::orderBy('sort_order')->get();

        return view('admin.products.create', compact('collections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'retail_price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'inventory' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:active,draft,archived',
            'description' => 'nullable|string',
            'material' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:100',
            'dimensions' => 'nullable|string|max:100',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'supplier_product_url' => 'nullable|url|max:2000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'collection_ids' => 'nullable|array',
            'collection_ids.*' => 'exists:collections,id',
            'variants' => 'nullable|array',
            'variants.*.title' => 'nullable|string|max:100',
            'variants.*.color_hex' => 'nullable|string|max:20',
            'variants.*.price_override' => 'nullable|numeric|min:0',
            'variants.*.inventory' => 'nullable|integer|min:0',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $slug = Str::slug($request->title);
        $base = $slug;
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        $coverAssetId = null;
        $coverUrl = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = 'cover-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('media', $filename, 'public');
            $coverUrl = '/storage/'.$path;

            $coverAsset = MediaAsset::create([
                'type' => 'image',
                'url' => $coverUrl,
                'filename' => $filename,
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
            $coverAssetId = $coverAsset->id;
        }

        $collectionIds = $request->input('collection_ids', []);
        $primaryCollection = Collection::query()->whereIn('id', $collectionIds)->orderBy('sort_order')->first();
        $sku = app(ProductSkuService::class)->make($request->title, $primaryCollection);

        $product = Product::create([
            'title' => $request->title,
            'slug' => $slug,
            'sku' => $sku,
            'description' => $request->description,
            'material' => $request->material,
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'image_url' => $coverUrl,
            'retail_price_minor' => (int) round(((float) $request->retail_price) * 100),
            'compare_at_price_minor' => $request->filled('compare_at_price') ? (int) round(((float) $request->compare_at_price) * 100) : null,
            'cost_price_minor' => $request->filled('cost_price') ? (int) round(((float) $request->cost_price) * 100) : 0,
            'inventory' => (int) $request->inventory,
            'low_stock_threshold' => $request->filled('low_stock_threshold') ? (int) $request->low_stock_threshold : null,
            'status' => $request->status,
            'currency' => 'EGP',
            'attributes_json' => array_filter([
                'supplier_product_url' => $request->input('supplier_product_url'),
            ]),
        ]);

        // Attach cover image to product media assets
        if ($coverAssetId) {
            $product->mediaAssets()->attach($coverAssetId, ['group' => 'cover', 'sort_order' => 0]);
        }

        // Sync Collections
        if ($request->filled('collection_ids')) {
            $product->collections()->sync($collectionIds);
        }

        // Upload Gallery Images
        if ($request->hasFile('gallery_images')) {
            $position = 1;
            foreach ($request->file('gallery_images') as $galleryFile) {
                if ($galleryFile->isValid()) {
                    $filename = 'gallery-'.time().'-'.Str::random(8).'.'.$galleryFile->getClientOriginalExtension();
                    $path = $galleryFile->storeAs('media', $filename, 'public');
                    $url = '/storage/'.$path;

                    $asset = MediaAsset::create([
                        'type' => 'image',
                        'url' => $url,
                        'filename' => $filename,
                        'mime_type' => $galleryFile->getClientMimeType(),
                        'size_bytes' => $galleryFile->getSize(),
                    ]);

                    $product->mediaAssets()->attach($asset->id, ['group' => 'gallery', 'sort_order' => $position++]);
                }
            }
        }

        // Create Color Variants
        if ($request->filled('has_color_variants') && $request->filled('variants')) {
            foreach ($request->input('variants', []) as $index => $varData) {
                $colorName = trim((string) ($varData['title'] ?? ''));
                if (empty($colorName)) {
                    continue;
                }

                $varImgUrl = null;
                if ($request->hasFile("variants.{$index}.image")) {
                    $vFile = $request->file("variants.{$index}.image");
                    $vFilename = 'variant-'.time().'-'.Str::random(8).'.'.$vFile->getClientOriginalExtension();
                    $vPath = $vFile->storeAs('media', $vFilename, 'public');
                    $varImgUrl = '/storage/'.$vPath;

                    MediaAsset::create([
                        'type' => 'image',
                        'url' => $varImgUrl,
                        'filename' => $vFilename,
                        'mime_type' => $vFile->getClientMimeType(),
                        'size_bytes' => $vFile->getSize(),
                    ]);
                }

                $colorHex = $varData['color_hex'] ?? '#000000';
                $priceOverride = ! empty($varData['price_override']) ? (int) round(((float) $varData['price_override']) * 100) : null;
                $varStock = isset($varData['inventory']) && $varData['inventory'] !== '' ? (int) $varData['inventory'] : (int) $product->inventory;

                $varSlug = Str::slug($colorName);
                if (empty($varSlug)) {
                    $varSlug = 'VAR-'.($index + 1);
                }
                $baseVarSku = strtoupper($product->sku.'-'.$varSlug);
                $varSku = $baseVarSku;
                $c = 1;
                while (ProductVariant::where('sku', $varSku)->exists()) {
                    $varSku = $baseVarSku.'-'.$c++;
                }

                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $varSku,
                    'title' => $colorName,
                    'attribute_name' => 'Color',
                    'attribute_value' => $colorName,
                    'price_override_minor' => $priceOverride,
                    'inventory' => $varStock,
                    'image_url' => $varImgUrl,
                    'attributes_json' => [
                        'color' => $colorName,
                        'color_hex' => $colorHex,
                        'image_url' => $varImgUrl,
                    ],
                    'status' => 'active',
                ]);
            }
        }

        AuditLog::log('product.create', 'product', $product->id, "Created product: {$product->title} (SKU: {$product->sku})");

        return redirect()->route('admin.products.edit', $product->id)
            ->with('success', "Product \"{$product->title}\" created successfully with all images, variants and collection assignments!");
    }

    public function edit(int $id): View
    {
        $product = Product::with(['collections', 'mediaAssets', 'variants.mediaAssets'])->findOrFail($id);
        $collections = Collection::orderBy('sort_order')->get();

        return view('admin.products.edit', compact('product', 'collections'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'retail_price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'inventory' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:active,draft,archived',
            'catalog_display_mode' => 'nullable|in:single_card,separate_cards',
            'description' => 'nullable|string',
            'material' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:100',
            'dimensions' => 'nullable|string|max:100',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'supplier_product_url' => 'nullable|url|max:2000',
            'collection_ids' => 'nullable|array',
            'collection_ids.*' => 'exists:collections,id',
        ]);

        $productAttributes = $product->attributes_json ?? [];
        $productAttributes['supplier_product_url'] = $request->input('supplier_product_url');
        if (empty($productAttributes['supplier_product_url'])) {
            unset($productAttributes['supplier_product_url']);
        }

        $collectionIds = $request->input('collection_ids', []);
        $primaryCollection = Collection::query()->whereIn('id', $collectionIds)->orderBy('sort_order')->first()
            ?? $product->collections()->orderBy('sort_order')->first();
        $sku = app(ProductSkuService::class)->make($request->title, $primaryCollection, $product->id);

        $product->update([
            'title' => $request->title,
            'sku' => $sku,
            'description' => $request->description,
            'material' => $request->material,
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'retail_price_minor' => (int) round(((float) $request->retail_price) * 100),
            'compare_at_price_minor' => $request->filled('compare_at_price') ? (int) round(((float) $request->compare_at_price) * 100) : null,
            'cost_price_minor' => $request->filled('cost_price') ? (int) round(((float) $request->cost_price) * 100) : 0,
            'inventory' => (int) $request->inventory,
            'low_stock_threshold' => $request->filled('low_stock_threshold') ? (int) $request->low_stock_threshold : null,
            'status' => $request->status,
            'catalog_display_mode' => $request->input('catalog_display_mode', $product->catalog_display_mode ?? 'separate_cards'),
            'is_new'         => $request->boolean('is_new'),
            'is_bestseller'  => $request->boolean('is_bestseller'),
            'attributes_json' => $productAttributes,
        ]);

        if ($request->has('collection_ids')) {
            $product->collections()->sync($collectionIds);
        }
        app(ProductSkuService::class)->syncVariantSkus($product->fresh());

        AuditLog::log('product.update', 'product', $product->id, "Updated product: {$product->title}");

        return back()->with('success', "Product \"{$product->title}\" updated successfully!");
    }

    public function toggleStatus(int $id)
    {
        $product = Product::findOrFail($id);
        $product->status = $product->status === 'active' ? 'draft' : 'active';
        $product->save();

        AuditLog::log('product.toggle_status', 'product', $product->id, "Changed status of {$product->title} to {$product->status}");

        return back()->with('success', "Product is now {$product->status}.");
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $title = $product->title;
        $product->collections()->detach();
        $product->mediaAssets()->detach();
        $product->variants()->delete();
        $product->delete();

        AuditLog::log('product.delete', 'product', $id, "Deleted product: {$title}");

        return redirect()->route('admin.products.index')->with('success', "Product \"{$title}\" permanently deleted.");
    }

    // Cover Image Replacement
    public function replaceImage(Request $request, int $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $product = Product::findOrFail($id);
        $file = $request->file('image');
        $filename = 'cover-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media', $filename, 'public');
        $url = '/storage/'.$path;

        $asset = MediaAsset::create([
            'type' => 'image',
            'url' => $url,
            'filename' => $filename,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        $product->image_url = $url;
        $product->save();

        $oldCovers = $product->mediaAssets()->wherePivot('group', 'cover')->pluck('media_assets.id')->toArray();
        if (! empty($oldCovers)) {
            $product->mediaAssets()->detach($oldCovers);
        }
        $product->mediaAssets()->attach($asset->id, ['group' => 'cover', 'sort_order' => 0]);

        AuditLog::log('product.image_replace', 'product', $product->id, "Replaced cover image for {$product->title}");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'image_url' => $url]);
        }

        return back()->with('success', 'Cover image updated successfully!');
    }

    // Gallery Image Upload
    public function uploadGallery(Request $request, int $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $product = Product::findOrFail($id);
        $file = $request->file('image');
        $filename = 'gallery-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media', $filename, 'public');
        $url = '/storage/'.$path;

        $asset = MediaAsset::create([
            'type' => 'image',
            'url' => $url,
            'filename' => $filename,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        $nextOrder = (int) $product->mediaAssets()->max('sort_order') + 1;
        $product->mediaAssets()->attach($asset->id, ['group' => 'gallery', 'sort_order' => $nextOrder]);

        AuditLog::log('product.gallery_upload', 'product', $product->id, "Added gallery image for {$product->title}");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'image_url' => $url, 'asset_id' => $asset->id]);
        }

        return back()->with('success', 'Gallery image added successfully!');
    }

    // Detach / Delete Media from Product
    public function deleteMedia(int $productId, int $assetId)
    {
        $product = Product::findOrFail($productId);
        $product->mediaAssets()->detach($assetId);

        $asset = MediaAsset::find($assetId);
        if ($asset) {
            $usage = DB::table('mediables')->where('media_asset_id', $assetId)->count();
            if ($usage === 0) {
                if (Storage::disk('public')->exists('media/'.$asset->filename)) {
                    Storage::disk('public')->delete('media/'.$asset->filename);
                }
                $asset->delete();
            }
        }

        AuditLog::log('product.media_delete', 'product', $productId, "Removed media asset #{$assetId} from {$product->title}");

        return back()->with('success', 'Image removed from product gallery.');
    }

    // Update Variant
    public function updateVariant(Request $request, int $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:100',
            'attribute_name' => 'nullable|string|max:100',
            'price_override' => 'nullable|numeric|min:0',
            'inventory' => 'required|integer|min:0',
            'color_hex' => 'nullable|string|max:20',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:'.config('media.max_image_size_kb', 5120),
            'variant_gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:'.config('media.max_image_size_kb', 5120),
        ]);

        $imageUrl = $variant->image_url;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'variant-cover-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('media', $filename, 'public');
            $imageUrl = '/storage/'.$path;

            $coverAsset = MediaAsset::create([
                'type' => 'image',
                'url' => $imageUrl,
                'filename' => $filename,
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
            $variant->mediaAssets()->attach($coverAsset->id, ['group' => 'cover', 'sort_order' => 0]);
        } elseif ($request->input('image_url') === '__DEFAULT__' || $request->input('image_url') === '__CLEAR__') {
            $imageUrl = null;
        } elseif ($request->filled('image_url')) {
            $imageUrl = $request->input('image_url');
        }

        $priceOverrideMinor = $request->filled('price_override')
            ? (int) round(((float) $request->input('price_override')) * 100)
            : null;

        $colorHex = $request->input('color_hex', $variant->attributes_json['color_hex'] ?? '#000000');

        $variant->update([
            'title' => $request->input('title'),
            'attribute_name' => $request->input('attribute_name', $variant->attribute_name ?? 'Color'),
            'attribute_value' => $request->input('title'),
            'price_override_minor' => $priceOverrideMinor,
            'inventory' => (int) $request->input('inventory'),
            'image_url' => $imageUrl,
            'attributes_json' => [
                'color' => $request->input('title'),
                'color_hex' => $colorHex,
                'image_url' => $imageUrl,
            ],
        ]);

        // Upload any new gallery images specifically for this variant
        if ($request->hasFile('variant_gallery_images')) {
            $nextOrder = (int) $variant->mediaAssets()->max('sort_order') + 1;
            foreach ($request->file('variant_gallery_images') as $gFile) {
                if ($gFile->isValid()) {
                    $filename = 'variant-gallery-'.time().'-'.Str::random(8).'.'.$gFile->getClientOriginalExtension();
                    $path = $gFile->storeAs('media', $filename, 'public');
                    $url = '/storage/'.$path;

                    $asset = MediaAsset::create([
                        'type' => 'image',
                        'url' => $url,
                        'filename' => $filename,
                        'mime_type' => $gFile->getClientMimeType(),
                        'size_bytes' => $gFile->getSize(),
                    ]);

                    $variant->mediaAssets()->attach($asset->id, ['group' => 'gallery', 'sort_order' => $nextOrder++]);
                }
            }
        }

        if (empty($variant->image_url) && $variant->mediaAssets()->exists()) {
            $firstAsset = $variant->mediaAssets()->orderByPivot('sort_order')->first();
            if ($firstAsset) {
                $firstUrl = $firstAsset->url ?: ('/storage/media/'.$firstAsset->filename);
                $variant->update([
                    'image_url' => $firstUrl,
                    'attributes_json' => array_merge($variant->attributes_json ?? [], ['image_url' => $firstUrl]),
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $galleryAssets = $variant->mediaAssets()->get()->map(function ($asset) use ($variant) {
                $assetSrc = $asset->url ?: ('/storage/media/' . $asset->filename);
                return [
                    'id' => $asset->id,
                    'url' => str_starts_with($assetSrc, 'http') ? $assetSrc : url($assetSrc),
                    'filename' => $asset->filename,
                    'delete_url' => route('admin.variants.delete-media', ['variantId' => $variant->id, 'assetId' => $asset->id]),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Updated variant: {$variant->title}",
                'variant' => [
                    'id' => $variant->id,
                    'title' => $variant->title,
                    'image_url' => $variant->image_url ? (str_starts_with($variant->image_url, 'http') ? $variant->image_url : url($variant->image_url)) : null,
                    'inventory' => $variant->inventory,
                    'price_override' => $variant->price_override_minor ? $variant->price_override_minor / 100 : null,
                    'color_hex' => $colorHex,
                    'gallery' => $galleryAssets,
                ],
            ]);
        }

        return back()->with('success', "Updated variant: {$variant->title}");
    }

    // Add new Variant
    public function addVariant(Request $request, int $productId)
    {
        $product = Product::findOrFail($productId);
        $request->validate([
            'title' => 'required|string|max:100',
            'attribute_name' => 'nullable|string|max:100',
            'price_override' => 'nullable|numeric|min:0',
            'inventory' => 'required|integer|min:0',
            'color_hex' => 'nullable|string|max:20',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:'.config('media.max_image_size_kb', 5120),
            'variant_gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:'.config('media.max_image_size_kb', 5120),
        ]);

        $imageUrl = null;
        $coverFile = null;
        $coverFilename = null;
        if ($request->hasFile('image_file')) {
            $coverFile = $request->file('image_file');
            $coverFilename = 'variant-cover-'.time().'-'.Str::random(8).'.'.$coverFile->getClientOriginalExtension();
            $path = $coverFile->storeAs('media', $coverFilename, 'public');
            $imageUrl = '/storage/'.$path;
        } elseif ($request->filled('image_url') && $request->input('image_url') !== '__DEFAULT__') {
            $imageUrl = $request->input('image_url');
        }

        $priceOverrideMinor = $request->filled('price_override')
            ? (int) round(((float) $request->input('price_override')) * 100)
            : null;

        $colorHex = $request->input('color_hex', '#000000');

        $slug = Str::slug($request->title);
        if (empty($slug)) {
            $slug = 'VAR-'.Str::random(4);
        }
        $baseSku = strtoupper($product->sku.'-'.$slug);
        $sku = $baseSku;
        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $baseSku.'-'.$counter++;
        }

        $variant = $product->variants()->create([
            'sku' => $sku,
            'title' => $request->title,
            'attribute_name' => $request->input('attribute_name', 'Color'),
            'attribute_value' => $request->title,
            'price_override_minor' => $priceOverrideMinor,
            'retail_price_minor' => $product->retail_price_minor,
            'inventory' => (int) $request->inventory,
            'image_url' => $imageUrl,
            'attributes_json' => [
                'color' => $request->title,
                'color_hex' => $colorHex,
                'image_url' => $imageUrl,
            ],
        ]);

        if (isset($coverFile) && isset($coverFilename) && $coverFile->isValid()) {
            $coverAsset = MediaAsset::create([
                'type' => 'image',
                'url' => $imageUrl,
                'filename' => $coverFilename,
                'mime_type' => $coverFile->getClientMimeType(),
                'size_bytes' => $coverFile->getSize(),
            ]);
            $variant->mediaAssets()->attach($coverAsset->id, ['group' => 'cover', 'sort_order' => 0]);
        }

        if ($request->hasFile('variant_gallery_images')) {
            $pos = 1;
            foreach ($request->file('variant_gallery_images') as $gFile) {
                if ($gFile->isValid()) {
                    $filename = 'variant-gallery-'.time().'-'.Str::random(8).'.'.$gFile->getClientOriginalExtension();
                    $path = $gFile->storeAs('media', $filename, 'public');
                    $url = '/storage/'.$path;

                    $asset = MediaAsset::create([
                        'type' => 'image',
                        'url' => $url,
                        'filename' => $filename,
                        'mime_type' => $gFile->getClientMimeType(),
                        'size_bytes' => $gFile->getSize(),
                    ]);

                    $variant->mediaAssets()->attach($asset->id, ['group' => 'gallery', 'sort_order' => $pos++]);

                    if (empty($variant->image_url)) {
                        $variant->update([
                            'image_url' => $url,
                            'attributes_json' => array_merge($variant->attributes_json ?? [], ['image_url' => $url]),
                        ]);
                    }
                }
            }
        }

        return back()->with('success', "Added variant \"{$variant->title}\".");
    }

    // Delete Media from Variant Gallery
    public function deleteVariantMedia(int $variantId, int $assetId)
    {
        $variant = ProductVariant::findOrFail($variantId);
        $variant->mediaAssets()->detach($assetId);

        $asset = MediaAsset::find($assetId);
        if ($asset) {
            $usage = DB::table('mediables')->where('media_asset_id', $assetId)->count();
            if ($usage === 0) {
                if (Storage::disk('public')->exists('media/'.$asset->filename)) {
                    Storage::disk('public')->delete('media/'.$asset->filename);
                }
                $asset->delete();
            }
        }

        AuditLog::log('variant.media_delete', 'product_variant', $variantId, "Removed media asset #{$assetId} from variant {$variant->title}");

        return back()->with('success', 'Image removed from variant gallery.');
    }

    // Duplicate / Clone Variant
    public function duplicateVariant(int $id)
    {
        $original = ProductVariant::with('mediaAssets')->findOrFail($id);
        $product = $original->product;

        $newTitle = $original->title . ' (Copy)';
        $slug = Str::slug($newTitle);
        if (empty($slug)) {
            $slug = 'VAR-' . Str::random(4);
        }
        $baseSku = strtoupper($product->sku . '-' . $slug);
        $sku = $baseSku;
        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter++;
        }

        $attrs = $original->attributes_json ?? [];
        $attrs['color'] = $newTitle;

        $clone = $product->variants()->create([
            'sku' => $sku,
            'title' => $newTitle,
            'attribute_name' => $original->attribute_name ?? 'Color',
            'attribute_value' => $newTitle,
            'price_override_minor' => $original->price_override_minor,
            'retail_price_minor' => $original->retail_price_minor,
            'inventory' => (int) $original->inventory,
            'image_url' => $original->image_url,
            'attributes_json' => $attrs,
            'status' => 'active',
        ]);

        // Copy media assets
        if ($original->mediaAssets->isNotEmpty()) {
            foreach ($original->mediaAssets as $mAsset) {
                $clone->mediaAssets()->attach($mAsset->id, [
                    'group' => $mAsset->pivot->group ?? 'gallery',
                    'sort_order' => $mAsset->pivot->sort_order ?? 0,
                ]);
            }
        }

        AuditLog::log('variant.duplicate', 'product_variant', $clone->id, "Duplicated variant from #{$original->id} ({$original->title}) to #{$clone->id} ({$clone->title})");

        return back()->with('success', "Duplicated variant \"{$original->title}\" as \"{$clone->title}\".");
    }

    // Inline Quick-Edit for Variant (Price & Stock)
    public function inlineEditVariant(Request $request, int $id)
    {
        $request->validate([
            'field' => 'required|in:price_override,inventory,status',
            'value' => 'nullable',
        ]);

        $variant = ProductVariant::findOrFail($id);
        $field = $request->input('field');
        $raw = $request->input('value');

        if ($field === 'price_override') {
            $priceOverrideMinor = ($raw !== null && $raw !== '') ? (int) round(((float) $raw) * 100) : null;
            if ($priceOverrideMinor !== null && $priceOverrideMinor < 0) {
                return response()->json(['success' => false, 'message' => 'Price cannot be negative.'], 422);
            }
            $variant->price_override_minor = $priceOverrideMinor;
            $variant->save();

            $formatted = $priceOverrideMinor !== null
                ? number_format($priceOverrideMinor / 100, 2) . ' EGP'
                : 'Base (' . number_format($variant->product->retail_price_minor / 100, 2) . ' EGP)';

            return response()->json([
                'success' => true,
                'field' => $field,
                'raw_value' => $raw,
                'formatted' => $formatted,
            ]);
        }

        if ($field === 'inventory') {
            $stock = (int) $raw;
            if ($stock < 0) {
                return response()->json(['success' => false, 'message' => 'Stock cannot be negative.'], 422);
            }
            $variant->inventory = $stock;
            $variant->save();

            return response()->json([
                'success' => true,
                'field' => $field,
                'raw_value' => $stock,
                'formatted' => (string) $stock,
            ]);
        }

        if ($field === 'status') {
            $status = in_array($raw, ['active', 'draft', 'archived']) ? $raw : 'active';
            $variant->status = $status;
            $variant->save();

            return response()->json([
                'success' => true,
                'field' => $field,
                'raw_value' => $status,
                'formatted' => ucfirst($status),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid field.'], 422);
    }

    // Delete / Archive Variant safely
    public function deleteVariant(int $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $hasOrders = DB::table('order_items')->where('product_variant_id', $variant->id)->exists();
        if ($hasOrders) {
            $variant->update(['status' => 'archived']);
            AuditLog::log('product.variant_archive', 'product_variant', $id, "Archived variant \"{$variant->title}\" because it is linked to past orders.");

            return back()->with('success', "Variant \"{$variant->title}\" has order history and was safely archived instead of deleted.");
        }

        $variant->delete();
        AuditLog::log('product.variant_delete', 'product_variant', $id, "Deleted variant \"{$variant->title}\"");

        return back()->with('success', "Variant \"{$variant->title}\" removed.");
    }

    // Toggle Variant Publish / Draft Status
    public function toggleVariantPublish(Request $request, int $id)
    {
        $variant = ProductVariant::with('product', 'mediaAssets')->findOrFail($id);

        if ($variant->isPublished()) {
            $variant->status = 'draft';
            $variant->save();
            AuditLog::log('product.variant_unpublish', 'product_variant', $id, "Unpublished variant \"{$variant->title}\" to Draft");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => 'draft',
                    'status_label' => 'Draft',
                    'message' => "Variant \"{$variant->title}\" is now Draft (hidden from storefront).",
                ]);
            }

            return back()->with('success', "Variant \"{$variant->title}\" is now Draft.");
        }

        // Validate readiness to publish
        $errors = $variant->getPublishValidationErrors();
        if (! empty($errors)) {
            $errorMessage = implode(' ', $errors);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $errors,
                ], 422);
            }

            return back()->with('error', "Cannot publish variant: {$errorMessage}");
        }

        $variant->status = 'published';
        $variant->save();
        AuditLog::log('product.variant_publish', 'product_variant', $id, "Published variant \"{$variant->title}\" live");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'published',
                'status_label' => 'Published',
                'message' => "Variant \"{$variant->title}\" is now Published and live on storefront.",
            ]);
        }

        return back()->with('success', "Variant \"{$variant->title}\" is now Published.");
    }

    // Bulk Update Variants Status for a Product
    public function bulkVariantsStatus(Request $request, int $productId)
    {
        $product = Product::findOrFail($productId);
        $request->validate([
            'variant_ids' => 'required|array',
            'variant_ids.*' => 'integer|exists:product_variants,id',
            'bulk_action' => 'required|string|in:publish,draft,archive',
        ]);

        $variantIds = $request->input('variant_ids');
        $action = $request->input('bulk_action');
        $variants = ProductVariant::where('product_id', $product->id)->whereIn('id', $variantIds)->get();

        $updatedCount = 0;
        $blockedErrors = [];

        foreach ($variants as $v) {
            if ($action === 'publish') {
                $errs = $v->getPublishValidationErrors();
                if (! empty($errs)) {
                    $blockedErrors[] = "{$v->title}: ".implode(', ', $errs);
                    continue;
                }
                $v->status = 'published';
                $v->save();
                $updatedCount++;
            } elseif ($action === 'draft') {
                $v->status = 'draft';
                $v->save();
                $updatedCount++;
            } elseif ($action === 'archive') {
                $v->status = 'archived';
                $v->save();
                $updatedCount++;
            }
        }

        $actionLabel = $action === 'publish' ? 'published' : ($action === 'draft' ? 'moved to draft' : 'archived');
        $msg = "{$updatedCount} variant(s) {$actionLabel}.";
        if (! empty($blockedErrors)) {
            $msg .= ' Some variants were skipped due to incomplete data: '.implode('; ', $blockedErrors);
        }

        AuditLog::log('product.bulk_variant_status', 'product', $product->id, $msg);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'blocked_errors' => $blockedErrors,
                'message' => $msg,
            ]);
        }

        return back()->with(empty($blockedErrors) ? 'success' : 'warning', $msg);
    }
}

