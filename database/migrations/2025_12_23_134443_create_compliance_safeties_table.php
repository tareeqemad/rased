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
        Schema::create('compliance_safeties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();

            // شهادة السلامة
            $table->string('safety_certificate_status'); // available, expired, not_available

            // تاريخ آخر زيارة تفقدية
            $table->date('last_inspection_date')->nullable();

            // الجهة المنفذة
            $table->string('inspection_authority')->nullable(); // الجهة المنفذة

            // نتيجة الزيارة
            $table->text('inspection_result')->nullable(); // نتيجة الزيارة

            // المخالفات المسجلة
            $table->text('violations')->nullable(); // المخالفات المسجلة

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_safeties');
    }
};
