export type BillSlipReceiptItem = {
    id: number;
    date_formatted: string | null;
    dish_title: string | null;
    persons: number;
    unit_price_formatted: string;
    line_total_formatted: string;
};

export type BillSlipReceiptData = {
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
    logo_url?: string | null;
    manager_name?: string | null;
    bank_label?: string | null;
    bank_account_number?: string | null;
    receipt_description?: string | null;
    signature_url?: string | null;
    items: BillSlipReceiptItem[];
};

type Props = {
    slip: BillSlipReceiptData;
};

export function BillSlipReceiptDocument({ slip }: Props) {
    const hasBank =
        Boolean(slip.bank_label) || Boolean(slip.bank_account_number);
    const hasSignatureBlock =
        Boolean(slip.manager_name) || Boolean(slip.signature_url);

    return (
        <div className="mx-auto max-w-3xl bg-white p-6 text-zinc-900 print:max-w-none print:p-0">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-4 border-b-2 border-zinc-900 pb-3">
                <div className="min-w-0">
                    {slip.logo_url ? (
                        <img
                            src={slip.logo_url}
                            alt={slip.site_name}
                            className="h-9 w-auto object-contain object-left print:h-9"
                        />
                    ) : (
                        <p className="text-sm font-semibold tracking-tight">
                            {slip.site_name}
                        </p>
                    )}
                </div>
                <div className="flex flex-col items-end gap-2 text-right">
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="rounded-md border border-zinc-300 px-3 py-1.5 text-sm print:hidden"
                    >
                        Print / save PDF
                    </button>
                    <div>
                        <h1 className="text-base font-semibold tracking-tight">
                            Bill slip / receipt
                        </h1>
                        <p className="mt-0.5 text-xs text-zinc-600">
                            {slip.slip_number} · {slip.status_label}
                        </p>
                    </div>
                </div>
            </div>

            <div className="grid gap-2 text-sm sm:grid-cols-2">
                <div>
                    <p className="text-[11px] font-bold tracking-[0.1em] text-zinc-500 uppercase">
                        Billed to
                    </p>
                    <p className="mt-0.5 text-base font-semibold leading-tight">
                        {slip.company_name}
                    </p>
                </div>
                <div className="sm:text-right">
                    <p className="text-[11px] font-bold tracking-[0.1em] text-zinc-500 uppercase">
                        Details
                    </p>
                    <p className="mt-0.5 leading-tight">
                        <span className="text-zinc-500">Period </span>
                        <span className="font-medium">{slip.period_label}</span>
                    </p>
                </div>
            </div>

            <table className="mt-4 w-full text-left text-xs">
                <thead className="border-y border-zinc-900 text-xs font-bold tracking-[0.08em] text-zinc-500 uppercase">
                    <tr>
                        <th className="py-1 pr-3">Date</th>
                        <th className="py-1 pr-3">Dish</th>
                        <th className="py-1 pr-3">Persons</th>
                        <th className="py-1 pr-3">Unit</th>
                        <th className="py-1 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {slip.items.map((item) => (
                        <tr
                            key={item.id}
                            className="border-b border-zinc-100"
                        >
                            <td className="py-1 pr-3 whitespace-nowrap">
                                {item.date_formatted}
                            </td>
                            <td className="py-1 pr-3">{item.dish_title}</td>
                            <td className="py-1 pr-3">{item.persons}</td>
                            <td className="py-1 pr-3 whitespace-nowrap">
                                {item.unit_price_formatted}
                            </td>
                            <td className="py-1 text-right font-medium whitespace-nowrap">
                                {item.line_total_formatted}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <div className="ml-auto mt-6 w-full max-w-xs space-y-1.5 text-[13px]">
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
                <div className="flex justify-between border-t-2 border-zinc-900 pt-2 text-sm font-semibold">
                    <span>Total</span>
                    <span>{slip.total_formatted}</span>
                </div>
            </div>

            {(hasBank ||
                slip.receipt_description ||
                slip.notes ||
                hasSignatureBlock) && (
                <div className="mt-10 grid gap-8 border-t border-zinc-200 pt-8 sm:grid-cols-2">
                    <div className="space-y-4 text-xs">
                        {hasBank && (
                            <div>
                                <p className="text-[11px] font-bold tracking-[0.1em] text-zinc-500 uppercase">
                                    Payment details
                                </p>
                                {slip.bank_label && (
                                    <p className="mt-2 text-xs font-medium">
                                        {slip.bank_label}
                                    </p>
                                )}
                                {slip.bank_account_number && (
                                    <p className="mt-0.5 font-mono text-xs text-zinc-700">
                                        {slip.bank_account_number}
                                    </p>
                                )}
                            </div>
                        )}

                        {slip.receipt_description && (
                            <p className="whitespace-pre-wrap text-xs text-zinc-600">
                                {slip.receipt_description}
                            </p>
                        )}

                        {slip.notes && (
                            <p className="text-xs text-zinc-600">
                                <span className="font-medium text-zinc-800">
                                    Notes:{' '}
                                </span>
                                {slip.notes}
                            </p>
                        )}
                    </div>

                    {hasSignatureBlock && (
                        <div className="flex flex-col items-start sm:items-end">
                            {slip.signature_url && (
                                <img
                                    src={slip.signature_url}
                                    alt=""
                                    className="mb-1.5 h-10 w-auto max-w-[140px] object-contain object-right"
                                />
                            )}
                            {slip.manager_name && (
                                <p className="text-base font-semibold tracking-tight">
                                    {slip.manager_name}
                                </p>
                            )}
                            <div className="mt-1 w-40 border-t border-zinc-400 pt-1 text-center text-[11px] tracking-[0.08em] text-zinc-500 uppercase sm:text-right">
                                Authorized
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
