<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\Message\WebchatMessageFormatter;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /** @var AttributeResolverService */
    protected $attributeResolver;

    /**
     * @inheritdoc
     */
    public function getMessageForIntent(string $intentName) : array
    {
        $selectedMessageTemplate = null;

        // Get this intent's message templates.
        $messageTemplates = MessageTemplate::forIntent($intentName)->get();

        if (count($messageTemplates) === 0) {
            return false;
        }

        $availableAttributes = $this->attributeResolver->getAvailableAttributes();

        /** @var MessageTemplate $messageTemplate */
        foreach ($messageTemplates as $messageTemplate) {
            // We iterate the templates and choose the first whose conditions pass.
            $conditions = $messageTemplate->getConditions();

            // If there are no conditions, we can use this template.
            if (empty($conditions)) {
                $selectedMessageTemplate = $messageTemplate;
                break;
            }

            // Iterate over the conditions and ensure that all pass.
            $conditionsPass = true;
            foreach ($conditions as $condition) {
                if (!array_key_exists($condition['attributeName'], $availableAttributes)) {
                    $conditionsPass = false;
                }

                // Instantiate our condition attribute.
                $attribute = new $availableAttributes[$condition['attributeName']]($condition['attributeValue']);

                // Get the resolved attribute.
                $resolvedAttribute = $this->attributeResolver->getAttributeFor($condition['attributeName']);

                // Check the condition.
                if ($resolvedAttribute->compare($attribute, $condition['operation']) !== true) {
                    $conditionsPass = false;
                }
            }

            if ($conditionsPass) {
                $selectedMessageTemplate = $messageTemplate;
                break;
            }
        }

        if (empty($selectedMessageTemplate)) {
            return false;
        }

        // Get the messages.
        $formatter = new WebchatMessageFormatter();
        $messages = $formatter->getMessages($selectedMessageTemplate->message_markup);

        return $messages;
    }

    /**
     * @inheritdoc
     */
    public function fillAttributes($text) : string
    {
        foreach ($this->attributeResolver->getAvailableAttributes() as $attributeName => $attributeClass) {
            $value = $this->attributeResolver->getAttributeFor($attributeName)->getValue();
            $text = str_replace('{' . $attributeName . '}', $value, $text);
        }
        return $text;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeResolver(AttributeResolverService $attributeResolverService) : void
    {
        $this->attributeResolver = $attributeResolverService;
    }
}
