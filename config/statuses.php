<?php

/**
 * Global status registry — single source of truth for domain statuses.
 * UI must render labels/colors from here; never show raw codes to users.
 */
return [
    'order' => [
        'pending' => [
            'label' => 'Pending',
            'color' => 'amber',
            'badge' => 'warning',
        ],
        'confirmed' => [
            'label' => 'Confirmed',
            'color' => 'blue',
            'badge' => 'info',
        ],
        'leave_for_delivery' => [
            'label' => 'Out for Delivery',
            'color' => 'indigo',
            'badge' => 'info',
        ],
        'delivered' => [
            'label' => 'Delivered',
            'color' => 'emerald',
            'badge' => 'success',
        ],
        'completed' => [
            'label' => 'Completed',
            'color' => 'green',
            'badge' => 'success',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'red',
            'badge' => 'danger',
        ],
    ],

    'schedule' => [
        'scheduled' => [
            'label' => 'Scheduled',
            'color' => 'slate',
            'badge' => 'default',
        ],
        'confirmed' => [
            'label' => 'Confirmed',
            'color' => 'blue',
            'badge' => 'info',
        ],
        'delivered' => [
            'label' => 'Delivered',
            'color' => 'emerald',
            'badge' => 'success',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'red',
            'badge' => 'danger',
        ],
        'skipped' => [
            'label' => 'Skipped',
            'color' => 'zinc',
            'badge' => 'default',
        ],
    ],

    'bill_slip' => [
        'draft' => [
            'label' => 'Draft',
            'color' => 'slate',
            'badge' => 'default',
        ],
        'issued' => [
            'label' => 'Issued',
            'color' => 'blue',
            'badge' => 'info',
        ],
        'paid' => [
            'label' => 'Paid',
            'color' => 'emerald',
            'badge' => 'success',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'red',
            'badge' => 'danger',
        ],
    ],

    'payment' => [
        'unpaid' => [
            'label' => 'Unpaid',
            'color' => 'red',
            'badge' => 'danger',
        ],
        'paid' => [
            'label' => 'Paid',
            'color' => 'emerald',
            'badge' => 'success',
        ],
    ],

    'user' => [
        'active' => [
            'label' => 'Active',
            'color' => 'green',
            'badge' => 'success',
        ],
        'inactive' => [
            'label' => 'Inactive',
            'color' => 'zinc',
            'badge' => 'default',
        ],
        'suspended' => [
            'label' => 'Suspended',
            'color' => 'red',
            'badge' => 'danger',
        ],
    ],

    'generic' => [
        'active' => [
            'label' => 'Active',
            'color' => 'green',
            'badge' => 'success',
        ],
        'inactive' => [
            'label' => 'Inactive',
            'color' => 'zinc',
            'badge' => 'default',
        ],
        'draft' => [
            'label' => 'Draft',
            'color' => 'slate',
            'badge' => 'default',
        ],
        'expired' => [
            'label' => 'Expired',
            'color' => 'amber',
            'badge' => 'warning',
        ],
    ],

    'page' => [
        'draft' => [
            'label' => 'Draft',
            'color' => 'slate',
            'badge' => 'default',
        ],
        'published' => [
            'label' => 'Published',
            'color' => 'green',
            'badge' => 'success',
        ],
    ],

    'expense' => [
        'pending' => [
            'label' => 'Pending',
            'color' => 'amber',
            'badge' => 'warning',
        ],
        'approved' => [
            'label' => 'Approved',
            'color' => 'green',
            'badge' => 'success',
        ],
        'rejected' => [
            'label' => 'Rejected',
            'color' => 'red',
            'badge' => 'danger',
        ],
    ],

    'unknown' => [
        'label' => 'Unknown',
        'color' => 'zinc',
        'badge' => 'default',
    ],
];
