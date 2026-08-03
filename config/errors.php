<?php

/**
 * Global error catalog — machine codes map to safe user-facing messages.
 * Never expose exception class names or stack traces in UI.
 */
return [
    'ok' => 'OK',
    'error' => 'Something went wrong. Please try again.',
    'validation_failed' => 'Please check the highlighted fields and try again.',
    'unauthenticated' => 'Please sign in to continue.',
    'unauthorized' => 'You do not have permission to perform this action.',
    'not_found' => 'The requested resource was not found.',
    'conflict' => 'This action conflicts with the current state.',
    'rate_limited' => 'Too many attempts. Please wait and try again.',
    'upload_failed' => 'Image upload failed. Please try a different file.',
    'image_invalid' => 'Please upload a valid image file.',
    'order_not_found' => 'Order not found.',
    'order_invalid_status' => 'This order status change is not allowed.',
    'cart_empty' => 'Your cart is empty.',
    'order_reordered' => 'Items added to your cart.',
    'order_reorder_partial' => 'Some items were unavailable and skipped. Available items were added to your cart.',
    'order_reorder_failed' => 'None of the items from this order are available to reorder.',
    'zone_required' => 'Please select a delivery zone.',
    'dish_unavailable' => 'This dish is currently unavailable.',
    'variation_unavailable' => 'This quantity option is currently unavailable.',
    'addon_unavailable' => 'One or more selected addons are unavailable.',
    'subscriber_inactive' => 'This subscriber contract is not active.',
    'schedule_locked' => 'This schedule entry cannot be modified.',
    'login_failed' => 'Invalid phone number or password.',
];
