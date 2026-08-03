import { Head } from '@inertiajs/react';
import {
    ContractForm,
    type AdminContract,
    type DayOption,
    type PackageOption,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/contracts/contract-form';

type Props = {
    contract: AdminContract;
    statusOptions: StatusOption[];
    packageOptions: PackageOption[];
    subscriberOptions: SelectOption[];
    mealTypeOptions: SelectOption[];
    dayOptions: DayOption[];
};

export default function ContractsEdit({
    contract,
    statusOptions,
    packageOptions,
    subscriberOptions,
    mealTypeOptions,
    dayOptions,
}: Props) {
    const title =
        contract.company_name || contract.subscriber_name || 'Contract';

    return (
        <>
            <Head title={`Edit · ${title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Packages
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Edit contract
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {title}
                        {contract.package_title
                            ? ` · ${contract.package_title}`
                            : ''}
                    </p>
                </div>

                <ContractForm
                    mode="edit"
                    action={`/admin/contracts/${contract.id}`}
                    method="put"
                    contract={contract}
                    statusOptions={statusOptions}
                    packageOptions={packageOptions}
                    subscriberOptions={subscriberOptions}
                    mealTypeOptions={mealTypeOptions}
                    dayOptions={dayOptions}
                    cancelHref={`/admin/contracts/${contract.id}`}
                />
            </div>
        </>
    );
}

ContractsEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Contracts', href: '/admin/contracts' },
        { title: 'Edit', href: '#' },
    ],
};
