<?php

namespace App\Jobs\Lms;

use App\Models\Catalog\Enrollment;
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

class SuspendMoodleEnrollmentJob implements ShouldQueue
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

        if (!$this->enrollment->moodle_user_id || !$this->enrollment->moodle_course_id) {
            Log::info("Skipping Moodle unenrollment for enrollment ID {$this->enrollment->id} as moodle details are missing.");
            return;
        }

        $connection = MoodleConnection::where('is_active', true)->first();
        if (!$connection) {
            Log::warning("No active Moodle connection found to suspend enrollment ID {$this->enrollment->id}.");
            return;
        }

        try {
            $moodleService->suspendEnrollment(
                $connection,
                $this->enrollment->moodle_user_id,
                $this->enrollment->moodle_course_id
            );

            LmsSyncLog::create([
                'enrollment_id' => $this->enrollment->id,
                'user_id' => $this->enrollment->user_id,
                'action' => 'suspend',
                'status' => 'success',
                'attempts' => $attempts,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to suspend Moodle enrollment for enrollment ID {$this->enrollment->id}: " . $e->getMessage());

            try {
                LmsSyncLog::create([
                    'enrollment_id' => $this->enrollment->id,
                    'user_id' => $this->enrollment->user_id,
                    'action' => 'suspend',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'attempts' => $attempts,
                ]);
            } catch (Exception $logEx) {
                Log::error("Failed to write LMS Sync suspension failure log: " . $logEx->getMessage());
            }

            throw $e;
        }
    }
}
