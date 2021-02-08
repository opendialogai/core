<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\ContextEngine\Contracts\ContextService;

class ContextEngineReflection implements ContextEngineReflectionInterface
{
    /** @var ContextService */
    private $contextService;

    /**
     * ContextEngineReflection constructor.
     * @param ContextService $contextService
     */
    public function __construct(ContextService $contextService)
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
