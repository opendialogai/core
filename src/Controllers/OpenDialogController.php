<?php

namespace OpenDialogAi\Core\Controllers;

use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationLog\Service\ConversationLogService;
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
     */
    public function runConversation(UtteranceInterface $utterance): OpenDialogMessages
    {
        $userContext = ContextService::createUserContext($utterance);

        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        // Log incoming message.
        $this->conversationLogService->logIncomingMessage($utterance);

        try {
            $messages = $this->responseEngineService->getMessageForIntent(
                $utterance->getPlatform(),
                $intent->getId()
            );
        } catch (NoMatchingMessagesException $e) {
            /** @var OpenDialogMessages $messages */
            $messages = $this->responseEngineService->buildTextFormatterErrorMessage(
                $utterance->getPlatform(),
                $e->getMessage()
            );
        }

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
}
