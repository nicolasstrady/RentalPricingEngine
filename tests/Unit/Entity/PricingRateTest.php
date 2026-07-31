<?php

namespace App\Tests\Unit\Entity;

use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PricingRateTest extends TestCase
{
    public function testItAcceptsAFlatRate(): void
    {
        $equipment = EquipmentFactory::createOne([
            'pricingModel' => PricingModel::FlatRate,
        ]);
        $rate = PricingRateFactory::createOne([
            'equipment' => $equipment,
            'amount' => 150,
            'durationInDays' => null,
        ]);

        self::assertNull($rate->getDurationInDays());
        self::assertSame(150, $rate->getAmount());
    }

    /** @return iterable<string, array{int, int|null, string}> */
    public static function invalidValues(): iterable
    {
        yield 'negative amount' => [-1, 1, 'Pricing amount cannot be negative.'];
        yield 'zero duration' => [100, 0, 'Pricing duration must be positive or null.'];
        yield 'negative duration' => [100, -1, 'Pricing duration must be positive or null.'];
    }

    #[DataProvider('invalidValues')]
    public function testItRejectsInvalidValues(int $amount, ?int $durationInDays, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs($message);

        PricingRateFactory::createOne([
            'amount' => $amount,
            'durationInDays' => $durationInDays,
        ]);
    }
}
