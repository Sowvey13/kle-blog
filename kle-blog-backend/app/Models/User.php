<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    public const LAST_ADMIN_MESSAGE = 'Sistemde en az bir yönetici bulunmalıdır.';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if ($user->isDirty('role') && $user->isDemotingLastAdmin($user->role)) {
                throw ValidationException::withMessages([
                    'role' => self::LAST_ADMIN_MESSAGE,
                ]);
            }
        });

        static::deleting(function (User $user): void {
            if ($user->isLastAdmin()) {
                throw ValidationException::withMessages([
                    'role' => self::LAST_ADMIN_MESSAGE,
                ]);
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    public function isLastAdmin(): bool
    {
        return $this->persistedRole() === UserRole::ADMIN
            && static::query()->where('role', UserRole::ADMIN)->count() === 1;
    }

    public function isDemotingLastAdmin(mixed $newRole): bool
    {
        $role = $newRole instanceof UserRole
            ? $newRole
            : UserRole::tryFrom((string) $newRole);

        return $this->isLastAdmin() && $role !== UserRole::ADMIN;
    }

    public static function deletionWouldRemoveLastAdmin(iterable $users): bool
    {
        $ids = collect($users)->pluck('id');

        $adminsBeingDeleted = static::query()
            ->where('role', UserRole::ADMIN)
            ->whereIn('id', $ids)
            ->count();

        if ($adminsBeingDeleted === 0) {
            return false;
        }

        return static::query()->where('role', UserRole::ADMIN)->count() <= $adminsBeingDeleted;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    private function persistedRole(): ?UserRole
    {
        if (! $this->exists) {
            return $this->role;
        }

        $original = $this->getOriginal('role');

        if ($original instanceof UserRole) {
            return $original;
        }

        return UserRole::tryFrom((string) $original);
    }
}
