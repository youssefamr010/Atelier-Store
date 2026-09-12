<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'title',
        'attribute_name',
        'attribute_value',
        'price_override_minor',
        'attributes_json',
        'image_url',
        'cost_price_minor',
        'retail_price_minor',
        'inventory',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cost_price_minor'     => 'integer',
            'retail_price_minor'   => 'integer',
            'price_override_minor' => 'integer',
            'inventory'            => 'integer',
            'attributes_json'      => 'array',
        ];
    }

    public function getEffectivePriceMinorAttribute(): int
    {
        if ($this->price_override_minor !== null && $this->price_override_minor > 0) {
            return (int) $this->price_override_minor;
        }

        if ($this->retail_price_minor !== null && $this->retail_price_minor > 0) {
            return (int) $this->retail_price_minor;
        }

        if ($this->relationLoaded('product') && $this->product) {
            return (int) ($this->product->retail_price_minor ?? 0);
        }

        return 0;
    }

    public function getImageUrlAttribute($value): ?string
    {
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

    // ─── Relationships ───────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function mediaAssets(): MorphToMany
    {
        return $this->morphToMany(MediaAsset::class, 'mediable')->withPivot('group', 'sort_order')->orderByPivot('sort_order');
    }

    // ─── Scopes & Helpers ───────────────────────────────────────────────────

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('status', ['published', 'active']);
    }

    public function scopePublished(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('status', ['published', 'active']);
    }

    public function scopeDraft(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeInStock(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('inventory', '>', 0);
    }

    public function isPublished(): bool
    {
        return in_array($this->status, ['published', 'active'], true);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Get validation errors that would block publishing this variant.
     *
     * @return array<string>
     */
    public function getPublishValidationErrors(): array
    {
        $errors = [];

        if (empty(trim($this->title ?? ''))) {
            $errors[] = 'Color title is required.';
        }

        $hex = $this->attributes_json['color_hex'] ?? $this->attributes_json['hex'] ?? '';
        if (empty($hex) || ! preg_match('/^#[0-9a-fA-F]{3,8}$/', $hex)) {
            $errors[] = 'A valid #HEX color is required.';
        }

        $hasImage = ! empty($this->image_url) || ($this->relationLoaded('mediaAssets') && $this->mediaAssets->isNotEmpty()) || $this->mediaAssets()->exists();
        if (! $hasImage) {
            $errors[] = 'Add a cover image before publishing.';
        }

        if ($this->effective_price_minor <= 0) {
            $errors[] = 'A valid price greater than 0 is required.';
        }

        return $errors;
    }

    public function isPublishReady(): bool
    {
        return empty($this->getPublishValidationErrors());
    }
}

