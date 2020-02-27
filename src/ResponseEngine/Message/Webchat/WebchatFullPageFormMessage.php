<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\FullPageFormMessage;

class WebchatFullPageFormMessage extends WebchatBaseFormMessage implements FullPageFormMessage
{
    protected $messageType = self::TYPE;
}
