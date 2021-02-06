<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\ContextEngine\ContextManager\ContextServiceInterface;

class ContextEngineReflection implements ContextEngineReflectionInterface
{
    /** @var ContextServiceInterface */
    private $contextService;

    /**
     * ContextEngineReflection constructor.
     * @param ContextServiceInterface $contextService
     */
    public function __construct(ContextServiceInterface $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableContexts(): Map
    {
        return new Map($this->contextService->getContexts());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "available_contexts" => $this->getAvailableContexts()->toArray(),
        ];
    }
}
