<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('promo_video_url')->nullable()->after('description');
            $table->json('relevance')->nullable()->after('requirements');
            $table->decimal('rating_average', 3, 2)->default(0)->after('is_featured');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_average');
            $table->unsignedInteger('students_count')->default(0)->after('rating_count');
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reviewer_name');
            $table->string('reviewer_title')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'promo_video_url',
                'relevance',
                'rating_average',
                'rating_count',
                'students_count',
            ]);
        });
    }
};
