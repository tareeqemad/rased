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
        // إضافة generation_unit_id أولاً
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->foreignId('generation_unit_id')->nullable()->after('generator_id')->constrained('generation_units')->cascadeOnDelete();
        });

        // نقل البيانات من generator_id إلى generation_unit_id
        // كل مولد له generation_unit_id، نستخدمه لنقل خزانات الوقود
        DB::statement('
            UPDATE fuel_tanks 
            INNER JOIN generators ON fuel_tanks.generator_id = generators.id
            SET fuel_tanks.generation_unit_id = generators.generation_unit_id
            WHERE generators.generation_unit_id IS NOT NULL
        ');

        // حذف generator_id وإضافة index جديد
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropForeign(['generator_id']);
            $table->dropColumn('generator_id');
            $table->foreignId('generation_unit_id')->nullable(false)->change();
            $table->index('generation_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إعادة generator_id
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->foreignId('generator_id')->nullable()->after('generation_unit_id')->constrained('generators')->cascadeOnDelete();
        });

        // نقل البيانات من generation_unit_id إلى generator_id
        // نأخذ أول مولد من كل وحدة توليد
        DB::statement('
            UPDATE fuel_tanks 
            INNER JOIN generators ON fuel_tanks.generation_unit_id = generators.generation_unit_id
            SET fuel_tanks.generator_id = generators.id
            WHERE generators.generation_unit_id IS NOT NULL
        ');

        // حذف generation_unit_id
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropForeign(['generation_unit_id']);
            $table->dropColumn('generation_unit_id');
            $table->foreignId('generator_id')->nullable(false)->change();
        });
    }
};

