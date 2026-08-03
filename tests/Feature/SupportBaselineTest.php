<?php

use App\Support\ErrorCatalog;
use App\Support\Money;
use App\Support\StatusRegistry;

test('status registry returns human labels never raw unknown dumps', function () {
    $status = StatusRegistry::get('order', 'pending');

    expect($status['label'])->toBe('Pending')
        ->and($status['code'])->toBe('pending')
        ->and(StatusRegistry::label('order', 'does_not_exist'))->toBe('Unknown');
});

test('error catalog maps codes to safe messages', function () {
    expect(ErrorCatalog::message('unauthorized'))
        ->toBe('You do not have permission to perform this action.')
        ->and(ErrorCatalog::message('totally_missing_code'))
        ->toBe('Something went wrong. Please try again.');
});

test('money helper formats pkr amounts', function () {
    expect(Money::of(100.456))->toBe('100.46')
        ->and(Money::add(10, 5.5))->toBe('15.50')
        ->and(Money::percentOf(200, 10))->toBe('20.00')
        ->and(Money::format(99))->toContain('PKR');
});
