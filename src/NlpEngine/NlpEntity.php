<?php

namespace OpenDialogAi\Core\NlpEngine;

class NlpEntity
{
    /** @var string */
    private $input;

    /** @var string */
    private $name;

    /** @var array */
    private $matches;

    /** @var string */
    private $type;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @param NlpEntityMatch $match
     */
    public function addMatch(NlpEntityMatch $match): void
    {
        $this->matches[] = $match;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
