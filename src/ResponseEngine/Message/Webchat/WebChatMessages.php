<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

class WebChatMessages
{
    /** @var WebChatMessage[] */
    protected $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * Adds a message object.
     *
     * @param WebChatMessage $message - a message to add.
     */
    public function addMessage(WebChatMessage $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Return the message objects.
     *
     * @return WebChatMessage|Array $messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the messages to post.
     *
     * @return Array $messagesToPost
     */
    public function getMessageToPost()
    {
        $messagesToPost = [];
        foreach ($this->messages as $i => $message) {
            if ($i > 0) {
                $message->setInternal(true);
            }
            if ($i < count($this->messages) - 1) {
                $message->setHidetime(true);
            }

            $messagesToPost[] = $message->getMessageToPost();
        }

        return $messagesToPost;
    }
}
