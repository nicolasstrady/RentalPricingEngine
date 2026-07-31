<?php

namespace App\Pricing\Calculator;

final class MinimumRentalPriceCalculator
{
    /** @param list<array{durationInDays: int, amount: int}> $options */
    public function calculate(int $requestedDays, array $options): int
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

            if ($option['amount'] < 0) {
                throw new \InvalidArgumentException('Pricing option amount cannot be negative.');
            }
        }

        $minimumCosts = array_fill(0, $requestedDays + 1, PHP_INT_MAX);
        $minimumCosts[0] = 0;

        for ($coveredDays = 0; $coveredDays < $requestedDays; ++$coveredDays) {
            if (PHP_INT_MAX === $minimumCosts[$coveredDays]) {
                continue;
            }

            foreach ($options as $option) {
                $newCoveredDays = min($requestedDays, $coveredDays + $option['durationInDays']);
                $candidateCost = $minimumCosts[$coveredDays] + $option['amount'];
                $minimumCosts[$newCoveredDays] = min($minimumCosts[$newCoveredDays], $candidateCost);
            }
        }

        return $minimumCosts[$requestedDays];
    }
}
