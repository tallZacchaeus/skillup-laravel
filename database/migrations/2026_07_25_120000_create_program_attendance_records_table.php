<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_registration_id')->constrained()->cascadeOnDelete();
            $table->date('attended_on');
            $table->boolean('present')->default(true);
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['program_registration_id', 'attended_on'], 'program_attendance_unique_day');
        });

        Schema::table('program_editions', function (Blueprint $table) {
            $table->unsignedSmallInteger('safeguarding_retention_months')->default(6)->after('seat_hold_minutes');
        });

        Schema::table('program_registrations', function (Blueprint $table) {
            $table->timestamp('safeguarding_purged_at')->nullable()->after('guardian_consent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_attendance_records');

        Schema::table('program_editions', function (Blueprint $table) {
            $table->dropColumn('safeguarding_retention_months');
        });

        Schema::table('program_registrations', function (Blueprint $table) {
            $table->dropColumn('safeguarding_purged_at');
        });
    }
};
