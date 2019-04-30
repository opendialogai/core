<?php


namespace OpenDialogAi\ContextEngine\ContextManager;

use Ds\Map;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ContextEngine\Contexts\UserContext;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class ContextService
{
    /* @var Map $activeContexts - a container for contexts that the service is managing */
    private $activeContexts;

    /* @var UserService */
    private $userService;

    /**
     * ContextService constructor.
     */
    public function __construct()
    {
        $this->activeContexts = new Map();
    }

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * @param string $contextId
     * @return ContextInterface
     */
    public function createContext(string $contextId): ContextInterface
    {
        $newContext =  new BaseContext($contextId);
        $this->addContext($newContext);
        return $newContext;
    }

    /**
     * @param ContextInterface $context
     */
    public function addContext(ContextInterface $context): void
    {
        $this->activeContexts->put($context->getId(), $context);
    }

    /**
     * @param string $contextId
     * @return ContextInterface
     */
    public function getContext(string $contextId): ContextInterface
    {
        if ($this->hasContext($contextId)) {
            return $this->activeContexts->get($contextId);
        }

        $message = sprintf('Cannot get context with name %s - does not exist', $contextId);
        Log::debug($message);
        throw new ContextDoesNotExistException($message);
    }

    /**
     * @param AbstractCustomContext[] $contexts
     */
    public function loadCustomContexts(array $contexts): void
    {
        foreach ($contexts as $context) {
            $this->loadCustomContext($context);
        }
    }

    /**
     * @param AbstractCustomContext $customContext
     */
    public function loadCustomContext($customContext): void
    {
        if (!class_exists($customContext)) {
            Log::warning(sprintf('Not adding custom context %s, class does not exist', $customContext));
            return;
        }

        if (empty($customContext::$name)) {
            Log::warning(sprintf('Not adding custom context %s, it has no name', $customContext));
            return;
        }

        if ($this->hasContext($customContext::$name)) {
            Log::warning(sprintf(
                    'Not adding custom context %s, context with that name is already registered',
                    $customContext
                )
            );
            return;
        }

        Log::debug(sprintf('Registering custom context %s', $customContext));
        try {
            /** @var AbstractCustomContext $context */
            $context = new $customContext();
            $context->loadAttributes();
            $this->addContext($context);
        } catch (Exception $e) {
            Log::warning(sprintf('Error while adding context %s - %s', $customContext, $e->getMessage()));
        }
    }

    /**
     * @param string $contextId
     * @return bool
     */
    public function hasContext(string $contextId): bool
    {
        return $this->activeContexts->hasKey($contextId);
    }

    /**
     * @param string $attributeId
     * @param string $contextId
     * @return AttributeInterface
     * @throws ContextDoesNotExistException
     */
    public function getAttribute(string $attributeId, string $contextId): AttributeInterface
    {
        if ($this->hasContext($contextId)) {
            /* @var ContextInterface $context */
            $context = $this->getContext($contextId);
            Log::debug(
                sprintf('Attempting to retrieve attribute %s in context %s', $attributeId, $context->getId())
            );
            return $context->getAttribute($attributeId);
        }

        throw new ContextDoesNotExistException(
            sprintf('Context %s for attribute %s not available.', $contextId, $attributeId)
        );
    }

    /**
     * Calls @see ContextService::getAttribute() to resolve an attribute and returns its concrete value
     *
     * @param string $attributeId
     * @param string $contextId
     * @return mixed
     */
    public function getAttributeValue(string $attributeId, string $contextId)
    {
        return $this->getAttribute($attributeId, $contextId)->getValue();
    }


    /**
     * @param UtteranceInterface $utterance
     * @return UserContext
     * @throws FieldNotSupported
     */
    public function createUserContext(UtteranceInterface $utterance): UserContext
    {
        $userContext = new UserContext($this->userService->createOrUpdateUser($utterance), $this->userService);
        $this->addContext($userContext);
        return $userContext;
    }

    /**
     * Returns all available contexts as an array
     *
     * @return ContextInterface[]
     */
    public function getContexts(): array
    {
        return $this->activeContexts->toArray();
    }
}
