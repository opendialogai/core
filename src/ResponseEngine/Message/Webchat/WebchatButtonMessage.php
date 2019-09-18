<?php

declare(strict_types=1);

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\ButtonMessage;

class WebchatButtonMessage extends ButtonMessage
{
    protected $messageType = 'button';
}
