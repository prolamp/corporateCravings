import { Head } from '@inertiajs/react';
import {
    SubscriberForm,
    type StatusOption,
} from '@/pages/admin/subscribers/subscriber-form';

type Props = {
    statusOptions: StatusOption[];
};

export default function SubscribersCreate({ statusOptions }: Props) {
    return (
        <>
            <Head title="Add subscriber" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Packages
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add subscriber
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Company account only. Add a contract under Contracts
                        when ready.
                    </p>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <SubscriberForm
                        mode="create"
                        action="/admin/subscribers"
                        statusOptions={statusOptions}
                        cancelHref="/admin/subscribers"
                    />
                </div>
            </div>
        </>
    );
}

SubscribersCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Subscribers', href: '/admin/subscribers' },
        { title: 'Add', href: '/admin/subscribers/create' },
    ],
};
