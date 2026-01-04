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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // نوع الصيانة
            $table->string('maintenance_type'); // periodic, emergency

            // تاريخ الصيانة
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable()->comment('تاريخ الصيانة القادمة المتوقع');
            $table->string('next_maintenance_type')->nullable()->comment('نوع الصيانة القادمة');

            // الفني المسؤول
            $table->string('technician_name')->nullable(); // اسم الفني المسؤول

            // الأعمال المنفذة
            $table->text('work_performed')->nullable(); // الاعمال المنفذة

            // زمن التوقف
            $table->decimal('downtime_hours', 8, 2)->nullable(); // زمن التوقف (ساعات)

            // تكلفة الصيانة
            $table->decimal('maintenance_cost', 10, 2)->nullable(); // تكلفة الصيانة

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['generator_id', 'maintenance_date'], 'idx_maintenance_records_generator_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
