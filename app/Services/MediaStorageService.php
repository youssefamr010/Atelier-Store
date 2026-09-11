<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Stores media with portable disk/path metadata instead of hard-coded URLs. */
class MediaStorageService
{
    public function store(UploadedFile $file, string $type = 'image', ?string $prefix = null): MediaAsset
    {
        $disk = config('media.disk');
        $extension = strtolower($file->extension() ?: $file->guessExtension() ?: 'bin');
        $filename = trim(($prefix ? Str::slug($prefix).'-' : '').Str::uuid().'.'.$extension, '-');
        $path = trim(config('media.directory'), '/').'/'.now()->format('Y/m').'/'.$filename;

        Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path), ['visibility' => 'public']);

        $metadata = ['hash' => hash_file('sha256', $file->getRealPath())];
        if ($type === 'image' && ($size = @getimagesize($file->getRealPath()))) {
            $metadata += ['width' => $size[0], 'height' => $size[1], 'aspect_ratio' => round($size[0] / $size[1], 4)];
        }

        return MediaAsset::create([
            'type' => $type,
            'disk' => $disk,
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'filename' => $filename,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'metadata' => $metadata,
        ]);
    }

    public function delete(MediaAsset $asset): void
    {
        $this->deleteLocation($asset->disk, $asset->path);
    }

    public function deleteLocation(?string $disk, ?string $path): void
    {
        if ($disk && $path) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function url(MediaAsset $asset): string
    {
        return $asset->disk && $asset->path
            ? Storage::disk($asset->disk)->url($asset->path)
            : $asset->url;
    }
}
