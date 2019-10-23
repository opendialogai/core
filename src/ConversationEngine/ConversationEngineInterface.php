<?php

namespace OpenDialogAi\ConversationEngine;

use GuzzleHttp\Exception\GuzzleException;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

interface ConversationEngineInterface
{
    /**
     * @param ConversationStoreInterface $conversationStore
     */
    public function setConversationStore(ConversationStoreInterface $conversationStore);

    /**
     * @return ConversationStoreInterface
     */
    public function getConversationStore(): ConversationStoreInterface;

    /**
     * @param InterpreterServiceInterface $interpreterService
     */
    public function setInterpreterService(InterpreterServiceInterface $interpreterService);

    /**
     * @param ActionEngineInterface $actionEngine
     */
    public function setActionEngine(ActionEngineInterface $actionEngine);

    /**
     * Given a user context and an utterance determine what the next intent should be.
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Intent
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws ConversationStore\EIModelCreatorException
     * @throws CurrentIntentNotSetException
     */
    public function getNextIntent(UserContext $userContext, UtteranceInterface $utterance): Intent;

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     */
    public function determineCurrentConversation(UserContext $userContext, UtteranceInterface $utterance): Conversation;

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     */
    public function updateConversationFollowingUserInput(UserContext $userContext, UtteranceInterface $utterance);
}
