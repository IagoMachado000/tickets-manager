<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'created_at' => $this->created_at?->toDateTimeString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'attachments' => TicketAttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
