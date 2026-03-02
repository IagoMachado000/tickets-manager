<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => null,
            'user_id' => null,
            'message' => fake('pt_BR')->paragraphs(fake()->numberBetween(1, 3), true),
        ];
    }
}
