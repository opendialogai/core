<?php

namespace OpenDialogAi\Core\Conversation;


class ActionResult
{
    const SUCCESS = 1;
    const FAIL = 0;

    private $status;

    private $results;

    public function __construct($status, $results)
    {
        $this->status = $status;
        $this->results = $results;
    }
}
