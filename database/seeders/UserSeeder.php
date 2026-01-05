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

        // Super Admins - 5 super admins
        User::firstOrCreate(
            ['email' => 'tareq@rased.ps'],
            [
                'name' => 'طارق',
                'username' => 'tareeqemad',
                'password' => Hash::make('tareq123'),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'faheem@rased.ps'],
            [
                'name' => 'فهيم',
                'username' => 'faheem',
                'password' => Hash::make('tareq123'),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'adham@rased.ps'],
            [
                'name' => 'أدهم',
                'username' => 'adham',
                'password' => Hash::make('tareq123'),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@rased.ps'],
            [
                'name' => 'أدمن',
                'username' => 'admin',
                'password' => Hash::make('tareq123'),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'khalid@rased.ps'],
            [
                'name' => 'خالد',
                'username' => 'khalid',
                'password' => Hash::make('tareq123'),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
            ]
        );

        // Admin (سلطة الطاقة)
        User::firstOrCreate(
            ['email' => 'admin@power.ps'],
            [
                'name' => 'مدير سلطة الطاقة',
                'username' => 'admin_power',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'role_id' => $adminRole?->id,
                'status' => 'active',
            ]
        );

        // Company Owner - mmluk
        $mmlukOwner = User::firstOrCreate(
            ['email' => 'mmluk@operator.ps'],
            [
                'name' => 'مشغل المملوك',
                'username' => 'mmluk',
                'password' => Hash::make('tareq123'),
                'role' => Role::CompanyOwner,
                'role_id' => $companyOwnerRole?->id,
                'status' => 'active',
            ]
        );

        // إنشاء أدوار خاصة لمشغل المملوك (سيتم إنشاؤها في OperatorsWithDataSeeder)

        // 5 Employees for mmluk with different permissions
        $employee1 = User::firstOrCreate(
            ['email' => 'emp1@mmluk.ps'],
            [
                'name' => 'موظف 1 - عرض فقط',
                'username' => 'emp1_mmluk',
                'password' => Hash::make('password'),
                'role' => Role::Employee,
                'role_id' => $employeeRole?->id,
                'status' => 'active',
            ]
        );

        $employee2 = User::firstOrCreate(
            ['email' => 'emp2@mmluk.ps'],
            [
                'name' => 'موظف 2 - عرض وتحديث',
                'username' => 'emp2_mmluk',
                'password' => Hash::make('password'),
                'role' => Role::Employee,
                'role_id' => $employeeRole?->id,
                'status' => 'active',
            ]
        );

        $employee3 = User::firstOrCreate(
            ['email' => 'emp3@mmluk.ps'],
            [
                'name' => 'موظف 3 - كامل الصلاحيات',
                'username' => 'emp3_mmluk',
                'password' => Hash::make('password'),
                'role' => Role::Employee,
                'role_id' => $employeeRole?->id,
                'status' => 'active',
            ]
        );

        $employee4 = User::firstOrCreate(
            ['email' => 'emp4@mmluk.ps'],
            [
                'name' => 'موظف 4 - سجلات فقط',
                'username' => 'emp4_mmluk',
                'password' => Hash::make('password'),
                'role' => Role::Employee,
                'role_id' => $employeeRole?->id,
                'status' => 'active',
            ]
        );

        $employee5 = User::firstOrCreate(
            ['email' => 'emp5@mmluk.ps'],
            [
                'name' => 'موظف 5 - مولدات فقط',
                'username' => 'emp5_mmluk',
                'password' => Hash::make('password'),
                'role' => Role::Employee,
                'role_id' => $employeeRole?->id,
                'status' => 'active',
            ]
        );

        // منح صلاحيات مختلفة للموظفين
        $permissions = Permission::all();

        // Employee 1: عرض فقط
        $employee1->permissions()->sync($permissions->whereIn('name', [
            'generators.view',
            'operation_logs.view',
            'fuel_efficiencies.view',
            'maintenance_records.view',
            'compliance_safeties.view',
        ])->pluck('id'));

        // Employee 2: عرض وتحديث
        $employee2->permissions()->sync($permissions->whereIn('name', [
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
        $employee3->permissions()->sync($permissions->whereIn('name', [
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
        $employee4->permissions()->sync($permissions->whereIn('name', [
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
        $employee5->permissions()->sync($permissions->whereIn('name', [
            'generators.view',
            'generators.create',
            'generators.update',
        ])->pluck('id'));

        $this->command->info('تم إنشاء المستخدمين بنجاح!');
        $this->command->info('Super Admins: tareeqemad, faheem, adham, admin, khalid / tareq123');
        $this->command->info('Admin: admin_power / password');
        $this->command->info('Company Owner (mmluk): mmluk / tareq123');
        $this->command->info('Employees: emp1_mmluk, emp2_mmluk, emp3_mmluk, emp4_mmluk, emp5_mmluk / password');
    }
}
