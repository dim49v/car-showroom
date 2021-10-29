<?php

namespace App\Entity;

use App\Entity\Enum\RoleEnum;
use App\Entity\Traits\IdEntity;
use App\Entity\Traits\SelectEntity;
use App\Interfaces\IdInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation as Serializer;
use function Symfony\Component\String\u;

/**
 * User.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements IdInterface, UserInterface
{
    use IdEntity;
    use SelectEntity;

    /**
     * @ORM\Column(type="string", length=100, nullable=false, options={"default": ""})
     */
    protected string $firstName = '';

    /**
     * @ORM\Column(type="string", length=100, nullable=false, options={"default": ""})
     */
    protected string $lastName = '';

    /**
     * @ORM\Column(type="string", length=100, nullable=false, options={"default": ""})
     */
    protected string $patronymic = '';

    /**
     * @ORM\Column(type="string", length=20, nullable=false, unique=true, options={"default": ""})
     */
    protected string $phone = '';

    /**
     * @ORM\Column(type="string", length=100, nullable=false, options={"default": ""})
     */
    protected string $password = '';

    protected ?string $plainPassword = null;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    protected bool $manager = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    protected bool $director = false;

    /**
     * @ORM\ManyToOne(targetEntity="Showroom", inversedBy="managers")
     * @ORM\JoinColumn(name="showroom_id", referencedColumnName="id")
     */
    protected ?Showroom $showroom = null;

    /**
     * @ORM\OneToMany(targetEntity="Purchase", mappedBy="customer")
     */
    protected $purchases;

    /**
     * @ORM\OneToMany(targetEntity="Purchase", mappedBy="manager")
     */
    protected $purchasesAsManager;

    protected ?CacheItemInterface $token = null;

    /**
     * @Serializer\Groups({"full", "login", "register"})
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @Serializer\Groups({"full", "register"})
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @Serializer\Groups({"full", "register"})
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @Serializer\Groups({"full", "register"})
     */
    public function getPatronymic(): string
    {
        return $this->patronymic;
    }

    public function setPatronymic(string $patronymic): User
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * @Serializer\Groups({"full", "register"})
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): User
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function isManager(): bool
    {
        return $this->manager;
    }

    public function setManager(bool $manager): User
    {
        $this->manager = $manager;

        return $this;
    }

    public function isDirector(): bool
    {
        return $this->director;
    }

    public function setDirector(bool $director): User
    {
        $this->director = $director;

        return  $this;
    }

    /**
     * @Serializer\Groups({"full"})
     */
    public function getShowroom(): ?Showroom
    {
        return $this->showroom;
    }

    public function setShowroom(?Showroom $showrooms): User
    {
        $this->showroom = $showrooms;

        return $this;
    }

    /**
     * @return Collection|Purchase[]
     */
    public function getPurchases()
    {
        return $this->purchases;
    }

    /**
     * @param Collection|Purchase[] $purchases
     */
    public function setPurchases($purchases): User
    {
        $this->purchases = $purchases;

        return $this;
    }

    /**
     * @return Collection|Purchase[]
     */
    public function getPurchasesAsManager()
    {
        return $this->purchasesAsManager;
    }

    /**
     * @param Collection|Purchase[] $purchasesAsManager
     */
    public function setPurchasesAsManager($purchasesAsManager): User
    {
        $this->purchasesAsManager = $purchasesAsManager;

        return $this;
    }

    /**
     * @Serializer\Groups({"full", "register", "login"})
     */
    public function getTitle(): string {
        return u(' ')->join([$this->lastName, $this->firstName, $this->patronymic]);
    }

    /**
     * @Serializer\Groups({"login"})
     * @OA\Property(
     *     type="array",
     *     @OA\Items( type="string")
     * )
     */
    public function getRoles(): array
    {
        $roles = [RoleEnum::ROLE_GUEST];

        if ($this->isDirector()) {
            $roles[] = RoleEnum::ROLE_DIRECTOR;
        }
        if ($this->isManager()) {
            $roles[] = RoleEnum::ROLE_MANAGER;
        }

        return $roles;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
    public function getUserIdentifier(): string
    {
        return $this->phone;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getToken(): ?CacheItemInterface
    {
        return $this->token;
    }

    public function setToken(CacheItemInterface $token): User
    {
        $this->token = $token;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}