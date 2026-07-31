<?php

namespace App\Pricing;

use App\Entity\Equipment;
use App\Pricing\Contract\PricingStrategyInterface;
use App\Pricing\Exception\UnsupportedPricingModelException;

final readonly class PricingEngine
{
    /** @param iterable<PricingStrategyInterface> $strategies */
    public function __construct(private iterable $strategies)
    {
    }

    public function calculate(Equipment $equipment, int $durationInDays): int
    {
        if ($durationInDays <= 0) {
            throw new \InvalidArgumentException('Rental duration must be positive.');
        }

        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($equipment->getPricingModel())) {
                return $strategy->calculate($equipment, $durationInDays);
            }
        }

        throw UnsupportedPricingModelException::forModel($equipment->getPricingModel());
    }
}
