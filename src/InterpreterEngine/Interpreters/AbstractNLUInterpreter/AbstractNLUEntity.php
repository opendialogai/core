<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

abstract class AbstractNLUEntity
{
    /**
     * In case of a simple entity the confidence score for the match
     * @var float
     */
    protected $score;

    /**
     * Where the entity begins in the phrase
     * @var int
     */
    protected $startIndex;

    /**
     * Where the entity ends in the phrase
     * @var int
     */
    protected $endIndex;

    /**
     * The user's input
     * @var string
     */
    protected $query;

    /* @var string */
    protected $type;

    /**
     * The exact match from the query
     * @var string
     */
    protected $entityString;

    /**
     * If a list type entity this provides all the resolution values
     * @var array
     */
    protected $resolutionValues;

    /**
     * @return int
     */
    public function getEndIndex(): int
    {
        return $this->endIndex;
    }

    /**
     * @return int
     */
    public function getStartIndex(): int
    {
        return $this->startIndex;
    }

    /**
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @return array
     */
    public function getResolutionValues(): array
    {
        return $this->resolutionValues;
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getEntityString(): string
    {
        return $this->entityString;
    }
}
