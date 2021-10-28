<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait IdEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Serializer\Groups({"full"})
     */
    public function getId(): ?int
    {
        return isset($this->id) ? (int) $this->id : null;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
