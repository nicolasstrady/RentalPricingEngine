<?php

namespace App\Factory;

use App\Entity\Equipment;
use App\Enum\EquipmentCategory;
use App\Enum\PricingModel;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<Equipment> */
final class EquipmentFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Equipment::class;
    }

    /** @return array<string, mixed> */
    protected function defaults(): array
    {
        return [
            'category' => self::faker()->randomElement(EquipmentCategory::cases()),
            'name' => rtrim(self::faker()->unique()->sentence(3), '.'),
            'pricingModel' => self::faker()->randomElement(PricingModel::cases()),
        ];
    }
}
