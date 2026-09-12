<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\Setting;
use App\Services\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminImageController extends Controller
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MAX_SIZE_KB = 5120; // 5MB

    public function __construct(private readonly MediaStorageService $mediaStorage) {}

    /**
     * Replace the main product cover image.
     */
    public function replaceProductImage(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:'.config('media.max_image_size_kb', self::MAX_SIZE_KB),
        ], [
            'image.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'image.max' => 'The image size cannot exceed 5MB.',
        ]);

        $product = Product::findOrFail($id);
        $file = $request->file('image');

        $asset = $this->mediaStorage->store($file, 'image', 'cover');
        $url = $this->mediaStorage->url($asset);

        $product->update(['image_url' => $url]);
        $oldCoverIds = $product->mediaAssets()->wherePivot('group', 'cover')->pluck('media_assets.id')->toArray();
        if (! empty($oldCoverIds)) {
            $product->mediaAssets()->detach($oldCoverIds);
        }
        $product->mediaAssets()->attach($asset->id, [
            'group' => 'cover',
            'sort_order' => 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Main image updated for \"{$product->title}\".",
                'image_url' => $url,
                'filename' => $asset->filename,
            ]);
        }

        return back()->with('success', "Main image updated for \"{$product->title}\".");
    }

    /**
     * Replace a specific gallery media asset.
     */
    public function replaceMediaAsset(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:'.config('media.max_image_size_kb', self::MAX_SIZE_KB),
        ], [
            'image.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'image.max' => 'The image size cannot exceed 5MB.',
        ]);

        $asset = MediaAsset::findOrFail($id);
        $file = $request->file('image');
        $oldDisk = $asset->disk;
        $oldPath = $asset->path;

        $replacement = $this->mediaStorage->store($file, 'image', 'gallery');
        $url = $this->mediaStorage->url($replacement);

        $asset->update([
            'url' => $url,
            'disk' => $replacement->disk,
            'path' => $replacement->path,
            'filename' => $replacement->filename,
            'mime_type' => $replacement->mime_type,
            'size_bytes' => $replacement->size_bytes,
            'metadata' => $replacement->metadata,
        ]);
        $replacement->delete();
        $this->mediaStorage->deleteLocation($oldDisk, $oldPath);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gallery image updated successfully.',
                'image_url' => $url,
                'filename' => $asset->filename,
                'asset_id' => $asset->id,
            ]);
        }

        return back()->with('success', 'Gallery image updated successfully.');
    }

    /**
     * Upload and attach a new gallery image to a product.
     */
    public function uploadGalleryImage(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:'.config('media.max_image_size_kb', self::MAX_SIZE_KB),
        ], [
            'image.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'image.max' => 'The image size cannot exceed 5MB.',
        ]);

        $product = Product::findOrFail($productId);
        $file = $request->file('image');

        $asset = $this->mediaStorage->store($file, 'image', 'gallery');
        $url = $this->mediaStorage->url($asset);

        $nextSort = ($product->mediaAssets()->max('sort_order') ?? 0) + 1;
        $product->mediaAssets()->attach($asset->id, ['group' => 'gallery', 'sort_order' => $nextSort]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "New gallery image attached to \"{$product->title}\".",
                'image_url' => $url,
                'asset_id' => $asset->id,
                'filename' => $asset->filename,
            ], 201);
        }

        return back()->with('success', "New gallery image attached to \"{$product->title}\".");
    }

    /**
     * Replace the promotional homepage banner image.
     */
    public function replacePromoBanner(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:'.config('media.max_image_size_kb', self::MAX_SIZE_KB),
        ], [
            'image.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'image.max' => 'The banner image size cannot exceed 5MB.',
        ]);

        $file = $request->file('image');
        $asset = $this->mediaStorage->store($file, 'image', 'banner');
        $url = $this->mediaStorage->url($asset);

        Setting::set('homepage_banner_image', $url);
        Setting::set('homeBannerImage', $url);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Homepage promotional banner updated successfully.',
                'image_url' => $url,
            ]);
        }

        return back()->with('success', 'Homepage promotional banner updated successfully.');
    }

    /**
     * Replace a collection cover image.
     */
    public function replaceCollectionImage(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:'.config('media.max_image_size_kb', self::MAX_SIZE_KB),
        ], [
            'image.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'image.max' => 'The collection image size cannot exceed 5MB.',
        ]);

        $collection = Collection::findOrFail($id);
        $file = $request->file('image');

        $asset = $this->mediaStorage->store($file, 'image', 'collection');
        $url = $this->mediaStorage->url($asset);

        $collection->update(['image_url' => $url]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Collection image updated for \"{$collection->title}\".",
                'image_url' => $url,
            ]);
        }

        return back()->with('success', "Collection image updated for \"{$collection->title}\".");
    }

    /**
     * Replace Client Access Portal / Account Login Background.
     */
    public function replacePortalBackground(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:'.config('media.max_image_size_kb', self::MAX_SIZE_KB),
        ], [
            'image.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'image.max' => 'The portal background image size cannot exceed 5MB.',
        ]);

        $file = $request->file('image');
        $asset = $this->mediaStorage->store($file, 'image', 'portal-background');
        $url = $this->mediaStorage->url($asset);

        Setting::set('account_portal_background_url', $url);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client Access Portal background wallpaper updated successfully.',
                'image_url' => $url,
            ]);
        }

        return back()->with('success', 'Client Access Portal background wallpaper updated successfully.');
    }

    /**
     * Fetch external image from URL (Amazon, Pinterest, CDN) to allow seamless drag-and-drop.
     */
    public function fetchImageUrl(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $url = trim((string) $request->input('url'));

        // Handle schemeless URLs (e.g. //m.media-amazon.com/...)
        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image URL provided.',
            ], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Referer' => 'https://www.amazon.eg/',
            ])->timeout(12)->get($url);

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch image from external website (Status: '.$response->status().').',
                ], 422);
            }

            $body = $response->body();
            $contentType = $response->header('Content-Type') ?: 'image/jpeg';
            if (! str_starts_with($contentType, 'image/')) {
                $contentType = 'image/jpeg';
            }

            $base64 = base64_encode($body);
            $dataUrl = "data:{$contentType};base64,{$base64}";
            $parsedPath = parse_url($url, PHP_URL_PATH);
            $filename = $parsedPath ? basename($parsedPath) : 'imported-image.jpg';
            if (! str_contains($filename, '.')) {
                $filename .= '.jpg';
            }

            return response()->json([
                'success' => true,
                'data_url' => $dataUrl,
                'filename' => $filename,
                'mime' => $contentType,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading image: '.$e->getMessage(),
            ], 500);
        }
    }
}
