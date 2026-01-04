<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConstantDetail;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateConstants extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف التكرارات بناءً على constant_master_id و code
        $duplicates = DB::table('constant_details')
            ->select('constant_master_id', 'code', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as count'))
            ->whereNotNull('code')
            ->groupBy('constant_master_id', 'code')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // حذف جميع السجلات المكررة ما عدا الأول
            DB::table('constant_details')
                ->where('constant_master_id', $duplicate->constant_master_id)
                ->where('code', $duplicate->code)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        // حذف التكرارات بناءً على constant_master_id و value (للحالات التي لا يوجد فيها code)
        $duplicatesByValue = DB::table('constant_details')
            ->select('constant_master_id', 'value', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as count'))
            ->whereNull('code')
            ->whereNotNull('value')
            ->groupBy('constant_master_id', 'value')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicatesByValue as $duplicate) {
            DB::table('constant_details')
                ->where('constant_master_id', $duplicate->constant_master_id)
                ->where('value', $duplicate->value)
                ->whereNull('code')
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        $this->command->info('تم حذف التكرارات بنجاح!');
    }
}


