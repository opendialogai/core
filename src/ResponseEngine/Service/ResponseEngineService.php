<?php

namespace OpenDialogAi\ResponseEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\ResponseEngine\Message\WebchatMessageFormatter;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /* @var OperationServiceInterface */
    protected $operationService;

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
     * @param OperationServiceInterface $operationService
     */
    public function setOperationService(OperationServiceInterface $operationService): void
    {
        $this->operationService = $operationService;
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
        foreach ($messageTemplate->getConditions() as $condition) {
            if (!$this->operationService->checkCondition($condition)) {
                return false;
            }
        }

        return true;
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
