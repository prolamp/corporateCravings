import { Head } from '@inertiajs/react';
import {
    CmsPageForm,
    type StatusOption,
} from '@/pages/admin/pages/page-form';

type Props = {
    statusOptions: StatusOption[];
};

export default function PagesCreate({ statusOptions }: Props) {
    return (
        <>
            <Head title="Add page" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Content
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add page
                    </h2>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <CmsPageForm
                        mode="create"
                        action="/admin/pages"
                        statusOptions={statusOptions}
                        cancelHref="/admin/pages"
                    />
                </div>
            </div>
        </>
    );
}

PagesCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Pages', href: '/admin/pages' },
        { title: 'Add', href: '/admin/pages/create' },
    ],
};
