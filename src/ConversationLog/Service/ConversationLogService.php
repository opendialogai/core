<?php

namespace OpenDialogAi\ConversationLog\Service;

use DateTime;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;

class ConversationLogService
{
    /**
     * Log an incoming message.
     *
     * @param UtteranceAttribute $utterance
     */
    public function logIncomingMessage(UtteranceAttribute $utterance): void
    {
        $message = '';
        $type = '';
        $messageId = '';
        $intent = null;
        $conversation = null;
        $scene = null;


        $message = $utterance->getText();
        $type = $utterance->getUtteranceType();

        //@todo determine whether we should re-introduce messageIds and timestamps
        $messageId = '';
        $timestamp = date("Y-m-d H:i:s.u");


        Message::create(
            $timestamp,
            $type,
            $this->getUserId($utterance),
            $this->getUserId($utterance),
            $message,
            $utterance->getData(),
            $messageId,
            $this->getUser($utterance),
        )->save();
    }

    /**
     * Log outgoing message.
     *
     * @param OpenDialogMessages $messageWrapper
     * @param UtteranceAttribute $utterance
     */
    public function logOutgoingMessages(
        OpenDialogMessages $messageWrapper,
        UtteranceAttribute $utterance
    ): void {
        $intents = null;
        $conversation = null;
        $scene = null;

        try {
            $intents = ContextService::getAttributeValue('next_intents', 'conversation');
            $conversation = ContextService::getAttributeValue('current_conversation', 'conversation');
            $scene = ContextService::getAttributeValue('current_scene', 'conversation');
        } catch (AttributeDoesNotExistException $e) {
        }

        /** @var OpenDialogMessage $message */
        foreach ($messageWrapper->getMessages() as $message) {
            $messageData = $message->getMessageToPost();
            if ($messageData) {
                Message::create(
                    null,
                    $messageData['type'],
                    $this->getUserId($utterance),
                    $messageData['author'],
                    (isset($messageData['data']['text'])) ? $messageData['data']['text'] : '',
                    $messageData['data'],
                    null,
                    $this->getUser($utterance),
                    $intents,
                    $conversation,
                    $scene
                )->save();
            } else {
                Log::debug(sprintf("Not logging outgoing message, nothing to log for %s", get_class($message)));
            }
        }
    }

    /**
     * @param UtteranceAttribute $utterance
     * @return String
     */
    private function getUserId(UtteranceAttribute $utterance): string
    {
        $userId = '';
        $userId = $utterance->getUserId();

        return $userId;
    }

    /**
     * @param UtteranceAttribute $utterance
     * @return array
     */
    private function getUser(UtteranceAttribute $utterance): array
    {
        $userInfo = $utterance->getUser();
        return [
            'first_name' => $userInfo->getFirstName(),
            'last_name' => $userInfo->getLastName(),
            'email' => $userInfo->getEmail(),
            'external_id' => $userInfo->getExternalId(),
            'ip_address' => $userInfo->getIPAddress(),
            'country' => $userInfo->getCountry(),
            'browser_language' => $userInfo->getBrowserLanguage(),
            'os' => $userInfo->getOS(),
            'browser' => $userInfo->getBrowser(),
            'timezone' => $userInfo->getTimezone(),
            'custom' => $userInfo->getCustomParameters(),
        ];
    }
}
