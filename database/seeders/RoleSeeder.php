<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'label' => 'مدير النظام',
                'description' => 'لديه جميع الصلاحيات في النظام',
                'is_system' => true,
                'order' => 1,
            ],
            [
                'name' => 'admin',
                'label' => 'سلطة الطاقة',
                'description' => 'يراقب ملفات المشغلين وبيانات المولدات - نظام استعلام فقط',
                'is_system' => true,
                'order' => 2,
            ],
            [
                'name' => 'company_owner',
                'label' => 'مشغل',
                'description' => 'مشغل المولدات - يمكنه إدارة بياناته وموظفيه',
                'is_system' => true,
                'order' => 3,
            ],
            [
                'name' => 'employee',
                'label' => 'موظف',
                'description' => 'موظف يعمل تحت إشراف المشغل',
                'is_system' => true,
                'order' => 4,
            ],
            [
                'name' => 'technician',
                'label' => 'فني',
                'description' => 'فني يعمل تحت إشراف المشغل',
                'is_system' => true,
                'order' => 5,
            ],
        ];

        $permissions = Permission::all();

        foreach ($roles as $roleData) {
            $role = Role::create($roleData);

            // منح الصلاحيات حسب الدور
            if ($role->name === 'super_admin') {
                // SuperAdmin لديه جميع الصلاحيات
                $role->permissions()->attach($permissions->pluck('id'));
            } elseif ($role->name === 'admin') {
                // Admin (سلطة الطاقة) - صلاحيات المراقبة والاستعلام فقط
                $role->permissions()->attach($permissions->whereIn('name', [
                    'operators.view',
                    'generators.view',
                    'operation_logs.view',
                    'fuel_efficiencies.view',
                    'maintenance_records.view',
                    'compliance_safeties.view',
                ])->pluck('id'));
            } elseif ($role->name === 'company_owner') {
                // CompanyOwner - صلاحيات كاملة على بياناته وموظفيه
                $role->permissions()->attach($permissions->whereIn('name', [
                    'operators.view',
                    'operators.update',
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
                    'users.view',
                    'users.create',
                    'users.update',
                    'permissions.manage',
                ])->pluck('id'));
            }
            // employee و technician لا يحصلون على صلاحيات افتراضية - يتم منحها يدوياً
        }

        $this->command->info('تم إنشاء الأدوار بنجاح!');
    }
}
