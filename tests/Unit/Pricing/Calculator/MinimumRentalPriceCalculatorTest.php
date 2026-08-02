<?php

namespace App\Tests\Unit\Pricing\Calculator;

use App\Pricing\Calculator\MinimumRentalPriceCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MinimumRentalPriceCalculatorTest extends TestCase
{
    /** @return iterable<string, array{int, float}> */
    public static function minimumPrices(): iterable
    {
        yield 'one day' => [1, 20.10];
        yield 'two daily packages' => [2, 40.20];
        yield 'weekly package covers five days' => [5, 60.55];
        yield 'one weekly package' => [7, 60.55];
        yield 'weekly and daily packages' => [8, 80.65];
        yield 'monthly package covers extra days' => [29, 200.99];
        yield 'monthly and daily packages' => [31, 221.09];
    }

    #[DataProvider('minimumPrices')]
    public function testItFindsTheMinimumPrice(int $requestedDays, float $expectedAmount): void
    {
        $options = [
            ['durationInDays' => 1, 'amount' => 20.10],
            ['durationInDays' => 7, 'amount' => 60.55],
            ['durationInDays' => 30, 'amount' => 200.99],
        ];

        self::assertSame($expectedAmount, (new MinimumRentalPriceCalculator())->calculate($requestedDays, $options));
    }

    public function testItCanPreferSeveralShortPackagesOverALongerPackage(): void
    {
        $options = [
            ['durationInDays' => 1, 'amount' => 10.05],
            ['durationInDays' => 7, 'amount' => 90.0],
        ];

        self::assertSame(70.35, (new MinimumRentalPriceCalculator())->calculate(7, $options));
    }

    public function testItSkipsUnreachableIntermediateDurations(): void
    {
        $options = [
            ['durationInDays' => 2, 'amount' => 10.25],
        ];

        self::assertSame(20.50, (new MinimumRentalPriceCalculator())->calculate(3, $options));
    }

    public function testItRoundsComplexCalculationsToTwoDecimalPlaces(): void
    {
        $options = [
            ['durationInDays' => 1, 'amount' => 0.10],
        ];

        self::assertSame(0.30, (new MinimumRentalPriceCalculator())->calculate(3, $options));
    }

    public function testItRejectsANonPositiveRequestedDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Requested duration must be positive.');

        (new MinimumRentalPriceCalculator())->calculate(0, [['durationInDays' => 1, 'amount' => 20.0]]);
    }

    public function testItRejectsAnEmptyOptionList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('At least one pricing option is required.');

        (new MinimumRentalPriceCalculator())->calculate(1, []);
    }

    /** @return iterable<string, array{array{durationInDays: int, amount: float}, string}> */
    public static function invalidOptions(): iterable
    {
        yield 'zero duration' => [['durationInDays' => 0, 'amount' => 20.0], 'Pricing option duration must be positive.'];
        yield 'negative amount' => [['durationInDays' => 1, 'amount' => -1.0], 'Pricing option amount cannot be negative.'];
        yield 'infinite amount' => [['durationInDays' => 1, 'amount' => INF], 'Pricing option amount must be finite.'];
    }

    /** @param array{durationInDays: int, amount: float} $option */
    #[DataProvider('invalidOptions')]
    public function testItRejectsAnInvalidOption(array $option, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs($message);

        (new MinimumRentalPriceCalculator())->calculate(1, [$option]);
    }
}
