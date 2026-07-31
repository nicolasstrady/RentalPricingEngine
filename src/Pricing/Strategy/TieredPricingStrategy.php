<?php

namespace App\Pricing\Strategy;

use App\Entity\Equipment;
use App\Enum\PricingModel;
use App\Pricing\Calculator\MinimumRentalPriceCalculator;
use App\Pricing\Contract\PricingStrategyInterface;
use App\Pricing\Exception\InvalidPricingRateException;

final readonly class TieredPricingStrategy implements PricingStrategyInterface
{
    public function __construct(private MinimumRentalPriceCalculator $calculator)
    {
    }

    public function supports(PricingModel $pricingModel): bool
    {
        return PricingModel::Tiered === $pricingModel;
    }

    public function calculate(Equipment $equipment, int $durationInDays): int
    {
        $options = [];

        foreach ($equipment->getPricingRates() as $rate) {
            $rateDurationInDays = $rate->getDurationInDays();

            if (null === $rateDurationInDays) {
                throw InvalidPricingRateException::missingTieredDuration($equipment->getName());
            }

            $options[] = [
                'durationInDays' => $rateDurationInDays,
                'amount' => $rate->getAmount(),
            ];
        }

        if ([] === $options) {
            throw InvalidPricingRateException::missingTieredRates($equipment->getName());
        }

        return $this->calculator->calculate($durationInDays, $options);
    }
}
