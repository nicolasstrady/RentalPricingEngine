<?php

namespace App\Pricing\Calculator;

final class MinimumRentalPriceCalculator
{
    /** @param list<array{durationInDays: int, amount: float}> $options */
    public function calculate(int $requestedDays, array $options): float
    {
        if ($requestedDays <= 0) {
            throw new \InvalidArgumentException('Requested duration must be positive.');
        }

        if ([] === $options) {
            throw new \InvalidArgumentException('At least one pricing option is required.');
        }

        foreach ($options as $option) {
            if ($option['durationInDays'] <= 0) {
                throw new \InvalidArgumentException('Pricing option duration must be positive.');
            }

            if (!is_finite($option['amount'])) {
                throw new \InvalidArgumentException('Pricing option amount must be finite.');
            }

            if ($option['amount'] < 0) {
                throw new \InvalidArgumentException('Pricing option amount cannot be negative.');
            }
        }

        $minimumCosts = array_fill(0, $requestedDays + 1, INF);
        $minimumCosts[0] = 0.0;

        for ($coveredDays = 0; $coveredDays < $requestedDays; ++$coveredDays) {
            if (INF === $minimumCosts[$coveredDays]) {
                continue;
            }

            foreach ($options as $option) {
                $newCoveredDays = min($requestedDays, $coveredDays + $option['durationInDays']);
                $candidateCost = round(
                    $minimumCosts[$coveredDays] + $option['amount'],
                    2,
                    PHP_ROUND_HALF_UP,
                );
                $minimumCosts[$newCoveredDays] = min($minimumCosts[$newCoveredDays], $candidateCost);
            }
        }

        return round($minimumCosts[$requestedDays], 2, PHP_ROUND_HALF_UP);
    }
}
