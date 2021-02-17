<?php

namespace OpenDialogAi\AttributeEngine\AttributeResolver;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Attributes\AbstractAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;
use OpenDialogAi\AttributeEngine\Exceptions\UnsupportedAttributeTypeException;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * The AttributeResolver maps from an attribute identifier to the attribute type for that Attribute.
 * Attribute types extend the @see AbstractAttribute
 */
class AttributeResolver
{
    public static $validIdPattern = "/^([a-z]+_)*[a-z]+$/";
    public static $validTypePattern = "/^attribute\.[A-Za-z]+\.[A-Za-z_]+$/";

    /** @var Map|AttributeDeclaration */
    private Map $supportedAttributes;
    private $attributeTypeService;

    public function __construct()
    {
        $this->supportedAttributes = new Map();
        $this->attributeTypeService = resolve(AttributeTypeServiceInterface::class);
    }

    /**
     * @return AttributeDeclaration[]|Map
     */
    public function getSupportedAttributes(): Map
    {
        return $this->supportedAttributes;
    }

    /**
     * Checks if the id of the DynamicAttribute is in the right format
     *
     * @param  string  $id
     *
     * @return bool
     */
    public static function isValidId(string $id): bool
    {
        return preg_match(AttributeResolver::$validIdPattern, $id) === 1;
    }

    /**
     * Checks if the type of the DynamicAttribute is in the right format
     *
     * @param  string  $type
     *
     * @return bool
     */
    public static function isValidType(string $type): bool
    {
        return preg_match(AttributeResolver::$validTypePattern, $type) === 1;
    }

    /**
     * Registers an array of attributes. The original set of attributes is preserved so this can be run multiple times
     *
     * @param $attributes string[]|Attribute[] Array of attribute class names
     * @param $source string
     * @throws UnsupportedAttributeTypeException
     */
    public function registerAttributes(array $attributes, string $source = ODComponentTypes::APP_COMPONENT_SOURCE): void
    {
        foreach ($attributes as $name => $attributeTypeClass) {
            if ($this->attributeTypeService->isAttributeTypeClassRegistered($attributeTypeClass)) {
                $this->supportedAttributes->put(
                    $name,
                    new AttributeDeclaration($name, $attributeTypeClass, $source)
                );
            } else {
                Log::error(sprintf(
                    "Not registering attribute %s as it has an unknown type %s, please ensure all "
                        . "custom attribute types are registered.",
                    $name,
                    $attributeTypeClass
                ));
                throw new UnsupportedAttributeTypeException();
            }
        }
    }

    /**
     * @param string $attributeId
     * @return bool
     */
    public function isAttributeSupported(string $attributeId): bool
    {
        return $this->supportedAttributes->hasKey($attributeId);
    }

    /**
     * Tries to resolve an attribute with the given id to a supported type.
     *
     * @param string $attributeId
     * @param $value
     * @return Attribute
     */
    public function getAttributeFor(string $attributeId, $value = null): Attribute
    {
        if ($this->isAttributeSupported($attributeId)) {
            // First instantiate the attribute so we can see what type it is and construct appropriately.
            $attributeDeclaration = $this->supportedAttributes->get($attributeId);
            $attributeType = $attributeDeclaration->getAttributeTypeClass();
            $attribute = (new $attributeType($attributeId));

            // For scalar attribute we prefer setting the AttributeValue object, but if that is not
            // available we set the raw value. Scalar attributes should always be able to handle null
            // raw values as well.
            if ($attribute instanceof ScalarAttribute) {
                if ($value instanceof AttributeValue) {
                    $attribute->setAttributeValue($value);
                } elseif ($value instanceof ScalarAttribute) {
                    $attribute->setRawValue($value->getAttributeValue()->getRawValue());
                } else {
                    $attribute->setRawValue($value);
                }
            }

            // For composite attributes we expect to either be provided with a prepopulated Ds\Map of
            // attributes or with a single attribute that we add to the composite attribute or with the
            // actual CompositeAttribute itself that we return back (the latter happens when we ask the context
            // service to save a composite attribute and the context service attempts to resolve it to determine if
            // it is a valid attribute tye).
            if ($attribute instanceof CompositeAttribute) {
                if (($value instanceof CompositeAttribute) && ($value->getId() == $attribute->getId())) {
                    return $value;
                }
                if ($value instanceof Map) {
                    $attribute->setAttributes($value);
                } elseif ($value instanceof Attribute) {
                    $attribute->addAttribute($value);
                } elseif (!isset($value)) {
                    return $attribute;
                }
            }
            return $attribute;
        } else {
            Log::debug(sprintf('Attribute %s is not registered, defaulting to String type', $attributeId));
            return new StringAttribute($attributeId, $value);
        }
    }


    /**
     * @return string
     */
    public function getValidIdPattern(): string
    {
        return static::$validIdPattern;
    }

    /**
     * @return string
     */
    public function getValidTypePattern(): string
    {
        return static::$validTypePattern;
    }
}
