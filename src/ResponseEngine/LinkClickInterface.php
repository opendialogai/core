<?php

namespace OpenDialogAi\ResponseEngine;

use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;

interface LinkClickInterface
{
    public function save(WebchatUrlClickUtterance $utterance): LinkClick;
}
