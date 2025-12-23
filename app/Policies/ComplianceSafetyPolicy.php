<?php

namespace App\Policies;

use App\Models\ComplianceSafety;
use App\Models\User;

class ComplianceSafetyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('compliance_safeties.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ComplianceSafety $complianceSafety): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('compliance_safeties.view')) {
            return false;
        }

        $operator = $complianceSafety->operator;
        if (! $operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if ($user->hasPermission('compliance_safeties.create')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ComplianceSafety $complianceSafety): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('compliance_safeties.update')) {
            return false;
        }

        $operator = $complianceSafety->operator;
        if (! $operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ComplianceSafety $complianceSafety): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('compliance_safeties.delete')) {
            return false;
        }

        $operator = $complianceSafety->operator;
        if (! $operator) {
            return false;
        }

        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ComplianceSafety $complianceSafety): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ComplianceSafety $complianceSafety): bool
    {
        return $user->isSuperAdmin();
    }
}
