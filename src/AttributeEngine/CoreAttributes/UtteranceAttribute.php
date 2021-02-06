<?php

namespace OpenDialogAi\AttributeEngine\CoreAttributes;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Components\ODComponentTypes;

class UtteranceAttribute extends BasicCompositeAttribute
{
    protected static ?string $componentId = 'attribute.core.utterance';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    public const UTTERANCE_PLATFORM = 'utterance_platforn';
    public const WEBCHAT_PLATFORM = 'webchat';

    public const TYPE = 'utterance_type';
    public const CHAT_OPEN = 'chat_open';
    public const WEBCHAT_MESSAGE = 'webchat_message';
    public const WEBCHAT_TRIGGER = 'webchat_trigger';
    public const WEBCHAT_BUTTON_RESPONSE = 'webchat_button_response';
    public const WEBCHAT_CLICK = 'webchat_click';
    public const WEBCHAT_LONGTEXT_RESPONSE = 'webchat_longtext_response';
    public const WEBCHAT_FORM_RESPONSE = 'webchat_form_response';

    public const UTTERANCE_DATA = 'utterance_data';
    public const UTTERANCE_TEXT = 'utterance_text';
    public const CALLBACK_ID = 'callback_id';
    public const UTTERANCE_DATA_VALUE = 'utterance_value';
    public const UTTERANCE_USER_ID = 'utterance_user_id';
    public const UTTERANCE_USER = 'utterance_user';


    /**
     * Helper function for adding attributes starting from either a rawValue or
     * adding an attribute directly.
     *
     * @param string $type
     * @param $value
     * @return $this
     */
    public function setUtteranceAttribute(string $type, $value)
    {
        // If the $value is not a rawValue but already an attribute just add it
        if ($value instanceof Attribute) {
            $this->addAttribute($value);
            return $this;
        }

        $utteranceType = AttributeResolver::getAttributeFor($type, $value);
        $this->addAttribute($utteranceType);
        return $this;
    }

    public function getUtteranceAttribute(string $type)
    {
        return $this->getAttribute($type)->getValue();
    }

    public function getPlatform()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_PLATFORM);
    }

    public function setPlatform($value)
    {
        $this->setUtteranceAttribute(self::UTTERANCE_PLATFORM, $value);
        return $this;
    }

    public function getFormValues()
    {
        return $this->getUtteranceAttribute('utterance_form_data');
    }

    public function setFormValues(array $formValues)
    {
        $newFormValues = [];
        foreach ($formValues as $name => $value) {
            if (!in_array($name, ['time', 'date', 'text'])) {
                $newFormValues[$name] = $value;
            }
        }
        $this->setUtteranceAttribute('utterance_form_data', $newFormValues);
        return $this;
    }

    public function getCallbackId()
    {
        return $this->getUtteranceAttribute('callback_id');
    }

    public function getUserId()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_USER_ID);
    }

    public function getUser()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_USER);
    }
}
