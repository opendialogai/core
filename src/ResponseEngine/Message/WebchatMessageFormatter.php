<?php

namespace OpenDialogAi\ResponseEngine\Message;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\WebchatCallbackButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\WebchatTabSwitchButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use SimpleXMLElement;

/**
 * Webchat Message formatter.
 */
class WebChatMessageFormatter implements MessageFormatterInterface
{
    /** @var ContextService */
    private $contextService;

    /** @var ResponseEngineService */
    private $responseEngineService;

    public function __construct()
    {
        $this->contextService = app()->make(ContextService::class);
        $this->responseEngineService = app()->make(ResponseEngineServiceInterface::class);
    }

    /**
     * Convert the template to the appropriate message types.
     *
     * @param String $markup
     * @return WebChatMessage[]
     */
    public function getMessages(string $markup): array
    {
        $messages = [];
        try {
            $message = new SimpleXMLElement($markup);

            foreach ($message->children() as $item) {
                if ($item->getName() === self::ATTRIBUTE_MESSAGE) {
                    $attributeText = $this->getAttributeMessageText((string)$item);
                    return $this->getMessages($attributeText);
                }

                $messages[] = $this->parseMessage($item);
            }
        } catch (\Exception $e) {
            Log::warning(sprintf('Message Builder error: %s', $e->getMessage()));
            return [];
        }

        return $messages;
    }

    /**
     * Parse XML markup and convert to the appropriate Message class.
     *
     * @param SimpleXMLElement $item
     * @return WebChatMessage
     */
    private function parseMessage(SimpleXMLElement $item)
    {
        switch ($item->getName()) {
            case self::BUTTON_MESSAGE:
                $template = [
                    'text' => (string)$item->text,
                ];
                foreach ($item->button as $button) {
                    if (isset($button->tab_switch)) {
                        $template['buttons'][] = [
                            'text' => (string)$button->text,
                            'tab_switch' => true,
                        ];
                    } else {
                        $template['buttons'][] = [
                            'callback' => (string)$button->callback,
                            'text' => (string)$button->text,
                            'value' => (string)$button->value,
                        ];
                    }
                }
                return $this->generateButtonMessage($template);
                break;
            case self::IMAGE_MESSAGE:
                $template = [
                    'link' => (string)$item->link,
                    'src' => (string)$item->src,
                ];
                return $this->generateImageMessage($template);
                break;
            case self::TEXT_MESSAGE:
                $template = ['text' => (string)$item];
                return $this->generateTextMessage($template);
                break;
            default:
                $template = ['text' => 'Sorry, I did not understand this message type.'];
                return $this->generateTextMessage($template);
                break;
        }
    }

    /**
     * @param array $template
     * @return WebChatButtonMessage
     */
    public function generateButtonMessage(array $template)
    {
        $message = new WebChatButtonMessage();
        $message->setText($template['text']);
        foreach ($template['buttons'] as $button) {
            if (isset($button['tab_switch'])) {
                $message->addButton(new WebchatTabSwitchButton($button['text']));
            } else {
                $message->addButton(new WebchatCallbackButton($button['text'], $button['callback'], $button['value']));
            }
        }
        return $message;
    }

    /**
     * @return EmptyMessage
     */
    public function generateEmptyMessage()
    {
        $message = new EmptyMessage();
        return $message;
    }

    /**
     * @param array $template
     * @return string
     */
    public function generateFormMessage(array $template)
    {
        // @TODO
        return '';
    }

    /**
     * @param array $template
     * @return WebChatImageMessage
     */
    public function generateImageMessage(array $template)
    {
        $message = (new WebChatImageMessage())->setImgSrc($template['src'])->setImgLink($template['link']);
        return $message;
    }

    public function generateListMessage(array $template)
    {
        // @TODO
        return '';
    }

    public function generateLongTextMessage(array $template)
    {
        // @TODO
        return '';
    }

    public function generateTextMessage(array $template)
    {
        $message = (new WebChatMessage())->setText($template['text'], [], true);
        return $message;
    }

    /**
     * Resolves the attribute by name to get the value for the attribute message, then resolves any attributes
     * in the resulting text
     *
     * @param string $attributeName
     * @return string
     */
    protected function getAttributeMessageText($attributeName): string
    {
        [$contextId, $attributeId] = ContextParser::determineContextAndAttributeId($attributeName);
        $attributeValue = $this->contextService->getAttributeValue($attributeId, $contextId);

        return $this->responseEngineService->fillAttributes($attributeValue);
    }
}
