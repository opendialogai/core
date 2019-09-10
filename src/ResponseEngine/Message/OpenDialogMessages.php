<?php

declare(strict_types=1);

namespace OpenDialogAi\Core\ResponseEngine\Message;

use OpenDialogAi\Core\ResponseEngine\Contracts\OpenDialogMessageContract;

class OpenDialogMessages
{
    /**
     * A collection of messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * OpenDialogMessages constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * Adds a message object.
     *
     * @param OpenDialogMessageContract $message - a message to add.
     */
    public function addMessage(OpenDialogMessageContract $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Returns the message objects
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Get the messages to post
     *
     * @return array
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
