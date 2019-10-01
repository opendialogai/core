<?php

declare(strict_types=1);

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
     * @return array
     */
    public function getMessages(): array;

    /**
     * Get the messages to post
     *
     * @return array
     */
    public function getMessageToPost(): array;
}
