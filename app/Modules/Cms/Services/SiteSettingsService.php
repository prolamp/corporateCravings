<?php

namespace App\Modules\Cms\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SiteSettingsService
{
    /**
     * Editable site settings (excludes status_labels / error_messages overrides).
     *
     * @return array<string, array{label: string, group: string, type: string, help?: string, default?: string}>
     */
    public function definitions(): array
    {
        return [
            'brand.name' => [
                'label' => 'Brand name',
                'group' => 'brand',
                'type' => 'string',
                'default' => 'Corporate Cravings',
            ],
            'brand.phone' => [
                'label' => 'Phone',
                'group' => 'brand',
                'type' => 'string',
                'help' => 'Shown in footer and contact.',
                'default' => '03041160070',
            ],
            'brand.whatsapp' => [
                'label' => 'WhatsApp number',
                'group' => 'brand',
                'type' => 'string',
                'help' => 'Digits only with country code, e.g. 923041160070 (used in wa.me links).',
                'default' => '923041160070',
            ],
            'brand.email' => [
                'label' => 'Email',
                'group' => 'brand',
                'type' => 'string',
                'default' => 'info@corporatecraving.com',
            ],
            'brand.address' => [
                'label' => 'Address',
                'group' => 'brand',
                'type' => 'string',
                'default' => 'I-9 Islamabad, Pakistan',
            ],
            'brand.hours' => [
                'label' => 'Business hours',
                'group' => 'brand',
                'type' => 'string',
                'help' => 'e.g. Mon–Sat 10:00–22:00',
                'default' => 'Mon–Sat 10:00–22:00',
            ],
            'analytics.ga_measurement_id' => [
                'label' => 'GA4 measurement ID',
                'group' => 'analytics',
                'type' => 'string',
                'help' => 'Optional. e.g. G-XXXXXXXXXX. Loaded on the public site only after cookie consent. .env GA_MEASUREMENT_ID overrides this when set.',
                'default' => '',
            ],
        ];
    }

    /**
     * Nested form values: brand.name → values.brand.name (avoids PHP dotted-key nesting bugs).
     *
     * @return array{
     *     groups: list<array{key: string, label: string, fields: list<array<string, mixed>>}>,
     *     values: array<string, array<string, string>>
     * }
     */
    public function formPayload(): array
    {
        $definitions = $this->definitions();
        $this->ensureDefaults($definitions);

        $stored = Setting::query()
            ->whereIn('key', array_keys($definitions))
            ->pluck('value', 'key');

        $values = [];
        $byGroup = [];

        foreach ($definitions as $key => $definition) {
            [$group, $field] = explode('.', $key, 2);
            $value = (string) ($stored[$key] ?? $definition['default'] ?? '');
            $values[$group][$field] = $value;
            $byGroup[$group][] = [
                'key' => $key,
                'group' => $group,
                'field' => $field,
                'label' => $definition['label'],
                'type' => $definition['type'],
                'help' => $definition['help'] ?? null,
                'value' => $value,
            ];
        }

        $groupLabels = [
            'brand' => 'Brand & contact',
            'analytics' => 'Analytics',
        ];

        $groups = [];
        foreach ($byGroup as $groupKey => $fields) {
            $groups[] = [
                'key' => $groupKey,
                'label' => $groupLabels[$groupKey] ?? ucfirst($groupKey),
                'fields' => $fields,
            ];
        }

        return [
            'groups' => $groups,
            'values' => $values,
        ];
    }

    /**
     * @param  array<string, array<string, string|null>>  $nested
     */
    public function update(array $nested): void
    {
        $definitions = $this->definitions();

        DB::transaction(function () use ($nested, $definitions): void {
            foreach ($definitions as $key => $definition) {
                [$group, $field] = explode('.', $key, 2);
                if (! array_key_exists($group, $nested) || ! array_key_exists($field, $nested[$group])) {
                    continue;
                }

                $raw = $nested[$group][$field];
                $value = is_string($raw) ? trim($raw) : '';

                Setting::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => $definition['group'],
                        'value' => $value,
                        'type' => $definition['type'],
                    ],
                );
            }
        });
    }

    /**
     * Brand/contact payload for public layout (header, footer, WhatsApp, analytics).
     *
     * @return array{
     *     brand: array{name: string, phone: string, whatsapp: string, email: string, address: string, hours: string},
     *     whatsapp_url: string|null,
     *     analytics: array{ga_measurement_id: string|null}
     * }
     */
    public function publicPayload(): array
    {
        $definitions = $this->definitions();
        $this->ensureDefaults($definitions);

        $stored = Setting::query()
            ->whereIn('group', ['brand', 'analytics'])
            ->pluck('value', 'key');

        $brand = [
            'name' => (string) ($stored['brand.name'] ?? $definitions['brand.name']['default'] ?? 'Corporate Cravings'),
            'phone' => (string) ($stored['brand.phone'] ?? $definitions['brand.phone']['default'] ?? ''),
            'whatsapp' => (string) ($stored['brand.whatsapp'] ?? $definitions['brand.whatsapp']['default'] ?? ''),
            'email' => (string) ($stored['brand.email'] ?? $definitions['brand.email']['default'] ?? ''),
            'address' => (string) ($stored['brand.address'] ?? $definitions['brand.address']['default'] ?? ''),
            'hours' => (string) ($stored['brand.hours'] ?? $definitions['brand.hours']['default'] ?? ''),
        ];

        $digits = preg_replace('/\D+/', '', $brand['whatsapp']) ?: null;

        return [
            'brand' => $brand,
            'whatsapp_url' => $digits ? 'https://wa.me/'.$digits : null,
            'analytics' => [
                'ga_measurement_id' => $this->resolveGaMeasurementId($stored, $definitions),
            ],
        ];
    }

    /**
     * Prefer .env GA_MEASUREMENT_ID, then admin site setting. Null when empty/invalid.
     *
     * @param  \Illuminate\Support\Collection<string, mixed>  $stored
     * @param  array<string, array{label: string, group: string, type: string, help?: string, default?: string}>  $definitions
     */
    private function resolveGaMeasurementId($stored, array $definitions): ?string
    {
        $fromEnv = trim((string) config('services.google_analytics.measurement_id', ''));
        $fromSettings = trim((string) ($stored['analytics.ga_measurement_id'] ?? $definitions['analytics.ga_measurement_id']['default'] ?? ''));
        $id = $fromEnv !== '' ? $fromEnv : $fromSettings;

        if ($id === '' || ! preg_match('/^G-[A-Z0-9]+$/i', $id)) {
            return null;
        }

        return strtoupper($id);
    }

    /**
     * @param  array<string, array{label: string, group: string, type: string, help?: string, default?: string}>  $definitions
     */
    private function ensureDefaults(array $definitions): void
    {
        foreach ($definitions as $key => $definition) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'group' => $definition['group'],
                    'value' => $definition['default'] ?? '',
                    'type' => $definition['type'],
                ],
            );
        }
    }
}
