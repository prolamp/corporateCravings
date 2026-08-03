import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    destroyPackage,
    PackageForm,
    type AdminPackage,
    type DishOption,
    type StatusOption,
} from '@/pages/admin/packages/package-form';

type Props = {
    package: AdminPackage;
    statusOptions: StatusOption[];
    dishOptions: DishOption[];
};

export default function PackagesEdit({
    package: pkg,
    statusOptions,
    dishOptions,
}: Props) {
    return (
        <>
            <Head title={`Edit ${pkg.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {pkg.title}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/packages/${pkg.id}`}>View</Link>
                        </Button>
                        <Button
                            variant="destructive"
                            type="button"
                            onClick={() => destroyPackage(pkg.id)}
                        >
                            Delete
                        </Button>
                    </div>
                </div>
                <div className="max-w-4xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <PackageForm
                        mode="edit"
                        action={`/admin/packages/${pkg.id}`}
                        method="put"
                        packageModel={pkg}
                        statusOptions={statusOptions}
                        dishOptions={dishOptions}
                        cancelHref={`/admin/packages/${pkg.id}`}
                    />
                </div>
            </div>
        </>
    );
}

PackagesEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Packages', href: '/admin/packages' },
        { title: 'Edit', href: '#' },
    ],
};
