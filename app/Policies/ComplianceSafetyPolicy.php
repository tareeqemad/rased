<?php

namespace App\Policies;

use App\Models\ComplianceSafety;
use App\Models\User;

class ComplianceSafetyPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->hasPermission('compliance_safeties.view')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    public function view(User $user, ComplianceSafety $complianceSafety): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if (! $user->hasPermission('compliance_safeties.view')) {
            return false;
        }

        return $user->belongsToOperator($complianceSafety->operator);
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasPermission('compliance_safeties.create')) {
            return true;
        }

        return $user->isCompanyOwner();
    }

    public function update(User $user, ComplianceSafety $complianceSafety): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('compliance_safeties.update')) {
            return false;
        }

        return $user->belongsToOperator($complianceSafety->operator);
    }

    public function delete(User $user, ComplianceSafety $complianceSafety): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('compliance_safeties.delete')) {
            return false;
        }

        return $user->belongsToOperator($complianceSafety->operator);
    }
}
