<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        if ($user->hasPermission('users.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('users.view')) {
            return false;
        }

        // CompanyOwner يمكنه رؤية موظفيه وفنييه فقط
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                return $model->operators->contains($operator) &&
                       ($model->isEmployee() || $model->isTechnician());
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin لا يمكنه إنشاء
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('users.create')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admin لا يمكنه التحديث
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('users.update')) {
            return false;
        }

        // CompanyOwner يمكنه تحديث موظفيه وفنييه فقط
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                return $model->operators->contains($operator) &&
                       ($model->isEmployee() || $model->isTechnician());
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Admin لا يمكنه الحذف
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->id === $model->id) {
            return false; // لا يمكن للمستخدم حذف نفسه
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('users.delete')) {
            return false;
        }

        // CompanyOwner يمكنه حذف موظفيه وفنييه فقط
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                return $model->operators->contains($operator) &&
                       ($model->isEmployee() || $model->isTechnician());
            }
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }
}
