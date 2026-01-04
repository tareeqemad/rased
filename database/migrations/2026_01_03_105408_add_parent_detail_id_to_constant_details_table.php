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
        Schema::table('constant_details', function (Blueprint $table) {
            $table->foreignId('parent_detail_id')->nullable()->after('constant_master_id')
                ->constrained('constant_details')->nullOnDelete();
            
            $table->index(['parent_detail_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('constant_details', function (Blueprint $table) {
            $table->dropForeign(['parent_detail_id']);
            $table->dropIndex(['parent_detail_id', 'is_active']);
            $table->dropColumn('parent_detail_id');
        });
    }
};
