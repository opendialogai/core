<?php

namespace OpenDialogAi\AttributeEngine\CoreAttributes;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;
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
    public const UTTERANCE_FORM_DATA = 'utterance_form_data';
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

    public function getUtteranceType()
    {
        return $this->getUtteranceAttribute(self::TYPE);
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
        return $this->getUtteranceAttribute(self::UTTERANCE_FORM_DATA);
    }

    public function setFormValues(array $formValues)
    {
        $newFormValues = [];
        foreach ($formValues as $name => $value) {
            if (!in_array($name, ['time', 'date', 'text'])) {
                $newFormValues[$name] = $value;
            }
        }
        $this->setUtteranceAttribute(self::UTTERANCE_FORM_DATA, $newFormValues);
        return $this;
    }

    public function getCallbackValue()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_DATA_VALUE);
    }

    public function setCallbackValue($value)
    {
        $this->setUtteranceAttribute(self::UTTERANCE_DATA_VALUE, $value);
        return $this;
    }

    public function getCallbackId()
    {
        return $this->getUtteranceAttribute('callback_id');
    }

    public function setCallbackId($callback_id)
    {
        $this->setUtteranceAttribute(self::CALLBACK_ID, $callback_id);
        return $this;
    }

    public function getUserId()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_USER_ID);
    }

    public function getUser()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_USER);
    }

    public function getText()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_TEXT);
    }

    public function setText($value)
    {
        $this->setUtteranceAttribute(self::UTTERANCE_TEXT, $value);
        return $this;
    }

    public function getData()
    {
        return $this->getUtteranceAttribute(self::UTTERANCE_DATA);
    }
}
