<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\ResponseEngine\Exceptions\FormatterNotRegisteredException;
use OpenDialogAi\ResponseEngine\Formatters\MessageFormatterInterface;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

/**
 * Defines the Response Engine Service
 */
interface ResponseEngineServiceInterface
{
    const OPERATION_KEY = 'operation';
    const ATTRIBUTES_KEY = 'attributes';
    const PARAMETERS_KEY = 'parameters';

    /**
     * Gets messages from the given intent formatted correctly for the platform the user is on
     *
     * @param string $platform
     * @param string $intentName
     * @return OpenDialogMessages $messageWrapper
     * @throws NoMatchingMessagesException
     */
    public function getMessageForIntent(string $platform, string $intentName): OpenDialogMessages;

    /**
     * Takes the input text and replaces named attributes with in curly braces.
     * Attribute filling may happen before a message is parsed as XML, so attribute
     * values should be encoded appropriately
     *
     * @param $text string The message test to fill
     * @return string The message text with attributes filled
     */
    public function fillAttributes($text): string;

    /**
     * Loops through all available formatters from config, and creates a local array keyed by the name of the
     * formatter
     */
    public function registerAvailableFormatters(): void;

    /**
     * Registers a single formatter if one with the same name isn't already set
     *
     * @param MessageFormatterInterface $formatter
     * @param bool $force If true, will force setting the formatter, even if one with the same name is already registered
     */
    public function registerFormatter(MessageFormatterInterface $formatter, $force = false): void;

    /**
     * Returns all available formatters
     *
     * @return MessageFormatterInterface[]
     */
    public function getAvailableFormatters(): array;

    /**
     * Returns the given formatter by name if it is available
     *
     * @param string $formatterName
     * @return MessageFormatterInterface
     */
    public function getFormatter(string $formatterName): MessageFormatterInterface;

    /**
     * Checks whether the given formatter is registered and available
     *
     * @param string $formatterName
     * @return bool
     */
    public function isFormatterAvailable(string $formatterName): bool;

    /**
     * Builds an error message for the given platform
     *
     * @param string $platform
     * @param string $message
     * @throws FormatterNotRegisteredException
     * @return OpenDialogMessages
     */
    public function buildTextFormatterErrorMessage(string $platform, string $message) : OpenDialogMessages;

    /**
     * Sets the operation service to use the response engine
     *
     * @param OperationServiceInterface $operationService
     */
    public function setOperationService(OperationServiceInterface $operationService): void;
}
