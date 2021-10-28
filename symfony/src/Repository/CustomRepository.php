<?php

namespace App\Repository;

use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\MappingException as PersistenceMappingException;
use ReflectionException;
use function Symfony\Component\String\u;

abstract class CustomRepository extends ServiceEntityRepository
{
    public const ENTITY = 'entity';

    /**
     * {@inheritdoc}
     */
    public function __construct(ManagerRegistry $registry, $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    protected function parseCriteria(QueryBuilder $qb, array $criteria): QueryBuilder
    {
        foreach ($criteria as $field => $values) {
            $values = is_array($values) ? $values : [$values];

            $orNull = false;
            foreach ($values as $key => $value) {
                if (null === $value || (is_string($value) && 'NULL' === strtoupper($value))) {
                    $orNull = true;
                    unset($values[$key]);
                    continue;
                }

                if ($value instanceof DateTimeInterface) {
                    $values[$key] = $value->format('Y-m-d H:i:s');
                    continue;
                }

                if (is_object($value)
                    && $this->getEntityManager()->getMetadataFactory()->hasMetadataFor(ClassUtils::getClass($value))) {
                    try {
                        $meta = $this->getEntityManager()->getMetadataFactory()->getMetadataFor(get_class($value));
                    } catch (PersistenceMappingException | ReflectionException) {
                        continue;
                    }
                    $values[$key] = u('-')->join($meta->getIdentifierValues($value))->toString();
                }
            }

            $this->parseSimpleFilter($qb, $field, $values, self::ENTITY, false, $orNull);
        }

        return $qb;
    }

    protected function parseSimpleFilter(
        QueryBuilder $qb,
        string       $field,
        array        $values,
        string       $entity,
        bool         $negative = false,
        bool         $orNull = false
    ): void
    {
        $eb = $this->makeExpressionBuilder($qb, $negative, $orNull);

        $tokens = explode('.', $field);
        if (count($tokens) > 1 && $this->getClassMetadata()->hasAssociation($tokens[0])) {
            $tmpEntity = "r_{$tokens[0]}";
            if (!in_array($tmpEntity, $qb->getAllAliases(), true)) {
                $qb->leftJoin("{$entity}.{$tokens[0]}", $tmpEntity);
            }
            $tmpField = "er_{$tokens[1]}";
            if (count($values) > 1) {
                $qb->andWhere(
                    $eb($qb->expr()->in("{$tmpEntity}.{$tokens[1]}", ":{$tmpField}"), "{$tmpEntity}.{$tokens[1]}")
                )
                    ->setParameter($tmpField, $values);
            } elseif (1 === count($values)) {
                $qb->andWhere(
                    $eb($qb->expr()->eq("{$tmpEntity}.{$tokens[1]}", ":{$tmpField}"), "{$tmpEntity}.{$tokens[1]}")
                )
                    ->setParameter($tmpField, $values[array_key_first($values)]);
            } else {
                $qb->andWhere(
                    $eb($qb->expr()->eq("{$tmpEntity}.{$tokens[1]}", ":{$tmpField}"), "{$tmpEntity}.{$tokens[1]}")
                )
                    ->setParameter($tmpField, u(' ')->join($values)->toString());
            }
        } elseif ($this->getClassMetadata()->hasField($tokens[0])
            || $this->getClassMetadata()->hasAssociation($tokens[0])) {
            $tmpField = "e_{$tokens[0]}";
            if (count($values) > 1) {
                $qb->andWhere($eb($qb->expr()->in("{$entity}.{$tokens[0]}", ":{$tmpField}"), "{$entity}.{$tokens[0]}"))
                    ->setParameter($tmpField, $values);
            } elseif (1 === count($values) && (null === $values[0] || (is_string($values[0]) && 'NULL' === strtoupper($values[0])))) {
                $qb->andWhere($eb($qb->expr()->isNull("{$entity}.{$tokens[0]}")));
            } elseif (1 === count($values)) {
                $qb->andWhere($eb($qb->expr()->eq("{$entity}.{$tokens[0]}", ":{$tmpField}"), "{$entity}.{$tokens[0]}"))
                    ->setParameter($tmpField, $values[array_key_first($values)]);
            } else {
                $qb->andWhere($eb($qb->expr()->eq("{$entity}.{$tokens[0]}", ":{$tmpField}"), "{$entity}.{$tokens[0]}"))
                    ->setParameter($tmpField, u(' ')->join($values)->toString());
            }
        }
    }

    protected function makeExpressionBuilder(QueryBuilder $qb, bool $negative, bool $orNull): callable
    {
        return static function ($expr, string $field = null) use ($qb, $negative, $orNull) {
            if ($orNull && null !== $field) {
                $expr = $qb->expr()->orX(
                    $expr,
                    $qb->expr()->isNull($field)
                );
            }

            if ($negative) {
                $expr = $qb->expr()->not($expr);
            }

            return $expr;
        };
    }

    protected function parseOrder(QueryBuilder $qb, array $orderBy): QueryBuilder
    {
        $entity = self::ENTITY;

        foreach ($orderBy as $field => $order) {
           if ($this->getClassMetadata()->hasField($field)
                || $this->getClassMetadata()->hasAssociation($field)) {
                $qb->addOrderBy("{$entity}.{$field}", $order);
            } else {
                $qb->addOrderBy($field, $order);
            }
        }

        return $qb;
    }

    /**
     * @param ?int $limit
     * @param ?int $offset
     * @return object[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->createParsedQueryBuilder($criteria, $orderBy, $limit, $offset);

        return $qb->getQuery()->getResult();
    }

    protected function createParsedQueryBuilder(
        array  $criteria,
        ?array $orderBy = null,
        ?int   $limit = null,
        ?int   $offset = null
    ): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select(self::ENTITY)
            ->from($this->getEntityName(), self::ENTITY);

        $qb = $this->parseCriteria($qb, $criteria);

        if (null !== $orderBy) {
            $qb = $this->parseOrder($qb, $orderBy);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }

    /**
     * @return object[]|Paginator
     */
    public function findByPaginated(
        array  $criteria,
        ?array $orderBy = null,
        ?int   $limit = null,
        ?int   $offset = null
    ): Paginator
    {
        $qb = $this->createParsedQueryBuilder($criteria, $orderBy, $limit, $offset);

        return new Paginator($qb, true);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select(self::ENTITY)
            ->from($this->getEntityName(), self::ENTITY);

        $qb = $this->parseCriteria($qb, $criteria);

        if (null !== $orderBy) {
            $qb = $this->parseOrder($qb, $orderBy);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
