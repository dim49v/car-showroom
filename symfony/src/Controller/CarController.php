<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\Enum\RoleEnum;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cars", name="cars_")
 */
class CarController extends BaseController
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->entityName = Car::class;
    }

    public function makeFilterModifications(array &$filters): void
    {
        if (!$this->isGranted(RoleEnum::ROLE_MANAGER)) {
            $filters["sold"] = [false];
        }
    }
}