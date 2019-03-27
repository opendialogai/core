<?php


namespace OpenDialogAi\Core\Graph\DGraph;


/**
 * A DGraph Query.
 */
class DGraphQuery
{
    const FUNC_NAME = 'dGraphQuery';
    const FUNC = 'func';
    const FUNC_TYPE = 'func_type';
    const ALLOFTERMS = 'allofterms';

    const PREDICATE = 'predicate';
    const TERM_LIST = 'term_list';

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

    public function setQueryGraph(array $graphFragment)
    {
        $this->queryGraph = $graphFragment;

        return $this;
    }

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

    public function prepare()
    {
        $this->queryString = '{ ' . self::FUNC_NAME . '( ' . self::FUNC . ':';

        $this->queryString .= $this->getQueryFunction() . ')';
        $this->queryString .= $this->decodeQueryGraph($this->queryGraph);

        $this->queryString .= '}';

        return $this->queryString;
    }

    public function getQueryFunction()
    {
        $queryFunction = $this->query[self::FUNC];

        $queryFunctionString = $queryFunction[self::FUNC_TYPE] . '(';
        $queryFunctionString .= $queryFunction[self::PREDICATE] . ',';
        $queryFunctionString .= '"' . $queryFunction[self::TERM_LIST] . '")';

        return $queryFunctionString;
    }
}
