<?php

namespace App\Services\Api\V1;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectService
{
    public function list(Request $request, User $user)
    {
        $query = Project::query();

        if ($user->role === 'user') {
            $query->where('id', $user->project_id);
        }

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        return $query->paginate(10);
    }

    public function show(Project $project, User $user)
    {
        if ($user->role === 'user' && $user->project_id !== $project->id) {
            abort(403, 'Acesso negado.');
        }

        return $project;
    }
}
