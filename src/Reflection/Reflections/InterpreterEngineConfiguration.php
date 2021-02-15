<?php


namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use JsonSerializable;

class InterpreterEngineConfiguration implements JsonSerializable
{
    /** @var string */
    private $configurationKey;

    /**
     * InterpreterEngineConfiguration constructor.
     * @param string $configurationKey
     */
    public function __construct(string $configurationKey)
    {
        $this->configurationKey = $configurationKey;
    }

    /**
     * @return string|null
     */
    public function getDefaultInterpreter(): ?string
    {
        return config("$this->configurationKey.default_interpreter");
    }

    /**
     * @return int|null
     */
    public function getDefaultCacheTime(): ?int
    {
        return config("$this->configurationKey.default_cache_time");
    }

    /**
     * @return Map|null
     */
    public function getSupportedCallbacks(): ?Map
    {
        $supportedCallbacks = config("$this->configurationKey.supported_callbacks");
        return !is_null($supportedCallbacks) ? new Map($supportedCallbacks) : $supportedCallbacks;
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
