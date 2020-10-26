<?php

namespace OpenDialogAi\ResponseEngine\Message;

interface OpenDialogMessage
{
    /**
     * Get the text from the message
     *
     * @return null|string
     */
    public function getText(): ?string;

    /**
     * Get the message data
     *
     * @return null|array
     */
    public function getData(): ?array;

    /**
     * Get the message to post back
     *
     * @return mixed
     */
    public function getMessageToPost();

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Sets the interpreter intent string to the message
     *
     * @param string $intent
     * @return $this
     */
    public function setIntent(string $intent): self;
}
