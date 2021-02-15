<?php

namespace OpenDialogAi\Core\Controllers;

use Ds\Set;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\Exceptions\NoConversationsException;
use OpenDialogAi\ConversationLog\Service\ConversationLogService;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class OpenDialogController
{
    /** @var ConversationLogService */
    private $conversationLogService;

    /** @var ConversationEngineInterface */
    private $conversationEngine;

    /** @var ResponseEngineServiceInterface */
    private $responseEngineService;

    /**
     * @param ConversationLogService $conversationLogService
     */
    public function setConversationLogService(ConversationLogService $conversationLogService): void
    {
        $this->conversationLogService = $conversationLogService;
    }

    /**
     * @param ConversationEngineInterface $conversationEngine
     */
    public function setConversationEngine(ConversationEngineInterface $conversationEngine): void
    {
        $this->conversationEngine = $conversationEngine;
    }

    /**
     * @param ResponseEngineServiceInterface $responseEngineService
     */
    public function setResponseEngine(ResponseEngineServiceInterface $responseEngineService): void
    {
        $this->responseEngineService = $responseEngineService;
    }

    /**
     * @param UtteranceAttribute $utterance
     * @return OpenDialogMessages
     */
    public function runConversation(UtteranceAttribute $utterance): ?OpenDialogMessages
    {

        $this->conversationLogService->logIncomingMessage($utterance);

        try {
            /** @var IntentCollection $intents */
            $intents = $this->conversationEngine->getNextIntents($utterance);
        } catch (NoConversationsException $e) {
            return $this->getNoConversationsMessages($utterance);
        }

        $messages = $this->getMessages($utterance, $intents);

        $this->processInternalMessages($messages);

        $this->conversationLogService->logOutgoingMessages($messages, $utterance);

        return $messages;

//      @todo determine whether we still need this
//      $userContext->addAttribute(AttributeResolver::getAttributeFor('last_seen', now()->timestamp));
//      $userContext->updateUser();
    }

    private function processInternalMessages(OpenDialogMessages $messageWrapper)
    {
        $messages = $messageWrapper->getMessages();

        /** @var OpenDialogMessage $message */
        foreach ($messages as $i => $message) {
            if ($i < count($messages) - 1) {
                $message->setHidetime(true);
                $message->setInternal(true);
            }
        }
    }

    /**
     * Return text message when no conversations are defined or activated in Dgraph.
     *
     * @param UtteranceAttribute $utterance
     * @return OpenDialogMessages
     */
    private function getNoConversationsMessages(UtteranceAttribute $utterance): OpenDialogMessages
    {
        $platform = $utterance->getPlatform();
        $formatter = $this->responseEngineService->getFormatter("formatter.core.{$platform}");

        $message = 'No conversations are defined or activated';

        return $formatter->getMessages(sprintf('<message><text-message>%s</text-message></message>', $message));
    }

    /**
     * Collects messages for each intent and if there is more than one intent, gather all messages into the first wrapper
     *
     * @param UtteranceAttribute $utterance
     * @param Intent $intent
     * @return OpenDialogMessages
     */
    private function getMessages(UtteranceAttribute $utterance, IntentCollection $intents): OpenDialogMessages
    {
        $messagesSet = new Set();

        foreach ($intents as $intent) {
            try {
                $messagesSet->add($this->responseEngineService->getMessageForIntent(
                    $utterance->getPlatform(),
                    $intent->getODId()
                ));
            } catch (NoMatchingMessagesException $e) {
                $messagesSet->add($this->responseEngineService->buildTextFormatterErrorMessage(
                    $utterance->getPlatform(),
                    $e->getMessage()
                ));
            }
        }

        $messages = $messagesSet->first();

        if (count($messagesSet) > 1) {
            /** @var OpenDialogMessages $item */
            foreach ($messagesSet->slice(1) as $item) {
                foreach ($item->getMessages() as $message) {
                    $messages->addMessage($message);
                }
            }
        }

        return $messages;
    }
}
