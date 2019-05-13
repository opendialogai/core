<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

/**
 * Class EmptyMessage
 *
 * An empty message will cause the ResponseEngine to just send an HTTP 200 with no content.
 *
 * Please do not use as part of an array of messages.
 *
 * @package OpenDialogAi\ResponseEngine\Message\Webchat
 */
class EmptyMessage extends WebChatMessage
{
    protected $messageType = 'empty';
    
    public function __construct()
    {
        parent::__construct();
        $this->setAsEmpty();
    }
}
