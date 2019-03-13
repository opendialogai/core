<?php

namespace OpenDialogAi\ActionEngine\Actions;

abstract class BaseAction implements ActionInterface
{
    protected $performs = [];

    public function performActions() : array
    {
        return $this->performs;
    }
}
