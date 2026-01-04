<?php

namespace Database\Seeders;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Models\ComplianceSafety;
use App\Models\FuelEfficiency;
use App\Models\FuelTank;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\OperationLog;
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
        $this->command->info('بدء إنشاء البيانات...');

        // الحصول على المحافظات والمدن من الثوابت
        $governoratesMaster = ConstantsHelper::get(1); // رقم ثابت المحافظات
        $citiesMaster = ConstantsHelper::get(20); // رقم ثابت المدن
        
        if ($governoratesMaster->isEmpty() || $citiesMaster->isEmpty()) {
            $this->command->error('يجب تشغيل ConstantSeeder أولاً!');
            return;
        }
        
        // تحضير بيانات المحافظات مع المدن من الثوابت
        $governoratesData = [];
        foreach ($governoratesMaster as $gov) {
            $cities = ConstantsHelper::getCitiesByGovernorate($gov->id);
            if ($cities->isNotEmpty()) {
                $governoratesData[] = [
                    'id' => $gov->id,
                    'code' => $gov->code,
                    'value' => (int) $gov->value,
                    'name' => $gov->label,
                    'cities' => $cities->map(function($city) {
                        return [
                            'id' => $city->id,
                            'code' => $city->code,
                            'label' => $city->label,
                        ];
                    })->toArray(),
                ];
            }
        }
        
        if (empty($governoratesData)) {
            $this->command->error('لا توجد محافظات مع مدن في الثوابت!');
            return;
        }

        // إحداثيات تقريبية لكل محافظة (استخدام value من الثوابت)
        $governorateCoordinates = [
            10 => ['lat' => 31.3547, 'lng' => 34.3088], // شمال غزة
            20 => ['lat' => 31.3547, 'lng' => 34.3088], // غزة
            30 => ['lat' => 31.4170, 'lng' => 34.3500], // الوسطى
            40 => ['lat' => 31.3436, 'lng' => 34.3061], // خانيونس
            50 => ['lat' => 31.2969, 'lng' => 34.2436], // رفح
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
            'رامي وليد كمال',
            'وليد يوسف سمير',
            'يوسف تامر بدر',
            'تامر زياد عمر',
        ];

        // أسماء للمولدات
        $generatorNames = [
            'المولد الرئيسي',
            'المولد الاحتياطي الأول',
            'المولد الاحتياطي الثاني',
            'المولد المساعد',
            'المولد الإضافي',
        ];

        // أسماء الفنيين
        $technicianNames = [
            'فني صيانة 1',
            'فني صيانة 2',
            'فني صيانة 3',
            'فني كهرباء 1',
            'فني كهرباء 2',
        ];

        // أنواع الصيانة
        $maintenanceTypes = [
            'صيانة دورية',
            'صيانة وقائية',
            'صيانة طارئة',
            'صيانة كبرى',
            'صيانة عادية',
        ];

        // جمع جميع المولدات لاستخدامها في السجلات
        $allGenerators = collect();
        $allOperators = collect();
        $mmlukGenerators = collect(); // مولدات مشغل المملوك
        $mmlukOperator = null; // مشغل المملوك

        // جلب الثوابت (نستخدمها لاحقاً)
        $statusConstants = ConstantsHelper::get(3); // حالة المولد
        $engineTypeConstants = ConstantsHelper::get(4); // نوع المحرك
        $injectionSystemConstants = ConstantsHelper::get(5); // نظام الحقن
        $measurementIndicatorConstants = ConstantsHelper::get(6); // مؤشر القياس
        $technicalConditionConstants = ConstantsHelper::get(7); // الحالة الفنية
        $controlPanelTypeConstants = ConstantsHelper::get(8); // نوع لوحة التحكم
        $controlPanelStatusConstants = ConstantsHelper::get(9); // حالة لوحة التحكم

        // دالة مساعدة للحصول على قيمة ثابت
        $getConstantValue = function($collection, $default) {
            return $collection->isNotEmpty() ? $collection->random()->value : $default;
        };

        // إنشاء مشغل المملوك أولاً
        $this->command->info('جاري إنشاء مشغل المملوك...');
        $mmlukOwner = User::where('username', 'mmluk')->first();
        
        if ($mmlukOwner) {
            // التحقق من عدم وجود المشغل مسبقاً
            $existingOperator = Operator::where('name', 'مشغل المملوك')->first();
            if (!$existingOperator) {
                // الحصول على محافظة غزة ومدينة غزة من الثوابت
                $gazaGovernorate = $governoratesMaster->where('code', 'GZ')->first();
                $gazaCity = $citiesMaster->where('code', 'GZ')->first();
                
                if ($gazaGovernorate && $gazaCity) {
                    $governorateEnum = Governorate::fromValue((int) $gazaGovernorate->value);
                    
                    // توليد رقم الوحدة وكود الوحدة
                    $unitNumber = Operator::getNextUnitNumber($governorateEnum, $gazaCity->id);
                    $unitCode = Operator::generateUnitCode($governorateEnum, $gazaCity->id, $unitNumber);
                    
                    $mmlukOperator = Operator::create([
                        'name' => 'مشغل المملوك',
                        'email' => 'info@mmluk.ps',
                        'phone' => '0599123456',
                        'phone_alt' => '0599123457',
                        'address' => 'غزة - شارع المملوك',
                        'owner_id' => $mmlukOwner->id,
                        'unit_number' => $unitNumber,
                        'unit_code' => $unitCode,
                        'unit_name' => 'وحدة المملوك',
                        'governorate' => $governorateEnum,
                        'city_id' => $gazaCity->id,
                        'detailed_address' => 'غزة - شارع المملوك - مبنى رقم 5',
                        'latitude' => 31.3547,
                        'longitude' => 34.3088,
                        'total_capacity' => 500,
                        'generators_count' => 4,
                        'synchronization_available' => true,
                        'max_synchronization_capacity' => 400,
                        'beneficiaries_count' => 150,
                        'beneficiaries_description' => 'سكان المنطقة والمؤسسات',
                        'environmental_compliance_status' => 'compliant',
                        'status' => 'active',
                        'profile_completed' => true,
                    ]);
                }

                $allOperators->push($mmlukOperator);

                // إنشاء 4 مولدات لمشغل المملوك
                $mmlukGeneratorsData = [
                    [
                        'name' => 'مولد المملوك 1',
                        'description' => 'مولد ديزل بقوة 100 كيلو فولت أمبير',
                        'capacity_kva' => 100,
                        'power_factor' => 0.8,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type' => $getConstantValue($engineTypeConstants, 'diesel'),
                        'manufacturing_year' => 2020,
                        'injection_system' => $getConstantValue($injectionSystemConstants, 'mechanical'),
                        'fuel_consumption_rate' => 25.5,
                        'ideal_fuel_efficiency' => 0.5,
                        'internal_tank_capacity' => 200,
                        'measurement_indicator' => $getConstantValue($measurementIndicatorConstants, 'mechanical'),
                        'technical_condition' => $getConstantValue($technicalConditionConstants, 'good'),
                        'control_panel_available' => true,
                        'control_panel_type' => $getConstantValue($controlPanelTypeConstants, 'manual'),
                        'control_panel_status' => $getConstantValue($controlPanelStatusConstants, 'active'),
                        'operating_hours' => 5000,
                        'external_fuel_tank' => true,
                        'fuel_tanks_count' => 2,
                        'status' => $getConstantValue($statusConstants, 'active'),
                    ],
                    [
                        'name' => 'مولد المملوك 2',
                        'description' => 'مولد ديزل بقوة 150 كيلو فولت أمبير',
                        'capacity_kva' => 150,
                        'power_factor' => 0.85,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type' => $getConstantValue($engineTypeConstants, 'diesel'),
                        'manufacturing_year' => 2021,
                        'injection_system' => $getConstantValue($injectionSystemConstants, 'mechanical'),
                        'fuel_consumption_rate' => 35.0,
                        'ideal_fuel_efficiency' => 0.55,
                        'internal_tank_capacity' => 300,
                        'measurement_indicator' => $getConstantValue($measurementIndicatorConstants, 'mechanical'),
                        'technical_condition' => $getConstantValue($technicalConditionConstants, 'good'),
                        'control_panel_available' => true,
                        'control_panel_type' => $getConstantValue($controlPanelTypeConstants, 'manual'),
                        'control_panel_status' => $getConstantValue($controlPanelStatusConstants, 'active'),
                        'operating_hours' => 3500,
                        'external_fuel_tank' => true,
                        'fuel_tanks_count' => 2,
                        'status' => $getConstantValue($statusConstants, 'active'),
                    ],
                    [
                        'name' => 'مولد المملوك 3',
                        'description' => 'مولد ديزل بقوة 200 كيلو فولت أمبير',
                        'capacity_kva' => 200,
                        'power_factor' => 0.9,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type' => $getConstantValue($engineTypeConstants, 'diesel'),
                        'manufacturing_year' => 2019,
                        'injection_system' => $getConstantValue($injectionSystemConstants, 'mechanical'),
                        'fuel_consumption_rate' => 45.5,
                        'ideal_fuel_efficiency' => 0.48,
                        'internal_tank_capacity' => 400,
                        'measurement_indicator' => $getConstantValue($measurementIndicatorConstants, 'mechanical'),
                        'technical_condition' => $getConstantValue($technicalConditionConstants, 'good'),
                        'control_panel_available' => true,
                        'control_panel_type' => $getConstantValue($controlPanelTypeConstants, 'manual'),
                        'control_panel_status' => $getConstantValue($controlPanelStatusConstants, 'active'),
                        'operating_hours' => 8000,
                        'external_fuel_tank' => true,
                        'fuel_tanks_count' => 2,
                        'status' => $getConstantValue($statusConstants, 'active'),
                    ],
                    [
                        'name' => 'مولد المملوك 4',
                        'description' => 'مولد ديزل بقوة 50 كيلو فولت أمبير',
                        'capacity_kva' => 50,
                        'power_factor' => 0.75,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type' => $getConstantValue($engineTypeConstants, 'diesel'),
                        'manufacturing_year' => 2022,
                        'injection_system' => $getConstantValue($injectionSystemConstants, 'mechanical'),
                        'fuel_consumption_rate' => 15.0,
                        'ideal_fuel_efficiency' => 0.52,
                        'internal_tank_capacity' => 150,
                        'measurement_indicator' => $getConstantValue($measurementIndicatorConstants, 'mechanical'),
                        'technical_condition' => $getConstantValue($technicalConditionConstants, 'good'),
                        'control_panel_available' => true,
                        'control_panel_type' => $getConstantValue($controlPanelTypeConstants, 'manual'),
                        'control_panel_status' => $getConstantValue($controlPanelStatusConstants, 'active'),
                        'operating_hours' => 2000,
                        'external_fuel_tank' => false,
                        'fuel_tanks_count' => 0,
                        'status' => $getConstantValue($statusConstants, 'active'),
                    ],
                ];

                foreach ($mmlukGeneratorsData as $genData) {
                    // توليد رقم المولد تلقائياً بناءً على unit_code
                    $genData['generator_number'] = Generator::getNextGeneratorNumber($mmlukOperator->id);
                    
                    $generator = Generator::create([
                        'operator_id' => $mmlukOperator->id,
                        ...$genData,
                    ]);

                    $allGenerators->push($generator);
                    $mmlukGenerators->push($generator); // إضافة لمولدات المملوك

                    // إنشاء خزانات الوقود إذا كان المولد يحتوي على خزانات
                    if (isset($genData['fuel_tanks_count']) && $genData['fuel_tanks_count'] > 0) {
                        for ($t = 0; $t < $genData['fuel_tanks_count']; $t++) {
                            // توليد كود الخزان تلقائياً
                            $tankCode = FuelTank::getNextTankCode($generator->id);
                            
                            FuelTank::create([
                                'generator_id' => $generator->id,
                                'tank_code' => $tankCode,
                                'capacity' => rand(100, 500),
                                'location' => ['داخلي', 'خارجي', 'أرضي', 'علوي'][rand(0, 3)],
                                'filtration_system_available' => rand(0, 1) === 1,
                                'condition' => ['جيد', 'ممتاز', 'مقبول'][rand(0, 2)],
                                'material' => ['حديد', 'بلاستيك', 'فولاذ'][rand(0, 2)],
                                'usage' => ['رئيسي', 'احتياطي', 'إضافي'][rand(0, 2)],
                                'measurement_method' => ['ميكانيكي', 'إلكتروني', 'يدوي'][rand(0, 2)],
                                'order' => $t + 1,
                            ]);
                        }
                    }
                }

                // ربط الموظفين الخمسة بالمشغل
                $employees = User::whereIn('username', [
                    'emp1_mmluk',
                    'emp2_mmluk',
                    'emp3_mmluk',
                    'emp4_mmluk',
                    'emp5_mmluk',
                ])->get();

                foreach ($employees as $employee) {
                    $mmlukOperator->users()->attach($employee->id);
                }

                $this->command->info('✓ تم إنشاء مشغل المملوك مع 4 مولدات و ' . $employees->count() . ' موظف');
            } else {
                $this->command->info('مشغل المملوك موجود بالفعل، سيتم استخدامه');
                $allOperators->push($existingOperator);
                $mmlukOperator = $existingOperator; // حفظ للملوك
                // إضافة مولدات المشغل الموجود
                $existingGenerators = $existingOperator->generators;
                $allGenerators = $allGenerators->merge($existingGenerators);
                $mmlukGenerators = $mmlukGenerators->merge($existingGenerators); // إضافة لمولدات المملوك
            }
        } else {
            $this->command->warn('لم يتم العثور على المستخدم mmluk، سيتم تخطي مشغل المملوك');
        }

        // إنشاء 10 مشغلين
        for ($i = 0; $i < 10; $i++) {
            // اختيار محافظة عشوائية
            $governorateData = $governoratesData[$i % count($governoratesData)];
            
            // اختيار مدينة عشوائية من المحافظة المختارة
            $cityData = $governorateData['cities'][array_rand($governorateData['cities'])];
            
            $coords = $governorateCoordinates[$governorateData['value']] ?? ['lat' => 31.3547, 'lng' => 34.3088];
            
            // إضافة تغيير بسيط في الإحداثيات
            $latitude = $coords['lat'] + (rand(-50, 50) / 1000);
            $longitude = $coords['lng'] + (rand(-50, 50) / 1000);

            // تحويل المحافظة إلى enum
            $governorateEnum = Governorate::fromValue($governorateData['value']);
            
            // توليد رقم الوحدة وكود الوحدة بناءً على المحافظة والمدينة المختارة
            // ملاحظة: يجب استخدام نفس القيم (governorateEnum و cityData['id']) في Operator::create()
            $unitNumber = Operator::getNextUnitNumber($governorateEnum, $cityData['id']);
            $unitCode = Operator::generateUnitCode($governorateEnum, $cityData['id'], $unitNumber);

            // إنشاء CompanyOwner
            $owner = User::firstOrCreate(
                ['email' => 'owner' . ($i + 1) . '@example.com'],
                [
                    'name' => 'صاحب شركة ' . ($i + 1),
                    'username' => 'company_owner_' . ($i + 1),
                    'password' => Hash::make('password'),
                    'role' => Role::CompanyOwner,
                    'status' => 'active',
                ]
            );

            // إنشاء Operator
            // ملاحظة: governorate و city_id يجب أن تكون نفس القيم المستخدمة في توليد unit_number و unit_code
            $operator = Operator::create([
                'name' => $operatorNames[$i],
                'email' => 'operator' . ($i + 1) . '@example.com',
                'phone' => '059' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'phone_alt' => '056' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'address' => 'شارع ' . ($i + 1) . '، ' . $cityData['label'],
                'owner_id' => $owner->id,
                'unit_number' => $unitNumber, // تم توليده بناءً على governorateEnum و cityData['id']
                'unit_code' => $unitCode, // تم توليده بناءً على governorateEnum و cityData['id']
                'unit_name' => 'وحدة ' . $operatorNames[$i],
                'governorate' => $governorateEnum, // نفس القيمة المستخدمة في توليد unit_code
                'city_id' => $cityData['id'], // نفس القيمة المستخدمة في توليد unit_code
                'detailed_address' => 'مبنى رقم ' . ($i + 1) . '، ' . $cityData['label'] . '، ' . $governorateData['name'],
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
                'beneficiaries_description' => 'مستفيدون من خدمات الكهرباء في منطقة ' . $cityData['label'],
                'environmental_compliance_status' => rand(0, 1) === 1 ? 'compliant' : 'non_compliant',
                'status' => 'active',
                'profile_completed' => true,
            ]);

            $allOperators->push($operator);

            // إنشاء حوالي 5 مولدات لكل مشغل
            $generatorsCount = rand(4, 6);
            for ($j = 0; $j < $generatorsCount; $j++) {
                // توليد رقم المولد تلقائياً بناءً على unit_code
                $generatorNumber = Generator::getNextGeneratorNumber($operator->id);
                if (!$generatorNumber) {
                    $this->command->warn("تم الوصول إلى الحد الأقصى لعدد المولدات للمشغل {$operator->id}");
                    break;
                }
                
                // اختيار قيم عشوائية من الثوابت
                $statusValue = $getConstantValue($statusConstants, 'active');
                $engineTypeValue = $getConstantValue($engineTypeConstants, 'diesel');
                $injectionSystemValue = $getConstantValue($injectionSystemConstants, 'mechanical');
                $measurementIndicatorValue = $getConstantValue($measurementIndicatorConstants, 'mechanical');
                $technicalConditionValue = $getConstantValue($technicalConditionConstants, 'good');
                $controlPanelTypeValue = $getConstantValue($controlPanelTypeConstants, 'manual');
                $controlPanelStatusValue = $getConstantValue($controlPanelStatusConstants, 'active');

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
                    'ideal_fuel_efficiency' => round(0.4 + (rand(0, 20) / 100), 3), // قيمة عشوائية بين 0.4 و 0.6
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

                $allGenerators->push($generator);

                // إنشاء خزانات وقود للمولد (إذا كان لديه خزانات)
                if ($generator->fuel_tanks_count > 0) {
                    for ($t = 0; $t < $generator->fuel_tanks_count; $t++) {
                        // توليد كود الخزان تلقائياً
                        $tankCode = FuelTank::getNextTankCode($generator->id);
                        
                        FuelTank::create([
                            'generator_id' => $generator->id,
                            'tank_code' => $tankCode,
                            'capacity' => rand(100, 500),
                            'location' => ['داخلي', 'خارجي', 'أرضي', 'علوي'][rand(0, 3)],
                            'filtration_system_available' => rand(0, 1) === 1,
                            'condition' => ['جيد', 'ممتاز', 'مقبول'][rand(0, 2)],
                            'material' => ['حديد', 'بلاستيك', 'فولاذ'][rand(0, 2)],
                            'usage' => ['رئيسي', 'احتياطي', 'إضافي'][rand(0, 2)],
                            'measurement_method' => ['ميكانيكي', 'إلكتروني', 'يدوي'][rand(0, 2)],
                            'order' => $t + 1,
                        ]);
                    }
                }
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
                $employee = User::firstOrCreate(
                    ['email' => 'user' . ($i + 1) . '_' . ($k + 1) . '@example.com'],
                    [
                        'name' => $employeeNames[$k],
                        'username' => 'user_' . ($i + 1) . '_' . ($k + 1),
                        'password' => Hash::make('password'),
                        'role' => $roles[$k],
                        'status' => 'active',
                    ]
                );

                // ربط الموظف/الفني بالمشغل
                $operator->users()->attach($employee->id);
            }
        }

        $this->command->info('تم إنشاء 10 مشغلين إضافيين مع مولداتهم وموظفيهم');

        // دالة مساعدة لإنشاء سجل تشغيل مع sequence
        $createOperationLog = function($generator, $operationDate, $startTime, $endTime, $loadPercentage, $fuelMeterStart, $fuelMeterEnd, $fuelConsumed, $energyMeterStart, $energyMeterEnd, $energyProduced, $operationalNotes, $malfunctions) {
            // حساب التسلسل لكل مولد
            $lastSequence = OperationLog::where('generator_id', $generator->id)->max('sequence') ?? 0;
            $sequence = $lastSequence + 1;

            return OperationLog::create([
                'generator_id' => $generator->id,
                'operator_id' => $generator->operator_id,
                'sequence' => $sequence,
                'operation_date' => $operationDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'load_percentage' => $loadPercentage,
                'fuel_meter_start' => $fuelMeterStart,
                'fuel_meter_end' => $fuelMeterEnd,
                'fuel_consumed' => $fuelConsumed,
                'energy_meter_start' => $energyMeterStart,
                'energy_meter_end' => $energyMeterEnd,
                'energy_produced' => $energyProduced,
                'operational_notes' => $operationalNotes,
                'malfunctions' => $malfunctions,
            ]);
        };

        // إنشاء أكثر من 100 سجل تشغيل لمشغل المملوك
        if ($mmlukOperator && $mmlukGenerators->isNotEmpty()) {
            $this->command->info('جاري إنشاء سجلات تشغيل لمشغل المملوك...');
            $mmlukOperationLogsCount = 110; // أكثر من 100
            
            // تجميع السجلات لكل مولد حسب التاريخ لضمان التسلسل الصحيح
            $mmlukLogsData = [];
            $logsPerGenerator = (int) ceil($mmlukOperationLogsCount / $mmlukGenerators->count());
            foreach ($mmlukGenerators as $mmlukGenerator) {
                for ($i = 0; $i < $logsPerGenerator; $i++) {
                    $operationDate = now()->subDays(rand(0, 365));
                    $startTime = $operationDate->copy()->setTime(rand(6, 10), rand(0, 59));
                    $endTime = $startTime->copy()->addHours(rand(2, 12))->addMinutes(rand(0, 59));
                    
                    $loadPercentage = round(rand(30, 10000) / 100, 2); // بين 30.00 و 100.00
                    $fuelMeterStart = round(rand(0, 1000) + (rand(0, 99) / 100), 2);
                    $fuelMeterEnd = $fuelMeterStart + round(rand(10, 200) + (rand(0, 99) / 100), 2);
                    $fuelConsumed = $fuelMeterEnd - $fuelMeterStart;
                    
                    $energyMeterStart = round(rand(0, 10000) + (rand(0, 99) / 100), 2);
                    $energyMeterEnd = $energyMeterStart + round(rand(50, 500) + (rand(0, 99) / 100), 2);
                    $energyProduced = $energyMeterEnd - $energyMeterStart;
                    
                    $mmlukLogsData[] = [
                        'generator' => $mmlukGenerator,
                        'operation_date' => $operationDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'load_percentage' => $loadPercentage,
                        'fuel_meter_start' => $fuelMeterStart,
                        'fuel_meter_end' => $fuelMeterEnd,
                        'fuel_consumed' => $fuelConsumed,
                        'energy_meter_start' => $energyMeterStart,
                        'energy_meter_end' => $energyMeterEnd,
                        'energy_produced' => $energyProduced,
                        'operational_notes' => rand(0, 1) === 1 ? 'تشغيل عادي بدون مشاكل' : 'تم التشغيل بنجاح',
                        'malfunctions' => rand(0, 1) === 1 ? null : 'لا توجد أعطال',
                    ];
                }
            }
            
            // ترتيب حسب المولد ثم التاريخ
            usort($mmlukLogsData, function($a, $b) {
                if ($a['generator']->id != $b['generator']->id) {
                    return $a['generator']->id <=> $b['generator']->id;
                }
                return $a['operation_date'] <=> $b['operation_date'];
            });
            
            // إنشاء السجلات بالترتيب الصحيح
            foreach ($mmlukLogsData as $logData) {
                $createOperationLog(
                    $logData['generator'],
                    $logData['operation_date'],
                    $logData['start_time'],
                    $logData['end_time'],
                    $logData['load_percentage'],
                    $logData['fuel_meter_start'],
                    $logData['fuel_meter_end'],
                    $logData['fuel_consumed'],
                    $logData['energy_meter_start'],
                    $logData['energy_meter_end'],
                    $logData['energy_produced'],
                    $logData['operational_notes'],
                    $logData['malfunctions']
                );
            }
            
            $this->command->info('✓ تم إنشاء ' . count($mmlukLogsData) . ' سجل تشغيل لمشغل المملوك');
        }

        // إنشاء 100 سجل تشغيل لباقي المشغلين
        $this->command->info('جاري إنشاء 100 سجل تشغيل للمشغلين الآخرين...');
        $mmlukGeneratorIds = $mmlukGenerators->pluck('id')->toArray();
        $otherGenerators = $allGenerators->filter(function($gen) use ($mmlukGeneratorIds) {
            return !in_array($gen->id, $mmlukGeneratorIds);
        });
        for ($i = 0; $i < 100; $i++) {
            $generator = $otherGenerators->random();
            $operator = $generator->operator;

            $operationDate = now()->subDays(rand(0, 365));
            $startTime = $operationDate->copy()->setTime(rand(6, 10), rand(0, 59));
            $endTime = $startTime->copy()->addHours(rand(2, 12))->addMinutes(rand(0, 59));

            $loadPercentage = round(rand(30, 10000) / 100, 2); // بين 30.00 و 100.00
            $fuelMeterStart = round(rand(0, 1000) + (rand(0, 99) / 100), 2);
            $fuelMeterEnd = $fuelMeterStart + round(rand(10, 200) + (rand(0, 99) / 100), 2);
            $fuelConsumed = $fuelMeterEnd - $fuelMeterStart;

            $energyMeterStart = round(rand(0, 10000) + (rand(0, 99) / 100), 2);
            $energyMeterEnd = $energyMeterStart + round(rand(50, 500) + (rand(0, 99) / 100), 2);
            $energyProduced = $energyMeterEnd - $energyMeterStart;

            $createOperationLog(
                $generator,
                $operationDate,
                $startTime,
                $endTime,
                $loadPercentage,
                $fuelMeterStart,
                $fuelMeterEnd,
                $fuelConsumed,
                $energyMeterStart,
                $energyMeterEnd,
                $energyProduced,
                rand(0, 1) === 1 ? 'تشغيل عادي بدون مشاكل' : 'تم التشغيل بنجاح',
                rand(0, 1) === 1 ? null : 'لا توجد أعطال'
            );
        }
        $this->command->info('✓ تم إنشاء 100 سجل تشغيل للمشغلين الآخرين');

        // إنشاء أكثر من 100 سجل صيانة لمشغل المملوك
        if ($mmlukOperator && $mmlukGenerators->isNotEmpty()) {
            $this->command->info('جاري إنشاء سجلات صيانة لمشغل المملوك...');
            $mmlukMaintenanceCount = 110;
            
            for ($i = 0; $i < $mmlukMaintenanceCount; $i++) {
                $generator = $mmlukGenerators->random();
                $maintenanceDate = now()->subDays(rand(0, 365));
                $startHour = rand(8, 14);
                $startMinute = rand(0, 59);
                $endHour = $startHour + rand(2, 8);
                $endMinute = rand(0, 59);
                if ($endHour >= 24) {
                    $endHour = 23;
                    $endMinute = 59;
                }
                
                $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
                $endTime = sprintf('%02d:%02d:00', $endHour, $endMinute);
                
                $laborHours = round(rand(2, 8) + (rand(0, 99) / 100), 2);
                $laborRatePerHour = round(rand(50, 200) + (rand(0, 99) / 100), 2);
                $partsCost = round(rand(100, 2000) + (rand(0, 99) / 100), 2);
                $laborCost = round($laborHours * $laborRatePerHour, 2);
                $maintenanceCost = round($partsCost + $laborCost, 2);

                MaintenanceRecord::create([
                    'generator_id' => $generator->id,
                    'maintenance_type' => $maintenanceTypes[rand(0, count($maintenanceTypes) - 1)],
                    'maintenance_date' => $maintenanceDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'technician_name' => $technicianNames[rand(0, count($technicianNames) - 1)],
                    'work_performed' => 'تم إجراء ' . $maintenanceTypes[rand(0, count($maintenanceTypes) - 1)] . ' على المولد',
                    'downtime_hours' => round(rand(1, 24) + (rand(0, 99) / 100), 2),
                    'parts_cost' => $partsCost,
                    'labor_hours' => $laborHours,
                    'labor_rate_per_hour' => $laborRatePerHour,
                    'maintenance_cost' => $maintenanceCost,
                ]);
            }
            $this->command->info('✓ تم إنشاء ' . $mmlukMaintenanceCount . ' سجل صيانة لمشغل المملوك');
        }

        // إنشاء 100 سجل صيانة لباقي المشغلين
        $this->command->info('جاري إنشاء 100 سجل صيانة للمشغلين الآخرين...');
        $mmlukGeneratorIds = $mmlukGenerators->pluck('id')->toArray();
        $otherGenerators = $allGenerators->filter(function($gen) use ($mmlukGeneratorIds) {
            return !in_array($gen->id, $mmlukGeneratorIds);
        });
        for ($i = 0; $i < 100; $i++) {
            $generator = $otherGenerators->random();
            $maintenanceDate = now()->subDays(rand(0, 365));
            $startHour = rand(8, 14);
            $startMinute = rand(0, 59);
            $endHour = $startHour + rand(2, 8);
            $endMinute = rand(0, 59);
            if ($endHour >= 24) {
                $endHour = 23;
                $endMinute = 59;
            }
            
            $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
            $endTime = sprintf('%02d:%02d:00', $endHour, $endMinute);
            
            $laborHours = round(rand(2, 8) + (rand(0, 99) / 100), 2);
            $laborRatePerHour = round(rand(50, 200) + (rand(0, 99) / 100), 2);
            $partsCost = round(rand(100, 2000) + (rand(0, 99) / 100), 2);
            $laborCost = round($laborHours * $laborRatePerHour, 2);
            $maintenanceCost = round($partsCost + $laborCost, 2);

            MaintenanceRecord::create([
                'generator_id' => $generator->id,
                'maintenance_type' => $maintenanceTypes[rand(0, count($maintenanceTypes) - 1)],
                'maintenance_date' => $maintenanceDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'technician_name' => $technicianNames[rand(0, count($technicianNames) - 1)],
                'work_performed' => 'تم إجراء ' . $maintenanceTypes[rand(0, count($maintenanceTypes) - 1)] . ' على المولد',
                'downtime_hours' => round(rand(1, 24) + (rand(0, 99) / 100), 2),
                'parts_cost' => $partsCost,
                'labor_hours' => $laborHours,
                'labor_rate_per_hour' => $laborRatePerHour,
                'maintenance_cost' => $maintenanceCost,
            ]);
        }
        $this->command->info('✓ تم إنشاء 100 سجل صيانة للمشغلين الآخرين');

        // إنشاء 100 سجل كفاءة وقود
        $this->command->info('جاري إنشاء 100 سجل كفاءة وقود...');
        for ($i = 0; $i < 100; $i++) {
            $generator = $allGenerators->random();
            $consumptionDate = now()->subDays(rand(0, 365));

            $operatingHours = round(rand(1, 24) + (rand(0, 99) / 100), 2);
            $fuelPricePerLiter = round(rand(5, 10) + (rand(0, 99) / 100), 2);
            $fuelConsumed = round(rand(50, 500) + (rand(0, 99) / 100), 2);
            $fuelEfficiencyPercentage = round(rand(70, 95) + (rand(0, 99) / 100), 2);
            $energyDistributionEfficiency = round(rand(75, 98) + (rand(0, 99) / 100), 2);
            $totalOperatingCost = round($fuelConsumed * $fuelPricePerLiter, 2);

            FuelEfficiency::create([
                'generator_id' => $generator->id,
                'consumption_date' => $consumptionDate,
                'operating_hours' => $operatingHours,
                'fuel_price_per_liter' => $fuelPricePerLiter,
                'fuel_consumed' => $fuelConsumed,
                'fuel_efficiency_percentage' => $fuelEfficiencyPercentage,
                'fuel_efficiency_comparison' => round($fuelEfficiencyPercentage + rand(-5, 5) + (rand(0, 99) / 100), 2),
                'energy_distribution_efficiency' => $energyDistributionEfficiency,
                'energy_efficiency_comparison' => round($energyDistributionEfficiency + rand(-3, 3) + (rand(0, 99) / 100), 2),
                'total_operating_cost' => $totalOperatingCost,
            ]);
        }
        $this->command->info('✓ تم إنشاء 100 سجل كفاءة وقود');

        // إنشاء أكثر من 100 سجل امتثال وسلامة لمشغل المملوك
        if ($mmlukOperator) {
            $this->command->info('جاري إنشاء سجلات امتثال وسلامة لمشغل المملوك...');
            $mmlukComplianceCount = 110;
            
            $safetyStatuses = ['compliant', 'non_compliant', 'pending'];
            $inspectionAuthorities = ['وزارة البيئة', 'البلدية', 'الدفاع المدني', 'جهة مختصة'];
            $inspectionResults = ['ممتاز', 'جيد', 'مقبول', 'يحتاج تحسين'];
            
            for ($i = 0; $i < $mmlukComplianceCount; $i++) {
                $inspectionDate = now()->subDays(rand(0, 365));

                ComplianceSafety::create([
                    'operator_id' => $mmlukOperator->id,
                    'safety_certificate_status' => $safetyStatuses[rand(0, count($safetyStatuses) - 1)],
                    'last_inspection_date' => $inspectionDate,
                    'inspection_authority' => $inspectionAuthorities[rand(0, count($inspectionAuthorities) - 1)],
                    'inspection_result' => $inspectionResults[rand(0, count($inspectionResults) - 1)],
                    'violations' => rand(0, 1) === 1 ? 'لا توجد مخالفات' : null,
                ]);
            }
            $this->command->info('✓ تم إنشاء ' . $mmlukComplianceCount . ' سجل امتثال وسلامة لمشغل المملوك');
        }

        // إنشاء 100 سجل امتثال وسلامة لباقي المشغلين
        $this->command->info('جاري إنشاء 100 سجل امتثال وسلامة للمشغلين الآخرين...');
        $otherOperators = $allOperators->filter(function($op) use ($mmlukOperator) {
            return $mmlukOperator && $op->id != $mmlukOperator->id;
        });
        $safetyStatuses = ['compliant', 'non_compliant', 'pending'];
        $inspectionAuthorities = ['وزارة البيئة', 'البلدية', 'الدفاع المدني', 'جهة مختصة'];
        $inspectionResults = ['ممتاز', 'جيد', 'مقبول', 'يحتاج تحسين'];
        
        for ($i = 0; $i < 100; $i++) {
            $operator = $otherOperators->random();
            $inspectionDate = now()->subDays(rand(0, 365));

            ComplianceSafety::create([
                'operator_id' => $operator->id,
                'safety_certificate_status' => $safetyStatuses[rand(0, count($safetyStatuses) - 1)],
                'last_inspection_date' => $inspectionDate,
                'inspection_authority' => $inspectionAuthorities[rand(0, count($inspectionAuthorities) - 1)],
                'inspection_result' => $inspectionResults[rand(0, count($inspectionResults) - 1)],
                'violations' => rand(0, 1) === 1 ? 'لا توجد مخالفات' : null,
            ]);
        }
        $this->command->info('✓ تم إنشاء 100 سجل امتثال وسلامة للمشغلين الآخرين');

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('تم إنشاء جميع البيانات بنجاح!');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('الملخص:');
        $this->command->info('- ' . $allOperators->count() . ' مشغل (بما في ذلك مشغل المملوك)');
        $this->command->info('- ' . $allGenerators->count() . ' مولد');
        
        $totalOperationLogs = \App\Models\OperationLog::count();
        $totalMaintenanceRecords = \App\Models\MaintenanceRecord::count();
        $totalComplianceSafeties = \App\Models\ComplianceSafety::count();
        
        $this->command->info('- ' . $totalOperationLogs . ' سجل تشغيل');
        $this->command->info('- ' . $totalMaintenanceRecords . ' سجل صيانة');
        $this->command->info('- 100 سجل كفاءة وقود');
        $this->command->info('- ' . $totalComplianceSafeties . ' سجل امتثال وسلامة');
        $this->command->info('═══════════════════════════════════════');
    }
}
