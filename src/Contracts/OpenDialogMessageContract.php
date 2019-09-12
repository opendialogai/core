<?php

declare(strict_types=1);

namespace OpenDialogAi\Core\Contracts;

interface OpenDialogMessageContract
{
    /**
     * Get the text from the message
     *
     * @return null|string
     */
    public function getText():?string;

    /**
     * Get the message data
     *
     * @return null|array
     */
    public function getData():?array;

    /**
     * Get the message to post back
     *
     * @return mixed
     */
    public function getMessageToPost();
}
