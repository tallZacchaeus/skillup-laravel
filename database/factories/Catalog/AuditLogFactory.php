<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'auditable_type' => null,
            'auditable_id' => null,
            'action' => 'created',
            'description' => fake()->sentence(),
            'old_values' => null,
            'new_values' => [],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadata' => [],
        ];
    }
}
