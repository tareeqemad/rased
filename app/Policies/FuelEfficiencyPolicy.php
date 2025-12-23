<?php

namespace App\Policies;

use App\Models\FuelEfficiency;
use App\Models\User;

class FuelEfficiencyPolicy
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
        if ($user->hasPermission('fuel_efficiencies.view')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee() || $user->isTechnician();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('fuel_efficiencies.view')) {
            return false;
        }

        $operator = $fuelEfficiency->generator->operator ?? null;
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
        if ($user->hasPermission('fuel_efficiencies.create')) {
            return true;
        }

        // Fallback للأدوار
        return $user->isCompanyOwner() || $user->isEmployee();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('fuel_efficiencies.update')) {
            return false;
        }

        $operator = $fuelEfficiency->generator->operator ?? null;
        if (! $operator) {
            return false;
        }

        return $user->belongsToOperator($operator);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الديناميكية
        if (! $user->hasPermission('fuel_efficiencies.delete')) {
            return false;
        }

        $operator = $fuelEfficiency->generator->operator ?? null;
        if (! $operator) {
            return false;
        }

        return $user->ownsOperator($operator);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FuelEfficiency $fuelEfficiency): bool
    {
        return $user->isSuperAdmin();
    }
}
