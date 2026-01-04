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
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->string('tank_code')->nullable()->after('generator_id');
            $table->index('tank_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropIndex(['tank_code']);
            $table->dropColumn('tank_code');
        });
    }
};
