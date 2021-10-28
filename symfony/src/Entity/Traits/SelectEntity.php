<?php

namespace App\Entity\Traits;

use App\Interfaces\IdInterface;
use RuntimeException;

trait SelectEntity
{
    public function asSelectableArray(string $titleField = 'title'): array
    {
        return [
            $titleField => $this->getTitle(),
            'value' => $this->getValue(),
        ];
    }

    public function getTitle(): string
    {
        /** @var object $object */
        $object = $this;

        if (property_exists($object, 'title')) {
            return $object->title;
        }

        return self::class." {$object->getValue()}";
    }

    /**
     * @return null|int
     */
    public function getValue()
    {
        /** @var object $object */
        $object = $this;

        if ($object instanceof IdInterface) {
            return $object->getId();
        }

        throw new RuntimeException('Cant transform entity to selectable.');
    }
}
