<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\ContextEngine\Contracts\Context;
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
        $contexts = $this->getAvailableContexts();

        $contextsWithData = array_map(function ($context) {
            /** @var $context Context */
            return [
                'component_data' => (array) $context::getComponentData(),
                'context_data' => [
                    'attributesReadOnly' => $context::attributesAreReadOnly()
                ]
            ];
        }, $contexts->toArray());

        return [
            "available_contexts" => $contextsWithData,
        ];
    }
}
