<?php

namespace App\Models\Discourse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscourseGroupMapping extends Model
{
    protected $fillable = [
        'discourse_connection_id',
        'discourse_group_id',
        'mappable_type',
        'mappable_id',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(DiscourseConnection::class, 'discourse_connection_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(DiscourseGroup::class, 'discourse_group_id');
    }

    public function mappable(): MorphTo
    {
        return $this->morphTo();
    }
}
