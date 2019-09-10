<?php

namespace OpenDialogAi\ResponseEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\ResponseEngine\Message\WebchatMessageFormatter;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /**
     * @inheritdoc
     *
     * @return OpenDialogMessages $messageWrapper
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getMessageForIntent(string $intentName): OpenDialogMessages
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

        $messageWrapper = new OpenDialogMessages();
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
                $replacement = ' ';
                try {
                    [$contextId, $attributeName] = ContextParser::determineContextAndAttributeId($attributeId);
                    $replacement = ContextService::getAttributeValue($attributeName, $contextId);
                    $replacement = $this->escapeCharacters($replacement);
                } catch (ContextDoesNotExistException $e) {
                    Log::warning($e->getMessage());
                } catch (AttributeDoesNotExistException $e) {
                    Log::warning($e->getMessage());
                }
                $text = str_replace('{' . $attributeId . '}', $replacement, $text);
            }
        }

        return $text;
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
        foreach ($messageTemplate->getConditions() as $contextId => $conditions) {
            foreach ($conditions as $conditionArray) {
                /** @var Condition $condition */
                $condition = array_values($conditionArray)[0];
                $attributeName = array_keys($conditionArray)[0];

                $attribute = $this->getAttributeForCondition($attributeName, $contextId);

                if (!$condition->compareAgainst($attribute)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Tries to get the attribute from the right context. Or returns a null value attribute if it does not exist
     *
     * @param $attributeName
     * @param $contextId
     * @return AttributeInterface
     */
    protected function getAttributeForCondition($attributeName, $contextId): AttributeInterface
    {
        try {
            $attribute = ContextService::getAttribute($attributeName, $contextId);
        } catch (AttributeDoesNotExistException $e) {
            // If the attribute does not exist, return a null value attribute
            $attribute = AttributeResolver::getAttributeFor($attributeName, null);
        }

        return $attribute;
    }

    /**
     * Escapes the ampersand character (& => &amp;)
     *
     * @param $replacement
     * @return string
     */
    private function escapeCharacters($replacement): string
    {
        $replacement = str_replace('&', '&amp;', $replacement);

        return $replacement;
    }
}
