<?php

namespace App\Controller;

use App\Api\Response\EquipmentDetailsResponse;
use App\Api\Response\EquipmentListResponse;
use App\Api\Response\EquipmentSummaryResponse;
use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/equipments', name: 'api_equipment_')]
final class EquipmentController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'], format: 'json')]
    public function list(EquipmentRepository $equipmentRepository): JsonResponse
    {
        $items = array_map(
            EquipmentSummaryResponse::fromEntity(...),
            $equipmentRepository->findBy([], ['name' => 'ASC']),
        );

        return $this->json(new EquipmentListResponse($items, count($items)));
    }

    #[Route(
        '/{equipmentId}',
        name: 'details',
        requirements: ['equipmentId' => '\d+'],
        methods: ['GET'],
        format: 'json',
    )]
    public function details(
        #[MapEntity(id: 'equipmentId', message: 'Equipment not found.')]
        Equipment $equipment,
    ): JsonResponse {
        return $this->json(EquipmentDetailsResponse::fromEntity($equipment));
    }
}
