<?php

namespace App\Services\Lms;

use App\Models\Lms\LmsAccount;
use App\Models\Lms\LmsApiLog;
use App\Models\Lms\MoodleConnection;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MoodleService
{
    /**
     * Send a request to the Moodle Web Services API.
     */
    protected function request(MoodleConnection $connection, string $function, array $params = []): array
    {
        $startTime = microtime(true);
        $url = rtrim($connection->base_url, '/') . '/webservice/rest/server.php';

        $queryParams = [
            'wstoken' => $connection->token,
            'wsfunction' => $function,
            'moodlewsrestformat' => 'json',
        ];

        // Format query params & post params
        $response = null;
        $status = null;
        $responsePayload = [];

        try {
            $response = Http::asForm()->post($url . '?' . http_build_query($queryParams), $params);
            $status = $response->status();
            $responsePayload = $response->json();
        } catch (Exception $e) {
            $responsePayload = ['error' => $e->getMessage()];
            $status = 500;
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Log the API call
        try {
            $requestSummary = [];
            if (isset($params['users']) && is_array($params['users'])) {
                $requestSummary['user_count'] = count($params['users']);
                if (isset($params['users'][0]['email'])) {
                    $requestSummary['target_email'] = $params['users'][0]['email'];
                }
            } elseif (isset($params['enrolments']) && is_array($params['enrolments'])) {
                $requestSummary['enrolment_count'] = count($params['enrolments']);
                if (isset($params['enrolments'][0]['userid'])) {
                    $requestSummary['target_userid'] = $params['enrolments'][0]['userid'];
                }
                if (isset($params['enrolments'][0]['courseid'])) {
                    $requestSummary['target_courseid'] = $params['enrolments'][0]['courseid'];
                }
            } elseif (isset($params['members']) && is_array($params['members'])) {
                $requestSummary['member_count'] = count($params['members']);
                if (isset($params['members'][0]['groupid'])) {
                    $requestSummary['target_groupid'] = $params['members'][0]['groupid'];
                }
            } elseif (isset($params['field']) && isset($params['values'])) {
                $requestSummary['search_field'] = $params['field'];
                $requestSummary['search_values'] = $params['values'];
            } else {
                $requestSummary['keys'] = array_keys($params);
            }
            $responseSummary = [];
            if (is_array($responsePayload)) {
                if (isset($responsePayload['exception'])) {
                    $responseSummary = [
                        'exception' => $responsePayload['exception'],
                        'errorcode' => $responsePayload['errorcode'] ?? null,
                        'message' => $responsePayload['message'] ?? null,
                    ];
                } elseif (isset($responsePayload[0]['id'])) {
                    $responseSummary = ['count' => count($responsePayload), 'first_id' => $responsePayload[0]['id']];
                } elseif (isset($responsePayload['id'])) {
                    $responseSummary = ['id' => $responsePayload['id']];
                } else {
                    $responseSummary = ['status' => 'success_or_unknown_format'];
                }
            } else {
                 $responseSummary = ['raw_type' => gettype($responsePayload)];
            }

            LmsApiLog::create([
                'moodle_connection_id' => $connection->id,
                'endpoint' => $function,
                'request_payload' => $requestSummary,
                'response_payload' => $responseSummary,
                'response_status' => $status,
                'duration_ms' => $durationMs,
            ]);
        } catch (Exception $logEx) {
            Log::error('Failed to write LMS API log: ' . $logEx->getMessage());
        }

        if ($status !== 200) {
            throw new Exception("Moodle API request to {$function} failed with status {$status}: " . json_encode($responsePayload));
        }

        if (is_array($responsePayload) && isset($responsePayload['exception'])) {
            throw new Exception("Moodle API returned exception: {$responsePayload['message']} ({$responsePayload['errorcode']})");
        }

        return $responsePayload;
    }

    /**
     * Test connection to the Moodle site.
     */
    public function testConnection(MoodleConnection $connection): bool
    {
        try {
            $info = $this->request($connection, 'core_webservice_get_site_info');
            return isset($info['sitename']);
        } catch (Exception $e) {
            Log::warning("Moodle connection test failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all courses from Moodle.
     */
    public function fetchCourses(MoodleConnection $connection): array
    {
        return $this->request($connection, 'core_course_get_courses');
    }

    /**
     * Fetch all course categories from Moodle.
     */
    public function fetchCategories(MoodleConnection $connection): array
    {
        return $this->request($connection, 'core_course_get_categories');
    }

    /**
     * Fetch all groups in a course from Moodle.
     */
    public function fetchGroups(MoodleConnection $connection, string $moodleCourseId): array
    {
        return $this->request($connection, 'core_group_get_course_groups', [
            'courseid' => (int) $moodleCourseId,
        ]);
    }

    /**
     * Find or create a user in Moodle, return their Moodle user ID.
     */
    public function findOrCreateUser(MoodleConnection $connection, User $user): string
    {
        // 1. Check if we already have a record
        $account = LmsAccount::where('user_id', $user->id)
            ->where('moodle_connection_id', $connection->id)
            ->first();

        if ($account) {
            return $account->moodle_user_id;
        }

        // 2. Search Moodle by email
        $results = $this->request($connection, 'core_user_get_users_by_field', [
            'field' => 'email',
            'values' => [$user->email],
        ]);

        if (!empty($results) && is_array($results) && isset($results[0]['id'])) {
            $moodleUserId = (string) $results[0]['id'];
            $moodleUsername = $results[0]['username'];

            LmsAccount::create([
                'user_id' => $user->id,
                'moodle_connection_id' => $connection->id,
                'moodle_user_id' => $moodleUserId,
                'moodle_username' => $moodleUsername,
                'sync_status' => 'active',
            ]);

            return $moodleUserId;
        }

        // 3. Create the user if not found in Moodle
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', strstr($user->email, '@', true))) . rand(100, 999);
        $password = 'SkillUp!' . Str::random(8) . rand(1, 9); // Ensure strong password requirements

        $nameParts = explode(' ', $user->name, 2);
        $firstname = $nameParts[0];
        $lastname = $nameParts[1] ?? 'Learner';

        $usersData = [
            'users' => [
                [
                    'username' => $username,
                    'password' => $password,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $user->email,
                    'auth' => 'manual',
                ]
            ]
        ];

        $creationResult = $this->request($connection, 'core_user_create_users', $usersData);

        if (!empty($creationResult) && isset($creationResult[0]['id'])) {
            $moodleUserId = (string) $creationResult[0]['id'];

            LmsAccount::create([
                'user_id' => $user->id,
                'moodle_connection_id' => $connection->id,
                'moodle_user_id' => $moodleUserId,
                'moodle_username' => $username,
                'sync_status' => 'active',
                'metadata' => [],
            ]);

            return $moodleUserId;
        }

        throw new Exception("Failed to create user in Moodle for email: {$user->email}");
    }

    /**
     * Enroll a Moodle user in a course and optionally add to a group.
     */
    public function enrollUser(MoodleConnection $connection, string $moodleUserId, string $moodleCourseId, ?string $moodleGroupId = null): bool
    {
        $params = [
            'enrolments' => [
                [
                    'roleid' => 5, // Student role in Moodle
                    'userid' => (int) $moodleUserId,
                    'courseid' => (int) $moodleCourseId,
                ]
            ]
        ];

        $this->request($connection, 'enrol_manual_enrol_users', $params);

        // If group is set, add user to group
        if ($moodleGroupId) {
            try {
                $this->request($connection, 'core_group_add_group_members', [
                    'members' => [
                        [
                            'groupid' => (int) $moodleGroupId,
                            'userid' => (int) $moodleUserId,
                        ]
                    ]
                ]);
            } catch (Exception $groupEx) {
                throw new \App\Exceptions\MoodleGroupAssignmentException("Enrolled user {$moodleUserId} in course {$moodleCourseId} but failed to add to group {$moodleGroupId}: " . $groupEx->getMessage());
            }
        }

        return true;
    }

    /**
     * Unenroll/suspend a Moodle user from a course.
     */
    public function suspendEnrollment(MoodleConnection $connection, string $moodleUserId, string $moodleCourseId): bool
    {
        $params = [
            'enrolments' => [
                [
                    'userid' => (int) $moodleUserId,
                    'courseid' => (int) $moodleCourseId,
                ]
            ]
        ];

        $this->request($connection, 'enrol_manual_unenrol_users', $params);
        return true;
    }
}
