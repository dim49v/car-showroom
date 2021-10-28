<?php

namespace App\Entity\Enum;

use ReflectionClass;
use ReflectionException;

abstract class SampleEnum
{
    public const ENUM_UNDEFINED = null;
    protected static array $enumsTitles = [
        self::ENUM_UNDEFINED => 'Undefined',
    ];

    public static function getEnums(): array
    {
        try {
            $oClass = new ReflectionClass(static::class);

            return $oClass->getConstants() ?: [];
        } catch (ReflectionException $exception) {
            return [];
        }
    }

    public static function getEnumsTitles(): array
    {
        return static::$enumsTitles;
    }

    /**
     * @param mixed $enum
     */
    public static function asSelectableArray($enum, string $titleField = 'title'): array
    {
        return [
            $titleField => static::getEnumTitle($enum),
            'value' => $enum,
        ];
    }

    /**
     * @param mixed $enum
     *
     * @return mixed
     */
    public static function getEnumTitle($enum)
    {
        return static::$enumsTitles[$enum] ?? $enum;
    }
}
