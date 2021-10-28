<?php

namespace App\Provider;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    protected EntityManagerInterface $entityManager;

    /**
     * UserProvider constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): ?User
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername(string $username): User
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $identifier): User
    {
        if (empty($identifier)) {
            throw $this->createIdentifierNotFoundException($identifier);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneByIdentifier($identifier);
        if (!($user instanceof User)) {
            throw $this->createIdentifierNotFoundException($identifier);
        }
        return $user;
    }

    protected function createIdentifierNotFoundException(string $username): UserNotFoundException
    {
        $exception = new UserNotFoundException("Phone {$username} not found.");
        $exception->setUserIdentifier($username);

        return $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}
