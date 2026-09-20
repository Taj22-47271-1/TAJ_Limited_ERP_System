<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable=['online_order_id','status','note','changed_by'];
    public function order(): BelongsTo { return $this->belongsTo(OnlineOrder::class, 'online_order_id'); }
    public function changer(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
