<?php

namespace App\Entity;

use App\Entity\Traits\IdEntity;
use App\Entity\Traits\SelectEntity;
use App\Interfaces\IdInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * CarBrand.
 *
 * @ORM\Entity(repositoryClass="App\Repository\CarBrandRepository")
 */
class CarBrand implements IdInterface
{
    use IdEntity;
    use SelectEntity;

    /**
     * @ORM\Column(type="string", length=256, nullable=false, options={"default": ""})
     */
    protected string $title = '';

    /**
     * @var Collection|Car[]
     * @ORM\OneToMany(targetEntity="Car", mappedBy="brand")
     */
    protected $cars;

    public function __construct()
    {
        $this->cars = new ArrayCollection();
    }

    /**
     * @Serializer\Groups({"full", "select"})
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): CarBrand
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Car[]
     *
     * @Serializer\Groups({"full"})
     * @OA\Property(
     *     type="array",
     *     @OA\Items(
     *          type="object",
     *          @OA\Property(property="title", type="string"),
     *          @OA\Property(property="value", type="integer"),
     *     )
     * )
     */
    public function getCars()
    {
        return $this->cars;
    }

    /**
     * @param Collection|Car[] $cars
     */
    public function setCars($cars): CarBrand
    {
        $this->cars = $cars;

        return $this;
    }
}