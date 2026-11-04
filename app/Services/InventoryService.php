<?php
namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function add(Product $product, float $quantity, float $unitCost, string $type, ?int $referenceId, ?int $userId, ?string $note = null): void
    {
        $product->increment('stock', $quantity);
        $product->refresh();
        $this->movement($product, $type, $referenceId, $quantity, 0, $unitCost, $userId, $note);
    }

    public function remove(Product $product, float $quantity, float $unitCost, string $type, ?int $referenceId, ?int $userId, ?string $note = null): void
    {
        $product->refresh();
        $available = (float) $product->stock - (float) $product->reserved_stock;
        if ($available < $quantity) {
            throw ValidationException::withMessages(['stock' => "Insufficient available stock for {$product->name}. Available: {$available}"]);
        }
        $product->decrement('stock', $quantity);
        $product->refresh();
        $this->movement($product, $type, $referenceId, 0, $quantity, $unitCost, $userId, $note);
    }

    public function reserve(Product $product, float $quantity): void
    {
        $product->refresh();
        $available = (float) $product->stock - (float) $product->reserved_stock;
        if ($available < $quantity) {
            throw ValidationException::withMessages(['stock' => "Insufficient available stock for {$product->name}. Available: {$available}"]);
        }
        $product->increment('reserved_stock', $quantity);
    }

    public function releaseReservation(Product $product, float $quantity): void
    {
        $product->refresh();
        $release = min((float) $product->reserved_stock, $quantity);
        if ($release > 0) {
            $product->decrement('reserved_stock', $release);
        }
    }

    public function commitReserved(Product $product, float $quantity, float $unitCost, string $type, ?int $referenceId, ?int $userId, ?string $note = null): void
    {
        $product->refresh();
        $reserved = (float) $product->reserved_stock;
        $toRelease = min($reserved, $quantity);
        if ($toRelease > 0) {
            $product->decrement('reserved_stock', $toRelease);
        }
        $product->decrement('stock', $quantity);
        $product->refresh();
        $this->movement($product, $type, $referenceId, 0, $quantity, $unitCost, $userId, $note);
    }

    public function adjust(Product $product, float $difference, float $unitCost, ?int $userId, string $note): void
    {
        $product->refresh();
        if ($difference < 0) {
            $available = (float) $product->stock - (float) $product->reserved_stock;
            if ($available < abs($difference)) {
                throw ValidationException::withMessages(['stock' => "Cannot reduce {$product->name} below reserved quantity. Available to adjust: {$available}"]);
            }
        }
        if ($difference > 0) {
            $product->increment('stock', $difference);
        } else {
            $product->decrement('stock', abs($difference));
        }
        $product->refresh();
        $this->movement(
            $product,
            'adjustment',
            null,
            $difference > 0 ? $difference : 0,
            $difference < 0 ? abs($difference) : 0,
            $unitCost,
            $userId,
            $note
        );
    }

    private function movement(Product $product, string $type, ?int $referenceId, float $in, float $out, float $unitCost, ?int $userId, ?string $note): void
    {
        StockMovement::create([
            'product_id'=>$product->id,'movement_type'=>$type,'reference_type'=>$type,'reference_id'=>$referenceId,
            'quantity_in'=>$in,'quantity_out'=>$out,'balance_after'=>$product->stock,'unit_cost'=>$unitCost,
            'note'=>$note,'created_by'=>$userId,
        ]);
    }
}
