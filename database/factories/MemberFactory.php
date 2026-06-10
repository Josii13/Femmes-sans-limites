<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['standard', 'gold', 'premium']);

        return [
            'member_number' => Member::generateNumber($type),
            'name' => fake()->name('female'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'motivation' => fake()->sentence(12),
            'profession' => fake()->jobTitle(),
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'type' => $type,
            'status' => 'pending',
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'joined_at' => now(),
            'expires_at' => now()->addYear(),
        ]);
    }

    public function optedOut(): static
    {
        return $this->state(fn () => ['marketing_opt_out_at' => now()]);
    }

    public function type(string $type): static
    {
        return $this->state(fn () => ['type' => $type, 'member_number' => Member::generateNumber($type)]);
    }
}
