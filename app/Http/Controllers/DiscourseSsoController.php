<?php

namespace App\Http\Controllers;

use App\Models\Discourse\DiscourseConnection;
use App\Models\Discourse\DiscourseGroupMapping;
use App\Models\Discourse\DiscourseSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class DiscourseSsoController extends Controller
{
    public function sso(Request $request)
    {
        $request->validate([
            'sso' => 'required|string',
            'sig' => 'required|string',
        ]);

        $connection = DiscourseConnection::where('is_active', true)->first();
        if (!$connection) {
            abort(503, 'Discourse connection not configured.');
        }

        $sso = $request->input('sso');
        $sig = $request->input('sig');
        $ssoSecret = $connection->sso_secret;

        // Validate signature
        $computedSig = hash_hmac('sha256', $sso, $ssoSecret);
        if (!hash_equals($computedSig, $sig)) {
            abort(400, 'Invalid SSO signature.');
        }

        $user = Auth::user();

        // Require verified email
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('error', 'You must verify your email before accessing the community.');
        }

        try {
            // Decode payload
            $decoded = base64_decode($sso);
            parse_str($decoded, $payloadParams);
            $nonce = $payloadParams['nonce'] ?? null;

            if (!$nonce) {
                abort(400, 'Nonce is missing from SSO payload.');
            }

            // Fetch user enrollments and mapping info
            $enrollments = $user->enrollments()
                ->whereIn('status', ['active', 'completed'])
                ->get();

            $productIds = $enrollments->pluck('product_id')->unique();
            $cohortIds = $enrollments->pluck('cohort_id')->filter()->unique();
            $trackIds = $enrollments->map(fn($e) => $e->product?->track_id)->filter()->unique();

            $mappings = DiscourseGroupMapping::with('group')
                ->where('discourse_connection_id', $connection->id)
                ->get();

            $targetGroups = [];
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
                    $targetGroups[] = $mapping->group->name;
                }
            }

            $targetGroups = array_unique($targetGroups);
            $allMappedGroups = $mappings->map(fn($m) => $m->group?->name)->filter()->unique()->toArray();
            $removeGroups = array_diff($allMappedGroups, $targetGroups);

            $params = [
                'nonce' => $nonce,
                'email' => $user->email,
                'external_id' => (string) $user->id,
                'username' => Str::slug($user->name, ''),
                'name' => $user->name,
                'add_groups' => implode(',', $targetGroups),
                'remove_groups' => implode(',', $removeGroups),
            ];

            $returnPayload = base64_encode(http_build_query($params));
            $returnSig = hash_hmac('sha256', $returnPayload, $ssoSecret);

            DiscourseSyncLog::create([
                'discourse_connection_id' => $connection->id,
                'user_id' => $user->id,
                'action' => 'sso_login',
                'status' => 'success',
                'payload' => $params,
            ]);

            $redirectUrl = rtrim($connection->base_url, '/') . '/session/sso_login?' . http_build_query([
                'sso' => $returnPayload,
                'sig' => $returnSig,
            ]);

            return redirect()->away($redirectUrl);

        } catch (Exception $e) {
            DiscourseSyncLog::create([
                'discourse_connection_id' => $connection->id,
                'user_id' => $user->id,
                'action' => 'sso_login',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            abort(500, 'SSO processing failed.');
        }
    }
}
