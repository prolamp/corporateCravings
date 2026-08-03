import {
    CalendarDays,
    ClipboardList,
    FileText,
    LayoutGrid,
    Receipt,
} from 'lucide-react';
import type { AdminNavGroup } from '@/lib/admin-nav';
import { userCan } from '@/lib/roles';
import type { User } from '@/types';

export const subscriberNavGroups: AdminNavGroup[] = [
    {
        label: 'Portal',
        items: [
            {
                title: 'Dashboard',
                href: '/subscriber/dashboard',
                icon: LayoutGrid,
                permission: 'portal.subscriber.dashboard',
            },
            {
                title: 'Schedules',
                href: '/subscriber/schedules',
                icon: CalendarDays,
                permission: 'portal.subscriber.schedules',
            },
            {
                title: 'History',
                href: '/subscriber/history',
                icon: ClipboardList,
                permission: 'portal.subscriber.history',
            },
            {
                title: 'Bills',
                href: '/subscriber/bills',
                icon: Receipt,
                permission: 'portal.subscriber.bills',
            },
            {
                title: 'Contract',
                href: '/subscriber/contract',
                icon: FileText,
                permission: 'portal.subscriber.contract',
            },
        ],
    },
];

export function filterSubscriberNav(user?: User | null): AdminNavGroup[] {
    // Subscribers always see the full portal menu; route middleware still enforces permissions.
    if (user?.is_subscriber) {
        return subscriberNavGroups;
    }

    return subscriberNavGroups
        .map((group) => ({
            ...group,
            items: group.items.filter(
                (item) => !item.permission || userCan(user, item.permission),
            ),
        }))
        .filter((group) => group.items.length > 0);
}
