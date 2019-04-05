<?php

namespace OpenDialogAi\ActionEngine\Actions;

use OpenDialogAi\ActionEngine\Exceptions\ActionNameNotSetException;

abstract class BaseAction implements ActionInterface
{
    protected $performs;

    /** @var string[] */
    protected $requiredAttributes = [];

    /** @var string[] */
    protected $outputsAttributes = [];

    /**
     * @inheritdoc
     */
    public function performs(): string
    {
        if (!isset($this->performs)) {
            throw new ActionNameNotSetException();
        }

        return $this->performs;
    }

    /**
     * @inheritdoc
     */
    public function getRequiredAttributes(): array
    {
        return $this->requiredAttributes;
    }

    /**
     * @inheritdoc
     */
    public function requiresAttribute($attributeName): bool
    {
        return in_array($attributeName, $this->requiredAttributes);
    }

    /**
     * @inheritdoc
     */
    public function getOutputAttributes(): array
    {
        return $this->outputsAttributes;
    }

    /**
     * @inheritdoc
     */
    public function outputsAttribute($attributeName): bool
    {
        return in_array($attributeName, $this->outputsAttributes);
    }
}
