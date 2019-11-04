<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

use OpenDialogAi\Core\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;

class RasaResponse extends AbstractNLUResponse
{
    public function __construct(array $response)
    {
        $this->query = isset($response->text) ? $response->text : null;
        $this->topScoringIntent = isset($response->intent) ? new RasaIntent($response->intent) : null;

        if (isset($response->entities)) {
            $this->createEntities($response->entities);
        }
    }

    /**
     * Creates a new AbstractNLUEntity from entity data
     * @param array $entity
     * @return AbstractNLUEntity
     */
    public function createEntity(array $entity): AbstractNLUEntity
    {
        return new RasaEntity($entity, $this->getQuery());
    }
}
