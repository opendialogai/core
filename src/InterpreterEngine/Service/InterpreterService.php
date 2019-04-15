<?php

namespace OpenDialogAi\InterpreterEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNotRegisteredException;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

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
        return $this->getInterpreter($interpreterName)->interpret($utterance);
    }

    /**
     * @inheritdoc
     */
    public function getAvailableInterpreters(): array
    {
        if (empty($this->availableInterpreters)) {
            $this->registerAvailableInterpreters();
        }

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
     */
    public function registerAvailableInterpreters(): void
    {
        /** @var InterpreterInterface $interpreter */
        foreach ($this->getAvailableInterpreterConfig() as $interpreter) {
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
    private function isValidName(string $name) : bool
    {
        return preg_match($this->validNamePattern, $name) === 1;
    }

    /**
     * Returns the list of available interpreters as registered in the available_interpreters config
     *
     * @return InterpreterInterface[]
     */
    private function getAvailableInterpreterConfig()
    {
        return config('opendialog.interpreter_engine.available_interpreters');
    }
}
