<?php

namespace App\Support;

use App\Models\Setting;
use App\Modules\System\Services\StatusErrorCatalogService;

class StatusRegistry
{
    /** @var array<string, string>|null */
    protected static ?array $labelOverrides = null;

    /**
     * @return array{label: string, color: string, badge: string, code: string, group: string}
     */
    public static function get(string $group, string $code): array
    {
        $statuses = config("statuses.{$group}", []);
        $entry = $statuses[$code] ?? null;

        if (! is_array($entry) || ! isset($entry['label'])) {
            $entry = config('statuses.unknown', [
                'label' => 'Unknown',
                'color' => 'zinc',
                'badge' => 'default',
            ]);
        }

        $overrideKey = "{$group}.{$code}";
        $label = self::labelOverrides()[$overrideKey]
            ?? $entry['label']
            ?? 'Unknown';

        return [
            'code' => $code,
            'group' => $group,
            'label' => $label,
            'color' => $entry['color'] ?? 'zinc',
            'badge' => $entry['badge'] ?? 'default',
        ];
    }

    /**
     * @return list<array{label: string, color: string, badge: string, code: string, group: string}>
     */
    public static function all(string $group): array
    {
        return array_map(
            fn (string $code): array => self::get($group, $code),
            self::codes($group),
        );
    }

    public static function label(string $group, string $code): string
    {
        return self::get($group, $code)['label'];
    }

    /**
     * @return list<string>
     */
    public static function codes(string $group): array
    {
        $statuses = config("statuses.{$group}", []);

        if (! is_array($statuses) || $group === 'unknown') {
            return [];
        }

        return array_map(strval(...), array_keys($statuses));
    }

    /**
     * @return list<string>
     */
    public static function groups(): array
    {
        return array_values(array_filter(
            array_keys(config('statuses', [])),
            fn (string|int $group): bool => $group !== 'unknown' && is_string($group),
        ));
    }

    public static function groupLabel(string $group): string
    {
        return match ($group) {
            'order' => 'Orders',
            'schedule' => 'Schedules',
            'bill_slip' => 'Bill slips',
            'payment' => 'Payment',
            'user' => 'Users',
            'generic' => 'Generic',
            'expense' => 'Expenses',
            'page' => 'CMS Pages',
            default => ucfirst(str_replace('_', ' ', $group)),
        };
    }

    public static function flushOverrides(): void
    {
        self::$labelOverrides = null;
    }

    /**
     * @return array<string, string>
     */
    protected static function labelOverrides(): array
    {
        if (self::$labelOverrides !== null) {
            return self::$labelOverrides;
        }

        try {
            self::$labelOverrides = Setting::query()
                ->where('group', StatusErrorCatalogService::STATUS_GROUP)
                ->pluck('value', 'key')
                ->all();
        } catch (\Throwable) {
            self::$labelOverrides = [];
        }

        return self::$labelOverrides;
    }
}
