<?php

namespace OpenDialogAi\ActionEngine\Actions;

use OpenDialogAi\AttributeEngine\AttributeBag\AttributeBag;
use OpenDialogAi\AttributeEngine\AttributeInterface;

/**
 * An action result
 */
class ActionResult
{
    protected $success;

    protected $attributes;

    public function __construct($success)
    {
        $this->success = $success;
        $this->attributes = new AttributeBag();
    }

    /**
     * Returns an action result with success set to false
     * @return ActionResult
     */
    public static function createFailedActionResult(): ActionResult
    {
        return new self(false);
    }

    /**
     * @param AttributeInterface[] $attributes
     * @return ActionResult
     */
    public static function createSuccessfulActionResultWithAttributes($attributes)
    {
        $result = new self(true);

        foreach ($attributes as $attribute) {
            $result->addAttribute($attribute);
        }

        return $result;
    }

    /**
     * Whether the action was successfully performed
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * @return AttributeBag
     */
    public function getResultAttributes(): AttributeBag
    {
        return $this->attributes;
    }

    /**
     * @param $attributeName
     * @return AttributeInterface
     */
    public function getResultAttribute($attributeName): AttributeInterface
    {
        return $this->attributes->getAttribute($attributeName);
    }

    /**
     * Adds the given attribute to the attribute bag
     *
     * @param AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->attributes->addAttribute($attribute);
    }
}
