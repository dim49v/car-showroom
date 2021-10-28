<?php

namespace App\Normalizer;

use App\Entity\Car;
use App\Entity\CarBrand;
use App\Entity\Showroom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CarNormalizer extends CustomObjectNormalizer
{
    /**
     * CarNormalizer constructor.
     */
    public function __construct(ObjectNormalizer $normalizer, EntityManagerInterface $entityManager)
    {
        parent::__construct($normalizer, $entityManager);
        $this->entityName = Car::class;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if (!isset($context[AbstractNormalizer::CALLBACKS])) {
            $context[AbstractNormalizer::CALLBACKS] = [];
        }

        $context[AbstractNormalizer::CALLBACKS]['brand'] = static function (CarBrand $attributeValue): array {
            return $attributeValue->asSelectableArray();
        };
        $context[AbstractNormalizer::CALLBACKS]['showroom'] = static function (Showroom $attributeValue): array {
            return $attributeValue->asSelectableArray();
        };

        return parent::normalize($object, $format, $context);
    }
}
