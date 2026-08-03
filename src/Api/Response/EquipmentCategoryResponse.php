<?php

namespace App\Api\Response;

use App\Enum\EquipmentCategory;

final readonly class EquipmentCategoryResponse
{
    public function __construct(
        public string $value,
        public string $label,
    ) {
    }

    public static function fromEnum(EquipmentCategory $category): self
    {
        return new self($category->value, $category->label());
    }
}
