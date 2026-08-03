import { Head } from '@inertiajs/react';
import {
    UserForm,
    type RoleOption,
    type StatusOption,
} from '@/pages/admin/users/user-form';

type Props = {
    roleOptions: RoleOption[];
    statusOptions: StatusOption[];
};

export default function UsersCreate({ roleOptions, statusOptions }: Props) {
    return (
        <>
            <Head title="Add user" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        People
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add user
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Only roles you are allowed to create are listed.
                    </p>
                </div>

                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <UserForm
                        mode="create"
                        action="/admin/users"
                        roleOptions={roleOptions}
                        statusOptions={statusOptions}
                        cancelHref="/admin/users"
                    />
                </div>
            </div>
        </>
    );
}

UsersCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: 'Add', href: '/admin/users/create' },
    ],
};
