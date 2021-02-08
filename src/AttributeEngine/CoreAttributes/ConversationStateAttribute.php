<?php

namespace OpenDialogAi\AttributeEngine\CoreAttributes;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Components\ODComponentTypes;

class ConversationStateAttribute extends BasicCompositeAttribute
{
    protected static ?string $componentId = 'attribute.core.conversation_state';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    public const PLATFORM = 'platform';
    public const WORKSPACE = 'workspace';
    public const UTTERANCE = 'utterance';
    public const SPEAKER = 'speaker';
    public const INTENT_ID = 'intent_id';
    public const SCENARIO_ID = 'scenario_id';
    public const CONVERSATION_ID = 'conversation_id';
    public const TURN_ID = 'turn_id';
    public const ACTIONS_PERFORMED = 'actions_performed';
    public const CONDITIONS_EVALUATED = 'conditions_evaluated';

    public function setConversationStateAttribute(string $type, $value)
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

    public function getConversationStateAttribute(string $type)
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
}
