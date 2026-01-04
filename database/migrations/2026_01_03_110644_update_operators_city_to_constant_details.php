<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            // إضافة عمود جديد city_id كـ foreign key
            $table->foreignId('city_id')->nullable()->after('governorate')
                ->constrained('constant_details')->nullOnDelete();
            
            // إضافة index
            $table->index('city_id');
        });

        // نقل البيانات من city (string) إلى city_id إذا أمكن
        // سنترك city كـ nullable string للتوافق مع البيانات القديمة
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropIndex(['city_id']);
            $table->dropColumn('city_id');
        });
    }
};
