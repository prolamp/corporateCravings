import {
    AlertCircle,
    Calculator,
    CalendarDays,
    BarChart3,
    ClipboardList,
    FileText,
    FolderTree,
    LayoutGrid,
    Layers,
    MapPin,
    Package,
    Receipt,
    Settings2,
    ShoppingBag,
    Tags,
    Users,
    UtensilsCrossed,
    Wallet,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { dashboard } from '@/routes';
import { userCan } from '@/lib/roles';
import type { User } from '@/types';

export type AdminNavItem = {
    title: string;
    href: string;
    icon?: LucideIcon;
    permission?: string;
    /** Visible for the role but not implemented until a later phase. */
    comingSoon?: boolean;
};

export type AdminNavGroup = {
    label: string;
    items: AdminNavItem[];
};

/**
 * Stitch admin IA — only Dashboard is live in Phase 2.
 * Other items are permission-gated placeholders until their CRUD phases.
 */
export const adminNavGroups: AdminNavGroup[] = [
    {
        label: 'Overview',
        items: [
            {
                title: 'Dashboard',
                href: dashboard.url(),
                icon: LayoutGrid,
                permission: 'dashboard.view',
            },
        ],
    },
    {
        label: 'Catalog',
        items: [
            {
                title: 'Categories',
                href: '/admin/categories',
                icon: Tags,
                permission: 'categories.manage',
            },
            {
                title: 'Dishes',
                href: '/admin/dishes',
                icon: UtensilsCrossed,
                permission: 'dishes.manage',
            },
            {
                title: 'Addons',
                href: '/admin/addons',
                icon: Layers,
                permission: 'addons.manage',
            },
        ],
    },
    {
        label: 'Packages',
        items: [
            {
                title: 'Packages',
                href: '/admin/packages',
                icon: Package,
                permission: 'packages.manage',
            },
            {
                title: 'Subscribers',
                href: '/admin/subscribers',
                icon: Users,
                permission: 'contracts.manage',
            },
            {
                title: 'Contracts',
                href: '/admin/contracts',
                icon: ClipboardList,
                permission: 'contracts.manage',
            },
            {
                title: 'Schedules',
                href: '/admin/schedules',
                icon: CalendarDays,
                permission: 'schedules.manage',
            },
            {
                title: 'Bill slips',
                href: '/admin/bill-slips',
                icon: Receipt,
                permission: 'bill_slips.view',
            },
        ],
    },
    {
        label: 'Orders',
        items: [
            {
                title: 'Orders',
                href: '/admin/orders',
                icon: ShoppingBag,
                permission: 'orders.view',
            },
        ],
    },
    {
        label: 'Operations',
        items: [
            {
                title: 'Delivery Zones',
                href: '/admin/delivery-zones',
                icon: MapPin,
                permission: 'delivery_zones.manage',
            },
            {
                title: 'Expense Categories',
                href: '/admin/expense-categories',
                icon: FolderTree,
                permission: 'expenses.manage',
            },
            {
                title: 'Expenses',
                href: '/admin/expenses',
                icon: Wallet,
                permission: 'expenses.view',
            },
            {
                title: 'Dish Cost Calculator',
                href: '/admin/cost-calculator',
                icon: Calculator,
                permission: 'cost_calculator.view',
            },
        ],
    },
    {
        label: 'Reports',
        items: [
            {
                title: 'Reports',
                href: '/admin/reports',
                icon: BarChart3,
                permission: 'reports.orders',
            },
        ],
    },
    {
        label: 'Content',
        items: [
            {
                title: 'Pages',
                href: '/admin/pages',
                icon: FileText,
                permission: 'cms.manage',
            },
            {
                title: 'Settings',
                href: '/admin/settings',
                icon: Settings2,
                permission: 'settings.manage',
            },
        ],
    },
    {
        label: 'People',
        items: [
            {
                title: 'Users',
                href: '/admin/users',
                icon: ClipboardList,
                permission: 'users.view',
            },
        ],
    },
    {
        label: 'System',
        items: [
            {
                title: 'Statuses & Errors',
                href: '/admin/statuses',
                icon: AlertCircle,
                permission: 'statuses.manage',
            },
        ],
    },
];

export function filterAdminNav(user: User | null | undefined): AdminNavGroup[] {
    return adminNavGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => {
                if (!item.permission) {
                    return true;
                }

                return userCan(user, item.permission);
            }),
        }))
        .filter((group) => group.items.length > 0);
}
