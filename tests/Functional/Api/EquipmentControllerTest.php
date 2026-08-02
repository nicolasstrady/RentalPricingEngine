<?php

namespace App\Tests\Functional\Api;

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
            'name' => 'Perceuse',
            'pricingModel' => PricingModel::Tiered,
        ]);
        $pressureWasher = EquipmentFactory::createOne([
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
                        'pricingModel' => PricingModel::FlatRate->value,
                    ],
                    [
                        'id' => $drill->getId(),
                        'name' => 'Perceuse',
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
}
