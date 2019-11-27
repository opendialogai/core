<?php

namespace OpenDialogAi\Core\Graph\DGraph;

abstract class DGraphQueryAbstract implements DGraphQueryInterface
{
    const FUNC_NAME = 'dGraphQuery';
    const FUNC_TYPE = 'func_type';

    const FUNC = 'func';
    const FILTER = 'filter';
    const ALLOFTERMS = 'allofterms';
    const EQ = 'eq';
    const UID = 'uid';
    const HAS = 'has';

    const NOT = 'not';
    const NOT_EQ = self::NOT . ' ' . self::EQ;
    const NOT_HAS = self::NOT . ' ' . self::HAS;


    const PREDICATE = 'predicate';
    const TERM_LIST = 'term_list';
    const VALUE = 'value';

    protected $query;

    protected $queryString;

    /**
     * Given a function from the query, this method returns a string containing the DGraph syntax for calling the given function
     * @param array $queryFunction
     * @return string
     */
    protected function getFunction(array $queryFunction): string
    {
        switch ($queryFunction[self::FUNC_TYPE]) {
            case self::ALLOFTERMS:
                $queryFunctionString = $queryFunction[self::FUNC_TYPE] . '(';
                $queryFunctionString .= $queryFunction[self::PREDICATE] . ',';
                $queryFunctionString .= '"' . $queryFunction[self::TERM_LIST] . '")';
                break;

            case self::EQ:
            case self::NOT_EQ:
                $queryFunctionString = $queryFunction[self::FUNC_TYPE] . '(';
                $queryFunctionString .= $queryFunction[self::PREDICATE] . ',';
                $queryFunctionString .= '"' . $queryFunction[self::VALUE] . '")';
                break;

            case self::HAS:
            case self::NOT_HAS:
            case self::UID:
                $queryFunctionString = $queryFunction[self::FUNC_TYPE] . '(';
                $queryFunctionString .= $queryFunction[self::VALUE] . ')';
                break;
        }

        return $queryFunctionString;
    }

    /**
     * @see https://docs.dgraph.io/query-language/#term-matching
     * @param $predicate
     * @param array $termList
     * @return $this
     */
    public function allofterms($predicate, array $termList): self
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
    public function eq($predicate, $value): self
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
    public function uid($uid): self
    {
        $this->query[self::FUNC] = [
            self::FUNC_TYPE => self::UID,
            self::VALUE => $uid
        ];

        return $this;
    }

    /**
     * @param $predicate
     * @return $this
     */
    public function has($predicate): self
    {
        $this->query[self::FUNC] = [
            self::FUNC_TYPE => self::HAS,
            self::VALUE => $predicate
        ];

        return $this;
    }
}
