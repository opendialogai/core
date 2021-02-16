<?php

namespace OpenDialogAi\AttributeEngine\CoreAttributes;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Components\ODComponentTypes;

class UserHistoryRecord extends BasicCompositeAttribute
{
    protected static string $componentId = 'attribute.core.conversation_state';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    public const USER_HISTORY_RECORD = 'user_history_record';
    public const PLATFORM = 'platform';
    public const WORKSPACE = 'workspace';
    public const UTTERANCE = 'utterance';
    public const SPEAKER = 'speaker';
    public const INTENT_ID = 'intent_id';
    public const SCENARIO_ID = 'scenario_id';
    public const CONVERSATION_ID = 'conversation_id';
    public const SCENE_ID = 'conversation_id';
    public const TURN_ID = 'turn_id';
    public const COMPLETED = 'completed';
    public const ACTIONS_PERFORMED = 'actions_performed';
    public const CONDITIONS_EVALUATED = 'conditions_evaluated';

    public function setUserHistoryRecordAttribute(string $type, $value)
    {
        // If the $value is not a rawValue but already an attribute just add it
        if ($value instanceof Attribute) {
            $this->addAttribute($value);
            return $this;
        }

        $attribute = AttributeResolver::getAttributeFor($type, $value);
        $this->addAttribute($attribute);
        return $this;
    }

    public function getUserHistoryRecordAttribute(string $type)
    {
        if ($this->hasAttribute($type)) {
            $attribute = $this->getAttribute($type);
            if ($attribute instanceof CompositeAttribute) {
                return $attribute;
            } elseif ($attribute instanceof ScalarAttribute) {
                return $this->getAttribute($type)->getValue();
            }
        }

        // @todo - might make more sense to return null or through an exception but for now
        // going down the more permissive path
        return '';
    }

    public function completedConversation(): bool
    {
        if ($this->hasAttribute(self::COMPLETED)) {
            return $this->getUserHistoryRecordAttribute(self::COMPLETED)->getValue();
        }

        return false;
    }

    public function getScenarioId()
    {
        return $this->getUserHistoryRecordAttribute(self::SCENARIO_ID);
    }

    public function getConversationId()
    {
        return $this->getUserHistoryRecordAttribute(self::CONVERSATION_ID);
    }

    public function getSceneId()
    {
        return $this->getUserHistoryRecordAttribute(self::SCENE_ID);
    }

    public function getTurnId()
    {
        return $this->getUserHistoryRecordAttribute(self::TURN_ID);
    }

    public function getIntentId()
    {
        return $this->getUserHistoryRecordAttribute(self::INTENT_ID);
    }
}
