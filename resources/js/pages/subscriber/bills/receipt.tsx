import { Head } from '@inertiajs/react';
import {
    BillSlipReceiptDocument,
    type BillSlipReceiptData,
} from '@/components/bill-slip-receipt-document';

type Props = { slip: BillSlipReceiptData };

export default function SubscriberBillReceipt({ slip }: Props) {
    return (
        <>
            <Head title={slip.site_name || 'Bill slip'} />
            <BillSlipReceiptDocument slip={slip} />
        </>
    );
}

SubscriberBillReceipt.layout = {
    breadcrumbs: [],
};
