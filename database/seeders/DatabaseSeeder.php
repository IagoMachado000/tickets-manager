<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketAttachment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Project::factory(3)->create()->each(function (Project $project) {

            $users = User::factory(5)->for($project)->create();

            Ticket::factory(10)->for($project)->make()->each(function (Ticket $ticket) use ($users) {

                $ticket->user_id = $users->random()->id;
                $ticket->save();

                $messages = TicketMessage::factory(fake()->numberBetween(1, 5))->for($ticket)->make()->each(function (TicketMessage $message) use ($users) {

                    $message->user_id = $users->random()->id;
                    $message->save();

                    if (fake()->boolean(40)) {
                        TicketAttachment::factory(fake()->numberBetween(1, 3))->for($message, 'message')->create();
                    }
                });
            });
        });

        // keep a known test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
