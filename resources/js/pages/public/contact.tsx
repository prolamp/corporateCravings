import { Link, useForm, usePage } from '@inertiajs/react';
import { Clock, Mail, MapPin, Phone } from 'lucide-react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type SubjectOption = { value: string; label: string };

type Props = {
    hero_image_url: string;
    subjects: SubjectOption[];
};

export default function PublicContact({ hero_image_url, subjects }: Props) {
    const { site, flash } = usePage().props;
    const brand = site?.brand;
    const name = brand?.name ?? 'Corporate Cravings';
    const whatsappUrl = site?.whatsapp_url;
    const sent = Boolean(flash?.success && flash?.code === 'contact_sent');

    const form = useForm({
        name: '',
        email: '',
        phone: '',
        subject: subjects[0]?.value ?? 'General inquiry',
        message: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post('/contact', {
            preserveScroll: true,
            onSuccess: () => form.reset('message'),
        });
    }

    return (
        <>
            <SeoHead
                title="Contact"
                description={`Contact ${name} in Islamabad for office lunches, packages, and bulk catering.`}
                image={hero_image_url}
                path="/contact"
            />

            <section className="relative flex min-h-[min(520px,70svh)] items-end overflow-hidden md:items-center">
                <div className="absolute inset-0">
                    <img
                        src={hero_image_url}
                        alt=""
                        className="size-full object-cover"
                    />
                    <div className="absolute inset-0 bg-linear-to-r from-black/85 via-black/55 to-black/25" />
                </div>
                <div className="relative z-10 mx-auto w-full max-w-7xl px-4 py-16 md:px-10 md:py-24">
                    <div className="max-w-2xl animate-in fade-in slide-in-from-bottom-4 duration-700">
                        <p className="font-display text-4xl font-bold tracking-tight text-[#f6c60c] sm:text-5xl">
                            {name}
                        </p>
                        <h1 className="mt-4 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            Contact us
                        </h1>
                        <p className="mt-4 max-w-xl text-base leading-relaxed text-white/90 sm:text-lg">
                            Questions about daily lunches, packages, or bulk
                            orders? Send a note or reach us on WhatsApp.
                        </p>
                    </div>
                </div>
            </section>

            <section className="bg-[#FDFBF7] py-16 md:py-24 dark:bg-muted/20">
                <div className="mx-auto grid max-w-7xl gap-12 px-4 md:px-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
                    <div>
                        <p className="text-[11px] font-bold tracking-[0.12em] text-primary uppercase">
                            Reach us
                        </p>
                        <h2 className="mt-3 font-display text-3xl font-bold tracking-tight">
                            Islamabad kitchen & support
                        </h2>
                        <p className="mt-4 text-muted-foreground">
                            We reply during kitchen hours. For urgent delivery
                            changes, WhatsApp is fastest.
                        </p>

                        <ul className="mt-10 space-y-5 text-sm md:text-base">
                            {brand?.address && (
                                <li className="flex items-start gap-3">
                                    <MapPin className="mt-0.5 size-4 shrink-0 text-primary" />
                                    <span>{brand.address}</span>
                                </li>
                            )}
                            {brand?.phone && (
                                <li className="flex items-center gap-3">
                                    <Phone className="size-4 shrink-0 text-primary" />
                                    <a
                                        href={`tel:${brand.phone.replace(/\s+/g, '')}`}
                                        className="font-medium hover:underline"
                                    >
                                        {brand.phone}
                                    </a>
                                </li>
                            )}
                            {brand?.email && (
                                <li className="flex items-center gap-3">
                                    <Mail className="size-4 shrink-0 text-primary" />
                                    <a
                                        href={`mailto:${brand.email}`}
                                        className="font-medium hover:underline"
                                    >
                                        {brand.email}
                                    </a>
                                </li>
                            )}
                            {brand?.hours && (
                                <li className="flex items-start gap-3">
                                    <Clock className="mt-0.5 size-4 shrink-0 text-primary" />
                                    <span className="text-muted-foreground">
                                        {brand.hours}
                                    </span>
                                </li>
                            )}
                        </ul>

                        {whatsappUrl && (
                            <Button
                                asChild
                                size="lg"
                                className="mt-8 min-h-12 border-0 bg-linear-to-br from-[#25D366] to-[#128C7E] px-8 font-bold text-white hover:opacity-90"
                            >
                                <a
                                    href={whatsappUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    WhatsApp us
                                </a>
                            </Button>
                        )}
                    </div>

                    <div className="border-t border-border/80 pt-10 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-12">
                        <h2 className="font-display text-2xl font-bold tracking-tight md:text-3xl">
                            Send a message
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Tell us what you need — packages, schedules, or
                            events.
                        </p>

                        {sent && (
                            <p
                                className="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100"
                                role="status"
                            >
                                {flash?.message ??
                                    'Thanks — we received your message.'}
                            </p>
                        )}

                        <form
                            onSubmit={submit}
                            className="mt-8 grid gap-5"
                            noValidate
                        >
                            <div>
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    className="mt-1.5 min-h-11"
                                    value={form.data.name}
                                    onChange={(e) =>
                                        form.setData('name', e.target.value)
                                    }
                                    autoComplete="name"
                                    required
                                />
                                <InputError
                                    className="mt-1"
                                    message={form.errors.name}
                                />
                            </div>
                            <div className="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        className="mt-1.5 min-h-11"
                                        value={form.data.email}
                                        onChange={(e) =>
                                            form.setData(
                                                'email',
                                                e.target.value,
                                            )
                                        }
                                        autoComplete="email"
                                        required
                                    />
                                    <InputError
                                        className="mt-1"
                                        message={form.errors.email}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="phone">
                                        Phone{' '}
                                        <span className="font-normal text-muted-foreground">
                                            (optional)
                                        </span>
                                    </Label>
                                    <Input
                                        id="phone"
                                        className="mt-1.5 min-h-11"
                                        value={form.data.phone}
                                        onChange={(e) =>
                                            form.setData(
                                                'phone',
                                                e.target.value,
                                            )
                                        }
                                        autoComplete="tel"
                                    />
                                    <InputError
                                        className="mt-1"
                                        message={form.errors.phone}
                                    />
                                </div>
                            </div>
                            <div>
                                <Label htmlFor="subject">Subject</Label>
                                <select
                                    id="subject"
                                    className="mt-1.5 flex min-h-11 w-full rounded-md border border-input bg-background px-3 text-sm"
                                    value={form.data.subject}
                                    onChange={(e) =>
                                        form.setData('subject', e.target.value)
                                    }
                                >
                                    {subjects.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    className="mt-1"
                                    message={form.errors.subject}
                                />
                            </div>
                            <div>
                                <Label htmlFor="message">Message</Label>
                                <textarea
                                    id="message"
                                    className="mt-1.5 min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    value={form.data.message}
                                    onChange={(e) =>
                                        form.setData('message', e.target.value)
                                    }
                                    required
                                />
                                <InputError
                                    className="mt-1"
                                    message={form.errors.message}
                                />
                            </div>
                            <div className="flex flex-wrap items-center gap-3 pt-2">
                                <Button
                                    type="submit"
                                    size="lg"
                                    className="min-h-12 bg-[#f6c60c] px-8 font-bold text-[#695300] hover:bg-[#ffe089]"
                                    disabled={form.processing}
                                >
                                    {form.processing
                                        ? 'Sending…'
                                        : 'Send message'}
                                </Button>
                                <Button
                                    asChild
                                    variant="outline"
                                    size="lg"
                                    className="min-h-12"
                                >
                                    <Link href="/packages">
                                        View packages
                                    </Link>
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </>
    );
}
