<?php

namespace OpenDialogAi\Core\Controllers;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationLog\Service\ConversationLogService;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class OpenDialogController
{
    /** @var ContextService */
    private $contextService;

    /** @var ConversationLogService */
    private $conversationLogService;

    /** @var ConversationEngineInterface */
    private $conversationEngine;

    /** @var ResponseEngineServiceInterface */
    private $responseEngineService;

    /**
     * @param ContextService $contextService
     */
    public function setContextService(ContextService $contextService): void
    {
        $this->contextService = $contextService;
    }

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
     * @return WebChatMessages
     * @throws FieldNotSupported
     */
    public function runConversation(UtteranceInterface $utterance): WebChatMessages
    {
        $userContext = $this->contextService->createUserContext($utterance);

        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        // Log incoming message.
        $this->conversationLogService->logIncomingMessage($utterance);

        try {
            $messageWrapper = $this->responseEngineService->getMessageForIntent($intent->getId());
        } catch (NoMatchingMessagesException $e) {
            Log::error($e->getMessage());
            $message = (new WebChatMessage())->setText($e->getMessage());
            $messageWrapper = new WebChatMessages();
            $messageWrapper->addMessage($message);
        }

        $this->processInternalMessages($messageWrapper);

        $this->conversationLogService->logOutgoingMessages($messageWrapper, $utterance);

        $userContext->addAttribute(AttributeResolver::getAttributeFor('last_seen', now()->timestamp));
        $userContext->updateUser();

        return $messageWrapper;
    }

    private function processInternalMessages(WebChatMessages $messageWrapper)
    {
        $messages = $messageWrapper->getMessages();

        foreach ($messages as $i => $message) {
            if ($i > 0) {
                $message->setInternal(true);
            }
            if ($i < count($messages) - 1) {
                $message->setHidetime(true);
            }
        }
    }
}
