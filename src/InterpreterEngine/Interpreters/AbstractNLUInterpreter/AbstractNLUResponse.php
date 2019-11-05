<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;



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
     * @return AbstractNLUIntent|null
     */
    public function getTopScoringIntent(): ?AbstractNLUIntent
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
     * @param $entity
     * @return AbstractNLUEntity
     */
    abstract public function createEntity($entity): AbstractNLUEntity;
}
