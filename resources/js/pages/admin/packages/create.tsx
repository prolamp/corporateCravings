import { Head } from '@inertiajs/react';
import {
    PackageForm,
    type DishOption,
    type StatusOption,
} from '@/pages/admin/packages/package-form';

type Props = {
    statusOptions: StatusOption[];
    dishOptions: DishOption[];
};

export default function PackagesCreate({ statusOptions, dishOptions }: Props) {
    return (
        <>
            <Head title="Create package" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Packages
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Create package
                    </h2>
                </div>
                <div className="max-w-4xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <PackageForm
                        mode="create"
                        action="/admin/packages"
                        statusOptions={statusOptions}
                        dishOptions={dishOptions}
                        cancelHref="/admin/packages"
                    />
                </div>
            </div>
        </>
    );
}

PackagesCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Packages', href: '/admin/packages' },
        { title: 'Create', href: '/admin/packages/create' },
    ],
};
