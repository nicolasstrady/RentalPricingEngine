<?php

namespace App\Entity;

use App\Repository\PricingRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PricingRateRepository::class)]
#[ORM\Table(name: 'pricing_rate')]
#[ORM\Index(columns: ['equipment_id'], name: 'idx_pricing_rate_equipment')]
#[ORM\UniqueConstraint(name: 'uniq_pricing_rate_duration', columns: ['equipment_id', 'duration_in_days'])]
class PricingRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pricingRates')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Equipment $equipment;

    #[ORM\Column(nullable: true)]
    private ?int $durationInDays;

    #[ORM\Column(type: Types::FLOAT)]
    private float $amount;

    public function __construct(Equipment $equipment, float $amount, ?int $durationInDays)
    {
        $this->setAmount($amount);
        $this->setDurationInDays($durationInDays);
        $equipment->addPricingRate($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipment(): Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(Equipment $equipment): void
    {
        $this->equipment = $equipment;
    }

    public function getDurationInDays(): ?int
    {
        return $this->durationInDays;
    }

    public function setDurationInDays(?int $durationInDays): void
    {
        if (null !== $durationInDays && $durationInDays <= 0) {
            throw new \InvalidArgumentException('Pricing duration must be positive or null.');
        }

        $this->durationInDays = $durationInDays;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        if (!is_finite($amount)) {
            throw new \InvalidArgumentException('Pricing amount must be finite.');
        }

        if ($amount < 0) {
            throw new \InvalidArgumentException('Pricing amount cannot be negative.');
        }

        $this->amount = round($amount, 2, PHP_ROUND_HALF_UP);
    }
}
