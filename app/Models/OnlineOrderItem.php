<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineOrderItem extends Model
{
    protected $fillable = [
        'online_order_id','product_id','product_name','selected_size','quantity',
        'unit_price','unit_cost','line_total'
    ];

    protected function casts(): array
    {
        return [
            'quantity'=>'decimal:2',
            'unit_price'=>'decimal:2',
            'unit_cost'=>'decimal:2',
            'line_total'=>'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
