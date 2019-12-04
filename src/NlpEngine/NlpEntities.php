<?php

namespace OpenDialogAi\Core\NlpEngine;

class NlpEntities
{
    /** @var string */
    private $input;

    /** @var array */
    private $entities;

    /**
     * @return string
     */
    public function getInput(): string
    {
        return $this->input;
    }

    /**
     * @param string $input
     */
    public function setInput(string $input): void
    {
        $this->input = $input;
    }

    /**
     * @return array
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @param NlpEntity $entity
     */
    public function addEntities(NlpEntity $entity): void
    {
        $this->entities[] = $entity;
    }
}
