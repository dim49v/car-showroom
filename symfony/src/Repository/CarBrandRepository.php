<?php

namespace App\Repository;

use App\Entity\CarBrand;
use Doctrine\Persistence\ManagerRegistry;

class CarBrandRepository extends CustomRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarBrand::class);
    }
}