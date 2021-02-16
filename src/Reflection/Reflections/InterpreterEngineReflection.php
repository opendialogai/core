<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class InterpreterEngineReflection implements InterpreterEngineReflectionInterface
{
    /** @var InterpreterServiceInterface */
    private $interpreterService;

    /**
     * @var string
     */
    private $configurationKey;

    /**
     * InterpreterEngineReflection constructor.
     * @param InterpreterServiceInterface $interpreterService
     * @param string $configurationKey
     */
    public function __construct(InterpreterServiceInterface $interpreterService, string $configurationKey)
    {
        $this->interpreterService = $interpreterService;
        $this->configurationKey = $configurationKey;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableInterpreters(): Map
    {
        return new Map($this->interpreterService->getAvailableInterpreters());
    }

    /**
     * @inheritDoc
     */
    public function getEngineConfiguration(): InterpreterEngineConfiguration
    {
        return new InterpreterEngineConfiguration($this->configurationKey);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $interpreters = $this->getAvailableInterpreters();

        $interpretersWithData = array_map(function ($interpreter) {
            /** @var $interpreter BaseInterpreter */
            return [
                'component_data' => (array) $interpreter::getComponentData(),
            ];
        }, $interpreters->toArray());

        return [
            "available_interpreters" => $interpretersWithData,
            "engine_configuration" => $this->getEngineConfiguration()
        ];
    }
}
