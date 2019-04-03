<?php

namespace InterpreterEngine\Service;

use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class InterpreterService implements InterpreterServiceInterface
{
    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        // TODO: Implement interpret() method.
    }
}
