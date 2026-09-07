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

    /** Ebook vendu sur le site : prix + PDF (le fichier lui-même reste à créer). */
    public function sellable(int $price = 5000): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'price' => $price,
            'currency' => 'XOF',
            'file_path' => 'ebooks/files/'.Str::random(8).'.pdf',
        ]);
    }

    /** Promotion en cours. */
    public function onPromo(int $promoPrice = 3500): static
    {
        return $this->state(fn () => [
            'promo_price' => $promoPrice,
            'promo_starts_at' => now()->subDay(),
            'promo_ends_at' => now()->addWeek(),
        ]);
    }

    /** Promotion terminée : le prix normal reprend effet. */
    public function promoExpired(int $promoPrice = 3500): static
    {
        return $this->state(fn () => [
            'promo_price' => $promoPrice,
            'promo_starts_at' => now()->subWeeks(2),
            'promo_ends_at' => now()->subDay(),
        ]);
    }

    /** Promotion programmée : pas encore commencée. */
    public function promoScheduled(int $promoPrice = 3500): static
    {
        return $this->state(fn () => [
            'promo_price' => $promoPrice,
            'promo_starts_at' => now()->addDays(3),
            'promo_ends_at' => now()->addDays(10),
        ]);
    }
}
