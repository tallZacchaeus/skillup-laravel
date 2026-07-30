<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\ProductStatus;
use App\Jobs\Lms\EnrollUserInMoodleJob;
use App\Jobs\Lms\SuspendMoodleEnrollmentJob;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMoodleMapping;
use App\Models\Lms\LmsAccount;
use App\Models\Lms\LmsSyncLog;
use App\Models\Lms\MoodleConnection;
use App\Models\Lms\MoodleCourse;
use App\Models\User;
use App\Services\Lms\MoodleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MoodleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected MoodleConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = MoodleConnection::create([
            'name' => 'Test Moodle',
            'base_url' => 'https://moodle.example.com',
            'token' => 'test-token-123',
            'is_active' => true,
        ]);
    }

    public function test_moodle_service_can_test_successful_connection(): void
    {
        Http::fake([
            'https://moodle.example.com/webservice/rest/server.php*' => Http::response([
                'sitename' => 'Test Site',
                'version' => '4.2',
            ], 200),
        ]);

        $service = new MoodleService();
        $this->assertTrue($service->testConnection($this->connection));
    }

    public function test_moodle_service_fails_connection_gracefully(): void
    {
        Http::fake([
            'https://moodle.example.com/webservice/rest/server.php*' => Http::response([
                'exception' => 'dml_exception',
                'message' => 'Database error',
            ], 200),
        ]);

        $service = new MoodleService();
        $this->assertFalse($service->testConnection($this->connection));
    }

    public function test_moodle_service_can_fetch_and_cache_courses(): void
    {
        Http::fake([
            'https://moodle.example.com/webservice/rest/server.php*' => Http::response([
                [
                    'id' => 101,
                    'shortname' => 'PM101',
                    'fullname' => 'Product Management 101',
                    'summary' => 'Introduction to PM',
                ],
                [
                    'id' => 102,
                    'shortname' => 'SE102',
                    'fullname' => 'Software Engineering 102',
                    'summary' => 'Advanced Coding',
                ]
            ], 200),
        ]);

        $service = new MoodleService();
        $courses = $service->fetchCourses($this->connection);

        $this->assertCount(2, $courses);
        $this->assertEquals('PM101', $courses[0]['shortname']);
    }

    public function test_moodle_enrollment_job_creates_user_and_enrolls_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $product = Product::factory()->create([
            'status' => ProductStatus::Published,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        ProductMoodleMapping::create([
            'product_id' => $product->id,
            'moodle_connection_id' => $this->connection->id,
            'moodle_course_id' => '101',
            'moodle_group_id' => '5',
            'sync_enabled' => true,
        ]);

        Http::fake([
            // 1. Search user by email -> return empty list (user does not exist)
            '*wsfunction=core_user_get_users_by_field*' => Http::response([], 200),
            // 2. Create user -> return created user details
            '*wsfunction=core_user_create_users*' => Http::response([
                ['id' => 456, 'username' => 'john123']
            ], 200),
            // 3. Enroll user -> return empty/null indicating success
            '*wsfunction=enrol_manual_enrol_users*' => Http::response([], 200),
            // 4. Add to group -> return empty/null indicating success
            '*wsfunction=core_group_add_group_members*' => Http::response([], 200),
        ]);

        $job = new EnrollUserInMoodleJob($enrollment);
        $job->handle(new MoodleService());

        // Assert user has an LMS account record cached
        $this->assertDatabaseHas('lms_accounts', [
            'user_id' => $user->id,
            'moodle_user_id' => '456',
        ]);

        // Assert enrollment was set to Active and provisioned
        $this->assertEquals(EnrollmentStatus::Active, $enrollment->fresh()->status);
        $this->assertEquals('456', $enrollment->fresh()->moodle_user_id);
        $this->assertEquals('101', $enrollment->fresh()->moodle_course_id);
        $this->assertNotNull($enrollment->fresh()->provisioned_at);

        // Assert sync log was written
        $this->assertDatabaseHas('lms_sync_logs', [
            'enrollment_id' => $enrollment->id,
            'action' => 'enroll',
            'status' => 'success',
        ]);
    }

    public function test_moodle_suspension_job_suspends_correctly(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'moodle_user_id' => '456',
            'moodle_course_id' => '101',
            'status' => EnrollmentStatus::Suspended,
        ]);

        Http::fake([
            '*wsfunction=enrol_manual_unenrol_users*' => Http::response([], 200),
        ]);

        $job = new SuspendMoodleEnrollmentJob($enrollment);
        $job->handle(new MoodleService());

        $this->assertDatabaseHas('lms_sync_logs', [
            'enrollment_id' => $enrollment->id,
            'action' => 'suspend',
            'status' => 'success',
        ]);
    }

    public function test_moodle_enrollment_job_fails_immediately_on_missing_mapping(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        $job = new EnrollUserInMoodleJob($enrollment);
        
        $job->handle(new MoodleService());
        
        $this->assertEquals(EnrollmentStatus::Failed, $enrollment->fresh()->status);
        $this->assertStringContainsString('No sync-enabled Moodle course mapping found', $enrollment->fresh()->failed_reason);
    }

    public function test_moodle_enrollment_job_throws_exception_on_api_error_for_retry(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => EnrollmentStatus::Pending,
        ]);
        ProductMoodleMapping::create([
            'product_id' => $product->id,
            'moodle_connection_id' => $this->connection->id,
            'moodle_course_id' => '101',
            'sync_enabled' => true,
        ]);

        Http::fake([
            '*wsfunction=core_user_get_users_by_field*' => Http::response([
                'exception' => 'moodle_exception',
                'errorcode' => 'api_timeout',
                'message' => 'API Timeout'
            ], 200),
        ]);

        $job = new EnrollUserInMoodleJob($enrollment);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API Timeout');
        $job->handle(new MoodleService());

        $this->assertEquals(EnrollmentStatus::Failed, $enrollment->fresh()->status);
    }

    public function test_moodle_enrollment_job_handles_group_assignment_failure_as_partial(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        ProductMoodleMapping::create([
            'product_id' => $product->id,
            'moodle_connection_id' => $this->connection->id,
            'moodle_course_id' => '101',
            'moodle_group_id' => '5',
            'sync_enabled' => true,
        ]);

        Http::fake([
            '*wsfunction=core_user_get_users_by_field*' => Http::response([], 200),
            '*wsfunction=core_user_create_users*' => Http::response([['id' => 456, 'username' => 'testuser']], 200),
            '*wsfunction=enrol_manual_enrol_users*' => Http::response([], 200),
            // Group add fails
            '*wsfunction=core_group_add_group_members*' => Http::response(['exception' => 'invalid_group', 'message' => 'Group not found'], 200),
        ]);

        $job = new EnrollUserInMoodleJob($enrollment);
        $job->handle(new MoodleService());

        // Assert enrollment is Partial
        $this->assertEquals(EnrollmentStatus::Partial, $enrollment->fresh()->status);
        $this->assertEquals('456', $enrollment->fresh()->moodle_user_id);
        
        $this->assertDatabaseHas('lms_sync_logs', [
            'enrollment_id' => $enrollment->id,
            'action' => 'enroll',
            'status' => 'partial',
        ]);
    }

    public function test_moodle_commands_are_registered_and_log_scaffold(): void
    {
        Http::fake([
            '*wsfunction=core_course_get_categories*' => Http::response([['id' => 1, 'name' => 'Category 1']], 200),
            '*wsfunction=core_course_get_courses*' => Http::response([['id' => 10, 'fullname' => 'Course 1', 'shortname' => 'C1']], 200),
            '*wsfunction=core_group_get_course_groups*' => Http::response([['id' => 100, 'name' => 'Group A']], 200),
        ]);

        $this->artisan('skillup:moodle-import')->assertSuccessful();
        
        $this->assertDatabaseHas('lms_sync_logs', [
            'action' => 'import',
            'status' => 'success',
        ]);
        
        $this->assertDatabaseHas('moodle_categories', ['moodle_category_id' => 1]);
        $this->assertDatabaseHas('moodle_courses', ['moodle_course_id' => 10]);
        $this->assertDatabaseHas('moodle_groups', ['moodle_group_id' => 100]);

        $this->artisan('skillup:moodle-reconcile')->assertSuccessful();
        $this->assertDatabaseHas('lms_sync_logs', [
            'action' => 'reconcile',
            'status' => 'success',
        ]);
    }
}
