<?php

namespace App\Jobs\Lms;

use App\Enums\EnrollmentStatus;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\ProductMoodleMapping;
use App\Models\Lms\LmsSyncLog;
use App\Models\Lms\MoodleConnection;
use App\Services\Lms\MoodleService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnrollUserInMoodleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Enrollment $enrollment
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MoodleService $moodleService): void
    {
        $attempts = $this->attempts();

        // 1. Find product to Moodle mapping
        $mapping = ProductMoodleMapping::with('moodleConnection')
            ->where('product_id', $this->enrollment->product_id)
            ->where('sync_enabled', true)
            ->first();

        if (!$mapping) {
            $err = "No sync-enabled Moodle course mapping found for product: {$this->enrollment->product?->title}";
            $this->logFailure($err, $attempts);
            $this->enrollment->update([
                'status' => EnrollmentStatus::Failed,
                'failed_reason' => $err,
            ]);
            $this->fail(new Exception($err));
            return;
        }

        // 2. Get active Moodle connection from mapping
        $connection = $mapping->moodleConnection;
        if (!$connection || !$connection->is_active) {
            $err = 'Mapped Moodle connection is missing or inactive.';
            $this->logFailure($err, $attempts);
            $this->enrollment->update([
                'status' => EnrollmentStatus::Failed,
                'failed_reason' => $err,
            ]);
            $this->fail(new Exception($err));
            return;
        }

        try {
            // 3. Find or create Moodle user
            $moodleUserId = $moodleService->findOrCreateUser($connection, $this->enrollment->user);

            // 4. Enroll user in course/group
            $moodleService->enrollUser(
                $connection,
                $moodleUserId,
                $mapping->moodle_course_id,
                $mapping->moodle_group_id
            );

            // 5. Update local enrollment
            $this->enrollment->update([
                'status' => EnrollmentStatus::Active,
                'moodle_user_id' => $moodleUserId,
                'moodle_course_id' => $mapping->moodle_course_id,
                'provisioned_at' => now(),
                'failed_reason' => null,
            ]);

            // 6. Log success
            LmsSyncLog::create([
                'enrollment_id' => $this->enrollment->id,
                'user_id' => $this->enrollment->user_id,
                'action' => 'enroll',
                'status' => 'success',
                'attempts' => $attempts,
            ]);

            // Update mapping last synced at
            $mapping->update(['last_synced_at' => now()]);

            if ($this->enrollment->user) {
                $this->enrollment->user->notify(new \App\Notifications\MoodleAccessSuccessNotification($this->enrollment));
            }

        } catch (\App\Exceptions\MoodleGroupAssignmentException $e) {
            $this->logFailure($e->getMessage(), $attempts, 'partial');

            $this->enrollment->update([
                'status' => EnrollmentStatus::Partial,
                'failed_reason' => $e->getMessage(),
                'moodle_user_id' => $moodleUserId ?? null,
                'moodle_course_id' => $mapping->moodle_course_id,
                'provisioned_at' => now(),
            ]);

            if ($this->enrollment->user) {
                $this->enrollment->user->notify(new \App\Notifications\MoodleAccessFailedNotification($this->enrollment, $e->getMessage()));
            }

        } catch (Exception $e) {
            $this->logFailure($e->getMessage(), $attempts, 'failed');

            $this->enrollment->update([
                'status' => EnrollmentStatus::Failed,
                'failed_reason' => $e->getMessage(),
            ]);

            // Re-throw exception to allow retry
            throw $e;
        }
    }

    protected function logFailure(string $errorMessage, int $attempts, string $status = 'failed'): void
    {
        Log::error("Failed Moodle enrollment for enrollment ID {$this->enrollment->id}: {$errorMessage}");

        try {
            LmsSyncLog::create([
                'enrollment_id' => $this->enrollment->id,
                'user_id' => $this->enrollment->user_id,
                'action' => 'enroll',
                'status' => $status,
                'error_message' => $errorMessage,
                'attempts' => $attempts,
            ]);
        } catch (Exception $logEx) {
            Log::error("Failed to write LMS Sync failure log: " . $logEx->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        if ($this->enrollment->user) {
            $this->enrollment->user->notify(new \App\Notifications\MoodleAccessFailedNotification($this->enrollment, $exception->getMessage()));
        }
    }
}
