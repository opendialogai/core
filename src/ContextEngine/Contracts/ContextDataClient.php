<?php


namespace OpenDialogAi\ContextEngine\Contracts;


use Ds\Map;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;

/**
 * A ContextDataClient works with a context to retrieve attributes from persistent storage or
 * to persist attributes.
 */
interface ContextDataClient
{
    /**
     * Retrieve the specified attribute.
     * @param string $attributeName
     * @return Attribute
     */
    public function loadAttribute(string $attributeName): Attribute;

    /**
     * Retrieve all the specified attributes.
     * @param array $attributes
     * @return Map
     */
    public function loadAttributes(array $attributes): Map;

    /**
     * Persist the specified attribute for the relevant context.
     * @param string $attributeName
     * @param string $context
     * @return bool
     */
    public function persistAttribute(string $attributeName, string $context): bool;

    /**
     * Persist all the specified attributes.
     * @param array $attributes
     * @param string $context
     * @return bool
     */
    public function persistAttributes(array $attributes, string $context): bool;
}
