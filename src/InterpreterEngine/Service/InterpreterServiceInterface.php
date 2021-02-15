<?php

namespace OpenDialogAi\InterpreterEngine\Service;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNotRegisteredException;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;

/**
 * Deals with registering and exposing registered interpreters and interpreting utterances into Intents
 */
interface InterpreterServiceInterface
{
    /**
     * Takes an utterance and returns the matching intents with their match score
     *
     * @param string $interpreterName Should be in the format interpreter.{namespace}.{name}
     * @param UtteranceAttribute $utterance
     * @return IntentCollection
     * @throws InterpreterNotRegisteredException
     */
    public function interpret(string $interpreterName, UtteranceAttribute $utterance) : IntentCollection;

    /**
     * Runs @see InterpreterServiceInterface::interpret() for the default interpreter
     * @param UtteranceAttribute $utterance
     * @return IntentCollection
     */
    public function interpretDefaultInterpreter(UtteranceAttribute $utterance) : IntentCollection;

    /**
     * Return the interpreter cache time if set or the global default cache time
     *
     * @param string $interpreterName Should be in the format interpreter.{namespace}.{name}
     * @return int
     */
    public function getInterpreterCacheTime(string $interpreterName): int;

    /**
     * Returns a list of all available interpreters keyed by name
     *
     * @return InterpreterInterface[]
     */
    public function getAvailableInterpreters() : array;

    /**
     * Checks if an interpreter with the given name has been registered
     *
     * @param string $interpreterName Should be in the format interpreter.{namespace}.{name}
     * @return bool
     */
    public function isInterpreterAvailable(string $interpreterName) : bool;

    /**
     * Gets the registered interpreter by name if it is registered
     * Should be in the format interpreter.{namespace}.{name}
     *
     * @param $interpreterName
     * @return InterpreterInterface
     */
    public function getInterpreter($interpreterName) : InterpreterInterface;

    /**
     * @param $interpreterName
     * @return mixed
     */
    public function setDefaultInterpreter($interpreterName);

    /**
     * @return InterpreterInterface
     */
    public function getDefaultInterpreter() : InterpreterInterface;
}
