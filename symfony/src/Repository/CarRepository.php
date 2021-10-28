<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CarRepository extends CustomRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    protected function parseCriteria(QueryBuilder $qb, array $criteria): QueryBuilder
    {
        $entity = self::ENTITY;

        if (isset($criteria['sold'])) {
            $tmpPurchase = 'r_purchase';
            if (!in_array($tmpPurchase, $qb->getAllAliases(), true)) {
                $qb->leftJoin("{$entity}.purchase", $tmpPurchase);
            }

            $qb->andWhere(
                $criteria['sold'][0]
                    ? $qb->expr()->isNotNull($tmpPurchase)
                    : $qb->expr()->isNull($tmpPurchase)
            );
        }

        return parent::parseCriteria($qb, $criteria);
    }
}