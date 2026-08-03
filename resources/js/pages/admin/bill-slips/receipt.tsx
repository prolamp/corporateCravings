import { Head } from '@inertiajs/react';

type SlipItem = {
    id: number;
    date_formatted: string | null;
    dish_title: string | null;
    persons: number;
    unit_price_formatted: string;
    line_total_formatted: string;
};

type Slip = {
    slip_number: string;
    company_name: string | null;
    package_title: string | null;
    period_label: string;
    subtotal_formatted: string;
    adjustment: number;
    adjustment_formatted: string;
    total_formatted: string;
    status_label: string;
    paid_at_formatted: string | null;
    notes: string | null;
    created_at_formatted: string | null;
    subscriber_phone: string | null;
    subscriber_email: string | null;
    subscriber_address: string | null;
    site_name: string;
    items: SlipItem[];
};

type Props = { slip: Slip };

export default function BillSlipReceipt({ slip }: Props) {
    return (
        <>
            <Head title={`Receipt ${slip.slip_number}`} />
            <div className="mx-auto max-w-3xl bg-white p-6 text-zinc-900 print:p-0">
                <div className="mb-6 flex flex-wrap items-start justify-between gap-4 border-b border-zinc-200 pb-4">
                    <div>
                        <p className="text-xs font-bold tracking-[0.12em] text-zinc-500 uppercase">
                            {slip.site_name}
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                            Bill slip / receipt
                        </h1>
                        <p className="mt-1 text-sm text-zinc-600">
                            {slip.slip_number} · {slip.status_label}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="rounded-md border border-zinc-300 px-3 py-1.5 text-sm print:hidden"
                    >
                        Print / save PDF
                    </button>
                </div>

                <div className="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-zinc-500 uppercase">
                            Billed to
                        </p>
                        <p className="mt-1 font-medium">{slip.company_name}</p>
                        {slip.subscriber_phone && (
                            <p className="text-zinc-600">{slip.subscriber_phone}</p>
                        )}
                        {slip.subscriber_email && (
                            <p className="text-zinc-600">{slip.subscriber_email}</p>
                        )}
                        {slip.subscriber_address && (
                            <p className="mt-1 text-zinc-600">
                                {slip.subscriber_address}
                            </p>
                        )}
                    </div>
                    <div className="sm:text-right">
                        <p className="text-xs font-bold tracking-[0.08em] text-zinc-500 uppercase">
                            Details
                        </p>
                        <p className="mt-1">Period: {slip.period_label}</p>
                        <p>Issued: {slip.created_at_formatted || '—'}</p>
                        {slip.paid_at_formatted && (
                            <p>Paid: {slip.paid_at_formatted}</p>
                        )}
                        {slip.package_title && (
                            <p>Package: {slip.package_title}</p>
                        )}
                    </div>
                </div>

                <table className="mt-8 w-full text-left text-sm">
                    <thead className="border-b border-zinc-300 text-[11px] font-bold tracking-[0.08em] text-zinc-500 uppercase">
                        <tr>
                            <th className="py-2 pr-3">Date</th>
                            <th className="py-2 pr-3">Dish</th>
                            <th className="py-2 pr-3">Persons</th>
                            <th className="py-2 pr-3">Unit</th>
                            <th className="py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        {slip.items.map((item) => (
                            <tr key={item.id} className="border-b border-zinc-100">
                                <td className="py-2.5 pr-3">{item.date_formatted}</td>
                                <td className="py-2.5 pr-3">{item.dish_title}</td>
                                <td className="py-2.5 pr-3">{item.persons}</td>
                                <td className="py-2.5 pr-3">
                                    {item.unit_price_formatted}
                                </td>
                                <td className="py-2.5 text-right font-medium">
                                    {item.line_total_formatted}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                <div className="ml-auto mt-6 w-full max-w-xs space-y-1 text-sm">
                    <div className="flex justify-between">
                        <span className="text-zinc-600">Subtotal</span>
                        <span>{slip.subtotal_formatted}</span>
                    </div>
                    {slip.adjustment !== 0 && (
                        <div className="flex justify-between">
                            <span className="text-zinc-600">Adjustment</span>
                            <span>{slip.adjustment_formatted}</span>
                        </div>
                    )}
                    <div className="flex justify-between border-t border-zinc-300 pt-2 text-base font-semibold">
                        <span>Total</span>
                        <span>{slip.total_formatted}</span>
                    </div>
                </div>

                {slip.notes && (
                    <p className="mt-8 text-sm text-zinc-600">
                        Notes: {slip.notes}
                    </p>
                )}
            </div>
        </>
    );
}

BillSlipReceipt.layout = {
    breadcrumbs: [],
};
