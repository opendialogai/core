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
use OpenDialogAi\Core\ResponseEngine\Exceptions\FormatterNameNotSetException;
use OpenDialogAi\Core\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\Exceptions\FormatterNotRegisteredException;
use OpenDialogAi\ResponseEngine\Message\WebchatMessageFormatter;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /** @var string A regex pattern for a valid formatter name */
    private $validNamePattern = "/^formatter\.[a-z]*\.[a-z_]*$/";

    /**
     * A place to store a cache of available formatters
     * @var []
     */
    private $availableFormatters = [];

    /**
     * @todo figure out type
     */
    protected $formatter;

    /**
     * @inheritdoc
     *
     * @return OpenDialogMessages $messageWrapper
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getMessageForIntent(string $formatter, string $intentName): OpenDialogMessages
    {
        try {
            $this->formatter = $this->getFormatter($formatter);
        } catch (FormatterNotRegisteredException $e) {
            throw new FormatterNotRegisteredException("Formatter with name $formatter is not available");
        }

        $selectedMessageTemplate = null;

        /** @var MessageTemplate[] $messageTemplates */
        $messageTemplates = MessageTemplate::forIntent($intentName)->get();

        if (count($messageTemplates) === 0) {
            throw new NoMatchingMessagesException(sprintf("No messages found for intent %s", $intentName));
        }

        // Get the message with the most conditions matched.
        $selectedMessageConditionsNumber = -1;
        foreach ($messageTemplates as $messageTemplate) {
            if ($this->messageMeetsConditions($messageTemplate)) {
                $messageConditionsNumber = $messageTemplate->getNumberOfConditions();

                if ($messageConditionsNumber > $selectedMessageConditionsNumber) {
                    $selectedMessageTemplate = $messageTemplate;
                    $selectedMessageConditionsNumber = $messageConditionsNumber;
                }
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

    /**
     * Loops through all available formatters from config, and creates a local array keyed by the name of the
     * formatter
     */
    public function registerAvailableFormatters()
    {
        foreach ($this->getAvailableFormatterConfig() as $formatter) {
            try {
                $name = $formatter::getName();

                if ($this->isValidName($name)) {
                    $this->availableFormatters[$name] = new $formatter();
                } else {
                    Log::warning(
                        sprintf("Not adding formatter with name %s. Name is in wrong format", $name)
                    );
                }
            } catch (FormatterNameNotSetException $e) {
                Log::warning(
                    sprintf("Not adding formatter %s. It has not defined a name", $formatter)
                );
            }
        }
    }

    /**
     * @inheritdoc
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
     * @param string $formatterName
     * @return @todo
     */
    public function getFormatter(string $formatterName)
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
     * Checks if the name of the formatter is in the right format
     *
     * @param string $name
     * @return bool
     */
    private function isValidName(string $name) : bool
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
}
