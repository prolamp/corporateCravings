import type { ImgHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

/**
 * Brand mark (favicon) for admin/auth chrome.
 */
export default function AppLogoIcon({
    className,
    alt = 'Corporate Cravings',
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src="/images/brand/favicon.svg"
            alt={alt}
            className={cn('object-contain', className)}
            {...props}
        />
    );
}
