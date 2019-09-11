<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\Core\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

/**
 * Defines the Response Engine Service
 */
interface ResponseEngineServiceInterface
{
    const ATTRIBUTE_OPERATION_KEY = 'operation';
    const ATTRIBUTE_NAME_KEY = 'attribute';
    const ATTRIBUTE_VALUE_KEY = 'value';

    /**
     * Gets messages from the given intent formatted correctly for the platform the user is on
     *
     * @param string $intentName
     * @return OpenDialogMessages $messageWrapper
     * @throws NoMatchingMessagesException
     */
    public function getMessageForIntent(string $intentName): OpenDialogMessages;

    /**
     * Takes the input text and replaces named attributes with in curly braces.
     * Attribute filling may happen before a message is parsed as XML, so attribute values should be encoded appropriately
     *
     * @param $text string The message test to fill
     * @return string The message text with attributes filled
     */
    public function fillAttributes($text): string;
}
