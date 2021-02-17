<?php

namespace OpenDialogAi\ContextEngine\Contexts;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;
use OpenDialogAi\ContextEngine\Exceptions\UnableToLoadAttributeFromPersistentStorageException;

class PersistentContext extends AbstractContext
{

    protected $persistentAttributes = [];

    protected ContextDataClient $dataClient;

    public function __construct(ContextDataClient $dataClient)
    {
        parent::__construct();
        $this->dataClient = $dataClient;
    }

    /**
     * If we are dealing with an attribute that is not persistent try to retrieve as usual, alternatively
     * if it is a persistent attributet has not already been retrieved or if we are meant to refresh its
     * value we access through the data client.
     *
     * @param string $attributeName
     * @param bool $refresh
     * @return Attribute
     */
    public function getAttribute(string $attributeName, bool $refresh = false): Attribute
    {
        if (!$this->isPersistentAttribute($attributeName)) {
            return parent::getAttribute($attributeName);
        }

        if ($this->hasAttribute($attributeName) && !$refresh) {
            return parent::getAttribute($attributeName);
        } elseif (($this->hasAttribute($attributeName) && $refresh) || (!$this->hasAttribute($attributeName))) {
            try {
                $attribute = $this->dataClient->loadAttribute($attributeName);
                $this->addAttribute($attribute);
                return parent::getAttribute($attributeName);
            } catch (UnableToLoadAttributeFromPersistentStorageException $e) {
                Log::debug(sprintf("Cannot return attribute with name %s - does not exist", $attributeName));
                throw new AttributeDoesNotExistException(
                    sprintf("Cannot return attribute with name %s - does not exist", $attributeName)
                );
            }
        }
    }

    /**
     * We first ensure that all persistent attribuets are loaded and/or are refreshed and then return the attribute
     * map as usual.
     * @param bool $refresh
     * @return Map
     */
    public function getAttributes(bool $refresh = false): Map
    {
        // Before we return attributes let us make sure all persistent attributes are available
        foreach ($this->persistentAttributes as $attributeName) {
            if (!$this->hasAttribute($attributeName) || $refresh) {
                $attribute = $this->dataClient->loadAttribute($attributeName);
                $this->attributes->put($attribute->getId(), $attribute);
            }
        }

        return parent::getAttributes();
    }

    public function isPersistentAttribute(string $attributeName): bool
    {
        if (in_array($attributeName, $this->persistentAttributes)) {
            return true;
        }

        return false;
    }

}

