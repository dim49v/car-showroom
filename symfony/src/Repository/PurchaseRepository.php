<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Persistence\ManagerRegistry;

class PurchaseRepository extends CustomRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }
}