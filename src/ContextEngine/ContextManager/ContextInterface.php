<?php

namespace OpenDialogAi\ContextEngine\ContextManager;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeDoesNotExistException;
use OpenDialogAi\AttributeEngine\AttributeInterface;

/**
 * A context is a semantically-related grouping of Attributes. It provides
 * a simpler way for other components of OpenDialog to extract related context
 * without having to ask for each individual attribute.
 */
interface ContextInterface
{
    /**
     * Returns the context's identifier (e.g. user, conversation)
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Returns all the attributes currently associated with this context.
     *
     * @return Map
     */
    public function getAttributes(): Map;

    /**
     * Retrieves an attribute, if present, from the context. It is always up to the calling service to let us know
     * what context we should use.
     *
     * @param string $attributeName
     * @return AttributeInterface
     * @throws AttributeDoesNotExistException
     */
    public function getAttribute(string $attributeName): AttributeInterface;

    /**
     * Adds an attribute to this context.
     *
     * @param AttributeInterface $attribute
     * @return mixed
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Removes an attribute from the context if there is one with the given ID
     *
     * @param string $attributeName
     * @return bool true if removed, false if not
     */
    public function removeAttribute(string $attributeName): bool;

    /**
     * Persist context value.
     * In the case where a context does not need to be persisted, it can do nothing, or just create a log message.
     *
     * @return bool true if successful, false if not
     */
    public function persist(): bool;
}
