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
        Schema::create('complaints_suggestions', function (Blueprint $table) {
            $table->id();
            
            // نوع الطلب (شكوى أو مقترح)
            $table->enum('type', ['complaint', 'suggestion'])->default('complaint');
            
            // معلومات المرسل
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            
            // الموقع
            $table->integer('governorate')->nullable(); // Governorate enum value
            
            // المولد المرتبط (اختياري)
            $table->foreignId('generator_id')->nullable()->constrained('generators')->nullOnDelete();
            
            // محتوى الطلب
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('image')->nullable(); // مسار الصورة
            
            // حالة الطلب
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])->default('pending');
            
            // الرد على الطلب
            $table->text('response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            
            // رمز التتبع
            $table->string('tracking_code', 20)->unique();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tracking_code');
            $table->index('status');
            $table->index('type');
            $table->index('governorate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints_suggestions');
    }
};
