<?php

namespace App\Support;

class Money
{
    public const CURRENCY = 'PKR';

    public const SCALE = 2;

    public static function of(int|float|string $amount): string
    {
        return number_format((float) $amount, self::SCALE, '.', '');
    }

    public static function add(int|float|string ...$amounts): string
    {
        $total = 0.0;

        foreach ($amounts as $amount) {
            $total += (float) $amount;
        }

        return self::of($total);
    }

    public static function subtract(int|float|string $a, int|float|string $b): string
    {
        return self::of((float) $a - (float) $b);
    }

    public static function multiply(int|float|string $amount, int|float|string $factor): string
    {
        return self::of((float) $amount * (float) $factor);
    }

    public static function percentOf(int|float|string $amount, int|float|string $percent): string
    {
        return self::of(((float) $amount * (float) $percent) / 100);
    }

    public static function format(int|float|string $amount): string
    {
        return self::CURRENCY.' '.number_format((float) self::of($amount), self::SCALE);
    }
}
