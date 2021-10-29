<?php

namespace App\Normalizer;

use App\Controller\BaseController;
use App\Entity\Car;
use App\Entity\Enum\RoleEnum;
use App\Entity\Showroom;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ShowroomNormalizer extends CustomObjectNormalizer
{
    /**
     * ShowroomNormalizer constructor.
     */
    public function __construct(ObjectNormalizer $normalizer, EntityManagerInterface $entityManager)
    {
        parent::__construct($normalizer, $entityManager);
        $this->entityName = Showroom::class;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if (!isset($context[AbstractNormalizer::CALLBACKS])) {
            $context[AbstractNormalizer::CALLBACKS] = [];
        }

        $context[AbstractNormalizer::CALLBACKS]['cars'] = static function (Collection $attributeValue): array {
            return $attributeValue->map(
                fn(Car $car) => $car->asSelectableArray()
            )->getValues();
        };
        $context[AbstractNormalizer::CALLBACKS]['managers'] = static function (Collection $attributeValue): array {
            return $attributeValue->map(
                fn(User $user) => $user->asSelectableArray()
            )->getValues();
        };

        $data = parent::normalize($object, $format, $context);

        if (isset($context[BaseController::AUTH_USER])
            && empty(array_intersect(
                $context[BaseController::AUTH_USER]->getRoles(),
                [RoleEnum::ROLE_MANAGER, RoleEnum::ROLE_DIRECTOR]
            ))
        ) {
            unset($data['cars']);
            unset($data['managers']);
        }

        return $data;
    }
}
