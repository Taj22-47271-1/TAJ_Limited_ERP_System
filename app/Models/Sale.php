<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable=['customer_id','online_order_id','invoice_no','sale_date','subtotal','discount','tax','total','paid_amount','due_amount','payment_method','status','notes','created_by'];
    protected function casts(): array { return ['sale_date'=>'date','subtotal'=>'decimal:2','discount'=>'decimal:2','tax'=>'decimal:2','total'=>'decimal:2','paid_amount'=>'decimal:2','due_amount'=>'decimal:2']; }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function onlineOrder(): BelongsTo { return $this->belongsTo(OnlineOrder::class); }
    public function items(): HasMany { return $this->hasMany(SaleItem::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
}
