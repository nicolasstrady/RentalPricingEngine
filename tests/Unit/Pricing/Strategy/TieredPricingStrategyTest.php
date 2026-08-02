<?php

namespace App\Tests\Unit\Pricing\Strategy;

use App\Entity\Equipment;
use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use App\Pricing\Calculator\MinimumRentalPriceCalculator;
use App\Pricing\Exception\InvalidPricingRateException;
use App\Pricing\Strategy\TieredPricingStrategy;
use PHPUnit\Framework\TestCase;

final class TieredPricingStrategyTest extends TestCase
{
    private TieredPricingStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new TieredPricingStrategy(new MinimumRentalPriceCalculator());
    }

    public function testItSupportsOnlyTieredPricing(): void
    {
        self::assertTrue($this->strategy->supports(PricingModel::Tiered));
        self::assertFalse($this->strategy->supports(PricingModel::FlatRate));
    }

    public function testItConvertsRatesToOptionsAndCalculatesTheMinimumPrice(): void
    {
        $equipment = $this->createTieredEquipment();
        PricingRateFactory::createSequence([
            [
                'equipment' => $equipment,
                'amount' => 20.10,
                'durationInDays' => 1,
            ],
            [
                'equipment' => $equipment,
                'amount' => 60.55,
                'durationInDays' => 7,
            ],
            [
                'equipment' => $equipment,
                'amount' => 200.99,
                'durationInDays' => 30,
            ],
        ]);

        self::assertSame(80.65, $this->strategy->calculate($equipment, 8));
    }

    public function testItRejectsARateWithoutADuration(): void
    {
        $equipment = $this->createTieredEquipment();
        PricingRateFactory::createOne([
            'equipment' => $equipment,
            'amount' => 20,
            'durationInDays' => null,
        ]);

        $this->expectException(InvalidPricingRateException::class);
        $this->expectExceptionMessageIsOrContains('cannot have a rate without a duration');

        $this->strategy->calculate($equipment, 1);
    }

    public function testItRejectsAnEmptyRateList(): void
    {
        $equipment = $this->createTieredEquipment();

        $this->expectException(InvalidPricingRateException::class);
        $this->expectExceptionMessageIsOrContains('must have at least one rate');

        $this->strategy->calculate($equipment, 1);
    }

    private function createTieredEquipment(): Equipment
    {
        return EquipmentFactory::createOne([
            'pricingModel' => PricingModel::Tiered,
        ]);
    }
}
