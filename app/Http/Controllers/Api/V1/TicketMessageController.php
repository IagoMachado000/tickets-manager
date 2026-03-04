<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTicketMessageRequest;
use App\Models\Ticket;
use App\Services\Api\V1\TicketMessageService;

class TicketMessageController extends BaseApiController
{
    public function __construct(
        private TicketMessageService $ticketMessageService
    ) {}

    public function store(StoreTicketMessageRequest $request, Ticket $ticket)
    {
        $message = $this->ticketMessageService->create(
            $request->validated(),
            $ticket,
            $request->user()
        );

        return $this->success(
            $message,
            'Mensagem enviada com sucesso.',
            201
        );
    }
}
