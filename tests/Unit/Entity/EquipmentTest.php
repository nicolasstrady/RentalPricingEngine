<?php

namespace App\Tests\Unit\Entity;

use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use PHPUnit\Framework\TestCase;

final class EquipmentTest extends TestCase
{
    public function testItTrimsItsNameAndManagesPricingRates(): void
    {
        $equipment = EquipmentFactory::createOne([
            'name' => '  Perceuse  ',
            'pricingModel' => PricingModel::Tiered,
        ]);
        $rate = PricingRateFactory::createOne([
            'equipment' => $equipment,
            'amount' => 20,
            'durationInDays' => 1,
        ]);

        self::assertSame('Perceuse', $equipment->getName());
        self::assertSame(PricingModel::Tiered, $equipment->getPricingModel());
        self::assertTrue($equipment->getPricingRates()->contains($rate));
        self::assertSame($equipment, $rate->getEquipment());

        $equipment->addPricingRate($rate);

        self::assertCount(1, $equipment->getPricingRates());

        $equipment->setPricingModel(PricingModel::FlatRate);

        self::assertSame(PricingModel::FlatRate, $equipment->getPricingModel());

        $equipment->removePricingRate($rate);

        self::assertFalse($equipment->getPricingRates()->contains($rate));
    }

    public function testItRejectsAnEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Equipment name cannot be empty.');

        EquipmentFactory::createOne(['name' => '   ']);
    }
}
