<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

class LuisEntity
{
    private const NO_RESOLUTION_VALUE = 0;
    private const ONE_RESOLUTION_VALUE = 1;
    private const MANY_RESOLUTION_VALUES = 2;

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
     * If a list type entity this provides all the resolution values
     * @var array
     */
    private $resolutionValues;

    /**
     * In case of a simple entity the confidence score for the match
     * @var float
     */
    private $score;

    public function __construct($entity, $query)
    {
        $this->query = $query;
        $this->type = $entity->type;
        $this->entityString = $entity->entity;

        if (isset($entity->startIndex)) {
            $this->startIndex = $entity->startIndex;
            $this->endIndex = $entity->endIndex;
        }

        $this->extractValues($entity);

        if (isset($entity->score)) {
            $this->score = floatval($entity->score);
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
     * @return array
     */
    public function getResolutionValues(): array
    {
        return $this->resolutionValues;
    }

    /**
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @param $entity
     */
    private function extractValues($entity): void
    {
        $resolutionStructure = $this->detectResolutionStructure($entity);

        switch ($resolutionStructure) {
            case self::ONE_RESOLUTION_VALUE:
                $values[] = $entity->resolution->value;
                break;

            case self::MANY_RESOLUTION_VALUES:
                $values = $entity->resolution->values;
                break;

            case self::NO_RESOLUTION_VALUE:
            default:
                $values[] = $this->extractValueWhenNoResolution();
        }

        foreach ($values as $value) {
            $this->resolutionValues[] = $value;
        }
    }

    /**
     * Returns a constant representing the structure (or existence) of the entity's resolution object
     * @param $entity
     * @return int
     */
    private function detectResolutionStructure($entity): int
    {
        if (isset($entity->resolution->values)) {
            return self::MANY_RESOLUTION_VALUES;
        }

        if (isset($entity->resolution->value)) {
            return self::ONE_RESOLUTION_VALUE;
        }

        return self::NO_RESOLUTION_VALUE;
    }

    /**
     * Extracts a value when the entity has no resolution value. The value is extracted from the the query if possible
     * and from the entity string otherwise.
     * @return string
     */
    private function extractValueWhenNoResolution(): string
    {
        $entityValue = $this->getEntityString();

        if (!is_null($this->getQuery()) && !is_null($this->getStartIndex()) && !is_null($this->getEndIndex())) {
            $entityValue = substr($this->getQuery(), $this->getStartIndex(), 1 + $this->getEndIndex() - $this->getStartIndex());
        }

        return $entityValue;
    }
}
