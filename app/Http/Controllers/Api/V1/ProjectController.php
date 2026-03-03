<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreProjectRequest;
use App\Http\Requests\Api\V1\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Services\Api\V1\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = $this->projectService->list($request, $request->user());

        return $this->success(
            ProjectResource::collection($projects),
            'Projetos listados com sucesso.',
            200,
            [
                'pagination' => [
                    'total' => $projects->total(),
                    'per_page' => $projects->perPage(),
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                ],
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->create($request->validated(), $request->user());

        return $this->success(
            new ProjectResource($project),
            'Projeto criado com sucesso.',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Request $request)
    {
        $project = $this->projectService->show($project, $request->user());

        return $this->success(
            new ProjectResource($project),
            'Projeto recuperado com sucesso.',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project = $this->projectService->update($project, $request->validated(), $request->user());

        return $this->success(
            new ProjectResource($project),
            'Projeto atualizado com sucesso.',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}
