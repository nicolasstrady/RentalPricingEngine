<?php

namespace App\Tests\Functional\Story;

use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use App\Story\AppStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[ResetDatabase]
final class AppStoryTest extends KernelTestCase
{
    public function testItLoadsTheEquipmentCatalogue(): void
    {
        AppStory::load();

        EquipmentFactory::assert()->count(5);
        PricingRateFactory::assert()->count(11);

        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $drill = EquipmentFactory::find(['name' => 'Perceuse']);
        self::assertSame(PricingModel::Tiered, $drill->getPricingModel());
        self::assertCount(3, $drill->getPricingRates());

        $sander = EquipmentFactory::find(['name' => 'Ponceuse']);
        self::assertSame(PricingModel::Tiered, $sander->getPricingModel());
        self::assertCount(3, $sander->getPricingRates());

        $circularSaw = EquipmentFactory::find(['name' => 'Scie circulaire']);
        self::assertSame(PricingModel::Tiered, $circularSaw->getPricingModel());
        self::assertCount(3, $circularSaw->getPricingRates());

        $pressureWasher = EquipmentFactory::find(['name' => 'Nettoyeur haute pression']);
        self::assertSame(PricingModel::FlatRate, $pressureWasher->getPricingModel());
        self::assertCount(1, $pressureWasher->getPricingRates());

        $flatRate = $pressureWasher->getPricingRates()->first();
        self::assertNotFalse($flatRate);
        self::assertNull($flatRate->getDurationInDays());
        self::assertSame(150, $flatRate->getAmount());

        $carpetCleaner = EquipmentFactory::find(['name' => 'Shampouineuse']);
        self::assertSame(PricingModel::FlatRate, $carpetCleaner->getPricingModel());
        self::assertCount(1, $carpetCleaner->getPricingRates());
    }
}
