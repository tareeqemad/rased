<?php

namespace Database\Seeders;

use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use Illuminate\Database\Seeder;

class AddGeneratorConstantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        $this->command->info('تم إضافة ثوابت المولدات الجديدة بنجاح!');
    }
}

