import { usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo({
    subtitle = 'Admin Portal',
}: {
    subtitle?: string;
}) {
    const { name } = usePage().props;

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-primary-container/40 shadow-sm">
                <AppLogoIcon className="size-7" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="truncate font-display text-base leading-tight font-semibold tracking-tight">
                    {name}
                </span>
                <span className="truncate text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                    {subtitle}
                </span>
            </div>
        </>
    );
}
