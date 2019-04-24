<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\ResponseEngine\Message\WebchatMessageFormatter;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
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

        // Get this intent's message templates.
        $messageTemplates = MessageTemplate::forIntent($intentName)->get();

        if (count($messageTemplates) === 0) {
            throw new NoMatchingMessagesException(sprintf("No messages found for intent %s", $intentName));
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
                list($contextId, $attributeId) = ContextParser::determineContext($condition['attributeName']);

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
                };
            }

            if ($conditionsPass) {
                $selectedMessageTemplate = $messageTemplate;
                break;
            }
        }

        if (empty($selectedMessageTemplate)) {
            throw new NoMatchingMessagesException(
                sprintf("No messages with passing conditions found for intent %s", $intentName)
            );
        }

        // Get the messages.
        $formatter = new WebchatMessageFormatter();
        $messages = $formatter->getMessages($selectedMessageTemplate->message_markup);

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
                list($contextId, $attributeName) = ContextParser::determineContext($attributeId);
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
}
