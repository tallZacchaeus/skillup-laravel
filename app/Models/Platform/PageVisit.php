<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per public page view, written by the RecordPageVisit middleware.
 *
 * Filament panels, XHR that is not an Inertia navigation, webhooks, health
 * probes, and obvious crawlers are excluded, so this table reflects
 * marketing-site traffic rather than "requests that reached PHP".
 */
class PageVisit extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'visitor_id',
        'path',
        'referrer_host',
        'ip_address',
        'user_agent',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Raw visits are only needed for the trailing-year reports; anything older
     * is dropped by the nightly model:prune so the table stays bounded.
     */
    public function prunable(): Builder
    {
        return static::query()->where('visited_at', '<', now()->subDays(400));
    }
}
