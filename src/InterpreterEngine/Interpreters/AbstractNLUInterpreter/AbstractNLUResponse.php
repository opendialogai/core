<?php


namespace OpenDialogAi\Core\InterpreterEngine\Interpreters\AbstractNLUInterpreter;


use OpenDialogAi\InterpreterEngine\Rasa\AbstractNLUEntity;
use OpenDialogAi\InterpreterEngine\Rasa\AbstractNLUIntent;
use OpenDialogAi\InterpreterEngine\Rasa\RasaEntity;

abstract class AbstractNLUResponse
{
    /* @var AbstractNLUEntity[] */
    protected $entities = [];

    /**
     *  @var string The text sent to the NLU service for intent analysis
     */
    protected $query;

    /** @var AbstractNLUIntent */
    protected $topScoringIntent;

    /**
     * @return AbstractNLUEntity[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @return AbstractNLUIntent
     */
    public function getTopScoringIntent(): AbstractNLUIntent
    {
        return $this->topScoringIntent;
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * Extract entities and create @param array $entities
     *@see RasaEntity objects.
     *
     */
    protected function createEntities(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entities[] = $this->createEntity($entity);
        }
    }

    /**
     * Creates a new AbstractNLUEntity from entity data
     * @param array $entity
     * @return AbstractNLUEntity
     */
    abstract public function createEntity(array $entity): AbstractNLUEntity;
}
