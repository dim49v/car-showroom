<?php

namespace App\Controller\Traits;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;
use function Symfony\Component\String\u;

/**
 * @property string $entityName
 * @property Request $request
 */
trait QueryParamsTrait
{
    public int $defaultPage = 1;
    public bool $defaultRelation = false;
    public int $defaultPerPage = 25;

    public function getOrderParam(): ?array
    {
        $sort = $this->request->query->get('sort');

        $order = null;
        if (null !== $sort) {
            $order = [];
            foreach (u($sort)->split(',') as $sortField) {
                $matches = $sortField->match('/(?<sign>[+-]?)(?<field>[\w]+)/');
                if (empty($matches)) {
                    continue;
                }

                if ('-' === $matches['sign']) {
                    $direction = Criteria::DESC;
                } else {
                    $direction = Criteria::ASC;
                }

                $order[$matches['field']] = $direction;
            }
        }

        return $order;
    }

    protected function makeFilterModifications(array &$filters): void
    {
    }

    public function getFilterParam(bool $original = false): array
    {
        $filterParam = $this->request->query->get('filter', '');

        $filterArray = [];
        if ('' !== $filterParam) {
            foreach (u($filterParam)->split(',') as $filter) {
                $matches = $filter->match('/(?<search>[\w\.]+(?<modifier>\[\w+\])?):(?<value>.*)/');
                if (empty($matches)) {
                    continue;
                }

                $filterArray[$matches['search']] = [];
                foreach (u($matches['value'])->split(' ') as $value) {
                    if ($value->ignoreCase()->equalsTo(['true', 'false'])) {
                        $filterArray[$matches['search']][] = filter_var($value->toString(), FILTER_VALIDATE_BOOLEAN);
                        continue;
                    }

                    $filterArray[$matches['search']][] = $value->toString();
                }
            }
        }

        $this->makeFilterModifications($filterArray);

        return $filterArray;
    }

    public function getPerPageParam(): ?int
    {
        $perPage = $this->request->query->get('per_page', $this->defaultPerPage);
        if (u($perPage)->ignoreCase()->equalsTo('all')) {
            return null;
        }
        $perPage = (int) $perPage;

        return ($perPage && ($perPage > 0)) ? $perPage : $this->defaultPage;
    }

    public function getPageParam(): int
    {
        $page = $this->request->query->getInt('page', $this->defaultPage);

        return ($page > 0) ? $page : $this->defaultPage;
    }

    public function getOffset(): int
    {
        $limit = $this->getLimit();
        if (null === $limit) {
            return 0;
        }

        return ($this->getPageParam() - 1) * $limit;
    }

    public function getLimit(): ?int
    {
        return $this->getPerPageParam();
    }
}
