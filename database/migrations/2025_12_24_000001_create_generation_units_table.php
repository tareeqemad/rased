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
        Schema::create('generation_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();

            // بيانات وحدة التوليد
            $table->string('unit_code')->unique()->comment('كود الوحدة التلقائي: GU-PP-CC-NNN (PP=كود المحافظة، CC=كود المدينة، NNN=رقم تسلسلي للوحدة)');
            $table->string('unit_number')->comment('رقم الوحدة: 001, 002, إلخ');
            $table->string('name')->comment('اسم وحدة التوليد');
            $table->integer('generators_count')->default(0)->comment('عدد المولدات المطلوبة في هذه الوحدة');

            // الحالة
            $table->string('status')->default('active')->comment('حالة الوحدة: active, inactive');

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['operator_id', 'status'], 'idx_generation_units_operator_status');
            $table->index(['unit_code'], 'idx_generation_units_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_units');
    }
};

