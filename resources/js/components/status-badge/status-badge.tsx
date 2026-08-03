import { cn } from '@/lib/utils';

type StatusBadgeProps = {
    label: string;
    badge?: string;
    color?: string;
    className?: string;
};

const badgeClasses: Record<string, string> = {
    default: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    success:
        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
    warning:
        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
    danger: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
    info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
};

export function StatusBadge({
    label,
    badge = 'default',
    className,
}: StatusBadgeProps) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium',
                badgeClasses[badge] ?? badgeClasses.default,
                className,
            )}
        >
            {label}
        </span>
    );
}

export default StatusBadge;
