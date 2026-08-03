<?php

namespace App\Modules\System\Services;

use App\Models\Setting;
use App\Support\ErrorCatalog;
use App\Support\StatusRegistry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StatusErrorCatalogService
{
    public const STATUS_GROUP = 'status_labels';

    public const ERROR_GROUP = 'error_messages';

    /**
     * @return array{
     *     statuses: list<array{group: string, group_label: string, items: list<array<string, mixed>>}>,
     *     errors: list<array<string, mixed>>,
     * }
     */
    public function catalog(): array
    {
        $statusOverrides = $this->statusOverrides();
        $errorOverrides = $this->errorOverrides();

        $statuses = [];
        foreach (StatusRegistry::groups() as $group) {
            $items = [];
            foreach (StatusRegistry::codes($group) as $code) {
                $base = config("statuses.{$group}.{$code}", []);
                $defaultLabel = (string) ($base['label'] ?? 'Unknown');
                $overrideKey = "{$group}.{$code}";
                $override = $statusOverrides[$overrideKey] ?? null;

                $resolved = StatusRegistry::get($group, $code);

                $items[] = [
                    'code' => $code,
                    'label' => $resolved['label'],
                    'default_label' => $defaultLabel,
                    'is_overridden' => $override !== null && $override !== $defaultLabel,
                    'color' => $resolved['color'],
                    'badge' => $resolved['badge'],
                ];
            }

            $statuses[] = [
                'group' => $group,
                'group_label' => StatusRegistry::groupLabel($group),
                'items' => $items,
            ];
        }

        $errors = [];
        foreach (ErrorCatalog::codes() as $code) {
            $default = (string) config("errors.{$code}");
            $override = $errorOverrides[$code] ?? null;

            $errors[] = [
                'code' => $code,
                'message' => ErrorCatalog::message($code),
                'default_message' => $default,
                'is_overridden' => $override !== null && $override !== $default,
            ];
        }

        return [
            'statuses' => $statuses,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, array<string, string>>  $labels  group => [code => label]
     */
    public function updateStatusLabels(array $labels): void
    {
        DB::transaction(function () use ($labels): void {
            foreach ($labels as $group => $items) {
                if (! in_array($group, StatusRegistry::groups(), true)) {
                    throw new InvalidArgumentException("Unknown status group [{$group}].");
                }

                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $code => $label) {
                    if (! in_array((string) $code, StatusRegistry::codes($group), true)) {
                        throw new InvalidArgumentException("Unknown status code [{$group}.{$code}].");
                    }

                    $this->persistStatusLabel($group, (string) $code, trim((string) $label));
                }
            }
        });

        StatusRegistry::flushOverrides();
    }

    /**
     * @param  array<string, string>  $messages  code => message
     */
    public function updateErrorMessages(array $messages): void
    {
        DB::transaction(function () use ($messages): void {
            foreach ($messages as $code => $message) {
                if (! in_array((string) $code, ErrorCatalog::codes(), true)) {
                    throw new InvalidArgumentException("Unknown error code [{$code}].");
                }

                $this->persistErrorMessage((string) $code, trim((string) $message));
            }
        });

        ErrorCatalog::flushOverrides();
    }

    protected function persistStatusLabel(string $group, string $code, string $label): void
    {
        $key = "{$group}.{$code}";
        $default = (string) (config("statuses.{$group}.{$code}.label") ?? 'Unknown');

        if ($label === '' || $label === $default) {
            Setting::query()
                ->where('group', self::STATUS_GROUP)
                ->where('key', $key)
                ->delete();

            return;
        }

        Setting::query()->updateOrCreate(
            ['group' => self::STATUS_GROUP, 'key' => $key],
            ['value' => $label, 'type' => 'string'],
        );
    }

    protected function persistErrorMessage(string $code, string $message): void
    {
        $default = (string) config("errors.{$code}");

        if ($message === '' || $message === $default) {
            Setting::query()
                ->where('group', self::ERROR_GROUP)
                ->where('key', $code)
                ->delete();

            return;
        }

        Setting::query()->updateOrCreate(
            ['group' => self::ERROR_GROUP, 'key' => $code],
            ['value' => $message, 'type' => 'string'],
        );
    }

    /**
     * @return array<string, string>
     */
    protected function statusOverrides(): array
    {
        return Setting::query()
            ->where('group', self::STATUS_GROUP)
            ->pluck('value', 'key')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function errorOverrides(): array
    {
        return Setting::query()
            ->where('group', self::ERROR_GROUP)
            ->pluck('value', 'key')
            ->all();
    }
}
