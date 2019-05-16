<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

class LuisResponse
{
    /**
     *  @var string The text sent to LUIS for intent analysis
     */
    private $query;

    /** @var LuisIntent */
    private $topScoringIntent;

    /* @var LuisEntity[] */
    private $entities = [];

    public function __construct($response)
    {
        $this->query = isset($response->query) ? $response->query : null;

        if (isset($response->topScoringIntent)) {
            $this->topScoringIntent = new LuisIntent($response->topScoringIntent);
        }

        if (isset($response->entities)) {
            $this->createEntities($response->entities);
        }
    }

    /**
     * Extract entities and create @see LuisEntity objects.
     *
     * @param $entities
     */
    private function createEntities($entities)
    {
        foreach ($entities as $entity) {
            $this->entities[] = new LuisEntity($entity);
        }
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return LuisIntent
     */
    public function getTopScoringIntent()
    {
        return $this->topScoringIntent;
    }

    /**
     * @return LuisEntity[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }
}
