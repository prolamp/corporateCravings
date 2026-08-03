import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';

type FlashProps = {
    flash?: {
        success?: boolean | null;
        message?: string | null;
        code?: string | null;
        error?: string | null;
    };
};

export function FlashToast() {
    const { flash } = usePage<FlashProps>().props;
    const [visible, setVisible] = useState(false);
    const message = flash?.message || flash?.error || null;
    const isError = Boolean(flash?.error) || flash?.success === false;

    useEffect(() => {
        if (!message) {
            setVisible(false);

            return;
        }

        setVisible(true);
        const timer = window.setTimeout(() => setVisible(false), 4000);

        return () => window.clearTimeout(timer);
    }, [message, flash?.code]);

    if (!visible || !message) {
        return null;
    }

    return (
        <div
            className={cn(
                'fixed right-4 bottom-4 z-50 max-w-sm rounded-lg border px-4 py-3 text-sm shadow-lg',
                isError
                    ? 'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100'
                    : 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100',
            )}
            role="status"
        >
            {message}
        </div>
    );
}

export default FlashToast;
