import { Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { useState } from 'react';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { faqPageJsonLd } from '@/lib/seo-jsonld';
import { cn } from '@/lib/utils';

type FaqItem = {
    question: string;
    answer: string;
};

type Props = {
    title: string;
    meta_title: string | null;
    meta_description: string | null;
    items: FaqItem[];
};

export default function PublicFaq({
    title,
    meta_title,
    meta_description,
    items,
}: Props) {
    const { site } = usePage().props;
    const brand = site?.brand?.name ?? 'Corporate Cravings';
    const whatsappUrl = site?.whatsapp_url;
    const [openIndex, setOpenIndex] = useState<number | null>(0);

    return (
        <>
            <SeoHead
                title={meta_title || title}
                description={
                    meta_description ||
                    `Answers about delivery, packages, orders, and office lunch contracts from ${brand}.`
                }
                path="/faq"
                rawTitle={Boolean(meta_title)}
                jsonLd={faqPageJsonLd(items)}
            />

            <section className="border-b border-border/80 bg-[#FDFBF7] py-16 md:py-20 dark:bg-muted/20">
                <div className="mx-auto max-w-3xl px-4 text-center md:px-10">
                    <p className="font-display text-3xl font-bold tracking-tight text-primary md:text-4xl">
                        {brand}
                    </p>
                    <h1 className="mt-4 font-display text-3xl font-bold tracking-tight md:text-4xl">
                        {title}
                    </h1>
                    <p className="mx-auto mt-4 max-w-xl text-muted-foreground md:text-lg">
                        Answers about delivery, packages, orders, and office
                        lunch contracts.
                    </p>
                </div>
            </section>

            <section className="py-12 md:py-16">
                <div className="mx-auto max-w-3xl px-4 md:px-10">
                    {items.length === 0 ? (
                        <p className="rounded-lg border border-dashed border-border/80 px-4 py-10 text-center text-sm text-muted-foreground">
                            FAQ content is being prepared. Please{' '}
                            <Link
                                href="/contact"
                                className="font-medium text-primary underline-offset-4 hover:underline"
                            >
                                contact us
                            </Link>{' '}
                            with your question.
                        </p>
                    ) : (
                        <ul className="divide-y divide-border/80 border-y border-border/80">
                            {items.map((item, index) => {
                                const open = openIndex === index;

                                return (
                                    <li key={`${index}-${item.question}`}>
                                        <button
                                            type="button"
                                            className="flex w-full items-start justify-between gap-4 py-5 text-left"
                                            aria-expanded={open}
                                            onClick={() =>
                                                setOpenIndex(
                                                    open ? null : index,
                                                )
                                            }
                                        >
                                            <span className="font-display text-lg font-semibold tracking-tight md:text-xl">
                                                {item.question}
                                            </span>
                                            <ChevronDown
                                                className={cn(
                                                    'mt-1 size-5 shrink-0 text-muted-foreground transition-transform duration-200',
                                                    open && 'rotate-180',
                                                )}
                                            />
                                        </button>
                                        <div
                                            className={cn(
                                                'grid transition-[grid-template-rows] duration-200',
                                                open
                                                    ? 'grid-rows-[1fr]'
                                                    : 'grid-rows-[0fr]',
                                            )}
                                        >
                                            <div className="overflow-hidden">
                                                <p className="pb-5 text-sm leading-relaxed whitespace-pre-line text-muted-foreground md:text-base">
                                                    {item.answer}
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>
            </section>

            <section className="border-t border-border/80 bg-muted/30 py-14 md:py-16">
                <div className="mx-auto flex max-w-3xl flex-col items-center gap-4 px-4 text-center md:px-10">
                    <h2 className="font-display text-2xl font-bold tracking-tight md:text-3xl">
                        Still need help?
                    </h2>
                    <p className="text-muted-foreground">
                        Message us and we will point you to the right package,
                        zone, or schedule.
                    </p>
                    <div className="mt-2 flex flex-col gap-3 sm:flex-row">
                        <Button asChild size="lg" className="min-h-12">
                            <Link href="/contact">Contact us</Link>
                        </Button>
                        {whatsappUrl && (
                            <Button
                                asChild
                                size="lg"
                                className="min-h-12 border-0 bg-linear-to-br from-[#25D366] to-[#128C7E] font-bold text-white hover:opacity-90"
                            >
                                <a
                                    href={whatsappUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    WhatsApp
                                </a>
                            </Button>
                        )}
                    </div>
                </div>
            </section>
        </>
    );
}
