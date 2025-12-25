<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // SuperAdmin: إدارة كل المستخدمين
        if ($user->isSuperAdmin()) {
            return true;
        }

        // CompanyOwner: يشوف فقط موظفينه/فنييه عبر Users module
        if ($user->isCompanyOwner()) {
            return true;
        }

        // Admin (سلطة الطاقة): ممنوع إدارة المستخدمين
        return false;
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // (اختياري) المستخدم يشوف نفسه
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            if (! $model->isEmployee() && ! $model->isTechnician()) {
                return false;
            }

            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                return false;
            }

            return $model->operators()
                ->where('operators.id', $operator->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // CompanyOwner ينشئ موظف/فني فقط (التحقق النهائي بالداتا بالـ Controller/Request)
        return $user->isCompanyOwner();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            if (! $model->isEmployee() && ! $model->isTechnician()) {
                return false;
            }

            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                return false;
            }

            return $model->operators()
                ->where('operators.id', $operator->id)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isCompanyOwner()) {
            if (! $model->isEmployee() && ! $model->isTechnician()) {
                return false;
            }

            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                return false;
            }

            return $model->operators()
                ->where('operators.id', $operator->id)
                ->exists();
        }

        return false;
    }
}
