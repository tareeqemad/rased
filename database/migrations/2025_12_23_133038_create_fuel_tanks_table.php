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
        Schema::create('fuel_tanks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();
            $table->integer('capacity')->nullable(); // لتر
            $table->string('location')->nullable(); // ارضي, علوي, تحت الارض
            $table->boolean('filtration_system_available')->default(false);
            $table->string('condition')->nullable();
            $table->string('material')->nullable(); // حديد, بلاستيك, مقوى, فايبر
            $table->string('usage')->nullable(); // مركزي / احتياطي
            $table->string('measurement_method')->nullable(); // سيخ, مدرج, ساعه ميكانيكية, حساس الكتروني, خرطوم شفاف
            $table->integer('order')->default(1); // ترتيب الخزان
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_tanks');
    }
};
