<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\RichMessage;

class WebchatRichMessage extends WebchatBaseRichMessage implements RichMessage
{
    protected $messageType = self::TYPE;
}
