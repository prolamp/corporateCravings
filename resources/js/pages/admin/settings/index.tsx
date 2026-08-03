import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Field = {
    key: string;
    group: string;
    field: string;
    label: string;
    type: string;
    help: string | null;
    value: string;
};

type Group = {
    key: string;
    label: string;
    fields: Field[];
};

type NestedValues = Record<string, Record<string, string>>;

type Props = {
    groups: Group[];
    values: NestedValues;
};

export default function SiteSettingsIndex({ groups, values }: Props) {
    const form = useForm({
        values: structuredClone(values),
    });

    function setField(group: string, field: string, value: string) {
        form.setData('values', {
            ...form.data.values,
            [group]: {
                ...(form.data.values[group] ?? {}),
                [field]: value,
            },
        });
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        form.put('/admin/settings');
    }

    return (
        <>
            <Head title="Site Settings" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Content
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                        Site settings
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Brand contact details and analytics for the public site
                        and WhatsApp links.
                    </p>
                </div>

                <form onSubmit={submit} className="max-w-2xl space-y-6">
                    {form.hasErrors && (
                        <div
                            className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"
                            role="alert"
                        >
                            Please fix the highlighted fields and try again.
                        </div>
                    )}

                    {groups.map((group) => (
                        <section
                            key={group.key}
                            className="rounded-xl border border-border/80 bg-card p-4 md:p-6"
                        >
                            <h3 className="font-display text-lg font-semibold">
                                {group.label}
                            </h3>
                            <div className="mt-4 space-y-4">
                                {group.fields.map((field) => (
                                    <div key={field.key} className="grid gap-2">
                                        <Label htmlFor={field.key}>
                                            {field.label}
                                        </Label>
                                        <Input
                                            id={field.key}
                                            value={
                                                form.data.values[field.group]?.[
                                                    field.field
                                                ] ?? ''
                                            }
                                            onChange={(e) =>
                                                setField(
                                                    field.group,
                                                    field.field,
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {field.help && (
                                            <p className="text-xs text-muted-foreground">
                                                {field.help}
                                            </p>
                                        )}
                                        <InputError
                                            message={
                                                form.errors[
                                                    `values.${field.group}.${field.field}`
                                                ]
                                            }
                                        />
                                    </div>
                                ))}
                            </div>
                        </section>
                    ))}

                    <Button type="submit" disabled={form.processing}>
                        Save settings
                    </Button>
                </form>
            </div>
        </>
    );
}

SiteSettingsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Settings', href: '/admin/settings' },
    ],
};
