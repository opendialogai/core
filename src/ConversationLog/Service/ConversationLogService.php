<?php

namespace OpenDialogAi\ConversationLog\Service;

use DateTime;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ContextEngine\Contexts\UserContext;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;


class ConversationLogService
{
    /* @var UserService */
    private $userService;

    /** @var ConversationEngineInterface */
    private $conversationEngine;

    public function __construct(ConversationEngineInterface $conversationEngine, UserService $userService)
    {
        $this->conversationEngine = $conversationEngine;
        $this->userService = $userService;
    }

    /**
     * Log an incoming message.
     *
     * @param UtteranceInterface $utterance
     * @param Intent $intent
     * @param UserContext $userContext
     * @throws FieldNotSupported
     */
    public function logIncomingMessage(UtteranceInterface $utterance, Intent $intent, UserContext $userContext): void
    {
        $message = '';
        $type = '';
        $messageId = '';

        try {
            $message = $utterance->getText();
        } catch (FieldNotSupported $e) {
            Log::debug(sprintf("Could not retrieve message text. Error: %s", $e->getMessage()));
        }

        try {
            $type = $utterance->getType();
        } catch (\Exception $e) {
            Log::debug(sprintf("Could not retrieve message type. Error: %s", $e->getMessage()));
        }

        try {
            $messageId = $utterance->getMessageId();
        } catch (\Exception $e) {
            Log::debug(sprintf("Could not retrieve message ID. Error: %s", $e->getMessage()));
        }

        $timestamp = DateTime::createFromFormat('U.u', $utterance->getTimestamp())->format('Y-m-d H:i:s.u');

        Message::create(
            $timestamp,
            $type,
            $this->getUserId($utterance),
            $this->getUserId($utterance),
            $message,
            $utterance->getData(),
            $messageId,
            $this->getUser($utterance),
            $this->getMatchedIntent($intent),
            $this->getSceneId($intent),
            $this->getConversationId($userContext, $utterance)
        )->save();
    }

    /**
     * Log outgoing message.
     *
     * @param WebChatMessages $messageWrapper
     * @param UtteranceInterface $utterance
     * @param Intent $intent
     * @param UserContext $userContext
     * @throws FieldNotSupported
     */
    public function logOutgoingMessages(
        WebChatMessages $messageWrapper,
        UtteranceInterface $utterance,
        Intent $intent,
        UserContext $userContext
    ): void {
        /** @var WebChatMessage $message */
        foreach ($messageWrapper->getMessages() as $message) {
            $messageData = $message->getMessageToPost();

            Message::create(
                null,
                $messageData['type'],
                $this->getUserId($utterance),
                $messageData['author'],
                $messageData['data']['text'],
                $messageData['data'],
                null,
                $this->getUser($utterance),
                $this->getMatchedIntent($intent),
                $this->getSceneId($intent),
                $this->getConversationId($userContext, $utterance)
            )->save();
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
     * @param Intent $intent
     * @return String
     */
    private function getMatchedIntent(Intent $intent): string
    {
        return $intent->getLabel();
    }

    /**
     * @param Intent $intent
     * @return String
     */
    private function getSceneId(Intent $intent): string
    {
        return $this->userService->getSceneForIntent($intent->getUid());
    }

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return String
     */
    private function getConversationId(UserContext $userContext, UtteranceInterface $utterance): string
    {
        return $this->conversationEngine->determineCurrentConversation($userContext, $utterance)->getId();
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
