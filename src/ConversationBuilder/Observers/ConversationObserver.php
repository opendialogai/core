<?php

namespace OpenDialogAi\ConversationBuilder\Observers;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphResponseErrorException;
use Spatie\Activitylog\Models\Activity;

class ConversationObserver
{
    /**
     * Handle the conversation "created" event.
     *
     * @param  Conversation  $conversation
     * @return void
     */
    public function created(Conversation $conversation)
    {
        //
    }

    /**
     * Handle the conversation "updated" event.
     *
     * @param  Conversation  $conversation
     * @return void
     */
    public function updated(Conversation $conversation)
    {
        //
    }

    /**
     * @param Conversation $conversation
     * @return bool
     * @throws GuzzleException
     * @throws BindingResolutionException
     * @throws EIModelCreatorException
     */
    public function deleting(Conversation $conversation): bool
    {
        if ($conversation->graph_uid) {
            $dGraph = app()->make(DGraphClient::class);

            /** @var ConversationStoreInterface $conversationStore */
            $conversationStore = app()->make(ConversationStoreInterface::class);

            /** @var EIModelConversation $conversationModel */
            $conversationModel = $conversationStore->getEIModelConversationTemplateByUid($conversation->graph_uid);

            if ($conversationModel->getConversationStatus() != ConversationNode::ARCHIVED) {
                return false;
            }

            try {
                $dGraph->deleteConversationAndHistory($conversation->graph_uid);
            } catch (DGraphResponseErrorException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle the conversation "deleted" event.
     *
     * @param  Conversation  $conversation
     * @return void
     */
    public function deleted(Conversation $conversation)
    {
        // Remove related state logs.
        ConversationStateLog::where('conversation_id', $conversation->id)->delete();

        // Remove related activity logs.
        Activity::where('subject_id', $conversation->id)->delete();
    }

    /**
     * Handle the conversation "restored" event.
     *
     * @param  Conversation  $conversation
     * @return void
     */
    public function restored(Conversation $conversation)
    {
        //
    }

    /**
     * Handle the conversation "force deleted" event.
     *
     * @param  Conversation  $conversation
     * @return void
     */
    public function forceDeleted(Conversation $conversation)
    {
        //
    }
}
