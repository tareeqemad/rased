<?php

namespace App\Policies;

use App\Models\Generator;
use App\Models\User;

class GeneratorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // SuperAdmin و Admin لديهم جميع الصلاحيات (Admin للعرض فقط)
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('generators.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Generator $generator): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generators.view')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->belongsToOperator($generator->operator);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin لا يمكنه الإنشاء
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('generators.create')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isTechnician();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Generator $generator): bool
    {
        // Admin لا يمكنه التحديث
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generators.update')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->belongsToOperator($generator->operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Generator $generator): bool
    {
        // Admin لا يمكنه الحذف
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generators.delete')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل (يجب أن يكون صاحب المشغل)
        return $user->ownsOperator($generator->operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Generator $generator): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Generator $generator): bool
    {
        return $user->isSuperAdmin();
    }
}
