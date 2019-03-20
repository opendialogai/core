<?php

namespace OpenDialogAi\ActionEngine\Actions;

use ActionEngine\Exceptions\ActionNameNotSetException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\AttributeInterface;

abstract class BaseAction implements ActionInterface
{
    protected $performs;

    /**
     * @var string[]
     */
    protected $requires = [];

    /**
     * @var AttributeInterface[]
     */
    private $attributes = [];

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
    public function requiresAttributes() : array
    {
        return $this->requires;
    }

    /**
     * @inheritdoc
     */
    public function requiresAttribute($attributeName): bool
    {
        return in_array($attributeName, $this->requires);
    }

    /**
     * @inheritdoc
     */
    public function setAttributeValue($attributeName, AttributeInterface $attributeValue)
    {
        if ($this->requiresAttribute($attributeName)) {
            Log::debug(sprintf("Setting attribute %s for action %s", $attributeName, static::class));
            $this->attributes[$attributeName] = $attributeValue;
        }
    }
}
