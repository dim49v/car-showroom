<?php

namespace App\Controller;

use App\Entity\Enum\RoleEnum;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users", name="users_")
 */
class UserController extends BaseController
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->entityName = User::class;
    }

    protected function makeFilterModifications(array &$filters): void
    {
        if (!$this->isGranted(RoleEnum::ROLE_DIRECTOR)) {
            $filters['manager'] = [false];
            $filters['director'] = [false];
        }
    }
}