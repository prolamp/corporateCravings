import { Head } from '@inertiajs/react';
import {
    ContractForm,
    type DayOption,
    type PackageOption,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/contracts/contract-form';

type Props = {
    statusOptions: StatusOption[];
    packageOptions: PackageOption[];
    subscriberOptions: SelectOption[];
    mealTypeOptions: SelectOption[];
    dayOptions: DayOption[];
    defaults: { user_id: number | null };
};

export default function ContractsCreate({
    statusOptions,
    packageOptions,
    subscriberOptions,
    mealTypeOptions,
    dayOptions,
    defaults,
}: Props) {
    return (
        <>
            <Head title="New contract" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Packages
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        New subscriber contract
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Start from the package public total, then set the
                        private discount.
                    </p>
                </div>

                <ContractForm
                    mode="create"
                    action="/admin/contracts"
                    defaults={defaults}
                    statusOptions={statusOptions}
                    packageOptions={packageOptions}
                    subscriberOptions={subscriberOptions}
                    mealTypeOptions={mealTypeOptions}
                    dayOptions={dayOptions}
                    cancelHref="/admin/contracts"
                />
            </div>
        </>
    );
}

ContractsCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Contracts', href: '/admin/contracts' },
        { title: 'Create', href: '#' },
    ],
};
