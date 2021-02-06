<?php

namespace OpenDialogAi\ContextEngine\Contracts;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;

interface ContextService
{

    /**
     * Creates a standard context under the specified contextId, enabling an application
     * to store and retrieve information.
     *
     * @param string $contextId
     * @return Context
     */
    public function createContext(string $contextId): Context;

    /**
     * Should be used to add a context that is already instantiated or has a more complex implementation.
     *
     * @param Context $context
     */
    public function addContext(Context $context): void;

    /**
     * @param string $contextId
     * @return Context
     * @throws ContextDoesNotExistException
     */
    public function getContext(string $contextId): Context;

    /**
     * @param string $contextId
     * @return bool
     */
    public function hasContext(string $contextId): bool;

    /**
     * Saves the attribute provided against a context.
     * If the $attributeName is namespace with a context name, will try to save in the named context.
     * If the named context does not exist or the attribute name is not namespaced,
     * will save against a default context (typically something like a session context).
     *
     * @param string $attributeName
     * @param $attributeValue
     */
    public function saveAttribute(string $attributeName, $attributeValue): void;

    /**
     * @param string $attributeId
     * @param string $contextId
     * @return Attribute
     */
    public function getAttribute(string $attributeId, string $contextId): Attribute;

    /**
     * Calls @param string $attributeId
     * @param string $contextId
     * @param array $index
     * @return mixed
     * @see ContextService::getAttribute() to resolve an attribute and returns its concrete value
     *
     */
    public function getAttributeValue(string $attributeId, string $contextId, array $index = []);

    /**
     * Returns all available contexts as an array
     *
     * @return Context[]
     */
    public function getContexts(): array;

}
