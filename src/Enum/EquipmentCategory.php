<?php

namespace App\Enum;

enum EquipmentCategory: string
{
    case Drill = 'drill';
    case Sander = 'sander';
    case CircularSaw = 'circular_saw';
    case PressureWasher = 'pressure_washer';
    case CarpetCleaner = 'carpet_cleaner';

    public function label(): string
    {
        return match ($this) {
            self::Drill => 'Perceuse',
            self::Sander => 'Ponceuse',
            self::CircularSaw => 'Scie circulaire',
            self::PressureWasher => 'Nettoyeur haute pression',
            self::CarpetCleaner => 'Shampouineuse',
        };
    }
}
