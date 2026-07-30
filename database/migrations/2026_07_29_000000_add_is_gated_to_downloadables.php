<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Makes download gating configurable per resource. Defaults to gated (true)
     * to preserve the existing email-capture behaviour.
     */
    public function up(): void
    {
        Schema::table('downloadables', function (Blueprint $table) {
            $table->boolean('is_gated')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('downloadables', function (Blueprint $table) {
            $table->dropColumn('is_gated');
        });
    }
};
