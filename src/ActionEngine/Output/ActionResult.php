<?php

namespace OpenDialogAi\ActionEngine\Output;

use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBag;
use OpenDialogAi\Core\Attribute\AttributeInterface;

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
    public function isSuccessful() : bool
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
