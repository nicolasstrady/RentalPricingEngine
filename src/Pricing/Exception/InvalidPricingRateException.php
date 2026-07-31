<?php

namespace App\Pricing\Exception;

final class InvalidPricingRateException extends \DomainException
{
    public static function invalidFlatRate(string $equipmentName): self
    {
        return new self(sprintf('Flat-rate equipment "%s" must have exactly one rate without a duration.', $equipmentName));
    }

    public static function missingTieredDuration(string $equipmentName): self
    {
        return new self(sprintf('Tiered equipment "%s" cannot have a rate without a duration.', $equipmentName));
    }

    public static function missingTieredRates(string $equipmentName): self
    {
        return new self(sprintf('Tiered equipment "%s" must have at least one rate.', $equipmentName));
    }
}
