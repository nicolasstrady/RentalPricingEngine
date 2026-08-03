<?php

namespace App\Controller;

use App\Api\Response\EquipmentCategoryResponse;
use App\Api\Response\EquipmentDetailsResponse;
use App\Api\Response\EquipmentListResponse;
use App\Api\Response\EquipmentSummaryResponse;
use App\Entity\Equipment;
use App\Enum\EquipmentCategory;
use App\Repository\EquipmentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/equipments', name: 'api_equipment_')]
final class EquipmentController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'], format: 'json')]
    public function list(Request $request, EquipmentRepository $equipmentRepository): JsonResponse
    {
        $criteria = [];
        $categoryValue = $request->query->getString('category');

        if ('' !== $categoryValue) {
            $category = EquipmentCategory::tryFrom($categoryValue);

            if (null === $category) {
                throw new BadRequestHttpException('Unknown equipment category.');
            }

            $criteria['category'] = $category;
        }

        $items = array_map(
            EquipmentSummaryResponse::fromEntity(...),
            $equipmentRepository->findBy($criteria, ['name' => 'ASC']),
        );

        return $this->json(new EquipmentListResponse($items, count($items)));
    }

    #[Route('/categories', name: 'categories', methods: ['GET'], format: 'json')]
    public function categories(): JsonResponse
    {
        return $this->json([
            'items' => array_map(EquipmentCategoryResponse::fromEnum(...), EquipmentCategory::cases()),
        ]);
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
