<?php

namespace OpenDialogAi\ResponseEngine\Service;

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
     * @return WebChatMessages $messageWrapper
     * @throws NoMatchingMessagesException
     */
    public function getMessageForIntent(string $intentName): WebChatMessages;

    /**
     * @param $text string The message test to fill
     * @return string The message text with attributes filled
     */
    public function fillAttributes($text) : string;
}
