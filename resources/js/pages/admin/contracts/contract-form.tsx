import { Link, router, useForm } from '@inertiajs/react';
import { useMemo, type FormEvent, type ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };
export type SelectOption = { value: number | string; label: string };
export type PackageOption = {
    value: number;
    label: string;
    public_total: number;
    public_total_formatted: string;
};
export type DayOption = { value: string; label: string };

export type ContractHistoryRow = {
    id: number;
    package_title: string | null;
    package_amount_formatted: string;
    discount_formatted: string;
    agreed_total_formatted: string;
    persons: number;
    effective_from: string | null;
    effective_to: string | null;
};

export type AdminContract = {
    id: number;
    user_id: number;
    subscriber_name: string | null;
    company_name: string | null;
    package_id: number;
    package_title: string | null;
    package_public_total: number | null;
    package_public_total_formatted: string | null;
    meal_type: string;
    days_of_week: string[];
    persons: number;
    delivery_time: string | null;
    package_amount: number;
    package_amount_formatted: string;
    discount: number;
    discount_formatted: string;
    agreed_total: number;
    agreed_total_formatted: string;
    meal_days_per_week?: number;
    day_bill?: number;
    day_bill_formatted?: string;
    month_bill_estimate?: number;
    month_bill_estimate_formatted?: string;
    status: string;
    status_label: string;
    status_badge: string;
    starts_on: string | null;
    ends_on: string | null;
    notes: string | null;
    schedules_count: number;
    orders_count: number;
    can_delete: boolean;
    history: ContractHistoryRow[];
    created_at: string | null;
    updated_at: string | null;
};

type ContractFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    contract?: AdminContract | null;
    defaults?: { user_id?: number | null };
    statusOptions: StatusOption[];
    packageOptions: PackageOption[];
    subscriberOptions: SelectOption[];
    mealTypeOptions: SelectOption[];
    dayOptions: DayOption[];
    cancelHref: string;
};

