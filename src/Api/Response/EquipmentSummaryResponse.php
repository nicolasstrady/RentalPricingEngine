<?php

namespace App\Api\Response;

use App\Entity\Equipment;

final readonly class EquipmentSummaryResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $pricingModel,
    ) {
    }

    public static function fromEntity(Equipment $equipment): self
    {
        $id = $equipment->getId();

        if (null === $id) {
            throw new \LogicException('Cannot expose an equipment without an identifier.');
        }

        return new self(
            id: $id,
            name: $equipment->getName(),
            pricingModel: $equipment->getPricingModel()->value,
        );
    }
}
