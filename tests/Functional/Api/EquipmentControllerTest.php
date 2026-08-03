<?php

namespace App\Tests\Functional\Api;

use App\Enum\EquipmentCategory;
use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[ResetDatabase]
final class EquipmentControllerTest extends WebTestCase
{
    public function testItListsEquipmentWithoutRates(): void
    {
        $client = self::createClient();
        $drill = EquipmentFactory::createOne([
            'category' => EquipmentCategory::Drill,
            'name' => 'Perceuse',
            'pricingModel' => PricingModel::Tiered,
        ]);
        $pressureWasher = EquipmentFactory::createOne([
            'category' => EquipmentCategory::PressureWasher,
            'name' => 'Nettoyeur haute pression',
            'pricingModel' => PricingModel::FlatRate,
        ]);
        PricingRateFactory::createOne([
            'equipment' => $drill,
            'amount' => 20,
            'durationInDays' => 1,
        ]);

        $client->request('GET', '/api/equipments');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'items' => [
                    [
                        'id' => $pressureWasher->getId(),
                        'name' => 'Nettoyeur haute pression',
                        'category' => EquipmentCategory::PressureWasher->value,
                        'pricingModel' => PricingModel::FlatRate->value,
                    ],
                    [
                        'id' => $drill->getId(),
                        'name' => 'Perceuse',
                        'category' => EquipmentCategory::Drill->value,
                        'pricingModel' => PricingModel::Tiered->value,
                    ],
                ],
                'count' => 2,
            ], JSON_THROW_ON_ERROR),
            (string) $client->getResponse()->getContent(),
        );
        self::assertStringNotContainsString('rates', (string) $client->getResponse()->getContent());
    }

    public function testItReturnsEquipmentDetailsWithRates(): void
    {
        $client = self::createClient();
        $equipment = EquipmentFactory::createOne([
            'category' => EquipmentCategory::Drill,
            'name' => 'Perceuse',
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
        $equipmentId = $equipment->getId();
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $client->request('GET', sprintf('/api/equipments/%d', $equipmentId));

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'id' => $equipmentId,
                'name' => 'Perceuse',
                'category' => EquipmentCategory::Drill->value,
                'pricingModel' => PricingModel::Tiered->value,
                'rates' => [
                    ['durationInDays' => 1, 'amount' => 20.10],
                    ['durationInDays' => 7, 'amount' => 60.55],
                    ['durationInDays' => 30, 'amount' => 200.99],
                ],
            ], JSON_THROW_ON_ERROR),
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testItReturnsNotFoundForUnknownEquipmentDetails(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/equipments/999999');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertResponseFormatSame('json');
    }

    public function testItListsOnlyEquipmentFromTheRequestedCategory(): void
    {
        $client = self::createClient();
        $drill = EquipmentFactory::createOne([
            'category' => EquipmentCategory::Drill,
            'name' => 'Perceuse à percussion',
            'pricingModel' => PricingModel::Tiered,
        ]);
        EquipmentFactory::createOne([
            'category' => EquipmentCategory::Sander,
            'name' => 'Ponceuse orbitale',
        ]);

        $client->request('GET', '/api/equipments?category=drill');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'items' => [[
                    'id' => $drill->getId(),
                    'name' => 'Perceuse à percussion',
                    'category' => EquipmentCategory::Drill->value,
                    'pricingModel' => PricingModel::Tiered->value,
                ]],
                'count' => 1,
            ], JSON_THROW_ON_ERROR),
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testItListsTheAvailableCategories(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/equipments/categories');

        self::assertResponseIsSuccessful();
        /** @var array{items: list<array{value: string, label: string}>} $response */
        $response = json_decode(
            (string) $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertCount(5, $response['items']);
        self::assertSame(['value' => 'drill', 'label' => 'Perceuse'], $response['items'][0]);
        self::assertSame(['value' => 'sander', 'label' => 'Ponceuse'], $response['items'][1]);
    }
}
