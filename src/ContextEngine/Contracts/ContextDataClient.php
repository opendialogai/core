<?php


namespace OpenDialogAi\ContextEngine\Contracts;


use Ds\Map;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotLoadAttributeException;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotPersistAttributeException;

/**
 * A ContextDataClient works with a context to retrieve attributes from persistent storage or
 * to persist attributes.
 */
interface ContextDataClient
{
    /**
     * Retrieves all persisted attributes for the given context-user pair.
     *
     * @param string $contextId
     * @param string $userId
     * @return AttributeBag
     * @throws CouldNotLoadAttributeException
     */
    public function loadAttributes(string $contextId, string $userId): AttributeBag;

    /**
     * Retrieves a desired persisted attribute for the given context-user pair.
     *
     * @param string $contextId
     * @param string $userId
     * @param string $attributeId
     * @return Attribute
     * @throws CouldNotLoadAttributeException
     */
    public function loadAttribute(string $contextId, string $userId, string $attributeId): Attribute;

    /**
     * Persists the given attributes to the context-user pair.
     *
     * @param string $contextId
     * @param string $userId
     * @param AttributeBag $attributes
     * @throws CouldNotPersistAttributeException
     */
    public function persistAttributes(string $contextId, string $userId, AttributeBag $attributes): void;

    /**
     * Persists the given attribute to the context-user pair.
     *
     * @param string $contextId
     * @param string $userId
     * @param Attribute $attribute
     * @throws CouldNotPersistAttributeException
     */
    public function persistAttribute(string $contextId, string $userId, Attribute $attribute): void;
}
