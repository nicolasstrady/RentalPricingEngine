<?php

namespace App\Story;

use App\Enum\EquipmentCategory;
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
        $this->createTieredEquipment(EquipmentCategory::Drill, 'Perceuse', 20.10, 60.55, 200.99);
        $this->createTieredEquipment(EquipmentCategory::Drill, 'Perceuse à percussion', 24.90, 79.90, 229.90);
        $this->createTieredEquipment(EquipmentCategory::Drill, 'Perceuse-visseuse sans fil', 18.50, 69.00, 189.00);
        $this->createTieredEquipment(EquipmentCategory::Drill, 'Perceuse SDS+', 32.00, 105.00, 319.00);

        $this->createTieredEquipment(EquipmentCategory::Sander, 'Ponceuse', 10.00, 90.00, 250.00);
        $this->createTieredEquipment(EquipmentCategory::Sander, 'Ponceuse orbitale', 14.50, 72.00, 215.00);
        $this->createTieredEquipment(EquipmentCategory::Sander, 'Ponceuse excentrique', 17.90, 84.90, 249.90);
        $this->createTieredEquipment(EquipmentCategory::Sander, 'Ponceuse à bande', 22.00, 99.00, 289.00);

        $this->createTieredEquipment(EquipmentCategory::CircularSaw, 'Scie circulaire', 25.00, 100.00, 500.00);
        $this->createTieredEquipment(EquipmentCategory::CircularSaw, 'Scie circulaire sur batterie', 29.90, 119.00, 359.00);
        $this->createTieredEquipment(EquipmentCategory::CircularSaw, 'Scie circulaire plongeante', 38.00, 149.00, 429.00);

        $this->createFlatRateEquipment(EquipmentCategory::PressureWasher, 'Nettoyeur haute pression', 150.00);
        $this->createFlatRateEquipment(EquipmentCategory::PressureWasher, 'Nettoyeur haute pression compact', 95.00);
        $this->createFlatRateEquipment(EquipmentCategory::PressureWasher, 'Nettoyeur haute pression professionnel', 210.00);

        $this->createFlatRateEquipment(EquipmentCategory::CarpetCleaner, 'Shampouineuse', 90.00);
        $this->createFlatRateEquipment(EquipmentCategory::CarpetCleaner, 'Shampouineuse canapé', 65.00);
        $this->createFlatRateEquipment(EquipmentCategory::CarpetCleaner, 'Shampouineuse professionnelle', 135.00);
    }

    private function createTieredEquipment(
        EquipmentCategory $category,
        string $name,
        float $dailyRate,
        float $weeklyRate,
        float $monthlyRate,
    ): void {
        $equipment = EquipmentFactory::createOne([
            'category' => $category,
            'name' => $name,
            'pricingModel' => PricingModel::Tiered,
        ]);

        PricingRateFactory::createSequence([
            [
                'equipment' => $equipment,
                'durationInDays' => 1,
                'amount' => $dailyRate,
            ],
            [
                'equipment' => $equipment,
                'durationInDays' => 7,
                'amount' => $weeklyRate,
            ],
            [
                'equipment' => $equipment,
                'durationInDays' => 30,
                'amount' => $monthlyRate,
            ],
        ]);
    }

    private function createFlatRateEquipment(
        EquipmentCategory $category,
        string $name,
        float $amount,
    ): void {
        $equipment = EquipmentFactory::createOne([
            'category' => $category,
            'name' => $name,
            'pricingModel' => PricingModel::FlatRate,
        ]);

        PricingRateFactory::createOne([
            'equipment' => $equipment,
            'durationInDays' => null,
            'amount' => $amount,
        ]);
    }
}
