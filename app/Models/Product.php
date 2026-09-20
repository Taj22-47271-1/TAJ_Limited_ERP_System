<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id','name','sku','barcode','unit','purchase_price','selling_price',
        'stock','reserved_stock','min_stock','image_path','has_sizes','size_options',
        'status','description'
    ];

    protected $appends = ['available_stock','image_url'];

    protected function casts(): array
    {
        return [
            'purchase_price'=>'decimal:2',
            'selling_price'=>'decimal:2',
            'stock'=>'decimal:2',
            'reserved_stock'=>'decimal:2',
            'min_stock'=>'decimal:2',
            'has_sizes'=>'boolean',
            'size_options'=>'array',
            'status'=>'boolean',
        ];
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
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function normalizedSizes(): array
    {
        if (! $this->has_sizes) {
            return [];
        }

        return collect($this->size_options ?? [])
            ->map(fn ($size) => trim((string) $size))
            ->filter()
            ->unique(fn ($size) => mb_strtolower($size))
            ->values()
            ->all();
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
