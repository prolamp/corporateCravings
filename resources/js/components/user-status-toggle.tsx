import { StatusToggle } from '@/components/status-toggle';

type Props = {
    userId: number;
    status: string;
    canToggle: boolean;
    className?: string;
};

export function UserStatusToggle({
    userId,
    status,
    canToggle,
    className,
}: Props) {
    return (
        <StatusToggle
            endpoint={`/admin/users/${userId}/status`}
            status={status}
            canToggle={canToggle}
            className={className}
            activeLabel="Deactivate user"
            inactiveLabel="Activate user"
        />
    );
}
