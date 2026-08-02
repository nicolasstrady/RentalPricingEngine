<?php

namespace App\Pricing;

use App\Entity\Equipment;
use App\Pricing\Exception\UnsupportedPricingModelException;
use App\Pricing\Strategy\PricingStrategyInterface;

final readonly class PricingEngine
{
    /** @param iterable<PricingStrategyInterface> $strategies */
    public function __construct(private iterable $strategies)
    {
    }

    public function calculate(Equipment $equipment, int $durationInDays): float
    {
        if ($durationInDays <= 0) {
            throw new \InvalidArgumentException('Rental duration must be positive.');
        }

        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($equipment->getPricingModel())) {
                return round($strategy->calculate($equipment, $durationInDays), 2, PHP_ROUND_HALF_UP);
            }
        }

        throw UnsupportedPricingModelException::forModel($equipment->getPricingModel());
    }
}
