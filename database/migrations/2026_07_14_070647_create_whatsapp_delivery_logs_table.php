<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_message_id')->constrained('whatsapp_messages')->cascadeOnDelete();
            $table->string('provider_message_id')->nullable();
            $table->string('status'); // success, failed
            $table->text('error_message')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_delivery_logs');
    }
};
