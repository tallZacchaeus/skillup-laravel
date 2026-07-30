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
        Schema::create('moodle_connections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->string('token');
            $table->string('service_name')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('moodle_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_connection_id')->constrained()->cascadeOnDelete();
            $table->string('moodle_course_id');
            $table->string('shortname');
            $table->string('fullname');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->unique(['moodle_connection_id', 'moodle_course_id']);
        });

        Schema::create('moodle_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_connection_id')->constrained()->cascadeOnDelete();
            $table->string('moodle_category_id');
            $table->string('name');
            $table->string('parent_id')->nullable();
            $table->timestamps();

            $table->unique(['moodle_connection_id', 'moodle_category_id']);
        });

        Schema::create('moodle_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_connection_id')->constrained()->cascadeOnDelete();
            $table->string('moodle_group_id');
            $table->string('moodle_course_id');
            $table->string('name');
            $table->timestamps();

            $table->unique(['moodle_connection_id', 'moodle_group_id']);
        });

        Schema::create('lms_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('moodle_connection_id')->constrained()->cascadeOnDelete();
            $table->string('moodle_user_id');
            $table->string('moodle_username');
            $table->string('sync_status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['moodle_connection_id', 'moodle_user_id']);
        });

        Schema::create('lms_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // e.g. enroll, suspend, unenroll
            $table->string('status'); // e.g. success, failed
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moodle_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('endpoint');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('response_status')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_api_logs');
        Schema::dropIfExists('lms_sync_logs');
        Schema::dropIfExists('lms_accounts');
        Schema::dropIfExists('moodle_groups');
        Schema::dropIfExists('moodle_categories');
        Schema::dropIfExists('moodle_courses');
        Schema::dropIfExists('moodle_connections');
    }
};
