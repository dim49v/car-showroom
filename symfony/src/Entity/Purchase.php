<?php

namespace App\Entity;

use App\Entity\Traits\IdEntity;
use App\Interfaces\IdInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Purchase.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PurchaseRepository")
 */
class Purchase implements IdInterface, Timestampable
{
    use IdEntity;
    use TimestampableEntity;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="purchases")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected User $customer;

    /**
     * @ORM\OneToOne(targetEntity="Car", inversedBy="purchase")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", nullable=false)
     */
    protected Car $car;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="purchasesAsManager")
     * @ORM\JoinColumn(name="user_manager_id", referencedColumnName="id", nullable=false)
     */
    protected User $manager;

    /**
     * @Serializer\Groups({"full"})
     */
    public function getCustomer(): User
    {
        return $this->customer;
    }

    public function setCustomer(User $customer): Purchase
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getCar(): Car
    {
        return $this->car;
    }

    public function setCar(Car $car): Purchase
    {
        $this->car = $car;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getManager(): User
    {
        return $this->manager;
    }

    public function setManager(User $manager): Purchase
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createAt): Purchase
    {
        $this->createdAt = $createAt;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): Purchase
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getShowroom(): Showroom
    {
        return $this->car->getShowroom();
    }
}