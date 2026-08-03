import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    destroySubscriber,
    SubscriberForm,
    type AdminSubscriber,
    type StatusOption,
} from '@/pages/admin/subscribers/subscriber-form';

type Props = {
    subscriber: AdminSubscriber;
    statusOptions: StatusOption[];
};

export default function SubscribersEdit({
    subscriber,
    statusOptions,
}: Props) {
    return (
        <>
            <Head title={`Edit ${subscriber.company_name || subscriber.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {subscriber.company_name || subscriber.name}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/subscribers/${subscriber.id}`}>
                                View
                            </Link>
                        </Button>
                        {subscriber.can_delete && !subscriber.contract && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() =>
                                    destroySubscriber(subscriber.id)
                                }
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <SubscriberForm
                        mode="edit"
                        action={`/admin/subscribers/${subscriber.id}`}
                        method="put"
                        subscriber={subscriber}
                        statusOptions={statusOptions}
                        cancelHref={`/admin/subscribers/${subscriber.id}`}
                    />
                </div>
            </div>
        </>
    );
}

SubscribersEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Subscribers', href: '/admin/subscribers' },
        { title: 'Edit', href: '#' },
    ],
};
