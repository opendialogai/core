<?php


namespace OpenDialogAi\ContextEngine\ContextManager;


use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ContextEngine\Contexts\UserContext;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class ContextService
{
    const UNDEFINED_CONTEXT = 'undefined_context';

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
    public function setUserService(UserService $userService)
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
    public function addContext(ContextInterface $context)
    {
        $this->activeContexts->put($context->getId(), $context);
    }

    /**
     * @param string $contextId
     * @return ContextInterface
     */
    public function getContext(string $contextId)
    {
        if ($this->hasContext($contextId)) {
            return $this->activeContexts->get($contextId);
        }

        Log::debug(sprintf("Cannot get context with name %s - does not exist", $contextId));
        throw new ContextDoesNotExistException();
    }

    /**
     * @param string $contextId
     * @return bool
     */
    public function hasContext(string $contextId)
    {
        return $this->activeContexts->hasKey($contextId);
    }

    /**
     * @param string $attributeId
     * @return AttributeInterface
     */
    public function getAttribute(string $attributeId, string $contextId): AttributeInterface
    {
        if ($this->hasContext($contextId)) {
            /* @var ContextInterface $context */
            $context = $this->getContext($contextId);
            Log::debug(
                sprintf("Attempting to retrieve attribute %s in context %s", $attributeId, $context->getId())
            );
            return $context->getAttribute($attributeId);
        }

        throw new ContextDoesNotExistException(
            sprintf('Context %s for attribute %s not available.', $contextId, $attributeId)
        );
    }

    /**
     * @param UtteranceInterface $utterance
     * @return UserContext
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     */
    public function createUserContext(UtteranceInterface $utterance)
    {
        $userContext = new UserContext($this->userService->createOrUpdateUser($utterance), $this->userService);
        $this->addContext($userContext);
        return $userContext;
    }
}
