<?php

namespace Database\Factories;

use App\Models\Ebook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ebook>
 */
class EbookFactory extends Factory
{
    protected $model = Ebook::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'category' => fake()->randomElement(['Leadership', 'Bien-être', 'Business']),
            'description' => fake()->paragraph(),
            'author_note' => null,
            'image' => null,
            'cta_label' => 'Télécharger',
            'cta_url' => 'https://charriow.com/ebook-test',
            'status' => 'published',
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }
}
