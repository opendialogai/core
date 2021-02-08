<?php

namespace OpenDialogAi\ActionEngine\Actions;

use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;

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
        $this->attributes = new BasicAttributeBag();
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
     * @param Attribute[] $attributes
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
     * @return \OpenDialogAi\AttributeEngine\Contracts\Attribute
     */
    public function getResultAttribute($attributeName): Attribute
    {
        return $this->attributes->getAttribute($attributeName);
    }

    /**
     * Adds the given attribute to the attribute bag
     *
     * @param \OpenDialogAi\AttributeEngine\Contracts\Attribute $attribute
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes->addAttribute($attribute);
    }
}
