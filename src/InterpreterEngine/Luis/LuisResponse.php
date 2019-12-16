<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUEntity;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;

class LuisResponse extends AbstractNLUResponse
{
    public function __construct($response)
    {
        $this->query = isset($response->query) ? $response->query : null;
        $this->topScoringIntent = isset($response->topScoringIntent) ? new LuisIntent($response->topScoringIntent) : null;

        if (isset($response->entities)) {
            $this->createEntities($response->entities);
        }
    }

    /**
     * Creates a new AbstractNLUEntity from entity data
     * @param $entity
     * @return AbstractNLUEntity
     */
    public function createEntity($entity): AbstractNLUEntity
    {
        return new LuisEntity($entity, $this->getQuery());
    }
}
