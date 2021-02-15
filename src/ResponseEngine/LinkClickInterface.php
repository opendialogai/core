<?php

namespace OpenDialogAi\ResponseEngine;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;

interface LinkClickInterface
{
    public function save(UtteranceAttribute $utterance): LinkClick;
}
