<?php

namespace App\Policies;

use App\Models\MaintenanceRecord;
use App\Models\User;

class MaintenanceRecordPolicy
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
        if ($user->hasPermission('maintenance_records.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('maintenance_records.view')) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator ?? null;
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
        if ($user->hasPermission('maintenance_records.create')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('maintenance_records.update')) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator ?? null;
        if (! $operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('maintenance_records.delete')) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator ?? null;
        if (! $operator) {
            return false;
        }

        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        return $user->isSuperAdmin();
    }
}
