<?php

namespace OpenDialogAi\ActionEngine\Results;

class ActionResult
{
    protected $success = true;

    public function isSuccessful()
    {
        return $this->success;
    }
}
