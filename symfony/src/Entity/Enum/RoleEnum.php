<?php

namespace App\Entity\Enum;

class RoleEnum extends SampleEnum
{
    public const ROLE_GUEST = 'ROLE_GUEST';
    public const ROLE_MANAGER = 'ROLE_MANAGER';
    public const ROLE_DIRECTOR = 'ROLE_DIRECTOR';

    protected static array $enumsTitles = [
        self::ROLE_GUEST => 'ROLE_GUEST',
        self::ROLE_MANAGER => 'ROLE_MANAGER',
        self::ROLE_DIRECTOR => 'ROLE_DIRECTOR',
    ];
}