<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;

class WebChatMessages implements OpenDialogMessages
{
    /** @var WebchatTextMessage[] */
    protected $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * @inheritDoc
     */
    public function addMessage(OpenDialogMessage $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return WebchatTextMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @inheritDoc
     */
    public function getMessageToPost(): array
    {
        $messagesToPost = [];
        foreach ($this->messages as $message) {
            $messagesToPost[] = $message->getMessageToPost();
        }

        return $messagesToPost;
    }
}
