<?php

declare(strict_types=1);

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\Core\ResponseEngine\Message\EmptyMessage;

class WebchatEmptyMessage extends EmptyMessage
{
    protected $messageType = 'empty';
}
