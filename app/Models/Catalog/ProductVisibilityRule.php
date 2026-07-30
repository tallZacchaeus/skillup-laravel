<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\ProductVisibilityRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVisibilityRule extends Model
{
    /** @use HasFactory<ProductVisibilityRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'rule_type',
        'operator',
        'value',
        'starts_at',
        'ends_at',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
