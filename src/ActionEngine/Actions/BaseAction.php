<?php

namespace OpenDialogAi\ActionEngine\Actions;

use ActionEngine\Exceptions\ActionNameNotSetException;
use ActionEngine\Exceptions\AttributeNotResolvedException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\AttributeInterface;

abstract class BaseAction implements ActionInterface
{
    protected $performs;

    /** @var string[] */
    protected $requiredAttributes = [];

    /** @var string[] */
    protected $outputsAttributes = [];

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
    public function getAttribute($attributeName): AttributeInterface
    {
        if (isset($this->attributes[$attributeName])) {
            return $this->attributes[$attributeName];
        }

        throw new AttributeNotResolvedException(sprintf("Attribute %s has not been resolved", $attributeName));
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
