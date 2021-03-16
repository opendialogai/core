<?php


namespace OpenDialogAi\ContextEngine\DataClients;


use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Attributes\BasicScalarAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotLoadAttributeException;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotPersistAttributeException;

class GraphAttributeDataClient implements ContextDataClient
{
    /**
     * @inheritDoc
     */
    public function loadAttributes(string $contextId, string $userId): AttributeBag
    {
        return new BasicAttributeBag();
    }

    /**
     * @inheritDoc
     */
    public function loadAttribute(string $contextId, string $userId, string $attributeId): Attribute
    {
        return new StringAttribute('example');
    }

    /**
     * @inheritDoc
     */
    public function persistAttributes(string $contextId, string $userId, AttributeBag $attributes): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function persistAttribute(string $contextId, string $userId, Attribute $attribute): void
    {
        return;
    }
}
