<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\TextMessage;

class WebchatTextMessage extends WebchatMessage implements TextMessage
{
    protected $messageType = self::TYPE;
}
