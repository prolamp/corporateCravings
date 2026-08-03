import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyAddon,
    type AdminAddon,
} from '@/pages/admin/addons/addon-form';

type Props = {
    addon: AdminAddon;
    canManage: boolean;
};

export default function AddonsShow({ addon, canManage }: Props) {
    return (
        <>
            <Head title={addon.title} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex items-start gap-4">
                        {addon.image_url ? (
                            <img
                                src={addon.image_url}
                                alt=""
                                className="size-20 rounded-xl object-cover"
                            />
                        ) : (
                            <div className="flex size-20 items-center justify-center rounded-xl bg-muted font-display text-2xl font-semibold text-muted-foreground">
                                {addon.title.slice(0, 1).toUpperCase()}
                            </div>
                        )}
                        <div>
                            <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Catalog
                            </p>
                            <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                                {addon.title}
                            </h2>
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    label={addon.status_label}
                                    badge={addon.status_badge}
                                />
                                <span className="rounded-md border px-2 py-0.5 text-xs font-medium">
                                    {addon.type_label}
                                </span>
                                <span className="text-sm font-medium">
                                    {addon.price_formatted}
                                </span>
                            </div>
                            {addon.short_description && (
                                <p className="mt-3 max-w-2xl text-sm text-muted-foreground">
                                    {addon.short_description}
                                </p>
                            )}
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/addons/${addon.id}/status`}
                            status={addon.status}
                            canToggle={canManage}
                            activeLabel="Deactivate addon"
                            inactiveLabel="Activate addon"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/addons">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link href={`/admin/addons/${addon.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyAddon(addon.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 md:p-6">
                    <Detail
                        label="Scope"
                        value={
                            addon.is_global
                                ? 'Global (all dishes)'
                                : 'Linked dishes only'
                        }
                    />
                    <Detail label="Type" value={addon.type_label} />
                    <Detail label="Sort order" value={String(addon.sort_order)} />
                </div>

                {!addon.is_global && (
                    <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                        <h3 className="font-display text-lg font-semibold tracking-tight">
                            Linked dishes
                        </h3>
                        {addon.dish_names.length === 0 ? (
                            <p className="mt-2 text-sm text-muted-foreground">
                                No dishes linked yet.
                            </p>
                        ) : (
                            <ul className="mt-3 list-inside list-disc text-sm">
                                {addon.dish_names.map((name) => (
                                    <li key={name}>{name}</li>
                                ))}
                            </ul>
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 text-sm font-medium">{value}</p>
        </div>
    );
}

AddonsShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Addons', href: '/admin/addons' },
        { title: 'Details', href: '#' },
    ],
};
