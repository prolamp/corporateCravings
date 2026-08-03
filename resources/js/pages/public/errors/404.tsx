import { Link, usePage } from '@inertiajs/react';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';

type Props = {
    status?: number;
};

export default function PublicNotFound({ status = 404 }: Props) {
    const { site } = usePage().props;
    const brand = site?.brand?.name ?? 'Corporate Cravings';

    return (
        <>
            <SeoHead title="Page not found" noindex />

            <section className="flex flex-1 flex-col items-center justify-center px-4 py-24 text-center md:py-32">
                <p className="font-display text-6xl font-bold tracking-tight text-primary md:text-7xl">
                    {status}
                </p>
                <p className="mt-4 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                    Page not found
                </p>
                <p className="mt-3 max-w-md text-muted-foreground">
                    That link does not exist on {brand}. Try the menu, packages,
                    or head back home.
                </p>
                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Button
                        asChild
                        size="lg"
                        className="min-h-12 bg-[#f6c60c] px-8 font-bold text-[#695300] hover:bg-[#ffe089]"
                    >
                        <Link href="/">Back to home</Link>
                    </Button>
                    <Button asChild size="lg" variant="outline" className="min-h-12">
                        <Link href="/menu">Browse menu</Link>
                    </Button>
                    <Button asChild size="lg" variant="outline" className="min-h-12">
                        <Link href="/contact">Contact</Link>
                    </Button>
                </div>
            </section>
        </>
    );
}
