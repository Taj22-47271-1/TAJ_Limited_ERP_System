<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable=['product_id','movement_type','reference_type','reference_id','quantity_in','quantity_out','balance_after','unit_cost','note','created_by'];
    protected function casts(): array { return ['quantity_in'=>'decimal:2','quantity_out'=>'decimal:2','balance_after'=>'decimal:2','unit_cost'=>'decimal:2']; }
    public function product(): BelongsTo { return $this->belongsTo(Product::class)->withTrashed(); }
}
