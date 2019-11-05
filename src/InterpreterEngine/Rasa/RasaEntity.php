<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

use OpenDialogAi\InterpreterEngine\AbstractNLU\AbstractNLUEntity;

class RasaEntity extends AbstractNLUEntity
{
    public function __construct($entity, $query)
    {
        $this->query = $query;
        $this->type = $entity->entity;

        if (isset($entity->start)) {
            $this->startIndex = $entity->start;
            $this->endIndex = $entity->end;

            $this->entityString = substr($query, $this->startIndex, $this->endIndex);
        } else {
            $this->entityString = $this->type;
        }

        if (isset($entity->confidence)) {
            $this->score = floatval($entity->confidence);
        }
    }
}
