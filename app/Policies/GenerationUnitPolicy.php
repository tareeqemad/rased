<?php

namespace App\Policies;

use App\Models\GenerationUnit;
use App\Models\User;

class GenerationUnitPolicy
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
        if ($user->hasPermission('generation_units.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GenerationUnit $generationUnit): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Company Owner يمكنه رؤية وحدات التوليد الخاصة به حتى لو لم يكن لديه الصلاحية الديناميكية
        if ($user->isCompanyOwner()) {
            return $user->ownsOperator($generationUnit->operator);
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generation_units.view')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل
        return $user->belongsToOperator($generationUnit->operator);
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

        // Company Owner يمكنه إضافة وحدات التوليد حتى لو لم يكن معتمد
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            return $operator !== null;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('generation_units.create')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isTechnician();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GenerationUnit $generationUnit): bool
    {
        // Admin لا يمكنه التحديث
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('generation_units.update')) {
            // التحقق من العلاقة مع المشغل
            return $user->belongsToOperator($generationUnit->operator);
        }

        // Fallback للأدوار: CompanyOwner وTechnician يمكنهم التعديل إذا كانت الوحدة تتبع مشغلهم
        if ($user->isCompanyOwner() || $user->isTechnician()) {
            return $user->belongsToOperator($generationUnit->operator);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GenerationUnit $generationUnit): bool
    {
        // Admin لا يمكنه الحذف
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('generation_units.delete')) {
            return false;
        }

        // التحقق من العلاقة مع المشغل (يجب أن يكون صاحب المشغل)
        return $user->ownsOperator($generationUnit->operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GenerationUnit $generationUnit): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GenerationUnit $generationUnit): bool
    {
        return $user->isSuperAdmin();
    }
}

