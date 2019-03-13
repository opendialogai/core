<?php

namespace OpenDialogAi\ActionEngine\Service;

class ActionEngineService
{
    public function getAvailableActions()
    {
        return config('opendialog.action_engine.available_actions');
    }
}
