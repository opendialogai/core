<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\FullPageRichMessage;

class WebchatFullPageRichMessage extends WebchatBaseRichMessage implements FullPageRichMessage
{
    protected $messageType = self::TYPE;
}
