<?php

namespace OpenDialogAi\InterpreterEngine\QnA;

class QnAResponse
{
    private $answers;

    public function __construct($response)
    {
        $this->answers = isset($response->answers) ? $response->answers : [];
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }
}
