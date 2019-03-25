<?php

namespace OpenDialogAi\Core\Attribute\AttributeBag;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Exceptions\AttributeBagAttributeDoesNotExist;

class AttributeBag
{
    /** @var Map */
    private $attributes;

    /**
     * @inheritdoc
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->attributes->put($attribute->getId(), $attribute);
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($attributeName) : AttributeInterface
    {
        if ($this->hasAttribute($attributeName)) {
            Log::debug(sprintf("Returning attribute with name %", $attributeName));
            return $this->attributes->get($attributeName);
        }

        Log::debug(sprintf("Cannot return attribute with name %s - does not exist", $attributeName));
        throw new AttributeBagAttributeDoesNotExist();
    }

    /**
     * @inheritdoc
     */
    public function hasAttribute($attributeName): bool
    {
        return $this->attributes->hasKey($attributeName);
    }
}
