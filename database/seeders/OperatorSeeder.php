<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mmlukOwner = User::where('username', 'mmluk')->first();

        if ($mmlukOwner) {
            // مشغل المملوك
            $mmlukOperator = Operator::create([
                'name' => 'مشغل المملوك',
                'email' => 'info@mmluk.ps',
                'phone' => '0599123456',
                'phone_alt' => '0599123457',
                'address' => 'غزة - شارع المملوك',
                'owner_id' => $mmlukOwner->id,
                'unit_number' => 'GAZ-001',
                'unit_code' => 'GAZ-001',
                'unit_name' => 'وحدة المملوك',
                'governorate' => 10, // Gaza
                'city' => 'غزة',
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

            $this->command->info('تم إنشاء مشغل المملوك بنجاح!');
            $this->command->info('المشغل: مشغل المملوك');
            $this->command->info('المالك: mmluk');
            $this->command->info('عدد المولدات: 4');
            $this->command->info('عدد الموظفين: 5');
        }
    }
}
