<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\CorporateAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CorporateAccount>
 */
class CorporateAccountFactory extends Factory
{
    protected $model = CorporateAccount::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'primary_contact_user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'billing_email' => fake()->companyEmail(),
            'billing_address' => fake()->address(),
            'status' => 'active',
            'metadata' => [],
        ];
    }
}
