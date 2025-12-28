<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('maintenance_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->decimal('parts_cost', 10, 2)->nullable()->after('downtime_hours');
            $table->decimal('labor_hours', 8, 2)->nullable()->after('parts_cost');
            $table->decimal('labor_rate_per_hour', 10, 2)->nullable()->after('labor_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'parts_cost', 'labor_hours', 'labor_rate_per_hour']);
        });
    }
};
