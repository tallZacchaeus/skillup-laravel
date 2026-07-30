<?php

namespace App\Services\Discourse;

use App\Models\Discourse\DiscourseConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DiscourseApiService
{
    public function checkHealth(DiscourseConnection $connection): bool
    {
        try {
            $response = Http::withHeaders($this->getHeaders($connection))
                ->timeout(5)
                ->get(rtrim($connection->base_url, '/') . '/site.json');

            return $response->successful();
        } catch (Exception $e) {
            Log::error("Discourse health check failed for connection {$connection->id}: " . $e->getMessage());
            return false;
        }
    }

    public function addUserToGroup(DiscourseConnection $connection, string $discourseGroupId, string $username): bool
    {
        try {
            $response = Http::withHeaders($this->getHeaders($connection))
                ->post(rtrim($connection->base_url, '/') . "/groups/{$discourseGroupId}/members.json", [
                    'usernames' => $username,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("Discourse: Failed to add user {$username} to group {$discourseGroupId}. Status: {$response->status()}, Response: {$response->body()}");
            return false;
        } catch (Exception $e) {
            Log::error("Discourse API Exception adding user {$username} to group {$discourseGroupId}: " . $e->getMessage());
            return false;
        }
    }

    public function removeUserFromGroup(DiscourseConnection $connection, string $discourseGroupId, string $username): bool
    {
        try {
            $response = Http::withHeaders($this->getHeaders($connection))
                ->delete(rtrim($connection->base_url, '/') . "/groups/{$discourseGroupId}/members.json", [
                    'usernames' => $username,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("Discourse: Failed to remove user {$username} from group {$discourseGroupId}. Status: {$response->status()}, Response: {$response->body()}");
            return false;
        } catch (Exception $e) {
            Log::error("Discourse API Exception removing user {$username} from group {$discourseGroupId}: " . $e->getMessage());
            return false;
        }
    }

    protected function getHeaders(DiscourseConnection $connection): array
    {
        return [
            'Api-Key' => $connection->api_key,
            'Api-Username' => $connection->api_username,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
