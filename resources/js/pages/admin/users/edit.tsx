import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    destroyUser,
    UserForm,
    type AdminUser,
    type RoleOption,
    type StatusOption,
} from '@/pages/admin/users/user-form';

type Props = {
    user: AdminUser;
    roleOptions: RoleOption[];
    statusOptions: StatusOption[];
    canDelete: boolean;
};

export default function UsersEdit({
    user,
    roleOptions,
    statusOptions,
    canDelete,
}: Props) {
    return (
        <>
            <Head title={`Edit ${user.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            People
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {user.name}
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Leave password blank to keep the current password.
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/users/${user.id}`}>View</Link>
                        </Button>
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

                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <UserForm
                        mode="edit"
                        action={`/admin/users/${user.id}`}
                        method="put"
                        user={user}
                        roleOptions={roleOptions}
                        statusOptions={statusOptions}
                        cancelHref={`/admin/users/${user.id}`}
                    />
                </div>
            </div>
        </>
    );
}

UsersEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: 'Edit', href: '#' },
    ],
};
