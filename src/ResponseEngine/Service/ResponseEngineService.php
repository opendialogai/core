<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\ResponseEngine\Message\WebchatMessageFormatter;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /** @var ContextService */
    protected $contextService;

    /** @var AttributeResolver */
    protected $attributeResolver;

    /**
     * @inheritdoc
     *
     * @return WebChatMessages $messageWrapper
     */
    public function getMessageForIntent(string $intentName): WebChatMessages
    {
        $selectedMessageTemplate = null;

        /** @var MessageTemplate[] $messageTemplates */
        $messageTemplates = MessageTemplate::forIntent($intentName)->get();

        if (count($messageTemplates) === 0) {
            throw new NoMatchingMessagesException(sprintf("No messages found for intent %s", $intentName));
        }

        foreach ($messageTemplates as $messageTemplate) {
            if ($this->messageMeetsConditions($messageTemplate)) {
                $selectedMessageTemplate = $messageTemplate;
                break;
            }
        }

        if ($selectedMessageTemplate === null) {
            throw new NoMatchingMessagesException(
                sprintf("No messages with passing conditions found for intent %s", $intentName)
            );
        }

        // Resolve all attributes in the markup.
        $markup = $this->fillAttributes($selectedMessageTemplate->message_markup);

        $formatter = new WebchatMessageFormatter();
        $messages = $formatter->getMessages($markup);

        $messageWrapper = new WebChatMessages();
        foreach ($messages as $message) {
            $messageWrapper->addMessage($message);
        }

        return $messageWrapper;
    }

    /**
     * @inheritdoc
     */
    public function fillAttributes($text): string
    {
        // Extract attributes that need to be resolved from the text
        $matches = [];
        $matchCount = preg_match_all("(\{(.*?)\})", $text, $matches, PREG_PATTERN_ORDER);
        if ($matchCount > 0) {
            foreach ($matches[1] as $attributeId) {
                [$contextId, $attributeName] = ContextParser::determineContextAndAttributeId($attributeId);
                $attribute = $this->contextService->getAttribute($attributeName, $contextId);
                $text = str_replace('{' . $attributeId . '}', $attribute->getValue(), $text);
            }
        }

        return $text;
    }

    /**
     * @inheritdoc
     */
    public function setContextService(ContextService $contextService): void
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
     * Checks whether a message's conditions are met. Returns true if there are no conditions, or if all conditions on
     * the message template are met
     *
     * @param $messageTemplate
     * @return mixed
     */
    protected function messageMeetsConditions(MessageTemplate $messageTemplate): bool
    {
        $conditions = $messageTemplate->getConditions();

        if (empty($conditions)) {
            return true;
        }

        $conditionsPass = true;
        foreach ($conditions as $condition) {
            $conditionsPass = $this->testCondition($condition);
        }

        if ($conditionsPass) {
            return true;
        }

        return false;
    }

    /**
     * Check if a condition passes by resolving it's attribute and comparing to the value specified
     *
     * @param $condition
     * @return bool
     */
    private function testCondition($condition): bool
    {
        $conditionsPass = true;

        [$contextId, $attributeId] = ContextParser::determineContextAndAttributeId($condition['attributeName']);

        // If we encounter an attribute that we wouldn't know how to resolve we will need to
        // bail now and fail the message.
        if (!$this->attributeResolver->isAttributeSupported($attributeId)) {
            $conditionsPass = false;
        }

        /* @var AttributeInterface $conditionAttribute */
        $conditionAttribute = $this->attributeResolver->getAttributeFor(
            $attributeId,
            $condition[MessageTemplate::ATTRIBUTE_VALUE]
        );

        /* @var Condition $conditionObject */
        $conditionObject = new Condition($conditionAttribute, $condition[MessageTemplate::OPERATION]);

        $attributeToCompareAgainst = $this->contextService->getAttribute($attributeId, $contextId);

        if (!$conditionObject->compareAgainst($attributeToCompareAgainst)) {
            $conditionsPass = false;
        }
        return $conditionsPass;
    }
}
