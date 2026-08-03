<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        string $code = 'ok',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'code' => $code,
            'data' => $data,
            'errors' => (object) [],
            'meta' => (object) $meta,
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     * @param  array<string, mixed>  $meta
     */
    public static function error(
        string $message,
        string $code = 'error',
        int $status = 400,
        array $errors = [],
        mixed $data = null,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => $data,
            'errors' => (object) $errors,
            'meta' => (object) $meta,
        ], $status);
    }

    /**
     * Standard flash payload for Inertia redirects.
     *
     * @return array{success: bool, message: string, code: string}
     */
    public static function flash(string $message, string $code = 'ok', bool $success = true): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'code' => $code,
        ];
    }
}
