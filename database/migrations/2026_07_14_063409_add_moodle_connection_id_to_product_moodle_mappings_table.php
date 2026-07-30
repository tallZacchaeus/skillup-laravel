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
        Schema::table('product_moodle_mappings', function (Blueprint $table) {
            $table->foreignId('moodle_connection_id')->nullable()->after('product_id')->constrained('moodle_connections')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_moodle_mappings', function (Blueprint $table) {
            $table->dropForeign(['moodle_connection_id']);
            $table->dropColumn('moodle_connection_id');
        });
    }
};
