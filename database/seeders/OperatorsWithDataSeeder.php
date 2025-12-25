<?php

namespace Database\Seeders;

use App\Helpers\ConstantsHelper;
use App\Models\Generator;
use App\Models\Operator;
use App\Models\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OperatorsWithDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // المحافظات المتاحة
        $governorates = [
            ['value' => '10', 'name' => 'غزة', 'cities' => ['غزة', 'الشجاعية', 'الرمال', 'الصبرة']],
            ['value' => '20', 'name' => 'الوسطى', 'cities' => ['دير البلح', 'المغازي', 'النصيرات', 'البريج']],
            ['value' => '30', 'name' => 'خانيونس', 'cities' => ['خانيونس', 'عبسان', 'القرارة', 'بني سهيلا']],
            ['value' => '40', 'name' => 'رفح', 'cities' => ['رفح', 'الشوكة', 'البروك', 'السودانية']],
        ];

        // إحداثيات تقريبية لكل محافظة (في فلسطين)
        $governorateCoordinates = [
            '10' => ['lat' => 31.3547, 'lng' => 34.3088], // غزة
            '20' => ['lat' => 31.4170, 'lng' => 34.3500], // الوسطى
            '30' => ['lat' => 31.3436, 'lng' => 34.3061], // خانيونس
            '40' => ['lat' => 31.2969, 'lng' => 34.2436], // رفح
        ];

        // أسماء عربية للمشغلين
        $operatorNames = [
            'مشغل الكهرباء المركزي',
            'مشغل الطاقة النظيفة',
            'مشغل المولدات الحديثة',
            'مشغل الكهرباء المتقدمة',
            'مشغل الطاقة المستدامة',
            'مشغل المولدات الذكية',
            'مشغل الكهرباء الموثوقة',
            'مشغل الطاقة المتكاملة',
            'مشغل المولدات الاحترافية',
            'مشغل الكهرباء المتميزة',
        ];

        // أسماء للموظفين
        $employeeNames = [
            'أحمد محمد علي',
            'محمد خالد حسن',
            'خالد أحمد محمود',
            'علي سعيد إبراهيم',
            'سعيد فتحي ناصر',
            'فتحي رامي سالم',
        ];

        // أسماء للمولدات
        $generatorNames = [
            'المولد الرئيسي',
            'المولد الاحتياطي الأول',
            'المولد الاحتياطي الثاني',
            'المولد المساعد',
            'المولد الإضافي',
        ];

        // إنشاء 10 مشغلين
        for ($i = 0; $i < 10; $i++) {
            $governorate = $governorates[$i % count($governorates)];
            $city = $governorate['cities'][array_rand($governorate['cities'])];
            $coords = $governorateCoordinates[$governorate['value']];
            
            // إضافة تغيير بسيط في الإحداثيات لجعلها مختلفة
            $latitude = $coords['lat'] + (rand(-50, 50) / 1000);
            $longitude = $coords['lng'] + (rand(-50, 50) / 1000);

            // إنشاء CompanyOwner
            $owner = User::create([
                'name' => 'صاحب شركة ' . ($i + 1),
                'username' => 'company_owner_' . ($i + 1),
                'email' => 'owner' . ($i + 1) . '@example.com',
                'password' => Hash::make('password'),
                'role' => Role::CompanyOwner,
            ]);

            // إنشاء Operator
            $operator = Operator::create([
                'name' => $operatorNames[$i],
                'email' => 'operator' . ($i + 1) . '@example.com',
                'phone' => '059' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'phone_alt' => '056' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'address' => 'شارع ' . ($i + 1) . '، ' . $city,
                'owner_id' => $owner->id,
                'unit_number' => 'UNIT-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'unit_code' => 'CODE-' . strtoupper(Str::random(4)),
                'unit_name' => 'وحدة ' . $operatorNames[$i],
                'governorate' => (int) $governorate['value'],
                'city' => $city,
                'detailed_address' => 'مبنى رقم ' . ($i + 1) . '، ' . $city . '، ' . $governorate['name'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'total_capacity' => rand(500, 2000),
                'generators_count' => 5,
                'synchronization_available' => rand(0, 1) === 1,
                'max_synchronization_capacity' => rand(300, 1500),
                'owner_name' => $owner->name,
                'owner_id_number' => str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'operation_entity' => rand(0, 1) === 1 ? 'same_owner' : 'other_party',
                'operator_id_number' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'beneficiaries_count' => rand(50, 500),
                'beneficiaries_description' => 'مستفيدون من خدمات الكهرباء في منطقة ' . $city,
                'environmental_compliance_status' => rand(0, 1) === 1 ? 'compliant' : 'non_compliant',
                'status' => 'active',
                'profile_completed' => true,
            ]);

            // إنشاء حوالي 5 مولدات لكل مشغل
            $generatorsCount = rand(4, 6); // بين 4 و 6 مولدات
            for ($j = 0; $j < $generatorsCount; $j++) {
                $generatorNumber = 'GEN-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . '-' . str_pad($j + 1, 2, '0', STR_PAD_LEFT);
                
                // جلب الثوابت
                $statusConstants = ConstantsHelper::getByName('حالة المولد');
                $engineTypeConstants = ConstantsHelper::getByName('نوع المحرك');
                $injectionSystemConstants = ConstantsHelper::getByName('نظام الحقن');
                $measurementIndicatorConstants = ConstantsHelper::getByName('مؤشر القياس');
                $technicalConditionConstants = ConstantsHelper::getByName('الحالة الفنية');
                $controlPanelTypeConstants = ConstantsHelper::getByName('نوع لوحة التحكم');
                $controlPanelStatusConstants = ConstantsHelper::getByName('حالة لوحة التحكم');

                // اختيار قيم عشوائية من الثوابت
                $statusValue = $statusConstants->isNotEmpty() ? $statusConstants->shuffle()->first()->value : 'active';
                $engineTypeValue = $engineTypeConstants->isNotEmpty() ? $engineTypeConstants->shuffle()->first()->value : 'diesel';
                $injectionSystemValue = $injectionSystemConstants->isNotEmpty() ? $injectionSystemConstants->shuffle()->first()->value : 'mechanical';
                $measurementIndicatorValue = $measurementIndicatorConstants->isNotEmpty() ? $measurementIndicatorConstants->shuffle()->first()->value : 'mechanical';
                $technicalConditionValue = $technicalConditionConstants->isNotEmpty() ? $technicalConditionConstants->shuffle()->first()->value : 'good';
                $controlPanelTypeValue = $controlPanelTypeConstants->isNotEmpty() ? $controlPanelTypeConstants->shuffle()->first()->value : 'manual';
                $controlPanelStatusValue = $controlPanelStatusConstants->isNotEmpty() ? $controlPanelStatusConstants->shuffle()->first()->value : 'active';

                $generator = Generator::create([
                    'name' => $generatorNames[$j % count($generatorNames)],
                    'generator_number' => $generatorNumber,
                    'operator_id' => $operator->id,
                    'description' => 'مولد كهربائي بقدرة ' . rand(50, 500) . ' KVA',
                    'status' => $statusValue,
                    'capacity_kva' => rand(50, 500),
                    'power_factor' => round(rand(80, 95) / 100, 2),
                    'voltage' => rand(220, 380),
                    'frequency' => 50,
                    'engine_type' => $engineTypeValue,
                    'manufacturing_year' => rand(2015, 2024),
                    'injection_system' => $injectionSystemValue,
                    'fuel_consumption_rate' => round(rand(10, 50) + (rand(0, 99) / 100), 2),
                    'internal_tank_capacity' => rand(100, 500),
                    'measurement_indicator' => $measurementIndicatorValue,
                    'technical_condition' => $technicalConditionValue,
                    'last_major_maintenance_date' => now()->subDays(rand(30, 365)),
                    'control_panel_available' => rand(0, 1) === 1,
                    'control_panel_type' => $controlPanelTypeValue,
                    'control_panel_status' => $controlPanelStatusValue,
                    'operating_hours' => rand(1000, 10000),
                    'external_fuel_tank' => rand(0, 1) === 1,
                    'fuel_tanks_count' => rand(0, 3),
                ]);
            }

            // تحديث عدد المولدات في Operator
            $operator->update(['generators_count' => $generatorsCount]);

            // إنشاء 6 موظفين لكل مشغل (3 موظفين + 3 فنيين)
            $roles = [
                Role::Employee,
                Role::Employee,
                Role::Employee,
                Role::Technician,
                Role::Technician,
                Role::Technician,
            ];

            for ($k = 0; $k < 6; $k++) {
                $employee = User::create([
                    'name' => $employeeNames[$k],
                    'username' => 'user_' . ($i + 1) . '_' . ($k + 1),
                    'email' => 'user' . ($i + 1) . '_' . ($k + 1) . '@example.com',
                    'password' => Hash::make('password'),
                    'role' => $roles[$k],
                ]);

                // ربط الموظف/الفني بالمشغل
                $operator->users()->attach($employee->id);
            }
        }

        $this->command->info('تم إنشاء 10 مشغلين مع بياناتهم بنجاح!');
        $this->command->info('- كل مشغل لديه صاحب شركة (CompanyOwner)');
        $this->command->info('- كل مشغل لديه حوالي 5 مولدات');
        $this->command->info('- كل مشغل لديه 6 موظفين (3 موظفين + 3 فنيين)');
        $this->command->info('- كل مشغل في محافظة مختلفة بإحداثيات مختلفة');
    }
}

