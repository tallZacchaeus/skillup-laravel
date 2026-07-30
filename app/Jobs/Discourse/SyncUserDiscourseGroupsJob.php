<?php

namespace App\Jobs\Discourse;

use App\Models\Discourse\DiscourseConnection;
use App\Models\Discourse\DiscourseGroupMapping;
use App\Models\Discourse\DiscourseSyncLog;
use App\Models\User;
use App\Services\Discourse\DiscourseApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Exception;

class SyncUserDiscourseGroupsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public User $user
    ) {}

    public function handle(DiscourseApiService $apiService): void
    {
        $connection = DiscourseConnection::where('is_active', true)->first();
        if (!$connection) {
            return;
        }

        // Clean user's username for Discourse
        $username = Str::slug($this->user->name, '');

        try {
            $enrollments = $this->user->enrollments()
                ->whereIn('status', ['active', 'completed'])
                ->get();

            $productIds = $enrollments->pluck('product_id')->unique();
            $cohortIds = $enrollments->pluck('cohort_id')->filter()->unique();
            $trackIds = $enrollments->map(fn($e) => $e->product?->track_id)->filter()->unique();

            $mappings = DiscourseGroupMapping::with('group')
                ->where('discourse_connection_id', $connection->id)
                ->get();

            $targetGroupIds = [];
            foreach ($mappings as $mapping) {
                if (!$mapping->group) {
                    continue;
                }

                $shouldHave = false;
                if ($mapping->mappable_type === \App\Models\Catalog\Product::class && $productIds->contains($mapping->mappable_id)) {
                    $shouldHave = true;
                } elseif ($mapping->mappable_type === \App\Models\Catalog\Cohort::class && $cohortIds->contains($mapping->mappable_id)) {
                    $shouldHave = true;
                } elseif ($mapping->mappable_type === \App\Models\Catalog\Track::class && $trackIds->contains($mapping->mappable_id)) {
                    $shouldHave = true;
                }

                if ($shouldHave) {
                    $targetGroupIds[] = $mapping->group->discourse_group_id;
                }
            }

            $targetGroupIds = array_unique($targetGroupIds);

            $allMappedGroupIds = $mappings->map(fn($m) => $m->group?->discourse_group_id)->filter()->unique()->toArray();
            $removeGroupIds = array_diff($allMappedGroupIds, $targetGroupIds);

            $added = [];
            $removed = [];
            $failedAdds = [];
            $failedRemoves = [];

            foreach ($targetGroupIds as $groupId) {
                if ($apiService->addUserToGroup($connection, $groupId, $username)) {
                    $added[] = $groupId;
                } else {
                    $failedAdds[] = $groupId;
                }
            }

            foreach ($removeGroupIds as $groupId) {
                if ($apiService->removeUserFromGroup($connection, $groupId, $username)) {
                    $removed[] = $groupId;
                } else {
                    $failedRemoves[] = $groupId;
                }
            }

            if (!empty($failedAdds) || !empty($failedRemoves)) {
                throw new Exception("Failed to sync some groups on Discourse. Failed additions: " . implode(',', $failedAdds) . ". Failed removals: " . implode(',', $failedRemoves));
            }

            DiscourseSyncLog::create([
                'discourse_connection_id' => $connection->id,
                'user_id' => $this->user->id,
                'action' => 'background_sync',
                'status' => 'success',
                'payload' => [
                    'added_groups' => $added,
                    'removed_groups' => $removed,
                ],
            ]);

        } catch (Exception $e) {
            DiscourseSyncLog::create([
                'discourse_connection_id' => $connection->id,
                'user_id' => $this->user->id,
                'action' => 'background_sync',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
