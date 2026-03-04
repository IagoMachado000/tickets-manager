<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\TicketMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class TicketMessageAttachmentService
{
    public function create(UploadedFile $file, TicketMessage $message)
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $attachment = $message->attachments()->create([
            'file_name' => $fileName,
            'file_path' => $file->storeAs("attachments/{$message->ticket->id}", $fileName, 'public'),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
        ]);

        return $attachment;
    }
}
