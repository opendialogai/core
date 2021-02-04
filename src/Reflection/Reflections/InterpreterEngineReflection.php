<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
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
        return [
            "available_interpreters" => $this->getAvailableInterpreters()->toArray(),
            "engine_configuration" => $this->getEngineConfiguration()
        ];
    }
}
