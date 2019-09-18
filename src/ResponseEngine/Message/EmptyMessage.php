<?php

declare(strict_types=1);

namespace OpenDialogAi\ResponseEngine\Message;

class EmptyMessage extends OpenDialogMessage
{
    protected $messageType = 'empty';

    public function __construct()
    {
        parent::__construct();

        $this->setAsEmpty();
    }
}
