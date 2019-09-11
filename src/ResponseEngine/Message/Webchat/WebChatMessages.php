<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\Message;

class WebChatMessages
{
    /** @var Message[] */
    protected $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * Adds a message object.
     *
     * @param Message $message - a message to add.
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Return the message objects.
     *
     * @return Message|array $messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the messages to post.
     *
     * @return array $messagesToPost
     */
    public function getMessageToPost()
    {
        $messagesToPost = [];
        foreach ($this->messages as $message) {
            $messagesToPost[] = $message->getMessageToPost();
        }

        return $messagesToPost;
    }
}
