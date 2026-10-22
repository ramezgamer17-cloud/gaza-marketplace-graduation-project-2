<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'cover',
        'address',
        'is_active',
        'password',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * من يستطيع الدخول إلى كل لوحة تحكم.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasRole('admin'),
            'vendor' => $this->hasRole('vendor'),
            'delivery' => $this->hasRole('delivery')
                && $this->deliveryProfile?->type === DeliveryProfile::TYPE_DRIVER
                && $this->deliveryProfile?->is_active,
            'company' => $this->hasRole('delivery_manager')
                && $this->deliveryProfile?->is_active
                && $this->deliveryProfile?->company?->is_active,
            default => false,
        };
    }

    /* ---------------------------------------------------------------
     | العلاقات
     * -------------------------------------------------------------- */

    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function deliveryProfile(): HasOne
    {
        return $this->hasOne(DeliveryProfile::class);
    }

    /** الشركة التي يديرها هذا المستخدم (إن كان مدير شركة توصيل). */
    public function managedCompany(): HasOne
    {
        return $this->hasOne(DeliveryCompany::class, 'manager_id');
    }

    public function isDeliveryDriver(): bool
    {
        return $this->deliveryProfile?->type === DeliveryProfile::TYPE_DRIVER;
    }

    public function isDeliveryManager(): bool
    {
        return $this->deliveryProfile?->type === DeliveryProfile::TYPE_MANAGER;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(VendorSection::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /** الطلبات التي اشتراها هذا المستخدم كزبون. */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** الطلبات الواردة إلى متجر هذا البائع. */
    public function vendorOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'vendor_id');
    }

    /** الطلبات المسندة إلى هذا الموظف للتوصيل. */
    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_user_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'vendor_id');
    }

    public function customerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'customer_id');
    }

    public function vendorConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'vendor_id');
    }
}
