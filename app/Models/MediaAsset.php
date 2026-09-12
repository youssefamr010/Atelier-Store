<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'url',
        'disk',
        'path',
        'filename',
        'mime_type',
        'size_bytes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function getUrlAttribute($value): ?string
    {
        // Priority 1: If disk + path are set, resolve via Storage — already returns a full URL
        if ($this->disk && $this->path) {
            try {
                $storageUrl = \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->path);
                // Storage::url() already returns an absolute URL — do NOT pass through asset() again
                if ($storageUrl && str_starts_with($storageUrl, 'http')) {
                    if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/(.*)$#i', $storageUrl, $m)) {
                        return url($m[3]);
                    }
                    return $storageUrl;
                }
                // Fallback: build manually
                return url('storage/' . ltrim($this->path, '/'));
            } catch (\Throwable $e) {
                // Disk unavailable — fall through to raw value
            }
        }

        if (empty($value)) {
            return null;
        }

        // Already an absolute external URL — return as-is
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            // Normalize localhost URLs to current APP_URL on production
            if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/(.*)$#i', $value, $m)) {
                return url($m[3]);
            }
            return $value;
        }

        // Relative storage path — build full URL
        if (str_starts_with($value, '/storage/') || str_starts_with($value, 'storage/')) {
            return url(ltrim($value, '/'));
        }

        // Bare path — assume storage
        return url('storage/' . ltrim($value, '/'));
    }

    public function models()
    {
        return $this->morphTo();
    }
}
