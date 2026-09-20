<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Account extends Model
{
    protected $fillable=['code','name','type','is_system'];
    protected function casts(): array { return ['is_system'=>'boolean']; }
}
