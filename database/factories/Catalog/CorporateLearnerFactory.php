<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\CorporateAccount;
use App\Models\Catalog\CorporateLearner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorporateLearner>
 */
class CorporateLearnerFactory extends Factory
{
    protected $model = CorporateLearner::class;

    public function definition(): array
    {
        return [
            'corporate_account_id' => CorporateAccount::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'status' => 'invited',
            'invited_at' => now(),
            'accepted_at' => null,
            'metadata' => [],
        ];
    }
}
