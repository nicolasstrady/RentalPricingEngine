<?php

namespace App\Tests\Unit\Pricing;

use App\Entity\Equipment;
use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use App\Pricing\Calculator\MinimumRentalPriceCalculator;
use App\Pricing\Exception\UnsupportedPricingModelException;
use App\Pricing\PricingEngine;
use App\Pricing\Strategy\FlatRatePricingStrategy;
use App\Pricing\Strategy\TieredPricingStrategy;
use PHPUnit\Framework\TestCase;

final class PricingEngineTest extends TestCase
{
    private Equipment $equipment;

    protected function setUp(): void
    {
        $this->equipment = EquipmentFactory::createOne([
            'pricingModel' => PricingModel::Tiered,
        ]);
    }

    public function testItDelegatesToTheSupportedStrategy(): void
    {
        PricingRateFactory::createSequence([
            [
                'equipment' => $this->equipment,
                'amount' => 20.10,
                'durationInDays' => 1,
            ],
            [
                'equipment' => $this->equipment,
                'amount' => 60.55,
                'durationInDays' => 7,
            ],
        ]);

        $engine = new PricingEngine([
            new FlatRatePricingStrategy(),
            new TieredPricingStrategy(new MinimumRentalPriceCalculator()),
        ]);

        self::assertSame(80.65, $engine->calculate($this->equipment, 8));
    }

    public function testItRejectsAModelWithoutAStrategy(): void
    {
        $engine = new PricingEngine([]);

        $this->expectException(UnsupportedPricingModelException::class);
        $this->expectExceptionMessageIs('No pricing strategy supports the "tiered" model.');

        $engine->calculate($this->equipment, 1);
    }

    public function testItRejectsANonPositiveDuration(): void
    {
        $engine = new PricingEngine([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Rental duration must be positive.');

        $engine->calculate($this->equipment, 0);
    }
}
