<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\Message\MessageFormatterInterface;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\ResponseEngine\MessageTemplate;
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

    public function getAvailableFormatters(): array;

    public function getFormatter(string $formatterName): MessageFormatterInterface;

    public function isFormatterAvailable(string $formatterName): bool;
}
