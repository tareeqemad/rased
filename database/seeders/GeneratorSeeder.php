<?php

namespace Database\Seeders;

use App\Models\Generator;
use App\Models\Operator;
use Illuminate\Database\Seeder;

class GeneratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mmlukOperator = Operator::where('name', 'مشغل المملوك')->first();

        if ($mmlukOperator) {
            // 4 مولدات لمشغل المملوك
            Generator::create([
                'name' => 'مولد المملوك 1',
                'generator_number' => 'GEN-MMLUK-001',
                'operator_id' => $mmlukOperator->id,
                'description' => 'مولد ديزل بقوة 100 كيلو فولت أمبير',
                'capacity_kva' => 100,
                'power_factor' => 0.8,
                'voltage' => 400,
                'frequency' => 50,
                'engine_type' => 'Perkins',
                'manufacturing_year' => 2020,
                'injection_system' => 'Normal',
                'fuel_consumption_rate' => 25.5,
                'internal_tank_capacity' => 200,
                'measurement_indicator' => 'Available and working',
                'technical_condition' => 'Excellent',
                'control_panel_available' => true,
                'control_panel_type' => 'Deep Sea',
                'control_panel_status' => 'Working',
                'operating_hours' => 5000,
                'external_fuel_tank' => true,
                'fuel_tanks_count' => 2,
                'status' => 'active',
            ]);

            Generator::create([
                'name' => 'مولد المملوك 2',
                'generator_number' => 'GEN-MMLUK-002',
                'operator_id' => $mmlukOperator->id,
                'description' => 'مولد ديزل بقوة 150 كيلو فولت أمبير',
                'capacity_kva' => 150,
                'power_factor' => 0.85,
                'voltage' => 400,
                'frequency' => 50,
                'engine_type' => 'Volvo',
                'manufacturing_year' => 2021,
                'injection_system' => 'Electric',
                'fuel_consumption_rate' => 35.0,
                'internal_tank_capacity' => 300,
                'measurement_indicator' => 'Available and working',
                'technical_condition' => 'Very Good',
                'control_panel_available' => true,
                'control_panel_type' => 'ComAp',
                'control_panel_status' => 'Working',
                'operating_hours' => 3500,
                'external_fuel_tank' => true,
                'fuel_tanks_count' => 2,
                'status' => 'active',
            ]);

            Generator::create([
                'name' => 'مولد المملوك 3',
                'generator_number' => 'GEN-MMLUK-003',
                'operator_id' => $mmlukOperator->id,
                'description' => 'مولد ديزل بقوة 200 كيلو فولت أمبير',
                'capacity_kva' => 200,
                'power_factor' => 0.9,
                'voltage' => 400,
                'frequency' => 50,
                'engine_type' => 'Caterpillar',
                'manufacturing_year' => 2019,
                'injection_system' => 'Hybrid',
                'fuel_consumption_rate' => 45.5,
                'internal_tank_capacity' => 400,
                'measurement_indicator' => 'Available and working',
                'technical_condition' => 'Good',
                'control_panel_available' => true,
                'control_panel_type' => 'Datakom',
                'control_panel_status' => 'Working',
                'operating_hours' => 8000,
                'external_fuel_tank' => true,
                'fuel_tanks_count' => 2,
                'status' => 'active',
            ]);

            Generator::create([
                'name' => 'مولد المملوك 4',
                'generator_number' => 'GEN-MMLUK-004',
                'operator_id' => $mmlukOperator->id,
                'description' => 'مولد ديزل بقوة 50 كيلو فولت أمبير',
                'capacity_kva' => 50,
                'power_factor' => 0.75,
                'voltage' => 400,
                'frequency' => 50,
                'engine_type' => 'Perkins',
                'manufacturing_year' => 2022,
                'injection_system' => 'Normal',
                'fuel_consumption_rate' => 15.0,
                'internal_tank_capacity' => 150,
                'measurement_indicator' => 'Available and working',
                'technical_condition' => 'Excellent',
                'control_panel_available' => true,
                'control_panel_type' => 'Deep Sea',
                'control_panel_status' => 'Working',
                'operating_hours' => 2000,
                'external_fuel_tank' => false,
                'fuel_tanks_count' => 0,
                'status' => 'active',
            ]);

            $this->command->info('تم إنشاء 4 مولدات لمشغل المملوك بنجاح!');
        }
    }
}
