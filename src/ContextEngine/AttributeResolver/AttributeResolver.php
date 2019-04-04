<?php

namespace OpenDialogAi\ContextEngine\AttributeResolver;

use ContextEngine\AttributeResolver\AttributeCouldNotBeResolvedException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;

/**
 * The AttributeResolver maps from an attribute identifier (in the form <contextid>.<attributeid>) to the attribute type
 * for that Attribute. The mapping is donw in configuration.
 */
class AttributeResolver
{

    /* @var array */
    private $supportedAttributes;

    public function __construct()
    {
        $this->supportedAttributes = $this->getSupportedAttributes();
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSupportedAttributes()
    {
        return config('opendialog.context_engine.supported_attributes');
    }

    /**
     * @param string $attributeId
     * @return bool
     */
    public function isAttributeSupported(string $attributeId)
    {
        if (isset($this->supportedAttributes[$attributeId])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $attributeId
     * @return AttributeInterface
     */
    public function getAttributeFor(string $attributeId, $value)
    {
        if ($this->isAttributeSupported($attributeId)) {
            switch ($this->supportedAttributes[$attributeId]) {
                case 'OpenDialogAi\Core\Attribute\StringAttribute':
                    return new StringAttribute($attributeId, $value);
                case 'OpenDialogAi\Core\Attribute\IntAttribute':
                    return new IntAttribute($attributeId, $value);
                case 'OpenDialogAi\Core\Attribute\BooleanAttribute':
                    return new BooleanAttribute($attributeId, $value);
            }
        } else {
            Log::debug(sprintf('Attribute %s could not be resolved', $attributeId));
            throw new AttributeCouldNotBeResolvedException(sprintf('Attribute %s could not be resolved', $attributeId));
        }
    }
}
