<?php

namespace OpenDialogAi\ConversationLog\Service;

use DateTime;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class ConversationLogService
{
    /**
     * Log an incoming message.
     *
     * @param UtteranceInterface $utterance
     * @throws FieldNotSupported
     */
    public function logIncomingMessage(UtteranceInterface $utterance): void
    {
        $message = '';
        $type = '';
        $messageId = '';
        $intent = null;
        $conversation = null;
        $scene = null;

        try {
            $message = $utterance->getText();
        } catch (FieldNotSupported $e) {
            Log::debug(sprintf('Could not retrieve message text. Error: %s', $e->getMessage()));
        }

        try {
            $type = $utterance->getType();
        } catch (\Exception $e) {
            Log::debug(sprintf('Could not retrieve message type. Error: %s', $e->getMessage()));
        }

        try {
            $messageId = $utterance->getMessageId();
        } catch (\Exception $e) {
            Log::debug(sprintf('Could not retrieve message ID. Error: %s', $e->getMessage()));
        }

        $timestamp = DateTime::createFromFormat('U.u', $utterance->getTimestamp())->format('Y-m-d H:i:s.u');

        try {
            $intent = ContextService::getAttributeValue('interpreted_intent', 'conversation');
            $conversation = ContextService::getAttributeValue('current_conversation', 'conversation');
            $scene = ContextService::getAttributeValue('current_scene', 'conversation');
        } catch (AttributeDoesNotExistException $e) {
            //
        }

        Message::create(
            $timestamp,
            $type,
            $this->getUserId($utterance),
            $this->getUserId($utterance),
            $message,
            $utterance->getData(),
            $messageId,
            $this->getUser($utterance),
            $intent,
            $conversation,
            $scene
        )->save();
    }

    /**
     * Log outgoing message.
     *
     * @param OpenDialogMessages $messageWrapper
     * @param UtteranceInterface $utterance
     * @throws FieldNotSupported
     */
    public function logOutgoingMessages(
        OpenDialogMessages $messageWrapper,
        UtteranceInterface $utterance
    ): void {
        $intent = null;
        $conversation = null;
        $scene = null;

        try {
            $intent = ContextService::getAttributeValue('next_intent', 'conversation');
            $conversation = ContextService::getAttributeValue('current_conversation', 'conversation');
            $scene = ContextService::getAttributeValue('current_scene', 'conversation');
        } catch (AttributeDoesNotExistException $e) {
        }

        /** @var Message $message */
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
                    $intent,
                    $conversation,
                    $scene
                )->save();
            } else {
                Log::debug(sprintf("Not logging outgoing message, nothing to log for %s", get_class($message)));
            }
        }
    }

    /**
     * @param UtteranceInterface $utterance
     * @return String
     */
    private function getUserId(UtteranceInterface $utterance): string
    {
        $userId = '';
        try {
            $userId = $utterance->getUserId();
        } catch (\Exception $e) {
            Log::debug(sprintf("Could not retrieve user id. Error: %s", $e->getMessage()));
        }

        return $userId;
    }

    /**
     * @param UtteranceInterface $utterance
     * @return array
     * @throws FieldNotSupported
     */
    private function getUser(UtteranceInterface $utterance): array
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
