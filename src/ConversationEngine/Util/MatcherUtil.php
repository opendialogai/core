<?php


namespace OpenDialogAi\ConversationEngine\Util;


use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class MatcherUtil
{
    public static function currentScenarioId(): Attribute
    {
        return ContextService::getAttribute(Scenario::CURRENT_SCENARIO, ConversationContext::getComponentId());
    }

    public static function currentConversationId(): Attribute
    {
        return ContextService::getAttribute(Conversation::CURRENT_CONVERSATION, ConversationContext::getComponentId());
    }

    public static function currentSceneId(): Attribute
    {
        return ContextService::getAttribute(Scene::CURRENT_SCENE, ConversationContext::getComponentId());
    }

    public static function currentTurnId(): Attribute
    {
        return ContextService::getAttribute(Turn::CURRENT_TURN, ConversationContext::getComponentId());
    }

    public static function currentIntentId(): Attribute
    {
        return ContextService::getAttribute(Intent::CURRENT_INTENT, ConversationContext::getComponentId());
    }
}
