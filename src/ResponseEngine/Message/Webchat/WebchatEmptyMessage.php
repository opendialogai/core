<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\EmptyMessage;

class WebchatEmptyMessage extends WebchatMessage implements EmptyMessage
{
    protected $messageType = self::TYPE;

    public function __construct()
    {
        parent::__construct();

        $this->setAsEmpty();
    }
}
