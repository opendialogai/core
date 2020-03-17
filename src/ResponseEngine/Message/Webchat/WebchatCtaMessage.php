<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\CtaMessage;

class WebchatCtaMessage extends WebchatMessage implements CtaMessage
{
    protected $messageType = self::TYPE;
}
