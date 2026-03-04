<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\TicketResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Services\Api\V1\TicketService;
use Illuminate\Http\Request;

class TicketController extends BaseApiController
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Request $request)
    {
        $tickets = $this->ticketService->list($project, $request->user());

        return $this->success(
            [
                'tickets' => TicketResource::collection($tickets),
                'project' => new ProjectResource($project)
            ],
            'Tickets listados com sucesso.',
            200,
            [
                'pagination' => [
                    'total' => $tickets->total(),
                    'per_page' => $tickets->perPage(),
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                ],
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request, Project $project)
    {
        $ticket = $this->ticketService->create($request->validated(), $project, $request->user());

        return $this->success(
            new TicketResource($ticket),
            'Ticket criado com sucesso.',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket, Request $request)
    {
        $ticket = $this->ticketService->show($ticket, $request->user());

        return $this->success(
            new TicketResource($ticket),
            'Ticket recuperado com sucesso.',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
