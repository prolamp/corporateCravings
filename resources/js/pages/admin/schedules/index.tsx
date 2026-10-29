import { Head, router, useForm } from '@inertiajs/react';
import { useMemo, useState, type FormEvent } from 'react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type StatusOption = { value: string; label: string };
type SelectOption = { value: number; label: string };

export type ScheduleEntry = {
    id: number;
    subscribe_contract_id: number;
    company_name: string | null;
    package_title: string | null;
    dish_id: number | null;
    dish_title: string | null;
    date: string | null;
    date_formatted: string | null;
    day: string | null;
    day_full: string | null;
    persons: number;
    unit_price: number;
    unit_price_formatted: string;
    total: number;
    total_formatted: string;
    status: string;
    status_label: string;
    status_badge: string;
    payment_status: string | null;
    payment_status_label: string | null;
    payment_status_badge: string | null;
    notes: string | null;
    has_order: boolean;
    can_edit: boolean;
    can_deliver: boolean;
    can_delete: boolean;
};

type CalendarDay = {
    date: string;
    day: number;
    weekday: string;
    is_today: boolean;
    is_weekend: boolean;
    entries: ScheduleEntry[];
};

type Calendar = {
    year: number;
    month: number;
    month_label: string;
    start: string;
    end: string;
    days: CalendarDay[];
    entries: ScheduleEntry[];
    counts: {
        total: number;
        scheduled: number;
        confirmed: number;
        delivered: number;
    };
};

type Props = {
    calendar: Calendar;
    filters: {
        year: number;
        month: number;
        contract_id: number | null;
        status: string | null;
        search: string | null;
        view: string;
    };
    statusOptions: StatusOption[];
    contractOptions: SelectOption[];
    dishOptions: SelectOption[];
    canManage: boolean;
};

