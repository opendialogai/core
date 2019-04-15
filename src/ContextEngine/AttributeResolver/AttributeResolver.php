<?php

namespace OpenDialogAi\ContextEngine\AttributeResolver;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\AttributeInterface;

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
     * @return AttributeInterface[]
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
     * Tries to resolve an attribute with the given id to a supported type.
     *
     * @param string $attributeId
     * @param $value
     * @return AttributeInterface
     * @throws AttributeCouldNotBeResolved
     */
    public function getAttributeFor(string $attributeId, $value)
    {
        if ($this->isAttributeSupported($attributeId)) {
            return new $this->supportedAttributes[$attributeId]($attributeId, $value);
        } else {
            Log::debug(sprintf('Attribute %s could not be resolved', $attributeId));
            throw new AttributeCouldNotBeResolved(sprintf('Attribute %s could not be resolved', $attributeId));
        }
    }
}
