<?php

namespace App\Modules\Delivery\Services;

use App\Modules\Delivery\Models\DeliveryZone;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliveryZoneService
{
    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, DeliveryZone>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DeliveryZone::query()
            ->withCount('orders')
            ->orderBy('sort_order')
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     fee: float|int|string,
     *     is_active: bool,
     *     sort_order?: int|null,
     * }  $data
     */
    public function create(array $data): DeliveryZone
    {
        return DB::transaction(function () use ($data): DeliveryZone {
            $zone = DeliveryZone::query()->create([
                'name' => $data['name'],
                'fee' => Money::of($data['fee']),
                'is_active' => (bool) $data['is_active'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            return $zone->loadCount('orders');
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     fee: float|int|string,
     *     is_active: bool,
     *     sort_order?: int|null,
     * }  $data
     */
    public function update(DeliveryZone $zone, array $data): DeliveryZone
    {
        return DB::transaction(function () use ($zone, $data): DeliveryZone {
            $zone->fill([
                'name' => $data['name'],
                'fee' => Money::of($data['fee']),
                'is_active' => (bool) $data['is_active'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ])->save();

            return $zone->loadCount('orders');
        });
    }

    public function delete(DeliveryZone $zone): void
    {
        if ($zone->orders()->exists()) {
            throw new InvalidArgumentException(
                'This zone is used on existing orders. Deactivate it instead of deleting.',
            );
        }

        $zone->delete();
    }

    public function toggleActive(DeliveryZone $zone): DeliveryZone
    {
        $zone->is_active = ! $zone->is_active;
        $zone->save();

        return $zone->loadCount('orders');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(DeliveryZone $zone): array
    {
        $statusCode = $zone->is_active ? 'active' : 'inactive';
        $status = StatusRegistry::get('generic', $statusCode);

        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'fee' => (float) $zone->fee,
            'fee_formatted' => Money::format((float) $zone->fee),
            'is_active' => (bool) $zone->is_active,
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'sort_order' => (int) $zone->sort_order,
            'orders_count' => (int) ($zone->orders_count ?? $zone->orders()->count()),
            'created_at' => $zone->created_at?->toIso8601String(),
            'updated_at' => $zone->updated_at?->toIso8601String(),
        ];
    }
}
