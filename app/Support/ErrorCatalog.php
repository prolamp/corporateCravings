<?php

namespace App\Support;

use App\Models\Setting;
use App\Modules\System\Services\StatusErrorCatalogService;
use Illuminate\Http\JsonResponse;

class ErrorCatalog
{
    /** @var array<string, string>|null */
    protected static ?array $messageOverrides = null;

    public static function message(string $code, ?string $fallback = null): string
    {
        return self::messageOverrides()[$code]
            ?? config("errors.{$code}")
            ?? $fallback
            ?? config('errors.error', 'Something went wrong. Please try again.');
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_map(strval(...), array_keys(config('errors', [])));
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function response(
        string $code,
        int $status = 400,
        array $errors = [],
        mixed $data = null,
    ): JsonResponse {
        return ApiResponse::error(
            message: self::message($code),
            code: $code,
            status: $status,
            errors: $errors,
            data: $data,
        );
    }

    public static function flushOverrides(): void
    {
        self::$messageOverrides = null;
    }

    /**
     * @return array<string, string>
     */
    protected static function messageOverrides(): array
    {
        if (self::$messageOverrides !== null) {
            return self::$messageOverrides;
        }

        try {
            self::$messageOverrides = Setting::query()
                ->where('group', StatusErrorCatalogService::ERROR_GROUP)
                ->pluck('value', 'key')
                ->all();
        } catch (\Throwable) {
            self::$messageOverrides = [];
        }

        return self::$messageOverrides;
    }
}
