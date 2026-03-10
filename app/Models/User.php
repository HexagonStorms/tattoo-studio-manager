<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable, HasRoles, HasPanelShield;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_platform_admin',
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
            'is_platform_admin' => 'boolean',
        ];
    }

    public function studios(): BelongsToMany
    {
        return $this->belongsToMany(Studio::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function waivers(): HasMany
    {
        return $this->hasMany(Waiver::class);
    }

    public function artistProfile(): HasOne
    {
        return $this->hasOne(Artist::class);
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        return $this->hasAnyRole(['super_admin', 'owner', 'editor', 'artist', 'apprentice']);
    }

    public function getTenants(Panel $panel): Collection
    {
        if ($this->isPlatformAdmin()) {
            return Studio::all();
        }

        return $this->studios;
    }

    public function canAccessTenant(\Illuminate\Database\Eloquent\Model $tenant): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        return $this->studios->contains($tenant);
    }
}
