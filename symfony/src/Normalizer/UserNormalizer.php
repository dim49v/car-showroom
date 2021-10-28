<?php

namespace App\Normalizer;

use App\Entity\Showroom;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer extends CustomObjectNormalizer
{
    protected UserPasswordHasherInterface $passwordHasher;

    /**
     * UserNormalizer constructor.
     */
    public function __construct(ObjectNormalizer $normalizer, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($normalizer, $entityManager);
        $this->entityName = User::class;
        $this->passwordHasher = $passwordHasher;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if (!isset($context[AbstractNormalizer::CALLBACKS])) {
            $context[AbstractNormalizer::CALLBACKS] = [];
        }

        $context[AbstractNormalizer::CALLBACKS]['showroom'] = static function (?Showroom $attributeValue): ?array {
            return $attributeValue?->asSelectableArray();
        };

        return parent::normalize($object, $format, $context);
    }

    public function denormalize($data, $class, string $format = null, array $context = [])
    {
        if (!is_array($data)) {
            return parent::denormalize($data, $class, $format, $context);
        }

        if (isset($data['password'])) {
            $data['plainPassword'] = $data['password'];
            unset($data['password']);
        }

        if (isset($data['newPassword'])) {
            $data['plainPassword'] = $data['newPassword'];
            unset($data['newPassword']);
        }

        /** @var User $object */
        $object = parent::denormalize($data, $class, $format, $context);

        if (null !== $object->getPlainPassword()) {
            $encodedPassword = $this->passwordHasher->hashPassword($object, $object->getPlainPassword());
            $object->setPassword($encodedPassword);
        }

        return $object;
    }
}
