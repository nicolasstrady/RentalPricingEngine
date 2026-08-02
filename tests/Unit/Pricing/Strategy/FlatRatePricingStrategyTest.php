<?php

namespace App\Tests\Unit\Pricing\Strategy;

use App\Entity\Equipment;
use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use App\Pricing\Exception\InvalidPricingRateException;
use App\Pricing\Strategy\FlatRatePricingStrategy;
use PHPUnit\Framework\TestCase;

final class FlatRatePricingStrategyTest extends TestCase
{
    private FlatRatePricingStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new FlatRatePricingStrategy();
    }

    public function testItSupportsOnlyFlatRatePricing(): void
    {
        self::assertTrue($this->strategy->supports(PricingModel::FlatRate));
        self::assertFalse($this->strategy->supports(PricingModel::Tiered));
    }

    public function testItReturnsTheSameAmountRegardlessOfTheDuration(): void
    {
        $equipment = $this->createFlatRateEquipment();
        PricingRateFactory::createOne([
            'equipment' => $equipment,
            'amount' => 150.75,
            'durationInDays' => null,
        ]);

        self::assertSame(150.75, $this->strategy->calculate($equipment, 1));
        self::assertSame(150.75, $this->strategy->calculate($equipment, 42));
    }

    public function testItRejectsARateWithADuration(): void
    {
        $equipment = $this->createFlatRateEquipment();
        PricingRateFactory::createOne([
            'equipment' => $equipment,
            'amount' => 150,
            'durationInDays' => 1,
        ]);

        $this->expectException(InvalidPricingRateException::class);
        $this->expectExceptionMessageIsOrContains('must have exactly one rate without a duration');

        $this->strategy->calculate($equipment, 1);
    }

    public function testItRejectsMoreThanOneRate(): void
    {
        $equipment = $this->createFlatRateEquipment();
        PricingRateFactory::createSequence([
            [
                'equipment' => $equipment,
                'amount' => 150,
                'durationInDays' => null,
            ],
            [
                'equipment' => $equipment,
                'amount' => 100,
                'durationInDays' => null,
            ],
        ]);

        $this->expectException(InvalidPricingRateException::class);
        $this->expectExceptionMessageIsOrContains('must have exactly one rate without a duration');

        $this->strategy->calculate($equipment, 1);
    }

    private function createFlatRateEquipment(): Equipment
    {
        return EquipmentFactory::createOne([
            'pricingModel' => PricingModel::FlatRate,
        ]);
    }
}
