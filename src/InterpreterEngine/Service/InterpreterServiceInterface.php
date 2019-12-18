<?php

namespace OpenDialogAi\InterpreterEngine\Service;

use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
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
     * @param UtteranceInterface $utterance
     * @return Intent[]
     * @throws InterpreterNotRegisteredException
     */
    public function interpret(string $interpreterName, UtteranceInterface $utterance) : array;

    /**
     * Runs @see InterpreterServiceInterface::interpret() for the default interpreter
     * @param UtteranceInterface $utterance
     * @return Intent[]
     */
    public function interpretDefaultInterpreter(UtteranceInterface $utterance) : array;

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