function formatPkr(amount: number): string {
    return `PKR ${amount.toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export function ContractForm({
    mode,
    action,
    method = 'post',
    contract,
    defaults,
    statusOptions,
    packageOptions,
    subscriberOptions,
    mealTypeOptions,
    dayOptions,
    cancelHref,
}: ContractFormProps) {
    const form = useForm({
        user_id: String(
            contract?.user_id ?? defaults?.user_id ?? '',
        ),
        package_id: String(contract?.package_id ?? ''),
        meal_type: contract?.meal_type ?? 'lunch',
        days_of_week: (contract?.days_of_week?.length
            ? contract.days_of_week
            : ['mon', 'tue', 'wed', 'thu', 'fri']) as string[],
        persons: String(contract?.persons ?? 10),
        delivery_time: contract?.delivery_time ?? '13:00',
        package_amount: String(contract?.package_amount ?? ''),
        discount: String(contract?.discount ?? 0),
        status: contract?.status ?? 'active',
        starts_on: contract?.starts_on ?? '',
        ends_on: contract?.ends_on ?? '',
        notes: contract?.notes ?? '',
    });

    const selectedPackage = useMemo(
        () =>
            packageOptions.find(
                (option) => String(option.value) === form.data.package_id,
            ) ?? null,
        [form.data.package_id, packageOptions],
    );

    const packageAmount = selectedPackage
        ? selectedPackage.public_total
        : Number(form.data.package_amount || 0);
    const discount = Number(form.data.discount || 0);
    const agreedTotal = Math.max(0, packageAmount - discount);

    function onPackageChange(packageId: string) {
        const pkg = packageOptions.find(
            (option) => String(option.value) === packageId,
        );

        form.setData((current) => ({
            ...current,
            package_id: packageId,
            package_amount: pkg ? String(pkg.public_total) : '',
        }));
    }

    function toggleDay(day: string) {
        const current = form.data.days_of_week;
        if (current.includes(day)) {
            form.setData(
                'days_of_week',
                current.filter((value) => value !== day),
            );
        } else {
            form.setData('days_of_week', [...current, day]);
        }
    }

    function submit(event: FormEvent) {
        event.preventDefault();

        const rate = selectedPackage
            ? selectedPackage.public_total
            : Number(form.data.package_amount || 0);

        // Inertia v3: transform() returns void — do not chain.
        form.transform((data) => ({
            ...data,
            user_id: data.user_id ? Number(data.user_id) : null,
            package_id: data.package_id ? Number(data.package_id) : null,
            persons: data.persons ? Number(data.persons) : null,
            package_amount: rate,
            discount: data.discount === '' ? 0 : Number(data.discount),
            starts_on: data.starts_on || null,
            ends_on: data.ends_on || null,
            delivery_time: data.delivery_time || null,
            notes: data.notes || null,
        }));

        if (method === 'put') {
            form.put(action);
        } else {
            form.post(action);
        }
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            {form.hasErrors && (
                <div
                    className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100"
                    role="alert"
                >
                    Please fix the highlighted fields and try again.
                    <ul className="mt-2 list-inside list-disc space-y-1">
                        {Object.entries(form.errors).map(([field, message]) => (
                            <li key={field}>
                                <span className="font-medium">{field}</span>
                                {message ? `: ${message}` : ''}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
            <div className="grid gap-4 md:grid-cols-2">
                <Field
                    label="Subscriber"
                    error={form.errors.user_id}
                    htmlFor="user_id"
                >
                    <select
                        id="user_id"
                        className={selectClass}
                        value={form.data.user_id}
                        onChange={(e) =>
                            form.setData('user_id', e.target.value)
                        }
                        required
                        disabled={mode === 'edit'}
                    >
                        <option value="">Select subscriber…</option>
                        {subscriberOptions.map((option) => (
                            <option
                                key={option.value}
                                value={String(option.value)}
                            >
                                {option.label}
                            </option>
                        ))}
                    </select>
                </Field>

                <Field
                    label="Package"
                    error={form.errors.package_id}
                    htmlFor="package_id"
                >
                    <select
                        id="package_id"
                        className={selectClass}
                        value={form.data.package_id}
                        onChange={(e) => onPackageChange(e.target.value)}
                        required
                    >
                        <option value="">Select package…</option>
                        {packageOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label} · {option.public_total_formatted}
                                /pp
                            </option>
                        ))}
                    </select>
                    {selectedPackage && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            Public total:{' '}
                            {selectedPackage.public_total_formatted}/person
                            (starting point for package amount)
                        </p>
                    )}
                </Field>

                <Field
                    label="Meal type"
                    error={form.errors.meal_type}
                    htmlFor="meal_type"
                >
                    <select
                        id="meal_type"
                        className={selectClass}
                        value={form.data.meal_type}
                        onChange={(e) =>
                            form.setData('meal_type', e.target.value)
                        }
                    >
                        {mealTypeOptions.map((option) => (
                            <option
                                key={option.value}
                                value={String(option.value)}
                            >
                                {option.label}
                            </option>
                        ))}
                    </select>
                </Field>

                <Field
                    label="Persons / day"
                    error={form.errors.persons}
                    htmlFor="persons"
                >
                    <Input
                        id="persons"
                        type="number"
                        min={1}
                        value={form.data.persons}
                        onChange={(e) =>
                            form.setData('persons', e.target.value)
                        }
                        required
                    />
                </Field>

                <Field
                    label="Delivery time"
                    error={form.errors.delivery_time}
                    htmlFor="delivery_time"
                >
                    <Input
                        id="delivery_time"
                        type="time"
                        value={form.data.delivery_time}
                        onChange={(e) =>
                            form.setData('delivery_time', e.target.value)
                        }
                    />
                </Field>

                <Field
                    label="Status"
                    error={form.errors.status}
                    htmlFor="status"
                >
                    <select
                        id="status"
                        className={selectClass}
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
                </Field>
            </div>

            <div>
                <Label className="mb-2 block">Meal days</Label>
                <div className="flex flex-wrap gap-2">
                    {dayOptions.map((day) => {
                        const active = form.data.days_of_week.includes(
                            day.value,
                        );

                        return (
                            <button
                                key={day.value}
                                type="button"
                                onClick={() => toggleDay(day.value)}
                                className={cn(
                                    'rounded-md border px-3 py-1.5 text-sm font-medium transition-colors',
                                    active
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-border bg-background text-muted-foreground hover:bg-muted',
                                )}
                            >
                                {day.label}
                            </button>
                        );
                    })}
                </div>
                <InputError
                    message={form.errors.days_of_week}
                    className="mt-2"
                />
            </div>

            <div className="rounded-xl border border-border/80 bg-muted/30 p-4">
                <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                    Private pricing · staff only
                </p>
                <div className="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <Label>Package rate /pp</Label>
                        <p className="mt-2 font-display text-xl font-semibold tracking-tight">
                            {selectedPackage
                                ? formatPkr(selectedPackage.public_total)
                                : '—'}
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            From selected package (not edited here)
                        </p>
                        <InputError
                            message={form.errors.package_amount}
                            className="mt-2"
                        />
                    </div>
                    <Field
                        label="Discount /pp"
                        error={form.errors.discount}
                        htmlFor="discount"
                    >
                        <Input
                            id="discount"
                            type="number"
                            min={0}
                            step="0.01"
                            value={form.data.discount}
                            onChange={(e) =>
                                form.setData('discount', e.target.value)
                            }
                        />
                    </Field>
                    <div>
                        <Label>Contract rate /pp</Label>
                        <p className="mt-2 font-display text-xl font-semibold tracking-tight">
                            {formatPkr(agreedTotal)}
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            package rate − discount
                        </p>
                    </div>
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <Field
                    label="Starts on"
                    error={form.errors.starts_on}
                    htmlFor="starts_on"
                >
                    <Input
                        id="starts_on"
                        type="date"
                        value={form.data.starts_on}
                        onChange={(e) =>
                            form.setData('starts_on', e.target.value)
                        }
                    />
                </Field>
                <Field
                    label="Ends on"
                    error={form.errors.ends_on}
                    htmlFor="ends_on"
                >
                    <Input
                        id="ends_on"
                        type="date"
                        value={form.data.ends_on}
                        onChange={(e) =>
                            form.setData('ends_on', e.target.value)
                        }
                    />
                </Field>
            </div>

            <Field label="Notes" error={form.errors.notes} htmlFor="notes">
                <textarea
                    id="notes"
                    className={cn(
                        selectClass,
                        'min-h-24 py-2',
                    )}
                    value={form.data.notes}
                    onChange={(e) => form.setData('notes', e.target.value)}
                    placeholder="Gate instructions, dietary notes…"
                />
            </Field>

            <div className="flex flex-wrap gap-2">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create contract' : 'Save changes'}
                </Button>
                <Button variant="outline" asChild>
                    <Link href={cancelHref}>Cancel</Link>
                </Button>
            </div>
        </form>
    );
}

export function destroyContract(id: number): void {
    router.delete(`/admin/contracts/${id}`);
}

function Field({
    label,
    error,
    htmlFor,
    children,
}: {
    label: string;
    error?: string;
    htmlFor: string;
    children: ReactNode;
}) {
    return (
        <div>
            <Label htmlFor={htmlFor}>{label}</Label>
            <div className="mt-2">{children}</div>
            <InputError message={error} className="mt-2" />
        </div>
    );
}

const selectClass =
    'border-input flex h-9 w-full rounded-md border bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50';
