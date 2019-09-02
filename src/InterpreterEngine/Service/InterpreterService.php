<?php

namespace OpenDialogAi\InterpreterEngine\Service;

use Exception;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNotRegisteredException;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;

class InterpreterService implements InterpreterServiceInterface
{
    /* @var string A regex pattern for a valid interpreter name */
    private $validNamePattern = "/^interpreter\.[A-Za-z]*\.[A-Za-z_]*$/";

    /**
     * A place to store a cache of available interpreters
     * @var InterpreterInterface[]
     */
    private $availableInterpreters = [];

    /* @var InterpreterInterface - the default interpreter to use when intent does not define its own */
    private $defaultInterpreter;

    /**
     * @inheritdoc
     */
    public function interpret(string $interpreterName, UtteranceInterface $utterance): array
    {
        if ($cachedResult = $this->getInterpreterResultFromCache($interpreterName, $utterance)) {
            Log::info(sprintf("Getting result from the cache for interpreter %s", $interpreterName));
            return $cachedResult;
        }

        $intepreterResult = $this->getInterpreter($interpreterName)->interpret($utterance);
        $this->putInterpreterResultToCache($interpreterName, $utterance, $intepreterResult);

        return $intepreterResult;
    }

    /**
     * @inheritDoc
     */
    public function getInterpreterCacheTime(string $interpreterName): int
    {
        $interpreterCacheTimes = config('opendialog.interpreter_engine.interpreter_cache_times');

        if (is_array($interpreterCacheTimes) && isset($interpreterCacheTimes[$interpreterName])) {
            return $interpreterCacheTimes[$interpreterName];
        }

        $defaultCacheTime = config('opendialog.interpreter_engine.default_cache_time');
        return $defaultCacheTime;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableInterpreters(): array
    {
        return $this->availableInterpreters;
    }

    /**
     * @inheritdoc
     */
    public function getInterpreter($interpreterName): InterpreterInterface
    {
        if ($this->isInterpreterAvailable($interpreterName)) {
            Log::debug(sprintf("Getting interpreter with name %s", $interpreterName));
            return $this->availableInterpreters[$interpreterName];
        }

        throw new InterpreterNotRegisteredException("Interpreter with name $interpreterName is not available");
    }

    /**
     * @inheritdoc
     */
    public function isInterpreterAvailable(string $interpreterName): bool
    {
        if (in_array($interpreterName, array_keys($this->getAvailableInterpreters()))) {
            Log::debug(sprintf("Interpreter with name %s is available", $interpreterName));
            return true;
        }

        Log::debug(sprintf("Interpreter with name %s is not available", $interpreterName));
        return false;
    }

    /**
     * Loops through all available interpreters from config, and creates a local array keyed by the name of the
     * interpreter
     *
     * @param $interpreters InterpreterInterface[]
     */
    public function registerAvailableInterpreters($interpreters): void
    {
        /** @var InterpreterInterface $interpreter */
        foreach ($interpreters as $interpreter) {
            try {
                $name = $interpreter::getName();

                if ($this->isValidName($name)) {
                    $this->availableInterpreters[$name] = new $interpreter();
                } else {
                    Log::warning(
                        sprintf("Not adding interpreter with name %s. Name is in wrong format", $name)
                    );
                }
            } catch (InterpreterNameNotSetException $e) {
                Log::warning(
                    sprintf("Not adding interpreter %s. It has not defined a name", $interpreter)
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setDefaultInterpreter($defaultInterpreterName)
    {
        $this->defaultInterpreter = $this->getInterpreter($defaultInterpreterName);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultInterpreter(): InterpreterInterface
    {
        return $this->defaultInterpreter;
    }

    /**
     * Checks if the name of the interpreter is in the right format
     *
     * @param string $name
     * @return bool
     */
    private function isValidName(string $name): bool
    {
        return preg_match($this->validNamePattern, $name) === 1;
    }

    /**
     * Gets a cached value from
     *
     * @param string $interpreterName
     * @param UtteranceInterface $utterance
     * @return array
     */
    private function getInterpreterResultFromCache(string $interpreterName, UtteranceInterface $utterance): array
    {
        $cacheKey = $this->generateCacheKey($interpreterName, $utterance);

        try {
            return cache($cacheKey, false);
        } catch (Exception $e) {
            Log::error(sprintf('Unable to retrieve interpreter cache with name %s - %s', $cacheKey, $e->getMessage()));
            return [];
        }
    }

    /**
     * Saves the interpreter result to cache
     *
     * @param string $interpreterName
     * @param UtteranceInterface $utterance
     * @param array $intents
     * @return bool
     */
    private function putInterpreterResultToCache(string $interpreterName, UtteranceInterface $utterance, array $intents): bool
    {
        $cacheKey = $this->generateCacheKey($interpreterName, $utterance);
        $cacheTime = $this->getInterpreterCacheTime($interpreterName);

        try {
            return cache([$cacheKey => $intents], $cacheTime);
        } catch (Exception $e) {
            Log::error(sprintf('Unable to save cache interpreter with name %s - %s', $cacheKey, $e->getMessage()));
            return false;
        }
    }

    /**
     * Returns the name of the interpreter and a serialisation of the entire utterance.
     *
     * @param string $interpreterName
     * @param UtteranceInterface $utterance
     * @return string
     */
    private function generateCacheKey(string $interpreterName, UtteranceInterface $utterance): string
    {
        $cacheKey = $interpreterName . '|' . serialize($utterance);
        return $cacheKey;
    }
}
