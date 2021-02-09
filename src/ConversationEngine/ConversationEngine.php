<?php

namespace OpenDialogAi\ConversationEngine;

use Ds\Map;
use Ds\Set;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;
use OpenDialogAi\ContextEngine\Contexts\Intent\IntentContext;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\Exceptions\NoConversationsException;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class ConversationEngine implements ConversationEngineInterface
{
    const NO_MATCH = 'intent.core.NoMatch';

    /* @var InterpreterServiceInterface */
    private $interpreterService;

    /* @var OperationServiceInterface */
    private $operationService;

    /* @var ActionEngineInterface */
    private $actionEngine;

    /**
     * @param InterpreterServiceInterface $interpreterService
     */
    public function setInterpreterService(InterpreterServiceInterface $interpreterService): void
    {
        $this->interpreterService = $interpreterService;
    }

    /**
     * @param OperationServiceInterface $operationService
     */
    public function setOperationService(OperationServiceInterface $operationService): void
    {
        $this->operationService = $operationService;
    }

    /**
     * @param ActionEngineInterface $actionEngine
     */
    public function setActionEngine(ActionEngineInterface $actionEngine): void
    {
        $this->actionEngine = $actionEngine;
    }

    public function getNextIntents(UtteranceAttribute $utterance): Intent
    {
        // The UtteranceReasoner uses the incoming utterance and returns an appropriate User attribute.
        $currentUser = UtteranceReasoner::analyseUtterance($utterance);

        // The ConversationStateReasoner determines whether the user is in an ongoing conversation or not and
        // retrieves a conversation state attribute that allows us

        // create a dummy intent as a response
        $intent = new Intent();
        $intent->setODId('intent.core.chat_open_response');
        return $intent;
    }
}
