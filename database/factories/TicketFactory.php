<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'in_progress', 'answered', 'closed']);
        $now = now();
        $lastInteraction = fake()->dateTimeBetween('-20 days', 'now');

        return [
            'project_id' => null,
            'user_id' => null,
            'title' => fake('pt_BR')->sentence(6),
            'description' => fake('pt_BR')->paragraph(),
            'status' => $status,
            'last_interaction_at' => $lastInteraction,
            'closed_at' => $status === 'closed' ? fake()->dateTimeBetween($lastInteraction, 'now') : null,
        ];
    }
}
