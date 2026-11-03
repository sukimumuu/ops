<?php

namespace App\Services\Payments;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

class DpAmountCalculator
{
    public function calculate(string|int|float $propertyAmount): int
    {
        $percentage = BigDecimal::of((string) config('services.mihom.dp_percentage'));

        if ($percentage->compareTo('0') <= 0 || $percentage->compareTo('100') > 0) {
            throw new InvalidArgumentException('The configured DP percentage must be greater than 0 and no more than 100.');
        }

        $amount = BigDecimal::of((string) $propertyAmount)
            ->multipliedBy($percentage)
            ->dividedBy('100', 0, RoundingMode::HalfUp)
            ->toInt();

        if ($amount <= 0) {
            throw new InvalidArgumentException('The calculated DP amount must be greater than zero.');
        }

        return $amount;
    }
}
