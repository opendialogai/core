<?php


namespace OpenDialogAi\ContextEngine\ContextManager;


use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;

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
     */
    public function getAttribute(string $attributeName): AttributeInterface;

    /**
     * Adds an attribute to this context.
     *
     * @param AttributeInterface $attribute
     * @return mixed
     */
    public function addAttribute(AttributeInterface $attribute);
}
