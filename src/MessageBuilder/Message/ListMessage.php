<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Button\CallbackButton;
use OpenDialogAi\MessageBuilder\Message\Button\LinkButton;
use OpenDialogAi\MessageBuilder\Message\Button\TabSwitchButton;
use OpenDialogAi\MessageBuilder\Message\Button\TranscriptDownloadButton;
use OpenDialogAi\MessageBuilder\Message\Image\Image;

class ListMessage
{
    public $viewType;

    public $title;

    public $messages = [];

    /**
     * ListMessage constructor.
     * @param $viewType
     */
    public function __construct($viewType, $title = '')
    {
        $this->viewType = $viewType;
        $this->title = $title;
    }

    public function addMessage($type, $message)
    {
        if ($type == 'button') {
            $buttonMessage = new ButtonMessage($message['text'], $message['external']);
            foreach ($message['buttons'] as $button) {
                if (isset($button['download'])) {
                    $buttonMessage->addButton(
                        (new TranscriptDownloadButton($button['text']))
                    );
                } elseif (isset($button['tab_switch'])) {
                    $buttonMessage->addButton(
                        (new TabSwitchButton($button['text']))
                    );
                } elseif (isset($button['link'])) {
                    $buttonMessage->addButton(
                        (new LinkButton($button['text'], $button['link'], $button['link_new_tab']))
                    );
                } else {
                    $buttonMessage->addButton(
                        (new CallbackButton($button['text'], $button['callback'], $button['value']))
                    );
                }
            }
            $this->messages[] = $buttonMessage;
        } elseif ($type == 'image') {
            $this->messages[] = new ImageMessage($message['src'], $message['link'], $message['new_tab']);
        } elseif ($type == 'text') {
            $this->messages[] = new TextMessage($message['text']);
        } elseif ($type == 'rich') {
            $richMessage = new RichMessage($message['title'], $message['subtitle'], $message['text']);

            if (!empty($message['buttons'])) {
                foreach ($message['buttons'] as $button) {
                    if (isset($button['download'])) {
                        $richMessage->addButton(
                            (new TranscriptDownloadButton($button['text']))
                        );
                    } elseif (isset($button['tab_switch'])) {
                        $richMessage->addButton(
                            (new TabSwitchButton($button['text']))
                        );
                    } elseif (isset($button['link'])) {
                        $richMessage->addButton(
                            (new LinkButton($button['text'], $button['link'], $button['link_new_tab']))
                        );
                    } else {
                        $richMessage->addButton(
                            (new CallbackButton($button['text'], $button['callback'], $button['value']))
                        );
                    }
                }
            }
            if (!empty($message['image'])) {
                $richMessage->addImage(
                    new Image($message['image']['src'], $message['image']['url'], $message['image']['new_tab'])
                );
            }

            $this->messages[] = $richMessage;
        }
    }

    public function getMarkUp()
    {
        $itemMarkup = '';

        foreach ($this->messages as $message) {
            $itemMarkup .= '<item>' . $message->getMarkUp() . '</item>';
        }

        return <<<EOT
<list-message view-type="$this->viewType">
    <title>$this->title</title>
    $itemMarkup
</list-message>
EOT;
    }
}
