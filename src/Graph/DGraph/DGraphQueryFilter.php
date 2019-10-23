<?php


namespace OpenDialogAi\Core\Graph\DGraph;


class DGraphQueryFilter extends DGraphQueryAbstract
{
    const AND = 'and';
    const OR = 'or';

    private $connective;

    /**
     * DGraphQueryFilter constructor.
     * @param $connective
     */
    public function __construct($connective = null)
    {
        $this->connective = $connective;
    }

    /**
     * Returns a string containing the query in DGraph syntax
     * @return string
     */
    public function prepare(): string
    {
        $preparedString = ' ';

        if ($this->connective) {
            $preparedString .= $this->connective . ' ';
        }

        $preparedString .= $this->getFunction($this->query[self::FUNC]);

        return $preparedString;
    }

    /**
     * @param $predicate
     * @param $value
     * @return $this
     */
    public function notEq($predicate, $value): self
    {
        $this->query[self::FUNC] = [
            self::FUNC_TYPE => self::NOT_EQ,
            self::PREDICATE => $predicate,
            self::VALUE => $value
        ];

        return $this;
    }

    /**
     * @param $predicate
     * @return $this
     */
    public function notHas($predicate): self
    {
        $this->query[self::FUNC] = [
            self::FUNC_TYPE => self::NOT_HAS,
            self::VALUE => $predicate
        ];

        return $this;
    }
}
