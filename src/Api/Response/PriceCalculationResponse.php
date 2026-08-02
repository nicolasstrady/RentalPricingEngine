<?php

namespace App\Api\Response;

final readonly class PriceCalculationResponse
{
    public function __construct(
        public EquipmentSummaryResponse $equipment,
        public string $startDate,
        public string $endDate,
        public int $durationInDays,
        public float $amount,
        public string $currency = 'EUR',
    ) {
    }
}
