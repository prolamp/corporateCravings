<?php

/**
 * Permission catalog and role → permission map.
 * Super Admin is granted every permission implicitly via seeder.
 */
return [
    'permissions' => [
        // Users
        'users.view',
        'users.create_admin',
        'users.create_manager',
        'users.create_assistant',
        'users.create_subscriber',
        'users.create_customer',
        'users.update',
        'users.delete',

        // Catalog / ops
        'dashboard.view',
        'categories.manage',
        'dishes.manage',
        'addons.manage',
        'packages.manage',
        'delivery_zones.manage',
        'contracts.manage',
        'schedules.manage',
        'bill_slips.view',
        'bill_slips.manage',
        'orders.view',
        'orders.manage',
        'orders.update_status',
        'expenses.view',
        'expenses.create',
        'expenses.manage',
        'cost_calculator.view',
        'cms.manage',
        'settings.manage',
        'statuses.manage',

        // Finance reports
        'payments.view',
        'reports.earnings',
        'reports.profit_loss',
        'reports.orders',
        'reports.contracts',

        // Portals
        'portal.subscriber.dashboard',
        'portal.subscriber.schedules',
        'portal.subscriber.history',
        'portal.subscriber.contract',
        'portal.subscriber.bills',
        'portal.customer.dashboard',
        'portal.customer.orders',
        'portal.customer.track',
        'portal.customer.history',
    ],

    /*
    | Role permission sets. Super admin gets ALL permissions in the seeder.
    | Admin: same as full management minus creating admin users.
    | Manager: ops without creating managers and without earnings / P&L.
    | Assistant: status updates + add expenses; no payment pages; cannot create assistants.
    */
    'roles' => [
        'admin' => [
            'dashboard.view',
            'users.view',
            'users.create_manager',
            'users.create_assistant',
            'users.create_subscriber',
            'users.create_customer',
            'users.update',
            'users.delete',
            'categories.manage',
            'dishes.manage',
            'addons.manage',
            'packages.manage',
            'delivery_zones.manage',
            'contracts.manage',
            'schedules.manage',
            'bill_slips.view',
            'bill_slips.manage',
            'orders.view',
            'orders.manage',
            'orders.update_status',
            'expenses.view',
            'expenses.create',
            'expenses.manage',
            'cost_calculator.view',
            'cms.manage',
            'settings.manage',
            'statuses.manage',
            'payments.view',
            'reports.earnings',
            'reports.profit_loss',
            'reports.orders',
            'reports.contracts',
        ],
        'manager' => [
            'dashboard.view',
            'users.view',
            'users.create_assistant',
            'users.create_subscriber',
            'users.create_customer',
            'users.update',
            'categories.manage',
            'dishes.manage',
            'addons.manage',
            'packages.manage',
            'delivery_zones.manage',
            'contracts.manage',
            'schedules.manage',
            'bill_slips.view',
            'bill_slips.manage',
            'orders.view',
            'orders.manage',
            'orders.update_status',
            'expenses.view',
            'expenses.create',
            'expenses.manage',
            'cost_calculator.view',
            'cms.manage',
            'payments.view',
            'reports.orders',
            'reports.contracts',
            // no reports.earnings, reports.profit_loss
            // no users.create_manager / create_admin
        ],
        'assistant' => [
            'dashboard.view',
            'users.view',
            'orders.view',
            'orders.update_status',
            'schedules.manage',
            'bill_slips.view',
            'expenses.view',
            'expenses.create',
            'categories.manage',
            'dishes.manage',
            'contracts.manage',
            // no payments.view, earnings, P&L, create assistant
        ],
        'subscriber' => [
            'portal.subscriber.dashboard',
            'portal.subscriber.schedules',
            'portal.subscriber.history',
            'portal.subscriber.contract',
            'portal.subscriber.bills',
        ],
        'customer' => [
            'portal.customer.dashboard',
            'portal.customer.orders',
            'portal.customer.track',
            'portal.customer.history',
        ],
    ],

    /*
    | Dashboard widgets each role may see (UI keys).
    */
    'dashboard_widgets' => [
        'super_admin' => [
            'orders',
            'earnings',
            'corporate_contracts',
            'pending_deliveries',
            'expenses',
            'customers',
        ],
        'admin' => [
            'orders',
            'earnings',
            'corporate_contracts',
            'pending_deliveries',
            'expenses',
            'customers',
        ],
        'manager' => [
            'orders',
            'corporate_contracts',
            'pending_deliveries',
            'customers',
            // no earnings / expense money totals
        ],
        'assistant' => [
            'orders',
            'pending_deliveries',
            // no earnings / expense money totals
        ],
        'subscriber' => [
            'delivered_orders',
            'total_bill',
            'persons_served',
        ],
        'customer' => [
            'my_orders_count',
            'track_order',
        ],
    ],
];
