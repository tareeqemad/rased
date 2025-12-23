<?php

namespace App\Policies;

use App\Models\Operator;
use App\Models\User;

class OperatorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('operators.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Operator $operator): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operators.view')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->belongsToOperator($operator);
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

        // فقط SuperAdmin يمكنه إنشاء مشغل جديد
        return $user->isSuperAdmin() && $user->hasPermission('operators.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Operator $operator): bool
    {
        // Admin لا يمكنه التحديث
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operators.update')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Operator $operator): bool
    {
        // Admin لا يمكنه الحذف
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('operators.delete')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Operator $operator): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Operator $operator): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can add employees to the operator.
     */
    public function addEmployee(User $user, Operator $operator): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->ownsOperator($operator);
    }
}
