<?php

namespace Database\Seeders;

use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use Illuminate\Database\Seeder;

class ConstantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. المحافظات
        $governorates = ConstantMaster::create([
            'constant_number' => 1,
            'constant_name' => 'المحافظة',
            'description' => 'قائمة المحافظات في فلسطين',
            'is_active' => true,
            'order' => 1,
        ]);

        $governorateDetails = [
            ['label' => 'غزة', 'code' => 'GAZ', 'value' => '10', 'order' => 1],
            ['label' => 'الوسطى', 'code' => 'MID', 'value' => '20', 'order' => 2],
            ['label' => 'خانيونس', 'code' => 'KHU', 'value' => '30', 'order' => 3],
            ['label' => 'رفح', 'code' => 'RAF', 'value' => '40', 'order' => 4],
        ];

        foreach ($governorateDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $governorates->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 2. جهة التشغيل
        $operationEntity = ConstantMaster::create([
            'constant_number' => 2,
            'constant_name' => 'جهة التشغيل',
            'description' => 'جهة تشغيل المولد',
            'is_active' => true,
            'order' => 2,
        ]);

        $operationEntityDetails = [
            ['label' => 'نفس المالك', 'code' => 'SAME_OWNER', 'value' => 'same_owner', 'order' => 1],
            ['label' => 'طرف آخر', 'code' => 'OTHER_PARTY', 'value' => 'other_party', 'order' => 2],
        ];

        foreach ($operationEntityDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $operationEntity->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 3. حالة المولد
        $generatorStatus = ConstantMaster::create([
            'constant_number' => 3,
            'constant_name' => 'حالة المولد',
            'description' => 'حالة المولد (فعال، غير فعال)',
            'is_active' => true,
            'order' => 3,
        ]);

        $generatorStatusDetails = [
            ['label' => 'فعال', 'code' => 'ACTIVE', 'value' => 'active', 'order' => 1],
            ['label' => 'غير فعال', 'code' => 'INACTIVE', 'value' => 'inactive', 'order' => 2],
        ];

        foreach ($generatorStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $generatorStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 4. نوع المحرك
        $engineType = ConstantMaster::create([
            'constant_number' => 4,
            'constant_name' => 'نوع المحرك',
            'description' => 'نوع محرك المولد',
            'is_active' => true,
            'order' => 4,
        ]);

        $engineTypeDetails = [
            ['label' => 'ديزل', 'code' => 'DIESEL', 'value' => 'diesel', 'order' => 1],
            ['label' => 'بنزين', 'code' => 'GASOLINE', 'value' => 'gasoline', 'order' => 2],
            ['label' => 'غاز', 'code' => 'GAS', 'value' => 'gas', 'order' => 3],
        ];

        foreach ($engineTypeDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $engineType->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 5. نظام الحقن
        $injectionSystem = ConstantMaster::create([
            'constant_number' => 5,
            'constant_name' => 'نظام الحقن',
            'description' => 'نوع نظام الحقن',
            'is_active' => true,
            'order' => 5,
        ]);

        $injectionSystemDetails = [
            ['label' => 'ميكانيكي', 'code' => 'MECHANICAL', 'value' => 'mechanical', 'order' => 1],
            ['label' => 'إلكتروني', 'code' => 'ELECTRONIC', 'value' => 'electronic', 'order' => 2],
        ];

        foreach ($injectionSystemDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $injectionSystem->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 6. مؤشر القياس
        $measurementIndicator = ConstantMaster::create([
            'constant_number' => 6,
            'constant_name' => 'مؤشر القياس',
            'description' => 'نوع مؤشر القياس',
            'is_active' => true,
            'order' => 6,
        ]);

        $measurementIndicatorDetails = [
            ['label' => 'ميكانيكي', 'code' => 'MECHANICAL', 'value' => 'mechanical', 'order' => 1],
            ['label' => 'رقمي', 'code' => 'DIGITAL', 'value' => 'digital', 'order' => 2],
        ];

        foreach ($measurementIndicatorDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $measurementIndicator->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 7. الحالة الفنية
        $technicalCondition = ConstantMaster::create([
            'constant_number' => 7,
            'constant_name' => 'الحالة الفنية',
            'description' => 'الحالة الفنية للمولد',
            'is_active' => true,
            'order' => 7,
        ]);

        $technicalConditionDetails = [
            ['label' => 'ممتاز', 'code' => 'EXCELLENT', 'value' => 'excellent', 'order' => 1],
            ['label' => 'جيد', 'code' => 'GOOD', 'value' => 'good', 'order' => 2],
            ['label' => 'مقبول', 'code' => 'FAIR', 'value' => 'fair', 'order' => 3],
            ['label' => 'يحتاج صيانة', 'code' => 'NEEDS_MAINTENANCE', 'value' => 'needs_maintenance', 'order' => 4],
        ];

        foreach ($technicalConditionDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $technicalCondition->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 8. نوع لوحة التحكم
        $controlPanelType = ConstantMaster::create([
            'constant_number' => 8,
            'constant_name' => 'نوع لوحة التحكم',
            'description' => 'نوع لوحة التحكم',
            'is_active' => true,
            'order' => 8,
        ]);

        $controlPanelTypeDetails = [
            ['label' => 'يدوي', 'code' => 'MANUAL', 'value' => 'manual', 'order' => 1],
            ['label' => 'أوتوماتيكي', 'code' => 'AUTOMATIC', 'value' => 'automatic', 'order' => 2],
            ['label' => 'ذكي', 'code' => 'SMART', 'value' => 'smart', 'order' => 3],
        ];

        foreach ($controlPanelTypeDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $controlPanelType->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 9. حالة لوحة التحكم
        $controlPanelStatus = ConstantMaster::create([
            'constant_number' => 9,
            'constant_name' => 'حالة لوحة التحكم',
            'description' => 'حالة لوحة التحكم',
            'is_active' => true,
            'order' => 9,
        ]);

        $controlPanelStatusDetails = [
            ['label' => 'فعال', 'code' => 'ACTIVE', 'value' => 'active', 'order' => 1],
            ['label' => 'غير فعال', 'code' => 'INACTIVE', 'value' => 'inactive', 'order' => 2],
            ['label' => 'يحتاج إصلاح', 'code' => 'NEEDS_REPAIR', 'value' => 'needs_repair', 'order' => 3],
        ];

        foreach ($controlPanelStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $controlPanelStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 10. مادة التصنيع (خزانات الوقود)
        $material = ConstantMaster::create([
            'constant_number' => 10,
            'constant_name' => 'مادة التصنيع',
            'description' => 'مادة تصنيع خزانات الوقود',
            'is_active' => true,
            'order' => 10,
        ]);

        $materialDetails = [
            ['label' => 'حديد', 'code' => 'STEEL', 'value' => 'steel', 'order' => 1],
            ['label' => 'بلاستيك', 'code' => 'PLASTIC', 'value' => 'plastic', 'order' => 2],
            ['label' => 'مقوى', 'code' => 'REINFORCED', 'value' => 'reinforced', 'order' => 3],
            ['label' => 'فايبر', 'code' => 'FIBER', 'value' => 'fiber', 'order' => 4],
        ];

        foreach ($materialDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $material->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 11. الاستخدام (خزانات الوقود)
        $usage = ConstantMaster::create([
            'constant_number' => 11,
            'constant_name' => 'الاستخدام',
            'description' => 'نوع استخدام خزان الوقود',
            'is_active' => true,
            'order' => 11,
        ]);

        $usageDetails = [
            ['label' => 'مركزي', 'code' => 'CENTRAL', 'value' => 'central', 'order' => 1],
            ['label' => 'احتياطي', 'code' => 'RESERVE', 'value' => 'reserve', 'order' => 2],
        ];

        foreach ($usageDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $usage->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 12. نوع الصيانة
        $maintenanceType = ConstantMaster::create([
            'constant_number' => 12,
            'constant_name' => 'نوع الصيانة',
            'description' => 'نوع عملية الصيانة',
            'is_active' => true,
            'order' => 12,
        ]);

        $maintenanceTypeDetails = [
            ['label' => 'وقائية', 'code' => 'PREVENTIVE', 'value' => 'preventive', 'order' => 1],
            ['label' => 'تصحيحية', 'code' => 'CORRECTIVE', 'value' => 'corrective', 'order' => 2],
            ['label' => 'طارئة', 'code' => 'EMERGENCY', 'value' => 'emergency', 'order' => 3],
        ];

        foreach ($maintenanceTypeDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $maintenanceType->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 13. حالة شهادة السلامة
        $safetyCertificateStatus = ConstantMaster::create([
            'constant_number' => 13,
            'constant_name' => 'حالة شهادة السلامة',
            'description' => 'حالة شهادة السلامة',
            'is_active' => true,
            'order' => 13,
        ]);

        $safetyCertificateStatusDetails = [
            ['label' => 'صالحة', 'code' => 'VALID', 'value' => 'valid', 'order' => 1],
            ['label' => 'منتهية', 'code' => 'EXPIRED', 'value' => 'expired', 'order' => 2],
            ['label' => 'غير موجودة', 'code' => 'MISSING', 'value' => 'missing', 'order' => 3],
        ];

        foreach ($safetyCertificateStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $safetyCertificateStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 14. حالة الامتثال البيئي
        $environmentalComplianceStatus = ConstantMaster::create([
            'constant_number' => 14,
            'constant_name' => 'حالة الامتثال البيئي',
            'description' => 'حالة الامتثال البيئي',
            'is_active' => true,
            'order' => 14,
        ]);

        $environmentalComplianceStatusDetails = [
            ['label' => 'متوافق', 'code' => 'COMPLIANT', 'value' => 'compliant', 'order' => 1],
            ['label' => 'غير متوافق', 'code' => 'NON_COMPLIANT', 'value' => 'non_compliant', 'order' => 2],
            ['label' => 'قيد المراجعة', 'code' => 'UNDER_REVIEW', 'value' => 'under_review', 'order' => 3],
        ];

        foreach ($environmentalComplianceStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $environmentalComplianceStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 15. حالة الوحدة
        $unitStatus = ConstantMaster::create([
            'constant_number' => 15,
            'constant_name' => 'حالة الوحدة',
            'description' => 'حالة وحدة المشغل',
            'is_active' => true,
            'order' => 15,
        ]);

        $unitStatusDetails = [
            ['label' => 'نشط', 'code' => 'ACTIVE', 'value' => 'active', 'order' => 1],
            ['label' => 'متوقف', 'code' => 'INACTIVE', 'value' => 'inactive', 'order' => 2],
            ['label' => 'قيد الصيانة', 'code' => 'MAINTENANCE', 'value' => 'maintenance', 'order' => 3],
        ];

        foreach ($unitStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $unitStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 16. مقارنة كفاءة الوقود
        $fuelEfficiencyComparison = ConstantMaster::create([
            'constant_number' => 16,
            'constant_name' => 'مقارنة كفاءة الوقود',
            'description' => 'مقارنة كفاءة الوقود',
            'is_active' => true,
            'order' => 16,
        ]);

        $fuelEfficiencyComparisonDetails = [
            ['label' => 'أفضل من المتوسط', 'code' => 'BETTER', 'value' => 'better', 'order' => 1],
            ['label' => 'متوسط', 'code' => 'AVERAGE', 'value' => 'average', 'order' => 2],
            ['label' => 'أقل من المتوسط', 'code' => 'WORSE', 'value' => 'worse', 'order' => 3],
        ];

        foreach ($fuelEfficiencyComparisonDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $fuelEfficiencyComparison->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 17. مقارنة كفاءة الطاقة
        $energyEfficiencyComparison = ConstantMaster::create([
            'constant_number' => 17,
            'constant_name' => 'مقارنة كفاءة الطاقة',
            'description' => 'مقارنة كفاءة الطاقة',
            'is_active' => true,
            'order' => 17,
        ]);

        $energyEfficiencyComparisonDetails = [
            ['label' => 'أفضل من المتوسط', 'code' => 'BETTER', 'value' => 'better', 'order' => 1],
            ['label' => 'متوسط', 'code' => 'AVERAGE', 'value' => 'average', 'order' => 2],
            ['label' => 'أقل من المتوسط', 'code' => 'WORSE', 'value' => 'worse', 'order' => 3],
        ];

        foreach ($energyEfficiencyComparisonDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $energyEfficiencyComparison->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 18. موقع الخزان
        $tankLocation = ConstantMaster::firstOrCreate(
            ['constant_number' => 18],
            [
                'constant_name' => 'موقع الخزان',
                'description' => 'موقع خزان الوقود',
                'is_active' => true,
                'order' => 18,
            ]
        );

        $tankLocationDetails = [
            ['label' => 'أرضي', 'code' => 'GROUND', 'value' => 'ارضي', 'order' => 1],
            ['label' => 'علوي', 'code' => 'OVERHEAD', 'value' => 'علوي', 'order' => 2],
            ['label' => 'تحت الأرض', 'code' => 'UNDERGROUND', 'value' => 'تحت الارض', 'order' => 3],
        ];

        foreach ($tankLocationDetails as $detail) {
            ConstantDetail::firstOrCreate(
                [
                    'constant_master_id' => $tankLocation->id,
                    'value' => $detail['value'],
                ],
                [
                    'label' => $detail['label'],
                    'code' => $detail['code'],
                    'is_active' => true,
                    'order' => $detail['order'],
                ]
            );
        }

        // 19. طريقة القياس
        $measurementMethod = ConstantMaster::firstOrCreate(
            ['constant_number' => 19],
            [
                'constant_name' => 'طريقة القياس',
                'description' => 'طريقة قياس مستوى الوقود في الخزان',
                'is_active' => true,
                'order' => 19,
            ]
        );

        $measurementMethodDetails = [
            ['label' => 'سيخ', 'code' => 'DIPSTICK', 'value' => 'سيخ', 'order' => 1],
            ['label' => 'مدرج', 'code' => 'GAUGE', 'value' => 'مدرج', 'order' => 2],
            ['label' => 'ساعة ميكانيكية', 'code' => 'MECHANICAL_METER', 'value' => 'ساعه ميكانيكية', 'order' => 3],
            ['label' => 'حساس إلكتروني', 'code' => 'ELECTRONIC_SENSOR', 'value' => 'حساس الكتروني', 'order' => 4],
            ['label' => 'خرطوم شفاف', 'code' => 'TRANSPARENT_HOSE', 'value' => 'خرطوم شفاف', 'order' => 5],
        ];

        foreach ($measurementMethodDetails as $detail) {
            ConstantDetail::firstOrCreate(
                [
                    'constant_master_id' => $measurementMethod->id,
                    'value' => $detail['value'],
                ],
                [
                    'label' => $detail['label'],
                    'code' => $detail['code'],
                    'is_active' => true,
                    'order' => $detail['order'],
                ]
            );
        }

        $this->command->info('تم إنشاء الثوابت بنجاح!');
    }
}
