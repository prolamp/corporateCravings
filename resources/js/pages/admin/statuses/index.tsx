import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type StatusItem = {
    code: string;
    label: string;
    default_label: string;
    is_overridden: boolean;
    color: string;
    badge: string;
};

type StatusGroup = {
    group: string;
    group_label: string;
    items: StatusItem[];
};

type ErrorItem = {
    code: string;
    message: string;
    default_message: string;
    is_overridden: boolean;
};

type Props = {
    statuses: StatusGroup[];
    errors: ErrorItem[];
};

type Tab = 'statuses' | 'errors';

export default function StatusesIndex({ statuses, errors }: Props) {
    const [tab, setTab] = useState<Tab>('statuses');

    const statusForm = useForm({
        labels: Object.fromEntries(
            statuses.map((group) => [
                group.group,
                Object.fromEntries(
                    group.items.map((item) => [item.code, item.label]),
                ),
            ]),
        ) as Record<string, Record<string, string>>,
    });

    const errorForm = useForm({
        messages: Object.fromEntries(
            errors.map((item) => [item.code, item.message]),
        ) as Record<string, string>,
    });

    return (
        <>
            <Head title="Statuses & Errors" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        System
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                        Statuses &amp; errors
                    </h2>
                    <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                        Edit display labels and user-facing messages. Machine
                        codes stay fixed so integrations keep working.
                    </p>
                </div>

                <div className="flex gap-2 border-b border-border/60 pb-px">
                    <TabButton
                        active={tab === 'statuses'}
                        onClick={() => setTab('statuses')}
                    >
                        Status labels
                    </TabButton>
                    <TabButton
                        active={tab === 'errors'}
                        onClick={() => setTab('errors')}
                    >
                        Error messages
                    </TabButton>
                </div>

                {tab === 'statuses' ? (
                    <form
                        className="space-y-6"
                        onSubmit={(e) => {
                            e.preventDefault();
                            statusForm.put('/admin/statuses/labels', {
                                preserveScroll: true,
                            });
                        }}
                    >
                        {statuses.map((group) => (
                            <section
                                key={group.group}
                                className="overflow-hidden rounded-xl border border-border/80 bg-card"
                            >
                                <div className="border-b border-border/60 px-4 py-3">
                                    <h3 className="font-display text-base font-semibold">
                                        {group.group_label}
                                    </h3>
                                    <p className="text-xs text-muted-foreground">
                                        Group code:{' '}
                                        <code className="text-foreground">
                                            {group.group}
                                        </code>
                                    </p>
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[640px] text-left text-sm">
                                        <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                            <tr>
                                                <th className="px-4 py-2.5">
                                                    Code
                                                </th>
                                                <th className="px-4 py-2.5">
                                                    Display label
                                                </th>
                                                <th className="px-4 py-2.5">
                                                    Preview
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border/60">
                                            {group.items.map((item) => (
                                                <tr key={item.code}>
                                                    <td className="px-4 py-3 align-middle">
                                                        <code className="text-xs">
                                                            {item.code}
                                                        </code>
                                                        {item.is_overridden && (
                                                            <span className="ml-2 text-[10px] font-bold tracking-wide text-amber-700 uppercase dark:text-amber-300">
                                                                Custom
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 align-middle">
                                                        <Input
                                                            value={
                                                                statusForm.data
                                                                    .labels[
                                                                    group.group
                                                                ]?.[item.code] ??
                                                                ''
                                                            }
                                                            onChange={(e) =>
                                                                statusForm.setData(
                                                                    'labels',
                                                                    {
                                                                        ...statusForm
                                                                            .data
                                                                            .labels,
                                                                        [group.group]:
                                                                            {
                                                                                ...statusForm
                                                                                    .data
                                                                                    .labels[
                                                                                    group
                                                                                        .group
                                                                                ],
                                                                                [item.code]:
                                                                                    e
                                                                                        .target
                                                                                        .value,
                                                                            },
                                                                    },
                                                                )
                                                            }
                                                            placeholder={
                                                                item.default_label
                                                            }
                                                        />
                                                        <p className="mt-1 text-[11px] text-muted-foreground">
                                                            Default:{' '}
                                                            {item.default_label}
                                                        </p>
                                                    </td>
                                                    <td className="px-4 py-3 align-middle">
                                                        <StatusBadge
                                                            label={
                                                                statusForm.data
                                                                    .labels[
                                                                    group.group
                                                                ]?.[
                                                                    item.code
                                                                ] ||
                                                                item.default_label
                                                            }
                                                            badge={item.badge}
                                                        />
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        ))}

                        <div className="flex flex-wrap gap-3">
                            <Button
                                type="submit"
                                disabled={statusForm.processing}
                            >
                                Save status labels
                            </Button>
                            {statusForm.isDirty && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={() => statusForm.reset()}
                                >
                                    Reset form
                                </Button>
                            )}
                        </div>
                    </form>
                ) : (
                    <form
                        className="space-y-6"
                        onSubmit={(e) => {
                            e.preventDefault();
                            errorForm.put('/admin/statuses/errors', {
                                preserveScroll: true,
                            });
                        }}
                    >
                        <section className="overflow-hidden rounded-xl border border-border/80 bg-card">
                            <div className="border-b border-border/60 px-4 py-3">
                                <h3 className="font-display text-base font-semibold">
                                    User-facing messages
                                </h3>
                                <p className="text-xs text-muted-foreground">
                                    Shown in toasts and API responses — never
                                    raw exception names.
                                </p>
                            </div>
                            <div className="divide-y divide-border/60">
                                {errors.map((item) => (
                                    <div
                                        key={item.code}
                                        className="grid gap-2 px-4 py-3 md:grid-cols-[200px_1fr]"
                                    >
                                        <div>
                                            <Label className="font-mono text-xs">
                                                {item.code}
                                            </Label>
                                            {item.is_overridden && (
                                                <p className="text-[10px] font-bold tracking-wide text-amber-700 uppercase dark:text-amber-300">
                                                    Custom
                                                </p>
                                            )}
                                        </div>
                                        <div>
                                            <Input
                                                value={
                                                    errorForm.data.messages[
                                                        item.code
                                                    ] ?? ''
                                                }
                                                onChange={(e) =>
                                                    errorForm.setData(
                                                        'messages',
                                                        {
                                                            ...errorForm.data
                                                                .messages,
                                                            [item.code]:
                                                                e.target.value,
                                                        },
                                                    )
                                                }
                                                placeholder={
                                                    item.default_message
                                                }
                                            />
                                            <p className="mt-1 text-[11px] text-muted-foreground">
                                                Default: {item.default_message}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <div className="flex flex-wrap gap-3">
                            <Button
                                type="submit"
                                disabled={errorForm.processing}
                            >
                                Save error messages
                            </Button>
                            {errorForm.isDirty && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={() => errorForm.reset()}
                                >
                                    Reset form
                                </Button>
                            )}
                        </div>
                    </form>
                )}
            </div>
        </>
    );
}

function TabButton({
    active,
    onClick,
    children,
}: {
    active: boolean;
    onClick: () => void;
    children: React.ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                '-mb-px border-b-2 px-3 py-2 text-sm font-medium transition-colors',
                active
                    ? 'border-primary text-foreground'
                    : 'border-transparent text-muted-foreground hover:text-foreground',
            )}
        >
            {children}
        </button>
    );
}

StatusesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Statuses & Errors', href: '/admin/statuses' },
    ],
};
