<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function create(array $data, User $user): Project
    {
        if ($user->role !== 'support') {
            abort(403, 'Acesso negado.');
        }

        return DB::transaction(function () use ($data) {
            return Project::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    public function show(Project $project, User $user): Project
    {
        if ($user->role === 'user' && $user->project_id !== $project->id) {
            abort(403, 'Acesso negado.');
        }

        return $project;
    }

    public function update(Project $project, array $data, User $user): Project
    {
        if ($user->role !== 'support') {
            abort(403, 'Acesso negado.');
        }

        return DB::transaction(function () use ($project, $data) {
            $project->update([
                'name' => $data['name'] ?? $project->name,
                'description' => $data['description'] ?? null,
            ]);

            return $project;
        });
    }
}
