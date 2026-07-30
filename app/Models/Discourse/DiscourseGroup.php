<?php

namespace App\Models\Discourse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscourseGroup extends Model
{
    protected $fillable = [
        'discourse_connection_id',
        'name',
        'discourse_group_id',
        'description',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(DiscourseConnection::class, 'discourse_connection_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(DiscourseGroupMapping::class);
    }
}
