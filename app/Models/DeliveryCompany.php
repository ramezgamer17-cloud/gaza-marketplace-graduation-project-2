<?php

namespace App\Models;

use App\Support\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryCompany extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'phone',
        'email',
        'address',
        'zone_id',
        'logo',
        'manager_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(function (DeliveryCompany $company) {
            if (! $company->manager_id) {
                return;
            }

            $manager = User::find($company->manager_id);

            if (! $manager) {
                return;
            }

            $role = \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => 'delivery_manager',
                'guard_name' => 'web',
            ]);

            if (! $manager->hasRole($role)) {
                $manager->assignRole($role);
            }

            $profile = DeliveryProfile::firstOrCreate(
                ['user_id' => $manager->id],
                [
                    'company_id' => $company->id,
                    'type' => DeliveryProfile::TYPE_MANAGER,
                    'is_active' => (bool) $manager->is_active,
                ]
            );

            if (! $profile->wasRecentlyCreated) {
                $profile->update([
                    'company_id' => $company->id,
                    'type' => DeliveryProfile::TYPE_MANAGER,
                ]);
            }
        });
        static::creating(function (DeliveryCompany $company) {
            $company->slug ??= Slug::make($company->name);
        });
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'zone_id');
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(DeliveryProfile::class, 'company_id');
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(DeliveryProfile::class, 'company_id')->where('type', 'driver');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_company_id');
    }
}
