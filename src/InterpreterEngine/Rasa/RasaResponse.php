<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

class RasaResponse
{
    /**
     *  @var string The text sent to RASA for intent analysis
     */
    private $query;

    /** @var RasaIntent */
    private $topScoringIntent;

    /* @var RasaEntity[] */
    private $entities = [];

    public function __construct($response)
    {
        $this->query = isset($response->text) ? $response->text : null;

        if (isset($response->intent)) {
            $this->topScoringIntent = new RasaIntent($response->intent);
        }

        if (isset($response->entities)) {
            $this->createEntities($response->entities);
        }
    }

    /**
     * Extract entities and create @see RasaEntity objects.
     *
     * @param $entities
     */
    private function createEntities($entities)
    {
        foreach ($entities as $entity) {
            $this->entities[] = new RasaEntity($entity, $this->getQuery());
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
     * @return RasaIntent
     */
    public function getTopScoringIntent()
    {
        return $this->topScoringIntent;
    }

    /**
     * @return RasaEntity[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }
}
