<?php

namespace Database\Factories;

use App\Models\PropertyDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PropertyDraft>
 */
class PropertyDraftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'seller_id' => User::factory(),
            'payload' => ['title' => fake()->sentence(4)],
            'uploads' => [],
            'current_step' => 1,
            'last_saved_at' => now(),
        ];
    }
}
