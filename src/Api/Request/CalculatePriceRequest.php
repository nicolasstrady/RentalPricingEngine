<?php

namespace App\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CalculatePriceRequest
{
    public function __construct(
        #[Assert\Date(message: 'Start date must use the YYYY-MM-DD format.')]
        public string $startDate,
        #[Assert\Date(message: 'End date must use the YYYY-MM-DD format.')]
        #[Assert\GreaterThanOrEqual(
            propertyPath: 'startDate',
            message: 'End date must be on or after start date.',
        )]
        public string $endDate,
    ) {
    }
}
