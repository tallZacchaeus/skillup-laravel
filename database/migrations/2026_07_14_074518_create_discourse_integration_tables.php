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
        Schema::create('discourse_connections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->text('sso_secret');
            $table->text('api_key');
            $table->string('api_username');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('discourse_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discourse_connection_id')->constrained('discourse_connections')->cascadeOnDelete();
            $table->string('name');
            $table->string('discourse_group_id')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('discourse_group_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discourse_connection_id')->constrained('discourse_connections')->cascadeOnDelete();
            $table->foreignId('discourse_group_id')->constrained('discourse_groups')->cascadeOnDelete();
            $table->string('mappable_type');
            $table->unsignedBigInteger('mappable_id');
            $table->index(['mappable_type', 'mappable_id']);
            $table->timestamps();
        });

        Schema::create('discourse_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discourse_connection_id')->nullable()->constrained('discourse_connections')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('status');
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discourse_sync_logs');
        Schema::dropIfExists('discourse_group_mappings');
        Schema::dropIfExists('discourse_groups');
        Schema::dropIfExists('discourse_connections');
    }
};
