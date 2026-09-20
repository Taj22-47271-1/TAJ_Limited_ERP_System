<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineOrder extends Model
{
    public const STATUSES = ['pending','approved','processing','packed','shipped','out_for_delivery','delivered','rejected','cancelled'];
    public const PROGRESS = ['pending','approved','processing','packed','shipped','out_for_delivery','delivered'];

    protected $fillable = [
        'user_id','order_no','full_name','email','phone','address','city','area','postal_code','notes',
        'subtotal','delivery_fee','total','payment_method','payment_channel','payment_status','payment_reference',
        'order_status','approved_by','approved_at','stock_committed_at'
    ];

    protected function casts(): array
    {
        return [
            'subtotal'=>'decimal:2','delivery_fee'=>'decimal:2','total'=>'decimal:2',
            'approved_at'=>'datetime','stock_committed_at'=>'datetime'
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(OnlineOrderItem::class); }
    public function histories(): HasMany { return $this->hasMany(OrderStatusHistory::class)->orderBy('id'); }

    public function isTerminal(): bool { return in_array($this->order_status, ['delivered','rejected','cancelled'], true); }
}
