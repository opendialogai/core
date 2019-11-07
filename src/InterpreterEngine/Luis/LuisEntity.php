<?php

namespace OpenDialogAi\InterpreterEngine\Luis;


use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUEntity;

class LuisEntity extends AbstractNLUEntity
{
    private const NO_RESOLUTION_VALUE = 0;
    private const ONE_RESOLUTION_VALUE = 1;
    private const MANY_RESOLUTION_VALUES = 2;

    /**
     * @param $entity
     * @param $query
     */
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
