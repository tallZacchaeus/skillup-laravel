<?php

namespace Tests\Feature\Services\Discourse;

use App\Jobs\Discourse\SyncUserDiscourseGroupsJob;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Product;
use App\Models\Discourse\DiscourseConnection;
use App\Models\Discourse\DiscourseGroup;
use App\Models\Discourse\DiscourseGroupMapping;
use App\Models\Discourse\DiscourseSyncLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class DiscourseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected DiscourseConnection $connection;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = DiscourseConnection::create([
            'name' => 'Test Discourse',
            'base_url' => 'https://community.example.com',
            'sso_secret' => 'supersecretssokey123456789012',
            'api_key' => 'apikey12345',
            'api_username' => 'system',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'email' => 'student@example.com',
            'name' => 'Jane Doe',
            'email_verified_at' => now(),
        ]);
    }

    public function test_discourse_sso_rejects_missing_parameters()
    {
        $response = $this->actingAs($this->user)
            ->get(route('discourse.sso'));

        $response->assertStatus(302); // Validation redirect
    }

    public function test_discourse_sso_rejects_invalid_signature()
    {
        $payload = base64_encode('nonce=123456');
        
        $response = $this->actingAs($this->user)
            ->get(route('discourse.sso', [
                'sso' => $payload,
                'sig' => 'wrongsignature',
            ]));

        $response->assertStatus(400);
    }

    public function test_discourse_sso_requires_verified_email()
    {
        $unverified = User::factory()->unverified()->create();
        $payload = base64_encode('nonce=123456');
        $sig = hash_hmac('sha256', $payload, $this->connection->sso_secret);

        $response = $this->actingAs($unverified)
            ->get(route('discourse.sso', [
                'sso' => $payload,
                'sig' => $sig,
            ]));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_discourse_sso_computes_correct_groups_and_redirects()
    {
        $payload = base64_encode('nonce=123456');
        $sig = hash_hmac('sha256', $payload, $this->connection->sso_secret);

        // Seed Mappings
        $product = Product::factory()->create();
        $enrollment = Enrollment::withoutEvents(function () use ($product) {
            return Enrollment::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $this->user->id,
                'product_id' => $product->id,
                'status' => 'active',
            ]);
        });

        $group = DiscourseGroup::create([
            'discourse_connection_id' => $this->connection->id,
            'name' => 'premium-students',
            'discourse_group_id' => '10',
        ]);

        DiscourseGroupMapping::create([
            'discourse_connection_id' => $this->connection->id,
            'discourse_group_id' => $group->id,
            'mappable_type' => Product::class,
            'mappable_id' => $product->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('discourse.sso', [
                'sso' => $payload,
                'sig' => $sig,
            ]));

        $response->assertRedirect();
        $redirectUrl = $response->getTargetUrl();
        $this->assertStringStartsWith('https://community.example.com/session/sso_login', $redirectUrl);

        // Parse query params
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $queryParams);
        $this->assertArrayHasKey('sso', $queryParams);
        $this->assertArrayHasKey('sig', $queryParams);

        // Decrypted response checks
        $returnedPayload = base64_decode($queryParams['sso']);
        parse_str($returnedPayload, $returnedParams);

        $this->assertEquals('123456', $returnedParams['nonce']);
        $this->assertEquals('student@example.com', $returnedParams['email']);
        $this->assertEquals($this->user->id, $returnedParams['external_id']);
        $this->assertEquals('janedoe', $returnedParams['username']);
        $this->assertEquals('premium-students', $returnedParams['add_groups']);
        $this->assertEquals('', $returnedParams['remove_groups']);

        // Check SSO log
        $log = DiscourseSyncLog::where('action', 'sso_login')->first();
        $this->assertNotNull($log);
        $this->assertEquals('success', $log->status);
    }

    public function test_discourse_group_sync_job_adds_and_removes_user_groups()
    {
        Http::fake([
            'https://community.example.com/groups/10/members.json' => Http::response(['success' => true], 200),
            'https://community.example.com/groups/11/members.json' => Http::response(['success' => true], 200),
        ]);

        // Mappings
        $product = Product::factory()->create();
        $cohort = Cohort::factory()->create();

        $group1 = DiscourseGroup::create([
            'discourse_connection_id' => $this->connection->id,
            'name' => 'course-group',
            'discourse_group_id' => '10',
        ]);

        $group2 = DiscourseGroup::create([
            'discourse_connection_id' => $this->connection->id,
            'name' => 'cohort-group',
            'discourse_group_id' => '11',
        ]);

        DiscourseGroupMapping::create([
            'discourse_connection_id' => $this->connection->id,
            'discourse_group_id' => $group1->id,
            'mappable_type' => Product::class,
            'mappable_id' => $product->id,
        ]);

        DiscourseGroupMapping::create([
            'discourse_connection_id' => $this->connection->id,
            'discourse_group_id' => $group2->id,
            'mappable_type' => Cohort::class,
            'mappable_id' => $cohort->id,
        ]);

        // Enrollment has active Product but no Cohort
        Enrollment::withoutEvents(function () use ($product) {
            Enrollment::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $this->user->id,
                'product_id' => $product->id,
                'cohort_id' => null,
                'status' => 'active',
            ]);
        });

        // Dispatch Job
        SyncUserDiscourseGroupsJob::dispatchSync($this->user);

        // Verify POST request (add) for group 10, DELETE request (remove) for group 11
        Http::assertSent(function ($request) {
            return $request->url() === 'https://community.example.com/groups/10/members.json' && $request->method() === 'POST';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://community.example.com/groups/11/members.json' && $request->method() === 'DELETE';
        });

        // Log entry
        $log = DiscourseSyncLog::where('action', 'background_sync')->first();
        $this->assertNotNull($log);
        $this->assertEquals('success', $log->status);
        $this->assertEquals(['10'], $log->payload['added_groups']);
        $this->assertEquals(['11'], $log->payload['removed_groups']);
    }

    public function test_discourse_group_sync_job_throws_exception_on_api_failure()
    {
        Http::fake([
            'https://community.example.com/groups/10/members.json' => Http::response(['success' => false], 500),
        ]);

        $product = Product::factory()->create();
        $group = DiscourseGroup::create([
            'discourse_connection_id' => $this->connection->id,
            'name' => 'course-group',
            'discourse_group_id' => '10',
        ]);

        DiscourseGroupMapping::create([
            'discourse_connection_id' => $this->connection->id,
            'discourse_group_id' => $group->id,
            'mappable_type' => Product::class,
            'mappable_id' => $product->id,
        ]);

        Enrollment::withoutEvents(function () use ($product) {
            Enrollment::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $this->user->id,
                'product_id' => $product->id,
                'cohort_id' => null,
                'status' => 'active',
                'order_id' => null,
            ]);
        });

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to sync some groups on Discourse');

        try {
            SyncUserDiscourseGroupsJob::dispatchSync($this->user);
        } finally {
            $log = DiscourseSyncLog::where('action', 'background_sync')->first();
            $this->assertNotNull($log);
            $this->assertEquals('failed', $log->status);
            $this->assertStringContainsString('Failed to sync some groups on Discourse', $log->error_message);
        }
    }
}
