import { router } from '@inertiajs/react';
import { cn } from '@/lib/utils';

type Props = {
    endpoint: string;
    status: string;
    canToggle: boolean;
    className?: string;
    activeLabel?: string;
    inactiveLabel?: string;
};

/** Compact active/inactive switch for admin action columns. */
export function StatusToggle({
    endpoint,
    status,
    canToggle,
    className,
    activeLabel = 'Deactivate',
    inactiveLabel = 'Activate',
}: Props) {
    const isActive = status === 'active';

    if (!canToggle) {
        return null;
    }

    function toggle() {
        router.patch(endpoint, {}, { preserveScroll: true });
    }

    return (
        <button
            type="button"
            onClick={toggle}
            className={cn(
                'inline-flex items-center rounded-full p-0.5 transition-opacity hover:opacity-80',
                className,
            )}
            title={isActive ? activeLabel : inactiveLabel}
            aria-pressed={isActive}
            aria-label={isActive ? activeLabel : inactiveLabel}
        >
            <span
                className={cn(
                    'relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors',
                    isActive ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-600',
                )}
            >
                <span
                    className={cn(
                        'inline-block size-4 rounded-full bg-white transition-transform',
                        isActive ? 'translate-x-4' : 'translate-x-0.5',
                    )}
                />
            </span>
        </button>
    );
}
