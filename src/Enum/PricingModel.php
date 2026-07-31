<?php

namespace App\Enum;

enum PricingModel: string
{
    case FlatRate = 'flat_rate';
    case Tiered = 'tiered';
}
