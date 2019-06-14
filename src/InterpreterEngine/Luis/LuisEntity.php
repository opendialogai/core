<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

class LuisEntity
{
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
     * If a list type entity this provides all the resolution values
     * @var array
     */
    private $resolutionValues;

    /**
     * In case of a simple entity the confidence score for the match
     * @var float
     */
    private $score;

    public function __construct($entity)
    {
        $this->type = $entity->type;

        $this->entityString = $entity->entity;

        if (isset($entity->startIndex)) {
            $this->startIndex = $entity->startIndex;
            $this->endIndex = $entity->endIndex;
        }

        if (isset($entity->resolution->values)) {
            $this->extractValues($entity);
        }

        if (isset($entity->score)) {
            $this->score = floatval($entity->score);
        }
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getEntityString()
    {
        return $this->entityString;
    }

    /**
     * @return mixed
     */
    public function getStartIndex()
    {
        return $this->startIndex;
    }

    /**
     * @return mixed
     */
    public function getEndIndex()
    {
        return $this->endIndex;
    }

    /**
     * @return mixed
     */
    public function getResolutionValues()
    {
        return $this->resolutionValues;
    }

    /**
     * @return mixed
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param $entity
     */
    private function extractValues($entity): void
    {
        foreach ($entity->resolution->values as $value) {
            $this->resolutionValues[] = $value;
        }
    }
}
