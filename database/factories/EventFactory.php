<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'event_date' => now()->addDays(15),
            'location' => 'Sofitel Hôtel Ivoire',
            'city' => 'Abidjan',
            'capacity' => null,
            'is_paid' => false,
            'price' => null,
            'currency' => 'XOF',
            'status' => 'published',
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => ['event_date' => now()->subDays(3)]);
    }

    public function withCapacity(int $capacity): static
    {
        return $this->state(fn () => ['capacity' => $capacity]);
    }
}
