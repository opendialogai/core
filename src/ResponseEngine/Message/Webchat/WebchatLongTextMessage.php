<?php

declare(strict_types=1);

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\LongTextMessage;

class WebchatLongTextMessage extends LongTextMessage
{
    protected $messageType = 'longtext';
}
