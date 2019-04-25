<?php

namespace OpenDialogAi\ResponseEngine\Message;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ResponseEngine\Message\Webchat\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use SimpleXMLElement;

/**
 * Webchat Message formatter.
 */
class WebChatMessageFormatter implements MessageFormatterInterface
{
    /** @var ContextService */
    private $contextService;

    public function __construct()
    {
        $this->contextService = app()->make(ContextService::class);
    }

    /**
     * Convert the template to the appropriate message types.
     *
     * @param String $markup
     * @return WebChatMessage|Array $messages
     */
    public function getMessages(string $markup)
    {
        $messages = [];
        try {
            $message = new SimpleXMLElement($markup);

            foreach ($message->children() as $item) {
                // Handle attribute messages.
                if ($item->getName() === 'attribute-message') {
                    // Resolve custom context values.
                    if (!empty((string) $item)) {
                        $attribute = explode('.', (string) $item, 2);
                        $attributeText = $this->contextService->getAttributeValue($attribute[1], $attribute[0]);
                        $item = new SimpleXMLElement($attributeText);
                    }

                    foreach ($item->children() as $child) {
                        $messages[] = $this->parseMessage($child);
                    }
                } else {
                    // Convert the markup to the appropriate type of message.
                    $messages[] = $this->parseMessage($item);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Message Builder error: ' . $e->getMessage());
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
            case 'button-message':
                $template = [
                    'text' => (string) $item->text,
                ];
                foreach ($item->button as $button) {
                    $template['buttons'][] = [
                        'callback' => (string) $button->callback,
                        'text' => (string) $button->text,
                        'value' => (string) $button->value,
                    ];
                }
                return $this->generateButtonMessage($template);
                break;
            case 'image-message':
                $template = [
                    'link' => (string) $item->link,
                    'src' => (string) $item->src,
                ];
                return $this->generateImageMessage($template);
                break;
            case 'text-message':
                $template = ['text' => (string) $item];
                return $this->generateTextMessage($template);
                break;
            default:
                $template = ['text' => 'Sorry, I did not understand this message type.'];
                return $this->generateTextMessage($template);
                break;
        }
    }

    public function generateButtonMessage(array $template)
    {
        $message = new WebChatButtonMessage();
        $message->setText($template['text']);
        foreach ($template['buttons'] as $button) {
            $message->addButton(new WebChatButton($button['text'], $button['callback'], $button['value']));
        }
        return $message;
    }

    public function generateEmptyMessage()
    {
        $message = new EmptyMessage();
        return $message;
    }

    public function generateFormMessage(array $template)
    {
        // @TODO
        return '';
    }

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
        $message = (new WebChatMessage())->setText($template['text']);
        return $message;
    }
}
