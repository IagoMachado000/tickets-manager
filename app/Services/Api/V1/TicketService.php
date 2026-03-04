<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function list(Project $project, User $user)
    {
        $query = $project->tickets()
            ->active()
            ->with('user:id,name,email,role,project_id');

        if ($user->role === 'user' && $user->project_id !== $project->id) {
            abort(403, 'Acesso negado.');
        }

        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        return $query->paginate(10);
    }

    public function create(array $data, Project $project, User $user): Ticket
    {
        if ($user->role === 'user' && $user->project_id !== $project->id) {
            abort(403, 'Acesso negado.');
        }

        return DB::transaction(function () use ($data, $project, $user) {
            return $project->tickets()->create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => 'pending',
                'last_interaction_at' => now(),
            ]);
        });
    }

    public function show(Ticket $ticket, User $user): Ticket
    {
        if ($user->role === 'user' && $user->project_id !== $ticket->project_id) {
            abort(403, 'Acesso negado.');
        }

        if ($user->role === 'user' && $ticket->user_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }

        $ticket->load([
            'user:id,name,email,role,project_id',
            'messages.user:id,name,email,role,project_id',
            'messages.attachments:id,ticket_message_id,file_name,file_path,file_size,mime_type',
        ]);

        return $ticket;
    }

    public function update(Ticket $ticket, array $data, User $user): Ticket
    {
        if ($user->role === 'user' && $user->project_id !== $ticket->project_id) {
            abort(403, 'Acesso negado.');
        }

        if ($user->role === 'user' && $ticket->user_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }

        if ($user->role === 'user' && ($ticket->status !== 'pending' || $ticket->closed_at !== null)) {
            abort(422, 'Ticket não pode ser alterado.');
        }

        if ($user->role === 'user' && isset($data['status'])) {
            abort(403, 'Usuário não pode alterar status do ticket.');
        }

        if ($ticket->closed_at !== null && isset($data['status'])) {
            abort(422, 'Ticket já fechado não pode ter status alterado.');
        }

        return DB::transaction(function () use ($ticket, $data) {
            $updateData = [
                'title' => $data['title'] ?? $ticket->title,
                'description' => $data['description'] ?? $ticket->description,
                'status' => $data['status'] ?? $ticket->status,
                'last_interaction_at' => now(),
            ];

            if (($data['status'] ?? null) === 'closed') {
                $updateData['closed_at'] = now();
            }

            $ticket->update($updateData);

            return $ticket;
        });
    }
}