export default function SchedulesIndex({
    calendar,
    filters,
    statusOptions,
    contractOptions,
    dishOptions,
    canManage,
}: Props) {
    const [editing, setEditing] = useState<ScheduleEntry | null>(null);
    const [selectedDate, setSelectedDate] = useState<string | null>(
        calendar.days.find((d) => d.is_today)?.date ?? null,
    );

    const weekStartOffset = useMemo(() => {
        const first = calendar.days[0];
        if (!first) {
            return 0;
        }
        const map: Record<string, number> = {
            Sun: 0,
            Mon: 1,
            Tue: 2,
            Wed: 3,
            Thu: 4,
            Fri: 5,
            Sat: 6,
        };

        return map[first.weekday] ?? 0;
    }, [calendar.days]);

    const dayEntries = useMemo(() => {
        if (!selectedDate) {
            return calendar.entries;
        }

        return calendar.entries.filter((entry) => entry.date === selectedDate);
    }, [calendar.entries, selectedDate]);

    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/schedules',
            {
                year: next.year ?? filters.year,
                month: next.month ?? filters.month,
                contract_id: next.contract_id ?? filters.contract_id ?? '',
                status: next.status ?? filters.status ?? '',
                search: next.search ?? filters.search ?? '',
                view: next.view ?? filters.view ?? 'month',
            },
            { preserveState: true, replace: true },
        );
    }

    function shiftMonth(delta: number) {
        let year = filters.year;
        let month = filters.month + delta;
        if (month < 1) {
            month = 12;
            year -= 1;
        } else if (month > 12) {
            month = 1;
            year += 1;
        }
        applyFilters({ year, month });
    }

    function generate() {
        if (!filters.contract_id) {
            return;
        }
        router.post('/admin/schedules/generate', {
            contract_id: filters.contract_id,
            year: filters.year,
            month: filters.month,
        });
    }

    function shuffle() {
        if (!filters.contract_id) {
            return;
        }
        router.post('/admin/schedules/shuffle', {
            contract_id: filters.contract_id,
            year: filters.year,
            month: filters.month,
        });
    }

    function deliver(id: number) {
        router.patch(`/admin/schedules/${id}/deliver`);
    }

    function destroy(id: number) {
        router.delete(`/admin/schedules/${id}`);
    }

    return (
        <>
            <Head title="Schedules" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Subscriber schedules
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Generate the month, shuffle meals, edit days, and
                            mark delivered to bill.
                        </p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <Button
                            variant="outline"
                            type="button"
                            onClick={() => shiftMonth(-1)}
                        >
                            Prev
                        </Button>
                        <p className="min-w-36 text-center font-display text-lg font-semibold">
                            {calendar.month_label}
                        </p>
                        <Button
                            variant="outline"
                            type="button"
                            onClick={() => shiftMonth(1)}
                        >
                            Next
                        </Button>
                    </div>
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-5">
                    <select
                        className={selectClass}
                        value={filters.contract_id ?? ''}
                        onChange={(e) =>
                            applyFilters({
                                contract_id: e.target.value
                                    ? Number(e.target.value)
                                    : null,
                            })
                        }
                    >
                        <option value="">All contracts</option>
                        {contractOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <select
                        className={selectClass}
                        value={filters.status ?? ''}
                        onChange={(e) =>
                            applyFilters({
                                status: e.target.value || null,
                            })
                        }
                    >
                        <option value="">All statuses</option>
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <Input
                        key={`search-${filters.search ?? ''}-${filters.contract_id ?? ''}-${filters.status ?? ''}`}
                        placeholder="Search company or meal…"
                        defaultValue={filters.search ?? ''}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                applyFilters({
                                    search: (
                                        e.target as HTMLInputElement
                                    ).value,
                                });
                            }
                        }}
                    />
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            router.get(
                                '/admin/schedules',
                                {},
                                { replace: true },
                            )
                        }
                    >
                        Clear filters
                    </Button>
                    <div className="flex flex-wrap gap-2">
                        {canManage && (
                            <>
                                <Button
                                    type="button"
                                    disabled={!filters.contract_id}
                                    onClick={generate}
                                >
                                    Generate month
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    disabled={!filters.contract_id}
                                    onClick={shuffle}
                                >
                                    Shuffle
                                </Button>
                            </>
                        )}
                    </div>
                </div>

                {!filters.contract_id && canManage && (
                    <p className="text-sm text-muted-foreground">
                        Select a contract to generate or shuffle that month’s
                        meal days.
                    </p>
                )}

                <div className="grid gap-3 sm:grid-cols-4">
                    <Stat label="Days" value={String(calendar.counts.total)} />
                    <Stat
                        label="Scheduled"
                        value={String(calendar.counts.scheduled)}
                    />
                    <Stat
                        label="Confirmed"
                        value={String(calendar.counts.confirmed)}
                    />
                    <Stat
                        label="Delivered"
                        value={String(calendar.counts.delivered)}
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
                    <div className="rounded-xl border border-border/80 bg-card p-4">
                        <div className="mb-3 grid grid-cols-7 gap-1 text-center text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(
                                (label) => (
                                    <div key={label}>{label}</div>
                                ),
                            )}
                        </div>
                        <div className="grid grid-cols-7 gap-1">
                            {Array.from({ length: weekStartOffset }).map(
                                (_, index) => (
                                    <div
                                        key={`pad-${index}`}
                                        className="min-h-20 rounded-md bg-transparent"
                                    />
                                ),
                            )}
                            {calendar.days.map((day) => (
                                <button
                                    key={day.date}
                                    type="button"
                                    onClick={() => setSelectedDate(day.date)}
                                    className={cn(
                                        'min-h-20 rounded-md border p-1.5 text-left transition-colors',
                                        selectedDate === day.date
                                            ? 'border-primary bg-primary/5'
                                            : 'border-border/60 hover:bg-muted/40',
                                        day.is_today && 'ring-1 ring-primary/40',
                                    )}
                                >
                                    <div className="flex items-center justify-between">
                                        <span className="text-xs font-semibold">
                                            {day.day}
                                        </span>
                                        {day.entries.length > 0 && (
                                            <span className="text-[10px] text-muted-foreground">
                                                {day.entries.length}
                                            </span>
                                        )}
                                    </div>
                                    <div className="mt-1 space-y-0.5">
                                        {day.entries.slice(0, 2).map((entry) => (
                                            <div
                                                key={entry.id}
                                                className="space-y-0.5"
                                            >
                                                <p className="truncate text-[10px] text-muted-foreground">
                                                    {filters.contract_id
                                                        ? entry.dish_title ||
                                                          entry.company_name
                                                        : entry.company_name ||
                                                          entry.dish_title}
                                                </p>
                                                {filters.contract_id &&
                                                    entry.payment_status_label && (
                                                        <StatusBadge
                                                            label={
                                                                entry.payment_status_label
                                                            }
                                                            badge={
                                                                entry.payment_status_badge
                                                            }
                                                            className="h-4 px-1 text-[9px] leading-none"
                                                        />
                                                    )}
                                            </div>
                                        ))}
                                    </div>
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-xl border border-border/80 bg-card p-4">
                        <div className="flex items-center justify-between gap-2">
                            <div>
                                <h3 className="font-display text-lg font-semibold">
                                    {selectedDate
                                        ? `Day · ${selectedDate}`
                                        : 'All days this month'}
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    {dayEntries.length} entr
                                    {dayEntries.length === 1 ? 'y' : 'ies'}
                                </p>
                            </div>
                            {selectedDate && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    type="button"
                                    onClick={() => setSelectedDate(null)}
                                >
                                    Show all
                                </Button>
                            )}
                        </div>

                        <div className="mt-4 space-y-3">
                            {dayEntries.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    No schedule rows for this selection.
                                </p>
                            ) : (
                                dayEntries.map((entry) => (
                                    <div
                                        key={entry.id}
                                        className="rounded-lg border border-border/70 p-3"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-2">
                                            <div>
                                                <p className="font-medium">
                                                    {entry.company_name || '—'}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {entry.dish_title || 'No meal'}{' '}
                                                    · {entry.persons} persons
                                                </p>
                                                <p className="mt-1 text-sm font-medium">
                                                    {entry.total_formatted}
                                                </p>
                                            </div>
                                            <div className="flex flex-col items-end gap-1">
                                                <StatusBadge
                                                    label={entry.status_label}
                                                    badge={entry.status_badge}
                                                />
                                                {entry.payment_status_label &&
                                                    entry.payment_status_badge && (
                                                        <StatusBadge
                                                            label={
                                                                entry.payment_status_label
                                                            }
                                                            badge={
                                                                entry.payment_status_badge
                                                            }
                                                        />
                                                    )}
                                            </div>
                                        </div>
                                        {canManage && (
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                {entry.can_edit && (
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        type="button"
                                                        onClick={() =>
                                                            setEditing(entry)
                                                        }
                                                    >
                                                        Edit
                                                    </Button>
                                                )}
                                                {entry.can_deliver && (
                                                    <Button
                                                        size="sm"
                                                        type="button"
                                                        onClick={() =>
                                                            deliver(entry.id)
                                                        }
                                                    >
                                                        Deliver
                                                    </Button>
                                                )}
                                                {entry.can_delete && (
                                                    <Button
                                                        size="sm"
                                                        variant="destructive"
                                                        type="button"
                                                        onClick={() =>
                                                            destroy(entry.id)
                                                        }
                                                    >
                                                        Remove
                                                    </Button>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>

                {editing && (
                    <EditScheduleDialog
                        entry={editing}
                        statusOptions={statusOptions}
                        dishOptions={dishOptions}
                        onClose={() => setEditing(null)}
                    />
                )}
            </div>
        </>
    );
}

function EditScheduleDialog({
    entry,
    statusOptions,
    dishOptions,
    onClose,
}: {
    entry: ScheduleEntry;
    statusOptions: StatusOption[];
    dishOptions: SelectOption[];
    onClose: () => void;
}) {
    const form = useForm({
        dish_id: entry.dish_id ? String(entry.dish_id) : '',
        persons: String(entry.persons),
        unit_price: String(entry.unit_price ?? 0),
        status: entry.status,
        notes: entry.notes ?? '',
    });

    const previewTotal = useMemo(() => {
        const persons = Number(form.data.persons);
        const unit = Number(form.data.unit_price);
        if (!Number.isFinite(persons) || !Number.isFinite(unit)) {
            return null;
        }

        return persons * unit;
    }, [form.data.persons, form.data.unit_price]);

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            dish_id: data.dish_id ? Number(data.dish_id) : null,
            persons: Number(data.persons),
            unit_price: Number(data.unit_price),
            notes: data.notes || null,
        }));
        form.put(`/admin/schedules/${entry.id}`, {
            onSuccess: () => onClose(),
        });
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-xl border border-border bg-card p-5 shadow-xl"
            >
                <div>
                    <h3 className="font-display text-lg font-semibold">
                        Edit day
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        {entry.date_formatted} · {entry.company_name}
                    </p>
                </div>

                <div>
                    <Label htmlFor="dish_id">Meal</Label>
                    <select
                        id="dish_id"
                        className={cn(selectClass, 'mt-2')}
                        value={form.data.dish_id}
                        onChange={(e) =>
                            form.setData('dish_id', e.target.value)
                        }
                    >
                        <option value="">Keep / select dish…</option>
                        {dishOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                        {entry.dish_id &&
                            !dishOptions.some(
                                (o) => o.value === entry.dish_id,
                            ) && (
                                <option value={entry.dish_id}>
                                    {entry.dish_title}
                                </option>
                            )}
                    </select>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <Label htmlFor="persons">Persons</Label>
                        <Input
                            id="persons"
                            className="mt-2"
                            type="number"
                            min={1}
                            value={form.data.persons}
                            onChange={(e) =>
                                form.setData('persons', e.target.value)
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="unit_price">Per head (PKR)</Label>
                        <Input
                            id="unit_price"
                            className="mt-2"
                            type="number"
                            min={0}
                            step="0.01"
                            value={form.data.unit_price}
                            onChange={(e) =>
                                form.setData('unit_price', e.target.value)
                            }
                        />
                        <p className="mt-1 text-xs text-muted-foreground">
                            Defaults to contract rate. Change for special
                            deliveries.
                        </p>
                    </div>
                </div>

                {previewTotal !== null && (
                    <p className="text-sm text-muted-foreground">
                        Day total:{' '}
                        <span className="font-medium text-foreground">
                            PKR{' '}
                            {previewTotal.toLocaleString('en-PK', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            })}
                        </span>
                    </p>
                )}

                <div>
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        className={cn(selectClass, 'mt-2')}
                        value={form.data.status}
                        onChange={(e) =>
                            form.setData('status', e.target.value)
                        }
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <Label htmlFor="notes">Notes</Label>
                    <textarea
                        id="notes"
                        className={cn(selectClass, 'mt-2 min-h-20 py-2')}
                        value={form.data.notes}
                        onChange={(e) =>
                            form.setData('notes', e.target.value)
                        }
                    />
                </div>

                <div className="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onClose}
                    >
                        Cancel
                    </Button>
                    <Button type="submit" disabled={form.processing}>
                        Save
                    </Button>
                </div>
            </form>
        </div>
    );
}

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border border-border/80 bg-card p-4">
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 font-display text-2xl font-semibold">{value}</p>
        </div>
    );
}

const selectClass =
    'border-input flex h-9 w-full rounded-md border bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50';

SchedulesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Schedules', href: '/admin/schedules' },
    ],
};
