<?php

namespace App\Controller;

use App\Entity\CarBrand;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/car-brands", name="car-brands_")
 */
class CarBrandController extends BaseController
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->entityName = CarBrand::class;
    }
}