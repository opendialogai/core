<?php

namespace OpenDialogAi\ContextEngine\Contexts;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotLoadAttributeException;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotPersistAttributeException;

abstract class PersistentContext extends AbstractContext
{
    private string $userId;

    public function __construct(ContextDataClient $dataClient)
    {
        parent::__construct($dataClient);
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @throws CouldNotLoadAttributeException
     */
    public function loadAttributes(): void
    {
        if (!isset($this->userId)) {
            throw new CouldNotLoadAttributeException('User ID not set.');
        }

        $attributes = $this->dataClient->loadAttributes(self::getComponentId(), $this->userId);
        $this->setAttributes($attributes->getAttributes());
    }

    /**
     * Retrieve an attribute, if $refresh is true then it will be re-retrieved from the data source.
     *
     * @param string $attributeName
     * @param bool $refresh
     * @return Attribute
     * @throws CouldNotLoadAttributeException
     */
    public function getAttribute(string $attributeName, bool $refresh = false): Attribute
    {
        if (!isset($this->userId)) {
            throw new CouldNotLoadAttributeException('User ID not set.');
        }

        if ($this->hasAttribute($attributeName) && !$refresh) {
            return parent::getAttribute($attributeName);
        }

        $attribute = $this->dataClient->loadAttribute(self::getComponentId(), $this->userId, $attributeName);
        $this->addAttribute($attribute);
        return parent::getAttribute($attributeName);
    }

    /**
     * @param bool $refresh
     * @return Map
     * @throws CouldNotLoadAttributeException
     */
    public function getAttributes(bool $refresh = false): Map
    {
        if (!isset($this->userId)) {
            throw new CouldNotLoadAttributeException('User ID not set.');
        }

        if (!parent::getAttributes()->isEmpty() && !$refresh) {
            return parent::getAttributes();
        }

        $this->loadAttributes();
        return parent::getAttributes();
    }

    /**
     * Persists all the attributes in the context.
     *
     * @return bool
     */
    public function persist(): bool
    {
        try {
            $attributes = parent::getAttributes();
            $attributeBag = new BasicAttributeBag();
            $attributeBag->setAttributes($attributes);
            $this->dataClient->persistAttributes(self::getComponentId(), $this->userId, $attributeBag);
            return true;
        } catch (CouldNotPersistAttributeException $e) {
            return false;
        }
    }
}

