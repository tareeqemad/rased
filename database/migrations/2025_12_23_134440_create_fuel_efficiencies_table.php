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
        Schema::create('fuel_efficiencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();

            // تاريخ الاستهلاك
            $table->date('consumption_date');

            // ساعات التشغيل
            $table->decimal('operating_hours', 8, 2)->nullable();

            // سعر الوقود
            $table->decimal('fuel_price_per_liter', 10, 2)->nullable(); // سعر الوقود (للتر)

            // كفاءة استهلاك الوقود
            $table->decimal('fuel_efficiency_percentage', 5, 2)->nullable(); // كفاءة استهلاك الوقود (%)
            $table->string('fuel_efficiency_comparison')->nullable(); // مقارنة مع المعيار (within_standard, above, below)

            // كفاءة توزيع الطاقة
            $table->decimal('energy_distribution_efficiency', 5, 2)->nullable(); // كفاءة توزيع الطاقة (%)
            $table->string('energy_efficiency_comparison')->nullable(); // مقارنة مع المعيار (within_standard, above, below)

            // تكلفة التشغيل
            $table->decimal('total_operating_cost', 12, 2)->nullable(); // حساب تكلفة التشغيل الإجمالية

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_efficiencies');
    }
};
