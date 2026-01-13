<?php

namespace Database\Factories;

use App\Models\TicketType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketType>
 */
class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        [$prefix, $name] = fake()->randomElement([
            ['GA', 'General Admission'],
            ['VIP', 'VIP Pass'],
            ['EB', 'Early Bird'],
            ['BS', 'Balcony Seat'],
            ['BA', 'Backstage Access'],
        ]);
        return [
            'event_id' => \App\Models\Event::factory(),
            'name' => $name,
            'code' => $prefix . '-' . fake()->unique()->regexify("[A-Z]{4}"),
            'price' => fake()->randomFloat(2, 10, 250),
            'total_quantity' => fake()->numberBetween(50, 500),
            'available_quantity' => function (array $attributes) {
                return $attributes['total_quantity'];
            },
            'description' => fake()->sentence(8),
        ];
    }
}
