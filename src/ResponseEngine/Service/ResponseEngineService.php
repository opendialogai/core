<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ResponseEngineService
{
    /** @var AttributeResolverService */
    protected $attributeResolver;

    private $messageTemplate;

    /**
     * ResponseEngineService constructor.
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
        $this->attributeResolver = app()->make(AttributeResolverService::ATTRIBUTE_RESOLVER);
    }

    public function getMessageForIntent($intentName)
    {
        // Get this intent's message templates.
        $messageTemplates = MessageTemplate::forIntent($intentName)->get();

        if (count($messageTemplates) === 0) {
            return false;
        }

        $availableAttributes = $this->attributeResolver->getAvailableAttributes();

        // Find the correct message template to use.
        foreach ($messageTemplates as $messageTemplate) {
            // We iterate the templates and choose the first whose conditions pass.
            $conditions = $messageTemplate->getConditions();

            // If there are no conditions, we can use this template.
            if (empty($conditions)) {
                $this->messageTemplate = $messageTemplate;
                break;
            }

            // Iterate over the conditions and ensure that all pass.
            $conditionsPass = true;
            foreach ($conditions as $condition) {
                $attributeName = '';
                $attributeValue = '';
                $operation = '';
                foreach ($condition as $key => $val) {
                    if ($key === 'operation') {
                        $operation = $val;
                    } else {
                        $attributeName = $key;
                        $attributeValue = $val;
                    }
                }

                if (!array_key_exists($attributeName, $availableAttributes)) {
                    $conditionsPass = false;
                }

                // Instantiate our condition attribute.
                $attribute = new $availableAttributes[$attributeName]($attributeValue);

                // Get the resolved attribute.
                $resolvedAttribute = $this->attributeResolver->getAttributeFor($attributeName);

                // Check the condition.
                if ($resolvedAttribute->compare($attribute, $operation) !== true) {
                    $conditionsPass = false;
                }
            }

            if ($conditionsPass) {
                $this->messageTemplate = $messageTemplate;
                break;
            }
        }

        if (!isset($this->messageTemplate)) {
            return false;
        }

        // Get the messages.
        $messages = $messageTemplate->getMessages();

        return $messages;
    }
}
