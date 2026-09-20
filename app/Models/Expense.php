<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Expense extends Model
{
    protected $fillable=['expense_date','category','description','amount','payment_method','reference','created_by'];
    protected function casts(): array { return ['expense_date'=>'date','amount'=>'decimal:2']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
}
