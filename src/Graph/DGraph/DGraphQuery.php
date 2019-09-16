<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use Closure;
use Ds\Set;

/**
 * A DGraph Query.
 */
class DGraphQuery extends DGraphQueryAbstract
{
    private $queryGraph;

    /** @var Set $filters */
    private $filters;

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
                $result .= $item . ' ';
            } else {
                $result .= $key;
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

        $this->queryString .= $this->getFunction($this->query[self::FUNC]) . ')';

        if ($this->filters->count() > 0) {
            $this->queryString .= '@filter(';

            /** @var DGraphQueryFilter $filter */
            foreach ($this->filters as $filter) {
                $this->queryString .= $filter->prepare();
            }

            $this->queryString .= ')';
        }

        $this->queryString .= $this->decodeQueryGraph($this->queryGraph);

        $this->queryString .= '}';
        return $this->queryString;
    }
}
