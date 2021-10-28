<?php

namespace App\Repository;

use App\Entity\Showroom;
use Doctrine\Persistence\ManagerRegistry;

class ShowroomRepository extends CustomRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Showroom::class);
    }
}