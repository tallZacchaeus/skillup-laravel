<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('program_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('slug');
            $table->string('title');
            $table->string('theme')->nullable();
            $table->string('status')->default('draft');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('schedule_text')->nullable();
            $table->string('delivery_mode')->default('in_person');
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('venue_map_url')->nullable();
            $table->unsignedInteger('capacity_total')->nullable();
            $table->string('payment_mode')->default('immediate');
            $table->date('age_reference_date')->nullable();
            $table->unsignedSmallInteger('seat_hold_minutes')->default(45);
            $table->boolean('allow_installments')->default(false);
            $table->string('terms_url')->nullable();
            $table->text('refund_policy')->nullable();
            $table->json('content')->nullable();
            $table->json('registration_fields')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'slug']);
            $table->index(['program_id', 'status']);
        });

        Schema::create('program_edition_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('summary')->nullable();
            $table->json('curriculum')->nullable();
            $table->string('facilitator_note')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['program_edition_id', 'slug']);
        });

        Schema::create('program_registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('program_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_edition_track_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->string('guardian_name');
            $table->string('guardian_email');
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_whatsapp')->nullable();

            $table->string('participant_name');
            $table->date('participant_dob');
            $table->string('participant_gender')->nullable();

            $table->string('status')->default('started');
            $table->string('email_verification_token', 64)->nullable();
            $table->string('email_verification_otp')->nullable();
            $table->timestamp('email_verification_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('email_invalid_at')->nullable();
            $table->timestamp('seat_held_until')->nullable();
            $table->string('resume_token', 64)->nullable()->index();
            $table->timestamp('profile_completed_at')->nullable();

            $table->json('custom_fields')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('authorized_pickups')->nullable();
            $table->boolean('first_aid_consent')->default(false);
            $table->boolean('media_consent')->default(false);
            $table->timestamp('guardian_consent_at')->nullable();

            $table->uuid('sibling_group_uuid')->nullable()->index();
            $table->string('source')->default('web');
            $table->json('utm')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['program_edition_id', 'guardian_email', 'participant_name', 'participant_dob'],
                'program_registrations_dedupe_unique',
            );
            $table->index(['program_edition_id', 'status']);
            $table->index(['program_edition_track_id', 'status']);
        });

        Schema::create('program_certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('serial')->unique();
            $table->foreignId('program_registration_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('program_title');
            $table->date('issued_on');
            $table->string('pdf_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_certificates');
        Schema::dropIfExists('program_registrations');
        Schema::dropIfExists('program_edition_tracks');
        Schema::dropIfExists('program_editions');
        Schema::dropIfExists('programs');
    }
};
