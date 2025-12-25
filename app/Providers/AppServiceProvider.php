<?php

namespace App\Providers;

use App\Models\ComplianceSafety;
use App\Models\FuelEfficiency;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\OperationLog;
use App\Models\Operator;
use App\Models\User;
use App\Policies\ComplianceSafetyPolicy;
use App\Policies\FuelEfficiencyPolicy;
use App\Policies\GeneratorPolicy;
use App\Policies\MaintenanceRecordPolicy;
use App\Policies\OperationLogPolicy;
use App\Policies\OperatorPolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Operator::class => OperatorPolicy::class,
        Generator::class => GeneratorPolicy::class,
        User::class => UserPolicy::class,
        OperationLog::class => OperationLogPolicy::class,
        FuelEfficiency::class => FuelEfficiencyPolicy::class,
        MaintenanceRecord::class => MaintenanceRecordPolicy::class,
        ComplianceSafety::class => ComplianceSafetyPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تعيين التوقيت المحلي واللغة
        Carbon::setLocale('ar');
        date_default_timezone_set('Asia/Gaza');

        // استخدام Bootstrap pagination
        Paginator::useBootstrap();
    }
}
