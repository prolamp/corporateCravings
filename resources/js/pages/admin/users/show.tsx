import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { UserStatusToggle } from '@/components/user-status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyUser,
    type AdminUser,
} from '@/pages/admin/users/user-form';

type Props = {
    user: AdminUser;
    canUpdate: boolean;
    canDelete: boolean;
};

export default function UsersShow({ user, canUpdate, canDelete }: Props) {
    return (
        <>
            <Head title={user.name} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex items-start gap-4">
                        {user.image_url ? (
                            <img
                                src={user.image_url}
                                alt=""
                                className="size-16 rounded-xl object-cover"
                            />
                        ) : (
                            <div className="flex size-16 items-center justify-center rounded-xl bg-primary-container/40 font-display text-2xl font-semibold text-on-primary-container">
                                {user.name.slice(0, 1).toUpperCase()}
                            </div>
                        )}
                        <div>
                            <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                People
                            </p>
                            <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                                {user.name}
                            </h2>
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    label={user.status_label}
                                    badge={user.status_badge}
                                />
                                <span className="rounded-md border px-2 py-0.5 text-xs font-medium">
                                    {user.role_label ?? 'No role'}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <UserStatusToggle
                            userId={user.id}
                            status={user.status}
                            canToggle={canUpdate}
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/users">Back</Link>
                        </Button>
                        {canUpdate && (
                            <Button asChild>
                                <Link href={`/admin/users/${user.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canDelete && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyUser(user.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-2 md:p-6">
                    <Detail label="Phone" value={user.phone} />
                    <Detail label="Email" value={user.email} />
                    <Detail label="Company" value={user.company_name} />
                    <Detail label="Address" value={user.address} />
                </div>
            </div>
        </>
    );
}

function Detail({
    label,
    value,
}: {
    label: string;
    value: string | null | undefined;
}) {
    return (
        <div>
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 text-sm font-medium">{value || '—'}</p>
        </div>
    );
}

UsersShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: 'Details', href: '#' },
    ],
};
