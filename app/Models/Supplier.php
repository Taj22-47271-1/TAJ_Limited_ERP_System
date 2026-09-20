<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Supplier extends Model
{
    protected $fillable=['name','company','phone','email','address','opening_balance','credit_limit','status'];
    protected function casts(): array { return ['opening_balance'=>'decimal:2','credit_limit'=>'decimal:2','status'=>'boolean']; }
}
