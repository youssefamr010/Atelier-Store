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
        if ($this->disk && $this->path) {
            return asset(ltrim(Storage::disk($this->disk)->url($this->path), '/'));
        }
        if (empty($value)) {
            return null;
        }
        if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/(.*)$#i', $value, $m)) {
            return asset($m[3]);
        }
        if (str_starts_with($value, '/storage/') || str_starts_with($value, 'storage/')) {
            return asset(ltrim($value, '/'));
        }
        return $value;
    }

    public function models()
    {
        return $this->morphTo();
    }
}
