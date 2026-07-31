<?php

namespace App\Factory;

use App\Entity\PricingRate;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<PricingRate> */
final class PricingRateFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return PricingRate::class;
    }

    /** @return array<string, mixed> */
    protected function defaults(): array
    {
        return [
            'amount' => self::faker()->numberBetween(1, 500),
            'durationInDays' => self::faker()->numberBetween(1, 30),
            'equipment' => EquipmentFactory::new(),
        ];
    }
}
