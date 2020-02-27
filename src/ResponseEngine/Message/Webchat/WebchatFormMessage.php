<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\FormMessage;

class WebchatFormMessage extends WebchatBaseFormMessage implements FormMessage
{
    protected $messageType = self::TYPE;
}
