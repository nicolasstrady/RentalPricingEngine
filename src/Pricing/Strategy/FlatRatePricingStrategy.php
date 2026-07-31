<?php

namespace App\Pricing\Strategy;

use App\Entity\Equipment;
use App\Enum\PricingModel;
use App\Pricing\Contract\PricingStrategyInterface;
use App\Pricing\Exception\InvalidPricingRateException;

final class FlatRatePricingStrategy implements PricingStrategyInterface
{
    public function supports(PricingModel $pricingModel): bool
    {
        return PricingModel::FlatRate === $pricingModel;
    }

    public function calculate(Equipment $equipment, int $durationInDays): int
    {
        $rates = $equipment->getPricingRates();
        $rate = $rates->first();

        if (1 !== $rates->count() || false === $rate || null !== $rate->getDurationInDays()) {
            throw InvalidPricingRateException::invalidFlatRate($equipment->getName());
        }

        return $rate->getAmount();
    }
}
