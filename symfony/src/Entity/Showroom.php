<?php

namespace App\Entity;

use App\Entity\Traits\IdEntity;
use App\Entity\Traits\SelectEntity;
use App\Interfaces\IdInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Showroom.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ShowroomRepository")
 */
class Showroom implements IdInterface
{
    use IdEntity;
    use SelectEntity;

    /**
     * @ORM\Column(type="string", length=256, nullable=false, options={"default": ""})
     */
    protected string $title = '';

    /**
     * @ORM\Column(type="string", length=256, nullable=false, options={"default": ""})
     */
    protected string $address = '';

    /**
     * @var Collection|Car[]
     * @ORM\OneToMany(targetEntity="Car", mappedBy="showroom")
     */
    protected $cars;

    /**
     * @var Collection|User[]
     * @ORM\OneToMany(targetEntity="User", mappedBy="showroom")
     */
    protected $managers;

    public function __construct()
    {
        $this->cars = new ArrayCollection();
        $this->managers = new ArrayCollection();
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Showroom
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): Showroom
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection|User[]
     *
     * @Serializer\Groups({"full"})
     */
    public function getManagers()
    {
        return $this->managers;
    }

    /**
     * @param Collection|User[] $managers
     */
    public function setManagers($managers): Showroom
    {
        $this->managers = $managers;

        return $this;
    }

    /**
     * @return Collection|Car[]
     *
     * @Serializer\Groups({"full"})
     */
    public function getCars()
    {
        return $this->cars;
    }

    /**
     * @param Collection|Car[] $cars
     */
    public function setCars($cars): Showroom
    {
        $this->cars = $cars;

        return $this;
    }
}