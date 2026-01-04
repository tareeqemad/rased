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
        Schema::create('constant_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('constant_master_id')->constrained('constant_masters')->cascadeOnDelete();
            $table->string('label'); // البيان (غزة، رفح، إلخ)
            $table->string('code')->nullable(); // الترميز (GAZ, RAF, إلخ)
            $table->string('value')->nullable(); // القيمة (يمكن استخدامها للبحث)
            $table->text('notes')->nullable(); // ملاحظة
            $table->boolean('is_active')->default(true); // هل العنصر نشط؟
            $table->integer('order')->default(0); // ترتيب العرض
            $table->timestamps();
            $table->softDeletes();

            $table->index(['constant_master_id', 'is_active']);
            $table->index(['constant_master_id', 'value'], 'idx_constant_details_master_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constant_details');
    }
};
