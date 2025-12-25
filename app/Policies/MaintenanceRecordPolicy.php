<?php

namespace App\Policies;

use App\Models\MaintenanceRecord;
use App\Models\User;

class MaintenanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->hasPermission('maintenance_records.view')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    public function view(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if (! $user->hasPermission('maintenance_records.view')) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator;

        return $user->belongsToOperator($operator);
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasPermission('maintenance_records.create')) {
            return true;
        }

        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    public function update(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('maintenance_records.update')) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator;

        return $user->belongsToOperator($operator);
    }

    public function delete(User $user, MaintenanceRecord $maintenanceRecord): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasPermission('maintenance_records.delete')) {
            return false;
        }

        $operator = $maintenanceRecord->generator->operator;

        return $user->belongsToOperator($operator);
    }
}
