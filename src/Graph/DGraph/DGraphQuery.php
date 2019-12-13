<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use Closure;
use Ds\Set;

/**
 * A DGraph Query.
 */
class DGraphQuery extends DGraphQueryAbstract
{
    const SORT_ASC = 'sort_asc';
    const SORT_DESC = 'sort_desc';
    const WITH_FACETS = 'with_facets';


    private $queryGraph;

    /** @var Set $filters */
    private $filters;

    /**
     * @var bool
     */
    private $recurseLoop;

    /**
     * @var int|null
     */
    private $recurseDepth;

    /**
     * @var string
     */
    private $sortPredicate;

    /**
     * @var string
     */
    private $sortOrder;

    /**
     * @var int
     */
    private $firstNumber;

    /**
     * @var int
     */
    private $offsetNumber;

    public function __construct()
    {
        $this->query = [];
        $this->filters = new Set();
    }

    /**
     * @see https://docs.dgraph.io/query-language/#term-matching
     * @param $predicate
     * @param $value
     * @return $this
     */
    public function filterEq($predicate, $value): DGraphQueryAbstract
    {
        $filter = new DGraphQueryFilter();
        $filter->eq($predicate, $value);

        $this->filters->add($filter);

        return $this;
    }

    /**
     * @param Closure $filterFn
     * @return $this
     */
    public function filter(Closure $filterFn): DGraphQueryAbstract
    {
        $filter = new DGraphQueryFilter();
        $filterFn($filter);

        $this->filters->add($filter);

        return $this;
    }

    /**
     * @param Closure $filterFn
     * @return $this
     */
    public function andFilter(Closure $filterFn): DGraphQueryAbstract
    {
        $filter = new DGraphQueryFilter(DGraphQueryFilter::AND);
        $filterFn($filter);

        $this->filters->add($filter);

        return $this;
    }

    /**
     * @param Closure $filterFn
     * @return $this
     */
    public function orFilter(Closure $filterFn): DGraphQueryAbstract
    {
        $filter = new DGraphQueryFilter(DGraphQueryFilter::OR);
        $filterFn($filter);

        $this->filters->add($filter);

        return $this;
    }

    /**
     * @param bool $loop
     * @param int|null $depth
     * @return $this
     */
    public function recurse(bool $loop = false, int $depth = null): DGraphQueryAbstract
    {
        $this->recurseLoop = $loop;

        if (!is_null($depth)) {
            $this->recurseDepth = $depth;
        }

        return $this;
    }

    /**
     * @param string $predicate
     * @param string $ordering
     * @return $this
     */
    public function sort(string $predicate, string $ordering = DGraphQuery::SORT_ASC): DGraphQueryAbstract
    {
        $this->sortPredicate = $predicate;
        $this->sortOrder = $ordering;

        return $this;
    }

    /**
     * @param int $number
     * @return $this
     */
    public function first(int $number = 1): DGraphQueryAbstract
    {
        $this->firstNumber = $number;

        return $this;
    }

    /**
     * @param int $number
     * @return $this
     */
    public function offset(int $number): DGraphQueryAbstract
    {
        $this->offsetNumber = $number;

        return $this;
    }

    /**
     * @param array $graphFragment
     * @return $this
     */
    public function setQueryGraph(array $graphFragment)
    {
        $this->queryGraph = $graphFragment;
        return $this;
    }

    /**
     * @param $queryGraph
     * @return string
     */
    public function decodeQueryGraph($queryGraph)
    {
        $result = '{';

        foreach ($queryGraph as $key => $item) {
            if (!is_array($item)) {
                if ($item === DGraphQuery::WITH_FACETS) {
                    $result .= $key . ' @facets';
                } else {
                    $result .= $item . ' ';
                }
            } else {
                $result .= $key;

                if ($this->itemHasFacets($item)) {
                    if ($this->itemHasExplicitFacets($item)) {
                        $result .= $this->handleFacets($item[DGraphQuery::WITH_FACETS]);
                        unset($item[DGraphQuery::WITH_FACETS]);
                    } else {
                        $result .= ' @facets ';
                        array_splice($item, array_search(DGraphQuery::WITH_FACETS, $item), 1);
                    }

                    if (count($item) < 1) {
                        continue;
                    }
                }

                $result .= $this->decodeQueryGraph($item);
            }
        }

        $result .= '}';

        return $result;
    }

    /**
     * @return string
     */
    public function prepare(): string
    {
        $this->queryString = '{ ' . self::FUNC_NAME . '( ' . self::FUNC . ':';
        $this->queryString .= $this->getFunction($this->query[self::FUNC]);
        $this->queryString .= $this->prepareSorting();
        $this->queryString .= $this->preparePagination();
        $this->queryString .= ')';

        if ($this->filters->count() > 0) {
            $this->queryString .= '@filter(';

            /** @var DGraphQueryFilter $filter */
            foreach ($this->filters as $filter) {
                $this->queryString .= $filter->prepare();
            }

            $this->queryString .= ')';
        }

        if (!is_null($this->recurseLoop)) {
            $this->queryString .= "@recurse(";
            $this->queryString .= "loop:" . ($this->recurseLoop ? "true" : "false");

            if (!is_null($this->recurseDepth)) {
                $this->queryString .= ",depth:" . $this->recurseDepth;
            }

            $this->queryString .= ")";
        }

        $this->queryString .= $this->decodeQueryGraph($this->queryGraph);

        $this->queryString .= '}';
        return $this->queryString;
    }

    /**
     * @return string
     */
    private function prepareSorting(): string
    {
        $sorting = "";

        if ($this->sortPredicate) {
            if ($this->sortOrder == self::SORT_DESC) {
                $order = "orderdesc";
            } else {
                $order = "orderasc";
            }

            $sorting .= "," . $order . ":" . $this->sortPredicate;
        }

        return $sorting;
    }

    /**
     * @return string
     */
    private function preparePagination(): string
    {
        $pagination = "";

        if ($this->firstNumber) {
            $pagination .= ",first:" . $this->firstNumber;
        }

        if ($this->offsetNumber) {
            $pagination .= ",offset:" . $this->offsetNumber;
        }

        return $pagination;
    }

    /**
     * @param array $item
     * @return string
     */
    private function handleFacets(array $item): string
    {
        $result = " @facets(";

        foreach ($item as $facet) {
            $result .= $facet . " ";
        }

        $result .= ")";

        return $result;
    }

    /**
     * The item has an array of facets to query
     *
     * @param array $item
     * @return bool
     */
    private function itemHasExplicitFacets(array $item): bool
    {
        return array_key_exists(DGraphQuery::WITH_FACETS, $item);
    }

    /**
     * The item has an element which signifies that all facets should be queried
     *
     * @param array $item
     * @return bool
     */
    private function itemHasImplicitFacets(array $item): bool
    {
        return in_array(DGraphQuery::WITH_FACETS, $item);
    }

    /**
     * @param array $item
     * @return bool
     */
    private function itemHasFacets(array $item): bool
    {
        return $this->itemHasExplicitFacets($item) || $this->itemHasImplicitFacets($item);
    }
}
