<?php

namespace OpenDialogAi\ResponseEngine\Message;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ResponseEngine\Message\Webchat\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButton;
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
    /** @var ResponseEngineService */
    private $responseEngineService;

    public function __construct()
    {
        $this->responseEngineService = app()->make(ResponseEngineServiceInterface::class);
    }

    public function getMessages(string $markup)
    {
        $messages = [];
        try {
            $message = new SimpleXMLElement($markup);
            foreach ($message->children() as $item) {
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
                        $messages[] = $this->generateButtonMessage($template);
                        break;
                    case 'image-message':
                        $template = [
                            'link' => (string) $item->link,
                            'src' => (string) $item->src,
                        ];
                        $messages[] = $this->generateImageMessage($template);
                        break;
                    case 'text-message':
                        $template = ['text' => (string) $item];
                        $messages[] = $this->generateTextMessage($template);
                        break;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Message Builder error: ' . $e->getMessage());
            return [];
        }

        return $messages;
    }

    public function generateButtonMessage(array $template)
    {
        $text = $this->responseEngineService->fillAttributes($template['text']);
        $message = new WebChatButtonMessage();
        $message->setText($text);
        foreach ($template['buttons'] as $button) {
            $buttonText = $this->responseEngineService->fillAttributes($button['text']);
            $message->addButton(new WebChatButton($buttonText, $button['callback'], $button['value']));
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
        $text = $this->responseEngineService->fillAttributes($template['text']);
        $message = (new WebChatMessage())->setText($text);
        return $message;
    }
}
