<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use OpenDialogAi\Core\Conversation\Intent;

class QnAQuestionMatchedIntent extends Intent
{
    const QNA_QUESTION_MATCHED = "intent.core.QnAQuestionMatched";

    public function __construct()
    {
        parent::__construct(self::QNA_QUESTION_MATCHED);
        parent::setConfidence(1);
    }
}
