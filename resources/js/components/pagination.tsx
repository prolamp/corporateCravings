import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type Props = {
    meta: PaginationMeta;
    /** Build a URL for a given page number (preserve filters). */
    pageUrl: (page: number) => string;
    className?: string;
};

function pageWindow(current: number, last: number): Array<number | 'ellipsis'> {
    if (last <= 7) {
        return Array.from({ length: last }, (_, i) => i + 1);
    }

    const pages = new Set<number>([1, last, current]);
    for (let i = current - 1; i <= current + 1; i++) {
        if (i >= 1 && i <= last) {
            pages.add(i);
        }
    }
    if (current <= 3) {
        pages.add(2);
        pages.add(3);
        pages.add(4);
    }
    if (current >= last - 2) {
        pages.add(last - 1);
        pages.add(last - 2);
        pages.add(last - 3);
    }

    const sorted = [...pages].sort((a, b) => a - b);
    const result: Array<number | 'ellipsis'> = [];
    let prev = 0;

    for (const page of sorted) {
        if (prev && page - prev > 1) {
            result.push('ellipsis');
        }
        result.push(page);
        prev = page;
    }

    return result;
}

export function Pagination({ meta, pageUrl, className }: Props) {
    const { current_page: current, last_page: last } = meta;

    if (last <= 1) {
        return (
            <div
                className={cn(
                    'flex items-center justify-between gap-3 border-t border-border/60 px-4 py-3 text-sm',
                    className,
                )}
            >
                <p className="text-muted-foreground">
                    {meta.from ?? 0}–{meta.to ?? 0} of {meta.total}
                </p>
            </div>
        );
    }

    const items = pageWindow(current, last);

    function go(page: number) {
        if (page < 1 || page > last || page === current) {
            return;
        }

        router.get(pageUrl(page), {}, { preserveState: true, replace: true });
    }

    return (
        <div
            className={cn(
                'flex flex-col gap-3 border-t border-border/60 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between',
                className,
            )}
        >
            <p className="text-muted-foreground">
                {meta.from ?? 0}–{meta.to ?? 0} of {meta.total}
            </p>
            <nav
                className="flex flex-wrap items-center gap-1"
                aria-label="Pagination"
            >
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={current <= 1}
                    onClick={() => go(current - 1)}
                >
                    Previous
                </Button>
                {items.map((item, index) =>
                    item === 'ellipsis' ? (
                        <span
                            key={`e-${index}`}
                            className="px-2 text-muted-foreground"
                            aria-hidden
                        >
                            …
                        </span>
                    ) : (
                        <Button
                            key={item}
                            type="button"
                            variant={item === current ? 'default' : 'outline'}
                            size="sm"
                            className="min-w-8"
                            aria-current={item === current ? 'page' : undefined}
                            onClick={() => go(item)}
                        >
                            {item}
                        </Button>
                    ),
                )}
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={current >= last}
                    onClick={() => go(current + 1)}
                >
                    Next
                </Button>
            </nav>
        </div>
    );
}
