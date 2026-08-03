import { Head } from '@inertiajs/react';
import {
    AddonForm,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/addons/addon-form';

type Props = {
    typeOptions: SelectOption[];
    statusOptions: StatusOption[];
    dishOptions: SelectOption[];
};

export default function AddonsCreate({
    typeOptions,
    statusOptions,
    dishOptions,
}: Props) {
    return (
        <>
            <Head title="Add addon" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Catalog
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add addon
                    </h2>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <AddonForm
                        mode="create"
                        action="/admin/addons"
                        typeOptions={typeOptions}
                        statusOptions={statusOptions}
                        dishOptions={dishOptions}
                        cancelHref="/admin/addons"
                    />
                </div>
            </div>
        </>
    );
}

AddonsCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Addons', href: '/admin/addons' },
        { title: 'Add', href: '/admin/addons/create' },
    ],
};
