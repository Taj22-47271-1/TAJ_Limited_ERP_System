<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable=['user_id','name','phone','email','address','customer_type','opening_balance','credit_limit','status'];
    protected function casts(): array { return ['opening_balance'=>'decimal:2','credit_limit'=>'decimal:2','status'=>'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
