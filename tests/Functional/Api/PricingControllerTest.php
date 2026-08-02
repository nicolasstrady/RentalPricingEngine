<?php

namespace App\Tests\Functional\Api;

use App\Entity\Equipment;
use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[ResetDatabase]
final class PricingControllerTest extends WebTestCase
{
    public function testItCalculatesATieredRentalPrice(): void
    {
        $client = self::createClient();
        $equipment = $this->createTieredEquipment();
        $equipmentId = $equipment->getId();
        $equipmentName = $equipment->getName();
        $this->clearEntityManager();

        $client->jsonRequest('POST', sprintf('/api/equipments/%d/pricing/calculate', $equipmentId), [
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-08',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'equipment' => [
                    'id' => $equipmentId,
                    'name' => $equipmentName,
                    'pricingModel' => PricingModel::Tiered->value,
                ],
                'startDate' => '2026-08-01',
                'endDate' => '2026-08-08',
                'durationInDays' => 8,
                'amount' => 80.65,
                'currency' => 'EUR',
            ], JSON_THROW_ON_ERROR),
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testItCalculatesAFlatRate(): void
    {
        $client = self::createClient();
        $equipment = EquipmentFactory::createOne([
            'pricingModel' => PricingModel::FlatRate,
        ]);
        PricingRateFactory::createOne([
            'equipment' => $equipment,
            'amount' => 150.75,
            'durationInDays' => null,
        ]);
        $equipmentId = $equipment->getId();
        $equipmentName = $equipment->getName();
        $this->clearEntityManager();
        $client->jsonRequest('POST', sprintf('/api/equipments/%d/pricing/calculate', $equipmentId), [
            'startDate' => '2026-08-01',
            'endDate' => '2026-09-11',
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'equipment' => [
                    'id' => $equipmentId,
                    'name' => $equipmentName,
                    'pricingModel' => PricingModel::FlatRate->value,
                ],
                'startDate' => '2026-08-01',
                'endDate' => '2026-09-11',
                'durationInDays' => 42,
                'amount' => 150.75,
                'currency' => 'EUR',
            ], JSON_THROW_ON_ERROR),
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testItReturnsNotFoundForAnUnknownEquipment(): void
    {
        $client = self::createClient();

        $client->jsonRequest('POST', '/api/equipments/999999/pricing/calculate', [
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-01',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertResponseFormatSame('json');
    }

    public function testItRejectsAnInvalidDate(): void
    {
        $client = self::createClient();
        $equipment = EquipmentFactory::createOne();

        $client->jsonRequest('POST', sprintf('/api/equipments/%d/pricing/calculate', $equipment->getId()), [
            'startDate' => '2026-02-30',
            'endDate' => '2026-03-01',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertStringContainsString(
            'Start date must use the YYYY-MM-DD format.',
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testItRejectsAnEndDateBeforeTheStartDate(): void
    {
        $client = self::createClient();
        $equipment = EquipmentFactory::createOne();

        $client->jsonRequest('POST', sprintf('/api/equipments/%d/pricing/calculate', $equipment->getId()), [
            'startDate' => '2026-08-08',
            'endDate' => '2026-08-01',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertStringContainsString(
            'End date must be on or after start date.',
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testItRejectsAMalformedPayload(): void
    {
        $client = self::createClient();
        $equipment = EquipmentFactory::createOne();

        $client->jsonRequest('POST', sprintf('/api/equipments/%d/pricing/calculate', $equipment->getId()), [
            'endDate' => '2026-08-01',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function createTieredEquipment(): Equipment
    {
        $equipment = EquipmentFactory::createOne([
            'pricingModel' => PricingModel::Tiered,
        ]);
        PricingRateFactory::createSequence([
            [
                'equipment' => $equipment,
                'amount' => 20.10,
                'durationInDays' => 1,
            ],
            [
                'equipment' => $equipment,
                'amount' => 60.55,
                'durationInDays' => 7,
            ],
            [
                'equipment' => $equipment,
                'amount' => 200.99,
                'durationInDays' => 30,
            ],
        ]);

        return $equipment;
    }

    private function clearEntityManager(): void
    {
        self::getContainer()->get(EntityManagerInterface::class)->clear();
    }
}
