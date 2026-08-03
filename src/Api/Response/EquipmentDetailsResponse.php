<?php

namespace App\Api\Response;

use App\Entity\Equipment;

final readonly class EquipmentDetailsResponse
{
    /** @param list<PricingRateResponse> $rates */
    public function __construct(
        public int $id,
        public string $name,
        public string $category,
        public string $pricingModel,
        public array $rates,
    ) {
    }

    public static function fromEntity(Equipment $equipment): self
    {
        $summary = EquipmentSummaryResponse::fromEntity($equipment);

        return new self(
            id: $summary->id,
            name: $summary->name,
            category: $summary->category,
            pricingModel: $summary->pricingModel,
            rates: array_values(
                array_map(
                    PricingRateResponse::fromEntity(...),
                    $equipment->getPricingRates()->toArray(),
                ),
            ),
        );
    }
}
