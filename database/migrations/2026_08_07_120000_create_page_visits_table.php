<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // sha256 of the session id — enough to count unique visitors, but
            // useless to anyone who gets hold of the table (cannot be replayed
            // as a session cookie).
            $table->string('visitor_id', 64)->nullable();
            $table->string('path', 255);
            $table->string('referrer_host', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at');

            // Every reporting query is "a date range, then group by" — these two
            // composites cover the daily series, top pages, and unique visitors.
            $table->index(['visited_at', 'path']);
            $table->index(['visited_at', 'visitor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
