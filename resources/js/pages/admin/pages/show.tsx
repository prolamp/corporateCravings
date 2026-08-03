import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import {
    destroyPage,
    togglePageStatus,
    type AdminPage,
} from '@/pages/admin/pages/page-form';

type Props = {
    page: AdminPage;
    canManage: boolean;
};

export default function PagesShow({ page, canManage }: Props) {
    return (
        <>
            <Head title={page.title} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Content
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            {page.title}
                        </h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={page.status_label}
                                badge={page.status_badge}
                            />
                            <span className="font-mono text-sm text-muted-foreground">
                                /{page.slug}
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/admin/pages">Back</Link>
                        </Button>
                        {canManage && (
                            <Button
                                variant="outline"
                                type="button"
                                onClick={() => togglePageStatus(page.id)}
                            >
                                {page.status === 'published'
                                    ? 'Unpublish'
                                    : 'Publish'}
                            </Button>
                        )}
                        {canManage && (
                            <Button asChild>
                                <Link href={`/admin/pages/${page.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyPage(page.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-2 md:p-6">
                    <Detail label="Meta title" value={page.meta_title || '—'} />
                    <Detail
                        label="Updated"
                        value={page.updated_at_formatted || '—'}
                    />
                    <div className="md:col-span-2">
                        <Detail
                            label="Meta description"
                            value={page.meta_description || '—'}
                        />
                    </div>
                </div>

                <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Body
                    </p>
                    {page.body ? (
                        <div
                            className="prose prose-sm dark:prose-invert mt-3 max-w-none"
                            dangerouslySetInnerHTML={{ __html: page.body }}
                        />
                    ) : (
                        <p className="mt-3 text-sm text-muted-foreground">
                            No content yet.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 text-sm font-medium whitespace-pre-wrap">
                {value}
            </p>
        </div>
    );
}

PagesShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Pages', href: '/admin/pages' },
        { title: 'Details', href: '#' },
    ],
};
