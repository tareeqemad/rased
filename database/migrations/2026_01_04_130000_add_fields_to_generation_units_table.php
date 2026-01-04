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
        Schema::table('generation_units', function (Blueprint $table) {
            // الملكية والتشغيل
            $table->string('owner_name')->nullable()->after('name');
            $table->string('owner_id_number')->nullable()->after('owner_name');
            $table->string('operation_entity')->nullable()->after('owner_id_number')->comment('same_owner, other_party');
            $table->string('operator_id_number')->nullable()->after('operation_entity');
            $table->string('phone')->nullable()->after('operator_id_number');
            $table->string('phone_alt')->nullable()->after('phone');
            $table->string('email')->nullable()->after('phone_alt');

            // الموقع
            $table->string('governorate')->nullable()->after('email')->comment('كود المحافظة');
            $table->foreignId('city_id')->nullable()->after('governorate')->constrained('constant_details')->nullOnDelete();
            $table->string('detailed_address')->nullable()->after('city_id');
            $table->decimal('latitude', 10, 8)->nullable()->after('detailed_address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            // القدرات الفنية
            $table->decimal('total_capacity', 10, 2)->nullable()->after('longitude')->comment('إجمالي القدرة (KVA)');
            $table->boolean('synchronization_available')->default(false)->after('total_capacity');
            $table->decimal('max_synchronization_capacity', 10, 2)->nullable()->after('synchronization_available');

            // المستفيدون والبيئة
            $table->integer('beneficiaries_count')->nullable()->after('max_synchronization_capacity');
            $table->text('beneficiaries_description')->nullable()->after('beneficiaries_count');
            $table->string('environmental_compliance_status')->nullable()->after('beneficiaries_description')->comment('compliant, under_monitoring, under_evaluation, non_compliant');

            // فهارس
            $table->index(['governorate', 'city_id'], 'idx_generation_units_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف foreign key constraint قبل حذف العمود
        Schema::table('generation_units', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
        });
        
        // محاولة حذف الفهرس إذا كان موجوداً (MySQL ستحذفه تلقائياً عند حذف الأعمدة، لكن نحذفه صراحة لتجنب الأخطاء)
        try {
            Schema::table('generation_units', function (Blueprint $table) {
                $table->dropIndex('idx_generation_units_location');
            });
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا كان الفهرس غير موجود
        }
        
        // حذف الأعمدة
        Schema::table('generation_units', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name',
                'owner_id_number',
                'operation_entity',
                'operator_id_number',
                'phone',
                'phone_alt',
                'email',
                'governorate',
                'city_id',
                'detailed_address',
                'latitude',
                'longitude',
                'total_capacity',
                'synchronization_available',
                'max_synchronization_capacity',
                'beneficiaries_count',
                'beneficiaries_description',
                'environmental_compliance_status',
            ]);
        });
    }
};

