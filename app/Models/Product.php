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
        'image_path', 'has_sizes', 'size_options', 'size_prices', 'colorways', 'status', 'description',
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
            'size_prices' => 'array',
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

    public function isClothing(): bool
    {
        if ($this->has_sizes) {
            return true;
        }
        $cat = mb_strtolower($this->category?->name ?? '');
        return str_contains($cat, 'cloth')
            || str_contains($cat, 'saree')
            || str_contains($cat, 'apparel')
            || str_contains($cat, 'fashion')
            || str_contains($cat, 'shirt')
            || str_contains($cat, 'pant')
            || str_contains($cat, 'dress');
    }

    public function isElectronics(): bool
    {
        $cat = mb_strtolower($this->category?->name ?? '');
        return str_contains($cat, 'electronic')
            || str_contains($cat, 'accessory')
            || str_contains($cat, 'accessories')
            || str_contains($cat, 'tech')
            || str_contains($cat, 'gadget');
    }

    public function isGrocery(): bool
    {
        $cat = mb_strtolower($this->category?->name ?? '');
        return str_contains($cat, 'grocer')
            || str_contains($cat, 'beverage')
            || str_contains($cat, 'food')
            || str_contains($cat, 'drink');
    }

    public function isOffice(): bool
    {
        $cat = mb_strtolower($this->category?->name ?? '');
        return str_contains($cat, 'office')
            || str_contains($cat, 'stationery')
            || str_contains($cat, 'paper');
    }

    public function isWatch(): bool
    {
        $cat = mb_strtolower($this->category?->name ?? '');
        return str_contains($cat, 'watch')
            || str_contains($cat, 'jewelry')
            || str_contains($cat, 'luxury');
    }

    public function isPerfume(): bool
    {
        $cat = mb_strtolower($this->category?->name ?? '');
        $name = mb_strtolower($this->name);
        return str_contains($cat, 'perfume')
            || str_contains($cat, 'fragrance')
            || str_contains($cat, 'scent')
            || str_contains($name, 'eau de parfum')
            || str_contains($name, 'perfume')
            || str_contains($name, 'oud');
    }

    public function getCategoryOptionsAttribute(): array
    {
        if ($this->isPerfume()) {
            return [
                'type' => 'perfume',
                'label' => 'Size (ml)',
                'options' => !empty($this->size_options) ? $this->size_options : ['50ml', '100ml'],
                'colorways' => [],
                'has_size_guide' => false,
            ];
        }

        if ($this->isClothing()) {
            return [
                'type' => 'clothing',
                'label' => 'Select Size',
                'options' => $this->normalizedSizes(),
                'colorways' => $this->available_colorways,
                'has_size_guide' => true,
            ];
        }

        if ($this->isElectronics()) {
            $name = mb_strtolower($this->name);
            if (str_contains($name, 'cable')) {
                return [
                    'type' => 'electronics',
                    'label' => 'Select Cable Length',
                    'options' => ['1 Meter', '2 Meter', '3 Meter (Extended)'],
                    'colorways' => [],
                    'has_size_guide' => false,
                ];
            }
            if (str_contains($name, 'hub') || str_contains($name, 'keyboard') || str_contains($name, 'mouse')) {
                return [
                    'type' => 'electronics',
                    'label' => 'Select Edition & Finish',
                    'options' => ['Standard Matte', 'Space Gray Edition', 'Pro Performance Edition'],
                    'colorways' => [],
                    'has_size_guide' => false,
                ];
            }
            return [
                'type' => 'electronics',
                'label' => 'Warranty & Care Option',
                'options' => ['1-Year Official Warranty', '2-Year TAJ Care+ Extended'],
                'colorways' => [],
                'has_size_guide' => false,
            ];
        }

        if ($this->isGrocery()) {
            return [
                'type' => 'grocery',
                'label' => 'Select Pack / Weight',
                'options' => ['Single Pack', 'Trio Pack (Save 10%)', 'Family Carton'],
                'colorways' => [],
                'has_size_guide' => false,
            ];
        }

        if ($this->isOffice()) {
            return [
                'type' => 'office',
                'label' => 'Select Quantity / Pack',
                'options' => ['1 Ream (500 Sheets)', '3 Reams Value Pack', 'Box of 5 Reams'],
                'colorways' => [],
                'has_size_guide' => false,
            ];
        }

        return [
            'type' => 'standard',
            'label' => 'Option',
            'options' => [],
            'colorways' => [],
            'has_size_guide' => false,
        ];
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

        // Restore image from database if wiped by ephemeral container restart
        if (! Storage::disk('public')->exists($this->image_path)) {
            if (\App\Models\StoredFile::ensureOnDisk($this->image_path)) {
                return Storage::disk('public')->url($this->image_path);
            }
            $dataUri = \App\Models\StoredFile::getDataUri($this->image_path);
            if ($dataUri) {
                return $dataUri;
            }
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function getAvailableColorwaysAttribute(): array
    {
        // 1. Explicit colorways assigned to product
        if (! empty($this->colorways) && is_array($this->colorways)) {
            return collect($this->colorways)->map(function ($cw, $idx) {
                $img = $cw['image'] ?? '';
                if (! empty($img) && ! str_starts_with($img, 'http') && ! str_starts_with($img, 'assets/') && ! Storage::disk('public')->exists($img)) {
                    \App\Models\StoredFile::ensureOnDisk($img);
                }
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

        // 3. If no explicit colorways or custom swatches exist, return empty array (do NOT inject hardcoded images)
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
        if (! $this->has_sizes && ! $this->isPerfume()) {
            return [];
        }

        $options = collect($this->size_options ?? [])
            ->map(fn ($size) => trim((string) $size))
            ->filter()
            ->unique(fn ($size) => mb_strtolower($size))
            ->values()
            ->all();

        if (! empty($options)) {
            return $options;
        }

        if ($this->isPerfume()) {
            return ['50ml', '100ml'];
        }

        return ['S', 'M', 'L', 'XL', 'XXL'];
    }

    public function hasSize(?string $size): bool
    {
        if (! $this->has_sizes && ! $this->isPerfume()) {
            return $size === null || trim((string) $size) === '';
        }

        if ($size === null || trim($size) === '') {
            return false;
        }

        return collect($this->normalizedSizes())->contains(
            fn ($option) => mb_strtolower($option) === mb_strtolower(trim($size))
        );
    }

    /**
     * Get the size pricing map normalized by lowercase size key.
     */
    public function getSizePricesMap(): array
    {
        if (empty($this->size_prices) || ! is_array($this->size_prices)) {
            return [];
        }
        $map = [];
        foreach ($this->size_prices as $key => $val) {
            if (is_array($val)) {
                $norm = mb_strtolower(trim((string)$key));
                $map[$norm] = $val;
            }
        }
        return $map;
    }

    /**
     * Get pricing parameters for a specific size.
     */
    public function getPricingForSize(?string $size): array
    {
        $fallbackSelling = (float) $this->selling_price;
        $fallbackEffective = (float) $this->effective_price;
        $fallbackDiscountPercent = $this->discount_percent_calculated;
        $fallbackHasDiscount = $this->has_discount;

        if ($size === null || trim((string)$size) === '' || empty($this->size_prices) || ! is_array($this->size_prices)) {
            return [
                'selling_price' => $fallbackSelling,
                'effective_price' => $fallbackEffective,
                'discount_price' => $this->discount_price !== null ? (float)$this->discount_price : null,
                'discount_percent' => $fallbackDiscountPercent,
                'has_discount' => $fallbackHasDiscount,
            ];
        }

        $normSize = mb_strtolower(trim((string)$size));
        $map = $this->getSizePricesMap();

        if (! isset($map[$normSize])) {
            return [
                'selling_price' => $fallbackSelling,
                'effective_price' => $fallbackEffective,
                'discount_price' => $this->discount_price !== null ? (float)$this->discount_price : null,
                'discount_percent' => $fallbackDiscountPercent,
                'has_discount' => $fallbackHasDiscount,
            ];
        }

        $entry = $map[$normSize];
        $selling = isset($entry['selling_price']) && is_numeric($entry['selling_price']) && (float)$entry['selling_price'] > 0
            ? (float)$entry['selling_price']
            : $fallbackSelling;

        $discountPrice = isset($entry['discount_price']) && is_numeric($entry['discount_price']) && (float)$entry['discount_price'] > 0
            ? (float)$entry['discount_price']
            : null;

        $discountPercent = isset($entry['discount_percent']) && is_numeric($entry['discount_percent']) && (float)$entry['discount_percent'] > 0
            ? (float)$entry['discount_percent']
            : null;

        $effective = $selling;
        if ($discountPrice !== null && $discountPrice > 0 && $discountPrice < $selling) {
            $effective = $discountPrice;
            if ($discountPercent === null && $selling > 0) {
                $discountPercent = round((($selling - $discountPrice) / $selling) * 100, 1);
            }
        } elseif ($discountPercent !== null && $discountPercent > 0 && $discountPercent < 100) {
            $effective = round($selling * (1 - ($discountPercent / 100)), 2);
            if ($discountPrice === null) {
                $discountPrice = $effective;
            }
        }

        $hasDiscount = $effective < $selling;

        return [
            'selling_price' => $selling,
            'effective_price' => $effective,
            'discount_price' => $hasDiscount ? $discountPrice : null,
            'discount_percent' => $hasDiscount ? $discountPercent : null,
            'has_discount' => $hasDiscount,
        ];
    }

    public function getEffectivePriceForSize(?string $size): float
    {
        return $this->getPricingForSize($size)['effective_price'];
    }

    public function getSellingPriceForSize(?string $size): float
    {
        return $this->getPricingForSize($size)['selling_price'];
    }

    public function getDiscountPercentForSize(?string $size): ?float
    {
        return $this->getPricingForSize($size)['discount_percent'];
    }

    public function hasDiscountForSize(?string $size): bool
    {
        return $this->getPricingForSize($size)['has_discount'];
    }
}
