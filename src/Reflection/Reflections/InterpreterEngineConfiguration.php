<?php


namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use JsonSerializable;

class InterpreterEngineConfiguration implements JsonSerializable
{
    /** @var string */
    private $defaultInterpreter;

    /** @var int */
    private $defaultCacheTime;

    /** @var Map */
    private $supportedCallbacks;

    /** @var array */
    private $configuration;

    /**
     * InterpreterEngineConfiguration constructor.
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getDefaultInterpreter(): string
    {
        return $this->defaultInterpreter;
    }

    /**
     * @return int
     */
    public function getDefaultCacheTime(): int
    {
        return $this->defaultCacheTime;
    }

    /**
     * @return Map
     */
    public function getSupportedCallbacks(): Map
    {
        return $this->supportedCallbacks;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "default_interpreter" => $this->getDefaultInterpreter(),
            "default_cache_time" => $this->getDefaultCacheTime(),
            "supported_callbacks" => $this->getSupportedCallbacks(),
        ];
    }
}
