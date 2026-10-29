import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState, type FormEvent } from 'react';
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
    signature_url: string | null;
};

export default function SiteSettingsIndex({
    groups,
    values,
    signature_url,
}: Props) {
    const form = useForm({
        values: structuredClone(values),
        signature: null as File | null,
        remove_signature: false as boolean,
        _method: 'put',
    });

    const [signaturePreview, setSignaturePreview] = useState<string | null>(
        null,
    );

    useEffect(() => {
        if (!form.data.signature) {
            setSignaturePreview(null);

            return;
        }

        const url = URL.createObjectURL(form.data.signature);
        setSignaturePreview(url);

        return () => URL.revokeObjectURL(url);
    }, [form.data.signature]);

    const existingSignature =
        !form.data.remove_signature && !signaturePreview
            ? signature_url
            : null;
    const displaySignature = signaturePreview ?? existingSignature;

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
        form.post('/admin/settings', { forceFormData: true });
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
                        Brand contact, bill-slip receipt footer, and analytics.
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
                                {group.fields.map((field) => {
                                    if (field.type === 'image') {
                                        return (
                                            <div
                                                key={field.key}
                                                className="grid gap-2"
                                            >
                                                <Label htmlFor={field.key}>
                                                    {field.label}
                                                </Label>
                                                {displaySignature && (
                                                    <img
                                                        src={displaySignature}
                                                        alt="Signature preview"
                                                        className="h-16 w-auto rounded border border-border/60 bg-white object-contain p-2"
                                                    />
                                                )}
                                                <Input
                                                    id={field.key}
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => {
                                                        const file =
                                                            e.target.files?.[0] ??
                                                            null;
                                                        form.setData(
                                                            'signature',
                                                            file,
                                                        );
                                                        if (file) {
                                                            form.setData(
                                                                'remove_signature',
                                                                false,
                                                            );
                                                        }
                                                    }}
                                                />
                                                {(displaySignature ||
                                                    form.data.signature) && (
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        className="w-fit"
                                                        onClick={() => {
                                                            form.setData(
                                                                'signature',
                                                                null,
                                                            );
                                                            form.setData(
                                                                'remove_signature',
                                                                true,
                                                            );
                                                        }}
                                                    >
                                                        Remove signature
                                                    </Button>
                                                )}
                                                {field.help && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {field.help}
                                                    </p>
                                                )}
                                                <InputError
                                                    message={
                                                        form.errors.signature
                                                    }
                                                />
                                            </div>
                                        );
                                    }

                                    if (field.type === 'text') {
                                        return (
                                            <div
                                                key={field.key}
                                                className="grid gap-2"
                                            >
                                                <Label htmlFor={field.key}>
                                                    {field.label}
                                                </Label>
                                                <textarea
                                                    id={field.key}
                                                    rows={4}
                                                    className="border-input flex min-h-24 w-full rounded-md border bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                    value={
                                                        form.data.values[
                                                            field.group
                                                        ]?.[field.field] ?? ''
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
                                        );
                                    }

                                    return (
                                        <div
                                            key={field.key}
                                            className="grid gap-2"
                                        >
                                            <Label htmlFor={field.key}>
                                                {field.label}
                                            </Label>
                                            <Input
                                                id={field.key}
                                                value={
                                                    form.data.values[
                                                        field.group
                                                    ]?.[field.field] ?? ''
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
                                    );
                                })}
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
