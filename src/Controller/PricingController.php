<?php

namespace App\Controller;

use App\Api\Request\CalculatePriceRequest;
use App\Api\Response\EquipmentSummaryResponse;
use App\Api\Response\PriceCalculationResponse;
use App\Entity\Equipment;
use App\Pricing\PricingEngine;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class PricingController extends AbstractController
{
    public function __construct(private readonly PricingEngine $pricingEngine)
    {
    }

    #[Route(
        '/api/equipments/{equipmentId}/pricing/calculate',
        name: 'api_pricing_calculate',
        requirements: ['equipmentId' => '\d+'],
        methods: ['POST'],
        format: 'json',
    )]
    public function __invoke(
        #[MapEntity(id: 'equipmentId', message: 'Equipment not found.')]
        Equipment $equipment,
        #[MapRequestPayload(acceptFormat: 'json')]
        CalculatePriceRequest $request,
    ): JsonResponse {
        $startDate = new \DateTimeImmutable($request->startDate);
        $endDate = new \DateTimeImmutable($request->endDate);
        $durationInDays = (int) $startDate->diff($endDate)->days + 1;

        return $this->json(new PriceCalculationResponse(
            equipment: EquipmentSummaryResponse::fromEntity($equipment),
            startDate: $request->startDate,
            endDate: $request->endDate,
            durationInDays: $durationInDays,
            amount: $this->pricingEngine->calculate($equipment, $durationInDays),
        ));
    }
}
