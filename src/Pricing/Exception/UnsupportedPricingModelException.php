<?php

namespace App\Pricing\Exception;

use App\Enum\PricingModel;

final class UnsupportedPricingModelException extends \LogicException
{
    public static function forModel(PricingModel $pricingModel): self
    {
        return new self(sprintf('No pricing strategy supports the "%s" model.', $pricingModel->value));
    }
}
