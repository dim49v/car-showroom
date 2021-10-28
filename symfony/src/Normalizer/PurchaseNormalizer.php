<?php

namespace App\Normalizer;

use App\Entity\Car;
use App\Entity\Purchase;
use App\Entity\Showroom;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PurchaseNormalizer extends CustomObjectNormalizer
{
    /**
     * PurchaseNormalizer constructor.
     */
    public function __construct(ObjectNormalizer $normalizer, EntityManagerInterface $entityManager)
    {
        parent::__construct($normalizer, $entityManager);
        $this->entityName = Purchase::class;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if (!isset($context[AbstractNormalizer::CALLBACKS])) {
            $context[AbstractNormalizer::CALLBACKS] = [];
        }

        $context[AbstractNormalizer::CALLBACKS]['customer'] = static function (User $attributeValue): array {
            return $attributeValue->asSelectableArray();
        };
        $context[AbstractNormalizer::CALLBACKS]['car'] = static function (Car $attributeValue): array {
            return $attributeValue->asSelectableArray();
        };
        $context[AbstractNormalizer::CALLBACKS]['manager'] = static function (User $attributeValue): array {
            return $attributeValue->asSelectableArray();
        };
        $context[AbstractNormalizer::CALLBACKS]['showroom'] = static function (Showroom $attributeValue): array {
            return $attributeValue->asSelectableArray();
        };

        return parent::normalize($object, $format, $context);
    }
}
