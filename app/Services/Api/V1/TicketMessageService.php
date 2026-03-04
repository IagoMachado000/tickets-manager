<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketMessageService
{
    public function __construct(
        private TicketMessageAttachmentService $attachmentService
    ) {}

    public function create(array $data, Ticket $ticket, User $user)
    {
        if ($user->role === 'user' && $ticket->user_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }

        return DB::transaction(function () use ($data, $ticket, $user) {
            $message = $ticket->messages()->create([
                'user_id' => $user->id,
                'message' => $data['message'],
            ]);

            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $this->attachmentService->create($file, $message);
                }
            }

            $status = $user->role === 'support'
                ? 'answered'
                : 'pending';

            $ticket->update([
                'last_interaction_at' => now(),
                'status' => $status
            ]);

            return $message->load([
                'user:id,name,email,role,project_id',
                'attachments'
            ]);
        });
    }
}
