<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends CustomRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByIdentifier(string $identifier): ?User
    {
        $entity = self::ENTITY;
        $qb = $this->getEntityManager()->createQueryBuilder();

        $exprPhone = $qb->expr()->eq("{$entity}.phone", ':er_identity');
        $qb->select($entity)
            ->from($this->getEntityName(), $entity)
            ->where($exprPhone)
            ->setParameter('er_identity', $identifier);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $exception) {
            return null;
        }
    }
}