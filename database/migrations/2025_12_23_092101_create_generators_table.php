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
        Schema::create('generators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generator_number')->unique();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();

            // المواصفات الفنية
            $table->decimal('capacity_kva', 10, 2)->nullable();
            $table->decimal('power_factor', 4, 2)->nullable();
            $table->integer('voltage')->nullable();
            $table->integer('frequency')->nullable();
            $table->string('engine_type')->nullable();

            // التشغيل والوقود
            $table->year('manufacturing_year')->nullable();
            $table->string('injection_system')->nullable();
            $table->decimal('fuel_consumption_rate', 8, 2)->nullable();
            $table->decimal('ideal_fuel_efficiency', 8, 3)->nullable()->comment('كفاءة الوقود المثالية (kWh/لتر)');
            $table->integer('internal_tank_capacity')->nullable();
            $table->string('measurement_indicator')->nullable();

            // الحالة الفنية والتوثيق
            $table->string('technical_condition')->nullable();
            $table->date('last_major_maintenance_date')->nullable();
            $table->date('last_operation_date')->nullable()->comment('تاريخ آخر تشغيل');
            $table->string('engine_data_plate_image')->nullable();
            $table->string('generator_data_plate_image')->nullable();

            // نظام التحكم
            $table->boolean('control_panel_available')->default(false);
            $table->string('control_panel_type')->nullable();
            $table->string('control_panel_status')->nullable();
            $table->string('control_panel_image')->nullable();
            $table->integer('operating_hours')->nullable();
            $table->integer('total_operating_hours')->default(0)->comment('إجمالي ساعات التشغيل');

            // خزانات الوقود
            $table->boolean('external_fuel_tank')->default(false);
            $table->integer('fuel_tanks_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['operator_id', 'status'], 'idx_generators_operator_status');
            $table->index(['name', 'generator_number'], 'idx_generators_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generators');
    }
};
