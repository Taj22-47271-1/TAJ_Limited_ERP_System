<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'sku', 'barcode', 'unit', 'purchase_price', 'selling_price',
        'discount_price', 'discount_percent', 'stock', 'reserved_stock', 'min_stock',
        'image_path', 'has_sizes', 'size_options', 'colorways', 'status', 'description',
        'showcase_config',
    ];

    protected $appends = [
        'available_stock',
        'image_url',
        'effective_price',
        'discount_percent_calculated',
        'has_discount',
        'available_colorways',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'stock' => 'decimal:2',
            'reserved_stock' => 'decimal:2',
            'min_stock' => 'decimal:2',
            'has_sizes' => 'boolean',
            'size_options' => 'array',
            'colorways' => 'array',
            'status' => 'boolean',
            'showcase_config' => 'array',
        ];
    }

    public function getEffectivePriceAttribute(): float
    {
        $selling = (float) $this->selling_price;
        if ($this->discount_price !== null && (float) $this->discount_price > 0 && (float) $this->discount_price < $selling) {
            return (float) $this->discount_price;
        }
        if ($this->discount_percent !== null && (float) $this->discount_percent > 0) {
            return round($selling * (1 - ((float) $this->discount_percent / 100)), 2);
        }
        return $selling;
    }

    public function getDiscountPercentCalculatedAttribute(): ?float
    {
        if ($this->discount_percent !== null && (float) $this->discount_percent > 0) {
            return (float) $this->discount_percent;
        }
        $selling = (float) $this->selling_price;
        if ($this->discount_price !== null && (float) $this->discount_price > 0 && (float) $this->discount_price < $selling && $selling > 0) {
            return round((($selling - (float) $this->discount_price) / $selling) * 100, 1);
        }
        return null;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->effective_price < (float) $this->selling_price;
    }

    public function getEffectiveShowcaseConfig(?Setting $settings = null): array
    {
        $global = $settings ? $settings->getShowcaseSettings() : Setting::defaultShowcaseConfig();
        $custom = $this->showcase_config ?? [];
        return array_replace_recursive($global, $custom);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getAvailableStockAttribute(): float
    {
        return max(0, (float) $this->stock - (float) $this->reserved_stock);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return asset('assets/images/products/default.svg');
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'assets/') || str_starts_with($this->image_path, 'images/')) {
            return asset($this->image_path);
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function getAvailableColorwaysAttribute(): array
    {
        // 1. Explicit colorways assigned to product
        if (! empty($this->colorways) && is_array($this->colorways)) {
            return collect($this->colorways)->map(function ($cw, $idx) {
                $img = $cw['image'] ?? '';
                $imgUrl = ! empty($img)
                    ? (str_starts_with($img, 'http') || str_starts_with($img, 'assets/') || str_starts_with($img, 'storage/') ? asset($img) : asset('storage/' . $img))
                    : $this->image_url;

                return [
                    'key' => $cw['key'] ?? 'color_' . $idx,
                    'name' => $cw['name'] ?? 'Color ' . ($idx + 1),
                    'color' => $cw['color'] ?? '#2563eb',
                    'sku' => $cw['sku'] ?? ($this->sku . '-' . strtoupper(substr($cw['key'] ?? 'C' . $idx, 0, 3))),
                    'image' => $img,
                    'image_url' => $imgUrl,
                    'stock' => (float)($cw['stock'] ?? $this->available_stock),
                    'tag' => $cw['tag'] ?? '',
                ];
            })->values()->all();
        }

        // 2. Custom showcase swatches configured for product
        $cfg = $this->showcase_config ?? [];
        if (! empty($cfg['swatches']) && is_array($cfg['swatches'])) {
            return collect($cfg['swatches'])->map(function ($sw, $idx) {
                $img = $sw['image'] ?? '';
                $imgUrl = ! empty($img)
                    ? (str_starts_with($img, 'http') || str_starts_with($img, 'assets/') || str_starts_with($img, 'storage/') ? asset($img) : asset('storage/' . $img))
                    : $this->image_url;

                return [
                    'key' => $sw['key'] ?? 'color_' . $idx,
                    'name' => $sw['name'] ?? 'Color ' . ($idx + 1),
                    'color' => $sw['color'] ?? '#2563eb',
                    'sku' => $this->sku . '-' . strtoupper(substr($sw['key'] ?? 'C' . $idx, 0, 3)),
                    'image' => $img,
                    'image_url' => $imgUrl,
                    'stock' => $this->available_stock,
                    'tag' => $sw['tag'] ?? '',
                ];
            })->values()->all();
        }

        // 3. For Clothing, Apparel, Sarees or products with size options, provide 5 signature luxury colorways
        $isClothing = $this->has_sizes || ($this->category && (
            stripos($this->category->name, 'cloth') !== false ||
            stripos($this->category->name, 'saree') !== false ||
            stripos($this->category->name, 'apparel') !== false
        ));

        if ($isClothing) {
            $defaultColorways = [
                ['key' => 'rose', 'name' => 'Champagne Rose', 'color' => '#e5b2a2', 'image' => 'assets/images/products/designer-saree-rose.png', 'tag' => 'Rose Gold Weave'],
                ['key' => 'sapphire', 'name' => 'Royal Sapphire', 'color' => '#1e3a8a', 'image' => 'assets/images/products/designer-saree-sapphire.png', 'tag' => 'Deep Navy Indigo'],
                ['key' => 'noir', 'name' => 'Midnight Noir', 'color' => '#18181b', 'image' => 'assets/images/products/designer-saree-noir.png', 'tag' => 'Obsidian Black'],
                ['key' => 'emerald', 'name' => 'Imperial Emerald', 'color' => '#065f46', 'image' => 'assets/images/products/designer-saree-emerald.png', 'tag' => 'Royal Forest'],
                ['key' => 'gold', 'name' => 'Regal Gold', 'color' => '#d97706', 'image' => 'assets/images/products/designer-saree-gold.png', 'tag' => 'Antique Zari'],
            ];

            return collect($defaultColorways)->map(function ($cw) {
                return [
                    'key' => $cw['key'],
                    'name' => $cw['name'],
                    'color' => $cw['color'],
                    'sku' => $this->sku . '-' . strtoupper(substr($cw['key'], 0, 3)),
                    'image' => $cw['image'],
                    'image_url' => asset($cw['image']),
                    'stock' => $this->available_stock,
                    'tag' => $cw['tag'],
                ];
            })->values()->all();
        }

        return [];
    }

    public function hasColorway(?string $color): bool
    {
        $colorways = $this->available_colorways;
        if (empty($colorways)) {
            return true;
        }
        if ($color === null || trim($color) === '') {
            return false;
        }
        return collect($colorways)->contains(
            fn ($cw) => mb_strtolower($cw['name']) === mb_strtolower(trim($color))
                     || mb_strtolower($cw['key']) === mb_strtolower(trim($color))
        );
    }

    public function findColorway(?string $color): ?array
    {
        $colorways = $this->available_colorways;
        if (empty($colorways)) {
            return null;
        }
        if ($color === null || trim($color) === '') {
            return $colorways[0] ?? null;
        }
        return collect($colorways)->first(
            fn ($cw) => mb_strtolower($cw['name']) === mb_strtolower(trim($color))
                     || mb_strtolower($cw['key']) === mb_strtolower(trim($color))
        ) ?? ($colorways[0] ?? null);
    }

    public function normalizedSizes(): array
    {
        if (! $this->has_sizes) {
            return [];
        }

        $options = collect($this->size_options ?? [])
            ->map(fn ($size) => trim((string) $size))
            ->filter()
            ->unique(fn ($size) => mb_strtolower($size))
            ->values()
            ->all();

        return ! empty($options) ? $options : ['S', 'M', 'L', 'XL', 'XXL'];
    }

    public function hasSize(?string $size): bool
    {
        if (! $this->has_sizes) {
            return $size === null || trim((string) $size) === '';
        }

        if ($size === null || trim($size) === '') {
            return false;
        }

        return collect($this->normalizedSizes())->contains(
            fn ($option) => mb_strtolower($option) === mb_strtolower(trim($size))
        );
    }
}
