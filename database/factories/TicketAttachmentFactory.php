<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketAttachment>
 */
class TicketAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $extensions = ['pdf', 'png', 'jpg', 'txt', 'docx'];
        $ext = fake()->randomElement($extensions);
        $mimeMap = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'txt' => 'text/plain',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $fileName = fake('pt_BR')->words(3, true) . '.' . $ext;

        return [
            'ticket_message_id' => null,
            'file_name' => $fileName,
            'file_path' => 'attachments/' . now()->format('Y/m/d') . '/' . $fileName,
            'file_size' => fake()->numberBetween(1024, 5 * 1024 * 1024),
            'mime_type' => $mimeMap[$ext],
        ];
    }
}
