<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\ResponseEngine\Formatters\BaseMessageFormatter;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class ResponseEngineReflection implements ResponseEngineReflectionInterface
{
    /** @var ResponseEngineServiceInterface */
    private $responseEngineService;

    /**
     * ResponseEngineReflection constructor.
     * @param ResponseEngineServiceInterface $responseEngineService
     */
    public function __construct(ResponseEngineServiceInterface $responseEngineService)
    {
        $this->responseEngineService = $responseEngineService;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableFormatters(): Map
    {
        return new Map($this->responseEngineService->getAvailableFormatters());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $formatters = $this->getAvailableFormatters();

        $formattersWithData = array_map(function ($formatter) {
            /** @var $formatter BaseMessageFormatter */
            return [
                'component_data' => (array) $formatter::getComponentData(),
            ];
        }, $formatters->toArray());

        return [
            "available_formatters" => $formattersWithData,
        ];
    }
}
