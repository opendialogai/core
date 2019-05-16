<?php

namespace OpenDialogAi\Core\Graph\DGraph;

/**
 * A DGraph Query.
 */
class DGraphQuery
{
    const FUNC_NAME = 'dGraphQuery';
    const FUNC_TYPE = 'func_type';

    const FUNC = 'func';
    const FILTER = 'filter';
    const ALLOFTERMS = 'allofterms';
    const EQ = 'eq';
    const UID = 'uid';


    const PREDICATE = 'predicate';
    const TERM_LIST = 'term_list';
    const VALUE = 'value';

    private $query;

    private $queryGraph;

    private $queryString;

    public function __construct()
    {
        $this->query = [];
    }

    /**
     * @see https://docs.dgraph.io/query-language/#term-matching
     * @param $predicate
     * @param array $termList
     * @return $this
     */
    public function allofterms($predicate, array $termList)
    {
        $this->query[self::FUNC] = [
            self::FUNC_TYPE => self::ALLOFTERMS,
            self::PREDICATE => $predicate,
            self::TERM_LIST => implode(" ", $termList)
        ];

        return $this;
    }

    /**
     * @param $predicate
     * @param $value
     * @return $this
     */
    public function eq($predicate, $value)
    {
        $this->query[self::FUNC] = [
            self::FUNC_TYPE => self::EQ,
            self::PREDICATE => $predicate,
            self::VALUE => $value
        ];

        return $this;
    }

    /**
     * @param $uid
     * @return $this
     */
    public function uid($uid)
    {
        $this->query[self::FUNC] = [
            self::FUNC_TYPE => self::UID,
            self::VALUE => $uid
        ];

        return $this;
    }

    /**
     * @param $predicate
     * @param $value
     * @return $this
     */
    public function filterEq($predicate, $value)
    {
        $this->query[self::FILTER] = [
            self::FUNC_TYPE => self::EQ,
            self::PREDICATE => $predicate,
            self::VALUE => $value
        ];

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
    public function prepare()
    {
        $this->queryString = '{ ' . self::FUNC_NAME . '( ' . self::FUNC . ':';

        $this->queryString .= $this->getFunction($this->query[self::FUNC]) . ')';

        if (isset($this->query[self::FILTER])) {
            $this->queryString .= '@filter(';
            $this->queryString .= $this->getFunction($this->query[self::FILTER]);
            $this->queryString .= ')';
        }

        $this->queryString .= $this->decodeQueryGraph($this->queryGraph);

        $this->queryString .= '}';
        return $this->queryString;
    }

    /**
     * @param $queryFunction
     * @return string
     */
    private function getFunction($queryFunction)
    {
        switch ($queryFunction[self::FUNC_TYPE]) {
            case self::ALLOFTERMS:
                $queryFunctionString = $queryFunction[self::FUNC_TYPE] . '(';
                $queryFunctionString .= $queryFunction[self::PREDICATE] . ',';
                $queryFunctionString .= '"' . $queryFunction[self::TERM_LIST] . '")';
                break;

            case self::EQ:
                $queryFunctionString = $queryFunction[self::FUNC_TYPE] . '(';
                $queryFunctionString .= $queryFunction[self::PREDICATE] . ',';
                $queryFunctionString .= '"' . $queryFunction[self::VALUE] . '")';
                break;

            case self::UID:
                $queryFunctionString = $queryFunction[self::FUNC_TYPE] . '(';
                $queryFunctionString .= $queryFunction[self::VALUE] . ')';
                break;
        }

        return $queryFunctionString;
    }
}
