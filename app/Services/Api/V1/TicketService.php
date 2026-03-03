<?php

namespace App\Services\Api\V1;

use App\Models\Project;
use App\Models\User;

class TicketService
{
    public function list(Project $project, User $user)
    {
        $query = $project->tickets()
            ->with('user:id,name,email,role,project_id');

        if ($user->role === 'user' && $user->project_id !== $project->id) {
            abort(403, 'Acesso negado.');
        }

        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        return $query->paginate(10);
    }
}
