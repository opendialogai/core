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
        return $this->getUtteranceAttribute('utterance_platform');
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
}
