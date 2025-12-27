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
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->nullable(); // تسلسل السجل لكل مولد

            // تاريخ ووقت التشغيل
            $table->date('operation_date');
            $table->time('start_time');
            $table->time('end_time');

            // نسبة التحميل
            $table->decimal('load_percentage', 5, 2)->nullable(); // نسبة التحميل وقت التشغيل

            // قراءات عداد الوقود
            $table->decimal('fuel_meter_start', 10, 2)->nullable(); // قراءة عداد الوقود عند البدء
            $table->decimal('fuel_meter_end', 10, 2)->nullable(); // قراءة عداد الوقود عند الانتهاء
            $table->decimal('fuel_consumed', 10, 2)->nullable(); // كمية الوقود المستهلك (لتر)

            // قراءات عداد الطاقة
            $table->decimal('energy_meter_start', 10, 2)->nullable(); // قراءة عداد الطاقة عند البدء
            $table->decimal('energy_meter_end', 10, 2)->nullable(); // قراءة عداد الطاقة عند الإيقاف
            $table->decimal('energy_produced', 10, 2)->nullable(); // كمية الطاقة المنتجة (kwh)

            // ملاحظات وأعطال
            $table->text('operational_notes')->nullable(); // ملاحظات تشغيلية
            $table->text('malfunctions')->nullable(); // الأعطال المسجلة

            $table->timestamps();
            $table->softDeletes();
            
            // إضافة index للبحث السريع
            $table->index(['generator_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
};
