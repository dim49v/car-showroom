<?php

namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

abstract class CustomObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    protected ObjectNormalizer $normalizer;
    protected EntityManagerInterface $entityManager;
    protected string $entityName;

    /**
     * CustomObjectNormalizer constructor.
     */
    public function __construct(ObjectNormalizer $normalizer, EntityManagerInterface $entityManager)
    {
        $this->normalizer = $normalizer;
        $this->entityManager = $entityManager;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof $this->entityName;
    }

    public function denormalize($data, $class, string $format = null, array $context = [])
    {
        $object = $this->idToObject($data, $context);
        if (null !== $object) {
            return $object;
        }

        return $this->normalizer->denormalize($data, $class, $format, $context);
    }

    protected function idToObject(&$data, array &$context): ?object
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        if (!empty($data['value'])) {
            if (is_object($data['value'])) {
                return $data['value'];
            }

            try {
                $object = $this->entityManager->getReference($this->entityName, $data['value']);
            } catch (Exception $exception) {
                $object = null;
            }
            if (null === $object) {
                throw new LogicException("{$this->entityName} with identifier {$data['value']} not found.");
            }

            return $object;
        }
        $identifier = null;
        if (!empty($data['id'])) {
            $identifier = $data['id'];
        }
        if (null !== $identifier && empty($context['object_to_populate']) && !isset($context['prevent_loading'])) {
            $object = $this->entityManager->find($this->entityName, $identifier);
            if (null === $object) {
                throw new LogicException("{$this->entityName} with identifier {$identifier} not found.");
            }
            $context['object_to_populate'] = $object;
        }

        return null;
    }

    public function supportsDenormalization($data, $type, string $format = null): bool
    {
        return $type === $this->entityName;
    }
}
