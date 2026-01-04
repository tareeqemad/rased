<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // المستخدمون
            ['name' => 'users.view', 'label' => 'عرض المستخدمين', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على عرض قائمة المستخدمين', 'order' => 1],
            ['name' => 'users.create', 'label' => 'إنشاء مستخدم', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على إنشاء مستخدم جديد', 'order' => 2],
            ['name' => 'users.update', 'label' => 'تحديث مستخدم', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على تحديث بيانات المستخدم', 'order' => 3],
            ['name' => 'users.delete', 'label' => 'حذف مستخدم', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على حذف المستخدم', 'order' => 4],

            // المشغلون
            ['name' => 'operators.view', 'label' => 'عرض المشغلين', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على عرض قائمة المشغلين', 'order' => 5],
            ['name' => 'operators.create', 'label' => 'إنشاء مشغل', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على إنشاء مشغل جديد', 'order' => 6],
            ['name' => 'operators.update', 'label' => 'تحديث مشغل', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على تحديث بيانات المشغل', 'order' => 7],
            ['name' => 'operators.delete', 'label' => 'حذف مشغل', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على حذف المشغل', 'order' => 8],

            // المولدات
            ['name' => 'generators.view', 'label' => 'عرض المولدات', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على عرض قائمة المولدات', 'order' => 9],
            ['name' => 'generators.create', 'label' => 'إنشاء مولد', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على إنشاء مولد جديد', 'order' => 10],
            ['name' => 'generators.update', 'label' => 'تحديث مولد', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على تحديث بيانات المولد', 'order' => 11],
            ['name' => 'generators.delete', 'label' => 'حذف مولد', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على حذف المولد', 'order' => 12],

            // سجلات التشغيل
            ['name' => 'operation_logs.view', 'label' => 'عرض سجلات التشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على عرض سجلات التشغيل', 'order' => 13],
            ['name' => 'operation_logs.create', 'label' => 'إنشاء سجل تشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على إنشاء سجل تشغيل جديد', 'order' => 14],
            ['name' => 'operation_logs.update', 'label' => 'تحديث سجل تشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على تحديث سجل التشغيل', 'order' => 15],
            ['name' => 'operation_logs.delete', 'label' => 'حذف سجل تشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على حذف سجل التشغيل', 'order' => 16],

            // كفاءة الوقود
            ['name' => 'fuel_efficiencies.view', 'label' => 'عرض كفاءة الوقود', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على عرض سجلات كفاءة الوقود', 'order' => 17],
            ['name' => 'fuel_efficiencies.create', 'label' => 'إنشاء سجل كفاءة', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على إنشاء سجل كفاءة وقود جديد', 'order' => 18],
            ['name' => 'fuel_efficiencies.update', 'label' => 'تحديث سجل كفاءة', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على تحديث سجل كفاءة الوقود', 'order' => 19],
            ['name' => 'fuel_efficiencies.delete', 'label' => 'حذف سجل كفاءة', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على حذف سجل كفاءة الوقود', 'order' => 20],

            // سجلات الصيانة
            ['name' => 'maintenance_records.view', 'label' => 'عرض سجلات الصيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على عرض سجلات الصيانة', 'order' => 21],
            ['name' => 'maintenance_records.create', 'label' => 'إنشاء سجل صيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على إنشاء سجل صيانة جديد', 'order' => 22],
            ['name' => 'maintenance_records.update', 'label' => 'تحديث سجل صيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على تحديث سجل الصيانة', 'order' => 23],
            ['name' => 'maintenance_records.delete', 'label' => 'حذف سجل صيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على حذف سجل الصيانة', 'order' => 24],

            // الامتثال والسلامة
            ['name' => 'compliance_safeties.view', 'label' => 'عرض الامتثال والسلامة', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على عرض سجلات الامتثال والسلامة', 'order' => 25],
            ['name' => 'compliance_safeties.create', 'label' => 'إنشاء سجل امتثال', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على إنشاء سجل امتثال جديد', 'order' => 26],
            ['name' => 'compliance_safeties.update', 'label' => 'تحديث سجل امتثال', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على تحديث سجل الامتثال', 'order' => 27],
            ['name' => 'compliance_safeties.delete', 'label' => 'حذف سجل امتثال', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على حذف سجل الامتثال', 'order' => 28],

            // أسعار التعرفة الكهربائية
            ['name' => 'electricity_tariff_prices.view', 'label' => 'عرض أسعار التعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على عرض أسعار التعرفة الكهربائية', 'order' => 29],
            ['name' => 'electricity_tariff_prices.create', 'label' => 'إنشاء سعر تعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على إنشاء سعر تعرفة جديد', 'order' => 30],
            ['name' => 'electricity_tariff_prices.update', 'label' => 'تحديث سعر تعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على تحديث سعر التعرفة', 'order' => 31],
            ['name' => 'electricity_tariff_prices.delete', 'label' => 'حذف سعر تعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على حذف سعر التعرفة', 'order' => 32],

            // إدارة الصلاحيات
            ['name' => 'permissions.manage', 'label' => 'إدارة الصلاحيات', 'group' => 'permissions', 'group_label' => 'الصلاحيات', 'description' => 'القدرة على إدارة صلاحيات المستخدمين', 'order' => 33],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('تم إنشاء/تحديث الصلاحيات بنجاح!');
    }
}
