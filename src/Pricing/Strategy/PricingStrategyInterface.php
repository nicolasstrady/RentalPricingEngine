<?php

namespace App\Pricing\Strategy;

use App\Entity\Equipment;
use App\Enum\PricingModel;

interface PricingStrategyInterface
{
    public function supports(PricingModel $pricingModel): bool;

    public function calculate(Equipment $equipment, int $durationInDays): float;
}
