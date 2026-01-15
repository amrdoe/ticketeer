<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 days', '+1 month');
        $endDate = (clone $startDate)->modify('+'.rand(1, 3).' hours');
        $saleStart = $this->faker->dateTimeBetween('-1 month', $startDate);
        $saleEnd = (clone $endDate)->modify('-1 hour');

        return [
            'user_id' => User::factory()->organizer(),
            'title' => fake()->randomElement([
                'Music Concert',
                'Art Exhibition',
                'Tech Conference',
                'Food Festival',
                'Charity Run',
                'Theater Play',
                'Dance Performance',
                'Book Fair',
                'Film Screening',
                'Workshop Series',
            ]).' '.date('Y'),
            'description' => $this->faker->paragraph(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sale_start' => $saleStart,
            'sale_end' => $saleEnd,
            'location' => $this->faker->address(),
            'image_url' => $this->faker->imageUrl(640, 480, 'event', true),
        ];
    }
}
