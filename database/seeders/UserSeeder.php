<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على الأدوار
        $superAdminRole = RoleModel::where('name', 'super_admin')->first();
        $adminRole = RoleModel::where('name', 'admin')->first();
        $companyOwnerRole = RoleModel::where('name', 'company_owner')->first();
        $employeeRole = RoleModel::where('name', 'employee')->first();

        // Super Admin
        User::create([
            'name' => 'مدير النظام',
            'username' => 'tareeqemad',
            'email' => 'admin@rased.ps',
            'password' => Hash::make('Tar@2025'),
            'role' => Role::SuperAdmin,
            'role_id' => $superAdminRole?->id,
        ]);

        // Admin (سلطة الطاقة)
        User::create([
            'name' => 'مدير سلطة الطاقة',
            'username' => 'admin_power',
            'email' => 'admin@power.ps',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
            'role_id' => $adminRole?->id,
        ]);

        // Company Owner - mmluk
        $mmlukOwner = User::create([
            'name' => 'مشغل المملوك',
            'username' => 'mmluk',
            'email' => 'mmluk@operator.ps',
            'password' => Hash::make('tareq123'),
            'role' => Role::CompanyOwner,
            'role_id' => $companyOwnerRole?->id,
        ]);

        // 5 Employees for mmluk with different permissions
        $employee1 = User::create([
            'name' => 'موظف 1 - عرض فقط',
            'username' => 'emp1_mmluk',
            'email' => 'emp1@mmluk.ps',
            'password' => Hash::make('password'),
            'role' => Role::Employee,
            'role_id' => $employeeRole?->id,
        ]);

        $employee2 = User::create([
            'name' => 'موظف 2 - عرض وتحديث',
            'username' => 'emp2_mmluk',
            'email' => 'emp2@mmluk.ps',
            'password' => Hash::make('password'),
            'role' => Role::Employee,
            'role_id' => $employeeRole?->id,
        ]);

        $employee3 = User::create([
            'name' => 'موظف 3 - كامل الصلاحيات',
            'username' => 'emp3_mmluk',
            'email' => 'emp3@mmluk.ps',
            'password' => Hash::make('password'),
            'role' => Role::Employee,
            'role_id' => $employeeRole?->id,
        ]);

        $employee4 = User::create([
            'name' => 'موظف 4 - سجلات فقط',
            'username' => 'emp4_mmluk',
            'email' => 'emp4@mmluk.ps',
            'password' => Hash::make('password'),
            'role' => Role::Employee,
            'role_id' => $employeeRole?->id,
        ]);

        $employee5 = User::create([
            'name' => 'موظف 5 - مولدات فقط',
            'username' => 'emp5_mmluk',
            'email' => 'emp5@mmluk.ps',
            'password' => Hash::make('password'),
            'role' => Role::Employee,
            'role_id' => $employeeRole?->id,
        ]);

        // منح صلاحيات مختلفة للموظفين
        $permissions = Permission::all();

        // Employee 1: عرض فقط
        $employee1->permissions()->attach($permissions->whereIn('name', [
            'generators.view',
            'operation_logs.view',
            'fuel_efficiencies.view',
            'maintenance_records.view',
            'compliance_safeties.view',
        ])->pluck('id'));

        // Employee 2: عرض وتحديث
        $employee2->permissions()->attach($permissions->whereIn('name', [
            'generators.view',
            'generators.update',
            'operation_logs.view',
            'operation_logs.update',
            'operation_logs.create',
            'fuel_efficiencies.view',
            'fuel_efficiencies.update',
            'fuel_efficiencies.create',
            'maintenance_records.view',
            'maintenance_records.update',
            'maintenance_records.create',
        ])->pluck('id'));

        // Employee 3: كامل الصلاحيات (ما عدا الحذف)
        $employee3->permissions()->attach($permissions->whereIn('name', [
            'generators.view',
            'generators.create',
            'generators.update',
            'operation_logs.view',
            'operation_logs.create',
            'operation_logs.update',
            'fuel_efficiencies.view',
            'fuel_efficiencies.create',
            'fuel_efficiencies.update',
            'maintenance_records.view',
            'maintenance_records.create',
            'maintenance_records.update',
            'compliance_safeties.view',
            'compliance_safeties.create',
            'compliance_safeties.update',
        ])->pluck('id'));

        // Employee 4: سجلات فقط
        $employee4->permissions()->attach($permissions->whereIn('name', [
            'operation_logs.view',
            'operation_logs.create',
            'operation_logs.update',
            'fuel_efficiencies.view',
            'fuel_efficiencies.create',
            'fuel_efficiencies.update',
            'maintenance_records.view',
            'maintenance_records.create',
            'maintenance_records.update',
        ])->pluck('id'));

        // Employee 5: مولدات فقط
        $employee5->permissions()->attach($permissions->whereIn('name', [
            'generators.view',
            'generators.create',
            'generators.update',
        ])->pluck('id'));

        $this->command->info('تم إنشاء المستخدمين بنجاح!');
        $this->command->info('Super Admin: tareeqemad / Tar@2025');
        $this->command->info('Admin: admin_power / password');
        $this->command->info('Company Owner (mmluk): mmluk / tareq123');
        $this->command->info('Employees: emp1_mmluk, emp2_mmluk, emp3_mmluk, emp4_mmluk, emp5_mmluk / password');
    }
}
