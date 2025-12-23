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
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_alt')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            // بيانات الوحدة
            $table->string('unit_number')->nullable();
            $table->string('unit_code')->nullable();
            $table->string('unit_name')->nullable();

            // الموقع
            $table->integer('governorate')->nullable();
            $table->string('city')->nullable();
            $table->text('detailed_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // القدرة والقدرات الفنية
            $table->decimal('total_capacity', 10, 2)->nullable();
            $table->integer('generators_count')->default(0);
            $table->boolean('synchronization_available')->default(false);
            $table->decimal('max_synchronization_capacity', 10, 2)->nullable();

            // الملكية والتشغيل
            $table->string('owner_name')->nullable();
            $table->string('owner_id_number')->nullable();
            $table->string('operation_entity')->nullable();
            $table->string('operator_id_number')->nullable();

            // المستفيدون والبيئة
            $table->integer('beneficiaries_count')->nullable();
            $table->text('beneficiaries_description')->nullable();
            $table->string('environmental_compliance_status')->nullable();

            // الحالة العامة
            $table->string('status')->default('active');
            $table->boolean('profile_completed')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
