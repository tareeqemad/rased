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
        Schema::table('fuel_efficiencies', function (Blueprint $table) {
            $table->decimal('fuel_consumed', 10, 2)->nullable()->after('fuel_price_per_liter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_efficiencies', function (Blueprint $table) {
            $table->dropColumn('fuel_consumed');
        });
    }
};
