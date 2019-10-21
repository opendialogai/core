<?php

namespace OpenDialogAi\ResponseEngine\Message;

interface OpenDialogMessages
{
    /**
     * Adds a message object.
     *
     * @param OpenDialogMessage $message - a message to add.
     */
    public function addMessage(OpenDialogMessage $message): void;

    /**
     * Returns the message objects
     *
     * @return OpenDialogMessage[]
     */
    public function getMessages(): array;

    /**
     * Get the messages to post
     *
     * @return array
     */
    public function getMessageToPost(): array;
}
