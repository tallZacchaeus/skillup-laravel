<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lms\MoodleConnection;
use App\Services\Lms\MoodleService;
use App\Models\Lms\LmsSyncLog;

class MoodleImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skillup:moodle-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import courses, categories, and groups from Moodle';

    /**
     * Execute the console command.
     */
    public function handle(MoodleService $moodleService)
    {
        $this->info('Starting Moodle import...');

        $connections = MoodleConnection::where('is_active', true)->get();

        foreach ($connections as $connection) {
            $this->info("Importing for connection: {$connection->name}");
            
            try {
                // Fetch and store categories
                $categories = $moodleService->fetchCategories($connection);
                foreach ($categories as $cat) {
                    if (isset($cat['id']) && isset($cat['name'])) {
                        \App\Models\Lms\MoodleCategory::updateOrCreate(
                            ['moodle_connection_id' => $connection->id, 'moodle_category_id' => $cat['id']],
                            ['name' => $cat['name'], 'parent_id' => $cat['parent'] ?? null]
                        );
                    }
                }

                // Fetch and store courses
                $courses = $moodleService->fetchCourses($connection);
                foreach ($courses as $course) {
                    if (isset($course['id']) && isset($course['fullname'])) {
                        \App\Models\Lms\MoodleCourse::updateOrCreate(
                            ['moodle_connection_id' => $connection->id, 'moodle_course_id' => $course['id']],
                            [
                                'fullname' => $course['fullname'],
                                'shortname' => $course['shortname'] ?? $course['fullname'],
                                'summary' => $course['summary'] ?? null
                            ]
                        );

                        // Fetch groups for this course
                        try {
                            $groups = $moodleService->fetchGroups($connection, (string)$course['id']);
                            foreach ($groups as $group) {
                                if (isset($group['id']) && isset($group['name'])) {
                                    \App\Models\Lms\MoodleGroup::updateOrCreate(
                                        ['moodle_connection_id' => $connection->id, 'moodle_group_id' => $group['id']],
                                        [
                                            'moodle_course_id' => $course['id'],
                                            'name' => $group['name']
                                        ]
                                    );
                                }
                            }
                        } catch (\Exception $e) {
                            $this->warn("Could not fetch groups for course {$course['id']}: " . $e->getMessage());
                        }
                    }
                }
                
                LmsSyncLog::create([
                    'action' => 'import',
                    'status' => 'success',
                    'attempts' => 1,
                    'error_message' => "Imported data from connection {$connection->id}",
                ]);
                $this->info("Import successful.");
            } catch (\Exception $e) {
                $this->error("Failed to import: " . $e->getMessage());
                LmsSyncLog::create([
                    'action' => 'import',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'attempts' => 1,
                ]);
            }
        }
    }
}
