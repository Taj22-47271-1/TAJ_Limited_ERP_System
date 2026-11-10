<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','phone','password','role','is_active'];
    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];
    }

    public function roleDefinition(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function onlineOrders(): HasMany
    {
        return $this->hasMany(OnlineOrder::class);
    }

    protected static array $roleCache = [];

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        $slug = (string) $this->role;
        if (! array_key_exists($slug, static::$roleCache)) {
            static::$roleCache[$slug] = $this->relationLoaded('roleDefinition')
                ? $this->roleDefinition
                : Role::where('slug', $slug)->first();
        }

        $role = static::$roleCache[$slug];
        if ($role) {
            return $role->hasPermission($permission);
        }

        return match ($this->role) {
            'cashier' => in_array($permission, [
                'dashboard.view','pos.use','sales.view','products.view','products.manage',
                'orders.view','orders.approve','orders.progress','customers.manage'
            ], true),
            'customer' => in_array($permission, ['dashboard.view','shop.use','orders.own'], true),
            default => false,
        };
    }

    public function isAdmin(): bool { return $this->role === 'admin' || $this->hasPermission('users.manage'); }
    public function isCashier(): bool { return $this->role === 'cashier'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }
}
