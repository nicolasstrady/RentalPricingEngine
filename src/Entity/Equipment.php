<?php

namespace App\Entity;

use App\Enum\PricingModel;
use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[ORM\Table(name: 'equipment')]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 20, enumType: PricingModel::class)]
    private PricingModel $pricingModel;

    /** @var Collection<int, PricingRate> */
    #[ORM\OneToMany(
        targetEntity: PricingRate::class,
        mappedBy: 'equipment',
        cascade: ['persist'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['durationInDays' => 'ASC'])]
    private Collection $pricingRates;

    public function __construct(string $name, PricingModel $pricingModel)
    {
        $this->pricingRates = new ArrayCollection();
        $this->setName($name);
        $this->pricingModel = $pricingModel;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $name = trim($name);

        if ('' === $name) {
            throw new \InvalidArgumentException('Equipment name cannot be empty.');
        }

        $this->name = $name;
    }

    public function getPricingModel(): PricingModel
    {
        return $this->pricingModel;
    }

    public function setPricingModel(PricingModel $pricingModel): void
    {
        $this->pricingModel = $pricingModel;
    }

    /** @return Collection<int, PricingRate> */
    public function getPricingRates(): Collection
    {
        return $this->pricingRates;
    }

    public function addPricingRate(PricingRate $pricingRate): void
    {
        if ($this->pricingRates->contains($pricingRate)) {
            return;
        }

        $this->pricingRates->add($pricingRate);
        $pricingRate->setEquipment($this);
    }

    public function removePricingRate(PricingRate $pricingRate): void
    {
        $this->pricingRates->removeElement($pricingRate);
    }
}
