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
            
            // البيانات الأساسية
            $table->string('name'); // اسم المشغل (العربي)
            $table->string('name_en')->nullable(); // اسم المشغل (بالإنجليزية) - للعرض والتقارير
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_alt')->nullable();
            $table->text('address')->nullable();
            
            // العلاقة مع المستخدم (المالك)
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            
            // تتبع المستخدمين (من TracksUser trait)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();

            // بيانات الوحدة
            $table->string('unit_number')->nullable();
            $table->string('unit_code')->nullable();
            $table->string('unit_name')->nullable();

            // الموقع
            $table->integer('governorate')->nullable(); // Governorate enum
            $table->foreignId('city_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 20 (المدن)');
            $table->text('detailed_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // القدرة والقدرات الفنية
            $table->decimal('total_capacity', 10, 2)->nullable();
            $table->integer('generators_count')->default(0);
            $table->boolean('synchronization_available')->default(false);
            $table->decimal('max_synchronization_capacity', 10, 2)->nullable();

            // الملكية
            $table->string('owner_name')->nullable(); // اسم المالك (قد يكون مختلف عن owner_id)
            $table->string('owner_id_number')->nullable(); // رقم هوية المالك
            $table->string('operator_id_number')->nullable(); // رقم هوية المشغل

            // الحالة العامة
            $table->string('status')->default('active'); // active, inactive
            $table->boolean('is_approved')->default(false)->comment('حالة الاعتماد - المشغل يحتاج موافقة Admin/Super Admin');
            $table->boolean('profile_completed')->default(false);

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['governorate', 'city_id'], 'idx_operators_location');
            $table->index(['name', 'unit_number'], 'idx_operators_search');
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
