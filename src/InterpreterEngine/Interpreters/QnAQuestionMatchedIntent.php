<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use OpenDialogAi\Core\Conversation\Intent;

class QnAQuestionMatchedIntent extends Intent
{
    const QNA_QUESTION_MATCHED = "intent.core.QnAQuestionMatched";

    public function __construct()
    {
        $this->setODId(self::QNA_QUESTION_MATCHED);
        parent::__construct();
        parent::setConfidence(1);
    }
}
