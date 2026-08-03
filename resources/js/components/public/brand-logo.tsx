import { cn } from '@/lib/utils';

const LOGO_SRC = '/images/brand/logo.svg';
const MARK_SRC = '/images/brand/favicon.svg';

type Props = {
    className?: string;
    /** Mark-only (favicon-style) vs full wordmark. */
    variant?: 'full' | 'mark';
    alt?: string;
};

export function BrandLogo({
    className,
    variant = 'full',
    alt = 'Corporate Cravings',
}: Props) {
    return (
        <img
            src={variant === 'mark' ? MARK_SRC : LOGO_SRC}
            alt={alt}
            className={cn(
                'block w-auto object-contain object-left',
                variant === 'mark' ? 'h-8' : 'h-8 md:h-10',
                className,
            )}
        />
    );
}
