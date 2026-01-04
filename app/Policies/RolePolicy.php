<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            // المشغل يمكنه رؤية الأدوار النظامية وأدوار مشغله فقط
            if ($role->is_system) {
                return true;
            }
            // الأدوار الخاصة بمشغل - يمكن رؤيتها فقط إذا كانت لمشغله
            if ($role->operator_id) {
                $operator = $role->operator;
                return $operator && $user->ownsOperator($operator);
            }
            // الأدوار العامة (operator_id = null) - لا يمكن رؤيتها
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            // المشغل يمكنه تحديث أدواره فقط (لا يمكن تحديث الأدوار النظامية)
            if ($role->is_system || !$role->operator_id) {
                return false;
            }
            $operator = $role->operator;
            return $operator && $user->ownsOperator($operator);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        // لا يمكن حذف الأدوار النظامية
        if ($role->is_system) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            // المشغل يمكنه حذف أدواره فقط
            if (!$role->operator_id) {
                return false;
            }
            $operator = $role->operator;
            return $operator && $user->ownsOperator($operator);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
