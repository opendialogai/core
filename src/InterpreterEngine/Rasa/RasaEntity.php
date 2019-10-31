<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

class RasaEntity
{
    /**
     * The user's input
     * @var string
     */
    private $query;

    /* @var string */
    private $type;

    /**
     * The exact match from the query
     * @var string
     */
    private $entityString;

    /**
     * Where the entity begins in the phrase
     * @var int
     */
    private $startIndex;

    /**
     * Where the entity ends in the phrase
     * @var int
     */
    private $endIndex;

    /**
     * In case of a simple entity the confidence score for the match
     * @var float
     */
    private $score;

    public function __construct($entity, $query)
    {
        $this->query = $query;
        $this->type = $entity->value;
        $this->entityString = $entity->entity;

        if (isset($entity->start)) {
            $this->startIndex = $entity->start;
            $this->endIndex = $entity->end;
        }

        if (isset($entity->confidence)) {
            $this->score = floatval($entity->confidence);
        }
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

    /**
     * @return int
     */
    public function getStartIndex(): int
    {
        return $this->startIndex;
    }

    /**
     * @return int
     */
    public function getEndIndex(): int
    {
        return $this->endIndex;
    }

    /**
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }
}
