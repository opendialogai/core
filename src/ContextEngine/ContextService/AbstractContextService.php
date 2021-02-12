<?php


namespace OpenDialogAi\ContextEngine\ContextService;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\BaseContext;
use OpenDialogAi\ContextEngine\Contracts\Context;
use OpenDialogAi\ContextEngine\Contracts\ContextService;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;

abstract class AbstractContextService implements ContextService
{
    /* @var Map $contexts - a container for contexts that the service is managing
     */
    private $contexts;

    public function __construct()
    {
        $this->contexts = new Map();
    }

    /**
     * @inheritDoc
     */
    public function createContext(string $contextId): Context
    {
        $newContext = new BaseContext($contextId);
        $this->addContext($newContext);
        return $newContext;
    }

    /**
     * @inheritDoc
     */
    public function addContext(Context $context): void
    {
        $this->contexts->put($context->getId(), $context);
    }

    /**
     * @inheritDoc
     */
    public function getContext(string $contextId): Context
    {
        if ($this->hasContext($contextId)) {
            return $this->contexts->get($contextId);
        }

        $message = sprintf('Cannot get context with name %s - does not exist', $contextId);
        Log::debug($message);
        throw new ContextDoesNotExistException($message);
    }

    /**
     * @inheritDoc
     */
    public function hasContext(string $contextId): bool
    {
        return $this->contexts->hasKey($contextId);
    }


    /**
     * @inheritDoc
     */
    public function saveAttribute(string $attributeName, $attributeValue): void
    {
        try {
            $context = $this->getContext(ContextParser::determineContextId($attributeName));
        } catch (ContextDoesNotExistException $e) {
            Log::debug(
                sprintf('Trying to save attribute without context id, using session context %s', $attributeName)
            );
        }

        $attributeId = ContextParser::determineAttributeId($attributeName);
        $attribute = AttributeResolver::getAttributeFor($attributeId, $attributeValue);

        $context->addAttribute($attribute);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $attributeId, string $contextId): Attribute
    {
        /* @var Context $context */
        $context = ($this->hasContext($contextId)) ? $this->getContext($contextId) : $this->getSessionContext();

        Log::debug(
            sprintf('Attempting to retrieve attribute %s in context %s', $attributeId, $context->getId())
        );

        try {
            return $context->getAttribute($attributeId);
        } catch (AttributeDoesNotExistException $e) {
            Log::warning(
                sprintf('Attribute %s does not exist in context %s', $attributeId, $context->getId())
            );

            return AttributeResolver::getAttributeFor($attributeId, '');
        }
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValue(string $attributeId, string $contextId)
    {
        return $this->getAttribute($attributeId, $contextId)->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getContexts(): array
    {
        return $this->contexts->toArray();
    }



}
