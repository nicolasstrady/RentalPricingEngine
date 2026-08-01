<?php

namespace App\Api\Response;

final readonly class EquipmentListResponse
{
    /** @param list<EquipmentSummaryResponse> $items */
    public function __construct(
        public array $items,
        public int $count,
    ) {
    }
}
