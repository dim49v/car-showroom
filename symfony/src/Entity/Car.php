<?php

namespace App\Entity;

use App\Entity\Traits\IdEntity;
use App\Entity\Traits\SelectEntity;
use App\Interfaces\IdInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
use function Symfony\Component\String\u;

/**
 * Car.
 *
 * @ORM\Entity(repositoryClass="App\Repository\CarRepository")
 */
class Car implements IdInterface
{
    use IdEntity;
    use SelectEntity;

    /**
     * @ORM\ManyToOne(targetEntity="CarBrand", inversedBy="cars")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", nullable=false)
     */
    protected CarBrand $brand;

    /**
     * @ORM\Column(type="string", length=256, nullable=false, options={"default": ""})
     */
    protected string $model;

    /**
     * @ORM\ManyToOne(targetEntity="Showroom", inversedBy="cars")
     * @ORM\JoinColumn(name="showroom_id", referencedColumnName="id", nullable=false)
     */
    protected Showroom $showroom;

    /**
     * @ORM\OneToOne(targetEntity="Purchase", mappedBy="car")
     */
    protected ?Purchase $purchase;

    /**
     * @Serializer\Groups({"full"})
     */
    public function getBrand(): CarBrand
    {
        return $this->brand;
    }

    public function setBrand(CarBrand $brand): Car
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): Car
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getShowroom(): Showroom
    {
        return $this->showroom;
    }

    public function setShowroom(Showroom $showroom): Car
    {
        $this->showroom = $showroom;

        return $this;
    }

    public function getPurchase(): ?Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(?Purchase $purchase): Car
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getTitle(): string
    {
        return u(' ')->join([$this->brand->getTitle(), $this->getModel()]);
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function isSold(): bool
    {
        return null !== $this->purchase;
    }
}