import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    CmsPageForm,
    destroyPage,
    type AdminPage,
    type StatusOption,
} from '@/pages/admin/pages/page-form';

type Props = {
    page: AdminPage;
    statusOptions: StatusOption[];
};

export default function PagesEdit({ page, statusOptions }: Props) {
    return (
        <>
            <Head title={`Edit ${page.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Content
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {page.title}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/pages/${page.id}`}>View</Link>
                        </Button>
                        <Button
                            variant="destructive"
                            type="button"
                            onClick={() => destroyPage(page.id)}
                        >
                            Delete
                        </Button>
                    </div>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <CmsPageForm
                        mode="edit"
                        action={`/admin/pages/${page.id}`}
                        method="put"
                        page={page}
                        statusOptions={statusOptions}
                        cancelHref={`/admin/pages/${page.id}`}
                    />
                </div>
            </div>
        </>
    );
}

PagesEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Pages', href: '/admin/pages' },
        { title: 'Edit', href: '#' },
    ],
};
