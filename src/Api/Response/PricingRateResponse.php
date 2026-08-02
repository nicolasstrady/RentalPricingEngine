<?php

namespace App\Api\Response;

use App\Entity\PricingRate;

final readonly class PricingRateResponse
{
    public function __construct(
        public ?int $durationInDays,
        public float $amount,
    ) {
    }

    public static function fromEntity(PricingRate $pricingRate): self
    {
        return new self(
            durationInDays: $pricingRate->getDurationInDays(),
            amount: $pricingRate->getAmount(),
        );
    }
}
