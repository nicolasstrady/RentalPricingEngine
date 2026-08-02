<?php

namespace App\Story;

use App\Enum\PricingModel;
use App\Factory\EquipmentFactory;
use App\Factory\PricingRateFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        $drill = EquipmentFactory::createOne([
            'name' => 'Perceuse',
            'pricingModel' => PricingModel::Tiered,
        ]);

        PricingRateFactory::createSequence([
            [
                'equipment' => $drill,
                'durationInDays' => 1,
                'amount' => 20.10,
            ],
            [
                'equipment' => $drill,
                'durationInDays' => 7,
                'amount' => 60.55,
            ],
            [
                'equipment' => $drill,
                'durationInDays' => 30,
                'amount' => 200.99,
            ],
        ]);

        $sander = EquipmentFactory::createOne([
            'name' => 'Ponceuse',
            'pricingModel' => PricingModel::Tiered,
        ]);

        PricingRateFactory::createSequence([
            [
                'equipment' => $sander,
                'durationInDays' => 1,
                'amount' => 10,
            ],
            [
                'equipment' => $sander,
                'durationInDays' => 7,
                'amount' => 90,
            ],
            [
                'equipment' => $sander,
                'durationInDays' => 30,
                'amount' => 250,
            ],
        ]);

        $circularSaw = EquipmentFactory::createOne([
            'name' => 'Scie circulaire',
            'pricingModel' => PricingModel::Tiered,
        ]);

        PricingRateFactory::createSequence([
            [
                'equipment' => $circularSaw,
                'durationInDays' => 1,
                'amount' => 25,
            ],
            [
                'equipment' => $circularSaw,
                'durationInDays' => 7,
                'amount' => 100,
            ],
            [
                'equipment' => $circularSaw,
                'durationInDays' => 30,
                'amount' => 500,
            ],
        ]);

        $pressureWasher = EquipmentFactory::createOne([
            'name' => 'Nettoyeur haute pression',
            'pricingModel' => PricingModel::FlatRate,
        ]);

        PricingRateFactory::createOne([
            'equipment' => $pressureWasher,
            'durationInDays' => null,
            'amount' => 150,
        ]);

        $carpetCleaner = EquipmentFactory::createOne([
            'name' => 'Shampouineuse',
            'pricingModel' => PricingModel::FlatRate,
        ]);

        PricingRateFactory::createOne([
            'equipment' => $carpetCleaner,
            'durationInDays' => null,
            'amount' => 90,
        ]);
    }
}
