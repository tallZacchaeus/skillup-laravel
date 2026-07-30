<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('future_modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('summary')->nullable();
            $table->string('status')->default('planned');
            $table->string('module_group')->default('future');
            $table->string('public_path')->nullable();
            $table->boolean('is_publicly_visible')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('readiness_checks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_publicly_visible', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('future_modules');
    }
};
