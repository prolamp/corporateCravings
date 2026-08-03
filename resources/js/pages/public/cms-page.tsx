import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { SeoHead } from '@/components/public/seo-head';

type Props = {
    title: string;
    slug: string;
    body_html: string;
    meta_title: string | null;
    meta_description: string | null;
    updated_at_formatted: string | null;
};

export default function PublicCmsPage({
    title,
    slug,
    body_html,
    meta_title,
    meta_description,
    updated_at_formatted,
}: Props) {
    const { site } = usePage().props;
    const brand = site?.brand?.name ?? 'Corporate Cravings';

    return (
        <>
            <SeoHead
                title={meta_title || title}
                description={
                    meta_description || `${title} for ${brand}.`
                }
                path={`/${slug}`}
                rawTitle={Boolean(meta_title)}
            />

            <section className="border-b border-border/80 bg-[#FDFBF7] py-14 md:py-16 dark:bg-muted/20">
                <div className="mx-auto max-w-3xl px-4 md:px-10">
                    <p className="text-[11px] font-bold tracking-[0.12em] text-primary uppercase">
                        {brand}
                    </p>
                    <h1 className="mt-3 font-display text-3xl font-bold tracking-tight md:text-4xl">
                        {title}
                    </h1>
                    {updated_at_formatted && (
                        <p className="mt-3 text-sm text-muted-foreground">
                            Last updated {updated_at_formatted}
                        </p>
                    )}
                </div>
            </section>

            <section className="py-12 md:py-16">
                <div className="mx-auto max-w-3xl px-4 md:px-10">
                    {body_html ? (
                        <div
                            className="prose-cms space-y-4 text-sm leading-relaxed text-foreground md:text-base [&_a]:font-medium [&_a]:text-primary [&_a]:underline-offset-4 hover:[&_a]:underline [&_h2]:mt-8 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:tracking-tight [&_h3]:mt-6 [&_h3]:font-display [&_h3]:text-xl [&_h3]:font-semibold [&_li]:ms-5 [&_li]:list-disc [&_ol>li]:list-decimal [&_p]:text-muted-foreground [&_ul]:space-y-2"
                            dangerouslySetInnerHTML={{ __html: body_html }}
                        />
                    ) : (
                        <p className="text-muted-foreground">
                            This page does not have content yet.
                        </p>
                    )}

                    <div className="mt-12 flex flex-wrap gap-3 border-t border-border/80 pt-8">
                        <Button asChild variant="outline">
                            <Link href="/contact">Contact us</Link>
                        </Button>
                        <Button asChild variant="ghost">
                            <Link href="/faq">FAQ</Link>
                        </Button>
                    </div>
                </div>
            </section>
        </>
    );
}
