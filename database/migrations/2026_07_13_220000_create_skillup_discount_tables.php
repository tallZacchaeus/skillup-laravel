<?php

use App\Enums\DiscountRedemptionStatus;
use App\Enums\DiscountRuleStatus;
use App\Enums\DiscountType;
use App\Enums\ScholarshipApplicationStatus;
use App\Enums\ScholarshipAwardStatus;
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
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('track_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default(DiscountRuleStatus::Draft->value)->index();
            $table->string('type')->default(DiscountType::Percentage->value)->index();
            $table->decimal('value', 12, 2)->default(0);
            $table->char('currency', 3)->default('NGN');
            $table->decimal('minimum_order_amount', 12, 2)->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_email_limit')->default(1);
            $table->unsignedInteger('per_user_limit')->default(1);
            $table->boolean('requires_code')->default(true)->index();
            $table->boolean('requires_email_eligibility')->default(false)->index();
            $table->boolean('installment_compatible')->default(true);
            $table->boolean('stackable')->default(false);
            $table->boolean('is_public')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_rule_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('visibility')->default('private')->index();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redeemed_count')->default(0);
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_eligibility_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_rule_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('source_filename')->nullable();
            $table->unsignedInteger('total_emails')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_eligible_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_eligibility_list_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('normalized_email')->index();
            $table->string('name')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['discount_eligibility_list_id', 'normalized_email'], 'eligible_email_list_unique');
        });

        Schema::create('discount_redemptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('discount_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discount_code_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('discount_eligibility_list_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('normalized_email')->nullable()->index();
            $table->string('status')->default(DiscountRedemptionStatus::Locked->value)->index();
            $table->string('discount_type');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->char('currency', 3)->default('NGN');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_after_discount', 12, 2)->default(0);
            $table->string('code')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['discount_rule_id', 'status']);
            $table->index(['discount_code_id', 'status']);
        });

        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('track_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('normalized_email')->index();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default(ScholarshipApplicationStatus::Submitted->value)->index();
            $table->string('requested_discount_type')->default(DiscountType::FullScholarship->value);
            $table->decimal('requested_discount_value', 12, 2)->default(100);
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarship_awards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('scholarship_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('discount_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('awarded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('track_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('normalized_email')->index();
            $table->string('status')->default(ScholarshipAwardStatus::Active->value)->index();
            $table->string('discount_type')->default(DiscountType::FullScholarship->value);
            $table->decimal('discount_value', 12, 2)->default(100);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_awards');
        Schema::dropIfExists('scholarship_applications');
        Schema::dropIfExists('discount_redemptions');
        Schema::dropIfExists('discount_eligible_emails');
        Schema::dropIfExists('discount_eligibility_lists');
        Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('discount_rules');
    }
};
