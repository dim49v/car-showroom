<?php

namespace App\Normalizer;

use App\Entity\Car;
use App\Entity\CarBrand;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CarBrandNormalizer extends CustomObjectNormalizer
{
    /**
     * CarBrandNormalizer constructor.
     */
    public function __construct(ObjectNormalizer $normalizer, EntityManagerInterface $entityManager)
    {
        parent::__construct($normalizer, $entityManager);
        $this->entityName = CarBrand::class;
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

        return parent::normalize($object, $format, $context);
    }
}
