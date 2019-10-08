<?php

namespace OpenDialogAi\ResponseEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\Core\Exceptions\NameNotSetException;
use OpenDialogAi\ResponseEngine\Exceptions\FormatterNotRegisteredException;
use OpenDialogAi\ResponseEngine\Message\MessageFormatterInterface;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;

class ResponseEngineService implements ResponseEngineServiceInterface
{
    /* @var OperationServiceInterface */
    protected $operationService;

    /** @var string A regex pattern for a valid formatter name */
    private $validNamePattern = "/^formatter\.core.[a-z_]*$/";

    /**
     * A place to store a cache of available formatters
     * @var []
     */
    private $availableFormatters = [];

    /**
     * @inheritdoc
     *
     * @param string $platform
     * @param string $intentName
     * @throws NoMatchingMessagesException
     * @return OpenDialogMessages
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
        $selectedMessageTemplate = $this->getCorrectMessage($messageTemplates, $selectedMessageConditionsNumber);

        if (is_null($selectedMessageTemplate)) {
            $message = sprintf("No messages with passing conditions found for intent %s", $intentName);
            throw new NoMatchingMessagesException($message);
        } else {
            $markup = $this->fillAttributes($selectedMessageTemplate->message_markup);

            $messages = $formatter->getMessages($markup);
        }

        return $messages;
    }

    public function getCorrectMessage($messageTemplates, int $selectedMessageConditionsNumber)
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

    /**
     * Loops through all available formatters from config, and creates a local array keyed by the name of the
     * formatter
     */
    public function registerAvailableFormatters(): void
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
            } catch (NameNotSetException $e) {
                Log::warning(
                    sprintf("Not adding formatter %s. It has not defined a name", $formatter)
                );
            }
        }
    }

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
     * @return MessageFormatterInterface
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

    /**
     * @param string $platform
     * @param string $message
     * @throws FormatterNotRegisteredException
     * @return OpenDialogMessages
     */
    public function buildTextFormatterErrorMessage(string $platform, string $message)
    {
        try {
            $formatter = $this->getFormatter("formatter.core.{$platform}");
        } catch (FormatterNotRegisteredException $e) {
            throw new FormatterNotRegisteredException("Formatter with name $platform is not available");
        }

        return $formatter->getMessages(sprintf('<message><text-message>%s</text-message></message>', $message));
    }
}
