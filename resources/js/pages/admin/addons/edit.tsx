import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    AddonForm,
    destroyAddon,
    type AdminAddon,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/addons/addon-form';

type Props = {
    addon: AdminAddon;
    typeOptions: SelectOption[];
    statusOptions: StatusOption[];
    dishOptions: SelectOption[];
};

export default function AddonsEdit({
    addon,
    typeOptions,
    statusOptions,
    dishOptions,
}: Props) {
    return (
        <>
            <Head title={`Edit ${addon.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Catalog
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {addon.title}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/addons/${addon.id}`}>View</Link>
                        </Button>
                        <Button
                            variant="destructive"
                            type="button"
                            onClick={() => destroyAddon(addon.id)}
                        >
                            Delete
                        </Button>
                    </div>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <AddonForm
                        mode="edit"
                        action={`/admin/addons/${addon.id}`}
                        method="put"
                        addon={addon}
                        typeOptions={typeOptions}
                        statusOptions={statusOptions}
                        dishOptions={dishOptions}
                        cancelHref={`/admin/addons/${addon.id}`}
                    />
                </div>
            </div>
        </>
    );
}

AddonsEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Addons', href: '/admin/addons' },
        { title: 'Edit', href: '#' },
    ],
};
