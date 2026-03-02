<?php

declare(strict_types=1);

namespace App\Traits\Api\V1;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        ?array $meta = null
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if ($meta) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    protected function error(
        string $message,
        int $status = 400,
        mixed $data = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }
}
