<?php

namespace OpenDialogAi\ResponseEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AttributeDoesNotExistException;
use OpenDialogAi\AttributeEngine\AttributeInterface;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Exceptions\NameNotSetException;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\ResponseEngine\Exceptions\FormatterNotRegisteredException;
use OpenDialogAi\ResponseEngine\Formatters\MessageFormatterInterface;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /* @var OperationServiceInterface */
    protected $operationService;

    /** @var string A regex pattern for a valid formatter name */
    private $validNamePattern = "/^formatter\.[a-z_]*.[a-z_]*$/";

    /**
     * A place to store a cache of available formatters
     * @var MessageFormatterInterface[]
     */
    private $availableFormatters = [];

    /**
     * @inheritdoc
     */
    public function getMessageForIntent(string $platform, string $intentName): OpenDialogMessages
    {
        $this->registerAvailableFormatters();

        try {
            $formatter = $this->getFormatter("formatter.core.{$platform}");
        } catch (FormatterNotRegisteredException $e) {
            throw new FormatterNotRegisteredException("Formatter with name $platform is not available");
        }

        $selectedMessageTemplate = null;

        /** @var MessageTemplate[] $messageTemplates */
        $messageTemplates = MessageTemplate::forIntent($intentName)->get();

        if (count($messageTemplates) === 0) {
            $message = sprintf("No messages found for intent %s", $intentName);
            throw new NoMatchingMessagesException($message);
        }

        // Get the message with the most conditions matched.
        $selectedMessageConditionsNumber = -1;
        $selectedMessageTemplate = $this->selectMessageFromConditions($messageTemplates, $selectedMessageConditionsNumber);

        if (is_null($selectedMessageTemplate)) {
            $message = sprintf("No messages with passing conditions found for intent %s", $intentName);
            throw new NoMatchingMessagesException($message);
        } else {
            $markup = $this->fillAttributes($selectedMessageTemplate->message_markup);
            $markup = $this->escapeCharacters($markup);

            $messages = $formatter->getMessages($markup);

            $this->setMessagesIntent($intentName, $messages);
        }

        return $messages;
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
                    $replacement = $this->getReplacement($attributeId);
                } catch (ContextDoesNotExistException $e) {
                    Log::warning(
                        sprintf(
                            'Invalid context name "%s" when filling message attributes',
                            ContextParser::determineContextId($attributeId)
                        )
                    );
                } catch (AttributeDoesNotExistException $e) {
                    Log::warning(sprintf('Cannot find attribute "%s" when filling message attributes', $attributeId));
                }
                Log::debug(sprintf('Using "%s" for attribute "%s" when filling message attributes', $replacement, $attributeId));
                $text = str_replace('{' . $attributeId . '}', $replacement, $text);
            }
        }

        return $text;
    }

    /**
     * @inheritDoc
     */
    public function setOperationService(OperationServiceInterface $operationService): void
    {
        $this->operationService = $operationService;
    }

    /**
     * @inheritDoc
     */
    public function registerAvailableFormatters(): void
    {
        /** @var MessageFormatterInterface $formatter */
        foreach ($this->getAvailableFormatterConfig() as $formatter) {
            $this->registerSingleFormatter($formatter);
        }
    }

    /**
     * @inheritDoc
     */
    public function registerFormatter(MessageFormatterInterface $formatter, $force = false): void
    {
        try {
            if ($force || !isset($this->getAvailableFormatters()[$formatter::getName()])) {
                $this->registerSingleFormatter(get_class($formatter));
            }
        } catch (NameNotSetException $e) {
            Log::warning(
                sprintf(
                    'Not adding formatter %s - it does not have a name',
                    get_class($formatter)
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getAvailableFormatters(): array
    {
        Log::debug('Getting available formatters');
        if (empty($this->availableFormatters)) {
            $this->registerAvailableFormatters();
        }

        return $this->availableFormatters;
    }

    /**
     * @inheritDoc
     */
    public function getFormatter(string $formatterName): MessageFormatterInterface
    {
        Log::debug("Getting formatter: {$formatterName}");
        if ($this->isFormatterAvailable($formatterName)) {
            Log::debug(sprintf("Getting formatter with name %s", $formatterName));
            return $this->availableFormatters[$formatterName];
        }

        throw new FormatterNotRegisteredException("Formatter with name $formatterName is not available");
    }

    /**
     * @inheritdoc
     */
    public function isFormatterAvailable(string $formatterName): bool
    {
        if (in_array($formatterName, array_keys($this->getAvailableFormatters()))) {
            Log::debug(sprintf("Formatter with name %s is available", $formatterName));
            return true;
        }

        Log::debug(sprintf("Formatter with name %s is not available", $formatterName));
        return false;
    }

    /**
     * @inheritDoc
     */
    public function buildTextFormatterErrorMessage(string $platform, string $message): OpenDialogMessages
    {
        try {
            $formatter = $this->getFormatter("formatter.core.{$platform}");
        } catch (FormatterNotRegisteredException $e) {
            throw new FormatterNotRegisteredException("Formatter with name $platform is not available");
        }

        return $formatter->getMessages(sprintf('<message><text-message>%s</text-message></message>', $message));
    }

    /**
     * Checks the conditions on all message templates, and selects the one with the most passing conditions.
     *
     * @param $messageTemplates
     * @param int $selectedMessageConditionsNumber
     * @return MessageTemplate|null
     */
    private function selectMessageFromConditions($messageTemplates, int $selectedMessageConditionsNumber)
    {
        $selectedMessageTemplate = null;

        /** @var MessageTemplate[] $messageTemplates */
        foreach ($messageTemplates as $messageTemplate) {
            if ($this->messageMeetsConditions($messageTemplate)) {
                $messageConditionsNumber = $messageTemplate->getNumberOfConditions();

                if ($messageConditionsNumber > $selectedMessageConditionsNumber) {
                    $selectedMessageTemplate = $messageTemplate;
                    $selectedMessageConditionsNumber = $messageConditionsNumber;
                }
            }
        }

        return $selectedMessageTemplate;
    }

    /**
     * Checks whether a message's conditions are met. Returns true if there are no conditions, or if all conditions on
     * the message template are met
     *
     * @param $messageTemplate
     * @return mixed
     */
    private function messageMeetsConditions(MessageTemplate $messageTemplate): bool
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
    public function escapeCharacters($replacement): string
    {
        $replacement = str_replace('&', '&amp;', $replacement);
        $replacement = str_replace('%', '%%', $replacement);

        return $replacement;
    }

    /**
     * Checks if the name of the formatter is in the right format
     *
     * @param string $name
     * @return bool
     */
    private function isValidName(string $name): bool
    {
        return preg_match($this->validNamePattern, $name) === 1;
    }

    /**
     * Returns the list of available formatters as registered in the available_formatters config
     *
     * @return []
     */
    private function getAvailableFormatterConfig()
    {
        return config('opendialog.response_engine.available_formatters');
    }

    /**
     * Registers the given formatter if it has a name that is valid
     *
     * @param string $formatter Fully qualified class name of the formatter to add
     */
    private function registerSingleFormatter(string $formatter): void
    {
        try {
            $name = $formatter::getName();

            if ($this->isValidName($name)) {
                $this->availableFormatters[$name] = new $formatter($this);
            } else {
                Log::warning(
                    sprintf("Not adding formatter with name %s. Name is in wrong format", $name)
                );
            }
        } catch (NameNotSetException $e) {
            Log::warning(
                sprintf("Not adding formatter %s. It has not defined a name", $formatter)
            );
        }
    }

    /**
     * @param $attributeId
     * @return mixed
     */
    private function getReplacement($attributeId)
    {
        $parsedAttribute = ContextParser::parseAttributeName($attributeId);
        $replacement = ContextService::getAttribute($parsedAttribute->attributeId, $parsedAttribute->contextId);
        if ($parsedAttribute->getAccessor()) {
            $attributeValue = $replacement->getValue($parsedAttribute->getAccessor());
            if ($attributeValue instanceof AttributeInterface) {
                return $attributeValue->toString();
            }
            return $attributeValue;
        }
        return $replacement->toString();
    }

    /**
     * @param $intentName
     * @param OpenDialogMessages $messages
     */
    private function setMessagesIntent($intentName, $messages)
    {
        foreach ($messages->getMessages() as $message) {
            $message->setIntent($intentName);
        }
    }
}
