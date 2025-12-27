<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOperationLogsSequence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operation-logs:update-sequence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحديث التسلسل لسجلات التشغيل الموجودة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء تحديث التسلسل لسجلات التشغيل...');

        $generators = DB::table('generators')->pluck('id');
        
        $totalUpdated = 0;
        
        foreach ($generators as $generatorId) {
            $logs = DB::table('operation_logs')
                ->where('generator_id', $generatorId)
                ->whereNull('deleted_at')
                ->orderBy('operation_date')
                ->orderBy('start_time')
                ->orderBy('id')
                ->get(['id']);
            
            $sequence = 1;
            foreach ($logs as $log) {
                DB::table('operation_logs')
                    ->where('id', $log->id)
                    ->update(['sequence' => $sequence]);
                $sequence++;
            }
            
            $totalUpdated += $logs->count();
            $this->info("تم تحديث {$logs->count()} سجل للمولد ID: {$generatorId}");
        }

        $this->info("تم تحديث {$totalUpdated} سجل بنجاح!");
        
        return Command::SUCCESS;
    }
}
