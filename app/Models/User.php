<?php

namespace App\Models;

use App\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
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
            'role' => Role::class,
        ];
    }

    public function ownedOperators(): HasMany
    {
        return $this->hasMany(Operator::class, 'owner_id');
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class, 'operator_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    public function permissionAuditLogs(): HasMany
    {
        return $this->hasMany(PermissionAuditLog::class, 'user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === Role::SuperAdmin;
    }

    public function isCompanyOwner(): bool
    {
        return $this->role === Role::CompanyOwner;
    }

    public function isEmployee(): bool
    {
        return $this->role === Role::Employee;
    }

    public function isTechnician(): bool
    {
        return $this->role === Role::Technician;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function ownsOperator(Operator $operator): bool
    {
        return $this->isSuperAdmin() || $this->ownedOperators()->where('id', $operator->id)->exists();
    }

    public function belongsToOperator(Operator $operator): bool
    {
        return $this->isSuperAdmin() || $this->ownsOperator($operator) || $this->operators()->where('operators.id', $operator->id)->exists();
    }

    /**
     * التحقق من وجود صلاحية معينة
     */
    public function hasPermission(string $permissionName): bool
    {
        // SuperAdmin و Admin لديهم جميع الصلاحيات (Admin للعرض فقط)
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * التحقق من وجود أي صلاحية من الصلاحيات المحددة
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        return $this->permissions()->whereIn('name', $permissionNames)->exists();
    }

    /**
     * التحقق من وجود جميع الصلاحيات المحددة
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        $userPermissions = $this->permissions()->whereIn('name', $permissionNames)->pluck('name')->toArray();

        return count($permissionNames) === count($userPermissions);
    }
}
