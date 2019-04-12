<?php

namespace OpenDialogAi\Core\Controllers;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class OpenDialogController
{
    /** @var ContextService */
    private $contextService;

    /** @var ConversationEngineInterface */
    private $conversationEngine;

    /** @var ResponseEngineServiceInterface */
    private $responseEngineService;

    /**
     * @param ContextService $contextService
     */
    public function setContextService(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * @param ConversationEngineInterface $conversationEngine
     */
    public function setConversationEngine(ConversationEngineInterface $conversationEngine)
    {
        $this->conversationEngine = $conversationEngine;
    }

    public function setResponseEngine(ResponseEngineServiceInterface $responseEngineService)
    {
        $this->responseEngineService = $responseEngineService;
    }

    public function runConversation(UtteranceInterface $utterance)
    {
        $userContext = $this->contextService->createUserContext($utterance);

        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        return $this->responseEngineService->getMessageForIntent($intent->getId());
    }
}
