<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\Message\WebchatMessageFormatter;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /** @var ContextService */
    protected $contextService;

    /** @var AttributeResolver */
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
                $attributeName = '';
                $attributeContext = '';

                $this->determineContext($condition['attributeName'], $attributeContext, $attributeName);
                // If we encounter an attribute that we wouldn't know how to resolve we will need to
                // bail now and fail the message.
                if (!$this->attributeResolver->isAttributeSupported($attributeName)) {
                    $conditionsPass = false;
                }

                /* @var AttributeInterface $conditionAttribute */
                $conditionAttribute = $this->attributeResolver->getAttributeFor(
                    $attributeName,
                    $condition[MessageTemplate::ATTRIBUTE_VALUE]
                );

                /* @var Condition $conditionObject*/
                $conditionObject = new Condition($conditionAttribute, $condition[MessageTemplate::OPERATION]);

                $attributeToCompareAgainst = $this->contextService->getAttribute($attributeName, $attributeContext);

                if (!$conditionObject->compareAgainst($attributeToCompareAgainst)) {
                    $conditionsPass = false;
                };
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
        // Extract attributes that need to be resolved from the text
        $matches = [];
        $matchCount = preg_match_all("(\{(.*?)\})", $text, $matches, PREG_PATTERN_ORDER);
        if ($matchCount > 0) {
            foreach ($matches[1] as $attributeId) {
                $attributeName = '';
                $contextId = '';
                $this->determineContext($attributeId, $contextId, $attributeName);
                $attribute = $this->contextService->getAttribute($attributeName, $contextId);
                $text = str_replace('{' . $attributeId . '}', $attribute->getValue(), $text);
            }
        }

        return $text;
    }

    /**
     * @inheritdoc
     */
    public function setContextService(ContextService $contextService) : void
    {
        $this->contextService = $contextService;
    }

    /**
     * @param AttributeResolver $attributeResolver
     */
    public function setAttributeResolver(AttributeResolver $attributeResolver): void
    {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * @param $attribute
     * @param $contextId
     * @param $attributeId
     */
    private function determineContext($attribute, &$contextId, &$attributeId)
    {
        $matches = explode('.', $attribute);

        if (count($matches) == 2) {
            $contextId = $matches[0];
            $attributeId = $matches[1];
        }

        if (count($matches) == 1) {
            $attributeId = $matches[0];
            $contextId = ContextService::UNDEFINED_CONTEXT;
        }
    }
}
