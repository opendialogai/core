<?php

namespace OpenDialogAi\Core\Controllers;

use Ds\Set;
use GuzzleHttp\Exception\GuzzleException;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\Exceptions\NoConversationsException;
use OpenDialogAi\ConversationLog\Service\ConversationLogService;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
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
     * @todo - return a system level no match intent if we don't get back a usercontext,
     * or intent and return back a system level no match message if we don't get that from
     * the response engine.
     *
     * @param UtteranceInterface $utterance
     * @return OpenDialogMessages
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws NodeDoesNotExistException
     */
    public function runConversation(UtteranceInterface $utterance): OpenDialogMessages
    {
        $userContext = ContextService::createUserContext($utterance);

        try {
            /** @var Intent[] $intents */
            $intents = $this->conversationEngine->getNextIntents($userContext, $utterance);
        } catch (NoConversationsException $e) {
            return $this->getNoConversationsMessages($utterance);
        }

        // Log incoming message.
        $this->conversationLogService->logIncomingMessage($utterance);

        $messages = $this->getMessages($utterance, $intents);

        $this->processInternalMessages($messages);

        $this->conversationLogService->logOutgoingMessages($messages, $utterance);

        $userContext->addAttribute(AttributeResolver::getAttributeFor('last_seen', now()->timestamp));
        $userContext->updateUser();

        return $messages;
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
     * @param UtteranceInterface $utterance
     * @return OpenDialogMessages
     */
    private function getNoConversationsMessages(UtteranceInterface $utterance): OpenDialogMessages
    {
        $platform = $utterance->getPlatform();
        $formatter = $this->responseEngineService->getFormatter("formatter.core.{$platform}");

        $message = 'No conversations are defined or activated';

        return $formatter->getMessages(sprintf('<message><text-message>%s</text-message></message>', $message));
    }

    /**
     * Collects messages for each intent and if there is more than one intent, gather all messages into the first wrapper
     *
     * @param UtteranceInterface $utterance
     * @param array $intents
     * @return OpenDialogMessages
     */
    private function getMessages(UtteranceInterface $utterance, array $intents): OpenDialogMessages
    {
        $messagesSet = new Set();

        foreach ($intents as $intent) {
            try {
                $messagesSet->add($this->responseEngineService->getMessageForIntent(
                    $utterance->getPlatform(),
                    $intent->getId()
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
