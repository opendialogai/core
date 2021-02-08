<?php

namespace OpenDialogAi\Core\Controllers;

use Ds\Set;
use GuzzleHttp\Exception\GuzzleException;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\Exceptions\NoConversationsException;
use OpenDialogAi\ConversationEngine\Reasoners\UtteranceReasoner;
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
     * @param UtteranceAttribute $utterance
     * @return OpenDialogMessages
     */
    public function runConversation(UtteranceAttribute $utterance): ?OpenDialogMessages
    {

        // The UtteranceReasoner uses the incoming utterance to determine whether it can return a
        // current user.
        $currentUser = UtteranceReasoner::analyseUtterance($utterance);
        // Log incoming message.
        $this->conversationLogService->logIncomingMessage($utterance);

        // create a dummy intent as a response
        $intent = new Intent();
        $intent->setODId('intent.core.chat_open_response');
        $messages = $this->getMessages($utterance, $intent);

        return $messages;

//        $userContext = ContextService::createUserContext($utterance);
        //return $this->getNoConversationsMessages($utterance);

//        try {
//            /** @var Intent[] $intents */
//            $intents = $this->conversationEngine->getNextIntents($userContext, $utterance);
//        } catch (NoConversationsException $e) {
//            return $this->getNoConversationsMessages($utterance);
//        }



//        $this->processInternalMessages($messages);

//        $this->conversationLogService->logOutgoingMessages($messages, $utterance);

//        $userContext->addAttribute(AttributeResolver::getAttributeFor('last_seen', now()->timestamp));
//       $userContext->updateUser();

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
    private function getMessages(UtteranceAttribute $utterance, $intent): OpenDialogMessages
    {
        $messagesSet = new Set();

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
