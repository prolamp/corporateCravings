import { Head } from '@inertiajs/react';
import {
    BillSlipReceiptDocument,
    type BillSlipReceiptData,
} from '@/components/bill-slip-receipt-document';

type Props = { slip: BillSlipReceiptData };

export default function BillSlipReceipt({ slip }: Props) {
    return (
        <>
            <Head title={`Receipt ${slip.slip_number}`} />
            <BillSlipReceiptDocument slip={slip} />
        </>
    );
}

BillSlipReceipt.layout = {
    breadcrumbs: [],
};
