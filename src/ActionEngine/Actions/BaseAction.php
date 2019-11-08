<?php

namespace OpenDialogAi\ActionEngine\Actions;

use Ds\Map;
use OpenDialogAi\ActionEngine\Exceptions\ActionNameNotSetException;

abstract class BaseAction implements ActionInterface
{
    protected $performs;

    /** @var string[] */
    protected $requiredAttributes = [];

    /** @var Map */
    protected $inputAttributes = [];

    /** @var Map */
    protected $outputAttributes = [];

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
    public function setInputAttributes($inputAttributes)
    {
        $this->inputAttributes = $inputAttributes;
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
    public function getInputAttributes(): Map
    {
        return $this->inputAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getOutputAttributes(): Map
    {
        return $this->outputAttributes;
    }

    /**
     * @inheritdoc
     */
    public function outputsAttribute($attributeName): bool
    {
        return $this->outputAttributes->hasKey($attributeName);
    }
}
