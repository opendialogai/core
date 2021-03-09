<?php


namespace OpenDialogAi\ConversationEngine\Util;


use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class ConversationContextUtil
{
    public static function currentScenarioUid()
    {
        return ContextService::getAttribute(
            Scenario::CURRENT_SCENARIO,
            ConversationContext::getComponentId()
        )->getValue();
    }

    public static function currentConversationUid()
    {
        return ContextService::getAttribute(
            Conversation::CURRENT_CONVERSATION,
            ConversationContext::getComponentId()
        )->getValue();
    }

    public static function currentSceneUid()
    {
        return ContextService::getAttribute(
            Scene::CURRENT_SCENE,
            ConversationContext::getComponentId()
        )->getValue();
    }

    public static function currentTurnUid()
    {
        return ContextService::getAttribute(
            Turn::CURRENT_TURN,
            ConversationContext::getComponentId()
        )->getValue();
    }

    public static function currentIntentId()
    {
        return ContextService::getAttribute(
            Intent::CURRENT_INTENT,
            ConversationContext::getComponentId()
        )->getValue();
    }

    public static function currentIntentIsRequest(): bool
    {
        return ContextService::getAttribute(
            Intent::INTENT_IS_REQUEST,
            ConversationContext::getComponentId()
        )->getValue();
    }

    public static function currentSpeaker(): string
    {
        return ContextService::getAttribute(
            Intent::CURRENT_SPEAKER,
            ConversationContext::getComponentId()
        )->getValue();
    }
}
